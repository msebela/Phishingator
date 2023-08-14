<?php
/**
 * Třída sdružující testy pro voláního externího monitoringu.
 *
 * @author Martin Šebela
 */
class MonitoringController extends Controller {
  /**
   * Zpracuje vstup z URL adresy a zavolá odpovídající metodu.
   *
   * @param array $arguments           Uživatelský vstup
   * @return void
   * @throws Exception
   */
  public function process($arguments) {
    $this->checkPermissionCall();

    if (isset($_GET['action'])) {
      if ($_GET['action'] == 'get-monitoring-status') {
        echo $this->getMonitoringStatus();
      }
    }

    exit();
  }


  /**
   * Vrátí, zdali je zdrojová IP adresa oprávněná k přístupu.
   *
   * @return bool
   */
  public static function isValidSourceIP() {
    $valid = false;

    $allowedIPs = explode(',', MONITORING_ALLOWED_IP);

    if ($allowedIPs && in_array($_SERVER['HTTP_X_REAL_IP'], $allowedIPs)) {
      $valid = true;
    }

    return $valid;
  }


  /**
   * Ověří, zdali jsou pro volání metod dostatečná oprávnění.
   *
   * @return void
   */
  private function checkPermissionCall() {
    if (!self::isValidSourceIP()) {
      Logger::error('Unauthorized access to display monitoring status.', $_SERVER['REMOTE_ADDR']);
      $invalid = true;
    }

    $token = $_SERVER['HTTP_PHISHINGATOR_TOKEN'] ?? '';

    if ($token != getenv('PHISHINGATOR_TOKEN')) {
      Logger::error('Invalid token to display monitoring status.', $token);
      $invalid = true;
    }

    if (isset($invalid)) {
      http_response_code(403);
      exit();
    }
  }


  /**
   * Aktivuje všechny testy monitoringu a vrátí jejich výsledek
   * společně s diagnostickými informacemi.
   *
   * @return false|string              Výstup monitoringu v JSON
   * @throws Exception
   */
  private function getMonitoringStatus() {
    $result = [
      'phishingator-version' => WEB_VERSION,
      'phishingator-organization' => getenv('ORG'),
      'error-messages' => $this->getErrorMessages(),
      'domains-list' => $this->getDomainsList(),
      'tests' => [
        $this->formalizeTestResult('database-backup', $this->databaseBackupTest()),
        $this->formalizeTestResult('database-connection', $this->databaseTest()),
        $this->formalizeTestResult('ldap-connection', $this->ldapTest()),
        $this->formalizeTestResult('credentials-valid', $this->credentialsTest()),
        $this->formalizeTestResult('credentials-invalid', $this->credentialsTest(true))
      ]
    ];

    return json_encode($result);
  }


  /**
   * Sjednotí a vrátí výsledek testu pro výstup monitoringu.
   *
   * @param string $name               Název testu
   * @param bool $result               Výsledek testu
   * @return array                     Výsledek testu určený pro výstup monitoringu
   */
  private function formalizeTestResult($name, $result) {
    return [
      'name' => $name,
      'result' => $this->getTestReturnCode($result)
    ];
  }


  /**
   * Vrátí návratový kód pro monitorující službu v závislosti na výsledku testu.
   *
   * @param int $testResult            Výsledek testu
   * @return int                       Návratový kód
   */
  private function getTestReturnCode($testResult) {
    if ($testResult === true) {
      // OK
      $returnCode = 0;
    }
    elseif ($testResult === false) {
      // Critical
      $returnCode = 2;
    }
    elseif ($testResult === -1) {
      // Warning
      $returnCode = 1;
    }
    else {
      // Unknown
      $returnCode = 3;
    }

    return $returnCode;
  }


  /**
   * Vrátí počet chybových hlášení (tj. ERROR) v aktuálním logu.
   *
   * @return int                       Počet chybových hlášení
   */
  private function getErrorMessages() {
    return substr_count(file_get_contents(LOGGER_FILEPATH), Logger::ERROR);
  }


  /**
   * Pokusí se vyhledat v posledním logu záznam o úspěšném provedení zálohy databáze k dnešnímu dni.
   *
   * @return bool                      TRUE pokud záloha proběhla úspěšně, jinak FALSE
   */
  private function databaseBackupTest() {
    $date = date('Y-m-d');
    $type = '\[' . Logger::INFO;
    $message = 'Backup file \(dump\) of Phishingator database for org. \'' . getenv('ORG') . '\' was successfully created.';

    return preg_match('/' . $date . ' (.*) ' . $type . ' (.*) ' . $message . '/', file_get_contents(LOGGER_FILEPATH)) === 1;
  }


  /**
   * Pokusí se navázat připojení s databází a vyhledat v seznamu uživatelů testovací identitu.
   *
   * @return bool                      TRUE pokud se vše podaří, jinak FALSE
   * @throws Exception
   */
  private function databaseTest() {
    Database::connect();

    $data = Database::querySingle(
      'SELECT `username` FROM `phg_users` WHERE `username` = ? AND `visible` = 1',
      getenv('TEST_USERNAME')
    );

    return $data != null && $data['username'] == getenv('TEST_USERNAME');
  }


  /**
   * Pokusí se navázat připojení s LDAP a vyhledat v jeho databázi testovací identitu.
   *
   * @return bool                      TRUE pokud se vše podaří, jinak FALSE
   */
  private function ldapTest() {
    $ldap = new LdapModel();

    $name = $ldap->getFullnameByUsername(getenv('TEST_USERNAME'));
    $email = $ldap->getEmailByUsername(getenv('TEST_USERNAME'));

    $ldap->close();

    return $name != null && $email != null;
  }


  /**
   * Pokusí se provést přihlášení do autentizační služby s přihlašovacími údaji testovací identity.
   *
   * @param bool $invalidCredentials   TRUE pokud má dojít k přihlášení s nesprávným heslem, jinak FALSE (výchozí)
   * @return bool                      TRUE pokud bylo přihlášení úspěšné, jinak FALSE
   */
  private function credentialsTest($invalidCredentials = false) {
    $password = getenv('TEST_PASSWORD');

    if ($invalidCredentials) {
      $password .= '-invalid';
    }

    $result = CredentialsTesterModel::tryLogin(getenv('TEST_USERNAME'), $password);

    if ($invalidCredentials) {
      $result = !$result;
    }

    return $result;
  }


  /**
   * Vrátí pole se jmény všech domén (tj. bez subdomén), které jsou
   * registrovány v proxy Phishingatoru.
   *
   * @return array                     Pole se jmény registrovaných domén
   */
  private function getDomainsList() {
    $registeredDomains = PhishingWebsiteModel::getDomainsRegisteredInProxy();
    $domainsList = [];

    foreach ($registeredDomains as $registeredDomain) {
      $domain = get_domain_from_url('https://' . $registeredDomain);

      if (!in_array($domain, $domainsList)) {
        $domainsList[] = $domain;
      }
    }

    return $domainsList;
  }
}
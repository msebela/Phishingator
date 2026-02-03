<?php
  /**
   * Třída sdružující testy pro volání externího monitoringu.
   *
   * @author Martin Šebela
   */
  class MonitoringController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
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
        Logger::error('Unauthorized access to display monitoring status.', $_SERVER['HTTP_X_REAL_IP']);
        $invalid = true;
      }

      $token = $_SERVER['HTTP_PHISHINGATOR_TOKEN'] ?? '';

      if ($token != PHISHINGATOR_TOKEN) {
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
     * @return false|string            Výstup monitoringu v JSON
     * @throws Exception
     */
    private function getMonitoringStatus() {
      $result = [
        'phishingator-version' => WEB_VERSION,
        'phishingator-organization' => getenv('ORG'),
        'error-messages' => $this->getErrorMessages(),
        'domains-list' => $this->getDomainsList(),
        'tests' => [
          $this->formalizeTestResult('database-backup', $this->databaseBackupMessageTest()),
          $this->formalizeTestResult('database-connection', $this->databaseConnectionTest()),

          $this->formalizeTestResult('ldap-local-connection', $this->localLdapConnectionTest()),
          $this->formalizeTestResult('ldap-remote-connection', $this->remoteLdapConnectionTest()),

          $this->formalizeTestResult('credentials-valid', $this->credentialsTest()),
          $this->formalizeTestResult('credentials-invalid', $this->credentialsTest(true)),

          $this->formalizeTestResult('credentials-email-valid', $this->credentialsTest(false, '@' . getenv('ORG_DOMAIN'))),
          $this->formalizeTestResult('credentials-email-invalid', $this->credentialsTest(true, '@' . getenv('ORG_DOMAIN')))
        ]
      ];

      return json_encode($result);
    }


    /**
     * Sjednotí a vrátí výsledek testu pro výstup monitoringu.
     *
     * @param string $name             Název testu
     * @param bool $result             Výsledek testu
     * @return array                   Výsledek testu určený pro výstup monitoringu
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
     * @param int $testResult          Výsledek testu
     * @return int                     Návratový kód
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
     * Vrátí počet chybových hlášení (tj. záznamy typu ERROR) v aktuálním logu.
     *
     * @return int|null                Počet chybových hlášení, jinak NULL při chybě
     */
    private function getErrorMessages() {
      $errorMessagesCount = 0;

      if (is_readable(LOGGER_FILEPATH)) {
        $logContent = file_get_contents(LOGGER_FILEPATH);

        if ($logContent !== false) {
          $errorMessagesCount = substr_count($logContent, Logger::ERROR);
        }
      }

      return $errorMessagesCount;
    }


    /**
     * Pokusí se vyhledat v aktuálním logu a logu z předchozího dne záznam o úspěšném provedení zálohy databáze.
     *
     * @return bool                    TRUE pokud byl nalezen záznam o úspěšně provedené záloze, jinak FALSE
     */
    private function databaseBackupMessageTest() {
      $isBackupMessageFound = false;

      $messageLevel = Logger::INFO;
      $message = 'Backup file \(dump\) of Phishingator database for org. \'' . getenv('ORG') . '\' was successfully created.';

      $logs = [LOGGER_FILEPATH, LOGGER_FILEPATH . '.1'];

      foreach ($logs as $i => $log) {
        if (is_readable($log)) {
          $date = date('Y-m-d', strtotime('-' . $i . ' day'));

          $logContent = file_get_contents($log);

          if ($logContent !== false && preg_match('/' . $date . ' (.*) \[' . $messageLevel . ' (.*) ' . $message . '/', $logContent) === 1) {
            $isBackupMessageFound = true;
            break;
          }
        }
      }

      return $isBackupMessageFound;
    }


    /**
     * Pokusí se navázat připojení s databází a vyhledat v seznamu uživatelů testovací identitu.
     *
     * @return bool                    TRUE pokud se vše podaří, jinak FALSE
     * @throws Exception
     */
    private function databaseConnectionTest() {
      Database::connect();

      $data = Database::querySingle(
        'SELECT `username` FROM `phg_users` WHERE `username` = ? AND `visible` = 1',
        TEST_USERNAME
      );

      return $data != null && $data['username'] == TEST_USERNAME;
    }


    /**
     * Pokusí se navázat připojení k lokálnímu LDAP a vyhledat v jeho struktuře testovací identitu.
     *
     * @return bool                    TRUE pokud se vše podaří, jinak FALSE
     */
    private function localLdapConnectionTest() {
      $ldap = new LdapModel();

      $name = $ldap->getFullnameByUsername(TEST_USERNAME);
      $email = $ldap->getEmailByUsername(TEST_USERNAME);

      $ldap->close();

      return $name != null && $email != null;
    }


    /**
     * Pokusí se navázat připojení s externím LDAP.
     *
     * @return bool                    TRUE v případě úspěšného navazání, FALSE pokud ne,
     *                                 3 pokud připojení k externímu LDAP není nastaveno
     */
    private function remoteLdapConnectionTest() {
      $result = false;

      $ldapHost = getenv('LDAP_MIGRATOR_HOST');

      if (!empty($ldapHost)) {
        $ldap = ldap_connect($ldapHost);

        if ($ldap) {
          ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
          ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 5);

          ldap_unbind($ldap);

          $result = true;
        }
      }
      else {
        // Připojení k externímu LDAP není nastaveno - tj. stav neznámý.
        $result = 3;
      }

      return $result;
    }


    /**
     * Pokusí se provést přihlášení do autentizační služby s přihlašovacími údaji testovací identity.
     *
     * @param bool $wrongPassword      TRUE pokud má dojít k přihlášení s nesprávným heslem, jinak FALSE (výchozí)
     * @param string $usernameSuffix   Nepovinný sufix přidávaný k uživatelskému jménu (např. doména)
     * @return bool                    TRUE pokud byl výsledek správný, FALSE pokud ne, 3 pokud byl neznámý
     */
    private function credentialsTest($wrongPassword = false, $usernameSuffix = '') {
      if (!($wrongPassword && MONITORING_SKIP_TEST_CREDS_INVALID)) {
        if ($wrongPassword) {
          $password = 'test-wrong-password';
        }
        else {
          $password = TEST_PASSWORD;
        }

        $result = CredentialsTesterModel::tryLogin(TEST_USERNAME . $usernameSuffix, $password);

        if ($wrongPassword) {
          $result = !$result;
        }
      }
      else {
        // Testování zadání neplatných údajů se na základě konfigurace neprovádí - tj. stav neznámý.
        $result = 3;
      }

      return $result;
    }


    /**
     * Vrátí pole se jmény všech domén (tj. bez subdomén), které jsou
     * registrovány v proxy Phishingatoru.
     *
     * @return array                   Pole se jmény registrovaných domén
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
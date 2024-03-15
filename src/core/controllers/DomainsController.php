<?php
/**
 * Třída zpracovává požadavky pro automatizovanou správu domén a subdomén
 * podvodných stránek s ohledem na (sub)domény evidované u podvodných
 * stránek v databázi a (sub)domény uložené v konfiguraci Phishingatoru.
 *
 * @author Martin Šebela
 */
class DomainsController extends Controller {
  /**
   * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
   *
   * @param array $arguments           Uživatelský vstup
   * @return void
   * @throws Exception
   */
  public function process($arguments) {
    $this->checkPermissionCall();

    if (isset($_GET['action'])) {
      if ($_GET['action'] == 'domains-to-activate') {
        $this->getDomainsToActivate();
      }
      elseif ($_GET['action'] == 'domains-to-deactivate') {
        $this->getDomainsToDeactivate();
      }
    }

    exit();
  }


  /**
   * Vrátí, zdali je zdrojová IP adresa oprávněná k volání metod.
   *
   * @return bool
   */
  public static function isValidSourceIP() {
    $valid = false;

    $allowedIPs = explode(',', DOMAINER_ALLOWED_IP);

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
      Logger::error('Unauthorized access to get fraudulent domains list.', $_SERVER['REMOTE_ADDR']);
      $invalid = true;
    }

    $token = $_SERVER['HTTP_PHISHINGATOR_TOKEN'] ?? '';

    if ($token != getenv('PHISHINGATOR_TOKEN')) {
      Logger::error('Invalid token to get fraudulent domains list.', $token);
      $invalid = true;
    }

    if (isset($invalid)) {
      http_response_code(403);
      exit();
    }
  }


  /**
   * Vypíše ve formátu JSON seznam (sub)domén podvodných stránek,
   * které nejsou v aktuální konfiguraci Phishingatoru aktivovány.
   *
   * @return void
   * @throws Exception
   */
  private function getDomainsToActivate() {
    Database::connect();

    $websites = PhishingWebsiteModel::getPhishingWebsites();

    $domainsActivated = PhishingWebsiteModel::getDomainsRegisteredInProxy();
    $domainsToActivate = [];

    foreach ($websites as $website) {
      $domain = mb_strtolower(get_hostname_from_url($website['url_protocol'] . $website['url']));

      if (!in_array($domain, $domainsActivated)) {
        $domainsToActivate[] = $domain;
      }
    }

    echo json_encode($domainsToActivate);
  }


  /**
   * Vypíše ve formátu JSON seznam (sub)domén podvodných stránek, které jsou
   * v aktuální konfiguraci Phishingatoru aktivovány, ale už se dále nevyužívají.
   *
   * @return void
   * @throws Exception
   */
  private function getDomainsToDeactivate() {
    Database::connect();

    $websites = PhishingWebsiteModel::getPhishingWebsites();

    $domainsActivated = PhishingWebsiteModel::getDomainsRegisteredInProxy();
    $domainsToDeactivate = [];

    foreach ($domainsActivated as $domainActivated) {
      $active = false;

      foreach ($websites as $website) {
        $websiteDomain = mb_strtolower(get_hostname_from_url($website['url_protocol'] . $website['url']));

        if ($websiteDomain == $domainActivated) {
          $active = true;
          break;
        }
      }

      if (!$active) {
        $domainsToDeactivate[] = $domainActivated;
      }
    }

    echo json_encode($domainsToDeactivate);
  }
}
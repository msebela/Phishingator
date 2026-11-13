<?php
  /**
   * Třída poskytuje informace o výchozích (sub)doménách přiřazených
   * dané instanci Phishingatoru a požadovaném stavu směrování (sub)domén
   * cvičných podvodných stránek pro jejich automatizovanou správu.
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
        if ($_GET['action'] == 'base-domains') {
          $this->getBaseDomains();
        }
        elseif ($_GET['action'] == 'desired-hosts') {
          $this->getDesiredHosts();
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

      if ($token != PHISHINGATOR_TOKEN) {
        Logger::error('Invalid token to get fraudulent domains list.', $token);
        $invalid = true;
      }

      if (isset($invalid)) {
        http_response_code(403);
        exit();
      }
    }


    /**
     * Vypíše ve formátu JSON seznam výchozích (sub)domén,
     * přiřazených dané instanci Phishingatoru.
     *
     * @return void
     */
    private function getBaseDomains() {
      $baseDomains = array_values(array_unique(PhishingWebsiteModel::getBaseDomains()));

      sort($baseDomains);

      echo json_encode($baseDomains);
    }


    /**
     * Vypíše ve formátu JSON seznam (sub)domén aktivních podvodných stránek,
     * které mají správně nastavené DNS záznamy a mají být směrovány
     * přes proxy na danou instanci Phishingatoru.
     *
     * @return void
     * @throws Exception
     */
    private function getDesiredHosts() {
      Database::connect();

      $websites = PhishingWebsiteModel::getPhishingWebsites();
      $desiredHosts = [];

      foreach ($websites as $website) {
        $websiteUrl = mb_strtolower($website['url_protocol'] . $website['url']);
        $domain = get_hostname_from_url($websiteUrl);

        if ($website['active'] == 1 && PhishingWebsiteModel::getPhishingWebsiteStatus($websiteUrl) != 1) {
          $desiredHosts[] = $domain;
        }
      }

      $desiredHosts = array_values(array_unique($desiredHosts));

      sort($desiredHosts);

      echo json_encode($desiredHosts);
    }
  }
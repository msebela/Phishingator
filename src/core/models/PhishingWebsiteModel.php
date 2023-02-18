<?php
  /**
   * Třída sloužící k získávání informací o podvodných webových stránkách,
   * k přidávání nových podvodných webových stránek, k úpravě těch existujících
   * a k dalším souvisejícím operacím.
   *
   * @author Martin Šebela
   */
  class PhishingWebsiteModel extends FormModel {
    /**
     * @var string      Název podvodné webové stránky.
     */
    protected $name;

    /**
     * @var string      URL podvodné webové stránky, na které bude dostupná.
     */
    protected $url;

    /**
     * @var int         ID šablony, která se na webové stránce zobrazí.
     */
    protected $idTemplate;

    /**
     * @var int         Proměnná uchovávající stav o tom, zdali je podvodná webová stránka dostupná pro ostatní
     *                  uživatele (1 pokud ano, jinak 0).
     */
    protected $active;


    /**
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function load($data) {
      parent::load($data);

      $this->active = (empty($this->active) ? 0 : 1);
    }


    /**
     * Připraví novou podvodnou webovou stránku v závislosti na vyplněných datech a vrátí ji formou pole.
     *
     * @return array                   Pole obsahující data o podvodné stránce.
     */
    private function makePhishingWebsite() {
      return [
        'name' => $this->name,
        'url' => str_replace(VAR_RECIPIENT_URL, PhishingWebsiteModel::validateRecipientUrlVar($this->url) . VAR_RECIPIENT_URL, $this->url),
        'id_template' => $this->idTemplate,
        'active' => $this->active
      ];
    }


    /**
     * Uloží do instance a zároveň vrátí (z databáze) informace o zvolené podvodné webové stránce.
     *
     * @param int $id                  ID podvodné stránky
     * @return array                   Pole obsahující informace o podvodné webové stránce.
     */
    public function getPhishingWebsite($id) {
      $this->dbRecordData = Database::querySingle('
              SELECT `id_website`, `name`, `url`, `id_template`, `active`
              FROM `phg_websites`
              WHERE `id_website` = ?
              AND `visible` = 1
      ', $id);

      $this->dbRecordData['status'] = $this->getPhishingWebsiteStatus($this->dbRecordData['url']);

      return $this->dbRecordData;
    }


    /**
     * Vrátí informace o konkrétní podvodné stránce na základě její URL adresy.
     *
     * @param string $url              URL adresa podvodné stránky (včetně protokolu)
     * @return mixed                   Pole obsahující informace o podvodné webové stránce.
     */
    public static function getPhishingWebsiteByUrl($url) {
      return Database::querySingle('
              SELECT `id_website`
              FROM `phg_websites`
              WHERE `url` = ?
              AND `visible` = 1
      ', [$url]);
    }


    /**
     * Vrátí seznam všech podvodných webových stránek z databáze.
     *
     * @return mixed                   Pole podvodných webových stránek a informace o každé z nich.
     */
    public static function getPhishingWebsites() {
      $result = Database::queryMulti('
              SELECT `id_website`, phg_websites.id_by_user, `name`, phg_websites.url, `active`, phg_websites.date_added,
              `username`,
              DATE_FORMAT(phg_websites.date_added, "%e. %c. %Y") AS date_added_formatted
              FROM `phg_websites`
              JOIN `phg_users`
              ON phg_websites.id_by_user = phg_users.id_user
              WHERE phg_websites.visible = 1
              ORDER BY `id_website` DESC
      ');

      foreach ($result as $key => $website) {
        $urlProtocol = get_protocol_from_url($website['url']);

        $result[$key]['url_protocol'] = $urlProtocol;
        $result[$key]['url_protocol_color'] = self::getColorURLProtocol($urlProtocol);
        $result[$key]['url'] = mb_substr($website['url'], mb_strlen($urlProtocol));

        $result[$key]['active_text'] = ($website['active']) ? 'ano' : 'ne';
        $result[$key]['active_color'] = ($website['active']) ? MSG_CSS_SUCCESS : MSG_CSS_ERROR;

        $result[$key]['status'] = self::getPhishingWebsiteStatus($website['url']);

        if ($result[$key]['status'] == 1) {
          $result[$key]['status_text'] = 'nedokončené směrování';
          $result[$key]['status_color'] = MSG_CSS_WARNING;
        }
        elseif ($result[$key]['status'] == 2) {
          $result[$key]['status_text'] = 'chybné DNS';
          $result[$key]['status_color'] = MSG_CSS_ERROR;
        }
      }

      return $result;
    }


    /**
     * Vrátí z databáze seznam podvodných webových stránek, které jsou skutečně dostupné
     * pro ostatní uživatele (případně v konkrétním roce).
     *
     * @param int $year                Zkoumaný rok [nepovinné]
     * @return mixed                   Pole aktivních podvodných webových stránek a informace o každé z nich.
     */
    public static function getActivePhishingWebsites($year = []) {
      $yearQuery = (!is_array($year) && is_numeric($year)) ? 'AND YEAR(`date_added`) = ?' : '';

      $result = Database::queryMulti('
              SELECT `id_website`, `name`, `url`
              FROM `phg_websites`
              WHERE `active` = 1
              AND `visible` = 1
              ' . $yearQuery . '
              ORDER BY `id_website` DESC
      ', $year);

      foreach ($result as $key => $website) {
        $result[$key]['status'] = self::getPhishingWebsiteStatus($website['url']);

        if ($result[$key]['status'] != 0) {
          unset($result[$key]);
        }
      }

      return $result;
    }


    /**
     * Vrátí počet kampaní, ke kterým je přiřazena daná podvodná webová stránka.
     *
     * @param int $id                  ID podvodné stránky
     * @return int                     Počet kampaní
     */
    public static function getCountOfUsePhishingWebsite($id) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_campaigns`
              WHERE `id_website` = ?
      ', $id);
    }


    /**
     * Ověří, zdali již v databázi existuje podvodná webová stránka s danou URL.
     *
     * @param string $url              URL podvodné webové stránky.
     * @param int $idWebsite           ID podvodné webové stránky (nepovinný parametr) pro vyloučení
     *                                 té právě upravované.
     * @return mixed                   0 pokud URL v databázi zatím neexistuje, jinak 1.
     */
    private function existsWebsiteUrl($url, $idWebsite = 0) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_websites`
              WHERE `id_website` != ?
              AND `url` = ?
              AND `visible` = 1
      ', [$idWebsite, $url]);
    }


    /**
     * Vrátí informace o konkrétní šabloně podvodné webové stránky z databáze.
     *
     * @param int $id                  ID šablony podvodné webové stránky.
     * @return mixed                   Pole s informacemi o šabloně.
     */
    public static function getPhishingWebsiteTemplate($id) {
      return Database::querySingle('
              SELECT `server_dir`
              FROM `phg_websites_templates`
              WHERE `id_website_template` = ?
              AND `visible` = 1
      ', $id);
    }


    /**
     * Vrátí seznam všech šablon podvodných webových stránek z databáze.
     *
     * @return mixed                   Pole šablon podvodných webových stránek a informace o každé z nich.
     */
    public function getPhishingWebsitesTemplates() {
      return Database::queryMulti('
              SELECT `id_website_template`, `name`
              FROM `phg_websites_templates`
              WHERE `visible` = 1
              ORDER BY `id_website_template` DESC
      ');
    }


    /**
     * Vloží do databáze novou podvodnou webovou stránku.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function insertPhishingWebsite() {
      $this->generateConfFile();

      $phishingWebsite = $this->makePhishingWebsite();

      $phishingWebsite['id_by_user'] = PermissionsModel::getUserId();
      $phishingWebsite['date_added'] = date('Y-m-d H:i:s');

      $this->isURLUnique();

      Logger::info('New phishing website added.', $phishingWebsite);

      Database::insert($this->dbTableName, $phishingWebsite);
    }


    /**
     * Upraví zvolenou podvodnou webovou stránku.
     *
     * @param int $id                  ID podvodné webové stránky
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function updatePhishingWebsite($id) {
      $phishingWebsite = $this->makePhishingWebsite();

      $this->isURLUnique($id);

      $originalWebsite = new PhishingWebsiteModel();
      $originalWebsite = $originalWebsite->getPhishingWebsite($id);

      // Pokud byla změněna URL adresa (stačí protokol) nebo šablona podvodné stránky,
      // je nutné deaktivovat původní a vytvořit nový VirtualHost.
      if ($originalWebsite['url'] != $this->url || $originalWebsite['id_template'] != $this->idTemplate) {
        // Deaktivace původního VirtualHost.
        $this->deactivateConfFile(get_hostname_from_url($originalWebsite['url']));

        // Pokud má být podvodná stránka aktivována, je nutné vygenerovat nový konfigurační soubor se správným URL.
        if ($this->active) {
          $this->generateConfFile();
        }
      }

      // Pokud se původní hodnota "active" liší od té aktuálně nastavené.
      if ($originalWebsite['active'] != $this->active) {
        if ($this->active) {
          $this->generateConfFile();
        }
        else {
          $this->deactivateConfFile();
        }
      }

      Logger::info('Phishing website modified.', $phishingWebsite);

      Database::update(
        $this->dbTableName,
        $phishingWebsite,
        'WHERE `id_website` = ? AND `visible` = 1',
        $id
      );
    }


    /**
     * Odstraní (resp. deaktivuje) zvolenou podvodnou webovou stránku z databáze.
     *
     * @param int $id                  ID podvodné webové stránky
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function deletePhishingWebsite($id) {
      if ($this->getCountOfUsePhishingWebsite($id) != 0) {
        throw new UserError(
          'Nelze smazat podvodnou stránku, která je svázána s nějakou existující kampaní.', MSG_ERROR
        );
      }

      $website = $this->getPhishingWebsite($id);
      $this->deactivateConfFile(get_hostname_from_url($website['url']));

      $result = Database::update(
        'phg_websites',
        ['visible' => 0],
        'WHERE `id_website` = ? AND `visible` = 1',
        $id
      );

      if ($result == 0) {
        Logger::info('Attempt to delete a non-existent phishing website.', $id);

        throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
      }

      Logger::info('Phishing website deleted.', $id);
    }


    /**
     * Vrátí screenshot podvodné stránky.
     *
     * @param int $id                  ID podvodné webové stránky
     */
    public function getPhishingWebsiteScreenshot($id) {
      $website = $this->getPhishingWebsite($id);

      if ($website != null) {
        $template = self::getPhishingWebsiteTemplate($website['id_template']);

        $name = $template['server_dir'] . '/' . PHISHING_WEBSITE_SCREENSHOT_FILENAME;
        $fp = fopen($name, 'rb');

        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($name));

        fpassthru($fp);
        exit();
      }
    }


    /**
     * Vrátí CSS třídy k odlišení protokolu podvodné webové stránky.
     *
     * @param string $urlProtocol      Protokol (HTTPS/HTTP) podvodné webové stránky.
     * @return string                  Název CSS třídy.
     */
    public static function getColorURLProtocol($urlProtocol) {
      $color = MSG_CSS_WARNING;

      if ($urlProtocol == 'https') {
        $color = MSG_CSS_SUCCESS;
      }
      elseif ($urlProtocol == 'http') {
        $color = MSG_CSS_ERROR;
      }

      return $color;
    }


    /**
     * Zkopíruje šablonu konfiguračního souboru podvodné stránky do zadaného adresáře.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function copyConfFileTemplate() {
      if (!file_exists(PHISHING_WEBSITE_TEMPLATE_CONF_FILE)) {
        Logger::error(
          'Unable to find sample site configuration template on the server.',
          PHISHING_WEBSITE_TEMPLATE_CONF_FILE
        );

        throw new UserError(
          'Nepodařilo se nalézt soubor se šablonou pro konfiguraci podvodné stránky.', MSG_ERROR
        );
      }

      if (!is_writable(PHISHING_WEBSITE_APACHE_SITES_DIR)) {
        Logger::error(
          'The destination directory for inserting the sample phishing website configuration file is not writable.',
          PHISHING_WEBSITE_APACHE_SITES_DIR
        );

        throw new UserError(
          'Adresář, do kterého se má vložit konfigurační soubor, není zapisovatelný.', MSG_ERROR
        );
      }

      $confFilepath = $this->getConfFilepath();

      if (!copy(PHISHING_WEBSITE_TEMPLATE_CONF_FILE, $confFilepath)) {
        Logger::error(
          'Failed to create a copy of the sample site configuration template.',
          $confFilepath
        );

        throw new UserError(
          'Nepodařilo se připravit soubor pro konfiguraci podvodné stránky.', MSG_ERROR
        );
      }
    }


    /**
     * Vrátí cestu včetně názvu souboru, kde bude uložen konfigurační soubor podvodné stránky.
     *
     * @return string                  Cesta včetně názvu souboru
     */
    private function getConfFilepath() {
      return PHISHING_WEBSITE_APACHE_SITES_DIR . get_hostname_from_url($this->url) . '.conf.new';
    }


    /**
     * Vrátí pole názvů proměnných, které se budou nahrazovat v šabloně konfiguračního souboru za skutečné hodnoty.
     *
     * @return string[]                Pole proměnných
     */
    private function getConfFileVarsToReplace() {
      return [
        'PHISHINGATOR_SERVER_PORT', 'PHISHINGATOR_SERVER_NAME', 'PHISHINGATOR_SERVER_ADMIN',
        'PHISHINGATOR_DOCUMENT_ROOT', 'PHISHINGATOR_SERVER_ALIAS', 'PHISHINGATOR_WEBSITE_PREPENDER'
      ];
    }


    /**
     * Vytvoří nový konfigurační soubor pro podvodnou stránku podle jejího nastavení.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function generateConfFile() {
      $this->copyConfFileTemplate();

      $apacheConfigFileName = $this->getConfFilepath();
      $apacheConfig = file_get_contents($apacheConfigFileName);

      if ($apacheConfig) {
        $template = $this->getPhishingWebsiteTemplate($this->idTemplate);

        if ($template) {
          // Při neexistenci proxy...
          // $port = (get_protocol_from_url($this->url) == 'https') ? 443 : 80;
          $port = 80;

          // Vytvoření aliasu, pokud je součástí URL adresy i cesta (názvy adresářů).
          $urlPath = parse_url($this->url, PHP_URL_PATH);
          $urlAlias = !empty($urlPath) ? $urlPath : '.';

          // Hodnoty za proměnné pro šablonu konfiguračního souboru podvodné stránky (ve správném pořadí).
          $values = [
            $port, get_hostname_from_url($this->url), PHISHING_WEBSITE_SERVER_ADMIN,
            $template['server_dir'], $urlAlias, PHISHING_WEBSITE_PREPENDER
          ];

          $apacheConfig = str_replace($this->getConfFileVarsToReplace(), $values, $apacheConfig);

          if (!file_put_contents($apacheConfigFileName, $apacheConfig)) {
            Logger::error('Failed to create a phishing website configuration file.', $apacheConfigFileName);

            throw new UserError('Nepodařilo se připravit soubor pro konfiguraci podvodné stránky.', MSG_ERROR);
          }
        }
        else {
          Logger::error('A non-existent phishing website template has been selected.', $apacheConfigFileName);

          throw new UserError('Zvolená šablona neexistuje.', MSG_ERROR);
        }
      }
      else {
        Logger::error('Failed to find sample phishing website configuration on the server.', $apacheConfigFileName);

        throw new UserError('Nenalezen soubor se šablonou pro konfiguraci podvodné stránky.', MSG_ERROR);
      }
    }


    /**
     * Deaktivuje konfigurační soubor (VirtualHost) podvodné stránky v Apache.
     *
     * @param string $hostname         Doména podvodné stránky (bez protokolu) [nepovinné].
     * @return void
     */
    public function deactivateConfFile($hostname = null) {
      $hostname = ($hostname != null) ? $hostname : get_hostname_from_url($this->url);
      $websiteConfigName = PHISHING_WEBSITE_APACHE_SITES_DIR . $hostname;

      $newConfigFilename = $websiteConfigName . '.conf.new';
      $configFilename = $websiteConfigName . '.conf';
      $deleteConfigFilename = $websiteConfigName . '.conf.delete';

      if (file_exists($newConfigFilename)) {
        rename($newConfigFilename, $deleteConfigFilename);
      }

      if (file_exists($configFilename)) {
        rename($configFilename, $deleteConfigFilename);
      }
    }


    /**
     * Vygeneruje a vrátí URL s parametry pro zobrazení náhledu podvodné stránky.
     *
     * @param int $idWebsite           ID podvodné stránky
     * @param int $idUser              ID uživatele, který chce zobrazit náhled podvodné stránky
     * @return string|null             URL pro náhled podvodné stránky nebo NULL
     */
    public static function getPreviewLink($idWebsite, $idUser) {
      $previewLink = null;

      // Zjištění, zdali nejsou v databázi pro daného uživatele ještě nějaké aktivní tickety pro náhled podvodné stránky.
      $access = Database::querySingle(
        'SELECT `hash` FROM `phg_websites_preview` WHERE `id_website` = ? AND `id_user` = ? AND `active_to` > now()',
        [$idWebsite, $idUser]
      );

      // Zjištění informací o uživateli, který náhled podvodné stránky požaduje.
      $usersModel = new UsersModel();
      $user = $usersModel->getUser($idUser);

      // Zjištění informací o podvodné stránce, jejíž náhled má být zobrazen.
      $model = new PhishingWebsiteModel();
      $website = $model->getPhishingWebsite($idWebsite);

      // Pokud podvodná stránka existuje a je nastavena jako skutečně aktivní (v Apache).
      if ($user != null && $website != null && $website['active'] == 1) {
        // Pokud v databázi nejsou aktivní žádné tickety pro přístup na náhled podvodné stránky...
        if (empty($access)) {
          $activeSince = date('Y-m-d H:i:s');
          $activeTo = date('Y-m-d H:i:s', strtotime('+1 min'));
          $hash = bin2hex(openssl_random_pseudo_bytes(PHISHING_WEBSITE_PREVIEW_HASH_BYTES));

          $access = [
            'id_website' => $idWebsite,
            'id_user' => $idUser,
            'hash' => $hash,
            'active_since' => $activeSince,
            'active_to' => $activeTo
          ];

          Database::insert('phg_websites_preview', $access);
        }
        else {
          // Jinak použít již existující...
          $hash = $access['hash'];
        }

        $mark = (parse_url($website['url'], PHP_URL_QUERY) == null) ? '?' : '&';

        $previewLink = $website['url'] . $mark . WebsitePrependerModel::makeUserWebsiteId(0, $user['url']) . '&' . ACT_PREVIEW . '=' . $hash;
      }

      return $previewLink;
    }


    /**
     * Vrátí aktuální stav přesměrování podvodné domény na server, kde běží instance Phishingatoru.
     *
     * @param string $url              URL adresa podvodné stránky (bez protokolu)
     * @return int                     Aktuální stav
     */
    private static function getPhishingWebsiteStatus($url) {
      $status = 0;

      $websiteHostname = get_hostname_from_url($url);
      $instanceHost = gethostbyname(get_hostname_from_url(getenv('WEB_URL')));

      // U podvodné domény chybí v DNS záznam typu A směrovaný na Phishingator.
      if (gethostbyname($websiteHostname) != $instanceHost && gethostbyname(get_domain_from_url($url)) != $instanceHost) {
        $status = 2;
      }
      // Chybí záznam o doméně v proxy Phishingatoru.
      elseif (!self::isDomainRegisteredInProxy($websiteHostname)) {
        $status = 1;
      }

      return $status;
    }


    /**
     * Ověří, zdali je doména zaregistrována v proxy Phishingatoru.
     *
     * @param string $website          Doména (bez protokolu)
     * @return bool                    TRUE, pokud byla nalezena v konfiguraci proxy, jinak FALSE
     */
    public static function isDomainRegisteredInProxy($website) {
      $registered = false;
      $proxyDomains = self::getDomainsRegisteredInProxy();

      if (!empty($proxyDomains)) {
        $domain = get_domain_from_url('https://' . $website);
        $subdomain = mb_substr($website, 0, -mb_strlen($domain) - 1);

        foreach ($proxyDomains as $proxyDomain) {
          if (!empty($subdomain)) {
            // Konkrétní subdoména v konfiguraci proxy.
            if ($proxyDomain == $subdomain . '.' . $domain) {
              $registered = true;
            }
            else {
              // Subdoména jako regulární výraz u domény v konfiguraci proxy.
              preg_match('/' . PHISHING_WEBSITE_PROXY_SUBDOMAIN_RULE . '\.' . $domain . '/', $proxyDomain, $matches);

              if (isset($matches[1])) {
                preg_match('/' . $matches[1] . '/', $subdomain, $subdomainMatches);

                if (isset($subdomainMatches[0]) && mb_strlen($subdomainMatches[0]) == mb_strlen($subdomain)) {
                  $registered = true;
                }
              }
            }
          }
          // Konkrétní doména v konfiguraci proxy.
          elseif ($proxyDomain == $domain) {
            $registered = true;
          }

          if ($registered) {
            break;
          }
        }
      }

      return $registered;
    }


    /**
     * Vrátí pole všech (sub)domén, které jsou registrované v proxy Phishingatoru
     * a které mohou být použity jako podvodné domény ve phishingových kampaních.
     *
     * @param bool $proxyRulesNames    TRUE, pokud mají být zachovány názvy pravidel z proxy Phishingatoru (výchozí)
     * @return array|false|string[]    Pole podvodných domén a subdomén
     */
    public static function getDomainsRegisteredInProxy($proxyRulesNames = true) {
      $proxyDomains = [];

      if (getenv('FRAUDULENT_HOSTS') !== null) {
        $proxyDomains = explode(',', str_replace('`', '', getenv('FRAUDULENT_HOSTS')));

        if (!$proxyRulesNames) {
          $proxyDomains = preg_replace('/' . PHISHING_WEBSITE_PROXY_SUBDOMAIN_RULE . '/', '$1', $proxyDomains);
        }
      }

      return $proxyDomains;
    }


    /**
     * Ověří, jakým způsobem je v URL adrese zapsána proměnná pro identifikaci uživatele na podvodné stránce
     * a pokud před proměnnou chybí symbol pro GET argument, doplní jej.
     *
     * @param string $websiteUrl       URL adresa podvodné stránky včetně proměnné pro identifikaci uživatele
     * @return string                  Ověřená URL adresa, kde je proměnná zvalidovaná jako GET argument
     */
    public static function validateRecipientUrlVar($websiteUrl) {
      // Odstranění hostname a protokolu z URL adresy.
      $hostnamePosition = mb_strpos($websiteUrl, get_hostname_from_url($websiteUrl));
      $websiteUrl = mb_substr($websiteUrl, $hostnamePosition + mb_strlen(get_hostname_from_url($websiteUrl)));

      // Zjištění symbolu, který je těsně před proměnnou.
      $varPosition = mb_strpos($websiteUrl, VAR_RECIPIENT_URL);
      $symbolBeforeVar = $websiteUrl[$varPosition - 1];

      $websiteUrlArgs = parse_url($websiteUrl, PHP_URL_QUERY);

      if ($symbolBeforeVar == '=' || $symbolBeforeVar == '?' || $symbolBeforeVar == '&') {
        $mark = '';
      }
      elseif ($websiteUrlArgs == null || $symbolBeforeVar == '/') {
        $mark = '?';
      }
      else {
        $mark = '&';
      }

      return $mark;
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function validateData() {
      $this->isNameEmpty();
      $this->isNameTooLong();

      $this->isURLEmpty();
      $this->isURLTooLong();
      $this->isURLValid();
      $this->isURLValidDNSRecord();
      $this->isURLPathValid();
      $this->isURLArgValid();

      $this->containURLUserIdentifierVariable();
      $this->checkCountURLUserIdentifierVariable();
      $this->isURLUserIdentifierVariableValid();

      $this->isTemplateEmpty();
      $this->existTemplate();

      $this->isWebsiteDeactivable();
    }


    /**
     * Ověří, zdali byl vyplněn název podvodné webové stránky.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isNameEmpty() {
      if (empty($this->name)) {
        throw new UserError('Není vyplněn název podvodné stránky.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný název podvodné webové stránky není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isNameTooLong() {
      if (mb_strlen($this->name) > $this->inputsMaxLengths['name']) {
        throw new UserError('Název podvodné stránky je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byla vyplněna URL adresa podvodné webové stránky.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLEmpty() {
      if (empty($this->url)) {
        throw new UserError('Není vyplněna URL adresa podvodné stránky.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaná URL adresa podvodné webové stránky není příliš dlouhá.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLTooLong() {
      if (mb_strlen($this->url) > $this->inputsMaxLengths['url']) {
        throw new UserError('URL adresa podvodné stránky je příliš dlouhá.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je zadaná URL adresa podvodné webové stránky validní.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLValid() {
      if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
        throw new UserError('URL adresa podvodné stránky je v nesprávném formátu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je u zadané (sub)domény v DNS nasměrován A záznam na instanci Phishingatoru.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLValidDNSRecord() {
      if (gethostbyname(get_hostname_from_url($this->url)) != gethostbyname(get_hostname_from_url(getenv('WEB_URL')))) {
        throw new UserError('U zadané domény (popř. subdomény) není v DNS nasměrován záznam typu A na IP adresu serveru, kde běží Phishingator.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaná cesta v URL adrese podvodné stránky neobsahuje nepovolené znaky a výrazy.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLPathValid() {
      $path = parse_url(str_replace(VAR_RECIPIENT_URL, 'var', $this->url), PHP_URL_PATH);

      if (!empty($path) && (preg_match('/[^A-Za-z0-9\/._-]/', $path) || strpos($path, './') !== false)) {
        throw new UserError('Adresářová cesta v URL adrese podvodné stránky obsahuje nepovolené znaky nebo výrazy.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali GET argumenty u zadané URL adresy podvodné stránky neobsahují nepovolené znaky a výrazy.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLArgValid() {
      $query = parse_url(str_replace(VAR_RECIPIENT_URL, 'var', $this->url), PHP_URL_QUERY);

      if (!empty($query) && preg_match('/[^A-Za-z0-9._?=&-]/', $query)) {
        throw new UserError('Argumenty v URL adrese podvodné stránky obsahují nepovolené znaky.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je v zadané URL adrese obsažena proměnná, která bude obsahovat identifikátor uživatele.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function containURLUserIdentifierVariable() {
      if (mb_strpos($this->url, VAR_RECIPIENT_URL) === false) {
        throw new UserError(
          'V argumentech URL adresy chybí použití proměnné "' . VAR_RECIPIENT_URL . '" pro identifikaci uživatele.',
          MSG_ERROR
        );
      }
    }


    /**
     * Ověří, zdali se v zadané URL adrese vícekrát nevyskytuje proměnná, která bude obsahovat identifikátor uživatele.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function checkCountURLUserIdentifierVariable() {
      if (substr_count($this->url, VAR_RECIPIENT_URL) > 1) {
        throw new UserError(
          'V argumentech URL adresy se nemůže proměnná "' . VAR_RECIPIENT_URL . '" vyskytovat vícekrát.',
          MSG_ERROR
        );
      }
    }


    /**
     * Ověří, zdali je v zadané URL adrese správně umístěna proměnná, která bude obsahovat identifikátor uživatele.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLUserIdentifierVariableValid() {
      $variablePosition = mb_strpos($this->url, VAR_RECIPIENT_URL) + mb_strlen(VAR_RECIPIENT_URL);

      if (isset($this->url[$variablePosition])) {
        if ($this->url[$variablePosition] != '&') {
          throw new UserError(
            'Za proměnnou "' . VAR_RECIPIENT_URL . '" může následovat pouze další argument (tj. znak &), nebo nic dalšího.',
            MSG_ERROR
          );
        }
      }
    }


    /**
     * Ověří, zdali zadanou URL adresu nepoužívá jiná podvodná webová stránka (tzn. jestli je unikátní).
     *
     * @param int $idWebsite           ID podvodné stránky
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isURLUnique($idWebsite = 0) {
      if ($this->existsWebsiteUrl($this->url, $idWebsite) != 0) {
        throw new UserError('Zadanou URL adresu již používá jiná vedená podvodná stránka.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byla vybrána šablona podvodné webové stránky.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isTemplateEmpty() {
      if (empty($this->idTemplate) || !is_numeric($this->idTemplate)) {
        throw new UserError('Není vybrána šablona stránky.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali vybraná šablona podvodné webové stránky existuje.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function existTemplate() {
      if (empty($this->getPhishingWebsiteTemplate($this->idTemplate))) {
        throw new UserError('Vybraná šablona stránky neexistuje.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali lze zvolenou podvodnou stránku deaktivovat, aniž by byly ovlivněny některé existující kampaně.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isWebsiteDeactivable() {
      if ($this->active == 0 && !empty($this->dbRecordData['id_website'])
          && self::getCountOfUsePhishingWebsite($this->dbRecordData['id_website']) != 0) {
        throw new UserError(
          'Nelze deaktivovat podvodnou stránku, která je využívána v nějaké existující kampani.', MSG_ERROR
        );
      }
    }
  }

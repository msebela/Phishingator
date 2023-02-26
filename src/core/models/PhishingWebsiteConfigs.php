<?php
  /**
   * Třída sloužící pro vytváření a správu konfiguračních
   * souborů podvodných stránek na webovém serveru.
   *
   * @author Martin Šebela
   */
  class PhishingWebsiteConfigs {
    /**
     * Vrátí cestu včetně názvu souboru, kde bude uložen konfigurační soubor podvodné stránky.
     *
     * @param string $url              URL adresa podvodné stránky
     * @return string                  Cesta včetně názvu souboru
     */
    private static function getConfFilepath($url) {
      return PHISHING_WEBSITE_CONF_DIR . get_hostname_from_url($url) . self::getPhishingWebsitePath($url) . PHISHING_WEBSITE_CONF_EXT_NEW;
    }


    /**
     * Vrátí adresářovou cestu (tj. část za doménou) u dané phishingové stránky.
     * Cesta je zbavena všech speciálních znaků, které jsou nahrazeny podtržítkem.
     *
     * @param string $url              URL podvodné stránky
     * @return string                  Adresářová cesta bez speciálních znaků
     */
    private static function getPhishingWebsitePath($url) {
      $urlPath = rtrim(parse_url(PhishingWebsiteModel::makeWebsiteUrl($url), PHP_URL_PATH), '/');

      $urlPath = str_replace("'", "", $urlPath);
      $urlPath = preg_replace('/[^\p{L}\p{N}]/u', '_', $urlPath);

      return (!empty($urlPath)) ? $urlPath : '';
    }


    /**
     * Zkopíruje šablonu konfiguračního souboru podvodné stránky do zadaného adresáře.
     *
     * @param string $url              URL adresa podvodné stránky
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private static function copyConfFileTemplate($url) {
      if (!file_exists(PHISHING_WEBSITE_TEMPLATE_CONF_FILE)) {
        Logger::error('Unable to find sample site configuration template on the server.', PHISHING_WEBSITE_TEMPLATE_CONF_FILE);

        throw new UserError('Nepodařilo se nalézt soubor se šablonou pro konfiguraci podvodné stránky.', MSG_ERROR);
      }

      if (!is_writable(PHISHING_WEBSITE_CONF_DIR)) {
        Logger::error('The destination directory for inserting the sample phishing website configuration file is not writable.', PHISHING_WEBSITE_CONF_DIR);

        throw new UserError('Adresář, do kterého se má vložit konfigurační soubor, není zapisovatelný.', MSG_ERROR);
      }

      $configFilepath = self::getConfFilepath($url);

      if (!copy(PHISHING_WEBSITE_TEMPLATE_CONF_FILE, $configFilepath)) {
        Logger::error('Failed to create a copy of the sample site configuration template.', $configFilepath);

        throw new UserError('Nepodařilo se připravit soubor pro konfiguraci podvodné stránky.', MSG_ERROR);
      }
    }


    /**
     * Vytvoří nový konfigurační soubor pro podvodnou stránku podle jejího nastavení.
     *
     * @param string $url              URL adresa podvodné stránky
     * @param int $idTemplate          ID šablony podvodné stránky
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public static function generateConfFile($url, $idTemplate) {
      self::copyConfFileTemplate($url);

      $configFilepath = self::getConfFilepath($url);
      $config = file_get_contents($configFilepath);

      if ($config) {
        $template = PhishingWebsiteModel::getPhishingWebsiteTemplate($idTemplate);

        if ($template) {
          // Při neexistenci proxy...
          // $port = (get_protocol_from_url($url) == 'https') ? 443 : 80;
          $port = 80;

          // Vytvoření aliasu, pokud je součástí URL adresy i cesta (názvy adresářů).
          $urlPath = parse_url($url, PHP_URL_PATH);
          $urlAlias = !empty($urlPath) ? $urlPath : '.';

          // Hodnoty za proměnné pro šablonu konfiguračního souboru podvodné stránky (ve správném pořadí).
          $values = [
            $port, get_hostname_from_url($url), PHISHING_WEBSITE_SERVER_ADMIN,
            $template['server_dir'], $urlAlias, PHISHING_WEBSITE_PREPENDER
          ];

          $config = str_replace(self::getConfFileVarsToReplace(), $values, $config);

          if (!file_put_contents($configFilepath, $config)) {
            Logger::error('Failed to create a phishing website configuration file.', $configFilepath);

            throw new UserError('Nepodařilo se připravit soubor pro konfiguraci podvodné stránky.', MSG_ERROR);
          }
        }
        else {
          Logger::error('A non-existent phishing website template has been selected.', $configFilepath);

          throw new UserError('Zvolená šablona neexistuje.', MSG_ERROR);
        }
      }
      else {
        Logger::error('Failed to find sample phishing website configuration on the server.', $configFilepath);

        throw new UserError('Nenalezen soubor se šablonou pro konfiguraci podvodné stránky.', MSG_ERROR);
      }
    }


    /**
     * Přejmenuje konfigurační soubor dané podvodné stránky tak, aby došlo k její deaktivaci.
     *
     * @param string $url              URL podvodné stránky
     */
    public static function deactivateConfFile($url) {
      $websiteConfigName = PHISHING_WEBSITE_CONF_DIR . get_hostname_from_url($url) . self::getPhishingWebsitePath($url);

      $newConfigFilename = $websiteConfigName . PHISHING_WEBSITE_CONF_EXT_NEW;
      $configFilename = $websiteConfigName . PHISHING_WEBSITE_CONF_EXT;
      $deleteConfigFilename = $websiteConfigName . PHISHING_WEBSITE_CONF_EXT_DEL;

      if (file_exists($newConfigFilename)) {
        rename($newConfigFilename, $deleteConfigFilename);
      }

      if (file_exists($configFilename)) {
        rename($configFilename, $deleteConfigFilename);
      }
    }


    /**
     * Zpracuje všechny vytvořené konfigurační soubory podvodných stránek
     * a buď je aktivuje, nebo deaktivuje na webovém serveru.
     */
    public static function processAllConfigs() {
      $files = scandir(PHISHING_WEBSITE_CONF_DIR);
      $changes = false;

      foreach ($files as $file) {
        if (strpos($file, PHISHING_WEBSITE_CONF_EXT) !== false) {
          $filepath = PHISHING_WEBSITE_CONF_DIR . $file;

          $serverName = self::pregMatchInFile($filepath, '/ServerName (.*)/');
          $serverNameWithProtocol = strpos($serverName, 'https') !== 0 ? 'https://' . $serverName : $serverName;

          if (filter_var($serverNameWithProtocol, FILTER_VALIDATE_URL)) {
            // Aktivace podvodné stránky v Apache.
            if (self::isConfigType($file, PHISHING_WEBSITE_CONF_EXT_NEW)) {
              $filename = self::getNewConfigName($file, PHISHING_WEBSITE_CONF_EXT_NEW);

              copy($filepath, PHISHING_WEBSITE_APACHE_DIR . $filename);
              exec('a2ensite ' . escapeshellarg($filename));
              rename($filepath, PHISHING_WEBSITE_CONF_DIR . $filename);

              echo '[new] copy(' . $filepath . ', ' . PHISHING_WEBSITE_APACHE_DIR . $filename . ')' . "\n";
              echo '[new] a2ensite ' . $serverName . "\n";
              echo '[new] rename(' . $filepath . ', ' . PHISHING_WEBSITE_CONF_DIR . $filename . ')' . "\n";

              $changes = true;
            }
            // Deaktivace podvodné stránky v Apache.
            elseif (self::isConfigType($file, PHISHING_WEBSITE_CONF_EXT_DEL)) {
              $filename = self::getNewConfigName($file, PHISHING_WEBSITE_CONF_EXT_DEL);

              exec('a2dissite ' . escapeshellarg($filename));
              unlink($filepath);

              echo '[del] a2dissite ' . $filename . "\n";
              echo '[del] unlink(' . $filepath . ')' . "\n";

              $changes = true;
            }
            // Aktivace již z dřívějška existující podvodné stránky v Apache.
            elseif (self::isConfigType($file, PHISHING_WEBSITE_CONF_EXT) && !file_exists(PHISHING_WEBSITE_APACHE_DIR . $file)) {
              copy($filepath, PHISHING_WEBSITE_APACHE_DIR . $file);
              exec('a2ensite ' . escapeshellarg($file));

              echo '[exg] copy(' . $filepath . ', ' . PHISHING_WEBSITE_APACHE_DIR . $file . ')' . "\n";
              echo '[exg] a2ensite ' . $file . "\n";

              $changes = true;
            }
          }
        }
      }

      if ($changes) {
        exec('apachectl graceful');

        echo 'apachectl graceful' . "\n";
      }
    }


    /**
     * Vrátí pole názvů proměnných, které se budou nahrazovat v šabloně konfiguračního souboru za skutečné hodnoty.
     *
     * @return string[]                Pole proměnných
     */
    private static function getConfFileVarsToReplace() {
      return [
        'PHISHINGATOR_SERVER_PORT', 'PHISHINGATOR_SERVER_NAME', 'PHISHINGATOR_SERVER_ADMIN',
        'PHISHINGATOR_DOCUMENT_ROOT', 'PHISHINGATOR_SERVER_ALIAS', 'PHISHINGATOR_WEBSITE_PREPENDER'
      ];
    }


    /**
     * Vrátí nový název konfiguračního souboru.
     *
     * @param string $filename         Název souboru
     * @param string $currentExt       Aktuální přípona souboru
     * @return string                  Nový název souboru včetně nové přípony
     */
    private static function getNewConfigName($filename, $currentExt) {
      return mb_substr($filename, 0, mb_strlen($filename) - mb_strlen($currentExt)) . PHISHING_WEBSITE_CONF_EXT;
    }


    /**
     * Ověří, zdali přípona (typ) konfiguračního souboru odpovídá té předpokládané.
     *
     * @param string $filename         Název souboru
     * @param string $extension        Očekávaná přípona souboru
     * @return bool
     */
    private static function isConfigType($filename, $extension) {
      return mb_substr($filename, -mb_strlen($extension)) == $extension;
    }


    /**
     * Vyhledá v souboru řetězce, které odpovídají danému regulárnímu výrazu.
     *
     * @param string $file             Cesta k souboru
     * @param string $regex            Regulární výraz
     * @param int $matchToReturn       Který z výsledků má být vrácen na výstupu
     * @return string                  Vrácený výsledek, který odpovídá regulárnímu výrazu
     */
    private static function pregMatchInFile($file, $regex, $matchToReturn = 1) {
      $data = '';

      foreach (file($file) as $line) {
        preg_match($regex, $line, $matches);

        if (isset($matches[$matchToReturn])) {
          $data = $matches[$matchToReturn];
          break;
        }
      }

      return $data;
    }
  }
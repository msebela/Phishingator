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
     * @param string $extension        Přípona konfiguračního souboru (nepovinné)
     * @return string                  Cesta včetně názvu souboru
     */
    public static function getConfigPath($url, $extension = null) {
      return PHISHING_WEBSITE_CONF_DIR . get_hostname_from_url($url) . (($extension == null) ? PHISHING_WEBSITE_CONF_EXT : $extension);
    }


    /**
     * Ověří, zdali konfigurační soubor pro podvodnou stránku není ve stavu probíhající úpravy,
     * a je tak možné s podvodnou stránkou pracovat.
     *
     * @param string $url              URL adresa podvodné stránky
     * @param bool $withException      TRUE, pokud se má vrátit výjimka při nepřipraveném konfiguračním souboru, jinak FALSE (výchozí)
     * @return bool                    TRUE pokud je konfigurační soubor připraven, jinak FALSE
     * @throws UserError
     */
    public static function isConfigReady($url, $withException = false) {
      $configReady = !file_exists(self::getConfigPath($url, PHISHING_WEBSITE_CONF_EXT_NEW)) &&
        !file_exists(self::getConfigPath($url, PHISHING_WEBSITE_CONF_EXT_DEL));

      if ($withException) {
        if (!$configReady) {
          Logger::warning('Changing the phishing website configuration during a change in progress.', $url);

          throw new UserError('Právě probíhá změna konfigurace podvodné stránky, opakujte, prosím, akci o několik minut později.', MSG_WARNING);
        }
      }
      else {
        $configReady = $configReady && file_exists(self::getConfigPath($url));
      }

      return $configReady;
    }


    /**
     * Zkopíruje šablonu konfiguračního souboru podvodné stránky do zadaného adresáře.
     *
     * @param string $url              URL adresa podvodné stránky
     * @return void
     * @throws UserError
     */
    private static function copyConfigTemplate($url) {
      if (!file_exists(PHISHING_WEBSITE_TEMPLATE_CONF_FILE)) {
        Logger::error('Unable to find sample site configuration template on the server.', PHISHING_WEBSITE_TEMPLATE_CONF_FILE);

        throw new UserError('Nepodařilo se nalézt soubor se šablonou pro konfiguraci podvodné stránky.', MSG_ERROR);
      }

      if (!is_writable(PHISHING_WEBSITE_CONF_DIR)) {
        Logger::error('The destination directory for inserting the sample phishing website configuration file is not writable.', PHISHING_WEBSITE_CONF_DIR);

        throw new UserError('Adresář, do kterého se má vložit konfigurační soubor, není zapisovatelný.', MSG_ERROR);
      }

      $configFilepath = self::getConfigPath($url, PHISHING_WEBSITE_CONF_EXT_NEW);

      if (!copy(PHISHING_WEBSITE_TEMPLATE_CONF_FILE, $configFilepath)) {
        Logger::error('Failed to create a copy of the sample site configuration template.', $configFilepath);

        throw new UserError('Nepodařilo se připravit soubor pro konfiguraci podvodné stránky.', MSG_ERROR);
      }
    }


    /**
     * Vytvoří nový konfigurační soubor pro podvodnou stránku.
     *
     * @param string $url              URL adresa podvodné stránky
     * @param int $idTemplate          ID šablony podvodné stránky
     * @return void
     * @throws UserError
     */
    public static function createNewConfig($url, $idTemplate) {
      self::isConfigReady($url, true);
      self::copyConfigTemplate($url);

      $configFilepath = self::getConfigPath($url, PHISHING_WEBSITE_CONF_EXT_NEW);
      $config = file_get_contents($configFilepath);

      if ($config) {
        $template = PhishingWebsiteModel::getPhishingWebsiteTemplate($idTemplate);

        if ($template) {
          // $port = (get_protocol_from_url($url) == 'https') ? 443 : 80;  // Při neexistenci proxy.
          $port = 80;

          // Hodnoty za proměnné pro šablonu konfiguračního souboru podvodné stránky (ve správném pořadí).
          $values = [
            $port, get_hostname_from_url($url), $template['server_dir'],
            self::getUrlAlias($url), PHISHING_WEBSITE_PREPENDER
          ];

          $config = str_replace(self::getConfigVarsToReplace(), $values, $config);

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
     * Upraví konfigurační soubor podvodné stránky.
     *
     * @param string $action           Typ úpravy - přídání/úprava/smazání z konfiguračníhou souboru
     * @param string $url              Nová URL adresa podvodné stránky
     * @param int $idTemplate          Nové ID šablony podvodné stránky
     * @param string $previousUrl      Původní URL adresa podvodné stránky (nepovinné)
     * @param int $previousIdTemplate  Původní ID šablony podvodné stránky (nepovinné)
     * @return void
     * @throws UserError
     */
    public static function editConfig($action, $url, $idTemplate, $previousUrl = false, $previousIdTemplate = false) {
      self::isConfigReady($url, true);

      $configFilepath = self::getConfigPath($url);

      if (file_exists($configFilepath)) {
        $config = file_get_contents($configFilepath);

        if ($previousIdTemplate) {
          $previousTemplate = PhishingWebsiteModel::getPhishingWebsiteTemplate($previousIdTemplate);
        }

        $newTemplate = PhishingWebsiteModel::getPhishingWebsiteTemplate($idTemplate);

        if ($newTemplate) {
          // Vytvoření aliasu, pokud je součástí URL adresy i cesta (názvy adresářů).
          $urlAlias = self::getUrlAlias($url);

          // Pokud se nejedná o alias, je třeba změnit DocumentRoot.
          if ($action == ACT_EDIT && $urlAlias == '/') {
            $config = preg_replace(
              '/DocumentRoot .*/',
              'DocumentRoot ' . $newTemplate['server_dir'],
              $config
            );
          }

          // Úprava existujícího aliasu.
          if ($action == ACT_EDIT && $previousUrl && $previousTemplate) {
            $previousUrlAlias = self::getUrlAlias($previousUrl);

            $config = str_replace(
              self::getAliasConfigPart($previousUrlAlias, $previousTemplate['server_dir']),
              self::getAliasConfigPart($urlAlias, $newTemplate['server_dir']),
              $config
            );
          }

          // Přidání nového aliasu.
          elseif ($action == ACT_NEW) {
            $config = str_replace(
              PHISHING_WEBSITE_ANOTHER_ALIAS,
              trim(self::getAliasConfigPart($urlAlias, $newTemplate['server_dir'], true)),
              $config
            );
          }

          // Odstranění existujícího aliasu.
          elseif ($action == ACT_DEL) {
            $config = str_replace(
              self::getAliasConfigPart($urlAlias, $newTemplate['server_dir']), '', $config
            );
          }

          // Uložit jako nový konfigurační soubor, který se bude muset aktivovat.
          if (!file_put_contents(self::getConfigPath($url, PHISHING_WEBSITE_CONF_EXT_NEW), $config)) {
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
        Logger::error('Failed to find phishing website configuration file on the server.', $configFilepath);

        throw new UserError('Nenalezen soubor s konfigurací podvodné stránky.', MSG_ERROR);
      }
    }


    /**
     * Přejmenuje konfigurační soubor dané podvodné stránky tak,
     * aby došlo k její deaktivaci a odstranění.
     *
     * @param string $url              URL podvodné stránky
     * @return void
     */
    public static function removeConfig($url) {
      $websiteConfigPath = PHISHING_WEBSITE_CONF_DIR . get_hostname_from_url($url);

      if (file_exists($websiteConfigPath . PHISHING_WEBSITE_CONF_EXT)) {
        rename($websiteConfigPath . PHISHING_WEBSITE_CONF_EXT, $websiteConfigPath . PHISHING_WEBSITE_CONF_EXT_DEL);
      }
    }


    /**
     * Zpracuje všechny vytvořené konfigurační soubory podvodných stránek
     * a buď je aktivuje, nebo deaktivuje na webovém serveru.
     *
     * @return void
     */
    public static function processAllConfigs() {
      $files = scandir(PHISHING_WEBSITE_CONF_DIR);
      $changes = false;

      foreach ($files as $file) {
        if (strpos($file, PHISHING_WEBSITE_CONF_EXT) !== false) {
          $filepath = PHISHING_WEBSITE_CONF_DIR . $file;

          $serverName = self::pregMatchInFile($filepath, '/ServerName (.*)/');
          $serverNameWithProtocol = (strpos($serverName, 'https') !== 0 ? 'https://' : '') . $serverName;

          if (filter_var($serverNameWithProtocol, FILTER_VALIDATE_URL)) {
            // Aktivace podvodné stránky.
            if (self::isConfigType($file, PHISHING_WEBSITE_CONF_EXT_NEW)) {
              $filepathNewConfig = PHISHING_WEBSITE_APACHE_DIR . $serverName . PHISHING_WEBSITE_CONF_EXT;
              $filepathAppliedConfig = PHISHING_WEBSITE_CONF_DIR . $serverName . PHISHING_WEBSITE_CONF_EXT;

              copy($filepath, $filepathNewConfig);
              exec('a2ensite ' . escapeshellarg($serverName));
              rename($filepath, $filepathAppliedConfig);

              Logger::info('New phishing website activated in Apache.', [$filepath, $filepathNewConfig, $filepathAppliedConfig, $serverName]);

              $changes = true;
            }

            // Deaktivace podvodné stránky.
            elseif (self::isConfigType($file, PHISHING_WEBSITE_CONF_EXT_DEL)) {
              exec('a2dissite ' . escapeshellarg($serverName));
              unlink($filepath);

              Logger::info('Phishing website deactivated in Apache.', [$filepath, $serverName]);

              $changes = true;
            }

            // Aktivace již z dřívějška existující podvodné stránky.
            elseif (self::isConfigType($file, PHISHING_WEBSITE_CONF_EXT) && !file_exists(PHISHING_WEBSITE_APACHE_DIR . $file)) {
              $filepathNewConfig = PHISHING_WEBSITE_APACHE_DIR . $serverName . PHISHING_WEBSITE_CONF_EXT;

              copy($filepath, $filepathNewConfig);
              exec('a2ensite ' . escapeshellarg($serverName));

              Logger::info('Existing phishing website activated in Apache.', [$filepath, $filepathNewConfig, $serverName]);

              $changes = true;
            }
          }
        }
      }

      if ($changes) {
        exec('apachectl graceful');

        Logger::info('Apache configuration reloaded.');
      }
    }


    /**
     * Vrátí část konfiguračního souboru pro vytvoření nového aliasu.
     *
     * @param string $websiteAlias     Alias podvodné stránky
     * @param string $templateDir      Adresář se šablonou podvodné stránky
     * @param bool $nextAliasVar       TRUE pokud má být na výstup přidána i proměnná pro další alias, jinak FALSE (výchozí)
     * @return string                  Část konfiguračního souboru s novým aliasem
     */
    private static function getAliasConfigPart($websiteAlias, $templateDir, $nextAliasVar = false) {
      return '    Alias "' . $websiteAlias . '" ' . $templateDir . "\n".
             '    <Directory "' . $templateDir . '">' . "\n".
             '        Options -Indexes' . "\n".
             '        php_value auto_prepend_file "' . PHISHING_WEBSITE_PREPENDER . '"' . "\n".
             '    </Directory>' . "\n\n" .
             (($nextAliasVar) ? '    ' . PHISHING_WEBSITE_ANOTHER_ALIAS : '');
    }


    /**
     * Vrátí pole názvů proměnných, které se budou nahrazovat v šabloně konfiguračního souboru za skutečné hodnoty.
     *
     * @return string[]                Pole proměnných
     */
    private static function getConfigVarsToReplace() {
      return [
        'PHISHINGATOR_SERVER_PORT', 'PHISHINGATOR_SERVER_NAME', 'PHISHINGATOR_DOCUMENT_ROOT',
        'PHISHINGATOR_SERVER_ALIAS', 'PHISHINGATOR_WEBSITE_PREPENDER'
      ];
    }


    /**
     * Ověří, zdali přípona (typ) konfiguračního souboru odpovídá té předpokládané.
     *
     * @param string $filename         Název souboru
     * @param string $extension        Očekávaná přípona souboru
     * @return bool                    TRUE pokud přípona konfiguračního souboru odpovídá té předané
     */
    private static function isConfigType($filename, $extension) {
      return mb_substr($filename, -mb_strlen($extension)) == $extension;
    }


    /**
     * Vrátí alias (adresářovou cestu) pro konfigurační soubor podvodné stárnky.
     *
     * @param string $url              URL podvodné stránky
     * @return string                  Alias (adresářová cesta) podvodné stránky
     */
    private static function getUrlAlias($url) {
      $url = PhishingWebsiteModel::makeWebsiteUrl($url);
      $urlPath = parse_url($url, PHP_URL_PATH);

      return (!empty($urlPath) && $urlPath != '/') ? $urlPath : '/';
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
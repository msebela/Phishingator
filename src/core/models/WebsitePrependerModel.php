<?php
  /**
   * Třída, která obsluhuje a řídí podvodné stránky a zachytává a zpracovává data na nich zadaná.
   *
   * @author Martin Šebela
   */
  class WebsitePrependerModel {
    /**
     * @var bool        Informace o tom, zdali má být uživateli zobrazena chybová zpráva, pokud cokoliv zadá
     *                  do formuláře na podvodné stránce a formulář odešle.
     */
    private $displayMessage;

    /**
     * Vrátí informaci o tom, zdali má být uživateli zobrazena chybová hláška při
     * odeslání formuláře na podvodné stránce.
     *
     * @return bool                    TRUE pro zobrazení chybové hlášky, jinak FALSE.
     */
    public function getDisplayMessage() {
      return $this->displayMessage;
    }

    /**
     * Nastaví výchozí hodnoty a spustí metodu pro zpracování zobrazované podvodné stránky.
     */
    public function __construct() {
      $this->displayMessage = false;
      $this->process();
    }


    /**
     * Vrátí IP adresu uživatele.
     *
     * @return string|null             IP adresa uživatele.
     */
    public static function getClientIp() {
      $ipAddress = null;

      if (getenv('HTTP_CLIENT_IP')) {
        $ipAddress = getenv('HTTP_CLIENT_IP');
      }
      elseif (getenv('HTTP_X_FORWARDED')) {
        $ipAddress = getenv('HTTP_X_FORWARDED');
      }
      elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ipAddress = getenv('HTTP_FORWARDED_FOR');
      }
      elseif (getenv('HTTP_FORWARDED')) {
        $ipAddress = getenv('HTTP_FORWARDED');
      }
      elseif (getenv('REMOTE_ADDR')) {
        $ipAddress = getenv('REMOTE_ADDR');
      }

      return $ipAddress;
    }


    /**
     * Pokusí se zjistit a vrátí lokální IP adresu uživatele.
     *
     * @return null|string             Lokální IP adresa uživatele.
     */
    public static function getClientLocalIp() {
      $ipAddress = '';

      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
      }
      elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
      }

      return $ipAddress;
    }


    /**
     * Zpracuje předaná data (typicky $_POST data z formuláře), nalezne v nich vstupní pole pro heslo
     * a uživatelem zadaný řetězec anonymizuje (podle konfigurace systému).
     *
     * @param array $inputs            Data z formuláře.
     * @param string $anonymLevel      Úroveň anonymizace (between, full nebo nevyplněno).
     * @return array                   Původní data s anonymizovanými hesly.
     */
    private function anonymizePasswords($inputs, $anonymLevel) {
      foreach ($inputs as $inputName => $value) {
        if (empty($value)) continue;

        if ($inputName == 'password') {
          /* Volba "between" - anonymizovat vše vyjma prvního a posledního znaku (počet znaků hesla zachovat). */
          if ($anonymLevel == 'between' && mb_strlen($value) >= 3) {
            $firstChar = mb_substr($value, 0, 1);
            $lastChar = mb_substr($value, -1, 1);

            /** @var int     Počet opakování zástupného znaku v hesle ("-2" označuje první a poslední znak). */
            $countRepeatChar = mb_strlen($value) - 2 * mb_strlen(PASSWORD_CHAR_ANONYMIZATION);

            $inputs[$inputName] = $firstChar . str_repeat(PASSWORD_CHAR_ANONYMIZATION, $countRepeatChar) . $lastChar;
          }
          /* Volba "full" - anonymizovat vše, zachovat pouze počet znaků původního hesla. */
          elseif ($anonymLevel == 'full') {
            $inputs[$inputName] = str_repeat(PASSWORD_CHAR_ANONYMIZATION, mb_strlen($value));
          }
          /* Volba "between3stars" - anonymizovat vše vyjma prvního a posledního znaku, ostatní znaky budou nahrazeny
             3 hvězdičkami, tzn. délka hesla bude vždy 5 znaků (i když bude zadáno kratší heslo). */
          elseif ($anonymLevel == 'between3stars') {
            $firstChar = mb_substr($value, 0, 1);
            $lastChar = mb_substr($value, -1, 1);

            $inputs[$inputName] = $firstChar . str_repeat(PASSWORD_CHAR_ANONYMIZATION, 3) . $lastChar;
          }
        }
      }

      return $inputs;
    }


    /**
     * Získá konkrétní data z $_POST, pošle je k anonymizaci a vrátí je anonymizovaná.
     *
     * @param array $capturedInputs    Konkrétní vstupní pole $_POST, která se mají dále zpracovávat.
     * @return array                   Anonymizovaná a zpracovaná data.
     */
    private function getPost($capturedInputs) {
      $postData = [];

      /* Získá konkrétní data z $_POST a uloží je do pomocného pole. */
      foreach ($capturedInputs as $input) {
        if (isset($_POST[$input])) {
          $postData[$input] = $_POST[$input];
        }
      }

      /* Anonymizace hesel v pomocném poli. */
      $postData = $this->anonymizePasswords($postData, PASSWORD_LEVEL_ANONYMIZATION);

      return $postData;
    }


    /**
     * Vloží do databáze záznam o aktivitě konkrétního uživatele na podvodné stránce.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @param string $email            E-mail uživatele
     * @param int $credentialsResult   1 pokud byly zadány platné přihlašovací údaje, jinak 0.
     */
    private function logCapturedData($idCampaign, $idUser, $email, $credentialsResult) {
      $record = [
        'id_campaign' => $idCampaign,
        'id_user' => $idUser,
        'id_action' => CAMPAIGN_VISIT_FRAUDULENT_PAGE_ID,
        'used_email' => $email,
        'visit_datetime' => date('Y-m-d H:i:s'),
        'ip' => self::getClientIp(),
        'local_ip' => self::getClientLocalIp(),
        'browser_fingerprint' => $_SERVER['HTTP_USER_AGENT']
      ];

      if (!empty($_POST)) {
        $record['id_action'] = (($credentialsResult == true) ? CAMPAIGN_VALID_CREDENTIALS_ID : CAMPAIGN_INVALID_CREDENTIALS_ID);
        $record['data_json'] = json_encode(
          $this->getPost(['username', 'password']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
      }

      Database::insert('phg_captured_data', $record);
    }


    /**
     * Ověří, zdali byly zadány platné přihlašovací údaje.
     *
     * @param string $username         Uživatelské jméno.
     * @param string $password         Heslo uživatele.
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE.
     */
    public function areCredentialsValid($username, $password) {
      $validCreds = false;

      if (!empty($username) && !empty($password)) {
        /* Pokud uživatelské jméno obsahuje jiné, než alfanumerické znaky, tak to určitě není uživatelské jméno
           používané v tomto univerzitním prostředí a nemá smysl jej ani ověřovat (i z bezpečnostního hlediska). */
        if (!ctype_alnum($username)) {
          Logger::warning('Snaha o použití uživatelského jména, které neobsahuje pouze alfanumerické znaky.', $username);

          return false;
        }

        $validCreds = CredentialsTesterModel::tryLogin($username, $password);
      }

      return $validCreds;
    }


    /**
     * Vrátí identifikátor uživatele pro podvodné stránky a ID kampaně z řetězce, přes který je možné se
     * na podvodnou stránku dostat.
     *
     * @param string $url              Řetězec, se kterým se uživatel snaží na podvodno stránku dostat.
     * @return array|null              Pole obsahující ID kampaně a identifikátor uživatele nebo NULL.
     */
    public static function parseWebsiteUrl($url) {
      $args = null;
      $halfLength = round(USER_ID_WEBSITE_LENGTH / 2);

      if (mb_strlen($url) > USER_ID_WEBSITE_LENGTH) {
        $idCampaign = mb_substr($url, $halfLength, mb_strlen(USER_ID_WEBSITE_LENGTH) - $halfLength - 1);
        $idUser = mb_substr($url, 0, $halfLength) . mb_substr($url, -mb_strlen(USER_ID_WEBSITE_LENGTH) - $halfLength + 1);

        $args = ['id_campaign' => $idCampaign, 'id_user' => $idUser, 'url' => $url];
      }

      return $args;
    }


    /**
     * Vytvoří řetězec unikátní pro každého uživatele, přes který se bude uživatel moci dostat na podvodnou stránku a přes který
     * zároveň dojde k identifikaci uživatele a konkrétní kampaně.
     *
     * @param int $idCampaign          ID kampaně
     * @param string $userUrl          Identifikátor uživatele pro podvodné stránky.
     * @return string                  Řetězec pro navštívení podvodné stránky a identifikaci uživatele a kampaně.
     */
    public static function makeWebsiteUrl($idCampaign, $userUrl) {
      $url = '';
      $halfLength = round(USER_ID_WEBSITE_LENGTH / 2);

      if (!empty($idCampaign) && !empty($userUrl)) {
        $url = mb_substr($userUrl, 0, $halfLength) . $idCampaign . mb_substr($userUrl, -mb_strlen(USER_ID_WEBSITE_LENGTH) - $halfLength + 1);
      }

      return $url;
    }


    /**
     * Ověří počet požadavků uživatele na podvodné stránce a v případě nadlimitního počtu požadavků
     * dojde k přesměrování uživatele na úvodní stránku projektu bez dočasné možnosti návratu.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     */
    private function checkCountUserRequests($idCampaign, $idUser) {
      $requests = Database::queryMulti('
              SELECT visit_datetime
              FROM phg_captured_data
              WHERE id_campaign = ?
              AND id_user = ?
              ORDER BY id_captured_data DESC
              LIMIT 10
      ', [$idCampaign, $idUser]);

      /* Pokud je počet požadavků u dané kampaně od jednoho uživatele větší než... */
      if (count($requests) >= 10) {
        /* Zjistí se první záznam a poslední záznam aktivity. */
        $latest_activity = $requests[0]['visit_datetime'];
        $latest_nineth_activity = $requests[count($requests) - 1]['visit_datetime'];

        /* Pokud mezi záznamy neuplynul dostatečný čas... */
        if (strtotime('+20 seconds', strtotime($latest_nineth_activity)) >= strtotime($latest_activity)
          && strtotime('+60 seconds', strtotime($latest_activity)) >= strtotime(date('Y-m-d H:i:s'))) {
          /* Dočasné přesměrování - nemožnost přistoupit zpět na podvodnou stránku po určitou dobu. */

          Logger::warning(
            'Zablokování nadlimitního počtu požadavků uživatele na podvodné stránce.',
            ['id_campaign' => $idCampaign, 'id_user' => $idUser]
          );

          header('Location: ' . WEB_URL);
          exit();
        }
      }
    }


    /**
     * Zpracuje požadavky a připraví podvodnou stránku na základě konfigurace u dané kampaně v databázi.
     */
    private function process() {
      if (isset($_GET) && count($_GET) == 1) {
        $campaignModel = new CampaignModel();

        $getArgs = array_keys($_GET);
        $args = $this->parseWebsiteUrl($getArgs[0]);

        if ($args == null) {
          $args[] = self::getClientIp();
          Logger::warning('Snaha o nepovolený přístup (s neplatnými argumenty) na podvodnou stránku.', $args);

          header('Location: ' . WEB_URL);
          exit();
        }

        Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

        $campaign = $campaignModel->getCampaign($args['id_campaign']);
        $user = UsersModel::getUserByURL($args['id_user']);

        // Kontrola existence záznamu.
        if (empty($campaign) || empty($user) || $campaignModel->isUserRecipient($args['id_campaign'], $user['id_user']) != 1) {
          $args[] = self::getClientIp();
          Logger::warning('Snaha o nepovolený přístup na podvodnou stránku.', $args);

          header('Location: ' . WEB_URL);
          exit();
        }

        // Stránka bude přístupná a bude zaznamenávat aktivitu od/do zvoleného data, přičemž hraniční čas konečného data je 23:59:59.
        if (strtotime($campaign['active_since']) > strtotime('now') || strtotime($campaign['active_to'] . ' 23:59:59') < strtotime('now')) {
          $args[] = self::getClientIp();
          Logger::warning('Snaha o přístup na podvodnou stránku u kampaně, která není aktivní.', $args);

          header('Location: ' . WEB_URL . '/' . ACT_PHISHING_TEST . '/' . $args['url']);
          exit();
        }

        // Ověření, zda uživatel nezasílá příliš mnoho požadavků a nesnaží se vytížit aplikaci neustálým zápisem záznamů o návštěvě podvodné stránky.
        $this->checkCountUserRequests($args['id_campaign'], $user['id_user']);

        // Uložení získaných dat.
        $credentialsResult = $this->areCredentialsValid($_POST['username'] ?? '', $_POST['password'] ?? '');
        $this->logCapturedData($args['id_campaign'], $user['id_user'], $user['email'], $credentialsResult);

        // Vykonání akce, která se stane po odeslání formuláře.
        if (!empty($_POST)) {
          $onsubmitAction = $campaignModel->getWebsiteAction($campaign['id_onsubmit']);

          if ($campaign['id_onsubmit'] == 2) {
            // Přesměrování na stránku s informací o absolvování praktického phishingového testu.
            header('Location: ' . WEB_URL . '/' . ACT_PHISHING_TEST . '/' . $args['url']);
            exit();
          }
          elseif ($campaign['id_onsubmit'] == 3) {
            // Přesměrování na konkrétní adresu.
            header('Location: ' . $onsubmitAction['url']);
            exit();
          }
          elseif ($campaign['id_onsubmit'] == 4) {
            // Neustálé zobrazování chybové zprávy.
            $this->displayMessage = true;
          }
          elseif ($campaign['id_onsubmit'] == 5) {
            // Po druhém zadání přihlašovacích údajů provést přesměrování.
            $countMaxSends = 2;
            $sessionName = 'phishingMessage-' . $args['id_campaign'];

            // Nastavení, kolikrát již uživatel zadal údaje do formuláře.
            if (isset($_SESSION[$sessionName])) {
              $_SESSION[$sessionName] += 1;
            }
            else {
              $_SESSION[$sessionName] = 1;
            }

            $this->displayMessage = true;

            // Při překročení nastaveného limitu přesměrovat na zvolenou URL.
            if ($_SESSION[$sessionName] >= $countMaxSends) {
              unset($_SESSION[$sessionName]);

              header('Location: ' . $onsubmitAction['url']);
              exit();
            }
          }
        }
      }
      // Náhled podvodné stránky pro administrátory a správce testů.
      elseif (isset($_GET) && count($_GET) == 2) {
        $this->processPreview();
      }
      else {
        Logger::warning('Snaha o nepovolený přístup (bez argumentů) na podvodnou stránku.', self::getClientIp());

        header('Location: ' . WEB_URL);
        exit();
      }
    }


    /**
     * Obslouží požadavky zajišťující náhled podvodné stránky pro administrátory a správce testů.
     */
    private function processPreview() {
      $getArgs = array_keys($_GET);
      $args = $this->parseWebsiteUrl($getArgs[0]);

      // Argumenty neodpovídají předpokladu.
      if ($args == null) {
        $args[] = self::getClientIp();
        Logger::warning('Snaha o nepovolený přístup na náhled podvodné stránky.', $args);

        header('Location: ' . WEB_URL);
        exit();
      }

      Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

      // Zjištění informací o uživateli, který se snaží na náhled podvodné stránky přistoupit.
      $user = UsersModel::getUserByURL($args['id_user']);
      $userDetail = UsersModel::getUserByUsername($user['username']);

      // Zjištění informací o zobrazované podvodné stránce.
      $website = PhishingWebsiteModel::getPhishingWebsiteByUrl(get_base_url());

      // Zjištění, zdali je v databázi existující ticket pro přístup na náhled podvodné stránky.
      $previewAccess = Database::querySingle('
            SELECT `active_since`, `active_to` FROM `phg_websites_preview` WHERE id_website = ? AND id_user = ? AND hash = ?',
        [$website['id_website'], $user['id_user'], $getArgs[1]]
      );

      // V databázi neexistuje ticket k přístupu na náhled podvodné stránky.
      if (empty($user) || $previewAccess == false) {
        $args[] = self::getClientIp();
        Logger::warning('Snaha o nepovolený přístup na náhled podvodné stránky (podvržení neplatných parametrů).', $args);

        header('Location: ' . WEB_URL);
        exit();
      }
      // Pokud platnost ticketu k přístupu na náhled podvodné stránky vypršela.
      elseif (strtotime('now') > strtotime($previewAccess['active_to'])) {
        $args[] = self::getClientIp();
        Logger::warning('Snaha o přístup na náhled podvodné stránky s vypršelým ticketem.', $args);

        echo 'Interval pro zobrazení náhledu podvodné stránky vypršel.';
        exit();
      }

      // Zjištění, zdali je uživatel správce testů, nebo administrátor.
      if ($userDetail['role'] <= PERMISSION_TEST_MANAGER) {
        // Vložení informační hlavičky s poznámkou, že se uživatel nachází na náhledu podvodné stránky.
        require CORE_DOCUMENT_ROOT . '/' . CORE_DIR_VIEWS . '/preview-phishing-website' . CORE_VIEWS_FILE_EXTENSION;

        Logger::info('Přístup na náhled podvodné stránky (správce testů/administrátor).', $args);
      }
      else {
        $args[] = self::getClientIp();
        Logger::warning('Snaha o nepovolený přístup na náhled podvodné stránky (nedostatečné oprávnění).', $args);

        header('Location: ' . WEB_URL);
        exit();
      }
    }
  }

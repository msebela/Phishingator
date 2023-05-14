<?php
  /**
   * Třída, která zajišťuje činnost podvodných stránek a zachytává a zpracovává data na nich zadaná.
   *
   * @author Martin Šebela
   */
  class WebsitePrependerModel {
    /**
     * @var string      Uživatelské jméno uživatele, který na podvodnou stránku přistoupil
     *                  (pro personalizovaný výpis do podvodné stránky).
     */
    private $username;

    /**
     * @var string      E-mail uživatele, který na podvodnou stránku přistoupil
     *                  (pro personalizovaný výpis do podvodné stránky).
     */
    private $email;

    /**
     * @var bool        Informace o tom, zdali má být uživateli zobrazena chybová zpráva, pokud cokoliv zadá
     *                  do formuláře na podvodné stránce a formulář odešle.
     */
    private $displayMessage;


    /**
     * Vrátí uživatelské jméno uživatele, který na podvodnou stránku přistoupil.
     *
     * @return string
     */
    public function getUsername() {
      return $this->username;
    }


    /**
     * Vrátí e-mail uživatele, který na podvodnou stránku přistoupil.
     *
     * @return string
     */
    public function getEmail() {
      return $this->email;
    }


    /**
     * Vrátí informaci o tom, zdali má být uživateli zobrazena chybová hláška při
     * odeslání formuláře na podvodné stránce.
     *
     * @return bool
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
     * Vrátí skutečnou IP adresu uživatele.
     *
     * @return string|null             IP adresa uživatele
     */
    public static function getClientIp() {
      $ipAddress = null;

      if (getenv('HTTP_X_REAL_IP')) {
        $ipAddress = getenv('HTTP_X_REAL_IP');
      }
      elseif (getenv('REMOTE_ADDR')) {
        $ipAddress = getenv('REMOTE_ADDR');
      }
      elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ipAddress = getenv('HTTP_X_FORWARDED_FOR');
      }
      elseif (getenv('HTTP_CLIENT_IP')) {
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

      if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
        $ipAddress = null;
      }

      return $ipAddress;
    }


    /**
     * Zpracuje předaná data (typicky $_POST data z formuláře), nalezne v nich vstupní pole pro heslo
     * a uživatelem zadaný řetězec anonymizuje (podle konfigurace Phishingatoru).
     *
     * @param array $inputs            Data z formuláře.
     * @param string $anonymLevel      Úroveň anonymizace (viz konfigurace Phishingatoru).
     * @return array                   Původní data s anonymizovanými hesly.
     */
    private function anonymizePasswords($inputs, $anonymLevel) {
      foreach ($inputs as $inputName => $value) {
        if (empty($value)) continue;

        if ($inputName == PHISHING_WEBSITE_INPUT_FIELD_PASSWORD) {
          // Volba "between" - anonymizovat vše vyjma prvního a posledního znaku (počet znaků hesla zachovat).
          if ($anonymLevel == 'between' && mb_strlen($value) >= 3) {
            $firstChar = mb_substr($value, 0, 1);
            $lastChar = mb_substr($value, -1, 1);

            // Počet opakování zástupného znaku v hesle ("-2" označuje první a poslední znak).
            $countRepeatChar = mb_strlen($value) - 2 * mb_strlen(PASSWORD_CHAR_ANONYMIZATION);

            $inputs[$inputName] = $firstChar . str_repeat(PASSWORD_CHAR_ANONYMIZATION, $countRepeatChar) . $lastChar;
          }
          // Volba "full" - anonymizovat vše, zachovat pouze počet znaků původního hesla.
          elseif ($anonymLevel == 'full') {
            $inputs[$inputName] = str_repeat(PASSWORD_CHAR_ANONYMIZATION, mb_strlen($value));
          }
          // Volba "between3stars" - anonymizovat vše vyjma prvního a posledního znaku, ostatní znaky budou nahrazeny
          // 3 hvězdičkami, tzn. délka hesla bude vždy 5 znaků (i když bude zadáno kratší heslo).
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

      // Získá konkrétní data z $_POST a uloží je do pomocného pole.
      foreach ($capturedInputs as $input) {
        if (isset($_POST[$input])) {
          $postData[$input] = $_POST[$input];
        }
      }

      // Anonymizace hesel.
      return $this->anonymizePasswords($postData, PASSWORD_LEVEL_ANONYMIZATION);
    }


    /**
     * Vloží do databáze záznam o aktivitě konkrétního uživatele na podvodné stránce.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @param string $email            E-mail uživatele
     * @param string $group            Skupina uživatele
     * @param int $credentialsResult   1 pokud byly zadány platné přihlašovací údaje, jinak 0.
     * @return void
     */
    private function logCapturedData($idCampaign, $idUser, $email, $group, $credentialsResult) {
      $record = [
        'id_campaign' => $idCampaign,
        'id_user' => $idUser,
        'id_action' => CAMPAIGN_VISIT_FRAUDULENT_PAGE_ID,
        'used_email' => $email,
        'used_group' => $group,
        'visit_datetime' => date('Y-m-d H:i:s'),
        'ip' => self::getClientIp(),
        'browser_fingerprint' => $_SERVER['HTTP_USER_AGENT']
      ];

      if (!empty($_POST)) {
        $record['id_action'] = ($credentialsResult ? CAMPAIGN_VALID_CREDENTIALS_ID : CAMPAIGN_INVALID_CREDENTIALS_ID);
        $record['data_json'] = json_encode(
          $this->getPost([PHISHING_WEBSITE_INPUT_FIELD_USERNAME, PHISHING_WEBSITE_INPUT_FIELD_PASSWORD]),
          JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
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
        // Pokud uživatelské jméno obsahuje jiné, než alfanumerické znaky, tak to pravděpodobně není uživatelské jméno
        // používané v organizaci a nemá smysl jej ani ověřovat (i z bezpečnostního hlediska).
        if (ctype_alnum($username)) {
          $validCreds = CredentialsTesterModel::tryLogin($username, $password);
        }
        else {
          Logger::warning('Attempt to use a username that does not contain only alphanumeric characters.', $username);
        }
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

      if (ctype_alnum($url) && mb_strlen($url) > USER_ID_WEBSITE_LENGTH) {
        $idCampaign = mb_substr($url, $halfLength, mb_strlen(USER_ID_WEBSITE_LENGTH) - $halfLength - 1);
        $idUser = mb_substr($url, 0, $halfLength) . mb_substr($url, -mb_strlen(USER_ID_WEBSITE_LENGTH) - $halfLength + 1);

        if (mb_strlen($idUser) == USER_ID_WEBSITE_LENGTH && is_numeric($idCampaign)) {
          $args = ['id_campaign' => $idCampaign, 'id_user' => $idUser, 'url' => $url];
        }
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
    public static function makeUserWebsiteId($idCampaign, $userUrl) {
      $url = '';
      $halfLength = round(USER_ID_WEBSITE_LENGTH / 2);

      if (is_numeric($idCampaign) && !empty($userUrl)) {
        $url = mb_substr($userUrl, 0, $halfLength) . $idCampaign . mb_substr($userUrl, -mb_strlen(USER_ID_WEBSITE_LENGTH) - $halfLength + 1);
      }

      return $url;
    }


    /**
     * Ověří počet požadavků uživatele na podvodné stránce a v případě nadlimitního počtu požadavků
     * dojde k uzavření uživatelského požadavku a k dočasnému zablokování přístupu na podvodnou stránku.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @return void
     */
    private function checkCountUserRequests($idCampaign, $idUser) {
      $maxUserRequests = 10;

      $requests = Database::queryMulti('
              SELECT visit_datetime
              FROM phg_captured_data
              WHERE id_campaign = ?
              AND id_user = ?
              ORDER BY id_captured_data DESC
              LIMIT ?
      ', [$idCampaign, $idUser, $maxUserRequests]);

      // Pokud je počet požadavků u dané kampaně od jednoho uživatele větší než...
      if (count($requests) >= $maxUserRequests) {
        // Zjistí se první záznam a poslední záznam aktivity.
        $latest_activity = $requests[0]['visit_datetime'];
        $latest_nineth_activity = $requests[count($requests) - 1]['visit_datetime'];

        // Pokud mezi záznamy neuplynul dostatečný čas...
        if (strtotime('+20 seconds', strtotime($latest_nineth_activity)) >= strtotime($latest_activity)
          && strtotime('+60 seconds', strtotime($latest_activity)) >= strtotime(date('Y-m-d H:i:s'))) {

          // Dočasné zablokování - nemožnost přistoupit zpět na podvodnou stránku po určitou dobu.

          Logger::warning(
            'Blocking an excessive number of user requests on a phishing website.',
            ['id_campaign' => $idCampaign, 'id_user' => $idUser]
          );

          exit();
        }
      }
    }


    /**
     * Pokusí se najít ve všech GET parametrech a hodnotách identifikátor
     * uživatele pro přístup na podvodnou stránku.
     *
     * @param array $get               GET data
     * @return array|null              Pole s identifikátorem uživatele, jinak NULL
     */
    private function getUserWebsiteId($get) {
      $userId = null;
      $getData = array_merge(array_keys($get), $get);

      foreach ($getData as $data) {
        $userId = $this->parseWebsiteUrl($data);

        if ($userId != null) {
          break;
        }
      }

      return $userId;
    }


    /**
     * Zpracuje požadavky a připraví podvodnou stránku na základě její konfigurace.
     *
     * @return void
     */
    private function process() {
      if (isset($_GET)) {
        $args = $this->getUserWebsiteId($_GET);

        // Ověření, zda má jít o náhled podvodné stránky pro administrátory a správce testů.
        $preview = isset($_GET[ACT_PREVIEW]) && mb_strlen($_GET[ACT_PREVIEW]) == PHISHING_WEBSITE_PREVIEW_HASH_BYTES * 2;

        if ($args == null) {
          $args[] = self::getClientIp();

          Logger::warning(
            'Unauthorized access to' . (($preview) ? ' preview' : '') . ' a phishing website (invalid user/campaign argument).',
            $args
          );

          header('Location: ' . WEB_BASE_URL);
          exit();
        }
        else {
          Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

          if ($preview) {
            $this->processAdminPreview($args);
          }
          else {
            $this->processView($args);
          }
        }
      }
      else {
        Logger::warning('Unauthorized access to a phishing website (without arguments).', self::getClientIp());

        header('Location: ' . WEB_BASE_URL);
        exit();
      }
    }


    /**
     * Obslouží požadavky zajišťující zobrazení podvodné stránky pro všechny účastníky kampaně.
     *
     * @param array $args              Argumenty pro podvodnou stránku
     * @return void
     */
    private function processView($args) {
      $campaignModel = new CampaignModel();
      $campaign = $campaignModel->getCampaign($args['id_campaign']);
      $user = UsersModel::getUserByURL($args['id_user']);

      // Kontrola existence záznamu.
      if (empty($campaign) || empty($user) || $campaignModel->isUserRecipient($args['id_campaign'], $user['id_user']) != 1) {
        $args[] = self::getClientIp();
        Logger::warning('Unauthorized access to a phishing website.', $args);

        header('Location: ' . WEB_BASE_URL);
        exit();
      }

      // Stránka bude přístupná a bude zaznamenávat aktivitu od/do zvoleného data a času.
      if (strtotime($campaign['active_since']) > strtotime('now') || strtotime($campaign['active_to'] . ' ' . CAMPAIGN_END_TIME) < strtotime('now')) {
        $args[] = self::getClientIp();
        Logger::warning('Invalid access a phishing website for a phishing campaign that is not active.', $args);

        header('Location: ' . WEB_URL . '/' . ACT_PHISHING_TEST . '/' . $args['url']);
        exit();
      }

      // Data o uživateli pro možný, personalizovaný výpis na podvodné stránce.
      $this->username = $user['username'];
      $this->email = $user['email'];

      // Ověření, zda uživatel nezasílá příliš mnoho požadavků a nesnaží se zatížit aplikaci neustálým zápisem záznamů o návštěvě podvodné stránky.
      $this->checkCountUserRequests($args['id_campaign'], $user['id_user']);

      // Uložení získaných dat.
      $credentialsResult = $this->areCredentialsValid($_POST[PHISHING_WEBSITE_INPUT_FIELD_USERNAME] ?? '', $_POST[PHISHING_WEBSITE_INPUT_FIELD_PASSWORD] ?? '');
      $this->logCapturedData($args['id_campaign'], $user['id_user'], $user['email'], $user['primary_group'], $credentialsResult);

      // Vykonání akce, ke které má dojít po odeslání formuláře.
      if (!empty($_POST)) {
        $action = $campaignModel->getWebsiteAction($campaign['id_onsubmit']);
        $this->processForm($action['id_onsubmit'], $args, $credentialsResult);
      }
    }


    /**
     * Obslouží požadavky zajišťující náhled podvodné stránky pro administrátory a správce testů.
     *
     * @param array $args              Argumenty pro podvodnou stránku
     * @return void
     */
    private function processAdminPreview($args) {
      // Zjištění informací o uživateli, který se snaží na náhled podvodné stránky přistoupit.
      $user = UsersModel::getUserByURL($args['id_user']);
      $userDetail = UsersModel::getUserByUsername($user['username']);

      // Úprava URL adresy do původní podoby (odstranění parametrů pro náhled podvodné stránky).
      $url = str_replace(
        [$args['url'], '&' . ACT_PREVIEW . '=' . $_GET[ACT_PREVIEW]],
        [VAR_RECIPIENT_URL, ''],
        get_current_url()
      );

      // Zjištění informací o zobrazované podvodné stránce.
      $website = PhishingWebsiteModel::getPhishingWebsiteByUrl($url);

      // Pokud se nepodařilo zjistit informace o podvodné stránce a mělo by se jednat o stránku
      // běžící na protokolu HTTP, ale prohlížeč předal adresu s HTTPS (např. z důvodu HSTS),
      // načíst informace o stránce i pro variantu s HTTPS.
      $https = 'https://';

      if (!$website && mb_substr($url, 0, mb_strlen($https)) == $https) {
        $website = PhishingWebsiteModel::getPhishingWebsiteByUrl(
          str_replace($https, 'http://', $url)
        );
      }

      // Zjištění, zdali je v databázi existující ticket pro přístup na náhled podvodné stránky.
      $previewAccess = Database::querySingle(
        'SELECT `active_since`, `active_to` FROM `phg_websites_preview` WHERE id_website = ? AND id_user = ? AND hash = ?',
        [$website['id_website'], $user['id_user'], $_GET[ACT_PREVIEW]]
      );

      // V databázi neexistuje ticket k přístupu na náhled podvodné stránky.
      if (empty($user) || !$previewAccess) {
        $args[] = self::getClientIp();
        Logger::warning('Unauthorized access to preview a phishing website (non-existent ticket).', $args);

        header('Location: ' . WEB_URL);
        exit();
      }
      // Pokud platnost ticketu k přístupu na náhled podvodné stránky vypršela.
      elseif (strtotime('now') > strtotime($previewAccess['active_to'])) {
        $args[] = self::getClientIp();
        Logger::warning('Invalid access a preview of a phishing website with an expired ticket.', $args);

        echo 'Interval pro zobrazení náhledu podvodné stránky vypršel.';
        exit();
      }

      // Zjištění, zdali je uživatel správce testů, nebo administrátor.
      if ($userDetail['role'] <= PERMISSION_TEST_MANAGER) {
        // Vložení informační hlavičky s poznámkou, že se jedná o náhled podvodné stránky.
        require CORE_DOCUMENT_ROOT . '/' . CORE_DIR_VIEWS . '/preview-phishing-website' . CORE_VIEWS_FILE_EXTENSION;

        // Data o uživateli pro možný, personalizovaný výpis na podvodné stránce.
        $this->username = $user['username'];
        $this->email = $user['email'];

        Logger::info('Access to a preview of the phishing website.', $args);
      }
      else {
        $args[] = self::getClientIp();
        Logger::warning('Unauthorized access to preview a phishing website (insufficient authorization).', $args);

        header('Location: ' . WEB_URL);
        exit();
      }
    }


    /**
     * Vykoná akci, ke které má dojít po odeslání formuláře na podvodné stránce.
     *
     * @param int $onSubmitAction      ID akce, ke které má dojít po odeslání formuláře
     * @param array $args              Další argumenty k akci, ke které má dojít (např. URL pro přesměrování)
     * @param bool $credentialsResult  TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     * @return void
     */
    private function processForm($onSubmitAction, $args, $credentialsResult) {
      // Přesměrování na vzdělávací stránku s indiciemi (po zadání čehokoliv).
      if ($onSubmitAction == 2) {
        header('Location: ' . WEB_URL . '/' . ACT_PHISHING_TEST . '/' . $args['url']);
        exit();
      }
      // Přesměrování na vzdělávací stránku s indiciemi (pouze po zadání platných přihlašovacích údajů).
      elseif ($onSubmitAction == 3) {
        if ($credentialsResult) {
          header('Location: ' . WEB_URL . '/' . ACT_PHISHING_TEST . '/' . $args['url']);
          exit();
        }
        else {
          $this->displayMessage = true;
        }
      }
      // Neustálé zobrazování chybové zprávy.
      elseif ($onSubmitAction == 4) {
        $this->displayMessage = true;
      }
      // Po druhém zadání přihlašovacích údajů přesměrovat na vzdělávací stránku s indiciemi.
      elseif ($onSubmitAction == 5) {
        $countMaxSends = 2;
        $sessionName = 'phishingMessage-' . $args['id_campaign'];

        // Nastavení, kolikrát již uživatel odeslal formulář.
        if (isset($_SESSION[$sessionName])) {
          $_SESSION[$sessionName] += 1;
        }
        else {
          $_SESSION[$sessionName] = 1;
        }

        $this->displayMessage = true;

        // Při překročení nastaveného limitu přesměrovat na vzdělávací stránku s indiciemi.
        if ($_SESSION[$sessionName] >= $countMaxSends) {
          unset($_SESSION[$sessionName]);

          header('Location: ' . WEB_URL . '/' . ACT_PHISHING_TEST . '/' . $args['url']);
          exit();
        }
      }
    }
  }
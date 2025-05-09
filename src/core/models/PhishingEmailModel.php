<?php
  /**
   * Třída sloužící k získávání informací o podvodných e-mailech,
   * k přidávání nových podvodných e-mailů, k úpravě těch existujících, k jejich personalizaci
   * a k dalším souvisejícím operacím.
   *
   * @author Martin Šebela
   */
  class PhishingEmailModel extends FormModel {
    /**
     * @var string      Název podvodného e-mailu.
     */
    protected $name;

    /**
     * @var int         Proměnná uchovávající informaci o tom, zdali je podvodný e-mail skryt pro správce testů.
     */
    protected $hidden;

    /**
     * @var string      Jméno odesílatele podvodného e-mailu.
     */
    protected $senderName;

    /**
     * @var string      E-mail odesílatele podvodného e-mailu.
     */
    protected $senderEmail;

    /**
     * @var string      Předmět podvodného e-mailu.
     */
    protected $subject;

    /**
     * @var string      Tělo podvodného e-mailu.
     */
    protected $body;

    /**
     * @var int         Proměnná uchovávající informaci o tom, zdali se jedná o e-mail v HTML formátu.
     */
    protected $html;


    /**
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function load($data) {
      parent::load($data);

      $this->html = (empty($this->html) ? 0 : 1);
      $this->hidden = (empty($this->hidden) ? 0 : 1);
    }


    /**
     * Připraví nový podvodný e-mail v závislosti na vyplněných datech a vrátí ho formou pole.
     *
     * @return array                   Pole obsahující data o podvodném e-mailu.
     */
    public function makePhishingEmail() {
      return [
        'name' => $this->name,
        'sender_name' => $this->senderName,
        'sender_email' => $this->senderEmail,
        'subject' => $this->subject,
        'body' => $this->body,
        'html' => $this->html,
        'hidden' => $this->hidden
      ];
    }


    /**
     * Uloží do instance a zároveň vrátí (z databáze) informace o zvoleném podvodném e-mailu.
     *
     * @param int $id                  ID e-mailu
     * @return array                   Pole obsahující informace o podvodném e-mailu.
     */
    public function getPhishingEmail($id) {
      $whereFilter = (PermissionsModel::getUserRole() == PERMISSION_TEST_MANAGER) ? 'AND `hidden` = 0' : '';

      $this->dbRecordData = Database::querySingle('
              SELECT `id_email`, `name`, `sender_name`, `sender_email`, `subject`, `body`, `html`, `hidden`
              FROM `phg_emails`
              WHERE `id_email` = ?
              AND `visible` = 1
              ' . $whereFilter
      , $id);

      return $this->dbRecordData;
    }


    /**
     * Vrátí seznam všech podvodných e-mailů z databáze.
     *
     * @return mixed                   Pole e-mailů a informace o každém z nich.
     */
    public static function getPhishingEmails() {
      $whereFilter = (PermissionsModel::getUserRole() == PERMISSION_TEST_MANAGER) ? 'AND `hidden` = 0' : '';

      $records = Database::queryMulti('
              SELECT `id_email`, phg_emails.id_by_user, `name`, `subject`, phg_emails.date_added, `html`, `hidden`,
              `username`, `email`,
              DATE_FORMAT(phg_emails.date_added, "%e. %c. %Y") AS date_added_formatted
              FROM `phg_emails`
              JOIN `phg_users`
              ON phg_emails.id_by_user = phg_users.id_user
              WHERE phg_emails.visible = 1
              ' . $whereFilter . '
              ORDER BY `id_email` DESC
      ');

      return UsersModel::setUsernamesByConfig($records);
    }


    /**
     * Vrátí počet kampaní, ke kterým je přiřazený daný podvodný e-mail.
     *
     * @param int $id                  ID e-mailu
     * @return int                     Počet kampaní
     */
    private function getCountOfUsePhishingEmail($id) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_campaigns`
              WHERE `id_email` = ?
      ', $id);
    }


    /**
     * Vrátí počet kampaní, ve kterých je použit podvodný e-mail uživatelem, jehož oprávnění je správců testů.
     *
     * @param int $id                  ID e-mailu
     * @return int                     Počet kampaní
     */
    private function getCountOfUsePhishingEmailByTestManager($id) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_campaigns`
              JOIN `phg_users`
              ON phg_campaigns.id_by_user = phg_users.id_user
              JOIN `phg_users_groups`
              ON phg_users.id_user_group = phg_users_groups.id_user_group
              JOIN `phg_users_roles`
              ON phg_users_groups.role = phg_users_roles.id_user_role
              WHERE phg_campaigns.id_email = ?
              AND phg_campaigns.visible = 1
              AND phg_users_roles.value = ?
      ', [$id, PERMISSION_TEST_MANAGER]);
    }


    /**
     * Vloží do databáze nový podvodný e-mail a v případě úspěchu vrátí jeho ID.
     *
     * @return int                     ID nově vloženého podvodného e-mailu
     */
    public function insertPhishingEmail() {
      $email = $this->makePhishingEmail();

      $email['id_by_user'] = $_SESSION['user']['id'];
      $email['date_added'] = date('Y-m-d H:i:s');

      Logger::info('New phishing email added.', $email);

      Database::insert($this->dbTableName, $email);

      return Database::getLastInsertId();
    }


    /**
     * Upraví zvolený e-mail.
     *
     * @param int $id                  ID e-mailu
     * @throws UserError
     */
    public function updatePhishingEmail($id) {
      $email = $this->makePhishingEmail();

      $this->isPhishingEmailUsedByTestManager($id);

      Logger::info('Phishing email modified.', $email);

      Database::update(
        $this->dbTableName,
        $email,
        'WHERE `id_email` = ? AND `visible` = 1',
        $id
      );
    }


    /**
     * Odstraní (resp. deaktivuje) podvodný e-mail z databáze.
     *
     * @param int $id                  ID podvodného e-mailu
     * @throws UserError
     */
    public function deletePhishingEmail($id) {
      if ($this->getCountOfUsePhishingEmail($id) != 0) {
        throw new UserError(
          'Nelze smazat podvodný e-mail, který je svázán s nějakou existující kampaní.', MSG_ERROR
        );
      }

      $result = Database::update(
        'phg_emails',
        ['visible' => 0],
        'WHERE `id_email` = ? AND `visible` = 1',
        $id
      );

      if ($result == 0) {
        Logger::warning('Attempt to delete a non-existent phishing email.', $id);

        throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
      }

      Logger::info('Phishing email deleted.', $id);
    }


    /**
     * Vrátí pole proměnných, které se mohou vyskytovat v těle e-mailu.
     *
     * @return array                   Pole proměnných
     */
    private static function getEmailBodyVariables() {
      return [
        VAR_RECIPIENT_USERNAME, VAR_RECIPIENT_EMAIL,
        VAR_RECIPIENT_FIRSTNAME, VAR_RECIPIENT_SURNAME, VAR_RECIPIENT_FULLNAME,
        VAR_DATE_CZ, VAR_DATE_EN,
        VAR_URL, VAR_URL_HTML
      ];
    }


    /**
     * Vrátí seznam domén, ze kterých mohou pocházet e-maily příjemců.
     *
     * @param bool $returnString       TRUE, pokud má být seznam vrácen jako řetězec (nepovinné)
     * @return string[]|string         Pole (výchozí) nebo řetězec se seznamem povolených domén
     */
    public static function getAllowedEmailDomains($returnString = false) {
      $domains = explode(',', EMAILS_ALLOWED_DOMAINS);
      $domains = array_map('strtolower', $domains);

      if ($returnString) {
        $domainsString = '';
        $separator = '/';

        foreach ($domains as $domain) {
          $domainsString .= '<code>' . Controller::escapeOutput($domain) . '</code>' . $separator;
        }

        $domains = trim($domainsString, $separator);
      }

       return $domains;
    }


    /**
     * Vrátí informaci, zdali zadaný e-mail vede na některou z povolených domén.
     *
     * @param string $email            E-mail
     * @return bool                    TRUE pokud je e-mail z povolené domény, jinak FALSE
     */
    public static function isEmailInAllowedDomains($email) {
      $allowedDomains = self::getAllowedEmailDomains();
      $prefix = '@';

      $email = mb_strtolower($email);
      $allowed = false;

      foreach ($allowedDomains as $domain) {
        $domain = mb_strtolower($domain);

        if (mb_substr($email, -mb_strlen($domain) - mb_strlen($prefix)) === $prefix . $domain) {
          $allowed = true;
          break;
        }
      }

      return $allowed;
    }


    /**
     * Pomocí HTML vyznačí v těle e-mailu proměnné a toto upravené tělo e-mailu vrátí.
     *
     * @param string $body             Tělo e-mailu, ve kterém mají být vyznačeny proměnné
     * @return string                  Tělo e-mailu s vyznačenými proměnnými
     */
    private static function markVariablesInEmailBody($body) {
      $variables = self::getEmailBodyVariables();

      foreach ($variables as $variable) {
        $body = str_replace($variable, '<span class="variable">' . $variable . '</span>', $body);
      }

      return $body;
    }


    /**
     * Personalizuje odesílatele e-mailu tak, aby byl jako odesílatel uveden e-mail příjemce.
     *
     * @param string $senderEmail      E-mail odesílatele
     * @param string $userEmail        E-mail příjemce
     * @return string                  Pozměněný e-mail odesílatele
     */
    private static function personalizeEmailSender($senderEmail, $userEmail) {
      if (mb_strpos($senderEmail, VAR_RECIPIENT_EMAIL) !== false) {
        return str_replace(VAR_RECIPIENT_EMAIL, $userEmail, $senderEmail);
      }

      return $senderEmail;
    }


    /**
     * Vrátí personalizované tělo e-mailu vůči vybranému uživateli.
     *
     * @param array $user                     Data o uživateli, vůči kterému má být tělo e-mailu personalizováno
     * @param string $body                    Tělo e-mailu
     * @param string|null $websiteUrl         URL podvodné stránky (nepovinné)
     * @param int|null $idCampaign            ID kampaně (nepovinné)
     * @param string|null $datetimeEmailSent  Datum a čas, kdy došlo k odeslání e-mailu pro doplnění proměnné (nepovinné)
     * @return string                         Personalizované tělo e-mailu
     */
    public static function personalizeEmailBody($user, $body, $websiteUrl = null, $idCampaign = null, $datetimeEmailSent = null) {
      // Data o uživateli, která se budou dosazovat za použité proměnné.
      $values = [
        $user['username'], $user['email'],
        $user['firstname'], $user['surname'], $user['fullname'],
        date(VAR_DATE_CZ_FORMAT), date(VAR_DATE_EN_FORMAT)
      ];

      // Změna obsahu proměnné s datem v závislosti na datu odeslání e-mailu.
      if ($datetimeEmailSent !== null) {
        $timestampEmailSent = strtotime($datetimeEmailSent);

        $values[5] = date(VAR_DATE_CZ_FORMAT, $timestampEmailSent);
        $values[6] = date(VAR_DATE_EN_FORMAT, $timestampEmailSent);
      }

      // Nahrazení proměnné pro URL adresu podvodné stránky za personalizovaný odkaz.
      $body = self::personalizeURL($user, $body, $websiteUrl, $idCampaign);

      // Nahrazení dalších proměnných v těle e-mailu za personalizované/požadované hodnoty.
      return str_replace(self::getEmailBodyVariables(), $values, $body);
    }


    /**
     * Nahradí v těle e-mailu proměnnou pro URL adresu na podvodnou stránku
     * a případně ji personalizuje pro daného uživatele a kampaň.
     *
     * @param array $user              Data o uživateli, vůči kterému má být odkaz personalizován
     * @param string $body             Tělo e-mailu
     * @param string|null $websiteUrl  URL podvodné stránky (nepovinné)
     * @param int|null $idCampaign     ID kampaně (nepovinné)
     * @return string                  Tělo e-mailu s nahrazenými/personalizovanými odkazy na podvodnou stránku
     */
    private static function personalizeURL($user, $body, $websiteUrl = null, $idCampaign = null) {
      $values = [];

      // Pokud se má nahrazovat proměnná pro URL adresu podvodné stránky...
      if ($websiteUrl != null) {
        $websiteUrl = str_replace('&amp;', '&', $websiteUrl);

        // Pokud je specifikována kampaň, přidat do URL podvodné stránky i identifikátor pro sledování uživatele.
        if ($idCampaign != null) {
          $url = PhishingWebsiteModel::makeWebsiteUrl($websiteUrl, WebsitePrependerModel::makeUserWebsiteId($idCampaign, $user['url']));
        }
        else {
          $url = str_replace(VAR_RECIPIENT_URL, 'id', $websiteUrl);
        }

        $url = Controller::escapeOutput($url);

        array_push($values, $url, $url);
      }
      else {
        $urlLabel = '[URL podvodné stránky]';

        array_push($values, $urlLabel, $urlLabel, '%url%');
      }

      // Nahrazení proměnných v těle e-mailu za požadované hodnoty.
      return str_replace([VAR_URL, VAR_URL_HTML, '%url_%'], $values, $body);
    }


    /**
     * Vrátí zformátovanou hlavičku odesílatele v závislosti na tom, která data byla o odesílateli vyplněna.
     *
     * @param string $senderEmail      E-mail odesílatele
     * @param string|bool $senderName  Jméno odesílatele nebo FALSE, pokud není uvedeno
     * @return string                  Zformátovaná hlavička odesílatele
     */
    public static function formatEmailSender($senderEmail, $senderName = false) {
      return (($senderName) ? $senderName . ' &lt;' . $senderEmail . '&gt;' : $senderEmail);
    }


    /**
     * Nahradí v těle e-mailu symboly nového řádku (\n) za HTML tag odřádkování.
     *
     * @param string $body             Tělo e-mailu
     * @return string                  Tělo e-mailu s nahrazenými novými řádky
     */
    public static function insertHTMLnewLines($body) {
      return str_replace("\n", '<br>', $body);
    }


    /**
     * Vrátí povolené uživatelské tagy včetně jejich náhrad za skutečné HTML tagy.
     *
     * @param bool $withReplacements   TRUE (výchozí), pokud se mají vrátit i náhrady se skutečnými HTML tagy
     * @return string[]                Pole povolených tagů, nebo asociativní pole s tagy včetně jejich náhrad
     */
    public static function getHTMLtags($withReplacements = true) {
      $tags = [
        '\[a href=(https?:\/\/.*?)\](.*?)\[\/a\]' => '<a href="\1" target="_blank" rel="nofollow">\2</a>',
        '\[b\](.*?)\[\/b\]' => '<b>\1</b>',
        '\[i\](.*?)\[\/i\]' => '<i>\1</i>',
        '\[u\](.*?)\[\/u\]' => '<u>\1</u>',
        '\[s\](.*?)\[\/s\]' => '<s>\1</s>',
        '\[br\]' => '<br>'
      ];

      if (!$withReplacements) {
        $tags = array_keys($tags);
      }

      return $tags;
    }


    /**
     * Nahradí v obsahu e-mailu povolené uživatelské tagy za HTML tagy.
     *
     * @param string $body             Tělo e-mailu
     * @return string                  Tělo e-mail včetně HTML tagů
     */
    public static function insertHTMLtags($body) {
      $replacements = self::getHTMLtags();

      foreach ($replacements as $pattern => $replacement) {
        $body = preg_replace("/$pattern/", $replacement, $body);
      }

      return $body;
    }


    /**
     * Vloží do e-mailu (včetně pole odesílatel a předmět) na požadované pozice HTML indicie.
     *
     * @param array $email             Pole s informacemi o e-mailu
     * @param array $emailIndications  Pole obsahující všechny indicie, které mají být do e-mailu vloženy
     * @return array                   Pole s informacemi o e-mailu včetně HTML indicií
     */
    private static function insertHTMLIndications($email, $emailIndications) {
      // Pole nahrazovaných proměnných v hlavičkách e-mailu.
      $emailHeadersVariables = [VAR_INDICATION_SENDER_NAME, VAR_INDICATION_SENDER_EMAIL, VAR_INDICATION_SUBJECT];

      // Vložení HTML indicií do hlaviček a těla e-mailu.
      foreach ($emailIndications as $indication) {
        $htmlTag = [
          '<a href="#indication-' . $indication['id_indication'] . '-text" id="indication-' . $indication['id_indication'] . '" class="indication anchor-link mark-indication" data-indication="' . $indication['id_indication'] . '">',
          '<div class="icons"><div><span data-feather="alert-triangle"></span></div><div><span data-feather="arrow-up-left"></span></div></div></a>'
        ];

        // Nahrazení proměnných za HTML indicie v hlavičce e-mailu - u odesílatele a předmětu.
        if (in_array($indication['expression'], $emailHeadersVariables)) {
          // Získání názvu indexu (jméno odesílatele apod.), ve kterém dojde k úpravě.
          $var = str_replace('%', '', $indication['expression']);

          // Nahrazení proměnné za HTML indicii.
          $email[$var] = $htmlTag[0] . $email[$var] . $htmlTag[1];
        }
        elseif ($indication['expression'] == VAR_URL) {
          // Přidání HTML indicií a zvýraznění všech odkazů na podvodné stránky.
          if ($email['html']) {
            $email['body'] = self::highlightURL($email['body'], $htmlTag[0], $htmlTag[1]);
          }

          $email['body'] = self::highlightURL($email['body'], $htmlTag[0], $htmlTag[1], true);
        }
        else {
          // Nahrazení zbylých promměných za HTML indicie v těle e-mailu.
          $email['body'] = str_replace($indication['expression'], $htmlTag[0] . $indication['expression'] . $htmlTag[1], $email['body']);
        }
      }

      return $email;
    }


    /**
     * Zvýrazní v těle e-mailu odkaz (resp. přidá k němu komponentu Tooltip)
     * s případným dodatečným HTML tagem, který bude odkaz obalovat (např. indicii).
     *
     * @param string $body             Tělo e-mailu
     * @param string $startTag         Otevírací část HTML tagu obalující odkaz (nepovinné)
     * @param string $endTag           Uzavírací část HTML tagu obalující odkaz (nepovinné)
     * @param bool $plainTextLinks     TRUE, pokud se mají zvýraznit i odkazy, které nejsou
     *                                 obaleny HTML tagy, jinak FALSE (výchozí)
     * @return string                  Tělo e-mailu se zvýrazněnými odkazy
     */
    private static function highlightURL($body, $startTag = '', $endTag = '', $plainTextLinks = false) {
      // Zvýraznění odkazu na podvodnou stránku, který není obalen HTML tagem.
      if ($plainTextLinks) {
        $linkRegexp = VAR_URL;
        $linkURL = VAR_URL;
        $linkLabel = VAR_URL;
        $textTooltip = 'podvodnou';
      }
      else {
        $linkRegexp = '\[a href=';

        // Zvýraznění HTML odkazu na podvodnou stránku.
        if (!empty($startTag) && !empty($endTag)) {
          $linkRegexp .= '(' . VAR_URL . '.*?)';
          $linkURL = VAR_URL_HTML;
          $linkLabel = '\2';
          $textTooltip = 'podvodnou';
        }
        else {
          // Zvýraznění HTML odkazu mimo podvodnou stránku.
          $linkRegexp .= '(?!' . VAR_URL . ')(https?:\/\/.*?)';
          $linkURL = '\1';
          $linkLabel = '\2';
          $textTooltip = '';
        }

        $linkRegexp .= '](.*?)\[\/a\]';
      }

      // Komponenta Tooltip, která odkaz zvýrazní a bude jej obalovat.
      $tooltipContent = 'Odkaz na ' . ((!empty($textTooltip)) ? $textTooltip . ' ' : '') . 'stránku:<br><span class=\'text-monospace\'>' . $linkURL . '</span>';
      $tooltapStartTag = '<span class="indication-link" data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="' . $tooltipContent . '">';
      $tooltipEndTag = '</span>';

      return preg_replace('/' . $linkRegexp . '/', $tooltapStartTag . $startTag . $linkLabel . $endTag . $tooltipEndTag, $body);
    }


    /**
     * Doplní a personalizuje konkrétní phishingový e-mail.
     *
     * @param array $phishingEmail           Asociativní pole s daty o phishingovém e-mailu
     * @param array|null $user               Asociativní pole s daty o uživateli, nebo NULL
     * @param bool|array $includeIndications TRUE (výchozí) pokud mají být k e-mailu zahrnuty i indicie pro jeho rozpoznání,
     *                                        FALSE pokud ne anebo pole indicií
     * @param bool $markVariables            Vyznačí proměnné v těle phishingového e-mailu
     * @return array                         Upravený, popř. personalizovaný phishingový e-mail
     */
    public static function personalizePhishingEmail($phishingEmail, $user, $includeIndications = true, $markVariables = false) {
      if ($phishingEmail['html']) {
        // Zvýraznění odkazů vedoucích mimo podvodné stránky.
        if ($markVariables || $includeIndications) {
          $phishingEmail['body'] = self::highlightURL($phishingEmail['body']);
        }

        // Zvýraznění odkazů s proměnnými v náhledu e-mailu.
        if ($markVariables) {
          $phishingEmail['body'] = self::highlightURL($phishingEmail['body'], '<span class="variable">', '</span>');
          $phishingEmail['body'] = self::highlightURL($phishingEmail['body'], '<span class="variable">', '</span>', true);

          $phishingEmail['body'] = self::personalizeURL($user, $phishingEmail['body']);
        }

        // Vložení povolených HTML tagů, pokud se má jednat o HTML e-mail.
        if (isset($phishingEmail['body'])) {
          $phishingEmail['body'] = self::insertHTMLtags($phishingEmail['body']);
        }
      }

      // Vyznačení proměnných v těle e-mailu.
      if ($markVariables) {
        $phishingEmail['body'] = self::markVariablesInEmailBody($phishingEmail['body']);
      }

      // Dodání seznamu indicií - musí být jako první, jinak jej přebijí následující metody.
      if ($includeIndications) {
        // Získání indicií.
        if (is_array($includeIndications)) {
          $emailIndications = $includeIndications;
        }
        else {
          $emailIndications = EmailIndicationsModel::getEmailIndications($phishingEmail['id_email']);
        }

        $phishingEmail['indications'] = $emailIndications;

        // Vložení HTML indicií do e-mailu.
        $phishingEmail = self::insertHTMLIndications($phishingEmail, $emailIndications);
      }

      // Personalizace e-mailu.
      if (isset($user['email']) && isset($user['id_user'])) {
        // Nahrazení proměnné odesílatele za e-mail uživatele (pokud byla proměnná použita).
        $phishingEmail['sender_email'] = self::personalizeEmailSender(
          $phishingEmail['sender_email'], $user['email']
        );

        // Vložení příjemce.
        $phishingEmail['recipient_email'] = $user['email'];

        if ($includeIndications) {
          $user = array_merge($user, UsersModel::getUserFullname($user['username']));

          // Personalizace těla e-mailu.
          $phishingEmail['body'] = self::personalizeEmailBody(
            $user, $phishingEmail['body'], ($phishingEmail['url'] ?? null), ($phishingEmail['id_campaign'] ?? null), ($phishingEmail['date_sent'] ?? null)
          );
        }

        if (isset($phishingEmail['id_campaign'])) {
          // Získání uživatelské reakce na daný e-mail.
          $phishingEmail['user_state'] = CampaignModel::getUserResponse(
            $phishingEmail['id_campaign'], $user['id_user']
          );
        }
      }

      // Zformátování hlavičky odesílatele podle toho, co bylo vyplněno.
      $phishingEmail['sender'] = PhishingEmailModel::formatEmailSender(
        $phishingEmail['sender_email'], $phishingEmail['sender_name']
      );

      if (isset($phishingEmail['body'])) {
        // Vložení odřádkování do těla e-mailu.
        $phishingEmail['body'] = self::insertHTMLnewLines($phishingEmail['body']);
      }

      return $phishingEmail;
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     *
     * @throws UserError
     */
    public function validateData() {
      $this->isNameEmpty();
      $this->isNameTooLong();

      $this->isSenderNameTooLong();

      $this->isSenderEmailEmpty();
      $this->isSenderEmailTooLong();
      $this->isSenderEmailValid();
      $this->isSenderEmailDomainValid();

      $this->isSubjectEmpty();
      $this->isSubjectTooLong();

      $this->isBodyEmpty();
      $this->isBodyTooLong();
      $this->containBodyPhishingWebsiteVariable();
    }


    /**
     * Ověří, zdali byl vyplněn název e-mailu.
     *
     * @throws UserError
     */
    private function isNameEmpty() {
      if (empty($this->name)) {
        throw new UserError('Není vyplněn název e-mailu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný název e-mailu není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isNameTooLong() {
      if (mb_strlen($this->name) > $this->inputsMaxLengths['name']) {
        throw new UserError('Název e-mailu je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadané jméno odesílatele e-mailu není příliš dlouhé.
     *
     * @throws UserError
     */
    private function isSenderNameTooLong() {
      if (mb_strlen($this->senderName) > $this->inputsMaxLengths['sender-name']) {
        throw new UserError('Jméno odesílatele je příliš dlouhé.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byl vyplněn e-mail odesílatele.
     *
     * @throws UserError
     */
    private function isSenderEmailEmpty() {
      if (empty($this->senderEmail)) {
        throw new UserError('Není vyplněn e-mail odesílatele.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný e-mail odesílatele není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isSenderEmailTooLong() {
      if (mb_strlen($this->senderEmail) > $this->inputsMaxLengths['sender-email']) {
        throw new UserError('E-mail odesílatele je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je zadaný e-mail odesílatele validní.
     *
     * @throws UserError
     */
    private function isSenderEmailValid() {
      if (!filter_var($this->senderEmail, FILTER_VALIDATE_EMAIL) && $this->senderEmail != VAR_RECIPIENT_EMAIL) {
        throw new UserError('E-mail odesílatele je v nesprávném formátu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali existuje doména použitá v zadaném e-mailu odesílatele.
     *
     * @throws UserError
     */
    private function isSenderEmailDomainValid() {
      $domain = get_email_part($this->senderEmail, 'domain');

      if (gethostbyname($domain) == $domain) {
        throw new UserError('Doména použitá v e-mailu odesílatele neexistuje.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byl vyplněn předmět e-mailu.
     *
     * @throws UserError
     */
    private function isSubjectEmpty() {
      if (empty($this->subject)) {
        throw new UserError('Není vyplněn předmět e-mailu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný předmět e-mailu není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isSubjectTooLong() {
      if (mb_strlen($this->subject) > $this->inputsMaxLengths['subject']) {
        throw new UserError('Předmět e-mailu je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali bylo vyplněno tělo e-mailu.
     *
     * @throws UserError
     */
    private function isBodyEmpty() {
      if (empty($this->body)) {
        throw new UserError('Není vyplněno tělo e-mailu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadané tělo e-mailu není příliš dlouhé.
     *
     * @throws UserError
     */
    private function isBodyTooLong() {
      if (mb_strlen($this->body) > $this->inputsMaxLengths['body']) {
        throw new UserError('Obsah e-mailu je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je v zadaném těle e-mailu obsažena proměnná, která bude obsahovat odkaz na podvodnou stránku.
     *
     * @throws UserError
     */
    private function containBodyPhishingWebsiteVariable() {
      if (mb_strpos($this->body, VAR_URL) === false) {
        throw new UserError(
          'V těle e-mailu chybí použití proměnné "' . VAR_URL . '" pro umístění odkazu na podvodnou stránku.',
          MSG_ERROR
        );
      }
    }


    /**
     * Ověří, zdali administrátor nechce skrýt e-mail, který je již použit v některé z kampaní
     * (některým ze správců testů).
     *
     * @param int $idEmail             ID e-mailu
     * @throws UserError
     */
    private function isPhishingEmailUsedByTestManager($idEmail) {
      if ($this->hidden == 1 && $this->getCountOfUsePhishingEmailByTestManager($idEmail) > 0) {
        throw new UserError(
          'Nelze skrýt e-mail, který je použit v existující kampani některým ze správců testů.',MSG_ERROR
        );
      }
    }
  }

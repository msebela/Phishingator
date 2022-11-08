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
     * @var int         Proměnná uchovávají informaci o tom, zdali je podvodný e-mail skryt pro správce testů
     *                  (1 pokud ano, jinak 0).
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
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function load($data) {
      parent::load($data);

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
              SELECT `id_email`, `name`, `sender_name`, `sender_email`, `subject`, `body`, `hidden`
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

      return Database::queryMulti('
              SELECT `id_email`, phg_emails.id_by_user, `name`, `subject`, phg_emails.date_added, `hidden`,
              `username`, phg_emails.date_added,
              DATE_FORMAT(phg_emails.date_added, "%e. %c. %Y") AS date_added_formatted
              FROM `phg_emails`
              JOIN `phg_users`
              ON phg_emails.id_by_user = phg_users.id_user
              WHERE phg_emails.visible = 1
              ' . $whereFilter . '
              ORDER BY `id_email` DESC
      ');
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

      Logger::info('Vkládání nového podvodného e-mailu.', $email);

      Database::insert($this->dbTableName, $email);

      return Database::getLastInsertId();
    }


    /**
     * Upraví zvolený e-mail.
     *
     * @param int $id                  ID e-mailu
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function updatePhishingEmail($id) {
      $email = $this->makePhishingEmail();

      $this->isPhishingEmailUsedByTestManager($id);

      Logger::info('Úprava existujícího podvodného e-mailu.', $email);

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
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
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
        Logger::warning('Snaha o smazání neexistujícího podvodného e-mailu.', $id);

        throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
      }

      Logger::info('Smazání existujícího podvodného e-mailu.', $id);
    }


    /**
     * Vrátí pole proměnných, které se mohou vyskytovat v těle e-mailu.
     *
     * @return array                   Pole proměnných.
     */
    private static function getEmailBodyVariables() {
      return [
        VAR_RECIPIENT_USERNAME, VAR_RECIPIENT_EMAIL,
        VAR_DATE_CZ, VAR_DATE_EN,
        VAR_URL
      ];
    }


    /**
     * Pomocí HTML vyznačí v těle e-mailu proměnné a toto upravené tělo e-mailu vrátí.
     *
     * @param string $body             Tělo e-mailu, ve kterém mají být vyznačeny proměnné.
     * @return string                  Tělo e-mailu s vyznačenými proměnnými.
     */
    private static function markVariablesInEmailBody($body) {
      $variables = self::getEmailBodyVariables();

      /* Označení všech proměnných HTML tagem. */
      foreach ($variables as $variable) {
        $body = str_replace($variable, '<code>' . $variable . '</code>', $body);
      }

      return $body;
    }


    /**
     * Personalizuje odesílatele e-mailu tak, aby byl jako odesílatel uveden e-mail příjemce.
     *
     * @param string $senderEmail      E-mail odesílatele.
     * @param string $userEmail        E-mail příjemce.
     * @return string                  Pozměněný e-mail odesílatele.
     */
    private static function personalizeEmailSender($senderEmail, $userEmail) {
      if (mb_strpos($senderEmail, VAR_RECIPIENT_EMAIL) !== false) {
        return str_replace(VAR_RECIPIENT_EMAIL, $userEmail, $senderEmail);
      }

      return $senderEmail;
    }


    /**
     * Personalizuje tělo e-mailu podle vybraného uživatele.
     *
     * @param array $recipient         ID/E-mail uživatele, vůči kterému má být e-mail personalizován.
     * @param string $body             Tělo e-mailu.
     * @param string|null $websiteUrl  URL podvodné stránky (nepovinný parametr).
     * @param int|null $idCampaign     ID kampaně (nepovinný parametr).
     * @return string|null             Personalizované tělo e-mailu nebo NULL (při nenalezení záznamů).
     */
    public static function personalizeEmailBody($recipient, $body, $websiteUrl = null, $idCampaign = null) {
      $usersModel = new UsersModel();
      $user = '';

      if (!empty($recipient['email'])) {
        $user = $usersModel->getUserByEmail($recipient['email']);
      }
      elseif (!empty($recipient['id'])) {
        $user = $usersModel->getUser($recipient['id']);
      }

      /* Pokud se nepodařilo uživatele nalézt, vrátíme NULL. */
      if (empty($user)) {
        return null;
      }

      /* Hodnoty, které se budou dosazovat za použité proměnné. */
      $values = [
        $user['username'], $user['email'],
        date('j. n. Y'), date('Y-m-d')
      ];

      /* Pokud byl vyplněn i tento nepovinný parametr, nahrazovat i proměnou pro URL podvodné stránky. */
      if ($websiteUrl != null) {
        $url = $websiteUrl;

        /* Jestliže je specifikována i kampaň, vložit do URL podvodné stránky i identifikátor pro sledování uživatele. */
        if ($idCampaign != null) {
          $url .= '?' . WebsitePrependerModel::makeWebsiteUrl($idCampaign, $user['url']);
        }

        $values[] = $url;
      }
      else {
        $values[] = '&lt;URL podvodné stránky&gt;';
      }

      /* Nahrazení proměnných v těle e-mailu za požadované hodnoty. */
      return str_replace(self::getEmailBodyVariables(), $values, $body);
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
     * Vloží do těla e-mailu nové řádky (HTML tag) tam, kde uživatel zamýšlel (tedy místo \n).
     *
     * @param string $body             Tělo e-mailu
     * @return string                  Tělo e-mailu s nahrazenými novými řádky
     */
    private static function insertHTMLnewLines($body) {
      return str_replace("\n", '<br>', $body);
    }


    /**
     * Vloží do e-mailu (včetně pole odesílatel a předmět) na požadované pozice HTML indicie.
     *
     * @param array $email             Pole s informacemi o e-mailu.
     * @param array $emailIndications  Pole obsahující všechny indicie, které mají být do e-mailu vloženy.
     * @return array                   Pole s informacemi o e-mailu včetně HTML indicií.
     */
    private static function insertHTMLIndications($email, $emailIndications) {
      // Pole nahrazovaných proměnných v hlavičkách e-mailu.
      $emailVariablesIndications = [VAR_INDICATION_SENDER_NAME, VAR_INDICATION_SENDER_EMAIL, VAR_INDICATION_SUBJECT];

      // Vložení HTML indicií do hlaviček a těla e-mailu.
      foreach ($emailIndications as $indication) {
        $htmlTag = [
          '<a href="#indication-' . $indication['id_indication'] . '-text" id="indication-' . $indication['id_indication'] . '" class="indication anchor-link" onclick="markIndication(' . $indication['id_indication'] . ')" onmouseover="markIndication(' . $indication['id_indication'] . ')" onmouseout="markIndication(' . $indication['id_indication'] . ')">',
          '<div class="icons"><div><span data-feather="alert-triangle"></span></div><div><span data-feather="arrow-up-left"></span></div></div></a>'
        ];

        if (in_array($indication['expression'], $emailVariablesIndications)) {
          // Získání názvu pole (jméno odesílatele apod.), ve kterém dojde k úpravě.
          $var = str_replace('%', '', $indication['expression']);

          // Nahrazení proměnné za HTML.
          $email[$var] = $htmlTag[0] . $email[$var] . $htmlTag[1];
        }
        else {
          // Nahrazení promměných za HTML v těle e-mailu.
          $email['body'] = str_replace(
            $indication['expression'],
            $htmlTag[0] . $indication['expression'] . $htmlTag[1],
            $email['body']
          );
        }
      }

      return $email;
    }


    /**
     * Doplní a personalizuje konkrétní phishingový e-mail.
     *
     * @param array $phishingEmail           Asociativní pole s daty o phishingovém e-mailu
     * @param array $user                    Asociativní pole s daty o uživateli (ID a e-mail)
     * @param bool|array $includeIndications TRUE (výchozí) pokud mají být k e-mailu zahrnuty i indicie pro jeho rozpoznání,
     *                                        FALSE pokud ne anebo pole indicií
     * @param bool $markVariables            Vyznačí proměnné v těle phishingového e-mailu
     * @return array                         Upravený, popř. personalizovaný phishingový e-mail
     */
    public static function personalizePhishingEmail($phishingEmail, $user, $includeIndications = true, $markVariables = false) {
      /* Dodání seznamu indicií - musí být jako první, jinak jej přebijí následující metody. */
      if ($includeIndications) {
        /* Získání indicií. */
        if (is_array($includeIndications)) {
          $emailIndications = $includeIndications;
        }
        else {
          $emailIndications = EmailIndicationsModel::getEmailIndications($phishingEmail['id_email']);
        }

        $phishingEmail['indications'] = $emailIndications;

        /* Vložení HTML indicií do e-mailu. */
        $phishingEmail = self::insertHTMLIndications($phishingEmail, $emailIndications);
      }

      /* Zformátování hlavičky odesílatele podle toho, co bylo vyplněno. */
      $phishingEmail['sender'] = PhishingEmailModel::formatEmailSender(
        $phishingEmail['sender_email'], $phishingEmail['sender_name']
      );

      if (isset($phishingEmail['body'])) {
        /* Vložení odřádkování do těla e-mailu. */
        $phishingEmail['body'] = self::insertHTMLnewLines($phishingEmail['body']);
      }

      /* Personalizace e-mailu. */
      if (isset($user['email']) && isset($user['id_user'])) {
        /* Nahrazení proměnné odesílatele za e-mail uživatele (pokud byla proměnná použita). */
        $phishingEmail['sender_email'] = self::personalizeEmailSender(
          $phishingEmail['sender_email'], $user['email']
        );

        /* Vložení příjemce. */
        $phishingEmail['recipient_email'] = $user['email'];

        if ($includeIndications) {
          /* Personalizace těla e-mailu. */
          $phishingEmail['body'] = self::personalizeEmailBody(
            ['id' => $user['id_user']], $phishingEmail['body'], ($phishingEmail['url'] ?? null)
          );
        }

        if (isset($phishingEmail['id_campaign'])) {
          /* Získání uživatelské reakce na daný e-mail. */
          $phishingEmail['user_state'] = CampaignModel::getUserReaction(
            $phishingEmail['id_campaign'], $user['id_user']
          );
        }
      }

      /* Vyznačení proměnných v těle e-mailu. */
      if ($markVariables) {
        $phishingEmail['body'] = self::markVariablesInEmailBody($phishingEmail['body']);
      }

      return $phishingEmail;
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function validateData() {
      $this->isNameEmpty();
      $this->isNameTooLong();

      $this->isSenderNameTooLong();

      $this->isSenderEmailEmpty();
      $this->isSenderEmailTooLong();
      $this->isSenderEmailValid();

      $this->isSubjectEmpty();
      $this->isSubjectTooLong();

      $this->isBodyEmpty();
      $this->isBodyTooLong();
      $this->containBodyPhishingWebsiteVariable();
    }


    /**
     * Ověří, zdali byl vyplněn název e-mailu.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isNameEmpty() {
      if (empty($this->name)) {
        throw new UserError('Není vyplněn název e-mailu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný název e-mailu není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isNameTooLong() {
      if (mb_strlen($this->name) > $this->inputsMaxLengths['name']) {
        throw new UserError('Název e-mailu je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadané jméno odesílatele e-mailu není příliš dlouhé.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isSenderNameTooLong() {
      if (mb_strlen($this->senderName) > $this->inputsMaxLengths['sender-name']) {
        throw new UserError('Jméno odesílatele je příliš dlouhé.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byl vyplněn e-mail odesílatele.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isSenderEmailEmpty() {
      if (empty($this->senderEmail)) {
        throw new UserError('Není vyplněn e-mail odesílatele.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný e-mail odesílatele není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isSenderEmailTooLong() {
      if (mb_strlen($this->senderEmail) > $this->inputsMaxLengths['sender-email']) {
        throw new UserError('E-mail odesílatele je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je zadaný e-mail odesílatele validní.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isSenderEmailValid() {
      if (!filter_var($this->senderEmail, FILTER_VALIDATE_EMAIL) && $this->senderEmail != VAR_RECIPIENT_EMAIL) {
        throw new UserError('E-mail odesílatele je v nesprávném formátu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byl vyplněn předmět e-mailu.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isSubjectEmpty() {
      if (empty($this->subject)) {
        throw new UserError('Není vyplněn předmět e-mailu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný předmět e-mailu není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isSubjectTooLong() {
      if (mb_strlen($this->subject) > $this->inputsMaxLengths['subject']) {
        throw new UserError('Předmět e-mailu je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali bylo vyplněno tělo e-mailu.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isBodyEmpty() {
      if (empty($this->body)) {
        throw new UserError('Není vyplněno tělo e-mailu.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadané tělo e-mailu není příliš dlouhé.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isBodyTooLong() {
      if (mb_strlen($this->body) > $this->inputsMaxLengths['body']) {
        throw new UserError('Obsah e-mailu je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je v zadaném těle e-mailu obsažena proměnná, která bude obsahovat odkaz na podvodnou stránku.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
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
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isPhishingEmailUsedByTestManager($idEmail) {
      if ($this->hidden == 1 && $this->getCountOfUsePhishingEmailByTestManager($idEmail) > 0) {
        throw new UserError(
          'Nelze skrýt e-mail, který je použit v existující kampani některým ze správců testů.',MSG_ERROR
        );
      }
    }
  }

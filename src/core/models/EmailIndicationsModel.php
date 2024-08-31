<?php
  /**
   * Třída sloužící k získávání informací o indiciích u podvodných e-mailů,
   * k přidávání nových indicií, k úpravě těch existujících a dalším souvisejícím operacím.
   *
   * @author Martin Šebela
   */
  class EmailIndicationsModel extends FormModel {
    /**
     * @var int         ID cvičného podvodného e-mailu, ke kterému indicie patří.
     */
    protected $idEmail;

    /**
     * @var string      Tělo cvičného podvodného e-mailu v plain textu, které slouží pouze ke kontrole,
     *                  jestli obsahuje uživatelem vyplněnou indicii.
     */
    public $email;

    /**
     * @var int         Pořadí indicie při výpisu v seznamu indicií u podvodného e-mailu.
     */
    protected $position;

    /**
     * @var string      Výraz (podezřelý text), který představuje indicii k rozpoznání phishingu.
     */
    protected $expression;

    /**
     * @var string      Název indicie.
     */
    protected $title;

    /**
     * @var string      Popis indicie.
     */
    protected $description;


    /**
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError
     */
    public function load($data) {
      parent::load($data);

      $this->idEmail = $_GET['id'];
    }


    /**
     * Připraví novou indicii pro podvodný e-mail v závislosti na vyplněných datech a vrátí ji formou pole.
     *
     * @return array                   Pole obsahující data o indicii
     */
    private function makeEmailIndication() {
      return [
        'id_email' => $this->idEmail,
        'position' => $this->position,
        'expression' => $this->expression,
        'title' => $this->title,
        'description' => $this->description
      ];
    }


    /**
     * Uloží do instance a zároveň vrátí (z databáze) informace o zvolené indicii.
     *
     * @param int $id                  ID indicie
     * @return array                   Pole obsahující informace o indicii
     */
    public function getEmailIndication($id) {
      $this->dbRecordData = Database::querySingle('
                              SELECT `position`, `expression`, `title`, `description`
                              FROM `phg_emails_indications`
                              WHERE `id_indication` = ?
                              AND `visible` = 1
                           ', $id);

      return $this->dbRecordData;
    }


    /**
     * Vrátí seznam všech indicií přidaných ke konkrétnímu cvičnému podvodnému e-mailu.
     *
     * @param int $idEmail             ID podvodného e-mailu
     * @return mixed                   Pole indicií s informacemi o každé z nich
     */
    public static function getEmailIndications($idEmail) {
      return Database::queryMulti('
              SELECT `id_indication`, `position`, `expression`, `title`, `description`
              FROM `phg_emails_indications`
              WHERE `id_email` = ?
              AND `visible` = 1
              ORDER BY `position`, `id_indication`
      ', $idEmail);
    }


    /**
     * Vrátí počet indicií přidaných ke konkrétnímu cvičnému podvodnému e-mailu.
     *
     * @param int $idEmail             ID podvodného e-mailu
     * @return int                     Počet indicií
     */
    public static function getCountEmailIndications($idEmail) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_emails_indications`
              WHERE `id_email` = ?
              AND `visible` = 1
      ', $idEmail);
    }


    /**
     * Vrátí název CSS třídy v závislosti na počtu indicií.
     *
     * @param int $sumIndications      Počet indicií u e-mailu
     * @return string|null             Název CSS třídy
     */
    public static function getColorByCountIndications($sumIndications) {
      $color = MSG_CSS_ERROR;

      if (is_numeric($sumIndications)) {
        if ($sumIndications > 2) {
          $color = 'light';
        }
        elseif ($sumIndications > 0) {
          $color = MSG_CSS_WARNING;
        }
      }

      return $color;
    }


    /**
     * Ověří, zdali již v databázi pro daný e-mail existuje konkrétní indicie.
     *
     * @param int $idEmail             ID podvodného e-mailu
     * @param string $expression       Výraz představující indicii
     * @param int $idIndication        ID indicie (nepovinný parametr) pro vyloučení právě upravované indicie.
     * @param bool $findSubstring      TRUE, pokud se má indicie vyhledávat jako podřetězec v ostatních indicích, jinak FALSE (výchozí)
     * @return mixed                   0 pokud indicie v databázi zatím neexistuje, jinak 1.
     */
    public static function existEmailIndication($idEmail, $expression, $idIndication = 0, $findSubstring = false) {
      if ($findSubstring) {
        $expression = '%' . $expression . '%';
      }

      return Database::queryCount('
               SELECT COUNT(*)
               FROM `phg_emails_indications`
               WHERE `id_indication` != ?
               AND `id_email` = ?
               AND `expression` LIKE ?
               AND `visible` = 1
      ', [$idIndication, $idEmail, $expression]);
    }


    /**
     * Vloží do databáze novou indicii.
     *
     * @throws UserError
     */
    public function insertEmailIndication() {
      $indication = $this->makeEmailIndication();

      $indication['id_by_user'] = PermissionsModel::getUserId();
      $indication['date_added'] = date('Y-m-d H:i:s');

      $this->isExpressionUnique();
      $this->isNotExpressionInExpressions();

      Logger::info('New phishing sign added.', $indication);

      Database::insert($this->dbTableName, $indication);
    }


    /**
     * Upraví zvolenou indicii.
     *
     * @param int $id                  ID indicie
     * @throws UserError
     */
    public function updateEmailIndication($id) {
      $indication = $this->makeEmailIndication();

      $this->isExpressionUnique($id);
      $this->isNotExpressionInExpressions($id);

      Logger::info('Phishing sign modified.', $indication);

      Database::update(
        $this->dbTableName,
        $indication,
        'WHERE `id_indication` = ? AND `visible` = 1',
        $id
      );
    }


    /**
     * Odstraní (resp. deaktivuje) indicii z databáze.
     *
     * @param int $id                  ID indicie
     * @throws UserError
     */
    public function deleteEmailIndication($id) {
      $result = Database::update(
        'phg_emails_indications',
        ['visible' => 0],
        'WHERE `id_indication` = ? AND `visible` = 1',
        $id
      );

      if ($result == 0) {
        Logger::warning('Attempt to delete a non-existent phishing sign.', $id);

        throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
      }

      Logger::info('Phishing sign deleted.', $id);
    }


    /**
     * Vrátí pole proměnných, které se mohou používat pro lokalizaci indicií.
     *
     * @return array                   Pole proměnných.
     */
    private function getEmailIndicationsVariables() {
      return [
        VAR_INDICATION_SENDER_NAME, VAR_INDICATION_SENDER_EMAIL,
        VAR_INDICATION_SUBJECT
      ];
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     *
     * @throws UserError
     */
    public function validateData() {
      $this->isPositionEmpty();
      $this->isPositionNumeric();
      $this->isPositionInLimit();

      $this->isExpressionEmpty();
      $this->isExpressionTooLong();
      $this->existExpressionInText();

      $this->isTitleEmpty();
      $this->isTitleTooLong();

      $this->isDescriptionTooLong();
    }


    /**
     * Ověří, zdali bylo vyplněno pořadí indicie.
     *
     * @throws UserError
     */
    private function isPositionEmpty() {
      if (empty($this->position)) {
        throw new UserError('Pořadí indicie nebylo vyplněno.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je pořadí indicie zadáno číselně.
     *
     * @throws UserError
     */
    private function isPositionNumeric() {
      if (!is_numeric($this->position)) {
        throw new UserError('Pořadí indicie není zadáno číselně.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je pořadí indicie v číselném intervalu.
     *
     * @throws UserError
     */
    private function isPositionInLimit() {
      if ($this->position < 0 || $this->position > 100) {
        throw new UserError('Pořadí indicie je mimo povolený interval.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byl vyplněn podezřelý text k rozpoznání phishingu.
     *
     * @throws UserError
     */
    private function isExpressionEmpty() {
      if (empty($this->expression)) {
        throw new UserError('Není vyplněn podezřelý text.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný podezřelý text není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isExpressionTooLong() {
      if (mb_strlen($this->expression) > $this->inputsMaxLengths['expression']) {
        throw new UserError('Podezřelý text je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali se podezřelý text opravdu nalézá v těle e-mailu (popř. jestli se jedná o proměnnou).
     *
     * @throws UserError
     */
    private function existExpressionInText() {
      if (mb_strpos($this->email, $this->expression) === false
          && !in_array($this->expression, self::getEmailIndicationsVariables())) {
        throw new UserError('Podezřelý text nebyl v těle e-mailu nalezen.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali podezřelý text není již mezi ostatními indiciemi (tzn. jestli je unikátní).
     *
     * @param int $idIndication        ID indicie (nepovinný parametr), aby se při úpravě vyloučila upravovaná indicie
     * @throws UserError
     */
    private function isExpressionUnique($idIndication = 0) {
      if (self::existEmailIndication($this->idEmail, $this->expression, $idIndication) > 0) {
        throw new UserError('Stejný podezřelý text je již veden mezi ostatními indiciemi.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali podezřelý text není obsažen jako podřetězec v některé z jiných indicií.
     *
     * @param int $idIndication        ID indicie (nepovinný parametr), aby se při úpravě vyloučila upravovaná indicie
     * @throws UserError
     */
    private function isNotExpressionInExpressions($idIndication = 0) {
      if (self::existEmailIndication($this->idEmail, $this->expression, $idIndication, true) > 0) {
        throw new UserError('Podezřelý text je obsažen v některé z jiných indicií.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byl vyplněn název indicie.
     *
     * @throws UserError
     */
    private function isTitleEmpty() {
      if (empty($this->title)) {
        throw new UserError('Není vyplněn název indicie.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný název indicie není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isTitleTooLong() {
      if (mb_strlen($this->title) > $this->inputsMaxLengths['title']) {
        throw new UserError('Název indicie je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali popis zadaný u indicie není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isDescriptionTooLong() {
      if (mb_strlen($this->description) > $this->inputsMaxLengths['description']) {
        throw new UserError('Popis indicie je příliš dlouhý.', MSG_ERROR);
      }
    }
  }

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
     * @var string      Výraz (část textu), který představuje indicii k rozpoznání phishingu.
     */
    protected $expression;

    /**
     * @var string      Krátký název indicie.
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
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
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
                              SELECT `expression`, `title`, `description`
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
              SELECT `id_indication`, `expression`, `title`, `description`
              FROM `phg_emails_indications`
              WHERE `id_email` = ?
              AND `visible` = 1
              ORDER BY `id_indication`
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
          $color = MSG_CSS_SUCCESS;
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
     * @return mixed                   0 pokud indicie v databázi zatím neexistuje, jinak 1.
     */
    public static function existEmailIndication($idEmail, $expression, $idIndication = 0) {
      return Database::queryCount('
               SELECT COUNT(*)
               FROM `phg_emails_indications`
               WHERE `id_indication` != ?
               AND `id_email` = ?
               AND `expression` = ?
               AND `visible` = 1
      ', [$idIndication, $idEmail, $expression]);
    }


    /**
     * Vloží do databáze novou indicii.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function insertEmailIndication() {
      $indication = $this->makeEmailIndication();

      $indication['id_by_user'] = PermissionsModel::getUserId();
      $indication['date_added'] = date('Y-m-d H:i:s');

      $this->isExpressionUnique();

      Logger::info('Vkládání nové indicie.', $indication);

      Database::insert($this->dbTableName, $indication);
    }


    /**
     * Upraví zvolenou indicii.
     *
     * @param int $id                  ID indicie
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function updateEmailIndication($id) {
      $indication = $this->makeEmailIndication();

      $this->isExpressionUnique($id);

      Logger::info('Úprava existující indicie.', $indication);

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
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function deleteEmailIndication($id) {
      $result = Database::update(
        'phg_emails_indications',
        ['visible' => 0],
        'WHERE `id_indication` = ? AND `visible` = 1',
        $id
      );

      if ($result == 0) {
        Logger::warning('Snaha o smazání neexistující indicie.', $id);

        throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
      }

      Logger::info('Smazání existující indicie.', $id);
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
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function validateData() {
      $this->isExpressionEmpty();
      $this->isExpressionTooLong();
      $this->existExpressionInText();

      $this->isTitleEmpty();
      $this->isTitleTooLong();

      $this->isDescriptionTooLong();
    }


    /**
     * Ověří, zdali byla vyplněna indicie k rozpoznání phishingu.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isExpressionEmpty() {
      if (empty($this->expression)) {
        throw new UserError('Není vyplněna indicie.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaná indicie není příliš dlouhá.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isExpressionTooLong() {
      if (mb_strlen($this->expression) > $this->inputsMaxLengths['expression']) {
        throw new UserError('Popsaná indicie je příliš dlouhá.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali se výraz představující indicii opravdu nalézá v těle e-mailu (popř. nebo jestli se jedná
     * o povolenou proměnnou).
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function existExpressionInText() {
      if (mb_strpos($this->email, $this->expression) === false
          && !in_array($this->expression, self::getEmailIndicationsVariables())) {
        throw new UserError('Popsaná indicie nebyla v těle e-mailu nalezena.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali výraz představující indicii není již mezi ostatními indiciemi (tzn. jestli je unikátní).
     *
     * @param int $idIndication        ID indicie (nepovinný parametr), aby se při úpravě vyloučila upravovaná indicie.
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isExpressionUnique($idIndication = 0) {
      if (self::existEmailIndication($this->idEmail, $this->expression, $idIndication) > 0) {
        throw new UserError('Popsaná indicie je již jednou vedena mezi ostatními indiciemi.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali byl vyplněn název indicie.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isTitleEmpty() {
      if (empty($this->title)) {
        throw new UserError('Není vyplněn nadpis indicie.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný název indicie není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isTitleTooLong() {
      if (mb_strlen($this->title) > $this->inputsMaxLengths['title']) {
        throw new UserError('Nadpis indicie je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali popis zadaný u indicie není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isDescriptionTooLong() {
      if (mb_strlen($this->description) > $this->inputsMaxLengths['description']) {
        throw new UserError('Popis indicie je příliš dlouhý.', MSG_ERROR);
      }
    }
  }

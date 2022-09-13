<?php
  /**
   * Třída řešící přihlašování a odhlašování uživatelů týkající se přijímání
   * cvičných podvodných zpráv.
   *
   * @author Martin Šebela
   */
  class ParticipationModel extends FormModel {
    /**
     * @var int         Promměná uchovávající informaci o tom, zdali je uživatel dobrovolně přihlášen k odebírání
     *                  cvičných phishingových zpráv (1 pokud ano, jinak 0).
     */
    protected $recieveEmail;

    /**
     * @var int         Proměnná uchovávající stav o tom, zdali uživatel zaškrtl, že si přeje nastavit limit
     *                  pro příjem cvičných podvodných zpráv (1 pokud ano, jinak 0).
     */
    protected $emailLimitCheckbox;

    /**
     * @var int         Maximální počet cvičných podvodných zpráv, které chce uživatel přijmout.
     */
    protected $emailLimit;


    /**
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function load($data) {
      parent::load($data);

      $this->recieveEmail = (empty($this->recieveEmail) ? 0 : 1);
      $this->emailLimitCheckbox = (empty($this->emailLimitCheckbox) ? 0 : 1);
    }


    /**
     * Připraví nastavení odebírání cvičných podvodných zpráv v závislosti na vyplněných datech a vrátí ho formou pole.
     *
     * @return array                   Pole obsahující data o nastavení odebírání cvičných podvodných zpráv.
     */
    private function makeParticipation() {
      /* Pokud nebyl vyplněn limit, tak se do databáze vloží NULL (nenastaven). */
      $this->emailLimit = (empty($this->emailLimit) ? NULL : $this->emailLimit);

      /* Pokud uživatel nastavil početní limit e-mailů na nulu a zároveň se chce účastnit,
         pak jdou tyto dvě tvrzení proti sobě a účastnit se pravděpodobně nechce. */
      if ($this->recieveEmail == 1 && $this->emailLimitCheckbox == 1 && is_null($this->emailLimit)) {
        $this->recieveEmail = 0;
        $this->emailLimit = NULL;
      }

      /* Pokud uživatel nastavil početní limit e-mailů, ale související checkbox odškrtl,
         tak pravděpodobně limit neplatí, nastavíme jej tedy na NULL (nenastaven). */
      if ($this->emailLimitCheckbox == 0 && $this->emailLimit > 0) {
        $this->emailLimit = NULL;
      }

      return [
        'recieve_email' => $this->recieveEmail,
        'email_limit' => $this->emailLimit
      ];
    }


    /**
     * Uloží do instance a zároveň vrátí (z databáze) informace o nastavení odebírání cvičných podvodných zpráv
     * pro konkrétního uživatele.
     *
     * @param int $id                  ID uživatele
     * @return array                   Pole obsahující informace o nastavení odebírání cvičných podvodných zpráv
     */
    public function getParticipation($id) {
      $this->dbRecordData = Database::querySingle('
              SELECT `recieve_email`, `email_limit`
              FROM `phg_users`
              WHERE `id_user` = ?
              AND `visible` = 1
      ', $id);

      return $this->dbRecordData;
    }


    /**
     * Upraví nastavení odebírání cvičných podvodných zpráv pro konkrétního uživatele.
     *
     * @param int $idUser              ID uživatele
     */
    public function updateParticipation($idUser) {
      $participation = $this->makeParticipation();

      $query = '
        UPDATE `phg_users`
        SET `recieve_email` = :recieve_email, `email_limit` = :email_limit
        WHERE `id_user` = :id_user
        AND `visible` = 1
      ';

      $args = [
        ':recieve_email' => $participation['recieve_email'],
        ':email_limit' => $participation['email_limit'],
        ':id_user' => $idUser
      ];

      Logger::info('Úprava nastavení odebírání cvičných podvodných zpráv.', $participation);

      Database::queryAffectedRows($query, $args);

      $this->logParticipation($idUser, $participation['recieve_email']);
    }


    /**
     * Vloží do databáze záznam o změně nastavení týkající se přihlášení k odebírání cvičných podvodných zpráv.
     *
     * @param int $idUser              ID uživatele
     * @param int $result              1 pokud se uživatel přihlásil k odebírání cvičných podvodných zpráv, jinak 0.
     */
    private function logParticipation($idUser, $result) {
      $record = [
        'id_user' => $idUser,
        'date_participation' => date('Y-m-d H:i:s'),
        'logged' => $result
      ];

      Database::insert('phg_users_participation_log', $record);
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function validateData() {
      $this->isEmailLimitEmpty();
      $this->isEmailLimitNegative();
      $this->isEmailLimitTooHigh();

      $this->isRecieveAndLimitValid();
    }


    /**
     * Ověří, zdali byl vyplněn limit, pokud byl zároveň zaškrtnou odpovídají checkbox pro nastavení
     * limit přijatých cvičných podvodných e-mailů.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isEmailLimitEmpty() {
      if ($this->emailLimitCheckbox && !is_numeric($this->emailLimit) && !is_null($this->emailLimit)) {
        throw new UserError('Není vyplněn zbývající počet cvičných phishingových zpráv.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali není zadaný limit záporný.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isEmailLimitNegative() {
      if ($this->emailLimitCheckbox && $this->emailLimit < 0) {
        throw new UserError('Zbývající počet cvičných phishingových zpráv nemůže být záporný.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali není zadaný limit příliš velký.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isEmailLimitTooHigh() {
      if ($this->emailLimitCheckbox && $this->emailLimit > 1000) {
        throw new UserError('Zbývající počet cvičných phishingových zpráv je příliš velký.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali se uživatel nepokouší zadat limit tehdy, když není přihlášen k odebírání cvičných podvodných zpráv.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isRecieveAndLimitValid() {
      if ($this->recieveEmail == 0 && $this->emailLimit) {
        throw new UserError('Nelze nastavit zbývající počet cvičných phishingových zpráv, když není uživatel zapojen do dobrovolného programu.', MSG_ERROR);
      }
    }
  }

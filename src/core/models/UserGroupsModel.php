<?php
  /**
   * Třída sloužící k získávání informací o skupinách uživatelů,
   * k přidávání nových skupin, k úpravě těch existujících a dalším
   * souvisejícím operacím.
   *
   * @author Martin Šebela
   */
  class UserGroupsModel extends FormModel {
    /**
     * @var string      Název skupiny
     */
    protected $name;

    /**
     * @var string      Popis skupiny
     */
    protected $description;

    /**
     * @var int         Oprávnění skupiny
     */
    protected $role;

    /**
     * @var string      Povolené skupiny z LDAP
     */
    protected $ldapGroups;

    /**
     * @var int         ID rodičovské skupiny
     */
    protected $idParentGroup;

    public function getName() {
      return $this->name;
    }


    /**
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function load($data) {
      parent::load($data);

      // Odstranění vícenásobných oddělovačů ze seznamu povolených LDAP skupin.
      $this->ldapGroups = preg_replace(
        '/' . LDAP_GROUPS_DELIMITER . LDAP_GROUPS_DELIMITER . '+/',
        LDAP_GROUPS_DELIMITER,
        $this->ldapGroups
      );

      // Odstranění přebytečných oddělovačů z konce a začátku seznamu povolených LDAP skupin.
      $this->ldapGroups = trim(
        $this->ldapGroups, LDAP_GROUPS_DELIMITER
      );

      // Jestliže se jedná o rodičovskou skupinu, nastavit hodnotu rodiče na -1,
      // abychom se vyhnuli následným kontrolám, které se vztahují na nově
      // vkládané skupiny (tedy na skupiny, které mají nějakého rodiče).
      if (isset($this->dbRecordData)) {
        $this->idParentGroup = $this->dbRecordData['id_parent_group'];
      }
      else {
        $this->idParentGroup = -1;
      }
    }


    /**
     * Připraví novou skupinu v závislosti na vyplněných datech a vrátí ji formou pole.
     *
     * @return array                   Pole obsahující data o skupině.
     */
    private function makeUserGroup() {
      $group = [
        'name' => $this->name,
        'description' => $this->description,
        'ldap_groups' => $this->ldapGroups
      ];

      // Pokud se nejedná o některou z rodičovských skupin, ukládat i další údaje.
      if ($this->idParentGroup !== NULL) {
        $group['id_parent_group'] = $this->role;
        $group['role'] = $this->role;

        $role = $this->getRole($this->role);

        // Pokud není zvoleno oprávnění "administrátor" nebo "správce testů", nemá smysl uvažovat LDAP skupiny.
        if ($role['value'] == PERMISSION_USER) {
          $group['ldap_groups'] = '';
        }
      }

      return $group;
    }


    /**
     * Uloží do instance a zároveň vrátí (z databáze) informace o zvolené skupině.
     *
     * @param int $id                  ID skupiny
     * @return array                   Pole obsahující informace o skupině.
     */
    public function getUserGroup($id) {
      $this->dbRecordData = Database::querySingle('
              SELECT `id_user_group`, `id_parent_group`, `role`, `name`, `description`, `ldap_groups`
              FROM `phg_users_groups`
              WHERE `id_user_group` = ?
              AND `visible` = 1
      ', $id);

      return $this->dbRecordData;
    }


    /**
     * Vrátí seznam všech skupin z databáze.
     *
     * @return mixed                   Pole skupin a informace o každé z nich.
     */
    public static function getUserGroups() {
      $result = Database::queryMulti('
              SELECT `id_user_group`, `id_parent_group`, `role`, phg_users_groups.name AS `name`, `description`, `ldap_groups`,
              `value`, phg_users_roles.name AS `role_name`
              FROM `phg_users_groups`
              JOIN `phg_users_roles`
              ON phg_users_groups.role = phg_users_roles.id_user_role
              WHERE `visible` = 1
      ');

      // Zjištění dalších údajů nutných pro výpis do seznamu.
      foreach ($result as $key => $group) {
        $result[$key]['role_color'] = self::getColorGroupRole($group['value']);
        $result[$key]['count_users'] = self::getCountOfUsersInGroup($group['id_user_group']);
      }

      return $result;
    }


    /**
     * Vrátí počet uživatelů ve zvolené skupině.
     *
     * @param int $id                  ID skupiny
     * @return int|null                Počet uživatelů nebo NULL
     */
    public static function getCountOfUsersInGroup($id) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_users`
              WHERE `id_user_group` = ?
              AND `visible` = 1
      ', $id);
    }


    /**
     * Vrátí ID rodičovské skupiny u zvolené skupiny.
     *
     * @param int $idChildGroup        ID skupiny
     * @return mixed                   Informace o rodičovské skupině
     */
    private function getParentGroup($idChildGroup) {
      return Database::querySingle('
              SELECT `id_parent_group`
              FROM `phg_users_groups`
              WHERE `id_user_group` = ?
              AND `visible` = 1
      ', $idChildGroup);
    }


    /**
     * Zjistí, zdali je daná skupina rodičovská nebo potomek některé z rodičovských.
     *
     * @param int $id                  ID skupiny
     * @return mixed                   1 pokud se jedná o rodičovskou skupinu, jinak 0.
     */
    private function isGroupParent($id) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_users_groups`
              WHERE `id_user_group` = ?
              AND `id_parent_group` IS NULL
              AND `visible` = 1
      ', $id);
    }


    /**
     * Vrátí seznam možných oprávnění.
     *
     * @param bool $noAdminRole        Při TRUE se do seznamu nebude zahrnovat administrátorské oprávnění.
     * @return mixed                   Seznam možných oprávnění
     */
    public static function getRoles($noAdminRole = false) {
      $query = 'SELECT `id_user_role`, `name`, `value`
                FROM `phg_users_roles`';

      // Nezahrnovat do výsledků oprávnění administrátora.
      if ($noAdminRole) {
        $query .= ' WHERE `value` != ' . PERMISSION_ADMIN;
      }

      return Database::queryMulti($query);
    }


    /**
     * Vrátí informace o zvoleném oprávnění.
     *
     * @param int $id                  ID oprávnění
     * @return mixed                   Informace o oprávnění
     */
    public static function getRole($id) {
      return Database::querySingle('
              SELECT `name`, `value`
              FROM `phg_users_roles`
              WHERE `id_user_role` = ?
      ', $id);
    }


    /**
     * Vloží do databáze novou skupinu.
     */
    public function insertUserGroup() {
      $group = $this->makeUserGroup();

      $group['id_by_user'] = PermissionsModel::getUserId();
      $group['date_added'] = date('Y-m-d H:i:s');

      Logger::info('Vkládání nové uživatelské skupiny.', $group);

      Database::insert($this->dbTableName, $group);
    }


    /**
     * Upraví zvolenou skupinu.
     *
     * @param int $id                  ID skupiny
     */
    public function updateUserGroup($id) {
      $group = $this->makeUserGroup();

      Logger::info('Úprava existující uživatelské skupiny.', $group);

      Database::update(
        $this->dbTableName,
        $group,
        'WHERE `id_user_group` = ? AND `visible` = 1',
        $id
      );
    }


    /**
     * Odstraní (resp. deaktivuje) zvolenou skupinu. V případě, že skupina obsahuje nějaké uživatele,
     * dojde k jejich přesunu do rodičovské skupiny.
     *
     * @param int $id                  ID skupiny
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function deleteUserGroup($id) {
      /* Ověření, zdali se uživatel nepokouší smazat rodičovskou (tj. základní) skupinu. */
      if ($this->isGroupParent($id) != 0) {
        Logger::warning('Snaha o smazání rodičovské, uživatelské skupiny.', $id);

        throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
      }

      /* Ověření, zdali je mazaná skupina prázdná. */
      if ($this->getCountOfUsersInGroup($id) != 0) {
        /* Zjištění uživatelů, kteří jsou v dané skupině. */
        $users = UsersModel::getUsersByGroup($id);

        /* Zjištění rodičovské skupiny u každého uživatele a přeřazení uživatele do dané skupiny. */
        foreach ($users as $user) {
          $group = $this->getParentGroup($user['id_user_group']);

          if (!empty($group)) {
            UsersModel::changeUserGroup($user['id_user'], $group['id_parent_group']);
          }
        }
      }

      /* Znovu dodatečné ověření, jestli opravdu došlo k přesunu všech uživatelů. */
      if ($this->getCountOfUsersInGroup($id) == 0) {
        $result = Database::update(
          'phg_users_groups',
          ['visible' => 0],
          'WHERE `id_user_group` = ? AND `id_parent_group` IS NOT NULL AND `visible` = 1',
          $id
        );

        if ($result == 0) {
          Logger::warning('Snaha o smazání neexistující uživatelské skupiny.', $id);

          throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
        }
      }
      else {
        Logger::error('Při přesouvání uživatelů u uživatelské skupiny došlo k chybě.', $id);

        throw new UserError('Během přesouvání uživatelů došlo k chybě.', MSG_ERROR);
      }

      Logger::info('Smazání existující uživatelské skupiny.', $id);
    }


    /**
     * Vrátí název CSS třídy v závislosti na tom, o jaké oprávnění půjde.
     *
     * @param int $role                ID oprávnění
     * @return string|null             Název CSS třídy nebo NULL
     */
    public static function getColorGroupRole($role) {
      switch ($role) {
        case PERMISSION_ADMIN:
          return MSG_CSS_ERROR;
        case PERMISSION_TEST_MANAGER:
          return MSG_CSS_WARNING;
        case PERMISSION_USER:
          return MSG_CSS_SUCCESS;
      }

      return null;
    }


    /**
     * Vrátí název CSS třídy v závislosti na tom, do jaké skupiny oprávnění spadá uživatelské jméno.
     *
     * @param string $username         Uživatelské jméno
     * @param array $groups            Skupiny administrátorů a správců testů (nezjišťují se, musí být předány),
     *                                  v rámci kterých se bude uživatelské jméno vyhledávat.
     * @return string|null             Název CSS třídy nebo NULL
     */
    public static function getColorGroupRoleByUsername($username, $groups) {
      if (in_array($username, $groups['admin'])) {
        return UserGroupsModel::getColorGroupRole(PERMISSION_ADMIN);
      }
      elseif (in_array($username, $groups['testmanager'])) {
        return UserGroupsModel::getColorGroupRole(PERMISSION_TEST_MANAGER);
      }

      return null;
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public function validateData() {
      $this->isNameEmpty();
      $this->isNameTooLong();

      $this->isDescriptionTooLong();

      $this->isRoleEmpty();
      $this->existRole();

      $this->isLdapGroupsTooLong();
      $this->isLdapGroupsUnique();
    }


    /**
     * Ověří, zdali byl vyplněn název skupiny.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isNameEmpty() {
      if (empty($this->name)) {
        throw new UserError('Není vyplněn název skupiny.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný název skupiny není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isNameTooLong() {
      if (mb_strlen($this->name) > $this->inputsMaxLengths['name']) {
        throw new UserError('Název skupiny je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný popis skupiny není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isDescriptionTooLong() {
      if (mb_strlen($this->description) > $this->inputsMaxLengths['description']) {
        throw new UserError('Popis skupiny je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali seznam zobrazovaných LDAP skupin není příliš dlouhý.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isLdapGroupsTooLong() {
      if (mb_strlen($this->ldapGroups) > $this->inputsMaxLengths['ldap-groups']) {
        throw new UserError('Seznam zobrazovaných LDAP skupin je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali seznam zobrazovaných LDAP skupin neobsahuje duplicity.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isLdapGroupsUnique() {
      $ldapGroups = explode(LDAP_GROUPS_DELIMITER, $this->ldapGroups);

      if (count(array_unique($ldapGroups)) != count($ldapGroups)) {
        throw new UserError('Seznam zobrazovaných LDAP skupin obsahuje duplicitní záznam.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali je vybráno oprávnění uživatele.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function isRoleEmpty() {
      if ((empty($this->role) || !is_numeric($this->role)) && $this->idParentGroup !== NULL) {
        throw new UserError('Není vybráno oprávnění skupiny.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zvolené oprávnění existuje.
     *
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private function existRole() {
      $role = $this->getRole($this->role);

      if ((empty($this->getRole($this->role)) || $role['value'] == PERMISSION_ADMIN) && $this->idParentGroup !== NULL) {
        throw new UserError('Vybrané oprávnění neexistuje.', MSG_ERROR);
      }
    }
  }

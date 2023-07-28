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
     * @var string      Seznam zobrazených LDAP skupin
     */
    protected $ldapGroups;

    /**
     * @var int         ID rodičovské skupiny
     */
    protected $idParentGroup;


    /**
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function load($data) {
      parent::load($data);

      if (is_array($this->ldapGroups)) {
        $groups = '';

        foreach ($this->ldapGroups as $group) {
          $groups .= LdapModel::escape($group) . LDAP_GROUPS_DELIMITER;
        }

        $this->ldapGroups = $groups;
      }

      $this->ldapGroups = trim($this->ldapGroups, LDAP_GROUPS_DELIMITER);

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

        $role = $this->role;
      }

      if (!isset($role)) {
        $role = $this->dbRecordData['role'];
      }

      $role = $this->getRole($role);

      // Pokud není zvoleno oprávnění "administrátor" nebo "správce testů", nedojde k uložení vybraných LDAP skupin.
      if ($role['value'] != PERMISSION_ADMIN && $role['value'] != PERMISSION_TEST_MANAGER) {
        $group['ldap_groups'] = '';
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

      foreach ($result as $key => $group) {
        $result[$key]['role_color'] = self::getColorGroupRole($group['value']);
        $result[$key]['count_users'] = self::getCountOfUsersInGroup($group['id_user_group']);

        $result[$key]['ldap_groups_sum'] = count(explode(LDAP_GROUPS_DELIMITER, $group['ldap_groups']));
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

      Logger::info('New user group added.', $group);

      Database::insert($this->dbTableName, $group);
    }


    /**
     * Upraví zvolenou skupinu.
     *
     * @param int $id                  ID skupiny
     */
    public function updateUserGroup($id) {
      $group = $this->makeUserGroup();

      Logger::info('User group modified.', $group);

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
     * @throws UserError
     */
    public function deleteUserGroup($id) {
      // Ověření, zdali se uživatel nepokouší smazat rodičovskou (tj. základní) skupinu.
      if ($this->isGroupParent($id) != 0) {
        Logger::warning('Attempt to delete root user group.', $id);

        throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
      }

      // Ověření, zdali je mazaná skupina prázdná.
      if ($this->getCountOfUsersInGroup($id) != 0) {
        // Zjištění uživatelů, kteří jsou v dané skupině.
        $users = UsersModel::getUsersByGroup($id);

        // Zjištění rodičovské skupiny u každého uživatele a přeřazení uživatele do dané skupiny.
        foreach ($users as $user) {
          $group = $this->getParentGroup($user['id_user_group']);

          if (!empty($group)) {
            UsersModel::changeUserGroup($user['id_user'], $group['id_parent_group']);
          }
        }
      }

      // Znovu dodatečné ověření, jestli opravdu došlo k přesunu všech uživatelů.
      if ($this->getCountOfUsersInGroup($id) == 0) {
        $result = Database::update(
          'phg_users_groups',
          ['visible' => 0],
          'WHERE `id_user_group` = ? AND `id_parent_group` IS NOT NULL AND `visible` = 1',
          $id
        );

        if ($result == 0) {
          Logger::warning('Attempt to delete a non-existent user group.', $id);

          throw new UserError('Záznam vybraný ke smazání neexistuje.', MSG_ERROR);
        }
      }
      else {
        Logger::error('Failed to move users to another user group.', $id);

        throw new UserError('Během přesouvání uživatelů došlo k chybě.', MSG_ERROR);
      }

      Logger::info('User group deleted.', $id);
    }


    /**
     * Ověří, zdali je daná skupina v seznamu LDAP skupin u administrátorského oprávnění.
     *
     * @param string $group            Uživatelská skupina
     * @return bool                    TRUE, pokud byla skupina v seznamu nalezena, jinak FALSE
     */
    public static function existsAdminGroup($group) {
      $found = false;

      $adminGroups = Database::querySingle('SELECT `ldap_groups` FROM `phg_users_groups` WHERE `id_user_group` = 1');
      $adminGroups = explode(LDAP_GROUPS_DELIMITER, $adminGroups['ldap_groups']);

      foreach ($adminGroups as $adminGroup) {
        if ($adminGroup == $group) {
          $found = true;
        }
      }

      return $found;
    }


    /**
     * Vrátí název CSS třídy v závislosti na tom, o jaké oprávnění půjde.
     *
     * @param int $role                ID oprávnění
     * @return string|null             Název CSS třídy nebo NULL
     */
    public static function getColorGroupRole($role) {
      return match ($role) {
        PERMISSION_ADMIN => MSG_CSS_ERROR,
        PERMISSION_TEST_MANAGER => MSG_CSS_WARNING,
        PERMISSION_USER => MSG_CSS_SUCCESS,
        default => null,
      };
    }


    /**
     * Vrátí název CSS třídy v závislosti na tom, do jaké skupiny oprávnění spadá daný uživatel.
     *
     * @param string $username         Uživatelské jméno
     * @param array $groups            Skupiny administrátorů a správců testů (nezjišťují se, musí být předány),
     *                                  v rámci kterých se bude uživatelské jméno vyhledávat.
     * @return string|null             Název CSS třídy nebo NULL
     */
    public static function getColorGroupRoleByUsername($username, $groups) {
      $color = null;

      if (in_array($username, $groups['admin'])) {
        $color = UserGroupsModel::getColorGroupRole(PERMISSION_ADMIN);
      }
      elseif (in_array($username, $groups['testmanager'])) {
        $color = UserGroupsModel::getColorGroupRole(PERMISSION_TEST_MANAGER);
      }

      return $color;
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     *
     * @throws UserError
     */
    public function validateData() {
      $this->isNameEmpty();
      $this->isNameTooLong();

      $this->isDescriptionTooLong();

      $this->isRoleEmpty();
      $this->existsRole();

      $this->isLdapGroupsValid();
      $this->isLdapGroupsTooLong();
      $this->isLdapGroupsUnique();
      $this->existsLdapGroup();
    }


    /**
     * Ověří, zdali byl vyplněn název skupiny.
     *
     * @throws UserError
     */
    private function isNameEmpty() {
      if (empty($this->name)) {
        throw new UserError('Není vyplněn název skupiny.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný název skupiny není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isNameTooLong() {
      if (mb_strlen($this->name) > $this->inputsMaxLengths['name']) {
        throw new UserError('Název skupiny je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zadaný popis skupiny není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isDescriptionTooLong() {
      if (mb_strlen($this->description) > $this->inputsMaxLengths['description']) {
        throw new UserError('Popis skupiny je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali seznam zobrazovaných LDAP skupin neobsahuje nepovolené znaky.
     *
     * @throws UserError
     */
    private function isLdapGroupsValid() {
      if ($this->ldapGroups !== ldap_escape($this->ldapGroups, '\\', LDAP_ESCAPE_FILTER)) {
        throw new UserError('Seznam zobrazovaných LDAP skupin obsahuje nepovolené znaky.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali seznam zobrazovaných LDAP skupin není příliš dlouhý.
     *
     * @throws UserError
     */
    private function isLdapGroupsTooLong() {
      if (mb_strlen($this->ldapGroups) > $this->inputsMaxLengths['ldap-groups']) {
        throw new UserError('Seznam zobrazovaných LDAP skupin je příliš dlouhý.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali seznam zobrazovaných LDAP skupin neobsahuje duplicity.
     *
     * @throws UserError
     */
    private function isLdapGroupsUnique() {
      $ldapGroups = explode(LDAP_GROUPS_DELIMITER, $this->ldapGroups);

      if (count(array_unique($ldapGroups)) != count($ldapGroups)) {
        throw new UserError('Seznam zobrazovaných LDAP skupin obsahuje duplicitní záznam.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali skupiny ze seznamu zobrazovaných LDAP skupin skutečně v LDAPu existují.
     *
     * @throws UserError
     */
    private function existsLdapGroup() {
      $ldap = new LdapModel();

      $ldapGroups = $ldap->getGroupNames();
      $inputGroups = explode(LDAP_GROUPS_DELIMITER, $this->ldapGroups);

      $ldap->close();

      foreach ($inputGroups as $group) {
        if (!empty($group) && !in_array($group, $ldapGroups)) {
          throw new UserError('Vybraná LDAP skupina (' . $group . ') neexistuje.', MSG_ERROR);
        }
      }
    }


    /**
     * Ověří, zdali je vybráno oprávnění uživatele.
     *
     * @throws UserError
     */
    private function isRoleEmpty() {
      if ((empty($this->role) || !is_numeric($this->role)) && $this->idParentGroup !== NULL) {
        throw new UserError('Není vybráno oprávnění skupiny.', MSG_ERROR);
      }
    }


    /**
     * Ověří, zdali zvolené oprávnění existuje.
     *
     * @throws UserError
     */
    private function existsRole() {
      $role = $this->getRole($this->role);

      if ((empty($this->getRole($this->role)) || $role['value'] == PERMISSION_ADMIN) && $this->idParentGroup !== NULL) {
        throw new UserError('Vybrané oprávnění neexistuje.', MSG_ERROR);
      }
    }
  }
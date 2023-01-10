<?php
  /**
   * Třída řešící přihlašování, registraci, oprávnění uživatelů v systému a získávání informací
   * o přihlášeném uživateli.
   *
   * @author Martin Šebela
   */
  class PermissionsModel {
    /**
     * Registruje do systému nového uživatele na základě jeho identity získané z SSO.
     *
     * @param string $identity         Identita uživatele
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele
     */
    private function register($identity) {
      $registrated = false;
      $user = new UsersModel();

      // Identita (e-mail) získaná z SSO.
      if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
        Logger::info('Dobrovolná registrace nového uživatele.', $identity);

        $user->dbTableName = 'phg_users';

        $user->email = $identity;
        $user->idUserGroup = NEW_USER_DEFAULT_GROUP_ID;
        $user->recieveEmail = NEW_USER_PARTICIPATION;
        $user->emailLimit = NEW_USER_PARTICIPATION_EMAILS_LIMIT;

        $registrated = $user->insertUser();

        if ($registrated) {
          $this->login($identity);
        }
      }

      if (!$registrated) {
        Logger::error('Při registraci se nepodařilo načíst informace o uživateli z LDAP.', $identity);

        // Pokud se nepodaří načíst informace o uživateli z LDAP, přesměrovat uživatele na úvodní stránku systému.
        //header('Location: ' . WEB_URL);
        exit();
      }
    }


    /**
     * Vrátí jednu konkrétní identitu (při existenci více identit) uživatele z SSO,
     * na základě které bude uživatel identifikován.
     *
     * @param string $identity         Identita/identity uživatele získané z SSO
     * @return string|null             Identita, kterou bude aplikace používat pro identifikaci daného uživatele
     */
    private function getRemoteUser($identity) {
      $primaryIdentity = null;
      $identities = explode(';', $identity);

      if (!empty($identity)) {
        // Pokud má uživatel více identit, využít tu první z nich.
        $primaryIdentity = (isset($identities[0]) && filter_var($identities[0], FILTER_VALIDATE_EMAIL)) ? $identities[0] : $identity;
      }

      return $primaryIdentity;
    }


    /**
     * Přihlásí uživatele do systému, přičemž pokud se jedná o první přihlášení uživatele do systému
     * (tedy typicky po automatické registraci), dojde k přesměrování do sekce, kde si lze navolit účast
     * v programu.
     *
     * @param string $identity         Identita uživatele
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele
     */
    public function login($identity) {
      $identity = $this->getRemoteUser($identity);

      // Ověření, zdali získaná identita není prázdná, tzn. zdali SSO něco předalo.
      if ($identity == null) {
        Logger::error('Při přihlašování se nepodařilo získat identitu uživatele z SSO.');

        echo 'Failed to retrieve username from SSO!';
        exit();
      }

      $user = UsersModel::getUserByEmail($identity);

      // Pokud je již uživatel registrován...
      if (!empty($user)) {
        $_SESSION['user']['id'] = $user['id_user'];
        $_SESSION['user']['email'] = $user['email'];

        // Nejvyšší oprávnění, které si může uživatel v systému vybrat (v rámci změny role).
        $_SESSION['user']['permission'] = $user['role'];

        // Aktuálně zvolená role v systému.
        $_SESSION['user']['role'] = $user['role'];

        // Vytvoření unikátního CSRF tokenu pro jednu relaci přihlášení.
        $_SESSION['csrf_token'] = $this->generateCsrfToken($user['id_user']);

        session_regenerate_id();

        // Zjištění, zdali se jedná o uživatele, který se zatím do systému nikdy v minulosti nepřihlásil.
        $countUserLogins = $this->getCountOfUserLogins($user['id_user']);

        // Zaznamenat přihlášení uživatele do databáze (pokud se nejedná o testovacího uživatele - sondu).
        if ($user['username'] != TEST_USERNAME) {
          $this->logLogin($user['id_user']);
        }

        // Pokud se jedná o první přihlášení uživatele, úmyslně ho přesměrovat do sekce "Moje účast v programu".
        if ($countUserLogins == 0) {
          header('Location: ' . WEB_URL . '/portal/my-participation');
          exit();
        }
      }
      else {
        $this->register($identity);
      }
    }


    /**
     * Vytvoří v databázi nový záznam o úspěšném přihlášení uživatele do systému.
     *
     * @param int $idUser              ID uživatele
     */
    private function logLogin($idUser) {
      Logger::info('Přihlášení uživatele do systému.', $idUser);

      $record = [
        'id_user' => $idUser,
        'login_datetime' => date('Y-m-d H:i:s'),
        'ip' => WebsitePrependerModel::getClientIp()
      ];

      Database::insert('phg_users_login_log', $record);
    }


    /**
     * Vrátí počet přihlášení konkrétního uživatele do systému.
     *
     * @param int $idUser              ID uživatele
     * @return mixed                   Celkový počet přihlášení
     */
    private function getCountOfUserLogins($idUser) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_users_login_log`
              WHERE `id_user` = ?
      ', $idUser);
    }


    /**
     * Přepne roli aktuálně přihlášeného uživatele.
     *
     * @param int $newRole             Identifikátor vybrané role
     */
    public static function switchRole($newRole) {
      // Ověření oprávnění uživatele - měnit role je umožněno pouze správcům testů a administrátorům.
      if (self::getUserPermission() <= PERMISSION_TEST_MANAGER) {
        self::setUserRole($newRole);
      }
      else {
        Logger::warning(
          'Snaha o změnu role u neoprávněného uživatele.',
          ['id_user' => self::getUserId(), 'new_role' => $newRole]
        );
      }
    }


    /**
     * Změní přihlášenému uživateli roli.
     *
     * @param int $newRole             Identifikátor role
     */
    private static function setUserRole($newRole) {
      // Pole možných oprávnění v systému.
      $roles = [
        PERMISSION_ADMIN_URL => PERMISSION_ADMIN,
        PERMISSION_TEST_MANAGER_URL => PERMISSION_TEST_MANAGER,
        PERMISSION_USER_URL => PERMISSION_USER
      ];

      // Pokud bylo požadované oprávnění nalezeno, tak jej změnit.
      if (array_key_exists($newRole, $roles)) {
        $_SESSION['user']['role'] = $roles[$newRole];
      }
    }


    /**
     * Vrátí textový název role.
     *
     * @return string|null             Název role, kterou má právě uživatel vybranou.
     */
    public static function getUserRoleText() {
      switch (self::getUserRole()) {
        case PERMISSION_ADMIN: return PERMISSION_ADMIN_TEXT;
        case PERMISSION_TEST_MANAGER: return PERMISSION_TEST_MANAGER_TEXT;
        case PERMISSION_USER: return PERMISSION_USER_TEXT;
      }

      return null;
    }


    /**
     * Vygeneruje a vrátí unikátní CSRF token pro konkrétního uživatele.
     *
     * @param int $idUser              ID uživatele
     * @return string                  Vygenerovaný CSRF token
     */
    private function generateCsrfToken($idUser) {
      return hash_hmac('sha512', microtime() . $idUser, CSRF_KEY);
    }


    /**
     * Vrátí CSRF token.
     *
     * @return mixed|null              CSRF token nebo NULL
     */
    public static function getCsrfToken() {
      if (isset($_SESSION['csrf_token'])) {
        return $_SESSION['csrf_token'];
      }

      return null;
    }


    /**
     * Vrátí e-mailové omezení (na příjemce, které může uživatel vybírat v kampaních)
     * pro právě přihlášeného uživatele.
     *
     * @return mixed|null              Pole e-mailových omezení nebo NULL
     */
    public static function getUserEmailRestrictions() {
      $result = Database::querySingle('
              SELECT `emails_restrictions`
              FROM `phg_users`
              JOIN `phg_users_groups`
              ON phg_users.id_user_group = phg_users_groups.id_user_group
              WHERE `id_user` = ?
      ', self::getUserId());

      if (!empty($result)) {
        return $result['emails_restrictions'];
      }

      return null;
    }


    /**
     * Vrátí řetězec povolených LDAP skupin pro právě přihlášeného uživatele.
     *
     * @return mixed|null              Řetězec povolených LDAP skupin nebo NULL
     */
    public static function getUserAllowedLdapGroups() {
      $result = Database::querySingle('
              SELECT `ldap_groups`
              FROM `phg_users`
              JOIN `phg_users_groups`
              ON phg_users.id_user_group = phg_users_groups.id_user_group
              WHERE `id_user` = ?
      ', self::getUserId());

      if (!empty($result)) {
        return $result['ldap_groups'];
      }

      return null;
    }


    /**
     * Odhlásí právě přihlášeného uživatele ze systému.
     */
    public static function logout() {
      session_destroy();
      unset($_SESSION['user']);
    }


    /**
     * Vrátí ID právě přihlášeného uživatele.
     *
     * @return int|null                ID uživatele nebo NULL
     */
    public static function getUserId() {
      if (isset($_SESSION['user']['id'])) {
        return $_SESSION['user']['id'];
      }

      return null;
    }


    /**
     * Vrátí identitu právě přihlášeného uživatele.
     *
     * @return string|null                Uživatelské jméno uživatele nebo NULL
     */
    public static function getUserName() {
      if (isset($_SESSION['user']['email'])) {
        return $_SESSION['user']['email'];
      }

      return null;
    }


    /**
     * Vrátí nejvyšší možné oprávnění, které má přihlášený uživatel.
     *
     * @return int|null                Číslo oprávnění nebo NULL
     */
    public static function getUserPermission() {
      if (isset($_SESSION['user']['permission'])) {
        return $_SESSION['user']['permission'];
      }

      return null;
    }


    /**
     * Vrátí aktuálně zvolenou roli právě přihlášeného uživatele.
     *
     * @return int|null                Číslo role nebo NULL
     */
    public static function getUserRole() {
      if (isset($_SESSION['user']['role'])) {
        return $_SESSION['user']['role'];
      }

      return null;
    }
  }

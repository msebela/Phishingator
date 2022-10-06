<?php
  /**
   * Třída řešící přihlašování, registraci, oprávnění uživatelů v systému a získávání informací
   * o přihlášeném uživateli.
   *
   * @author Martin Šebela
   */
  class PermissionsModel {
    /**
     * Registruje do systému nového uživatele na základě jeho uživatelského jména,
     * které se před samotnou registrací ověří v LDAPu.
     *
     * @param string $username         Uživatelské jméno
     */
    private function register($username) {
      $newUser = new UsersModel();
      $ldap = new LdapModel();

      // Získání e-mailu uživatele z LDAP.
      $newUser->email = $ldap->getEmailByUsername($username);
      $ldap->close();

      // Pokud se podařilo z LDAP získat e-mail, dojde k registraci nového uživatele.
      if (!empty($newUser->email) && filter_var($newUser->email, FILTER_VALIDATE_EMAIL)) {
        Logger::info('Dobrovolná registrace nového uživatele.', $username);

        $newUser->dbTableName = 'phg_users';

        $newUser->username = $username;
        $newUser->idUserGroup = NEW_USER_DEFAULT_GROUP_ID;
        $newUser->recieveEmail = NEW_USER_PARTICIPATION;
        $newUser->emailLimit = NEW_USER_PARTICIPATION_EMAILS_LIMIT;

        $newUser->insertUser();
        $this->login($username);
      }
      else {
        Logger::error('Při registraci se nepodařilo načíst e-mail uživatele z LDAP.', $username);

        // Pokud se nepodaří načíst e-mail z LDAP, přesměrovat uživatele na úvodní stránku systému.
        header('Location: ' . WEB_URL);
        exit();
      }
    }


    /**
     * Vrátí jednu konkrétní identitu (při existenci více identit) uživatele z SSO,
     * díky které bude uživatel v aplikaci identifikován.
     *
     * @param string $identity         Identita/identity uživatel získané ze SSO
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
     * @param string $username         Uživatelské jméno
     */
    public function login($username) {
      $username = $this->getRemoteUser($username);

      // Ověření, zdali uživatelské jméno není prázdné, tzn. zdali SSO něco předalo.
      if ($username == null) {
        Logger::error('Při přihlašování se nepodařilo načíst uživatelské jméno uživatele z SSO.');

        echo 'Failed to retrieve username from SSO!';
        exit();
      }

      $user = UsersModel::getUserByUsername($username);

      // Pokud je již uživatel registrován...
      if (!empty($user)) {
        $_SESSION['user']['id'] = $user['id_user'];
        $_SESSION['user']['username'] = $user['username'];

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
        $this->register($username);
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
        'ip' => WebsitePrependerModel::getClientIp(),
        'local_ip' => WebsitePrependerModel::getClientLocalIp()
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
      unset($_SESSION['user']);

      header('Location: ' . WEB_URL . '/Shibboleth.sso/Logout');
      exit();
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
     * Vrátí uživatelské jméno právě přihlášeného uživatele.
     *
     * @return string|null                Uživatelské jméno uživatele nebo NULL
     */
    public static function getUserName() {
      if (isset($_SESSION['user']['username'])) {
        return $_SESSION['user']['username'];
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

<?php
  /**
   * Třída sloužící k ověření správnosti uživatelem zadaných
   * přihlašovacích údajů na podvodných stránek.
   *
   * @author Martin Šebela
   */
  class CredentialsTesterModel {
    /**
     * @var string      Uživatelské jméno
     */
    private static $username;

    /**
     * @var string      Heslo uživatele
     */
    private static $password;


    /**
     * Ověří, zdali jsou uživatelem zadané přihlašovací údaje platné.
     *
     * @param string $username         Uživatelské jméno
     * @param string $password         Heslo uživatele
     * @param string $method           Metoda použitá k ověření platnosti přihlašovacích údajů, při nevyplnění
     *                                 bude použita metoda nastavená v konfiguračním souboru [nepovinné]
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     */
    public static function tryLogin($username, $password, $method = null) {
      $validCreds = false;

      self::$username = $username;
      self::$password = $password;

      if ($method == null) {
        $method = AUTHENTICATION_TYPE;
      }

      if ($method == 'kerberos') {
        $validCreds = self::tryKerberosLogin();
      }
      elseif ($method == 'ldap') {
        $validCreds = self::tryLdapLogin();
      }
      elseif ($method == 'imap') {
        $validCreds = self::tryImapLogin();
      }

      return $validCreds;
    }


    /**
     * V Kerberos ověří (voláním kinit), zdali byly zadány platné přihlašovací údaje.
     *
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     */
    private static function tryKerberosLogin() {
      $validCreds = false;

      // Ošetření argumentů skriptu.
      $username = escapeshellarg(self::$username);
      $password = escapeshellarg(self::$password);

      // Ošetření volání skriptu a volání skriptu pro zjištění, zdali jsou zadané přihlašovací údaje platné.
      $output = shell_exec(
        escapeshellcmd(CORE_DOCUMENT_ROOT . '/verify-credentials.kerberos.sh ' . $username . ' ' . $password)
      );

      // Získání pouze posledního znaku, který skript vrátí (viz skript).
      $output = substr(remove_new_line_symbols($output), -1);

      // Pokud je výstupem skriptu "0", jsou přihlašovací údaje platné.
      if (mb_strlen($output) == 1 && $output === '0') {
        $validCreds = true;
      }

      return $validCreds;
    }


    /**
     * Otevřením LDAP spojení ověří, zdali byly zadány platné přihlašovací údaje.
     *
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     */
    private static function tryLdapLogin() {
      $ldap = new LdapModel(false);

      $username = AUTHENTICATION_LDAP_USER_PREFIX . self::$username;

      if (!empty(AUTHENTICATION_LDAP_USER_SUFFIX) && !str_contains($username, AUTHENTICATION_LDAP_USER_SUFFIX)) {
        $username .= AUTHENTICATION_LDAP_USER_SUFFIX;
      }

      $username = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
      $password = ldap_escape(self::$password, '', LDAP_ESCAPE_FILTER);

      $validCreds = $ldap->connect($username, $password, AUTHENTICATION_LDAP_HOST, AUTHENTICATION_LDAP_PORT);

      $ldap->close();

      return $validCreds;
    }


    /**
     * Otevřením IMAP spojení ověří, zdali byly zadány platné přihlašovací údaje.
     *
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     */
    private static function tryImapLogin() {
      $validCreds = false;

      // Úmyslné potlačení výpisu chyb při nesprávně zadaných přihlašovacích údajích.
      $imap = @imap_open(AUTHENTICATION_IMAP_ARGS, self::$username, self::$password,  OP_READONLY, 1);

      if ($imap !== false) {
        $validCreds = true;

        imap_close($imap);
      }

      return $validCreds;
    }
  }
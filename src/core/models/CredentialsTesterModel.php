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
    public static function tryLogin($username, $password, $method = AUTHENTICATION_TYPE) {
      $validCreds = false;

      self::$username = $username;
      self::$password = $password;

      if ($method == 'ldap') {
        $validCreds = self::tryLdapLogin();
      }
      elseif ($method == 'web') {
        $validCreds = self::tryWebLogin();
      }
      elseif ($method == 'kerberos') {
        $validCreds = self::tryKerberosLogin();
      }
      elseif ($method == 'imap') {
        $validCreds = self::tryImapLogin();
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

      $validCreds = $ldap->connect($username, self::$password, AUTHENTICATION_LDAP_HOST, AUTHENTICATION_LDAP_PORT, true);

      $ldap->close();

      return $validCreds;
    }


    /**
     * Odesláním přihlašovacího formuláře přes metodu POST na zvoleném webu ověří,
     * zdali byly zadány platné přihlašovací údaje.
     *
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     */
    private static function tryWebLogin() {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, AUTHENTICATION_WEB_URL);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
          AUTHENTICATION_WEB_INPUT_USERNAME => self::$username,
          AUTHENTICATION_WEB_INPUT_PASSWORD => self::$password
        ])
      );

      $server_output = curl_exec($ch);
      $server_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      curl_close($ch);

      return ($server_response == AUTHENTICATION_WEB_RESPONSE_CODE || $server_output == AUTHENTICATION_WEB_RESPONSE_OUTPUT);
    }


    /**
     * Voláním příkazu kinit ve službě Kerberos ověří, zdali byly zadány platné přihlašovací údaje.
     *
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     */
    private static function tryKerberosLogin() {
      $validCreds = false;

      $username = escapeshellarg(self::$username);
      $password = escapeshellarg(self::$password);

      $output = shell_exec(
        escapeshellcmd(CORE_DOCUMENT_ROOT . '/verify-credentials.kerberos.sh ' . $username . ' ' . $password)
      );

      // Získání pouze posledního znaku, který skript vrátí.
      $output = substr(remove_new_line_symbols($output), -1);

      // Pokud je výstupem skriptu "0", jsou přihlašovací údaje platné.
      if (mb_strlen($output) == 1 && $output === '0') {
        $validCreds = true;
      }

      return $validCreds;
    }


    /**
     * Otevřením IMAP spojení ověří, zdali byly zadány platné přihlašovací údaje.
     *
     * @return bool                    TRUE pokud byly zadány platné přihlašovací údaje, jinak FALSE
     */
    private static function tryImapLogin() {
      $validCreds = false;

      $imap = imap_open(AUTHENTICATION_IMAP_ARGS, self::$username, self::$password,  OP_READONLY, 1);

      if ($imap !== false) {
        $validCreds = true;

        imap_close($imap);
      }

      return $validCreds;
    }
  }
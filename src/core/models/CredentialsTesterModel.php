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

      if (str_contains($username, '@')) {
        $username = get_email_part($username, 'username');
      }

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
      elseif ($method == 'policy') {
        $validCreds = self::tryPolicyCheck();
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

      exec('echo ' . $password . ' | kinit ' . $username, $output, $returnCode);

      if ($returnCode === 0) {
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


    /**
     * Otestováním bezpečnostní politiky hesel ověří, zdali zadané heslo
     * této politice vyhoví, a mohlo by se tak jednat o skutečné heslo.
     *
     * @return bool                    TRUE pokud heslo vyhovuje heslové politice, jinak FALSE
     */
    private static function tryPolicyCheck() {
      $validCreds = false;

      if (mb_strlen(self::$password) >= AUTHENTICATION_POLICY_MIN_LENGTH) {
        $sumGroupsChars = 0;

        // Malá písmena
        if (preg_match('/[a-z]/', self::$password)) {
          $sumGroupsChars++;
        }

        // Velká písmena
        if (preg_match('/[A-Z]/', self::$password)) {
          $sumGroupsChars++;
        }

        // Čísla
        if (preg_match('/[0-9]/', self::$password)) {
          $sumGroupsChars++;
        }

        // Speciální znaky
        if (preg_match('/[^\da-zA-Z]/', self::$password)) {
          $sumGroupsChars++;
        }

        if ($sumGroupsChars >= AUTHENTICATION_POLICY_MIN_CHARS_GROUPS) {
          $validCreds = true;
        }

        if (!AUTHENTICATION_POLICY_ALLOW_CONTAIN_USERNAME) {
          $username = str_contains(self::$username, '@') ? get_email_part(self::$username, 'username') : self::$username;

          if (str_contains(mb_strtolower(self::$password), mb_strtolower($username))) {
            $validCreds = false;
          }
        }
      }

      return $validCreds;
    }
  }
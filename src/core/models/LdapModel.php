<?php
  /**
   * Třída určená pro komunikaci a vyhledávání v LDAP.
   *
   * @author Martin Šebela
   */
  class LdapModel {
    /**
     * @var LDAP\Connection  LDAP spojení nebo NULL.
     */
    private $ldapConnection;


    /**
     * Konstruktor třídy ve výchozím stavu automaticky volající metodu pro připojení k LDAP.
     *
     * @param bool $autoConnect        TRUE (výchozí), pokud má automaticky dojít k připojení
     *                                 k LDAP přihlašovacími údaji z konfiguračního souboru
     */
    public function __construct($autoConnect = true) {
      if ($autoConnect) {
        $this->connect();
      }
    }


    /**
     * Připojí a autentizuje se k LDAP a nastaví verzi používaného protokolu.
     * Při nepředání parametrů (uživatelské jméno a heslo, případně hostname a port)
     * budou použity údaje z konfiguračního souboru.
     *
     * @param string $username         Uživatelské jméno pro připojení do LDAP [nepovinné]
     * @param string $password         Heslo pro připojení do LDAP [nepovinné]
     * @param string $hostname         LDAP server [nepovinné]
     * @param int $port                Port, na kterém LDAP běží [nepovinné]
     * @return bool                    TRUE pokud došlo k úspěšnému připojení, jinak FALSE
     */
    public function connect($username = null, $password = null, $hostname = null, $port = null) {
      $connected = false;
      $ldapBind = false;

      if ($hostname == null) {
        $hostname = LDAP_HOSTNAME;
        $port = LDAP_PORT;
      }

      $this->ldapConnection = ldap_connect($hostname . ':' . $port);

      if ($this->ldapConnection) {
        ldap_set_option($this->ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($username == null || $password == null) {
          $username = LDAP_USERNAME;
          $password = LDAP_PASSWORD;
        }

        $ldapBind = ldap_bind($this->ldapConnection, $username, $password);
      }

      if (!$this->ldapConnection || !$ldapBind) {
        Logger::error('Failed to connect or authenticate to LDAP.', ldap_error($this->ldapConnection));
      }
      else {
        $connected = true;
      }

      return $connected;
    }


    /**
     * Uzavře LDAP spojení.
     *
     * @return void
     */
    public function close() {
      ldap_unbind($this->ldapConnection);
    }


    /**
     * Vyhledá a vrátí data z LDAP na základě zadaných parametrů.
     *
     * @param string $ldapDn           Cesta v LDAP, ve které se bude vyhledávat.
     * @param string $filter           Filtr, na základě kterého se bude vyhledávat.
     * @return array|null              Vyhledaná data nebo NULL
     */
    private function getDataByFilter($ldapDn, $filter) {
      $found = false;
      $data = null;

      if ($this->ldapConnection && !empty($ldapDn) && !empty($filter)) {
        if ($ldapDn != LDAP_BASE_DN) {
          $ldapDn .= ',' . LDAP_BASE_DN;
        }

        $results = ldap_search($this->ldapConnection, $ldapDn, $filter);

        if ($results) {
          $countRecords = ldap_count_entries($this->ldapConnection, $results);
          $found = true;

          if ($countRecords >= 1) {
            $data = ldap_get_entries($this->ldapConnection, $results);
          }
        }
      }

      if (!$found) {
        Logger::error(
          'Failed to retrieve data from LDAP.',
          ['ldap_dn' => $ldapDn, 'filter' => $filter]
        );
      }

      return $data;
    }


    /**
     * Vrátí hodnotu konkrétního atributu u zvoleného uživatele, a to v závislosti na jeho uživatelském jménu z LDAP.
     *
     * @param string $username         Uživatelské jméno, u něhož se má získat hodnota atributu
     * @param string $attributeName    Název atributu v LDAP
     * @param int $onlyFirstRecord     Omezení počtu vrácených záznamů [nepovinné]
     * @return mixed|null              Hodnota uložená u atributu v LDAP
     */
    private function getAttributeValue($username, $attributeName, $onlyFirstRecord = 1) {
      $attrValue = null;

      $username = $this->getAttributeFromDN(LDAP_USER_ATTR_ID, $username);

      if (!empty($username)) {
        $info = $this->getDataByFilter(LDAP_USERS_DN, ldap_escape(LDAP_USER_ATTR_ID, '', LDAP_ESCAPE_FILTER) . '=' . ldap_escape($username, '', LDAP_ESCAPE_FILTER));

        if (isset($info['count']) && $info['count'] > 0 && isset($info[0][$attributeName])) {
          for ($i = 0; $i < $info[0][$attributeName]['count']; $i++) {
            $value = $info[0][$attributeName][$i];

            if ($onlyFirstRecord) {
              $attrValue = $value;
              break;
            }
            else {
              $attrValue[] = $value;
            }
          }
        }
      }

      return $attrValue;
    }


    /**
     * Vrátí pouze hodnotu atributu a smaže ostatní části DN.
     * (Pokud je např. funkci předáno "uid=uzivatel,dc=...", je cílem vrátít řetězec "uzivatel").
     *
     * @param string $attributeName    Požadovaný atribut, jehož hodnotu je třeba získat
     * @param string $dn               Vstupní řetězec, ze kterého se bude hodnota atributu získávat
     * @return mixed|string            Hodnota atributu nebo NULL, popř. FALSE.
     */
    private function getAttributeFromDN($attributeName, $dn) {
      $attributeName = $attributeName . '=';
      $attributePos = strpos($dn, $attributeName);

      $attributeSeparator = ',';
      $attributeSeparatorPos = strpos($dn, $attributeSeparator);

      // např. uid=uzivatel,dc=(...)
      if ($attributePos !== false && $attributeSeparatorPos !== false) {
        $attribute = substr($dn, strlen($attributeName), $attributeSeparatorPos - strlen($attributeName));
      }
      // např. uid=uzivatel
      elseif ($attributePos !== false && $attributeSeparatorPos === false) {
        $attribute = substr($dn, strlen($attributeName));
      }
      else {
        $attribute = $dn;
      }

      return $attribute;
    }


    /**
     * Vrátí uživatelské jméno na základě e-mailu uživatele.
     *
     * @param string $email            E-mail uživatele
     * @return string|null             Uživatelské jméno nebo NULL
     */
    public function getUsernameByEmail($email) {
      $username = null;

      if (!empty($email)) {
        $info = $this->getDataByFilter(LDAP_USERS_DN, 'mail=' . ldap_escape($email, '', LDAP_ESCAPE_FILTER));

        if (isset($info[0][LDAP_USER_ATTR_ID][0])) {
          $username = $info[0][LDAP_USER_ATTR_ID][0];
        }
      }

      return $username;
    }


    /**
     * Vrátí e-mail uživatele (první v pořadí) v závislosti na jeho uživatelském jménu z LDAP.
     *
     * @param string $username         Uživatelské jméno
     * @return string|null             E-mail uživatele nebo NULL
     */
    public function getEmailByUsername($username) {
      $email = null;

      if (!empty($username)) {
        $email = $this->getAttributeValue($username, LDAP_USER_ATTR_EMAIL);
      }

      return $email;
    }


    /**
     * Vrátí všechny e-maily uživatele v závislosti na jeho uživatelském jménu z LDAP.
     *
     * @param string $username         Uživatelské jméno
     * @return array|null              Všechny e-maily uživatele nebo NULL
     */
    public function getEmailsByUsername($username) {
      $emails = [];

      if (!empty($username)) {
        $emails = $this->getAttributeValue($username, 'mail', null);
      }

      if (!is_array($emails)) {
        $emails = [$emails];
      }

      return $emails;
    }


    /**
     * Vrátí jméno a příjmení uživatele v závislosti na jeho uživatelském jménu z LDAP.
     *
     * @param string $username         Uživatelské jméno
     * @return string|null             Jméno a příjmení uživatele nebo NULL
     */
    public function getFullnameByUsername($username) {
      $name = null;

      if (!empty($username)) {
        $name = $this->getDecodedString($this->getAttributeValue($username, LDAP_USER_ATTR_NAME));
      }

      return $name;
    }


    /**
     * Vrátí primární skupinu uživatele v závislosti na jeho uživatelském jménu z LDAP.
     *
     * @param string $username            Uživatelské jméno
     * @param bool $preferAdminLdapGroups TRUE, pokud mají být preferovány skupiny definované
     *                                    u administrátorského oprávnění (výchozí) [nepovinné]
     * @return string                     Primární skupina uživatele nebo NULL
     */
    public function getPrimaryGroupByUsername($username, $preferAdminLdapGroups = true) {
      $found = false;
      $group = '';

      if (!empty($username)) {
        $group = $this->getAttributeValue($username, LDAP_USER_ATTR_PRIMARY_GROUP, null);

        if (is_array($group)) {
          // Pokud je uživatel členem několika skupin...
          if (count($group) > 1) {
            if ($preferAdminLdapGroups) {
              foreach ($group as $item) {
                $item = $this->getDnPart($item, 0);

                if (UserGroupsModel::existsAdminGroup($item)) {
                  $found = true;
                  $group = $item;

                  break;
                }
              }
            }

            if (!$preferAdminLdapGroups || !$found) {
              $group = end($group);
            }
          }
          else {
            $group = $group[0];
            $found = true;
          }
        }

        if (!$found) {
          $group = $this->getDnPart($group, 0);
        }
      }

      return $group;
    }


    /**
     * Vrátí seznam uživatelů (resp. jejich e-maily) ze zvolené LDAP skupiny.
     *
     * @param string $group            Název skupiny
     * @return array|null              Seznam uživatelů nebo NULL
     */
    public function getUsersInGroup($group) {
      $users = null;

      if (!empty($group)) {
        $info = $this->getDataByFilter(LDAP_GROUPS_DN, 'cn=' . ldap_escape($group, '', LDAP_ESCAPE_FILTER));

        if (isset($info[0][LDAP_GROUPS_ATTR_MEMBER]['count'])) {
          // Seznam uživatelů ve skupině.
          $users = [];

          // Projít všechny uživatele dané skupiny a u každého uživatele zjistit jeho e-mail.
          for ($i = 0; $i < $info[0][LDAP_GROUPS_ATTR_MEMBER]['count']; $i++) {
            $users[] = $this->getEmailByUsername($info[0][LDAP_GROUPS_ATTR_MEMBER][$i]);
          }
        }
      }

      return $users;
    }


    /**
     * Vrátí seznam názvů všech skupin z LDAP.
     *
     * @return array                   Pole názvů skupin
     */
    public function getGroupNames() {
      $groupNames = [];

      $groups = $this->getDataByFilter(LDAP_GROUPS_DN, 'cn=*');

      foreach ($groups as $group) {
        if (isset($group['cn'][0])) {
          $groupNames[] = $group['cn'][0];
        }
      }

      sort($groupNames);

      return $groupNames;
    }


    /**
     * Vrátí seznam oddělení z LDAP (resp. fakulty a jejich katedry, oddělení apod.).
     *
     * @return array                   Asociativní pole, kde jsou jako klíče názvy rodičovských oddělení a jako
     *                                 hodnoty pole oddělení spadajících pod daného rodiče.
     */
    public function getDepartments() {
      $departments = [];

      if ($this->ldapConnection && LDAP_ROOT_DEPARTMENTS_FILTER_DN) {
        $rootDepartments = ldap_search($this->ldapConnection, LDAP_DEPARTMENTS_DN, ldap_escape(LDAP_ROOT_DEPARTMENTS_FILTER_DN, '', LDAP_ESCAPE_FILTER));
        $countRootDep = ldap_count_entries($this->ldapConnection, $rootDepartments);

        $rootDepData = ldap_get_entries($this->ldapConnection, $rootDepartments);

        // Název atributu, podle kterého se budou vybírat podřazená pracoviště.
        $attributeDepartment = explode('=', LDAP_ROOT_DEPARTMENTS_FILTER_DN);
        $attributeDepartment = (isset($attributeDepartment[0])) ? $attributeDepartment[0] : null;

        if ($attributeDepartment != null) {
          for ($i = 0; $i < $countRootDep; $i++) {
            // Zjištění zkratky mateřského oddělení (existuje-li).
            $rootDepAbbr = $rootDepData[$i]['ou'][0] ?? null;

            // Zjištění ID mateřského oddělení.
            $rootDepId = $rootDepData[$i]['cn'][0] ?? null;

            if ($rootDepAbbr != null && !is_numeric($rootDepAbbr) && $rootDepId != null) {
              // Založení nového mateřského pracoviště v poli (např. fakulty).
              $departments[$rootDepAbbr] = [];

              // Zjištění všech oddělení na fakultě/pracovišti apod.
              $childDepartments = ldap_search($this->ldapConnection, LDAP_DEPARTMENTS_DN, ldap_escape($attributeDepartment, '', LDAP_ESCAPE_FILTER) . '=' . ldap_escape($rootDepId, '', LDAP_ESCAPE_FILTER));
              $countChildDep = ldap_count_entries($this->ldapConnection, $childDepartments);

              $childDepData = ldap_get_entries($this->ldapConnection, $childDepartments);

              // Vložení zkratek oddělení do pole.
              for ($y = 0; $y < $countChildDep; $y++) {
                $departments[$rootDepAbbr][] = $childDepData[$y]['ou'][0];
              }
            }
          }
        }
      }

      return $departments;
    }


    /**
     * Vrátí zvolenou část z DN řetězce.
     *
     * @param string $dnString         DN řetězec
     * @param int $index               Index požadované části
     * @return string                  Požadovaná část
     */
    private function getDnPart($dnString, $index) {
      $part = '';

      if ($dnString != null) {
        $parts = ldap_explode_dn($dnString, 1);
        $part = $parts[$index];
      }

      return $part;
    }


    /**
     * Ověří, zdali jde o řetězec zakódovaný base64 a pokud ano, dojde k jeho dekódování
     * a vrácení na výstup. V opačném případě dojde k vrácení původního řetězce.
     *
     * @param string $string           Řetězec k dekódování
     * @return string                  Dekódovaný/původní řetězec
     */
    private function getDecodedString($string) {
      $output = '';

      if ($string != null) {
        $output = base64_decode($string, true);

        if (base64_encode($output) !== $string) {
          $output = $string;
        }
      }

      return $output;
    }


    /**
     * Ošetří speciální znaky v řetězci vstupujícího do "dn" v LDAP přidáním zpětného lomítka.
     *
     * @param string $string           Řetězec k ošetření
     * @return string                  Ošetřený řetězec
     */
    public static function escape($string) {
      return preg_replace('/([\\\,#+<>;"=*()])/', '\\\$1', $string);
    }
  }
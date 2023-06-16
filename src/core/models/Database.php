<?php
  /**
   * Třída řešící databázovou vrstvu a všechny operace s ní spojené.
   *
   * @author Martin Šebela
   */
  class Database {
    /**
     * @var PDO         Otevřené připojení k databázi.
     */
    private static $connection;


    /**
     * Pokusí se připojit k databázi (pokud zatím k připojení nedošlo).
     *
     * @param string $pdoDns           Řetězec obsahující název databázového enginu, hostitele a název databáze
     * @param string $dbUsername       Uživatelské jméno pro připojení k databázi
     * @param string $dbPassword       Heslo pro připojení k databázi
     */
    public static function connect($pdoDns, $dbUsername, $dbPassword) {
      if (!isset(self::$connection)) {
        self::$connection = @new PDO($pdoDns, $dbUsername, $dbPassword);

        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES ' . DB_ENCODING);
        self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      }
    }


    /**
     * Vykoná SQL dotaz s tím, že jako výsledek dotazu se předpokládá jeden řádek.
     *
     * @param string $query            SQL dotaz
     * @param array $args              Parametry dotazu
     * @return mixed                   Výsledek provedení SQL dotazu
     */
    public static function querySingle($query, $args = []) {
      $result = self::$connection->prepare($query);
      $result->execute(self::convertArgsToArray($args));

      return $result->fetch();
    }


    /**
     * Vykoná SQL dotaz s tím, že jako výsledek dotazu se předpokládá jeden nebo více řádků.
     *
     * @param string $query            SQL dotaz
     * @param array $args              Parametry dotazu
     * @return array|false             Výsledek provedení SQL dotazu
     */
    public static function queryMulti($query, $args = []) {
      $result = self::$connection->prepare($query);
      $result->execute(self::convertArgsToArray($args));

      return $result->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Vykoná SQL dotaz s tím, že jako výsledek dotazu se předpokládá
     * jediný řádek s SQL hodnotou COUNT(*) na nulté pozici.
     *
     * @param string $query            SQL dotaz
     * @param array $args              Parametry dotazu
     * @return mixed
     */
    public static function queryCount($query, $args = []) {
      $result = self::querySingle($query, self::convertArgsToArray($args));

      return $result[0];
    }


    /**
     * Vykoná SQL dotaz s tím, že jako výsledek dotazu vrátí počet ovlivněných řádků.
     *
     * @param string $query            SQL dotaz
     * @param array $args              Parametry dotazu
     * @return int                     Počet ovlivněných řádků SQL dotazem
     */
    public static function queryAffectedRows($query, $args = []) {
      $result = self::$connection->prepare($query);
      $result->execute(self::convertArgsToArray($args));

      return $result->rowCount();
    }


    /**
     * Vrátí ID posledního záznamu vloženého do databáze.
     *
     * @return false|string            ID naposledy vloženého záznamu nebo FALSE
     */
    public static function getLastInsertId() {
      return self::$connection->lastInsertId();
    }


    /**
     * Vloží nový řádek do databáze.
     *
     * @param string $table            Databázová tabulka
     * @param array $args              Pole s názvy atributů a hodnotami zapisovanými do databáze
     * @return mixed                   Výsledek dotazu
     */
    public static function insert($table, $args = []) {
      $args = self::convertArgsToArray($args);
      $columns = implode('`, `', array_keys($args));
      $valuesMarks = str_repeat('?,', count($args) - 1);

      return self::querySingle(
        "INSERT INTO `$table` (`" . $columns . "`) VALUES (" . $valuesMarks . "?)", array_values($args)
      );
    }


    /**
     * Upraví vybraný záznam v databázi.
     *
     * @param string $table            Databázová tabulka
     * @param array $args              Pole s názvy atributů a hodnotami zapisovanými do databáze
     * @param string $whereCondition   Podmínka, na základě které bude upravován záznam v databázi (WHERE column = ...)
     * @param array $whereCondValues   Hodnoty pro podmínku
     * @return int                     Počet ovlivněných řádků SQL dotazem
     */
    public static function update($table, $args, $whereCondition, $whereCondValues = []) {
      $args = self::convertArgsToArray($args);
      $whereCondValues = self::convertArgsToArray($whereCondValues);

      return self::queryAffectedRows(
        "UPDATE `$table`
              SET `" . implode('` = ?, `', array_keys($args)) . '` = ? ' . $whereCondition,
        array_merge(array_values($args), $whereCondValues)
      );
    }


    /**
     * Připraví parametry pro SQL dotaz tak, aby se vždy jednalo o pole parametrů.
     *
     * @param string|array $args       Parametr nebo pole parametrů pro SQL dotaz.
     * @return array                   Pole parametrů pro SQL dotaz.
     */
    private static function convertArgsToArray($args) {
      if (!is_array($args)) {
        $args = [$args];
      }

      return $args;
    }
  }

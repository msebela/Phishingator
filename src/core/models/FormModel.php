<?php
  /**
   * Třída určená k obsluze formuláře webové stránky
   * a ke správě dat v něm zadaných.
   *
   * @author Martin Šebela
   */
  abstract class FormModel {
    /**
     * @var string      Prefix formuláře.
     */
    public $formPrefix;

    /**
     * @var string      Jméno tabulky v databázi, se kterou je formulář svázán.
     */
    public $dbTableName;

    /**
     * @var array       Pole obsahující názvy všech vstupních polí ve formuláři.
     */
    public $inputNames = [];

    /**
     * @var array       Pole obsahující zadané hodnoty ve vstupních polích ve formuláři.
     */
    public $inputsValues = [];

    /**
     * @var array       Pole obsahující maximální počet znaků pro každé vstupní pole ve formuláři.
     */
    public $inputsMaxLengths = [];

    /**
     * @var array       Data načtená z databáze.
     */
    public $dbRecordData;


    /**
     * Vrátí pole obsahující maximální počet znaků pro každé ze vstupních polí formuláře.
     *
     * @return array                   Pole obsahující informaci o maximálním počtu znaků.
     */
    public function getInputsMaxLengths() {
      return $this->inputsMaxLengths;
    }


    /**
     * Inicializuje nastavení pro formulář a pro každé vstupní pole zavolá funkci pro zjištění
     * maximálního počtu znaků, které může pole obsahovat (na základě limitu uvedeného v databázi).
     *
     * @param string $inputNames       Názvy všech vstupních polí formuláře, o kterých má být veden záznam.
     * @param string $formPrefix       Prefix formulář.
     * @param string $dbTableName      Název tabulky v databázi, se kterou je formulář svázán.
     */
    public function initForm($inputNames, $formPrefix, $dbTableName) {
      $this->inputNames = $inputNames;
      $this->formPrefix = $formPrefix;
      $this->dbTableName = $dbTableName;

      if (!empty($this->dbTableName)) {
        foreach ($this->inputNames as $input) {
          $this->inputsMaxLengths[$input] = $this->getMaxInputLengthFromDB($this->dbTableName, $this->getDbColumnName($input));
        }
      }
    }


    /**
     * Vrátí pole obsahující hodnoty vyplněné v každém ze vstupním polí formuláře.
     *
     * @return array                   Pole obsahující hodnoty pro každé vstupní pole formuláře.
     */
    public function getInputsValues() {
      foreach ($this->inputNames as $input) {
        $this->inputsValues[$input] = $this->getInputValue($input);
      }

      return $this->inputsValues;
    }


    /**
     * Získá zadanou hodnotu vstupního pole tak, že nejdříve ověří, zdali existuje nějaký uživatelský vstup
     * a pokud ne, tak ověří, jestli existuje uživatelský vstup z databáze (např. při úpravě záznamu)
     * a tento vstup vrátí.
     *
     * @param string $name             Název vstupního pole, jehož hodnota se zjišťuje.
     * @return string                  Hodnota ve vstupní poli.
     */
    public function getInputValue($name) {
      $inputName = $this->formPrefix . $name;
      $inputDbName = $this->getDbColumnName($name);
      $inputValue = '';

      if (isset($_POST[$inputName])) {
        $inputValue = $_POST[$inputName];
      }
      elseif (isset($this->dbRecordData) && array_key_exists($inputDbName, $this->dbRecordData)) {
        $inputValue = $this->dbRecordData[$inputDbName];
      }

      return $inputValue;
    }


    /**
     * Načte a zpracuje předaná data.
     *
     * @param array $data              Vstupní data
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function load($data) {
      // Nejprve ověřit CSRF token a pokud je validní, tak zpracovat uživatelský vstup.
      $this->isValidCsrfToken($data);

      foreach ($this->inputNames as $inputName) {
        $inputKey = $this->formPrefix . $inputName;
        $inputName = $this->getClassAttributeName($inputName);

        if (array_key_exists($inputKey, $data)) {
          if (!is_array($data[$inputKey])) {
            $data[$inputKey] = trim($data[$inputKey]);
          }

          $userInput = $data[$inputKey];
        }
        else {
          $userInput = 0;
        }

        $this->{$inputName} = $userInput;
      }
    }


    /**
     * Vrátí název atributu v závislosti na tom, jaká je jmenná konvence názvů atributů v databázi.
     *
     * @param string $input            Název atributu
     * @return string                  Název atributu v databázi
     */
    private function getDbColumnName($input) {
      return str_replace('-', '_', $input);
    }


    /**
     * Vrátí název atributu třídy podle camel notace (camel case).
     *
     * @param string $inputName        Název proměnné
     * @return string                  Název atributu v camel notaci
     */
    function getClassAttributeName($inputName) {
      $variableName = str_replace('-', '', ucwords($inputName, '-'));

      return lcfirst($variableName);
    }


    /**
     * Zkontroluje uživatelský vstup (atributy třídy), který se bude zapisovat do databáze.
     */
    public abstract function validateData();


    /**
     * Ověří, zdali CSRF token získaný z formuláře odpovídá tomu, který byl uživateli vygenerován při přihlášení.
     *
     * @param array $postData          POST data, ve kterých si metoda nalezne odpovídající CSRF token.
     * @throws UserError               Výjimka platná tehdy, pokud CSRF token neodpovídá původně vygenerovanému.
     */
    public function isValidCsrfToken($postData) {
      if (!isset($postData['csrf-token']) || $postData['csrf-token'] !== PermissionsModel::getCsrfToken()) {
        Logger::error('Unauthorized activity (invalid CSRF token).', $postData);

        throw new UserError('Zaznamenána neoprávněná akce! Její provedení bylo zablokováno.', MSG_ERROR);
      }
    }


    /**
     * Vrátí maximální počet znaků, který může obsahovat konkrétní atribut ve zvolené databázové tabulce.
     *
     * @param string $table            Název databázové tabulky
     * @param string $column           Název sloupce v databázové tabulce
     * @return int                     Maximální počet znaků ve sloupci v databázové tabulce nebo 0, pokud se
     *                                 data nepodařilo získat
     */
    private function getMaxInputLengthFromDB($table, $column) {
      $tableWithoutUnderscore = str_replace('_', '', $table);
      $columnWithoutUnderscore = str_replace('_', '', $column);

      /* Pro jistotu ověření, zdali je název a sloupec tabulky složen pouze z alfanumerických znaků
         (než budou parametry předány do SQL dotazu). Výjimečná náhrada za prepared statements. */
      if (ctype_alnum($tableWithoutUnderscore) && ctype_alnum($columnWithoutUnderscore)) {
        $data = Database::querySingle('SHOW COLUMNS FROM `' . $table . '` WHERE FIELD = "' . $column . '"');

        if (!empty($data)) {
          $column = $data['Type'];

          $dataTypesInt = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal'];
          $dataTypes = ['(', ')', 'varchar', 'char', 'unsigned', ' '];

          $length = str_replace($dataTypes, '', $column);

          if (in_array(mb_strtolower(str_replace(range(0, 9), '', $length)), $dataTypesInt)) {
            $length = str_replace($dataTypesInt, '', $length);
          }
          elseif ($length == 'text') {
            $length = 65534;
          }

          return $length;
        }
      }

      return 0;
    }
  }

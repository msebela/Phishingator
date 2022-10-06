<?php
  /**
   * Jednoduchá třída určená k logování (zaznamenávání systémových zpráv) do
   * jednoho konkrétního souboru nastaveného v konfiguraci aplikace.
   *
   * Třída zaznamenává jen ty zprávy, které mají takovou nebo důležitější úroveň
   * specifikovanou v konfiguraci aplikace.
   *
   * @author Martin Šebela
   */
  class Logger {
    /** @var string     Úroveň pro ladění */
    const DEBUG = 'DEBUG';

    /** @var string     Úroveň pro informativní sdělení */
    const INFO = 'INFO';

    /** @var string     Úroveň pro varovné zprávy */
    const WARNING = 'WARN';

    /** @var string     Úroveň pro chybová sdělení */
    const ERROR = 'ERROR';


    /**
     * Zaznamená ladící informace.
     *
     * @param string $message          Obsah zprávy
     * @param null|string|array $data  Zaznamenaná data k uložení (nepovinný parametr)
     */
    public static function debug($message, $data = null) {
      self::writeRecord($message, Logger::DEBUG, $data);
    }


    /**
     * Zaznamená běžné události.
     *
     * @param string $message          Obsah zprávy
     * @param null|string|array $data  Zaznamenaná data k uložení (nepovinný parametr)
     */
    public static function info($message, $data = null) {
      self::writeRecord($message, Logger::INFO, $data);
    }


    /**
     * Zaznamená varování aplikace.
     *
     * @param string $message          Obsah zprávy
     * @param null|string|array $data  Zaznamenaná data k uložení (nepovinný parametr)
     */
    public static function warning($message, $data = null) {
      self::writeRecord($message, Logger::WARNING, $data);
    }


    /**
     * Zaznamená chybové stavy aplikace.
     *
     * @param string $message          Obsah zprávy
     * @param null|string|array $data  Zaznamenaná data k uložení (nepovinný parametr)
     */
    public static function error($message, $data = null) {
      self::writeRecord($message, Logger::ERROR, $data);
    }


    /**
     * Zformátuje zprávu vkládanou do protokolu všech zaznamenaných zpráv.
     *
     * @param string $message          Obsah zprávy
     * @param string $level            Úroveň zprávy
     * @param null|string|array $data  Zaznamenaná data k uložení (nepovinný parametr)
     * @return string                  Zformátovaná zpráva
     */
    private static function getFormattedMessage($message, $level, $data = null) {
      $time = date(LOGGER_DATE_FORMAT);
      $level = str_pad($level, 5, ' ');

      // Soubor, ve kterém dochází k nějaké akci.
      if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['REQUEST_URI'])) {
        $filepath = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
      }
      else {
        // Pokud se soubor nepodařilo zjistit, je to pravděpodobně CRON...
        $filepath = '';
      }

      // Přidání informace o užvateli, jsou-li k dispozici.
      if (!empty(PermissionsModel::getUserId())) {
        $user = '[' . PermissionsModel::getUserName() . ' (' . PermissionsModel::getUserId() . ')]';
      }
      else {
        $user = '';
      }

      // Data (nepovinný parametr).
      if ($data != null) {
        $data = '[' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ']';
      }
      else {
        $data = '';
      }

      return trim("$time [$level] : [$filepath] $user - $message $data");
    }


    /**
     * Ověří, zdali je zadanou zprávu povoleno zapsat do protokolu (na základě maximální úrovně zpráv nastavené
     * v konfiguračním souboru).
     *
     * Pořadí úrovní zpráv je následující: DEBUG < INFO < WARNING < ERROR
     *
     * @param string $level            Úroveň zprávy
     * @return int                     TRUE pokud je povoleno zprávu zapsat do protokolu, jinak FALSE.
     */
    private static function checkLevel($level) {
      $levelsOrder = [Logger::DEBUG, Logger::INFO, Logger::WARNING, Logger::ERROR];

      $maxLevelOrder = array_search(LOGGER_LEVEL, $levelsOrder);

      if (in_array($maxLevelOrder, $levelsOrder) && array_search($level, $levelsOrder) >= $maxLevelOrder) {
        return true;
      }

      return false;
    }


    /**
     * Zapíše zprávu do protokolu všech zpráv (pokud je její úroveň nastavena tak, aby mohla být do protokolu vložena).
     *
     * @param string $message          Obsah zprávy
     * @param string $level            Úroveň zprávy
     * @param null|string|array $data  Zaznamenaná data k uložení (nepovinný parametr)
     */
    private static function writeRecord($message, $level, $data = null) {
      if (self::checkLevel($level)) {
        $message = self::getFormattedMessage($message, $level, $data);

        file_put_contents(LOGGER_FILEPATH, $message . PHP_EOL, FILE_APPEND);
      }
    }
  }
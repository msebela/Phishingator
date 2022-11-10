<?php
  //
  // Soubor obsahující obecné funkce používané napříč zdrojovým kódem.
  //
  // @author Martin Šebela
  //

  /**
   * Automaticky importuje PHP třídy, které aplikace vyžaduje.
   *
   * @param string $className          Název třídy
   */
  function autoload_functions($className) {
    if (preg_match('/Controller$/', $className)) {
      require CORE_DOCUMENT_ROOT . '/' . CORE_DIR_CONTROLLERS . '/' . $className . '.php';
    }

    $modelClassLocation = CORE_DOCUMENT_ROOT . '/' . CORE_DIR_MODELS . '/' . $className . '.php';

    if (file_exists($modelClassLocation)) {
      require $modelClassLocation;
    }
  }


  /**
   * Upraví nastavení PHP dle požadované lokalizace.
   */
  function init_locales() {
    date_default_timezone_set(PHP_TIME_ZONE);
    mb_internal_encoding(PHP_MULTIBYTE_ENCODING);
  }


  /**
   * Nastaví zabezpečené HTTP hlavičky.
   */
  function init_http_security_headers() {
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Permissions-Policy: accelerometer=(); ambient-light-sensor=(); autoplay=(); battery=(); camera=(); display-capture=(); document-domain=(); encrypted-media=(); fullscreen=(); gamepad=(); geolocation=(); gyroscope=(); layout-animations=(); magnetometer=(); microphone=(); midi=(); payment=(); picture-in-picture=(); speaker-selection=(); usb=(); xr-spatial-tracking=()");
    header("Content-Security-Policy: default-src 'self'; upgrade-insecure-requests; script-src 'self' '" . ((!defined('HTTP_HEADER_CSP_NONCE')) ? "unsafe-inline" : "nonce-" . HTTP_HEADER_CSP_NONCE) . "'; font-src 'self' data: fonts.gstatic.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; img-src 'self' data:");
  }


  /**
   * Nastaví a spustí zabezpečnou formu SESSIONS (resp. COOKIES).
   */
  function init_secure_session_start() {
    ini_set('session.cookie_secure', true);
    ini_set('session.cookie_httponly', true);

    session_name('__Host-PHPSESSID');
    session_start();
  }


  /**
   * Rozdělí e-mailovou adresu na části a vrátí tu část, která je požadována.
   *
   * @param string $email              Zkoumaný e-mail
   * @param null|string $requiredPart  Název části, která má být vrácena ("username" nebo "domain"),
   *                                   nebo NULL (tj. všechny části)
   * @return array|string|null         Zvolená část (nebo části) e-mailu
   */
  function get_email_part($email, $requiredPart = null) {
    $returnPart = null;
    $symbol = '@';

    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    $emailParts = explode($symbol, $email);

    if (strpos($email, $symbol) !== false) {
      if ($requiredPart == 'username') {
        $returnPart = $emailParts[0] ?? null;
      }
      elseif ($requiredPart == 'domain') {
        $returnPart = $emailParts[1] ?? null;
      }
      elseif ($requiredPart == null) {
        $returnPart = $emailParts;
      }
    }

    return $returnPart;
  }


  /**
   * Vrátí název protokolu použitého v URL adrese.
   *
   * @param string $url                URL adresa
   * @return string                    Protokol
   */
    function get_protocol_from_url($url) {
    $protocol = '';

    if (!empty($url)) {
      $protocol = parse_url($url, PHP_URL_SCHEME);
    }

    return strtolower($protocol);
  }


  /**
   * Vrátí doménu včetně subdomén z konkrétní URL adresy.
   *
   * @param string $url                URL adresa
   * @return string                    Název domény včetně subdomén
   */
  function get_hostname_from_url($url) {
    $host = '';

    if (!empty($url)) {
      $host = parse_url($url, PHP_URL_HOST);
    }

    return $host;
  }


  /**
   * Vrátí název domény (second level domain) z konkrétní URL adresy.
   *
   * @param string $url                URL adresa
   * @return string|null               Název domény
   */
  function get_domain_from_url($url) {
    $domain = parse_url($url, PHP_URL_HOST);
    $secondLevelDomain = null;

    if ($domain != null) {
      $hostNames = explode('.', $domain);

      if (count($hostNames) >= 2) {
        $secondLevelDomain = $hostNames[count($hostNames) - 2] . '.' . $hostNames[count($hostNames) - 1];
      }
    }

    return $secondLevelDomain;
  }


  /**
   * Vrátí základní URL adresu včetně použitého protokolu (HTTP/HTTPS), kde aplikace běží.
   *
   * @return string                    Základní URL adresa
   */
  function get_base_url() {
    return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
  }


  /**
   * Ověří, zdali je předaný vstup číslo a pokud ano, vrátí jej.
   *
   * @param string $string             Testovaný vstup
   * @return bool|int                  Číslo nebo FALSE, pokud se nejedná o číslo
   */
  function get_number_from_get_string($string) {
    $number = false;

    if (isset($string) && is_numeric($string)) {
      $number = (int) $string;
    }

    return $number;
  }


  /**
   * Zformátuje číslo podle zvyklostí v České republice (oddělení jednotlivých řádů apod.).
   *
   * @param int $number                Číslo, které má být zformátováno
   * @return string                    Zformátované číslo
   */
  function get_formatted_number($number) {
    return number_format($number,0, ',', ' ');
  }


  /**
   * Odstraní z řetězce symboly nových řádků a mezery na začátku a na konci řetězce.
   *
   * @param string $string             Upravovaný řetězec
   * @return string                    Řetězec zbavený symbolů nových řádků a mezer na začátku a konci řetězce
   */
  function remove_new_line_symbols($string) {
    return preg_replace('/\r\n|[\r\n]/', '', trim($string));
  }


  /**
   * Nahradí klasické mezery v řetězci za pevné mezery.
   *
   * @param string $string             Upravovaný řetězec
   * @return string                    Upravený řetězec obsahující pevné mezery
   */
  function insert_nonbreaking_spaces($string) {
    return str_replace(' ', '&nbsp;', $string);
  }
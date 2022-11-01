<?php
  // Definice DOCUMENT_ROOT, který v CRONu (v rámci kterého je tento soubor spouštěn) neexistuje.
  $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);

  require $_SERVER['DOCUMENT_ROOT'] . '/base.php';

  define('APACHE_CONF_SITES_DIR', '/etc/apache2/sites-available/');

  function preg_match_in_file($file, $regex, $matchToReturn = 1) {
    $data = '';

    foreach (file($file) as $line) {
      preg_match($regex, $line, $matches);

      if (isset($matches[$matchToReturn])) {
        $data = $matches[$matchToReturn];
        break;
      }
    }

    return $data;
  }


  $files = scandir(PHISHING_WEBSITE_APACHE_SITES_DIR);
  $changes = false;

  foreach ($files as $file) {
    if (strpos($file, '.conf') !== false) {
      $filepath = PHISHING_WEBSITE_APACHE_SITES_DIR . $file;

      $serverName = preg_match_in_file($filepath, '/ServerName (.*)/');
      $serverNameWithProtocol = strpos($serverName, 'http') !== 0 ? 'http://' . $serverName : $serverName;

      if (filter_var($serverNameWithProtocol, FILTER_VALIDATE_URL)) {
        // Aktivace podvodné stránky v Apache.
        if (strpos($file, '.conf.new') !== false) {
          copy($filepath, APACHE_CONF_SITES_DIR . $serverName . '.conf');
          exec('a2ensite ' . $serverName);
          rename($filepath, PHISHING_WEBSITE_APACHE_SITES_DIR . $serverName . '.conf');

          echo 'copy(' . $filepath . ', ' . APACHE_CONF_SITES_DIR . $serverName . '.conf)' . "\n";
          echo 'a2ensite ' . $serverName . "\n";
          echo 'rename(' . $filepath . ', ' . PHISHING_WEBSITE_APACHE_SITES_DIR . $serverName . '.conf' . ')' . "\n";

          $changes = true;
        }

        // Deaktivace podvodné stránky v Apache.
        elseif (strpos($file, '.conf.delete') !== false) {
          exec('a2dissite ' . $serverName);
          unlink($filepath);

          echo 'a2dissite ' . $serverName . "\n";
          echo 'unlink(' . $filepath . ')' . "\n";

          $changes = true;
        }

        // Aktivace již z dřívějška existující podvodné stránky v Apache.
        elseif (!file_exists(APACHE_CONF_SITES_DIR . $file)) {
          copy($filepath, APACHE_CONF_SITES_DIR . $serverName . '.conf');
          exec('a2ensite ' . $serverName);

          echo 'copy(' . $filepath . ', ' . APACHE_CONF_SITES_DIR . $serverName . '.conf)' . "\n";
          echo 'a2ensite ' . $serverName . "\n";

          $changes = true;
        }
      }
    }
  }

  if ($changes) {
    exec('apachectl graceful');

    echo 'apachectl graceful' . "\n";
  }
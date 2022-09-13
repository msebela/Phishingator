<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 0);


  // Začlenění konfigurace a důležitých funkcí.
  require $_SERVER['DOCUMENT_ROOT'] . '../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../globalFunctions.php';

  // Automatické začlenění funkcí.
  spl_autoload_register('autoload_functions');

  // Inicializace zabezpečených SESSION a HTTP hlaviček.
  init_secure_session_start();
  init_http_security_headers();

  // Nastavení PHP multibyte kódování.
  mb_internal_encoding(PHP_MULTIBYTE_ENCODING);

  // Vytvoření nové instance routeru, který zpracovává příchozí požadavky.
  $router = new RouterController();

  $router->process($_SERVER['REQUEST_URI']);
  $router->displayView();

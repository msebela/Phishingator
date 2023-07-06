<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 0);


  require $_SERVER['DOCUMENT_ROOT'] . '../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../globalFunctions.php';

  spl_autoload_register('autoload_functions');

  init_locales();
  init_secure_session_start();
  init_http_security_headers();

  Database::connect();

  $model = new PermissionsModel();

  // Přihlášení uživatele dle parametrů z SSO (pokud již není přihlášen).
  if ($model->getUserId() === null) {
    $model->login($_SERVER['REMOTE_USER'] ?? '');
  }

  // Router, který zpracovává příchozí požadavky.
  $router = new RouterController();

  $router->process($_SERVER['REQUEST_URI']);
  $router->displayView();

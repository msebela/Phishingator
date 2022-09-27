<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);


  require $_SERVER['DOCUMENT_ROOT'] . '../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../globalFunctions.php';

  spl_autoload_register('autoload_functions');

  init_secure_session_start();
  init_http_security_headers();

  mb_internal_encoding(PHP_MULTIBYTE_ENCODING);

  Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

  // TODO: Smazat
  if (getenv('DOCKER_REMOTE_USER')) {
    $_SERVER['REMOTE_USER'] = getenv('DOCKER_REMOTE_USER');
  }

  $model = new PermissionsModel();

  // Přihlášení uživatele dle parametrů z SSO (pokud již není přihlášen).
  if ($model->getUserId() === null) {
    $model->login($_SERVER['REMOTE_USER'] ?? '');
  }

  // Vytvoření nové instance routeru, který zpracovává příchozí požadavky.
  $router = new RouterController();

  $router->process($_SERVER['REQUEST_URI']);
  $router->displayView();

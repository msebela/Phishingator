<?php
  ini_set('display_errors', 0);

  session_start();

  // Začlenění nutných souborů (vůči adresáři templates/websites/nazev-podvodne-stranky/).
  require $_SERVER['DOCUMENT_ROOT'] . '../../../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../../../globalFunctions.php';

  init_locales();

  spl_autoload_register('autoload_functions');

  $prepender = new WebsitePrependerModel();

  $message = $prepender->getDisplayMessage();
  $username = Controller::escapeOutput($prepender->getUsername());
  $email = Controller::escapeOutput($prepender->getEmail());
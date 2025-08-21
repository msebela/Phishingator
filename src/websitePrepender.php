<?php
  ini_set('display_errors', 0);

  session_start();

  // Začlenění nutných souborů (vůči DOCUMENT_ROOT v konfiguračním souboru podvodné stránky).
  require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/../globalFunctions.php';

  init_locales();

  spl_autoload_register('autoload_functions');

  $template = new WebsitePrependerModel();

  // Chybová hláška pro šablonu podvodné stránky.
  $message = $template->getDisplayMessage();

  // Data pro šablonu podvodné stránky.
  $username = Controller::escapeOutput($template->getUsername());
  $email = Controller::escapeOutput($template->getEmail());
  $service = Controller::escapeOutput($template->getServiceName());
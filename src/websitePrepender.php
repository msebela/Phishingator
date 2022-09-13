<?php
  session_start();

  // Začlenění nutných souborů (vůči adresáři templates/websites/nazev-podvodne-stranky/).
  require $_SERVER['DOCUMENT_ROOT'] . '../../../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../../../globalFunctions.php';

  mb_internal_encoding(PHP_MULTIBYTE_ENCODING);
  spl_autoload_register('autoload_functions');

  $prepender = new WebsitePrependerModel();
  $message = $prepender->getDisplayMessage();
<?php
  // Definice DOCUMENT_ROOT, který v CRONu (v rámci kterého je tento soubor spouštěn) neexistuje.
  $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);

  require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/../globalFunctions.php';

  init_locales();

  spl_autoload_register('autoload_functions');
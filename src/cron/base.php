<?php
  require $_SERVER['DOCUMENT_ROOT'] . '../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../globalFunctions.php';

  init_locales();

  spl_autoload_register('autoload_functions');
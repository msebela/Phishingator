<?php
  require $_SERVER['DOCUMENT_ROOT'] . '../config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../globalFunctions.php';

  mb_internal_encoding(PHP_MULTIBYTE_ENCODING);
  spl_autoload_register('autoload_functions');
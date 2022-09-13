<?php
  // Definice DOCUMENT_ROOT, který v CRONu (v rámci kterého je tento soubor spouštěn) neexistuje.
  $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);

  require $_SERVER['DOCUMENT_ROOT'] . '/phpmailer/src/Exception.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/phpmailer/src/PHPMailer.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/phpmailer/src/SMTP.php';

  require $_SERVER['DOCUMENT_ROOT'] . '/config.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/globalFunctions.php';

  mb_internal_encoding(PHP_MULTIBYTE_ENCODING);
  spl_autoload_register('autoload_functions');

  Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

  $mailer = new EmailSenderModel();
  $mailer->startSendingEmails();

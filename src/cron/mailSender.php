<?php
  // Definice DOCUMENT_ROOT, který v CRONu (v rámci kterého je tento soubor spouštěn) neexistuje.
  $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);

  require $_SERVER['DOCUMENT_ROOT'] . '/base.php';

  require $_SERVER['DOCUMENT_ROOT'] . '../phpmailer/src/Exception.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../phpmailer/src/PHPMailer.php';
  require $_SERVER['DOCUMENT_ROOT'] . '../phpmailer/src/SMTP.php';

  Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

  $mailer = new EmailSenderModel();
  $mailer->startSendingEmails();

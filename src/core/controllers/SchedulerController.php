<?php
  /**
   * Třída řešící požadavky na spuštění opakujících se činností.
   *
   * @author Martin Šebela
   */
  class SchedulerController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function process($arguments) {
      $this->checkPermissionCall();

      if (isset($_GET['action'])) {
        if ($_GET['action'] == 'send-emails') {
          $this->sendEmails();
        }
        elseif ($_GET['action'] == 'send-notifications') {
          $this->sendNotifications();
        }
        elseif ($_GET['action'] == 'synchronize-users-data') {
          $this->synchronizeUsersData();
        }
        elseif ($_GET['action'] == 'deploy-websites') {
          $this->deployWebsites();
        }
      }

      exit();
    }


    /**
     * Vrátí, zdali je zdrojová IP adresa oprávněná k volání metod.
     *
     * @return bool
     */
    public static function isValidSourceIP() {
      $valid = false;

      if (!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
          && $_SERVER['REMOTE_ADDR'] == gethostbyname('scheduler')) {
        $valid = true;
      }

      return $valid;
    }


    /**
     * Ověří, zdali jsou pro volání metod dostatečná oprávnění.
     *
     * @return void
     */
    private function checkPermissionCall() {
      if (!self::isValidSourceIP()) {
        Logger::error('Unauthorized access to call scheduler job.', $_SERVER['REMOTE_ADDR']);
        $invalid = true;
      }

      $token = $_SERVER['HTTP_PHISHINGATOR_TOKEN'] ?? '';

      if ($token != getenv('PHISHINGATOR_TOKEN')) {
        Logger::error('Invalid token to call scheduler job.', $token);
        $invalid = true;
      }

      if (isset($invalid)) {
        http_response_code(403);
        exit();
      }
    }


    /**
     * Zavolá metodu pro odesílání cvičných podvodných e-mailů.
     *
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendEmails() {
      self::requirePhpMailerClasess();
      Database::connect();

      $mailer = new EmailSenderModel();
      $mailer->startSendingEmails();
    }


    /**
     * Zavolá metodu pro odesílání e-mailů s notifikacemi.
     *
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendNotifications() {
      self::requirePhpMailerClasess();
      Database::connect();

      $mailer = new NotificationsModel();
      $mailer->startSendingNotifications();
    }


    /**
     * Zavolá metodu pro synchronizaci dat o uživatelích.
     *
     * @return void
     */
    private function synchronizeUsersData() {
      Database::connect();

      UsersModel::synchronizeUsers();
    }


    /**
     * Zavolá metodu pro aktivaci podvodných stránek.
     *
     * @return void
     */
    private function deployWebsites() {
      PhishingWebsiteConfigs::processAllConfigs();
    }


    /**
     * Načte zdrojové soubory knihovny PHPMailer.
     *
     * @return void
     */
    private function requirePhpMailerClasess() {
      require $_SERVER['DOCUMENT_ROOT'] . '/../phpmailer/src/Exception.php';
      require $_SERVER['DOCUMENT_ROOT'] . '/../phpmailer/src/PHPMailer.php';
      require $_SERVER['DOCUMENT_ROOT'] . '/../phpmailer/src/SMTP.php';
    }
  }
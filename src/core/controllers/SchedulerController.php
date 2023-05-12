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
     * Ověří, zdali jsou pro volání metod dostatečná oprávnění.
     *
     * @return void
     */
    private function checkPermissionCall() {
      if ($_SERVER['REMOTE_ADDR'] != SCHEDULER_ALLOWED_IP) {
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
      Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

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
      Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

      $mailer = new NotificationsModel();
      $mailer->startSendingNotifications();
    }


    /**
     * Zavolá metodu pro synchronizaci dat o uživatelích.
     *
     * @return void
     */
    private function synchronizeUsersData() {
      Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

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
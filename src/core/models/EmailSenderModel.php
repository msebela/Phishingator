<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;

  /**
   * Třída se zabývá odesíláním e-mailů a zjišťováním informací
   * o již odeslaných e-mailech.
   *
   * @author Martin Šebela
   */
  class EmailSenderModel extends EmailSender {
    /**
     * Vrátí celkový počet všech odeslaných e-mailů (případně v konkrétním roce).
     *
     * @param int $year                Zkoumaný rok [nepovinné]
     * @return mixed                   Počet odeslaných e-mailů
     */
    public static function getCountOfSentEmails($year = []) {
      $yearQuery = (!is_array($year) && is_numeric($year)) ? 'YEAR(`date_sent`) = ?' : '`date_sent` IS NOT NULL';

      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              WHERE ' . $yearQuery . '
              AND phg_campaigns.visible = 1
      ', $year);
    }


    /**
     * Vrátí celkový počet odeslaných e-mailů v rámci konkrétní kampaně, případně v rámci několika kampaní.
     *
     * @param int|array $idCampaign    ID konkrétní kampaně nebo pole s ID několika kampaní
     * @return mixed                   Počet odeslaných e-mailů
     */
    public static function getCountOfSentEmailsInCampaign($idCampaign) {
      $cols = (is_array($idCampaign)) ? str_repeat(' OR `id_campaign` = ?', count($idCampaign) - 1) : '';

      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              WHERE `id_campaign` = ?' . $cols . '
              AND `date_sent` IS NOT NULL
      ', $idCampaign);
    }


    /**
     * Zjistí počet e-mailů, které byly odeslány konkrétnímu uživateli.
     *
     * @param int $idUser              ID uživatele
     * @return mixed                   Počet odeslaných e-mailů
     */
    public static function getCountOfRecievedEmails($idUser) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              WHERE `id_user` = ?
              AND `date_sent` IS NOT NULL
              AND phg_campaigns.visible = 1
      ', $idUser);
    }


    /**
     * Ověří, zdali byl v konkrétní kampani danému uživateli odeslán podvodný e-mail.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @return bool                    TRUE pokud ano, FALSE pokud ne.
     */
    public function isEmailSent($idCampaign, $idUser) {
      $result = Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              WHERE `id_campaign` = ?
              AND `id_user` = ?
              AND `date_sent` IS NOT NULL
      ', [$idCampaign, $idUser]);

      return $result > 0;
    }


    /**
     * Vrátí seznam kampaní, které jsou právě aktivní.
     *
     * @return mixed                   Pole kampaní
     */
    private function getCampaignsToSend() {
      return Database::queryMulti('
              SELECT `id_campaign`
              FROM `phg_campaigns`
              WHERE `time_send_since` <= TIME(NOW())
              AND `active_since` <= CURDATE()
              AND `active_to` >= CURDATE()
              AND `visible` = 1
      ');
    }


    /**
     * Přidá do databáze záznam o tom, zdali byl e-mail (ne)úspěšně odeslán.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idEmail             ID e-mailu
     * @param int $idUser              ID uživatele
     * @param int $result              1 pokud se e-mail podařilo odeslat, 0 pokud ne.
     */
    private function logSentEmail($idCampaign, $idEmail, $idUser, $result) {
      $record = [
        'id_campaign' => $idCampaign,
        'id_email' => $idEmail,
        'id_user' => $idUser
      ];

      // Pokud se e-mail podařilo úspěšně odeslat, uložit do databáze datum odeslání.
      if ($result == 1) {
        Logger::info('Úspěšné odeslání e-mailu.', $record);

        $record['date_sent'] = date('Y-m-d H:i:s');

        Database::insert('phg_sent_emails', $record);
      }
      else {
        Logger::error('Neúspěšné odeslání e-mailu.', $record);
      }
    }


    /**
     * Sníží uživatelem nastavený limit pro příjem e-mailů o jedna.
     *
     * @param int $idUser              ID uživatele
     */
    private function decrementUserEmailLimit($idUser) {
      $user = UsersModel::getUserEmailLimit($idUser);

      if (!empty($user) && $user['email_limit'] != null) {
        // Nastavení nového limitu a kontrola, zdali nejde limit do záporných čísel.
        $newLimit = (($user['email_limit'] > 0) ? $user['email_limit'] - 1 : 0);

        Logger::info(
          'Snížení zbývajícího limitu obdržených podvodných zpráv uživatele.',
          ['id_user' => $idUser, 'email_limit' => $newLimit]
        );

        Database::update(
          'phg_users',
          ['email_limit' => $newLimit],
          'WHERE `id_user` = ? AND `visible` = 1',
          $idUser
        );
      }
    }


    /**
     * Přidá do databáze záznam o tom, že uživatel v dané kampani zatím žádným způsobem nereagoval.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @param string $usedEmail        E-mail uživatele použitý v kampani
     * @param string $usedGroup        Skupina, která uživateli během kampaně náleží
     */
    private function insertNoReactionRecord($idCampaign, $idUser, $usedEmail, $usedGroup) {
      $record = [
        'id_campaign' => $idCampaign,
        'id_user' => $idUser,
        'id_action' => CAMPAIGN_NO_REACTION_ID,
        'used_email' => $usedEmail,
        'used_group' => $usedGroup
      ];

      Database::insert('phg_captured_data', $record);
    }


    /**
     * Vytvoří novou instanci třídy PHPMailer společně s nastavením
     * společným pro všechny odesílané e-maily.
     *
     * @return PHPMailer               Instance třídy PHPMailer
     */
    public static function getPHPMailerInstance() {
      $mailer = new PHPMailer;

      if (!empty(getenv('SMTP_HOST')) && !empty(getenv('SMTP_PORT'))) {
        $mailer->isSMTP();

        $mailer->Host = getenv('SMTP_HOST');
        $mailer->Port = getenv('SMTP_PORT');

        if (!empty(getenv('SMTP_USERNAME')) && !empty(getenv('SMTP_PASSWORD'))) {
          $mailer->SMTPAuth = true;

          if (getenv('SMTP_TLS')) {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
          }

          // Neuzavírat SMTP spojení po odeslání každého e-mailu.
          $mailer->SMTPKeepAlive = true;

          $mailer->Username = getenv('SMTP_USERNAME');
          $mailer->Password = getenv('SMTP_PASSWORD');
        }
      }

      $mailer->CharSet = 'UTF-8';
      $mailer->Encoding = 'base64';

      // K odstranění názvu klienta, který e-mail zaslal.
      // SMTP::DEBUG_OFF = off (for production use)
      $mailer->SMTPDebug = SMTP::DEBUG_OFF;
      $mailer->XMailer = ' ';

      // Speciální hlavička určená k identifikaci cvičného phishingu z tohoto systému.
      $mailer->addCustomHeader(PHISHING_EMAIL_HEADER_ID, PHISHING_EMAIL_HEADER_VALUE);

      return $mailer;
    }


    /**
     * Odešle e-mail v rámci instance třídy PHPMailer.
     *
     * @param PHPMailer $mailer        Instance třídy PHPMailer
     * @param string $senderEmail      E-mail odesílatele
     * @param string $senderName       Jméno odesílatele
     * @param string $recipientEmail   E-mail příjemce
     * @param string $subject          Předmět e-mailu
     * @param string $body             Tělo e-mailu
     * @return bool                    Výsledek odeslání e-mailu
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendEmail($mailer, $senderEmail, $senderName, $recipientEmail, $subject, $body) {
      $mailer->setFrom($senderEmail, $senderName);
      $mailer->addAddress($recipientEmail);

      $mailer->Subject = $subject;
      $mailer->Body = $body;

      return $mailer->send();
    }


    /**
     * Aktivuje odesílání e-mailů.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function startSendingEmails() {
      $campaignModel = new CampaignModel();

      /* Počet odeslaných e-mailů. */
      $countSentMails = 0;

      /* Zjištění kampaní, které jsou aktivní. */
      $campaigns = $this->getCampaignsToSend();

      /* Získání nové instance PHPMailer. */
      $mailer = $this->getPHPMailerInstance();

      foreach ($campaigns as $campaign) {
        /* Získání zasílaného e-mailu, příjemců apod. z databáze. */
        $campaign = $campaignModel->getCampaignDetail($campaign['id_campaign']);
        $recipients = $campaignModel->getCampaignRecipients($campaign['id_campaign']);

        echo '<h1>KAMPAŇ ' . $campaign['id_campaign'] . '</h1>' . "\n";

        foreach ($recipients as $recipient) {
          $user = UsersModel::getUserByEmail($recipient);

          /* Ověření, zdali už nedošlo v rámci kampaně k odeslání e-mailu stejnému uživateli někdy dříve. */
          if ($this->isEmailSent($campaign['id_campaign'], $user['id_user'])) {
            continue;
          }

          /* Pokud byl odesílatel u předchozí iterace stejný jako příjemce, provést vyresetování. */
          if (isset($senderEmailMe)) {
            $campaign['sender_email'] = VAR_RECIPIENT_EMAIL;
          }

          /* Změnit odesílatele na e-mail příjemce, pokud byl tak podvodný e-mail vytvořen. */
          if ($campaign['sender_email'] == VAR_RECIPIENT_EMAIL) {
            $senderEmailMe = true;
            $campaign['sender_email'] = $recipient;
          }

          /* Personalizace těla e-mailu na základě příjemce. */
          $campaign['body_personalized'] = PhishingEmailModel::personalizeEmailBody(
            ['email' => $recipient],
            $campaign['body'],
            $campaign['url_protocol'] . $campaign['url'] . '/',
            $campaign['id_campaign']
          );



          /* Testovací výpisy. */
          echo '<pre>' . "\n";
          echo '<b>From:</b> ' . PhishingEmailModel::formatEmailSender($campaign['sender_email'], $campaign['sender_name']) . "\n";
          echo '<b>To:</b> ' . Controller::escapeOutput($recipient) . "\n";
          echo '<b>Subject:</b> ' . Controller::escapeOutput($campaign['subject']) . "\n\n";
          echo Controller::escapeOutput($campaign['body_personalized']) . "\n\n";
          echo '</pre>' . "\n";



          // Poslání e-mailu.
          $mailResult = $this->sendEmail(
            $mailer,
            $campaign['sender_email'], $campaign['sender_name'], $recipient,
            $campaign['subject'], $campaign['body_personalized']
          );

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat a případná dekrementace uživatelova omezení.
          if ($mailResult) {
            // Pokud se e-mail podařilo úspěšně odeslat.
            $this->logSentEmail($campaign['id_campaign'], $campaign['id_email'], $user['id_user'], 1);
            $this->decrementUserEmailLimit($user['id_user']);

            echo '<p><b style="color: green;">E-mail odeslan!</b></p>' . "\n";
          }
          else {
            // Pokud se e-mail nepodařilo odeslat.
            $this->logSentEmail($campaign['id_campaign'], $campaign['id_email'], $user['id_user'], 0);

            echo '<p><b style="color: red;">E-mail se nepodarilo odeslat:</b></p>' . $mailer->ErrorInfo . "\n";
          }

          // Vložení záznamu do databáze o tom, že uživatel zatím na kampaň nereagoval.
          $this->insertNoReactionRecord($campaign['id_campaign'], $user['id_user'], $recipient, $user['primary_group']);

          echo '<hr>' . "\n";

          // Vyčištění pro další iteraci.
          $mailer->clearAddresses();

          // Uspání skriptu po odeslání určitého množství e-mailů.
          $countSentMails = $this->sleepSender($countSentMails);
        }
      }
    }
  }

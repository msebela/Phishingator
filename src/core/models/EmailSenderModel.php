<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;

  /**
   * Třída řešící odesílání e-mailů a zjišťování informací o již odeslaných e-mailech.
   *
   * @author Martin Šebela
   */
  class EmailSenderModel extends EmailSender {


    /**
     * Vytvoří novou instanci třídy PHPMailer společně s nastavením společným pro všechny odesílané e-maily.
     *
     * @return PHPMailer               Instance třídy PHPMailer
     * @throws \PHPMailer\PHPMailer\Exception
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
    public static function sendEmail($mailer, $senderEmail, $senderName, $recipientEmail, $subject, $body) {
      $mailer->setFrom($senderEmail, $senderName);
      $mailer->addAddress($recipientEmail);

      $mailer->Subject = $subject;
      $mailer->Body = $body;

      return $mailer->send();
    }


    /**
     * Přidá do databáze záznam o tom, zdali byl e-mail (ne)úspěšně odeslán.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idEmail             ID e-mailu
     * @param int $idUser              ID uživatele
     * @param int $result              1 pokud se e-mail podařilo odeslat, 0 pokud ne
     * @param string $errorMessage     Chybová hláška, pokud se e-mail nepodařilo odeslat (nepovinné)
     */
    private function logSentEmail($idCampaign, $idEmail, $idUser, $result, $errorMessage = false) {
      $record = [
        'id_campaign' => $idCampaign,
        'id_email' => $idEmail,
        'id_user' => $idUser
      ];

      // Pokud se e-mail podařilo úspěšně odeslat, uložit do databáze datum odeslání.
      if ($result == 1) {
        Logger::info('Phishing email sent successfully.', $record);

        $record['date_sent'] = date('Y-m-d H:i:s');

        Database::insert('phg_sent_emails', $record);
      }
      else {
        Logger::error('Failure to send phishing email: ' . Controller::escapeOutput($errorMessage), $record);
      }
    }


    /**
     * Ověří, zdali byl v konkrétní phishingové kampani danému uživateli odeslán podvodný e-mail.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @return bool                    TRUE pokud ano, FALSE pokud ne
     */
    private function isEmailSent($idCampaign, $idUser) {
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
     * Vrátí celkový počet všech odeslaných e-mailů (případně do konkrétního roku).
     *
     * @param int $year                Maximální rok, do kterého se bude počet odeslaných e-mailů zjišťovat [nepovinné]
     * @return mixed                   Počet odeslaných e-mailů
     */
    public static function getCountOfSentEmails($year = []) {
      $yearQuery = (is_numeric($year)) ? 'YEAR(`date_sent`) <= ?' : '`date_sent` IS NOT NULL';

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
     * Vrátí datum a čas odeslání podvodného e-mailu, který byl odeslán
     * konkrétnímu příjemci z vybrané phishingové kampaně.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idEmail             ID e-mailu
     * @param int $idUser              ID uživatele
     * @return mixed                   Pole s informací o datu a čase odeslání e-mailu
     */
    public static function getDateSentEmail($idCampaign, $idEmail, $idUser) {
      return Database::querySingle('
            SELECT `date_sent`,
            DATE_FORMAT(date_sent, "%e. %c. %Y") AS `date_sent_formatted`,
            DATE_FORMAT(date_sent, "%e. %c. %Y (%k:%i)") AS `datetime_sent_formatted`
            FROM `phg_sent_emails`
            WHERE `id_campaign` = ?
            AND `id_email` = ?
            AND `id_user` = ?
      ', [$idCampaign, $idEmail, $idUser]);
    }


    /**
     * Aktivuje odesílání cvičných podvodných e-mailů z phishingových kampaní.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function startSendingEmails() {
      $mailer = $this->getPHPMailerInstance();

      $countSentMails = 0;

      // Seznam kampaní, u kterých je možné zahájit rozesílání e-mailů.
      $campaigns = CampaignModel::getActiveCampaignsToSend();

      foreach ($campaigns as $campaign) {
        $campaign = CampaignModel::getCampaignDetail($campaign['id_campaign']);
        $recipients = CampaignModel::getCampaignRecipients($campaign['id_campaign']);

        foreach ($recipients as $recipient) {
          $user = UsersModel::getUserByEmail($recipient);

          // Ověření, zdali nedošlo v rámci kampaně k odeslání e-mailu stejnému uživateli někdy dříve.
          if ($this->isEmailSent($campaign['id_campaign'], $user['id_user'])) {
            continue;
          }

          // Pokud byl e-mail odesílatele u předchozí iterace stejný jako e-mail příjemce, provést vyresetování.
          if (isset($senderEmailMe)) {
            $campaign['sender_email'] = VAR_RECIPIENT_EMAIL;
          }

          // Změnit odesílatele na e-mail příjemce, pokud byl tak podvodný e-mail vytvořen.
          if ($campaign['sender_email'] == VAR_RECIPIENT_EMAIL) {
            $senderEmailMe = true;
            $campaign['sender_email'] = $recipient;
          }

          // Personalizace těla e-mailu na základě příjemce.
          $campaign['body_personalized'] = PhishingEmailModel::personalizeEmailBody(
            ['email' => $recipient],
            $campaign['body'],
            $campaign['url_protocol'] . $campaign['url'] . '/',
            $campaign['id_campaign']
          );

          Logger::info('Phishing e-mail ready to send.', [
              'id_campaign' => $campaign['id_campaign'],
              'id_user' => $user['id_user'],
              'sender' => PhishingEmailModel::formatEmailSender($campaign['sender_email'], $campaign['sender_name']),
              'recipient' => Controller::escapeOutput($recipient),
              'subject' => Controller::escapeOutput($campaign['subject']),
              'body' => Controller::escapeOutput($campaign['body_personalized'])
            ]
          );

          // Odeslání e-mailu.
          $mailResult = $this->sendEmail(
            $mailer,
            $campaign['sender_email'], $campaign['sender_name'], $recipient,
            $campaign['subject'], $campaign['body_personalized']
          );

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat a případná dekrementace uživatelova omezení.
          $this->logSentEmail($campaign['id_campaign'], $campaign['id_email'], $user['id_user'], $mailResult, $mailer->ErrorInfo);

          if ($mailResult) {
            ParticipationModel::decrementEmailLimit($user['id_user']);
          }

          // Vložení záznamu do databáze o tom, že uživatel zatím na kampaň nereagoval.
          CampaignModel::insertNoReactionRecord($campaign['id_campaign'], $user['id_user'], $recipient, $user['primary_group']);

          // Vyčištění pro další iteraci.
          $mailer->clearAddresses();

          // Uspání skriptu po odeslání určitého množství e-mailů.
          $countSentMails = $this->sleepSender($countSentMails);
        }
      }
    }
  }

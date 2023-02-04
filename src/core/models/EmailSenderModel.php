<?php
  /**
   * Třída řešící odesílání e-mailů a zjišťování informací o již odeslaných e-mailech.
   *
   * @author Martin Šebela
   */
  class EmailSenderModel extends EmailSender {
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
            $campaign['sender_email'], $campaign['sender_name'], $recipient,
            $campaign['subject'], $campaign['body_personalized']
          );

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat a případná dekrementace uživatelova omezení.
          $this->logSentEmail($campaign['id_campaign'], $campaign['id_email'], $user['id_user'], $mailResult, $this->mailer->ErrorInfo);

          if ($mailResult) {
            ParticipationModel::decrementEmailLimit($user['id_user']);
          }

          // Vložení záznamu do databáze o tom, že uživatel zatím na kampaň nereagoval.
          CampaignModel::insertNoReactionRecord($campaign['id_campaign'], $user['id_user'], $recipient, $user['primary_group']);

          // Vyčištění pro další iteraci.
          $this->mailer->clearAddresses();

          // Uspání skriptu po odeslání určitého množství e-mailů.
          $countSentMails = $this->sleepSender($countSentMails);
        }
      }
    }
  }

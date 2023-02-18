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
            $campaign['url_protocol'] . $campaign['url'],
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

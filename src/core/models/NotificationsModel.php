<?php
  use PHPMailer\PHPMailer\PHPMailer;

  /**
   * Třída řešící odesílání e-mailů s notifikacemi o phishingových kampaní.
   *
   * @author Martin Šebela
   */
  class NotificationsModel extends EmailSender {
    /**
     * @var PHPMailer   Instance třídy PHPMailer
     */
    private PHPMailer $mailer;


    /**
     * Odešle e-mail s notifikací.
     *
     * @param string $recipientEmail   E-mail příjemce
     * @param string $subject          Předmět e-mailu
     * @param string $body             Tělo e-mailu
     * @return bool                    Výsledek odeslání e-mailu
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendEmail($recipientEmail, $subject, $body) {
      return EmailSenderModel::sendEmail(
        $this->mailer, NOTIFICATION_SENDER, WEB_HTML_BASE_TITLE,
        $recipientEmail, WEB_HTML_BASE_TITLE . ' · ' . $subject, $body
      );
    }


    /**
     * Přidá do databáze záznam o tom, zdali byl e-mail s notifikací (ne)úspěšně odeslán.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @param int $notificationType    Typ notifikace:
     *                                    1 = notifikace administrátorům o přidání nové phishingové kampaně
     *                                    2 = notifikace pro administrátory a tvůrce kampaně o ukončení kampaně
     *                                    3 = notifikace pro příjemce kampaně
     * @param int $result              1 pokud se e-mail s notifikací podařilo odeslat, 0 pokud ne
     * @param string $errorMessage     Chybová hláška, pokud se e-mail nepodařilo odeslat (nepovinné)
     */
    private function logSentEmail($idCampaign, $idUser, $notificationType, $result, $errorMessage = false) {
      $record = [
        'id_campaign' => $idCampaign,
        'id_user' => $idUser,
        'id_notification_type' => $notificationType
      ];

      // Pokud se e-mail podařilo úspěšně odeslat, uložit do databáze datum odeslání.
      if ($result == 1) {
        Logger::info('Notification email sent successfully.', $record);

        $record['date_sent'] = date('Y-m-d H:i:s');

        Database::insert('phg_sent_notifications', $record);
      }
      else {
        Logger::error('Failure to send notification email: ' . Controller::escapeOutput($errorMessage), $record);
      }
    }


    /**
     * Ověří, zdali byl uživateli e-mail s notifikací o phishingové kampani někdy dříve odeslán.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele
     * @param int $notificationType    Typ notifikace
     * @return bool                    TRUE pokud byl e-mail v minulosti odeslán, jinak FALSE.
     */
    private function isEmailSent($idCampaign, $idUser, $notificationType) {
      $result = Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_notifications`
              WHERE `id_campaign` = ?
              AND `id_user` = ?
              AND `id_notification_type` = ?
              AND `date_sent` IS NOT NULL
      ', [$idCampaign, $idUser, $notificationType]);

      return $result > 0;
    }


    /**
     * Rozešle všem administrátorům (a tvůrci kampaně) notifikaci o nově přidaných phishingových kampaních.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendNewCampaignsNotifications() {
      // Seznam nově přidaných kampaní.
      $campaigns = CampaignModel::getNewAddedCampaigns();

      foreach ($campaigns as $campaign) {
        $campaignDetail = CampaignModel::getCampaignDetail($campaign['id_campaign']);
        $recipients = UsersModel::getUsersByPermission(PERMISSION_ADMIN);

        // Pokud je tvůrce kampaně uživatel s rolí správce testů, přidat mezi příjemce i jeho.
        if (UsersModel::getUserRole($campaign['id_by_user']) == PERMISSION_TEST_MANAGER) {
          $recipients[] = [
            'id_user' => $campaign['id_by_user'],
            'email' => $campaign['email']
          ];
        }

        // Organizace, ve které k vytvoření phishingové kampaně došlo.
        $campaignOrg = getenv('ORG') . ' (' . getenv('ORG_DOMAIN') . ')';

        // Předmět a obsah notifikace.
        $notificationSubject = 'Nová phishingová kampaň [' . $campaign['id_campaign'] . ']';

        $notificationBody = 'Automatická notifikace systému ' . WEB_HTML_BASE_TITLE . "\n" .
          '-------------------------------------------' . "\n\n" .
          'V systému došlo k vytvoření nové kampaně:' . "\n\n" .
          'Organizace:               ' . $campaignOrg . "\n\n" .
          'Název:                    ' . $campaignDetail['name'] . "\n" .
          'Přidáno:                  ' . $campaign['date_added'] . "\n" .
          'Přidal:                   ' . $campaignDetail['username'] . "\n\n" .
          'Podvodný e-mail:          ' . $campaignDetail['email_name'] . "\n" .
          'URL podvodné stránky:     ' . $campaignDetail['url_protocol'] . $campaignDetail['url'] . "\n" .
          'Šablona podvodné stránky: ' . $campaignDetail['website_name'] . "\n" .
          'Počet příjemců:           ' . $campaignDetail['count_recipients'] . "\n\n" .
          'Aktivní od:               ' . $campaignDetail['active_since_formatted'] . "\n" .
          'Aktivní do:               ' . $campaignDetail['active_to_formatted'] . "\n\n" .
          '-------------------------------------------' . "\n\n" .
          'Detaily vytvořené kampaně jsou k dispozici po přihlášení na URL adrese:' . "\n" .
          WEB_URL . '/portal/campaigns/' . ACT_EDIT . '/' . $campaign['id_campaign'];

        foreach ($recipients as $recipient) {
          // Ověření, zdali nedošlo k odeslání notifikace o vytvoření kampaně stejnému uživateli někdy dříve.
          if ($this->isEmailSent($campaign['id_campaign'], $recipient['id_user'], 1)) {
            continue;
          }

          // Odeslání e-mailu.
          $mailResult = $this->sendEmail($recipient['email'], $notificationSubject, $notificationBody);

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat.
          $this->logSentEmail($campaign['id_campaign'], $recipient['id_user'], 1, $mailResult, $this->mailer->ErrorInfo);

          // Vyčištění pro další iteraci.
          $this->mailer->clearAddresses();
        }
      }
    }


    /**
     * Rozešle všem administrátorům (a tvůrci kampaně) notifikaci o právě ukončených phishingových kampaních.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendFinishedCampaignsNotifications() {
      $statsModel = new StatsModel();

      // Seznam kampaní, které ke včerejšímu dni skončily.
      $campaigns = CampaignModel::getFinishedCampaigns();

      foreach ($campaigns as $campaign) {
        $campaignDetail = CampaignModel::getCampaignDetail($campaign['id_campaign']);
        $recipients = UsersModel::getUsersByPermission(PERMISSION_ADMIN);

        // Pokud je tvůrce kampaně uživatel s rolí správce testů, přidat mezi příjemce i jeho.
        if (UsersModel::getUserRole($campaign['id_by_user']) == PERMISSION_TEST_MANAGER) {
          $recipients[] = [
            'id_user' => $campaign['id_by_user'],
            'email' => $campaign['email']
          ];
        }

        // Získání výsledků kampaně.
        $campaignStats = $statsModel->getStatsForAllEndActions($campaign['id_campaign'], null, true);

        // Organizace, ve které k vytvoření phishingové kampaně došlo.
        $campaignOrg = getenv('ORG') . ' (' . getenv('ORG_DOMAIN') . ')';

        // Předmět a obsah notifikace.
        $notificationSubject = 'Phishingová kampaň ukončena [' . $campaign['id_campaign'] . ']';

        $notificationBody = 'Automatická notifikace systému ' . WEB_HTML_BASE_TITLE . "\n" .
          '-------------------------------------------' . "\n\n" .
          'V systému došlo k ukončení platnosti kampaně:' . "\n\n" .
          'Organizace:               ' . $campaignOrg . "\n\n" .
          'Název:                    ' . $campaignDetail['name'] . "\n" .
          'Přidáno:                  ' . $campaign['date_added'] . "\n" .
          'Přidal:                   ' . $campaignDetail['username'] . "\n\n" .
          'Podvodný e-mail:          ' . $campaignDetail['email_name'] . "\n" .
          'URL podvodné stránky:     ' . $campaignDetail['url_protocol'] . $campaignDetail['url'] . "\n" .
          'Šablona podvodné stránky: ' . $campaignDetail['website_name'] . "\n" .
          'Počet příjemců:           ' . $campaignDetail['count_recipients'] . "\n\n" .
          'Aktivní od:               ' . $campaignDetail['active_since_formatted'] . "\n" .
          'Aktivní do:               ' . $campaignDetail['active_to_formatted'] . "\n\n" .
          '-------------------------------------------' . "\n\n" .
          'Konečné reakce příjemců byly následující:' . "\n\n";

        // Vložení reakcí příjemců do obsahu notifikace.
        foreach ($statsModel->legend as $key => $legend) {
          // Zarovnání hodnoty v obsahu notifikace.
          $value = str_pad($campaignStats[$key], 25 - mb_strlen($legend), ' ', STR_PAD_LEFT);
          $notificationBody .= $legend . ': ' . $value . "\n";
        }

        // Patička notifikace.
        $notificationBody .= "\n" . '-------------------------------------------' . "\n\n" .
          'Detaily ukončené kampaně jsou k dispozici po přihlášení na URL adrese:' . "\n" .
          WEB_URL . '/portal/campaigns/' . ACT_STATS . '/' . $campaign['id_campaign'];

        foreach ($recipients as $recipient) {
          // Ověření, zdali nedošlo k odeslání notifikace o ukončení kampaně někdy dříve.
          if ($this->isEmailSent($campaign['id_campaign'], $recipient['id_user'], 2)) {
            continue;
          }

          // Odeslání e-mailu.
          $mailResult = $this->sendEmail($recipient['email'], $notificationSubject, $notificationBody);

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat.
          $this->logSentEmail($campaign['id_campaign'], $recipient['id_user'], 2, $mailResult, $this->mailer->ErrorInfo);

          // Vyčištění pro další iteraci.
          $this->mailer->clearAddresses();
        }
      }
    }


    /**
     * Rozešle všem příjemcům kampaně notifikaci s informací o tom, že byli součástí
     * phishingové kampaně, a to včetně jejich reakce.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendFinishedCampaignsNotificationsToUsers() {
      $countSentMails = 0;

      // Seznam kampaní, které ke včerejšímu dni skončily.
      $campaigns = CampaignModel::getFinishedCampaigns();

      foreach ($campaigns as $campaign) {
        $campaignDetail = CampaignModel::getCampaignDetail($campaign['id_campaign']);
        $recipients = CampaignModel::getCampaignRecipients($campaign['id_campaign']);

        // Pokud byl tvůrce kampaně uživatel s rolí spráce testů, dojde ke zjištěního jeho
        // jména a příjmení a k jeho doplnění do obsahu notifikace.
        if (UsersModel::getUserRole($campaign['id_by_user']) == PERMISSION_TEST_MANAGER) {
          $ldapModel = new LdapModel();
          $testManager = $ldapModel->getUserCNByUsername(get_email_part($campaign['email'], 'username'));
          $ldapModel->close();
        }
        else {
          $testManager = null;
        }

        foreach ($recipients as $recipient) {
          $user = UsersModel::getUserByEmail($recipient);

          // Ověření, zdali nedošlo k odeslání notifikace o účasti v kampani někdy dříve.
          if ($this->isEmailSent($campaign['id_campaign'], $user['id_user'], 3)) {
            continue;
          }

          // Zjištění uživatelského URL klíče pro vzdělávací stránku.
          $code = WebsitePrependerModel::makeWebsiteUrl($campaign['id_campaign'], $user['url']);

          // Zjištění data a času odeslání podvodného e-mailu konkrétnímu uživateli.
          $emailSent = EmailSenderModel::getDateSentEmail($campaign['id_campaign'], $campaignDetail['id_email'], $user['id_user']);

          // Získání reakce příjemce na phishingovou kampaň.
          $user['reaction'] = CampaignModel::getUserReaction($campaign['id_campaign'], $user['id_user']);

          // Úprava znění notifikace podle reakce příjemce - pokud uživatel (ne)vyplnil platné přihlašovací údaje.
          if ($user['reaction']['id_action'] != CAMPAIGN_VALID_CREDENTIALS_ID) {
            $reactionText = 'Gratulujeme, v testu jste obstáli :)' . "\n\n";
          }
          else {
            $reactionText = '';
          }

          // Předmět a obsah notifikace.
          $notificationSubject = 'Cvičný phishing z ' . $emailSent['date_sent_formatted'];
          $notificationBody =
            'Automatická notifikace systému ' . WEB_HTML_BASE_TITLE . "\n" .
            '-------------------------------------------' . "\n\n" .
            'Dne ' . $emailSent['datetime_sent_formatted'] . ' Vám byl odeslán e-mail "' . $campaignDetail['subject'] . '".' . "\n" .
            'Jednalo se o cvičný phishing (podvodnou zprávu) s typickými znaky, které útočníci' . "\n" .
            'používají při snaze získat Vaše heslo, osobní údaje nebo číslo platební karty.' . "\n\n" .
            $reactionText .
            'E-mail včetně indicií pro jeho rozpoznání si můžete prohlédnout zde:' . "\n" .
            WEB_URL . '/' . ACT_PHISHING_TEST . '/' . $code . "\n\n\n";

          // Pokud je uživatel dobrovolník...
          if ($user['recieve_email'] == 1) {
            $notificationBody .=
              'Děkujeme, že máte zájem vzdělávat se v oblasti phishingu.' . "\n\n" .
              'Váš zbývající počet cvičných phishingových zpráv: ' . ((!is_null($user['email_limit'])) ? $user['email_limit'] : 'nenastaven') . "\n" .
              'Změnu můžete provést po přihlášení na:' . "\n" .
              WEB_URL;
          }
          else {
            // Pokud uživatel není dobrovolník...
            $notificationBody .=
              'Cílem bylo ukázat Vám, čeho jsou útočníci schopni a jak podvodný e-mail' . "\n" .
              '(phishing) rozpoznat. Chcete-li podobné cvičné podvodné zprávy dostávat' . "\n" .
              'pravidelně, zapojte se do projektu ' . WEB_HTML_BASE_TITLE . '. Pomůže Vám lépe' . "\n" .
              'poznat skutečné phishingové útoky s falešnými fakturami, falešnými' . "\n" .
              'přihlašovacími formuláři apod. a budete vědět, na co se v e-mailu' . "\n" .
              'zaměřit a podle čeho rozpoznat typický phishing.';
          }

          if ($testManager != null) {
            $notificationBody .= "\n\n" .
            '-------------------------------------------' . "\n\n" .
            'Tento cvičný phishing pro Vás připravil ' . $testManager . ' (' . $campaign['email'] . ').' . "\n" .
            'Jeho cílem nebylo nachytat Vás, ale zvýšit povědomí o této bezpečnostní hrozbě.';
          }

          Logger::info('Notification ready to send.', [
              'id_campaign' => $campaign['id_campaign'],
              'id_user' => $user['id_user'],
              'recipient' => Controller::escapeOutput($recipient),
              'subject' => Controller::escapeOutput($notificationSubject),
              'body' => Controller::escapeOutput($notificationBody)
            ]
          );

          // Odeslání e-mailu.
          $mailResult = $this->sendEmail($recipient, $notificationSubject, $notificationBody);

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat.
          $this->logSentEmail($campaign['id_campaign'], $user['id_user'], 3, $mailResult, $this->mailer->ErrorInfo);

          // Vyčištění pro další iteraci.
          $this->mailer->clearAddresses();

          // Uspání skriptu po odeslání určitého množství e-mailů.
          $countSentMails = $this->sleepSender($countSentMails);
        }
      }
    }


    /**
     * Aktivuje odesílání e-mailů s notifikacemi.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function startSendingNotifications() {
      $this->mailer = EmailSenderModel::getPHPMailerInstance();

      // Notifikace pro administrátory a správce testů.
      $this->sendNewCampaignsNotifications();
      $this->sendFinishedCampaignsNotifications();

      // Notifikace pro uživatele.
      $this->sendFinishedCampaignsNotificationsToUsers();
    }
  }

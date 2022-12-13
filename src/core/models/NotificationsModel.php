<?php
  use PHPMailer\PHPMailer\PHPMailer;

  /**
   * Třída řešící odesílání e-mailů s notifikacemi
   * o phishingových kampaní v systému.
   *
   * @author Martin Šebela
   */
  class NotificationsModel extends EmailSender {
    /**
     * Ověří, zdali byl uživateli již e-mail s notifikací
     * o phishingové kampani někdy dříve odeslán.
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
     * Vrátí seznam všech nově (k dnešnímu dni) přidaných kampaní.
     *
     * @return mixed                   Pole obsahující informace o nově přidaných kampaní.
     */
    private function getNewAddedCampaigns() {
      return Database::queryMulti('
              SELECT `id_campaign`,
              `username`,
              DATE_FORMAT(phg_campaigns.date_added, "%e. %c. %Y (%k:%i)") AS `date_added`
              FROM `phg_campaigns`
              JOIN `phg_users`
              ON phg_campaigns.id_by_user = phg_users.id_user
              WHERE DATE(phg_campaigns.date_added) = CURDATE()
              AND phg_campaigns.visible = 1
      ');
    }


    /**
     * Vrátí seznam všech kampaní, které ke včerejšímu dni vypršely.
     *
     * @return mixed                   Pole obsahující informace o všech kampaních,
     *                                 které ke včerejšímu dni vypršely.
     */
    private function getEndCampaigns() {
      return Database::queryMulti('
              SELECT `id_campaign`, phg_campaigns.id_by_user, `active_to`,
              `username`, `email`,
              DATE_FORMAT(phg_campaigns.date_added, "%e. %c. %Y (%k:%i)") AS `date_added`,
              DATE_FORMAT(active_to, "%e. %c. %Y") AS `active_to`
              FROM `phg_campaigns`
              JOIN `phg_users`
              ON phg_campaigns.id_by_user = phg_users.id_user
              WHERE `active_to` = DATE_ADD(CURDATE(), INTERVAL -1 DAY)
              AND phg_campaigns.visible = 1
      ');
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
     * @param int $result              1 pokud se e-mail s notifikací podařilo odeslat, 0 pokud ne.
     */
    private function logSentEmail($idCampaign, $idUser, $notificationType, $result) {
      $record = [
        'id_campaign' => $idCampaign,
        'id_user' => $idUser,
        'id_notification_type' => $notificationType
      ];

      // Pokud se e-mail podařilo úspěšně odeslat, uložit do databáze datum odeslání.
      if ($result == 1) {
        Logger::info('Úspěšné odeslání e-mailu s notifikací.', $record);

        $record['date_sent'] = date('Y-m-d H:i:s');
      }
      else {
        Logger::error('Neúspěšné odeslání e-mailu s notifikací.', $record);
      }

      Database::insert('phg_sent_notifications', $record);
    }


    /**
     * Odešle e-mail v rámci instance třídy PHPMailer.
     *
     * @param PHPMailer $mailer        Instance třídy PHPMailer
     * @param string $recipientEmail   E-mail příjemce
     * @param string $subject          Předmět e-mailu
     * @param string $body             Tělo e-mailu
     * @return bool                    Výsledek odeslání e-mailu
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendEmail($mailer, $recipientEmail, $subject, $body) {
      $mailer->setFrom(NOTIFICATION_SENDER, WEB_HTML_BASE_TITLE);
      $mailer->addAddress($recipientEmail);

      $mailer->Subject = WEB_HTML_BASE_TITLE . ' · ' . $subject;
      $mailer->Body = $body;

      return $mailer->send();
    }


    /**
     * Rozešle všem administrátorům notifikaci o nových phishingových kampaních.
     *
     * @param PHPMailer $mailer        Instance třídy PHPMailer
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendNewCampaignsNotifications($mailer) {
      $campaignModel = new CampaignModel();

      // Zjištění nově přidaných kampaní.
      $campaigns = $this->getNewAddedCampaigns();

      // Získání seznamu příjemců (administrátoři).
      $recipients = UsersModel::getUsersByPermission(PERMISSION_ADMIN);

      foreach ($campaigns as $campaign) {
        foreach ($recipients as $recipient) {
          // Ověření, zdali už nedošlo k odeslání notifikace o vytvoření kampaně stejnému uživateli někdy dříve.
          if ($this->isEmailSent($campaign['id_campaign'], $recipient['id_user'], 1)) {
            continue;
          }

          // Získání detailních informací o právě přidané kampani.
          $campaignDetail = $campaignModel->getCampaignDetail($campaign['id_campaign']);

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

          // Poslání e-mailu.
          $mailResult = $this->sendEmail($mailer, $recipient['email'], $notificationSubject, $notificationBody);

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat.
          $mailResult = ($mailResult) ? 1 : 0;
          $this->logSentEmail($campaign['id_campaign'], $recipient['id_user'], 1, $mailResult);

          // Vyčištění pro další iteraci.
          $mailer->clearAddresses();
        }
      }
    }


    /**
     * Rozešle tvůrci kampaně (administrátorovi nebo správci testů) notifikaci s informací o tom,
     * že jeho phishingová kampaň již skončila (stejná notifikace se pošle i všem administrátorům).
     *
     * @param PHPMailer $mailer        Instance třídy PHPMailer
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendEndCampaignsNotifications($mailer) {
      $campaignModel = new CampaignModel();
      $statsModel = new StatsModel();

      // Počet odeslaných e-mailů.
      $countSentMails = 0;

      // Zjištění kampaní, které ke včerejšímu dni vypršely.
      $campaigns = $this->getEndCampaigns();

      // NOTIFIKACE TVŮRCŮM KAMPANĚ

      foreach ($campaigns as $campaign) {
        echo '<h1>KAMPAŇ ' . $campaign['id_campaign'] . '</h1>' . "\n";

        // Získání detailních informací o končící kampani.
        $campaignDetail = $campaignModel->getCampaignDetail($campaign['id_campaign']);

        // Získání výsledků kampaně.
        $campaignStats = $statsModel->getStatsForAllEndActions($campaign['id_campaign'], null, true);

        // Organizace, ve které k vytvoření phishingové kampaně došlo.
        $campaignOrg = getenv('ORG') . ' (' . getenv('ORG_DOMAIN') . ')';

        // Předmět notifikace.
        $notificationSubject = 'Phishingová kampaň ukončena';

        // Obsah notifikace.
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

        // Notifikaci odešleme jen tehdy, pokud je tvůrce kampaně správce testů (pokud je administátor, tak to řeší další část kódu).
        if (UsersModel::getUserRole($campaign['id_by_user']) == PERMISSION_TEST_MANAGER) {
          // Ověření, zdali už nedošlo k odeslání notifikace o ukončení kampaně někdy dříve.
          if (!$this->isEmailSent($campaign['id_campaign'], $campaign['id_by_user'], 2)) {
            // Poslání e-mailu tvůrci kampaně.
            $mailResult = $this->sendEmail($mailer, $campaign['email'], $notificationSubject, $notificationBody);

            // Uložení záznamu o tom, zda se e-mail podařilo odeslat.
            $mailResult = ($mailResult) ? 1 : 0;
            $this->logSentEmail($campaign['id_campaign'], $campaign['id_by_user'], 2, $mailResult);

            // Vyčištění pro další iteraci.
            $mailer->clearAddresses();

            // Uspání skriptu po odeslání určitého množství e-mailů.
            $countSentMails = $this->sleepSender($countSentMails);
          }

          // Zjištění jména a příjmení správce testů, které bude doplněno do notifikace pro uživatele, aby bylo jasné, kdo kampaň připravil.
          $ldapModel = new LdapModel();
          $testManager = $ldapModel->getUserCNByUsername(get_email_part($campaign['email'], 'username'));
          $ldapModel->close();
        }


        // KOPIE O UKONČENÍ KAMPANĚ ADMINISTRÁTORŮM

        // Získání seznamu příjemců (administrátoři).
        $adminRecipients = UsersModel::getUsersByPermission(PERMISSION_ADMIN);

        // Zaslání kopie u ukončení kampaně administrátorům.
        foreach ($adminRecipients as $recipient) {
          // Ověření, zdali už nedošlo k odeslání notifikace o ukončení kampaně někdy dříve.
          if ($this->isEmailSent($campaign['id_campaign'], $recipient['id_user'], 2)) {
            continue;
          }

          // Předmět notifikace pro administrátory (přidáno ID kampaně).
          $notificationSubject = 'Phishingová kampaň ukončena [' . $campaign['id_campaign'] . ']';

          // Poslání e-mailu.
          $mailResult = $this->sendEmail($mailer, $recipient['email'], $notificationSubject, $notificationBody);

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat.
          $mailResult = ($mailResult) ? 1 : 0;
          $this->logSentEmail($campaign['id_campaign'], $recipient['id_user'], 2, $mailResult);

          // Vyčištění pro další iteraci.
          $mailer->clearAddresses();

          // Uspání skriptu po odeslání určitého množství e-mailů.
          $countSentMails = $this->sleepSender($countSentMails);
        }


        // ZPĚTNÁ VAZBA PRO PŘÍJEMCE KAMPANĚ

        // Získání příjemců kampaně.
        $recipients = CampaignModel::getCampaignRecipients($campaign['id_campaign']);

        foreach ($recipients as $recipient) {
          // Získání detailnějších informací o příjemci.
          $user = UsersModel::getUserByEmail($recipient);

          // Ověření, zdali už nedošlo k odeslání notifikace o účasti v kampani někdy dříve.
          if ($this->isEmailSent($campaign['id_campaign'], $user['id_user'], 3)) {
            continue;
          }

          // Získání reakce příjemce na phishingovou kampaň.
          $user['reaction'] = CampaignModel::getUserReaction($campaign['id_campaign'], $user['id_user']);

          // Úprava znění notifikace podle reakce příjemce - pokud uživatel (ne)vyplnil platné přihlašovací údaje.
          if ($user['reaction']['id_action'] != CAMPAIGN_VALID_CREDENTIALS_ID) {
            $notificationReaction = 'Gratulujeme, v testu jste obstáli :)' . "\n\n";
          }
          else {
            $notificationReaction = '';
          }

          // Zjištění uživatelského URL klíče pro vzdělávací stránku.
          $code = WebsitePrependerModel::makeWebsiteUrl($campaign['id_campaign'], $user['url']);

          // Zjištění času a data odeslání podvodného e-mailu konkrétnímu uživateli.
          $mailSent = Database::querySingle('
            SELECT `date_sent`,
            DATE_FORMAT(date_sent, "%e. %c. %Y") AS `date_sent_formatted`,
            DATE_FORMAT(date_sent, "%e. %c. %Y (%k:%i)") AS `date_sent_full_formatted`
            FROM `phg_sent_emails`
            WHERE `id_campaign` = ?
            AND `id_email` = ?
            AND `id_user` = ?
           ', [$campaign['id_campaign'], $campaignDetail['id_email'], $user['id_user']]);

          // Předmět a obsah notifikace.
          $notificationSubject = 'Cvičný phishing z ' . $mailSent['date_sent_formatted'];
          $notificationBody =
            'Automatická notifikace systému ' . WEB_HTML_BASE_TITLE . "\n" .
            '-------------------------------------------' . "\n\n" .
            'Dne ' . $mailSent['date_sent_full_formatted'] . ' Vám byl odeslán e-mail "' . $campaignDetail['subject'] . '".' . "\n" .
            'Jednalo se o cvičný phishing (podvodnou zprávu) s typickými znaky, které útočníci' . "\n" .
            'používají při snaze získat Vaše heslo, osobní údaje nebo číslo platební karty.' . "\n\n" .
            $notificationReaction .
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

          if (isset($testManager) && $testManager != null) {
            $notificationBody .= "\n\n" .
            '-------------------------------------------' . "\n\n" .
            'Tento cvičný phishing pro Vás připravil ' . $testManager . ' (' . $campaign['email'] . ').' . "\n" .
            'Jeho cílem nebylo nachytat Vás, ale zvýšit povědomí o této bezpečnostní hrozbě.';
          }

          // Testovací HTML výpisy.
          echo '<pre>' . "\n";
          echo '<b>To:</b> ' . $recipient . "\n";
          echo '<b>Subject:</b> ' . $notificationSubject . "\n\n";
          echo $notificationBody . "\n\n";
          echo '</pre>' . "\n";

          // Poslání e-mailu.
          $mailResult = $this->sendEmail($mailer, $recipient, $notificationSubject, $notificationBody);

          // Uložení záznamu o tom, zda se e-mail podařilo odeslat.
          $mailResult = ($mailResult) ? 1 : 0;
          $this->logSentEmail($campaign['id_campaign'], $user['id_user'], 3, $mailResult);

          echo '<hr>' . "\n\n";

          // Vyčištění pro další iteraci.
          $mailer->clearAddresses();

          // Uspání skriptu po odeslání určitého množství e-mailů.
          $countSentMails = $this->sleepSender($countSentMails);
        }
      }
    }


    /**
     * Aktivuje odesílání notifikačních e-mailů.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function startSendingNotifications() {
      // Získání nové instance PHPMailer.
      $mailer = EmailSenderModel::getPHPMailerInstance();

      // Poslání notifikací o nových phishingových kampaních.
      $this->sendNewCampaignsNotifications($mailer);

      // Poslání notifikací o dokončených phishingových kampaních.
      $this->sendEndCampaignsNotifications($mailer);
    }
  }

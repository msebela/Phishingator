<?php
  /**
   * Třída zpracovává uživatelský vstup ve veřejné části aplikace, na základě kterého volá
   * odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class PublicHomepageController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     */
    public function process($arguments) {
      if (isset($_GET['section'])) {
        if ($_GET['section'] == ACT_PHISHING_TEST && isset($_GET['id'])) {
          $args = WebsitePrependerModel::parseWebsiteUrl($_GET['id']);

          if ($args == null) {
            Logger::warning('Snaha o nepovolený přístup na stránku o testovacím phishingovém e-mailu.', [$args, $_GET]);

            header('Location: ' . WEB_URL);
            exit();
          }

          // Pokud parametry URL adresy odpovídají předpokladu, dojde k připojení k databázi.
          Database::connect(DB_PDO_DSN, DB_USERNAME, DB_PASSWORD);

          $this->processEmailDetail($args['id_campaign'], $args['id_user']);

          $this->setViewData('phishingEmail', true);
          $this->setViewData('user_url', self::escapeOutput($_GET['id']));
        }
        else {
          $this->redirect(null, 1);
        }
      }
      else {
        header('Location: ' . WEB_URL . '/portal');
        exit();
      }
    }


    /**
     * Vypíše informace o absolvování cvičného phishingu včetně personalizovaného e-mailu a seznamu indicií.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idUser              ID uživatele (příjemce)
     */
    private function processEmailDetail($idCampaign, $idUser) {
      $this->setView('public/phishing-email');

      // Získání informací o kampani.
      $campaignModel = new CampaignModel();
      $campaign = $campaignModel->getCampaign($idCampaign);

      // Získání informaci o uživateli (příjemci).
      $user = UsersModel::getUserByURL($idUser);

      // Kontrola existence záznamu.
      if (empty($campaign) || empty($user) || $campaignModel->isUserRecipient($idCampaign, $user['id_user']) != 1) {
        Logger::warning(
          'Snaha o nepovolený přístup na stránku o testovacím phishingovém e-mailu (podstrčené parametry).',
          [$idCampaign, $idUser, $_GET]
        );

        header('Location: ' . WEB_URL);
        exit();
      }

      // Vyžádání screenshotu podvodné stránky.
      if (isset($_GET[ACT_PHISHING_IMG])) {
        $websiteModel = new PhishingWebsiteModel();
        $websiteModel->getPhishingWebsiteScreenshot($campaign['id_website']);
      }

      // Zjištění, zdali už byla kampaň ukončena (pro úpravu textů na stránce).
      $this->setViewData('campaign_ended', (date('Y-m-d') > $campaign['active_to']));

      // Zjištění, zdali je uživatel dobrovolník (pro úpravu textů na stránce).
      $volunteer = UsersModel::getUserEmailLimit($user['id_user']);
      $this->setViewData('volunteer', $volunteer['recieve_email']);

      // Pro získání informací o podvodném e-mailu, který byl v kampani použit.
      $model = new RecievedEmailModel();

      // Získání detailů o e-mailu a ošetření pro výpis.
      $phishingEmail = self::escapeOutput($model->getRecievedPhishingEmail($campaign['id_email'], $user['id_user']));

      // Ověření existence záznamu.
      $this->checkRecordExistence($phishingEmail);

      // Personalizace e-mailu podle uživatele.
      $phishingEmail = PhishingEmailModel::personalizePhishingEmail($phishingEmail, $user, true);

      // Nalezení indicie, která souvisí s podvodnou stránkou.
      if (isset($phishingEmail['indications'])) {
        foreach ($phishingEmail['indications'] as $indication) {
          if ($indication['expression'] == VAR_URL) {
            $website['indication'] = (!empty($indication['description'])) ? $indication['description'] : $indication['title'];
            break;
          }
        }
      }

      // Získání informací o podvodné stránce.
      $website['http'] = get_protocol_from_url($phishingEmail['url']) == 'http';
      $website['domain'] = get_domain_from_url($phishingEmail['url']);
      $website['url_without_domain'] = mb_substr($phishingEmail['url'], 0, mb_strlen($phishingEmail['url']) - mb_strlen($website['domain']));
      $website['image_src'] = '/' . ACT_PHISHING_TEST . '/' . self::escapeOutput($_GET['id']) . '?' . ACT_PHISHING_IMG;

      // Vložení získaných dat do View.
      $this->setViewData('website', $website);
      $this->setViewData('email', $phishingEmail);

      // Logování přístupu na stránku o absolvování cvičného phishingu.
      $record = [
        'id_campaign' => $idCampaign,
        'id_user' => $user['id_user'],
        'id_action' => 4,
        'used_email' => $user['email'],
        'visit_datetime' => date('Y-m-d H:i:s'),
        'ip' => WebsitePrependerModel::getClientIp(),
        'local_ip' => WebsitePrependerModel::getClientLocalIp(),
        'browser_fingerprint' => $_SERVER['HTTP_USER_AGENT']
      ];

      Logger::info('Přístup na stránku o testovacím phishingovém e-mailu.', $record);
      Database::insert('phg_captured_data_end', $record);
    }
  }

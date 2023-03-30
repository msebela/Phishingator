<?php
  /**
   * Třída zpracovává uživatelský vstup týkající se obdržených cvičných podvodných e-maiů, na základě
   * kterého volá odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class RecievedEmailsController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     */
    public function process($arguments) {
      $this->checkPermission(PERMISSION_USER);

      $this->setView('header-recieved-emails', true);
      $this->setUrlSection('recieved-phishing-emails');

      $model = new RecievedEmailModel();

      $this->processList($model);

      $this->setHelpLink('https://github.com/msebela/phishingator/blob/main/MANUAL.md#13-p%C5%99ijat%C3%A9-phishingov%C3%A9-e-maily');
    }


    /**
     * Vypíše seznam přijatých cvičných phishingových zpráv pro právě přihlášeného uživatele.
     *
     * @param RecievedEmailModel $model Instance třídy
     */
    private function processList($model) {
      $this->setTitle('Přijaté phishingové e-maily');
      $this->setView('list-recieved-emails');

      $modelUser = new UsersModel();
      $user = $modelUser->getUser(PermissionsModel::getUserId());

      // Získání všech e-mailů, které byly uživateli odeslány.
      $records = $model->getRecievedPhishingEmails($user['id_user']);

      // Personalizace a dodatečné úpravy každého z e-mailů.
      foreach ($records as $key => $email) {
        $records[$key]['sender_name'] = self::escapeOutput($email['sender_name']);
        $records[$key]['code'] = WebsitePrependerModel::makeUserWebsiteId($email['id_campaign'], $user['url']);

        $records[$key] = PhishingEmailModel::personalizePhishingEmail($records[$key], $user, false);
      }

      $this->setViewData('phishingEmails', $records);
      $this->setViewData('countRecordsText', self::getTableFooter(count($records)));
    }
  }

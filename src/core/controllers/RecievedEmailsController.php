<?php
  /**
   * Třída zpracovává uživatelský vstup týkající obdržených cvičných podvodných e-maiů, na základě
   * kterého volá odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class RecievedEmailsController extends Controller {
    /**
     * @var array       Pole s informacemi o právě přihlášeném uživateli
     */
    private $user;


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
      $modelUser = new UsersModel();

      $this->user = $modelUser->getUser(PermissionsModel::getUserId());

      if (isset($_GET['action'])) {
        if ($_GET['action'] == ACT_PREVIEW && !isset($_GET['page']) || (isset($_GET['page']) && is_numeric($_GET['page']))) {
          $this->processEmailDetail($model);
        }
        else {
          $this->addMessage(MSG_ERROR, 'Zvolená akce neexistuje.');
          $this->redirect($this->urlSection);
        }
      }
      else {
        $this->processList($model);
      }

      // Odkaz na nápovědu.
      $this->setHelpLink('');
    }


    /**
     * Vypíše seznam přijatých cvičných phishingových zpráv pro právě přihlášeného uživatele.
     *
     * @param RecievedEmailModel $model Instance třídy
     */
    private function processList($model) {
      $this->setTitle('Přijaté cvičné phishingové e-maily');
      $this->setView('list-recieved-emails');

      // Získání všech e-mailů, které byly uživateli odeslány.
      $recievedEmails = $model->getRecievedPhishingEmails($this->user['id_user']);

      // Personalizace a dodatečné úpravy každého z e-mailů.
      foreach ($recievedEmails as $key => $email) {
        $recievedEmails[$key]['sender_name'] = self::escapeOutput($email['sender_name']);
        $recievedEmails[$key] = PhishingEmailModel::personalizePhishingEmail($recievedEmails[$key], $this->user, false);
      }

      $this->setViewData('phishingEmails', $recievedEmails);
    }


    /**
     * Vypíše detailní informace o konkrétní přijaté cvičné phishingové zprávě s možností stránkování
     * mezi dalšími přijatými zprávami, které právě přihlášený uživatel obdržel.
     *
     * @param RecievedEmailModel $model Instance třídy
     */
    private function processEmailDetail($model) {
      $this->setTitle('Přijaté cvičné phishingové e-maily');
      $this->setView('recieved-email');

      // Zjištění počtu vypisovaných e-mailů.
      $recievedEmailsCount = EmailSenderModel::getCountOfRecievedEmails($this->user['id_user']);

      if ($recievedEmailsCount == 0) {
        $this->redirect($this->urlSection);
      }

      // Zpracování parametrů pro stránkování a určení prvního zobrazeného e-mailu.
      $startEmail = 0;

      if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        if ($_GET['page'] > 0 && $_GET['page'] < $recievedEmailsCount) {
          $startEmail = get_number_from_get_string($_GET['page']);
        }
        else {
          $this->redirect($this->urlSection);
        }
      }

      // Vybrání určitého počtu e-mailů, které se budou vypisovat na stránce.
      $recievedEmails = $model->getRecievedPhishingEmails($this->user['id_user'], $startEmail);

      // Získání detailů o e-mailu a ošetření pro výpis.
      $phishingEmail = self::escapeOutput(
        $model->getRecievedPhishingEmail($recievedEmails[0]['id_email'], $this->user['id_user'])
      );

      // Ověření existence záznamu.
      $this->checkRecordExistence($phishingEmail);

      // Personalizace e-mailu podle přihlášeného uživatele.
      $phishingEmail = PhishingEmailModel::personalizePhishingEmail($phishingEmail, $this->user);

      // Pořadí e-mailu tak, jak byl uživateli odeslán.
      $phishingEmail['id_record'] = ((isset($_GET['page'])) ? $recievedEmailsCount - $_GET['page'] : $recievedEmailsCount);

      $this->setViewData('email', $phishingEmail);

      // Zobrazení tlačítek pro stránkování.
      $this->setViewData('prevPageButton', (($recievedEmailsCount > 1 && isset($_GET['page']) && $_GET['page'] > 0) ? 1 : 0));
      $this->setViewData('prevPage', ((isset($_GET['page']) && $_GET['page'] - 1 > 0) ? '?page=' . ($_GET['page'] - 1) : ''));

      $this->setViewData('nextPageButton', ((
        ($recievedEmailsCount > 1 && !isset($_GET['page'])) || (isset($_GET['page']) && $recievedEmailsCount > ($_GET['page'] + 1))
      ) ? 1 : 0));
      $this->setViewData('nextPage', (isset($_GET['page']) ? ($_GET['page'] + 1) : 1));
    }
  }

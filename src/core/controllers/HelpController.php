<?php
  /**
   * Třída zpracovává uživatelský vstup stránky s nápovědou, na základě kterého volá
   * odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class HelpController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup.
     */
    public function process($arguments) {
      $invalidAction = false;

      if (isset($_GET['action'])) {
        if ($_GET['action'] == 'about-phishing') {
          $this->setUserHelp();
        }
        elseif ($_GET['action'] == 'principles-phishing') {
          $this->setAdminHelp();
        }
        else {
          $invalidAction = true;
        }
      }
      else {
        $invalidAction = true;
      }

      if ($invalidAction) {
        $this->addMessage(MSG_ERROR, 'Zvolená akce neexistuje.');
        $this->redirect($this->urlSection);
      }
    }


    /**
     * Vypíše obsah nápovědy určenou pro běžné uživatele.
     */
    private function setUserHelp() {
      $this->setTitle('Jak poznat phishing');
      $this->setView('help-about-phishing');

      // Personalizovaná data pro příklad phishingového e-mailu.
      $phishing = [
        'recipient' => PermissionsModel::getUserName(),
        'org' => strtoupper(getenv('ORG')),
        'orgDomain' => getenv('ORG_DOMAIN')
      ];

      $this->setViewData('phishing', $phishing);

      // Odkaz na nápovědu.
      $this->setHelpLink('https://gitlab.cesnet.cz/709/flab/phishingator/-/blob/main/MANUAL.md#1-pro-u%C5%BEivatele');
    }


    /**
     * Vypíše obsah nápovědy určenou pro administátory, kteří budou připravovat phishingové kampaně.
     */
    private function setAdminHelp() {
      $this->setTitle('Jak připravit phishing');
      $this->setView('help-principles-phishing');

      // Odkaz na nápovědu.
      $this->setHelpLink('https://gitlab.cesnet.cz/709/flab/phishingator/-/blob/main/MANUAL.md#2-pro-administr%C3%A1tory');
    }
  }

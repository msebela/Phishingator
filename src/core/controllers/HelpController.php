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

      // Seznam indicií pro příklad phishingového e-mailu.
      $phishingIndications = [
        [
          'E-mail odesílatele',
          'E-mail odesílatele nemá s&nbsp;organizací ' . $phishing['org'] . ' nic společného, byť se útočník snažil, aby byl v&nbsp;adrese odesílatele uveden její název.'
        ],
        [
          'Podezřelá URL',
          'Nejedná se o&nbsp;oficiální adresu organizace ' . $phishing['org'] . ', ale o&nbsp;snahu útočníka napodobit její název falešnou doménou <span class="text-monospace">web' . $phishing['orgDomain'] . '</span>.</p><p class="card-text">Celý odkaz navíc začíná zastaralým a&nbsp;nezabezpečeným protokolem HTTP místo bezpečného HTTPS.'
        ],
        [
          'Časový nátlak',
          'Útočník se snaží donutit uživatele k&nbsp;okamžité akci &ndash; kliknout na podvodný odkaz pod hrozbou nezískání odměn.'
        ],
        [
          'Hrozba ztrátou',
          'Útočník se snaží vyhrožovat, aby uživatele motivoval k&nbsp;okamžité akci bez přemýšlení.'
        ],
        [
          'Vydávání se za autoritou',
          'Útočník se vydává za autoritu, aby byl podvodný e-mail více důvěryhodný a&nbsp;pocitově důležitější a&nbsp;motivoval uživatele k&nbsp;akci.'
        ]
      ];

      $this->setViewData('phishing', $phishing);
      $this->setViewData('phishingIndications', $phishingIndications);

      $this->setHelpLink('https://gitlab.cesnet.cz/709/flab/phishingator/-/blob/main/MANUAL.md#1-pro-u%C5%BEivatele');
    }


    /**
     * Vypíše obsah nápovědy určenou pro administátory, kteří budou připravovat phishingové kampaně.
     */
    private function setAdminHelp() {
      $this->setTitle('Jak připravit phishing');
      $this->setView('help-principles-phishing');

      $this->setHelpLink('https://gitlab.cesnet.cz/709/flab/phishingator/-/blob/main/MANUAL.md#2-pro-administr%C3%A1tory');
    }
  }

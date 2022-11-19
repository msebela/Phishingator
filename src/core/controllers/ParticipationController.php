<?php
  /**
   * Třída zpracovává uživatelský vstup týkající úpravy nastavení dobrovolného přijímání cvičných
   * phishingových zpráv, na základě kterého volá odpovídající metody, přičemž svůj výstup
   * předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class ParticipationController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     */
    public function process($arguments) {
      $this->checkPermission(PERMISSION_USER);

      $this->setTitle('Moje účast v programu');
      $this->setView('header-participation', true);
      $this->setUrlSection('my-participation');

      $model = new ParticipationModel();
      $formData = [
        'inputsNames' => ['recieve-email', 'email-limit-checkbox', 'email-limit'],
        'formPrefix' => 'participation-',
        'dbTable' => 'phg_users'
      ];

      if (isset($_GET['action'])) {
        $this->addMessage(MSG_ERROR, 'Zvolená akce neexistuje.');
        $this->redirect($this->urlSection);
      }
      else {
        $this->processParticipation($model, $formData);
      }

      $this->setHelpLink('https://gitlab.cesnet.cz/709/flab/phishingator/-/blob/main/MANUAL.md#1-pro-u%C5%BEivatele');
    }


    /**
     * Vypíše formulář a obslouží s ním související prvky pro úpravu nastavení dobrovolného přijímání
     * cvičných phishnigových zpráv pro právě přihlášeného uživatele.
     *
     * @param ParticipationModel $model Instance třídy
     * @param array $formData           Nastavení a vlastnosti formuláře
     */
    private function processParticipation($model, $formData) {
      $model->initForm($formData['inputsNames'], $formData['formPrefix'], $formData['dbTable']);

      $this->setView('form-participation');

      // ID právě přihlášeného uživatele.
      $idUser = PermissionsModel::getUserId();

      $this->setViewData('formPrefix', $formData['formPrefix']);
      $this->setViewData('participation', $model->getParticipation($idUser));

      if (!empty($this->getData('participation'))) {
        $this->setViewData('inputsValues', $model->getInputsValues());

        if (isset($_POST[$model->formPrefix])) {
          try {
            $model->isValidCsrfToken($_POST);
            $model->load($_POST);

            $model->validateData();
            $model->updateParticipation($idUser);

            $this->addMessage(MSG_SUCCESS, 'Úprava proběhla úspěšně.');
            $this->redirect($this->urlSection);
          }
          catch (UserError $error) {
            $this->addMessage($error->getCode(), $error->getMessage());
          }
        }
      }
    }
  }

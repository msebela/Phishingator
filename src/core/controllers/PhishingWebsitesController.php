<?php
  /**
   * Třída zpracovává uživatelský vstup týkající správy podvodných webových stránek, na základě
   * kterého volá odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class PhishingWebsitesController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     */
    public function process($arguments) {
      $this->checkPermission(PERMISSION_TEST_MANAGER);

      $this->setView('header-phishing-websites', true);
      $this->setUrlSection('phishing-websites');

      $model = new PhishingWebsiteModel();
      $formData = [
        'inputsNames' => ['name', 'url', 'id-template', 'active'],
        'formPrefix' => 'phishing-website-',
        'dbTable' => 'phg_websites'
      ];

      if (isset($_GET['action'])) {
        $id = isset($_GET['id']) ? get_number_from_get_string($_GET['id']) : false;

        if ($_GET['action'] == ACT_NEW) {
          $this->processNew($model, $formData);
        }
        elseif ($_GET['action'] == ACT_EDIT && $id !== false) {
          $this->processEdit($model, $formData, $id);
        }
        elseif ($_GET['action'] == ACT_PREVIEW && $id !== false) {
          $this->processPreview($model, $id);
        }
        elseif ($_GET['action'] == ACT_DEL && $id !== false) {
          $this->processDelete($model, $id);
        }
        else {
          $this->addMessage(MSG_ERROR, 'Zvolená akce neexistuje.');
          $this->redirect($this->urlSection);
        }
      }
      else {
        $this->processList($model);
      }

      $this->setHelpLink('https://github.com/CESNET/Phishingator/blob/main/MANUAL.md#24-podvodn%C3%A9-str%C3%A1nky');
    }


    /**
     * Vypíše formulář a obslouží s ním související prvky pro přidání nové podvodné stránky.
     *
     * @param PhishingWebsiteModel $model   Instance třídy
     * @param array $formData               Nastavení a vlastnosti formuláře
     */
    private function processNew($model, $formData) {
      $this->checkPermission(PERMISSION_ADMIN);

      $this->setTitle('Nová podvodná stránka');
      $this->setView('form-phishing-website');

      $model->initForm($formData['inputsNames'], $formData['formPrefix'], $formData['dbTable']);
      $this->initViewData($model, ACT_NEW, $formData['formPrefix']);

      $this->setViewData('templates', $model->getPhishingWebsitesTemplates());
      $this->setViewData('domains', $model->getDomainsRegisteredInProxy());

      if (isset($_POST[$model->formPrefix . $this->getData('action')])) {
        try {
          $model->load($_POST);

          $model->validateData();
          $model->insertPhishingWebsite();

          $this->addMessage(MSG_SUCCESS, 'Přidání proběhlo úspěšně.');
          $this->redirect($this->urlSection);
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }
      }
    }


    /**
     * Vypíše formulář a obslouží s ním související prvky pro úpravu konkrétní podvodné stránky.
     *
     * @param PhishingWebsiteModel $model   Instance třídy
     * @param array $formData               Nastavení a vlastnosti formuláře
     * @param int $idWebsite                ID podvodné stránky
     */
    private function processEdit($model, $formData, $idWebsite) {
      $this->checkPermission(PERMISSION_ADMIN);

      $this->setTitle('Úprava podvodné stránky');
      $this->setView('form-phishing-website');

      $model->initForm($formData['inputsNames'], $formData['formPrefix'], $formData['dbTable']);
      $this->setViewData('phishingWebsite', $model->getPhishingWebsite($idWebsite));

      // Ověření existence záznamu.
      $this->checkRecordExistence($this->getData('phishingWebsite'));

      $this->initViewData($model, ACT_EDIT, $formData['formPrefix']);

      $this->setViewData('templates', $model->getPhishingWebsitesTemplates());
      $this->setViewData('domains', $model->getDomainsRegisteredInProxy());

      if (isset($_POST[$model->formPrefix . $this->getData('action')])) {
        try {
          $model->load($_POST);

          $model->validateData();
          $model->updatePhishingWebsite($idWebsite);

          $this->addMessage(MSG_SUCCESS, 'Úprava proběhla úspěšně.');
          $this->redirect($this->urlSection);
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }
      }
    }


    /**
     * Přesměruje uživatele na časově omezený odkaz s náhledem konkrétní podvodné stránky.
     *
     * @param PhishingWebsiteModel $model   Instance třídy
     * @param int $idWebsite                ID podvodné stránky
     */
    private function processPreview($model, $idWebsite) {
      try {
        $previewLink = $model->getPreviewLink($idWebsite, PermissionsModel::getUserId());

        if ($previewLink != null) {
          // Přesměrování přes meta tag v HTML kvůli tomu, že pravděpodobně HSTS u podvodných stránek
          // běžících na HTTP protokolu vynutilo HTTPS protokol a změnilo tak jejich URL adresu.
          echo '<meta http-equiv="refresh" content="0; url=' . $previewLink . '">';
          exit();
        }
      }
      catch (UserError $error) {
        $this->addMessage($error->getCode(), $error->getMessage());
      }

      $this->redirect($this->urlSection);
    }


    /**
     * Zavolá metodu pro odstranění konkrétní podvodné stránky.
     *
     * @param PhishingWebsiteModel $model   Instance třídy
     * @param int $idWebsite                ID podvodné stránky
     */
    private function processDelete($model, $idWebsite) {
      $this->checkPermission(PERMISSION_ADMIN);

      if (isset($_POST)) {
        try {
          $model->isValidCsrfToken($_POST);
          $model->deletePhishingWebsite($idWebsite);

          $this->addMessage(MSG_SUCCESS, 'Smazání proběhlo úspěšně.');
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }

        $this->redirect($this->urlSection);
      }
    }


    /**
     * Vypíše seznam podvodných stránek.
     *
     * @param PhishingWebsiteModel $model   Instance třídy
     */
    private function processList($model) {
      $this->checkPermission(PERMISSION_ADMIN);

      $this->setTitle('Podvodné stránky');
      $this->setView('list-phishing-websites');

      $records = $model->getPhishingWebsites();

      $this->setViewData('phishingWebsites', $records);
      $this->setViewData('countRecordsText', self::getTableFooter(count($records)));
    }
  }

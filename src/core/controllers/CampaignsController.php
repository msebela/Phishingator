<?php
  /**
   * Třída zpracovává uživatelský vstup týkající správy kampaní, na základě kterého volá
   * odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class CampaignsController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup.
     */
    public function process($arguments) {
      $this->checkPermission(PERMISSION_TEST_MANAGER);

      $this->setView('header-campaigns', true);
      $this->setUrlSection('campaigns');

      $model = new CampaignModel();
      $formData = [
        'inputsNames' => ['id-email', 'id-website', 'id-onsubmit', 'id-ticket', 'name', 'time-send-since', 'active-since', 'active-to'],
        'formPrefix' => 'campaign-',
        'dbTable' => 'phg_campaigns'
      ];

      if (isset($_GET['action'])) {
        $id = isset($_GET['id']) ? get_number_from_get_string($_GET['id']) : false;

        if ($_GET['action'] == ACT_NEW) {
          $this->processNew($model, $formData);
        }
        elseif ($_GET['action'] == ACT_EDIT && $id !== false) {
          $this->processEdit($model, $formData, $id);
        }
        elseif ($_GET['action'] == ACT_DEL && $id !== false) {
          $this->processDelete($model, $id);
        }
        elseif ($_GET['action'] == ACT_STATS && $id !== false) {
          $this->processStats($model, $id);
        }
        elseif ($_GET['action'] == ACT_EXPORT && $id !== false && isset($_GET['data'])) {
          $this->processExportStats($model, $id, $_GET['data']);
        }
        else {
          $this->addMessage(MSG_ERROR, 'Zvolená akce neexistuje.');
          $this->redirect($this->urlSection);
        }
      }
      else {
        $this->processList($model);
      }

      $this->setHelpLink('https://gitlab.cesnet.cz/709/flab/phishingator/-/blob/main/MANUAL.md#22-kampan%C4%9B');
    }


    /**
     * Vypíše formulář a obslouží s ním související prvky pro přidání nové kampaně.
     *
     * @param CampaignModel $model     Instance třídy
     * @param array $formData          Nastavení a vlastnosti formuláře.
     */
    private function processNew($model, $formData) {
      $this->setTitle('Nová kampaň');
      $this->setView('form-campaign');

      $model->initForm($formData['inputsNames'], $formData['formPrefix'], $formData['dbTable']);
      $this->initViewData($model, ACT_NEW, $formData['formPrefix']);

      $userEmailRestriction = PermissionsModel::getUserEmailRestrictions();

      // Data z databáze pro vstupní pole.
      $this->setViewData('emails', PhishingEmailModel::getPhishingEmails());
      $this->setViewData('websites', PhishingWebsiteModel::getActivePhishingWebsites());
      $this->setViewData('websiteActions', $model->getWebsiteActions());
      $this->setViewData('recipients', ($_POST[$model->formPrefix . 'recipients'] ?? ''));
      $this->setViewData('countRecipients', count(explode(EMAILS_SEPARATOR, $this->getData('recipients'))) - 1);

      if (PermissionsModel::getUserRole() == PERMISSION_ADMIN) {
        $this->setViewData('recipientsVolunteers', $model->getVolunteersRecipients($this->getData('recipients')));
      }
      else {
        $this->setViewData('recipientsVolunteers', array());
      }

      $userAllowedLdapGroups = (PermissionsModel::getUserAllowedLdapGroups() != null) ? PermissionsModel::getUserAllowedLdapGroups() : null;
      $this->setViewData('recipientsLdapGroups', $model->getLdapRecipients($this->getData('recipients'), $userEmailRestriction, $userAllowedLdapGroups));

      if (isset($_POST[$model->formPrefix . $this->getData('action')])) {
        try {
          $model->load($_POST);

          $model->validateData();
          $model->insertCampaign();

          $this->addMessage(MSG_SUCCESS, 'Přidání proběhlo úspěšně.');
          $this->redirect($this->urlSection);
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }
      }
    }


    /**
     * Vypíše formulář a obslouží s ním související prvky pro úpravu konkrétní kampaně.
     *
     * @param CampaignModel $model     Instance třídy
     * @param array $formData          Nastavení a vlastnosti formuláře.
     * @param int $idCampaign          ID kampaně
     */
    private function processEdit($model, $formData, $idCampaign) {
      $this->setTitle('Úprava kampaně');
      $this->setView('form-campaign');

      $model->initForm($formData['inputsNames'], $formData['formPrefix'], $formData['dbTable']);
      $this->setViewData('campaign', $model->getCampaign($idCampaign));

      // Ověření existence záznamu.
      $this->checkRecordExistence($this->getData('campaign'));

      $this->initViewData($model, ACT_EDIT, $formData['formPrefix']);

      $userEmailRestriction = PermissionsModel::getUserEmailRestrictions();

      // Data z databáze pro vstupní pole.
      $this->setViewData('emails', PhishingEmailModel::getPhishingEmails());
      $this->setViewData('websites', PhishingWebsiteModel::getActivePhishingWebsites());
      $this->setViewData('websiteActions', $model->getWebsiteActions());
      $this->setViewData('recipients', ($_POST[$model->formPrefix . 'recipients'] ?? $model->getCampaignRecipients($idCampaign, true)));
      $this->setViewData('countRecipients', count(explode(EMAILS_SEPARATOR, $this->getData('recipients'))) - 1);

      if (PermissionsModel::getUserRole() == PERMISSION_ADMIN) {
        $this->setViewData('recipientsVolunteers', $model->getVolunteersRecipients($this->getData('recipients')));
      }
      else {
        $this->setViewData('recipientsVolunteers', array());
      }

      $userAllowedLdapGroups = (PermissionsModel::getUserAllowedLdapGroups() != null) ? PermissionsModel::getUserAllowedLdapGroups() : null;
      $this->setViewData('recipientsLdapGroups', $model->getLdapRecipients($this->getData('recipients'), $userEmailRestriction, $userAllowedLdapGroups));

      if (isset($_POST[$model->formPrefix . $this->getData('action')])) {
        try {
          $model->load($_POST);

          $model->validateData();
          $model->updateCampaign($idCampaign);

          $this->addMessage(MSG_SUCCESS, 'Úprava proběhla úspěšně.');
          $this->redirect($this->urlSection);
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }
      }
    }


    /**
     * Vypíše statistiku ke konkrétní kampani.
     *
     * @param CampaignModel $model     Instance třídy
     * @param int $idCampaign          ID kampaně
     */
    private function processStats($model, $idCampaign) {
      $this->setTitle('Statistika kampaně');
      $this->setView('stats-campaign');

      $statsModel = new StatsModel();

      // Detailní informace o zobrazované kampani.
      $campaign = $model->getCampaignDetail($idCampaign);

      // Ověření existence záznamu.
      $this->checkRecordExistence($campaign);

      // Ověření, zdali se uživatel nepokouší zobrazit statistiku pro kampaň, která zatím nebyla spuštěna.
      if (strtotime($campaign['active_since'] . ' ' . $campaign['time_send_since']) >= strtotime('now')) {
        $this->addMessage(MSG_ERROR, 'Nelze zobrazit statistiku pro kampaň, u které zatím nedošlo k odeslání e-mailů.');
        $this->redirect($this->urlSection);
      }

      // Doplnění informací o roli uživatele, který kampaň vytvořil.
      $campaign['role_color'] = UserGroupsModel::getColorGroupRole(
        UsersModel::getUserRole($campaign['id_by_user'])
      );

      $this->setViewData('campaign', $campaign);

      // Rozmazání identit uživatelů dle konfigurace.
      $this->setViewData('blurIdentities', (((int) CAMPAIGN_STATS_BLUR_IDENTITIES) ? 'blur-text' : ''));

      // Získání nasbíraných dat.
      if (isset($_GET[ACT_STATS_ALL_ACTIONS])) {
        // Data uživatelů zaznamenané na podvodné stránce a akce "bez reakce".
        $capturedData = $model->getCapturedDataInCampaign($idCampaign);
        $this->setViewData('capturedData', $capturedData);
      }
      elseif (isset($_GET[ACT_STATS_END_ACTIONS])) {
        // Konečné akce uživatelů v kampani.
        $endActions = $model->getUsersEndActionInCampaign($idCampaign);
        $this->setViewData('endActions', $endActions);
        $this->setViewData('endActionsLegend', $statsModel->legend);
        $this->setViewData('endActionsLegendCssClasses', $statsModel->cssClasess);

        // Data o navštívení stránky o absolvování phishingu.
        $testPageData = $model->getCapturedDataTestPage($idCampaign);
        $this->setViewData('testPageData', $testPageData);
      }
      elseif (isset($_POST[ACT_STATS_REPORT_PHISH])) {
        // Úprava nastavení uživatelského hlášení o phishingu.
        $this->processReportPhish($model, $idCampaign);
      }

      // Data a legenda pro koláčový graf.
      $this->setViewData('chartLegend', $statsModel->getLegendAsString('"'));
      $this->setViewData('chartColors', $statsModel->getColorsAsString('"'));

      // Data pro koláčový graf (konečná akce uživatele).
      $this->setViewData('chartDataUserEndAction', $statsModel->getStatsForAllEndActions($idCampaign));

      // Data a legenda pro sloupcový graf.
      $barChart = $statsModel->getStatsForAllEndActionsByGroups($idCampaign);

      $this->setViewData('barChartLegend', $statsModel->legend);
      $this->setViewData('barChartLegendColors', $statsModel->colors);
      $this->setViewData('barChartLegendDesc', $barChart['legend']);
      $this->setViewData('barChartLegendData', $barChart['data']);

      // Data pro koláčový graf pro všechny akce uživatelů.
      $this->setViewData('chartData', $statsModel->getStatsForAllActions($idCampaign));
    }


    /**
     * Exportuje vybraná data z konkrétní kampaně.
     *
     * @param CampaignModel $model     Instance třídy
     * @param int $idCampaign          ID kampaně
     * @param string $exportData       Jaká data se mají exportovat ("count-users-actions",
     *                                 "all-users-actions" nebo "all").
     */
    private function processExportStats($model, $idCampaign, $exportData) {
      // Ověření existence záznamu.
      $this->checkRecordExistence($model->getCampaign($idCampaign));

      try {
        // Exportovat ta data, která si uživatel vyžádal a podle toho zavolat odpovídající metodu.
        switch ($exportData) {
          case 'users-end-actions':
            StatsExportModel::exportEndActions($idCampaign);
            break;

          case 'all-users-actions':
            StatsExportModel::exportAllCapturedData($idCampaign);
            break;

          case 'count-users-actions':
            StatsExportModel::exportCountUsersActions($idCampaign);
            break;

          case 'all':
            StatsExportModel::exportAllToZipArchive($idCampaign);
            break;

          default:
            throw new UserError('Zvolená možnost exportu neexistuje!', MSG_ERROR);
        }
      }
      catch (UserError $error) {
        $this->addMessage($error->getCode(), $error->getMessage());
        $this->redirect($this->urlSection . '/' . ACT_STATS . '/' . $idCampaign);
      }
    }


    /**
     * Nastaví u konkrétního záznamu uživatele v kampani příznak,
     * že došlo k uživatelskému nahlášení phishingu.
     *
     * @param CampaignModel $model     Instance třídy
     * @param int $idCampaign          ID kampaně
     */
    private function processReportPhish($model, $idCampaign) {
      if (isset($_POST)) {
        try {
          $model->isValidCsrfToken($_POST);
          $idRecord = $_POST[ACT_STATS_REPORT_PHISH];

          if (!is_numeric($idRecord)) {
            throw new UserError('Záznam o nahlášení phishingu je v nesprávném formátu.', MSG_ERROR);
          }

          $model->setUserPhishReport($idRecord);
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }

        $this->redirect($this->urlSection . '/' . ACT_STATS . '/' . $idCampaign . '?' . ACT_STATS_END_ACTIONS . '#list');
      }
    }


    /**
     * Zavolá metodu pro odstranění konkrétní kampaně.
     *
     * @param CampaignModel $model     Instance třídy
     * @param int $idCampaign          ID kampaně
     */
    private function processDelete($model, $idCampaign) {
      $this->checkPermission(PERMISSION_ADMIN);

      if (isset($_POST)) {
        try {
          $model->isValidCsrfToken($_POST);
          $model->deleteCampaign($idCampaign);

          $this->addMessage(MSG_SUCCESS, 'Smazání proběhlo úspěšně.');
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }

        $this->redirect($this->urlSection);
      }
    }


    /**
     * Vypíše seznam přidaných kampaní.
     *
     * @param CampaignModel $model     Instance třídy
     */
    private function processList($model) {
      $this->setTitle('Kampaně');
      $this->setView('list-campaigns');

      $records = $model->getCampaigns();

      $this->setViewData('campaigns', $records);
      $this->setViewData('countRecordsText', self::getTableFooter(count($records)));
    }
  }

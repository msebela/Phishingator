<?php
  /**
   * Třída zpracovává uživatelský vstup týkající správy uživatelů, na základě kterého volá
   * odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class UsersController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     */
    public function process($arguments) {
      $this->checkPermission(PERMISSION_ADMIN);

      $this->setView('header-users', true);
      $this->setUrlSection('users');

      $model = new UsersModel();
      $formData = [
        'inputsNames' => ['email', 'id-user-group'],
        'formPrefix' => 'user-',
        'dbTable' => 'phg_users'
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
        else {
          $this->addMessage(MSG_ERROR, 'Zvolená akce neexistuje.');
          $this->redirect($this->urlSection);
        }
      }
      else {
        $this->processList($model);
      }

      $this->setHelpLink('https://github.com/CESNET/Phishingator/phishingator/blob/main/MANUAL.md#25-u%C5%BEivatel%C3%A9');
    }


    /**
     * Vypíše formulář a obslouží s ním související prvky pro přidání nového uživatele.
     *
     * @param UsersModel $model        Instance třídy
     * @param array $formData          Nastavení a vlastnosti formuláře
     */
    private function processNew($model, $formData) {
      $this->setTitle('Nový uživatel');
      $this->setView('form-user');

      $model->initForm($formData['inputsNames'], $formData['formPrefix'], $formData['dbTable']);
      $this->initViewData($model, ACT_NEW, $formData['formPrefix']);

      // Data z databáze (seznam uživatelských skupin) pro vstupní pole.
      $this->setViewData('groups', UserGroupsModel::getUserGroups());

      if (isset($_POST[$model->formPrefix . $this->getData('action')])) {
        try {
          $model->load($_POST);

          $model->validateData();
          $model->insertUser();

          $this->addMessage(MSG_SUCCESS, 'Přidání proběhlo úspěšně.');
          $this->redirect($this->urlSection);
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }
      }
    }


    /**
     * Vypíše formulář a obslouží s ním související prvky pro úpravu konkrétního uživatele.
     *
     * @param UsersModel $model        Instance třídy
     * @param array $formData          Nastavení a vlastnosti formuláře
     * @param int $idUser              ID uživatele
     */
    private function processEdit($model, $formData, $idUser) {
      $this->setTitle('Úprava uživatele');
      $this->setView('form-user');

      $model->initForm($formData['inputsNames'], $formData['formPrefix'], $formData['dbTable']);

      $user = $model->getUser($idUser);

      // Ověření existence záznamu.
      $this->checkRecordExistence($user);

      $userDetails = UsersModel::getUserDetail($user['id_user']);

      // Přidání do pole s daty další detailní informace o uživateli.
      foreach ($userDetails as $key => $record) {
        $user[$key] = $record;
      }

      // Zjištění seznamu kampaní, do kterých byl uživatel zapojen.
      $campaigns = RecievedEmailModel::getUserCampaignsParticipation($user['id_user']);

      foreach ($campaigns as $key => $campaign) {
        $campaigns[$key]['user_reaction'] = CampaignModel::getUserReaction($campaign['id_campaign'], $user['id_user']);
      }

      // Získaná data z databáze předat do View.
      $this->setViewData('user', $user);
      $this->setViewData('campaigns', $campaigns);

      $this->initViewData($model, ACT_EDIT, $formData['formPrefix']);

      // Data z LDAP.
      $ldap = new LdapModel();
      $this->setViewData('name', $ldap->getFullnameByUsername($user['username']));
      $ldap->close();

      // Data z databáze (seznam uživatelských skupin) pro vstupní pole.
      $this->setViewData('groups', UserGroupsModel::getUserGroups());

      if (isset($_POST[$model->formPrefix . $this->getData('action')])) {
        try {
          $model->load($_POST);

          $model->validateData();
          $model->updateUser($idUser);

          $this->addMessage(MSG_SUCCESS, 'Úprava proběhla úspěšně.');
          $this->redirect($this->urlSection);
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }
      }
    }


    /**
     * Zavolá metodu pro odstranění konkrétního uživatele.
     *
     * @param UsersModel $model        Instance třídy
     * @param int $idUser              ID uživatele
     */
    private function processDelete($model, $idUser) {
      if (isset($_POST)) {
        try {
          $model->isValidCsrfToken($_POST);
          $model->deleteUser($idUser);

          $this->addMessage(MSG_SUCCESS, 'Smazání proběhlo úspěšně.');
        }
        catch (UserError $error) {
          $this->addMessage($error->getCode(), $error->getMessage());
        }

        $this->redirect($this->urlSection);
      }
    }


    /**
     * Vypíše seznam uživatelů s možností filtrování záznamů a stránkování.
     *
     * @param UsersModel $model        Instance třídy
     */
    private function processList($model) {
      $this->setTitle('Uživatelé');
      $this->setView('list-users');

      // Získání seznamu skupin pro HTML výpis (filtrování).
      $this->setViewData('groups', UserGroupsModel::getUserGroups());

      // Získání seznamu oprávnění pro HTML výpis (filtrování).
      $this->setViewData('permissions', UserGroupsModel::getRoles());

      // Požadovaný počet záznamů, které chce uživatel na stránce vypsat.
      $countRecordsOnPage = $this->getCountRecordsOnPage();

      // URL adresa, od které se odvíjí další odkazy pro stránkování (v závislosti na parametrech filtrování).
      $pageLink = $this->getPagingUrl($this->urlSection);

      // Vyhledávání podle e-mailu.
      if (isset($_GET['find'])) {
        $filterFind = $_GET['find'];
        $pageLink .= '&amp;find=' . $filterFind;
      }
      else {
        $filterFind = '';
      }

      // Vyhledávání podle uživatelské skupiny.
      $filterGroup = 0;

      if (isset($_GET['group']) && is_numeric($_GET['group']) && $_GET['group'] > 0) {
        // Ověření existence skupiny v databázi (na základě dat,
        // které už máme stejně tak připravené pro výpis do HTML).
        foreach ($this->getData('groups') as $group) {
          if ($group['id_user_group'] == $_GET['group']) {
            $filterGroup = $group['id_user_group'];
            $pageLink .= '&amp;group=' . $filterGroup;

            break;
          }
        }
      }

      // Vyhledávání podle oprávnění.
      $filterPermission = null;

      if (isset($_GET['permission']) && is_numeric($_GET['permission'])) {
        // Ověření existence oprávnění v databázi (na základě dat,
        // které už máme stejně tak připravené pro výpis do HTML).
        foreach ($this->getData('permissions') as $permission) {
          if ($permission['id_user_role'] == $_GET['permission']) {
            $filterPermission = $permission['id_user_role'];
            $pageLink .= '&amp;permission=' . $filterPermission;

            break;
          }
        }
      }

      // Zobrazovat pouze dobrovolníky.
      if (isset($_GET['only-volunteers']) && $_GET['only-volunteers'] == 1) {
        $filterOnlyVolunteers = true;
        $pageLink .= '&amp;only-volunteers=1';
      }
      else {
        $filterOnlyVolunteers = false;
      }

      // Zjištění počtu vypisovaných uživatelů.
      $usersCount = $model->getUsersCount($filterOnlyVolunteers, $filterGroup, $filterFind, $filterPermission);

      // Počet stránek nutných k zobrazení záznamů podle zvolených parametrů.
      $countPages = $this->getCountPages($usersCount, $countRecordsOnPage);

      // Zpracování parametrů pro stránkování a zjištění prvního záznamu.
      $startEmail = $this->getStartRecord($countPages, $countRecordsOnPage);

      // Získání záznamů pro výpis na základě uživatelských parametrů.
      $this->setViewData('users', $model->getUsers(
        $startEmail, $countRecordsOnPage, $filterOnlyVolunteers, $filterGroup, $filterFind, $filterPermission
      ));

      // Zobrazení tlačítek pro stránkování.
      $this->setViewData('prevPageButton', $this->displayPrevButton($usersCount));
      $this->setViewData('prevPage', $this->getPrevPage($pageLink, $countPages));

      $this->setViewData('page', $this->getCurrentPage($countPages));
      $this->setViewData('pageLink', $pageLink . '&amp;page=');

      $this->setViewData('nextPageButton', $this->displayNextButton($usersCount, $countRecordsOnPage));
      $this->setViewData('nextPage', $this->getNextPage($pageLink, $countPages));

      // Data vyplněná pro filtrování.
      $this->setViewData('filterFind', $filterFind);
      $this->setViewData('filterGroup', $filterGroup);
      $this->setViewData('filterPermission', $filterPermission);
      $this->setViewData('filterOnlyVolunteers', $filterOnlyVolunteers);

      // Data pro stránkování.
      $this->setViewData('countRecords', $usersCount);
      $this->setViewData('countRecordsOnPage', $countRecordsOnPage);
      $this->setViewData('countPages', $countPages);
    }


    /**
     * Vrátí počet záznamů, které se budou zobrazovat na jedné stránce.
     *
     * @return int                     Maximální počet záznamů na jedné stránce
     */
    private function getCountRecordsOnPage() {
      if (isset($_GET['records']) && is_numeric($_GET['records'])
        && $_GET['records'] >= PAGING_MIN_RECORDS_ON_PAGE
        && $_GET['records'] <= PAGING_MAX_RECORDS_ON_PAGE) {
        $countRecordsOnPage = $_GET['records'];
      }
      else {
        $countRecordsOnPage = PAGING_DEFAULT_COUNT_RECORDS_ON_PAGE;
      }

      return $countRecordsOnPage;
    }


    /**
     * Vrátí část relativní adresy obsahující základní parametry pro stránkování.
     *
     * @param string $section          URL sekce
     * @return string                  Relativní URL adresa
     */
    private function getPagingUrl($section) {
      return $section . '?records=' . $this->getCountRecordsOnPage();
    }


    /**
     * Vrátí pořadí záznamu, od kterého se budou vypisovat další záznamy.
     *
     * @param int $countPages          Maximální počet stránek pro zobrazení všech záznamů
     * @param int $countRecordsOnPage  Maximální počet záznamů na jednu stránku
     * @return int                     Pořadí záznamu
     */
    private function getStartRecord($countPages, $countRecordsOnPage) {
      $startRecord = 0;

      if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        if ($_GET['page'] > 1 && $_GET['page'] <= $countPages) {
          // Od jakého záznamu (pořadí) začít vypisovat záznamy.
          $startRecord = ($_GET['page'] - 1) * $countRecordsOnPage;
        }
        elseif ($_GET['page'] == 1) {
          // Pokud je dotazována 1. stránka, nemá smysl uvažovat tento parametr v URL.
          $this->redirect($this->urlSection . '&records=' . $countRecordsOnPage);
        }
        else {
          // Neexistující stránky přesměrovat na výpis (1. stránku).
          $this->redirect($this->urlSection);
        }
      }

      return $startRecord;
    }


    /**
     * Vrátí počet stránek nutných k zobrazení všech záznamů v závislosti na maximálním
     * počtu záznamů na jedné stránce.
     *
     * @param int $countRecords        Celkový počet záznamů
     * @param int $countRecordsOnPage  Maximální počet záznamů na jednu stránku
     * @return float                   Počet stránek
     */
    private function getCountPages($countRecords, $countRecordsOnPage) {
      return ceil($countRecords / $countRecordsOnPage);
    }


    /**
     * Vrátí, jestli se má zobrazovat (resp. být aktivní) tlačítko pro zobrazení předchozí stránky.
     *
     * @param int $countRecords        Celkový počet záznamů
     * @return bool                    TRUE pokud se tlačítko má zobrazovat, jinak FALSE.
     */
    private function displayPrevButton($countRecords) {
      return ($countRecords > 1 && isset($_GET['page']) && $_GET['page'] > 0);
    }


    /**
     * Vrátí, jestli se má zobrazovat (resp. být aktivní) tlačítko pro zobrazení následující stránky.
     *
     * @param int $countRecords        Celkový počet záznamů
     * @param int $countRecordsOnPage  Maximální počet záznamů na jednu stránku
     * @return bool                    TRUE pokud se tlačítko má zobrazovat, jinak FALSE.
     */
    private function displayNextButton($countRecords, $countRecordsOnPage) {
      return (
        (!isset($_GET['page']) && $countRecords > 1 && $countRecords > $countRecordsOnPage)
        ||
        (isset($_GET['page']) && $countRecords > $_GET['page'] * $countRecordsOnPage)
      );
    }


    /**
     * Vrátí relativní URL adresu předchozí stránky v pořadí (vůči aktuálně zobrazené stránce).
     *
     * @param string $pageLink         Část relativní URL adresy, ke které se přidají další parametry.
     * @param int $countPages          Maximální počet stránek pro zobrazení všech záznamů
     * @return string                  Relativní adresa obsahující navíc parametr pro přechod na předchozí stránku.
     */
    private function getPrevPage($pageLink, $countPages) {
      $currentPage = $this->getCurrentPage($countPages);
      $prevPage = $currentPage - 1;

      $page = (($currentPage != 0 && $prevPage > 0 && $prevPage != 1) ? '&amp;page=' . $prevPage : '');

      return $pageLink . $page;
    }


    /**
     * Vrátí relativní URL adresu následující stránky v pořadí (vůči aktuálně zobrazené stránce).
     *
     * @param string $pageLink         Část relativní URL adresy, ke které se přidají další parametry.
     * @param int $countPages          Maximální počet stránek pro zobrazení všech záznamů
     * @return string                  Relativní adresa obsahující navíc parametr pro přechod na následující stránku.
     */
    private function getNextPage($pageLink, $countPages) {
      $currentPage = $this->getCurrentPage($countPages);
      $nextPage = $currentPage + 1;

      $page = ($currentPage != 0 && $nextPage <= $countPages) ? $nextPage : 2;

      return $pageLink . '&amp;page=' . $page;
    }


    /**
     * Vrátí číslo aktuálně zvolené stránky (pokud stránka existuje).
     *
     * @param int $countPages          Maximální počet stránek pro zobrazení všech záznamů
     * @return int                     Číslo aktuální stránky nebo 0, pokud stránka neexistuje.
     */
    private function getCurrentPage($countPages) {
      $page = 0;

      if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $countPages) {
        $page = $_GET['page'];
      }

      return $page;
    }
  }

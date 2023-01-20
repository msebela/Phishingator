<?php
  /**
   * První třída, která zpracovává uživatelský vstup z URL adresy, na základě kterého zavolá odpovídající metodu
   * nebo vytvoří odpovídající Controller, kterému uživatelské argumenty dál předá.
   *
   * @author Martin Šebela
   */
  class RouterController extends Controller {
    /**
     * @var mixed       Vybraný Controller, který bude zpracovávat uživatelské požadavky.
     */
    protected $controller;

    /**
     * @var array       Seznam všech dostupných Controllerů.
     */
    private $existsControllers;


    /**
     * Konstruktor nastavující výchozí hodnoty instance třídy (resp. seznam všech dostupných Controllerů).
     */
    public function __construct() {
      $this->controller = null;

      $this->existsControllers = [
        'public-homepage'          => 'PublicHomepageController',
        'campaigns'                => 'CampaignsController',
        'phishing-emails'          => 'PhishingEmailsController',
        'phishing-websites'        => 'PhishingWebsitesController',
        'users'                    => 'UsersController',
        'user-groups'              => 'UserGroupsController',
        'stats'                    => 'StatsController',
        'my-participation'         => 'ParticipationController',
        'recieved-phishing-emails' => 'RecievedEmailsController',
        'help'                     => 'HelpController'
      ];
    }


    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param string $arguments        Uživatelský vstup
     */
    public function process($arguments) {
      $publicSite = true;

      // Uživatel je přihlášen a je v autorizované části aplikace.
      if (PermissionsModel::getUserId() != null) {
        $publicSite = false;

        if (mb_substr($arguments, 0, 8) == '/portal/') {
          if (isset($_GET['section']) && $_GET['section'] != ACT_PHISHING_TEST) {
            $this->controller = $this->getController($_GET['section']);
          }
          elseif (isset($_GET[ACT_SWITCH_ROLE])) {
            $this->changeUserRole($_GET[ACT_SWITCH_ROLE]);
          }
          elseif (isset($_GET['logout'])) {
            $this->logout();
          }
          else {
            $this->controller = new DashboardController();
          }
        }
      }

      // Ve všech ostatních případech se uživatel nalézá ve veřejné části aplikace (to se ovšem může dít
      // i za předpokladu, že je přihlášený, proto to není jako ELSE větev předchozí podmínky).
      if ($this->controller == null) {
        $this->controller = $this->getController('public-homepage', true);
        $publicSite = true;
      }

      // Aktivovat Controller určený pro danou stránku (resp. sekci).
      $this->setControllerData($arguments, $publicSite);
    }


    /**
     * Vrátí odpovídající Controller v závislosti na předaném argumentu.
     *
     * @param string $controllerName   Název Controlleru
     * @param bool $public             TRUE pokud se jedná o Controller přístupný na veřejné části aplikace, jinak FALSE
     * @return mixed|null              Instance Controlleru nebo NULL při nenalezení odpovídajícího Controlleru
     */
    private function getController($controllerName, $public = false) {
      if (empty($controllerName)) {
        return null;
      }

      if (array_key_exists($controllerName, $this->existsControllers)) {
        return new $this->existsControllers[$controllerName]();
      }

      if ($public == false) {
        // Chybová stránka o nenalezení sekce v neveřejné části aplikace.
        return new ErrorNotFoundController();
      }
      else {
        $this->redirect(null, 1);
      }

      return null;
    }


    /**
     * Předá uživatelský vstup a data instanci Controlleru.
     *
     * @param string $arguments        Uživatelský vstup
     */
    private function setControllerData($arguments, $public = false) {
      if ($this->controller != null) {
        $this->controller->process($arguments);

        $this->setViewData('html_title', $this->controller->getTitle());

        $currentSection = $_GET['section'] ?? '';

        if (!$public) {
          $this->setViewData('menu', $this->getMenu());
          $this->setViewData('rolesMenu', $this->getRolesMenu());

          $this->setViewData('currentSection', $currentSection);
          $this->setViewData('currentAction', $_GET['action'] ?? '');

          $this->setViewData('messages', $this->getMessages());

          $this->setViewData('userPermission', PermissionsModel::getUserPermission());
          $this->setViewData('userRole', PermissionsModel::getUserRole());
          $this->setViewData('userRoleText', PermissionsModel::getUserRoleText());
          $this->setViewData('userRoleColor', UserGroupsModel::getColorGroupRole(PermissionsModel::getUserRole()));

          $this->setView('wrapper');
        }
        else {
          $this->setViewData('phishingPage', $currentSection == ACT_PHISHING_TEST);

          $this->setView('public/wrapper');
        }
      }
    }


    /**
     * Změní roli právě přihlášeného uživatele a přesměruje ho na úvodní stránku Phishingatoru.
     *
     * @param string $role             Název požadované role
     */
    private function changeUserRole($role) {
      PermissionsModel::switchRole($role);

      $this->redirect();
    }


    /**
     * Odhlásí uživatele z Phishingatoru a přesměruje ho na úvodní stránku projektu.
     */
    private function logout() {
      PermissionsModel::logout();

      header('Location: ' . WEB_BASE_URL);
      exit();
    }
  }

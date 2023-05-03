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
     * @return void
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

      if ($this->controller == null && $_SERVER['REMOTE_ADDR'] == SCHEDULER_ALLOWED_IP) {
        $this->controller = new SchedulerController();
      }

      // Ve všech ostatních případech se uživatel nalézá ve veřejné části aplikace (to se ovšem může dít
      // i za předpokladu, že je přihlášený, proto to není jako ELSE větev první podmínky).
      if ($this->controller == null) {
        $this->controller = $this->getController('public-homepage', true);
        $publicSite = true;
      }

      // Aktivovat Controller určený pro danou stránku (sekci).
      $this->setControllerData($arguments, $publicSite);
    }


    /**
     * Vrátí odpovídající Controller v závislosti na předaném argumentu.
     *
     * @param string $controllerName   Název Controlleru
     * @param bool $public             TRUE pokud se jedná o Controller přístupný na veřejné části aplikace, jinak FALSE (výchozí)
     * @return mixed|null              Instance Controlleru nebo NULL při nenalezení odpovídajícího Controlleru
     */
    private function getController($controllerName, $public = false) {
      $controller = null;

      if (!empty($controllerName)) {
        if (array_key_exists($controllerName, $this->existsControllers)) {
          $controller = new $this->existsControllers[$controllerName]();
        }
        elseif (!$public) {
          // Chybová stránka o nenalezení sekce v neveřejné části aplikace.
          $controller = new ErrorNotFoundController();
        }
        else {
          $this->redirect(null, 1);
        }
      }

      return $controller;
    }


    /**
     * Předá uživatelský vstup a data instanci Controlleru.
     *
     * @param string $arguments        Uživatelský vstup
     * @param bool $public             TRUE pokud se jedná o Controller přístupný na veřejné části aplikace, jinak FALSE (výchozí)
     * @return void
     */
    private function setControllerData($arguments, $public = false) {
      if ($this->controller != null) {
        $this->controller->process($arguments);

        $this->setViewData('html_title', $this->controller->getTitle());

        if (!$public) {
          $currentSection = $_GET['section'] ?? '';
          
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
          $this->setView('public/wrapper');
        }
      }
    }


    /**
     * Změní roli právě přihlášeného uživatele a přesměruje ho na úvodní stránku Phishingatoru.
     *
     * @param string $role             Identifikátor požadované role
     * @return void
     */
    private function changeUserRole($role) {
      PermissionsModel::switchRole($role);

      $this->redirect();
    }


    /**
     * Odhlásí uživatele z Phishingatoru a přesměruje ho na úvodní stránku projektu.
     *
     * @return void
     */
    private function logout() {
      PermissionsModel::logout();

      header('Location: ' . WEB_BASE_URL);
      exit();
    }
  }

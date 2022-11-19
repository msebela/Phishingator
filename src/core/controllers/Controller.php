<?php
  /**
   * Třída shromažďující obecné promměné a metody nutné ke komunikaci s nižší vrstvou View (pohledem),
   * které každý Controller zdědí.
   *
   * @author Martin Šebela
   */
  abstract class Controller {
    /**
     * @var string      Název hlavičky View (horní části stránky)
     */
    protected $viewHeaderName;

    /**
     * @var string      Název View vkládaného do stránky (pod hlavičku)
     */
    protected $viewName;

    /**
     * @var array       Data dále používaná ve View
     */
    protected $viewData = [];

    /**
     * @var string      Nadpis stránky (HTML tag title)
     */
    protected $htmlTitle;

    /**
     * @var string      Název URL sekce
     */
    protected $urlSection;

    /**
     * @var string      CSRF token
     */
    protected $csrfToken;

    /**
     * @var string      Odkaz na nápovědu
     */
    protected $helpLink;


    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu (nutno implementovat).
     *
     * @param string $arguments        Uživatelský vstup
     */
    public abstract function process($arguments);


    /**
     * Nastaví data určená pro View týkající se formuláře.
     *
     * @param mixed $model             Instance třídy modelu podle formuláře
     * @param string $action           Název akce, ke které bude docházet ve formuláři
     * @param string $formPrefix       Prefix formuláře
     */
    public function initViewData($model, $action, $formPrefix) {
      $this->setViewData('action', $action);
      $this->setViewData('formPrefix', $formPrefix);
      $this->setViewData('inputsValues', $model->getInputsValues());
      $this->setViewData('inputsMaxLengths', $model->getInputsMaxLengths());
    }


    /**
     * Nastaví název View, který se bude vkládat do stránky.
     *
     * @param string $view             Název souboru View (bez přípony)
     * @param bool $headerView         TRUE pokud se jedná o hlavičku pohledu (horní část stránky)
     */
    public function setView($view, $headerView = false) {
      $viewFilepath = CORE_DOCUMENT_ROOT . '/' . CORE_DIR_VIEWS . '/' . $view . CORE_VIEWS_FILE_EXTENSION;

      if (!empty($view) && file_exists($viewFilepath)) {
        if ($headerView === false) {
          $this->viewName = $view;
        }
        else {
          $this->viewHeaderName = $view;
        }
      }
    }


    /**
     * Ověří existenci souboru hlavičky View a v případě jeho nalezení jej vloží do stránky společně s extrahovanými
     * proměnnými používanými ve View.
     */
    public function displayHeader() {
      $viewFilepath = CORE_DOCUMENT_ROOT . '/' . CORE_DIR_VIEWS . '/' . $this->viewHeaderName . CORE_VIEWS_FILE_EXTENSION;

      if (!empty($this->viewHeaderName) && file_exists($viewFilepath)) {
        $this->setViewData('helpLink', $this->helpLink);

        extract($this->viewData);

        require $viewFilepath;
      }
    }


    /**
     * Ověří existenci souboru View a v případě jeho nalezení jej vloží do stránky společně s extrahovanými
     * proměnnými používanými ve View.
     */
    public function displayView() {
      $viewFilepath = CORE_DOCUMENT_ROOT . '/' . CORE_DIR_VIEWS . '/' . $this->viewName . CORE_VIEWS_FILE_EXTENSION;

      if (!empty($this->viewName) && file_exists($viewFilepath)) {
        $this->setViewData('helpLink', $this->helpLink);
        $this->setViewData('csrfToken', PermissionsModel::getCsrfToken());

        extract(self::escapeOutput($this->viewData));
        extract($this->viewData, EXTR_PREFIX_ALL, '');

        require $viewFilepath;
      }
    }


    /**
     * Přidá data, která budou dále využívána pro Model.
     *
     * @param string $indexName        Index (resp. proměnná), přes kterou budou data dostupná
     * @param mixed $data              Data
     */
    public function setViewData($indexName, $data) {
      $this->viewData[$indexName] = $data;
    }


    /**
     * Vrátí konkrétní data, která jsou uložena mezi již přidanými daty.
     *
     * @param string $indexName        Index, pod kterým jsou data uložena
     * @return null|mixed              Požadovaná data nebo NULL, pokud index neexistuje
     */
    public function getData($indexName) {
      if (array_key_exists($indexName, $this->viewData)) {
        return $this->viewData[$indexName];
      }

      return null;
    }


    /**
     * Ošetří řetězec nebo všechny prvky pole pro výstup do stránky.
     *
     * @param string|array $pattern    Ošetřovaný řetězec/pole
     * @return array|string            Ošetřený řetězec pro výstup do stránky
     */
    public static function escapeOutput($pattern) {
      if (is_array($pattern)) {
        foreach ($pattern as $key => $value) {
          $pattern[$key] = self::escapeOutput($value);
        }

        return $pattern;
      }
      else {
        return htmlspecialchars(stripslashes(trim($pattern)), ENT_QUOTES);
      }
    }


    /**
     * Přidá do seznamu nepřečtených systémových zpráv další položku.
     *
     * @param string $type             Typ systémové zprávy
     * @param string $message          Text systémové zprávy
     */
    public function addMessage($type, $message) {
      $message = [
        'type' => $this->getMessageColorByType($type),
        'message' => $message
      ];

      if (isset($_SESSION['messages'])) {
        $_SESSION['messages'][] = $message;
      }
      else {
        $_SESSION['messages'] = [$message];
      }
    }


    /**
     * Vrátí CSS třídu (barvu) systémové hlášky na základě jejího typu.
     *
     * @param string $type             Typ systémové zprávy
     * @return string                  Název CSS třídy
     */
    private function getMessageColorByType($type) {
      switch ($type) {
        case MSG_ERROR:
          return MSG_CSS_ERROR;
        case MSG_SUCCESS:
          return MSG_CSS_SUCCESS;
        default:
          return MSG_CSS_DEFAULT;
      }
    }


    /**
     * Vrátí seznam systémových zpráv pro uživalele.
     *
     * @return array                   Pole systémových zpráv.
     */
    public static function getMessages() {
      if (isset($_SESSION['messages'])) {
        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);

        return $messages;
      }

      return [];
    }


    /**
     * Vrátí možnosti dostupné v menu v závislosti na právě zvolené roli uživatele.
     *
     * @return array|null              Položky menu nebo NULL při neexistující roli
     */
    public function getMenu() {
      $userRole = PermissionsModel::getUserRole();

      $menu = [
        'Úvodní stránka' => ['minRole' => PERMISSION_USER, 'url' => '', 'icon' => 'home'],
        'Kampaně' => ['minRole' => PERMISSION_TEST_MANAGER, 'url' => 'campaigns', 'icon' => 'layers'],
        'Podvodné e-maily' => ['minRole' => PERMISSION_TEST_MANAGER, 'url' => 'phishing-emails', 'icon' => 'mail'],
        'Podvodné stránky' => ['minRole' => PERMISSION_ADMIN, 'url' => 'phishing-websites', 'icon' => 'link'],
        'Uživatelé' => ['minRole' => PERMISSION_ADMIN, 'url' => 'users', 'icon' => 'user'],
        'Skupiny' => ['minRole' => PERMISSION_ADMIN, 'url' => 'user-groups', 'icon' => 'users'],
        'Roční statistiky' => ['minRole' => PERMISSION_ADMIN, 'url' => 'stats', 'icon' => 'bar-chart-2'],
        'Moje účast v&nbsp;programu' => ['minRole' => PERMISSION_USER, 'maxRole' => PERMISSION_USER, 'url' => 'my-participation', 'icon' => 'activity'],
        'Přijaté phishingové e-maily' => ['minRole' => PERMISSION_USER, 'maxRole' => PERMISSION_USER, 'url' => 'recieved-phishing-emails', 'icon' => 'mail']
      ];

      if ($userRole !== null) {
        foreach ($menu as $key => $item) {
          // Odstranění těch položek menu, ke kterým by uživatel podle své role neměl mít přístup.
          if ($item['minRole'] < $userRole || (isset($item['maxRole']) && $item['maxRole'] > $userRole)) {
            unset($menu[$key]);
          }
        }

        return $menu;
      }

      return null;
    }


    /**
     * Přesměruje uživatele na konkrétní adresu v rámci systému.
     *
     * @param null|string $target      Relativní URL v rámci systému (nepovinný parametr). Při nevyplnění dojde
     *                                  k přesměrování na úvodní stránku přihlášeného uživatele.
     * @param null|int $redirectToRoot TRUE pro přesměrování do kořene webu (nepovinný parametr).
     */
    public function redirect($target = null, $redirectToRoot = null) {
      $target = (($redirectToRoot == null) ? 'portal/' : '') . (($target != null) ? $target : '');

      header('Location: ' . WEB_URL . '/' . $target);
      exit();
    }


    /**
     * Nastaví URL název sekce, přes kterou je dostupná.
     *
     * @param string $section          URL sekce
     */
    public function setUrlSection($section) {
      if (!empty($section)) {
        $this->urlSection = $section;
        $this->setViewData('urlSection', $this->urlSection);
      }
    }


    /**
     * Nastaví nadpis stránky (HTML tag title).
     *
     * @param string $htmlTitle        Nadpis stránky
     */
    public function setTitle($htmlTitle) {
      if (!empty($htmlTitle)) {
        $this->htmlTitle = $htmlTitle . ' · ' . WEB_HTML_BASE_TITLE;
      }
    }


    /**
     * Vrátí nadpis stránky (HTML tag title).
     *
     * @return string                  Nadpis stránky
     */
    public function getTitle() {
      return $this->htmlTitle;
    }


    /**
     * Nastaví odkaz na nápovědu pro danou sekci.
     *
     * @param string $link             Odkaz na nápovědu
     */
    public function setHelpLink($link) {
      if (!empty($link)) {
        $this->helpLink = $link;
      }
    }


    /**
     * Vrátí popisek pro patičku tabulky se seznamem záznamů.
     *
     * @param int $countRecords        Počet záznamů
     * @return string                  Popisek pro patičku tabulky
     */
    public function getTableFooter($countRecords) {
      $text = 'Žádné záznamy';

      if ($countRecords > 0) {
        $text = 'Celkem ' . $countRecords . ' ';

        if ($countRecords == 1) {
          $text .= 'záznam';
        }
        elseif ($countRecords >= 2 && $countRecords <= 4) {
          $text .= 'záznamy';
        }
        else {
          $text .= 'záznamů';
        }
      }

      return $text . '.';
    }


    /**
     * Ověří, zdali má uživatel dostatečné oprávnění ke vstupu do dané sekce.
     *
     * @param int $minPermission       Hodnota uživatelova oprávnění
     */
    public function checkPermission($minPermission) {
      if (PermissionsModel::getUserRole() > $minPermission) {
        $this->addMessage(MSG_ERROR, 'Pro tuto akci nemáte dostatečná oprávnění.');
        $this->redirect();
      }
    }


    /**
     * Ověří, zdali je předaný záznam prázdný a pokud ano, vyhodí výjimku, aby kód dále nepokračoval.
     *
     * @param mixed $record            Kontrolovaný záznam
     */
    public function checkRecordExistence($record) {
      if (empty($record)) {
        $this->addMessage(MSG_ERROR, 'Zvolený záznam neexistuje.');
        $this->redirect($this->urlSection);
      }
    }
  }

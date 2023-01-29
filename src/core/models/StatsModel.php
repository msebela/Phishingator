<?php
  /**
   * Třída zajišťující statistiku a operace s ní související (zjištění legendy
   * a barev do grafů apod.)
   *
   * @author Martin Šebela
   */
  class StatsModel {
    /**
     * @var array       Pole obsahující legendu grafu.
     */
    public $legend;

    /**
     * @var array       Pole obsahující barvy legendy a grafu.
     */
    public $colors;

    /**
     * @var array       Pole obsahující názvy CSS tříd.
     */
    public $cssClasess;


    /**
     * Připraví atributy instance třídy pro získání dat ke grafům.
     */
    public function __construct() {
      $this->getChartLegend();
    }


    /**
     * Získá z databáze a nastaví atribut legendy, barvy grafu a CSS třídy legendy.
     */
    private function getChartLegend() {
      $data = Database::queryMulti('
        SELECT `id_action`, `name`, `hex_color`, `css_color_class`
        FROM `phg_captured_data_actions`
      ');

      foreach ($data as $legend) {
        $this->legend[$legend['id_action']] = $legend['name'];
        $this->colors[$legend['id_action']] = $legend['hex_color'];
        $this->cssClasess[$legend['id_action']] = $legend['css_color_class'];
      }
    }


    /**
     * Změní pole na řetězec tak, že jednotlivé hodnoty původního pole oddělí zvoleným oddělovačem
     * a případně ještě před danou hodnotu přidá zvolený prefix (to je nepovinný parametr).
     *
     * @param array $array             Pole, které se má transformovat na řetězec.
     * @param string $delimiter        Oddělovač hodnot pole.
     * @param string $valuePrefix      Prefix všech hodnot pole.
     * @return string                  Řetězec vzniklý z původního pole.
     */
    private function getArrayAsDelimiterString($array, $delimiter, $valuePrefix = '') {
      $string = '';

      if (!empty($array)) {
        foreach (array_reverse($array) as $value) {
          $string .= $delimiter . $valuePrefix . $value . $delimiter . ', ';
        }
      }

      // Odstranění oddělovače u poslední položky, která byla do řetězce přidána.
      return rtrim($string, ', ');
    }


    /**
     * Vrátí legendu grafu jako řetězec s tím, že jednotlivé hodnoty budou odděleny zvoleným oddělovačem.
     *
     * @param string $delimiter        Oddělovač jednotlivých hodnot legendy.
     * @return string                  Řetězec obsahující všechny hodnoty legendy.
     */
    public function getLegendAsString($delimiter) {
      return $this->getArrayAsDelimiterString($this->legend, $delimiter);
    }


    /**
     * Vrátí barvy grafu (a legendy) jako řetězec s tím, že jednotlivé hodnoty budou odděleny zvoleným oddělovačem.
     *
     * @param string $delimiter        Oddělovač jednotlivých barev.
     * @return string                  Řetězec obsahující všechny barvy.
     */
    public function getColorsAsString($delimiter) {
      return $this->getArrayAsDelimiterString($this->colors, $delimiter, '#');
    }


    /**
     * Vrátí vynulované pole akcí, které mohl uživatel v kampani udělat.
     *
     * @return array                   Vynulované pole akcí.
     */
    public function getEmptyArrayCountActions() {
      $actionsArray = [];

      if (!empty($this->legend)) {
        $actionsKeys = array_keys($this->legend);

        foreach ($actionsKeys as $key) {
          $actionsArray[$key] = 0;
        }
      }

      return $actionsArray;
    }


    /**
     * Zjistí počet každého typu akce provedené na podvodné stránce u konkrétní kampaně a výsledek vrátí formou pole.
     *
     * @param int $idCampaign          ID kampaně
     * @return array                   Pole s jednotlivými typy akcí a počtem každé z nich.
     */
    private function getCountOfEveryActionInCampaign($idCampaign) {
      $data = [];

      // Obecný SQL dotaz, který se bude měnit dle parametrů v jednotlivých iteracích.
      $query = 'SELECT COUNT(*) FROM `phg_captured_data` WHERE `id_campaign` = ? AND `id_action` = ?';

      // Spuštění dotazu pro každou z položek legendy (tedy pro každou možnou akci uživatele).
      foreach ($this->legend as $key => $legend) {
        if ($key == CAMPAIGN_NO_REACTION_ID) {
          $usersActions = Database::queryMulti('SELECT `id_user`, `id_action` FROM `phg_captured_data` WHERE `id_campaign` = ?', $idCampaign);
          $usersNoReaction = [];

          foreach ($usersActions as $action) {
            if ($action['id_action'] == CAMPAIGN_NO_REACTION_ID) {
              $usersNoReaction[] = $action['id_user'];
              continue;
            }

            if (($key = array_search($action['id_user'], $usersNoReaction)) !== false) {
              unset($usersNoReaction[$key]);
            }
          }

          $data[$key] = count($usersNoReaction);

          continue;
        }

        // Přidání ID kampaně a hodnoty legendy do pole argumentů pro prepared statements.
        $args = [$idCampaign, $key];

        // Zjištění počtu dané akce.
        $data[$key] = Database::queryCount($query, $args);
      }

      return $data;
    }


    /**
     * Vrátí data pro statistiku ukazující podíl všech typů provedených akcí v konkrétní kampani.
     *
     * @param int $idCampaign          ID kampaně
     * @return string|null             Data s počtem každého typu možných akcí nebo NULL.
     */
    public function getStatsForAllActions($idCampaign) {
      $data = null;

      if (!empty($this->legend) && !empty($idCampaign)) {
        // Zjištění počtu každého typu akce provedené v kampani.
        $data = $this->getCountOfEveryActionInCampaign($idCampaign);
        $data = implode(', ', array_reverse($data));
      }

      return $data;
    }


    /**
     * Vrátí data pro statistiku ukazující podíl konečných typů provedených akcí (těch nejvážnějších,
     * které mohli uživatelé na podvodné stránce udělat).
     *
     * @param int|array|null $idCampaign ID kampaně (nebo pole ID kampaní), pro které se statistika zjišťuje nebo NULL,
     *                                   pokud se zjišťuje pro všechny kampaně.
     * @param int|null $idUser           ID uživatele, pro kterého se statistika zjišťuje (v tom případě musí být
     *                                   předchozí parametr metody NULL), pokud pro všechny uživatele, tak NULL.
     * @param bool $returnArray          FALSE pokud data vrátit jako řetězec (výchozí) nebo TRUE pokud jako pole.
     * @return array|string              Data s počtem konečných akcí uživatelů/uživatele.
     */
    public function getStatsForAllEndActions($idCampaign = null, $idUser = null, $returnArray = false) {
      // Připravení prázdného pole, kde index v poli představuje danou akci.
      $dataCountActions = $this->getEmptyArrayCountActions();

      if (!is_array($idCampaign) && $idCampaign != null) {
        // Zjištění statistiky pro jednu konkrétní kampaň.
        $query = '
          SELECT `id_user`, MAX(`id_action`) AS `id_action`
          FROM `phg_captured_data`
          WHERE `id_campaign` = ?
          GROUP BY `id_user`';
        $args = $idCampaign;
      }
      elseif ($idCampaign == null && $idUser) {
        // Zjištění statistiky pro konkrétního uživatele (tedy bez návaznosti na kampaň, ale na ID uživatele).
        $query = '
          SELECT `id_user`, phg_campaigns.id_campaign, MAX(`id_action`) AS `id_action`
          FROM `phg_captured_data`
          JOIN `phg_campaigns`
          ON phg_campaigns.id_campaign = phg_captured_data.id_campaign
          WHERE `visible` = 1
          AND `id_user` = ?
          GROUP BY `id_campaign`';
        $args = $idUser;
      }
      else {
        // Zjištění celkové statistiky pro všechny kampaně (nebo pro určité množství kampaní).
        $allCampaigns = true;

        $query = '
          SELECT DISTINCT `id_user`, phg_campaigns.id_campaign, `id_action`
          FROM `phg_captured_data`
          JOIN `phg_campaigns`
          ON phg_campaigns.id_campaign = phg_captured_data.id_campaign
          WHERE `visible` = 1
          ORDER BY `id_action` DESC';
        $args = [];
      }

      // Seznam akcí, které uživatelé provedli.
      $dataUserActions = Database::queryMulti($query, $args);

      // Počet akcí, kdy uživatelé na podvodné stránce nějak reagovali (něco udělali, stačí přístup).
      $countActionsWithReaction = 0;

      if (isset($allCampaigns)) {
        // Pole kampaní (ID kampaně jako index pole) s tím, že v každé kampani jsou
        // uloženy jako indexy ID uživatelů a jako hodnoty těchto indexů ID jejich nejhorší akce,
        // kterou v kampani udělali.
        $theWorstUserActionInCampaigns = [];

        foreach ($dataUserActions as $action) {
          // Pokud se zjišťuje statistika pro množinu kampaní a daná kampaň není mezi požadovanými.
          if (is_array($idCampaign) && !in_array($action['id_campaign'], $idCampaign)) {
            continue;
          }

          if (!isset($theWorstUserActionInCampaigns[$action['id_campaign']][$action['id_user']])) {
            // Pokud zatím záznam o uživateli neexistuje, vložíme jeho první nalezený záznam o akci, kterou provedl.
            $theWorstUserActionInCampaigns[$action['id_campaign']][$action['id_user']] = $action['id_action'];
          }
          else {
            if ($action['id_action'] > $theWorstUserActionInCampaigns[$action['id_campaign']][$action['id_user']]) {
              // Pokud v poli existuje záznam o jiné akci, kterou již uživatel v kampani udělal, ale právě zkoumaná
              // akce je horší, než ta, která je tam dosud, tak ji změníme na aktuální.
              $theWorstUserActionInCampaigns[$action['id_campaign']][$action['id_user']] = $action['id_action'];
            }
          }
        }

        // Získání počtu jednotlivých akcí na podvodné stránce.
        foreach ($theWorstUserActionInCampaigns as $campaign) {
          foreach ($campaign as $action) {
            $dataCountActions[$action] += 1;
            $countActionsWithReaction++;
          }
        }
      }
      else {
        // Získání počtu jednotlivých akcí na podvodné stránce.
        foreach ($dataUserActions as $action) {
          $dataCountActions[$action['id_action']] += 1;
          $countActionsWithReaction++;
        }
      }

      return ($returnArray) ? $dataCountActions : implode(', ', array_reverse($dataCountActions));
    }


    /**
     * Vrátí pouze subdomény (domény nižších řádů) ze zvoleného e-mailu (pokud e-mail obsahuje pouze název domény,
     * pak se daná doména odstraňovat nebude).
     *
     * @param string $email            E-mail
     * @return string                  Subdomény (domény nižších řádů), které byly součástí původní e-mailu
     */
    public static function removeAllFromEmailExceptSubdomains($email) {
      $domain = get_email_part($email, 'domain');

      if (!empty($domain) && $domain != EMAILS_ALLOWED_DOMAIN) {
        $domain = str_replace('.' . EMAILS_ALLOWED_DOMAIN, '', $domain);
      }

      return strtolower($domain);
    }


    /**
     * Vrátí data a legendu pro sloupcový graf, který bude znázorňovat počet provedených akcí (každého typu)
     * po skupinách uživatelů (na základě jejich e-mailů). Data lze požadovat buď pro konkrétní kampaň
     * nebo pro všechny kampaně.
     *
     * @param int|null $idCampaign     ID kampaně
     * @return array                   Pole s daty a legendou určené pro sloupcový graf knihovny Chart.js
     */
    public function getStatsForAllEndActionsByGroups($idCampaign = null) {
      if ($idCampaign != null && !is_array($idCampaign)) {
        // Zjištění statistiky pro konkrétní kampaň.
        $query = '
          SELECT `id_user`, MAX(`id_action`) AS `id_action`, `used_email`, `used_group`
          FROM `phg_captured_data`
          WHERE `id_campaign` = ?
          GROUP BY `id_user`';
        $args = [$idCampaign];
      }
      elseif (is_array($idCampaign) && !empty($idCampaign)) {
        // Zjištění statistiky pru určité množství kampaní.
        $cols = str_repeat(' OR `id_campaign` = ?', count($idCampaign) - 1);
        $query = '
          SELECT DISTINCT `id_user`, `id_campaign`, `id_action`, `used_email`, `used_group`
          FROM `phg_captured_data`
          WHERE `id_campaign` = ?' . $cols . '
          ORDER BY `id_action` DESC';
        $args = $idCampaign;
      }
      else {
        // Zjištění celkové statistiky (pro všechny nesmazané kampaně).
        $query = '
          SELECT DISTINCT `id_user`, phg_campaigns.id_campaign, `id_action`, `used_email`, `used_group`
          FROM `phg_captured_data`
          JOIN `phg_campaigns`
          ON phg_campaigns.id_campaign = phg_captured_data.id_campaign
          WHERE `visible` = 1
          ORDER BY `id_action` DESC';
        $args = [];
      }

      $capturedData = Database::queryMulti($query, $args);

      // Zjištění fakult, kateder a oddělení z LDAP.
      $ldap = new LdapModel();
      $allDepartments = $ldap->getDepartments();
      $ldap->close();

      if ($idCampaign != null && !is_array($idCampaign)) {
        // Zjišťování statistiky pro konkrétní kampaň.
        $data = $this->processDataForAllEndActionsByGroupsInCampaign($capturedData, $allDepartments);
      }
      else {
        // Zjišťování statistiky pro všechny kampaně.
        $data = $this->processDataForAllEndActionsByGroupsInAllCampaigns($capturedData, $allDepartments, true);
      }

      // Transformace dat do požadované struktury pro sloupcový graf Chart.js.
      return $this->getFormattedDataForBarChart($data);
    }


    /**
     * Zpracuje zaznamenaná data konečných akcí uživatelů ve všech kampaních a zpracovaná data vrátí.
     *
     * @param array $capturedData      Pole obsahující zaznamenaná data z podvodné stránky
     * @param array $allDepartments    Seznam pracovišť
     * @param bool $percentage         Vrátit data v procentech (TRUE), jinak FALSE (výchozí)
     * @return array                   Zpracovaná data
     */
    private function processDataForAllEndActionsByGroupsInAllCampaigns($capturedData, $allDepartments, $percentage = false) {
      $data = [];
      $usersResponses = [];

      // Zjištění nejhorší akce, kterou mohl uživatel v každé z kampaní udělat a její uložení do pomocného pole.
      foreach ($capturedData as $record) {
        $campaign = $record['id_campaign'];
        $recipientEmail = $record['used_email'];
        $recipientDepartment = $record['used_group'];

        // Pokud záznam o reakci daného uživatele v poli ještě neexistuje, vytvořit jej.
        if (!isset($usersResponses[$campaign][$recipientEmail])) {
          $usersResponses[$campaign][$recipientEmail] = [
            'department' => $recipientDepartment,
            'response' => $record['id_action']
          ];
        }
        // Pokud je další reakce uživatele ve stejné kampani horší, než ta z předchozích iterací, použít tu horší.
        elseif ($record['id_action'] > $usersResponses[$campaign][$recipientEmail]) {
          $usersResponses[$campaign][$recipientEmail] = [
            'department' => $recipientDepartment,
            'response' => $record['id_action']
          ];
        }
      }

      // Ze všech zjištěných provedených akcí v každé kampani vytvořit souhrn
      // všech provedených akcí pro každou ze skupin.
      foreach ($usersResponses as $campaignResponses) {
        foreach ($campaignResponses as $recipientEmail => $record) {
          if (CAMPAIGN_STATS_AGGREGATION == 2) {
            // Zjištění (sub)domény e-mailu, která bude poté použita jako klíč v poli pro každou skupinu.
            $domain = $this->getUserDepartment($recipientEmail, $allDepartments);
          }
          else {
            $domain = $record['department'];
          }

          // Pro každou novou skupinu vynulování počtu akcí, které mohli uživatelé udělat.
          if (!isset($data[$domain])) {
            $data[$domain] = $this->getEmptyArrayCountActions();
          }

          // Inkrementace konkrétní akce v právě procházené skupině.
          $data[$domain][$record['response']] += 1;
        }
      }

      // Přepočítání všech hodnot, pokud se má jednat o sloupcový graf v procentech.
      if ($percentage) {
        foreach ($data as $groupKey => $recipientDepartment) {
          $suma = 0;

          foreach ($recipientDepartment as $action) {
            $suma += $action;
          }

          foreach ($recipientDepartment as $actionKey => $action) {
            $data[$groupKey][$actionKey] = round($action * 100 / $suma);
          }
        }
      }

      return $data;
    }


    /**
     * Zpracuje zaznamenaná data konečných akcí uživatelů zapojených do konkrétní kampaně a zpracovaná data vrátí.
     *
     * @param array $capturedData      Pole obsahující zaznamenaná data z podvodné stránky
     * @param array $allDepartments    Seznam pracovišť
     * @return array                   Zpracovaná data
     */
    private function processDataForAllEndActionsByGroupsInCampaign($capturedData, $allDepartments) {
      $data = [];

      // Vytvoření skupin na základě subdomén e-mailů (resp. domén nižších řádů)
      // a zjištění zaznamenaných akcí v rámci těchto skupin.
      foreach ($capturedData as $recipient) {
        if (CAMPAIGN_STATS_AGGREGATION == 2) {
          // Zjištění (sub)domény e-mailu, která bude poté použita jako klíč v poli pro každou skupinu.
          $department = $this->getUserDepartment($recipient['used_email'], $allDepartments);
        }
        else {
          $department = $recipient['used_group'];
        }

        // Pro každou novou skupinu vynulování počtu akcí, které uživatelé mohli udělat.
        if (!isset($data[$department])) {
          $data[$department] = $this->getEmptyArrayCountActions();
        }

        // Inkrementace konkrétní akce v právě procházené skupině (pokud je známa konkrétní kampaň).
        $data[$department][$recipient['id_action']] += 1;
      }

      return $data;
    }


    /**
     * Transformuje data a legendu pro sloupcový graf do struktury požadované knihovnou Chart.js.
     *
     * @param array $data              Pole s hodnotami pro sloupcový graf
     * @return array                   Pole obsahující na indexu "legend" legendu a na indexu "data"
     *                                 pole s hodnotami pro sloupcový graf.
     */
    private function getFormattedDataForBarChart($data) {
      $legend = '';
      $formattedData = [];

      ksort($data);

      foreach ($data as $groupKey => $groupActions) {
        $legend .= '"' . mb_strtoupper($groupKey) . '", ';

        // Procházení akcí u každé skupiny.
        foreach ($groupActions as $actionKey => $count) {
          if (!isset($formattedData[$actionKey])) {
            $formattedData[$actionKey] = [];
          }

          $formattedData[$actionKey][] = $count;
        }
      }

      $legend = rtrim($legend, ', ');

      // Pole s hodnotami v každém z indexů transformujeme na řetězec hodnot (jak je požadováno knihovnou Chart.js).
      foreach ($formattedData as $actionKey => $count) {
        $formattedData[$actionKey] = implode(', ', $count);
      }

      return ['legend' => $legend, 'data' => $formattedData];
    }


    /**
     * Vrátí zkratku nadřazeného pracoviště daného uživatele.
     *
     * @param string $recipient        E-mail uživatele
     * @param array $allDepartments    Asociativní pole obsahující názvy oddělení
     * @return string                  Zkratka/doména pracoviště, pod níž uživatel spadá
     */
    private function getUserDepartment($recipient, $allDepartments) {
      // Zjištění (sub)domény e-mailu, která bude poté použita jako klíč v poli pro každou skupinu.
      $domain = $this->removeAllFromEmailExceptSubdomains($recipient);

      // Zjištění rodičovského pracoviště.
      $parentDepartment = $this->getParentDepartment($domain, $allDepartments);

      // Pokud se nejedná o samostatnou fakultu, přidat k názvu pracoviště její zkratku.
      if ($parentDepartment != null && !in_array($domain, explode(',', INDEPENDENT_DEPARTMENTS))) {
        $domain = strtolower($parentDepartment);
      }

      return $domain;
    }


    /**
     * Vrátí zkratku rodičovského oddělení (resp. fakultu) na základě zkratky katedry, oddělení apod.
     *
     * @param string $findChild        Hledaná katedra, oddělení
     * @param array $parentDepartments Asociativní pole, kde jsou jako klíče názvy rodičovských oddělení a jako
     *                                 hodnoty pole oddělení spadajících pod daného rodiče.
     * @return string|null             Zkratka rodičovského oddělení (fakulty)
     */
    private function getParentDepartment($findChild, $parentDepartments) {
      $abbr = null;

      // Procházení fakult.
      foreach ($parentDepartments as $department => $childDepartments) {
        // Procházení kateder a oddělení na fakultě.
        foreach ($childDepartments as $childDepartment) {
          if (strtolower($childDepartment) == strtolower($findChild)) {
            $abbr = $department;
            break;
          }
        }
      }

      return $abbr;
    }


    /**
     * Zjistí počet dobrovolníků v každé ze skupin (podle subdomény v e-mailu dobrovolníka).
     * Vrácená data jsou ve formátu požadované knihovnou Chart.js u sloupcového grafu.
     *
     * @return array                   Pole obsahující na indexu "legend" legendu a na indexu "data"
     *                                 pole s hodnotami pro sloupcový graf.
     */
    public function getVolunteersStats() {
      $data = [];
      $legend = '';
      $formattedData = '';

      // Zjištění fakult, kateder a oddělení z LDAP.
      if (CAMPAIGN_STATS_AGGREGATION == 2) {
        $ldap = new LdapModel();
        $allDepartments = $ldap->getDepartments();
        $ldap->close();
      }

      // Zjištění dobrovolníků.
      $volunteers = Database::queryMulti('SELECT `email`, `primary_group` FROM `phg_users` WHERE `recieve_email` = 1 AND `visible` = 1');

      // Zjištění skupin a počet dobrovolníků v každé z nich.
      foreach ($volunteers as $recipient) {
        if (CAMPAIGN_STATS_AGGREGATION == 2) {
          // Zjištění (sub)domény e-mailu, která bude poté použita jako klíč v poli pro každou skupinu.
          $domain = $this->getUserDepartment($recipient['email'], $allDepartments);
        }
        else {
          $domain = $recipient['primary_group'];
        }

        if (!isset($data[$domain])) {
          $data[$domain] = 1;
        }
        else {
          $data[$domain]++;
        }
      }

      // Seřazení pole sestupně podle počtu dobrovolníků.
      arsort($data);

      // Úprava formátu dat pro potřeby knihovny Chart.js.
      foreach ($data as $domain => $record) {
        $legend .= '"' . mb_strtoupper($domain) . '", ';
        $formattedData .= $record . ', ';
      }

      $legend = rtrim($legend, ', ');
      $formattedData = rtrim($formattedData, ', ');

      return ['legend' => $legend, 'data' => $formattedData];
    }


    /**
     * Spočítá a vrátí úspěšnost v odhalování phishingu pro konkrétního uživatele.
     *
     * @param int $idUser              ID uživatele
     * @return int                     Zaokrouhlená úspěšnost (na jednotky) v odhalování phishingu
     */
    public static function getUserSuccessRate($idUser) {
      // Reakce uživatelů, které jsou považovány za "úspěšné odhalení phishingu".
      $correctReactionsId = [CAMPAIGN_NO_REACTION_ID, CAMPAIGN_VISIT_FRAUDULENT_PAGE_ID, CAMPAIGN_INVALID_CREDENTIALS_ID];

      // Počet jednotlivých reakcí na zaslané e-maily.
      $allReactionsCount = 0;
      $correctReactionsCount = 0;

      $recievedEmails = RecievedEmailModel::getRecievedPhishingEmails($idUser);

      // Průchod všemi odeslanými e-maily a zjištění reakce uživatele.
      foreach ($recievedEmails as $email) {
        $reaction = CampaignModel::getUserReaction($email['id_campaign'], $idUser);

        // Zjištění počtu správných reakcí na phishing.
        if (in_array($reaction['id_action'], $correctReactionsId)) {
          $correctReactionsCount++;
        }

        $allReactionsCount++;
      }

      // Spočítání úspěšnosti v odhalování phishingu.
      return (($allReactionsCount != 0) ? round((100 * $correctReactionsCount) / $allReactionsCount) : 0);
    }


    /**
     * Vrátí CSS třídu na základě úspěšnosti.
     *
     * @param int $value               Úspěšnost
     * @return string                  Název CSS třídy
     */
    public static function getUserSuccessRateColor($value) {
      $color = MSG_CSS_DEFAULT;

      if ($value !== null) {
        if ($value >= 90) {
          $color = MSG_CSS_SUCCESS;
        }
        elseif ($value >= 50) {
          $color = MSG_CSS_WARNING;
        }
        else {
          $color = MSG_CSS_ERROR;
        }
      }

      return $color;
    }


    /**
     * Vrátí text ke statistice v odpovídajícím pádu českého jazyka.
     *
     * @param int $count               Počet záznamů, na základě kterého dojde ke skloňování.
     * @param string $type             Čeho se počet záznamů (a tedy i text) týká (viz zdrojový kód metody).
     * @return string|null             Text v odpovídajícím pádu nebo NULL.
     */
    public function getStatsText($count, $type) {
      $running = (PermissionsModel::getUserRole() == PERMISSION_ADMIN && !isset($_GET['section'])) ? 'běžící ' : '';

      if (PermissionsModel::getUserRole() == PERMISSION_ADMIN && isset($_GET['section']) && $_GET['section'] == 'stats') {
        $overall = ' celkem';

        if ($count == 1) $new = 'nová ';
        elseif ($count >= 2 && $count <= 4) $new = 'nové ';
        else $new = 'nových ';
      }
      else {
        $overall = '';
        $new = '';
      }

      if ($count == 1) {
        switch ($type) {
          case 'campaignsCount': return $new . $running . 'kampaň';
          case 'recipientsCount': return $overall . 'registrovaný příjemce';
          case 'volunteersCount': return 'dobrovolník' . $overall;
          case 'sentEmails': return 'odeslaný e-mail' . $overall;
          case 'websitesCount': return $new . 'podvodná stránka';
          case 'recievedEmails': return 'přijatý e-mail';
        }
      }
      elseif ($count >= 2 && $count <= 4) {
        switch ($type) {
          case 'campaignsCount': return $new . $running . 'kampaně';
          case 'recipientsCount': return 'registrovaní příjemci' . $overall;
          case 'volunteersCount': return 'dobrovolníci' . $overall;
          case 'sentEmails': return 'odeslané e-maily' . $overall;
          case 'websitesCount': return $new . 'podvodné stránky';
          case 'recievedEmails': return 'přijaté e-maily';
        }
      }
      else {
        if ($running) $running = 'běžících ';

        switch ($type) {
          case 'campaignsCount': return $new . $running . 'kampaní';
          case 'recipientsCount': return 'registrovaných příjemců' . $overall;
          case 'volunteersCount': return 'dobrovolníků' . $overall;
          case 'sentEmails': return 'odeslaných e-mailů' . $overall;
          case 'websitesCount': return $new . 'podvodných stránek';
          case 'recievedEmails': return 'přijatých e-mailů';
        }
      }

      return null;
    }
  }

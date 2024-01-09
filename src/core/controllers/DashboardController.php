<?php
  /**
   * Třída zpracovává uživatelský vstup úvodní stránky, na základě kterého volá
   * odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class DashboardController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     * @return void
     */
    public function process($arguments) {
      $this->setTitle('Úvodní stránka');
      $this->setView('homepage');

      $model = new StatsModel();

      // Získání legendy a barev pro grafy.
      $this->setViewData('chartLegend', $model->getLegendAsString('"'));
      $this->setViewData('chartColors', $model->getColorsAsString('"'));

      // Zjištění role uživatele a na základě zjištěné role zavolání metod pro vypsání konkrétních grafů.
      $userRole = PermissionsModel::getUserRole();

      if ($userRole == PERMISSION_ADMIN) {
        $this->setStatsAdminData($model);
      }
      elseif ($userRole == PERMISSION_TEST_MANAGER) {
        $this->setStatsTestManagerData($model);
      }
      elseif ($userRole == PERMISSION_USER) {
        $this->setStatsUserData($model);
      }
    }


    /**
     * Vypíše statistiku a grafy pro administrátorské (nejvyšší) oprávnění.
     *
     * @param StatsModel $model        Instance třídy
     * @return void
     */
    private function setStatsAdminData($model) {
      // Počet běžících kampaní.
      $countCampaigns = count(CampaignModel::getActiveCampaigns());

      $this->setViewData('countCampaigns', get_formatted_number($countCampaigns));
      $this->setViewData('countCampaignsText', $model->getStatsText($countCampaigns, 'campaignsCount'));

      // Počet zaregistrovaných příjemců.
      $countUsers = UsersModel::getCountOfActiveUsers();

      $this->setViewData('countUsers', get_formatted_number($countUsers));
      $this->setViewData('countUsersText', $model->getStatsText($countUsers, 'recipientsCount'));

      // Počet dobrovolníků.
      $countVolunteers = UsersModel::getCountOfVolunteers();

      $this->setViewData('countVolunteers', get_formatted_number($countVolunteers));
      $this->setViewData('countVolunteersText', $model->getStatsText($countVolunteers, 'volunteersCount'));

      // Počet odeslaných e-mailů.
      $countSentEmails = RecievedEmailModel::getCountOfSentEmails();

      $this->setViewData('countSentEmails', get_formatted_number($countSentEmails));
      $this->setViewData('countSentEmailsText', $model->getStatsText($countSentEmails, 'sentEmails'));

      // Počet běžících podvodných stránek.
      $countPhishingWebsites = count(PhishingWebsiteModel::getActivePhishingWebsites());

      $this->setViewData('countPhishingWebsites', get_formatted_number($countPhishingWebsites));
      $this->setViewData('countPhishingWebsitesText', $model->getStatsText($countPhishingWebsites, 'websitesCount'));

      // Data pro koláčový graf (reakce uživatelů).
      $this->setViewData('chartDataUsersResponses', $model->getUsersResponses());

      // Data a legenda pro sloupcový graf (reakce uživatelů dle oddělení).
      $barChart = $model->getUsersResponsesByGroups();

      $this->setViewData('barChartLegend', $model->legend);
      $this->setViewData('barChartLegendColors', $model->colors);
      $this->setViewData('barChartLegendDesc', $barChart['legend']);
      $this->setViewData('barChartLegendData', $barChart['data']);
      $this->setViewData('barChartSumGroups', count(explode('", "', $barChart['legend'])));

      // Data a legenda pro sloupcový graf obsahující informace o dobrovolnících dle skupin.
      $barChart = $model->getVolunteersStats();

      $this->setViewData('chartVolunteers', $barChart['legend']);
      $this->setViewData('chartVolunteersData', ($barChart['data']) ? $barChart['data'] : 0);

      $this->setHelpLink('https://github.com/CESNET/Phishingator/blob/main/MANUAL.md#2-p%C5%99%C3%ADru%C4%8Dka-pro-administr%C3%A1tory');
    }


    /**
     * Vypíše statistiku a grafy pro oprávnění správce testů.
     *
     * @param StatsModel $model        Instance třídy
     * @return void
     */
    private function setStatsTestManagerData($model) {
      // Zjištění ID přihlášeného uživatele pro konkretizování statistiky a grafů.
      $idUser = PermissionsModel::getUserId();

      // Relevantní kampaně, ke kterým se bude zjišťovat statistika.
      $campaigns = CampaignModel::getIdCampaignsInUserGroup($idUser);

      // Počet běžících kampaní.
      $countCampaigns = count($campaigns);

      $this->setViewData('countCampaigns', get_formatted_number($countCampaigns));
      $this->setViewData('countCampaignsText', $model->getStatsText($countCampaigns, 'campaignsCount'));

      // Počet odeslaných e-mailů.
      $countSentEmails = (count($campaigns) > 0) ? RecievedEmailModel::getCountOfSentEmailsInCampaign($campaigns) : 0;

      $this->setViewData('countSentEmails', get_formatted_number($countSentEmails));
      $this->setViewData('countSentEmailsText', $model->getStatsText($countSentEmails, 'sentEmails'));

      // Data pro koláčový graf (reakce uživatelů).
      $this->setViewData('chartDataUsersResponses', $model->getUsersResponses($campaigns));

      // Data a legenda pro sloupcový graf (reakce uživatelů dle oddělení).
      $barChart = $model->getUsersResponsesByGroups($campaigns);

      $this->setViewData('barChartLegend', $model->legend);
      $this->setViewData('barChartLegendColors', $model->colors);
      $this->setViewData('barChartLegendDesc', $barChart['legend']);
      $this->setViewData('barChartLegendData', $barChart['data']);
      $this->setViewData('barChartSumGroups', count(explode('", "', $barChart['legend'])));

      $this->setHelpLink('https://github.com/CESNET/Phishingator/blob/main/MANUAL.md#2-p%C5%99%C3%ADru%C4%8Dka-pro-administr%C3%A1tory');
    }


    /**
     * Vypíše statistiku a grafy pro nejnižší oprávnění, tzn. pro běžného uživatele.
     *
     * @param StatsModel $model        Instance třídy
     * @return void
     */
    private function setStatsUserData($model) {
      // Zjištění ID přihlášeného uživatele pro konkretizování statistiky a grafů.
      $idUser = PermissionsModel::getUserId();

      // Počet přijatých e-mailů.
      $countRecievedEmails = RecievedEmailModel::getCountOfRecievedPhishingEmails($idUser, true);

      $this->setViewData('countRecievedEmails', get_formatted_number($countRecievedEmails));
      $this->setViewData('countRecievedEmailsText', $model->getStatsText($countRecievedEmails, 'recievedEmails'));

      // Úspěšnost v odhalování phishingu.
      $this->setViewData('countSuccessRate', $model->getUserSuccessRate($idUser));

      // Data pro koláčový graf (reakce uživatele).
      $this->setViewData('chartDataUsersResponses', $model->getUsersResponses(null, $idUser));

      $this->setHelpLink('https://github.com/CESNET/Phishingator/blob/main/MANUAL.md#1-p%C5%99%C3%ADru%C4%8Dka-pro-u%C5%BEivatele');
    }
  }

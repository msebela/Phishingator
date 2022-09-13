<?php
  /**
   * Třída zpracovává uživatelský vstup na stránce ročních statistik, na základě kterého volá
   * odpovídající metody, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class StatsController extends Controller {
    /**
     * @var int         Rok, od kterého se budou zjišťovat statistiky.
     */
    private $startStatsYear;

    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup
     */
    public function process($arguments) {
      $this->setTitle('Roční statistiky');
      $this->setView('stats');

      $this->startStatsYear = CampaignModel::getYearFirstActiveCampaign();

      // Získání dat pro roční statistiky.
      $this->setStatsData();

      // Odkaz na nápovědu.
      $this->setHelpLink('');
    }


    /**
     * Získá data pro statistiku za každý rok.
     */
    private function setStatsData() {
      $this->setViewData('statsStartYear', $this->startStatsYear);

      $model = new StatsModel();

      // Data a legenda pro sloupcový graf o konečných akcích uživatelů dle skupin.
      $barChart = $model->getStatsForAllEndActionsByGroups();

      $this->setViewData('barChartLegend', $model->legend);
      $this->setViewData('barChartLegendColors', $model->colors);
      $this->setViewData('barChartLegendDesc', $barChart['legend']);
      $this->setViewData('barChartLegendData', $barChart['data']);

      for ($year = date('Y'); $year >= $this->startStatsYear; $year--) {
        $campaings = CampaignModel::getActiveCampaigns($year);
        $campaingsId = [];

        foreach ($campaings as $campaing) {
          $campaingsId[] = $campaing['id_campaign'];
        }

        $countCampaigns = count($campaings);

        // Počet aktivních kampaní v daném roce.
        $this->setViewData('countCampaigns' . $year, get_formatted_number($countCampaigns));
        $this->setViewData('countCampaignsText' . $year, $model->getStatsText($countCampaigns, 'campaignsCount'));

        // Počet zaregistrovaných příjemců.
        $countUsers = UsersModel::getCountOfActiveUsers($year);

        $this->setViewData('countUsers' . $year, get_formatted_number($countUsers));
        $this->setViewData('countUsersText' . $year, $model->getStatsText($countUsers, 'recipientsCount'));

        // Počet dobrovolníků.
        $countVolunteers = UsersModel::getCountOfVolunteers($year);

        $this->setViewData('countVolunteers' . $year, get_formatted_number($countVolunteers));
        $this->setViewData('countVolunteersText' . $year, $model->getStatsText($countVolunteers, 'volunteersCount'));

        // Počet odeslaných e-mailů.
        $countSentEmails = EmailSenderModel::getCountOfSentEmails($year);

        $this->setViewData('countSentEmails' . $year, get_formatted_number($countSentEmails));
        $this->setViewData('countSentEmailsText' . $year, $model->getStatsText($countSentEmails, 'sentEmails'));

        // Počet běžících podvodných stránek.
        $countPhishingWebsites = count(PhishingWebsiteModel::getActivePhishingWebsites($year));

        $this->setViewData('countPhishingWebsites' . $year, get_formatted_number($countPhishingWebsites));
        $this->setViewData('countPhishingWebsitesText' . $year, $model->getStatsText($countPhishingWebsites, 'websitesCount'));

        // Data a legenda pro koláčový graf.
        $this->setViewData('chartLegend', $model->getLegendAsString('"'));
        $this->setViewData('chartColors', $model->getColorsAsString('"'));

        // Data pro koláčový graf (konečná akce uživatele).
        $this->setViewData('chartDataUserEndAction' . $year, $model->getStatsForAllEndActions($campaingsId));

        // Data a legenda pro sloupcový graf o konečných akcích uživatelů dle skupin.
        $barChart = $model->getStatsForAllEndActionsByGroups($campaingsId);

        $this->setViewData('barChartLegend' . $year, $model->legend);
        $this->setViewData('barChartLegendColors' . $year, $model->colors);
        $this->setViewData('barChartLegendDesc' . $year, $barChart['legend']);
        $this->setViewData('barChartLegendData' . $year, $barChart['data']);

        // Data a legenda pro sloupcový graf obsahující informace o dobrovolnících dle skupin.
        $barChart = $model->getVolunteersStats($year);

        $this->setViewData('chartVolunteers' . $year, $barChart['legend']);
        $this->setViewData('chartVolunteersData' . $year, ($barChart['data']) ? $barChart['data'] : 0);
      }
    }
  }

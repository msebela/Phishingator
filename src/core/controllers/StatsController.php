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
      $this->checkPermission(PERMISSION_ADMIN);

      $this->setTitle('Roční statistiky');
      $this->setView('stats');

      $this->startStatsYear = CampaignModel::getYearFirstActiveCampaign();

      // Získání dat pro roční statistiky.
      $this->setStatsData();

      $this->setHelpLink('https://github.com/CESNET/Phishingator/blob/main/MANUAL.md#2-p%C5%99%C3%ADru%C4%8Dka-pro-administr%C3%A1tory');
    }


    /**
     * Získá data pro statistiku za každý rok.
     */
    private function setStatsData() {
      $this->setViewData('statsStartYear', $this->startStatsYear);

      $model = new StatsModel();
      $yearsStats = [];

      for ($year = $this->startStatsYear; $year <= date('Y'); $year++) {
        $campaings = CampaignModel::getActiveCampaigns($year);
        $campaingsId = [];

        foreach ($campaings as $campaing) {
          $campaingsId[] = $campaing['id_campaign'];
        }

        $countCampaigns = count($campaings);
        $yearsStats[$year]['countCampaigns'] = $countCampaigns;

        $diff = ($year != $this->startStatsYear) ? $countCampaigns - ($yearsStats[$year - 1]['countCampaigns'] ?? 0) : 0;

        // Počet aktivních kampaní v daném roce.
        $this->setViewData('countCampaigns' . $year, get_formatted_number($countCampaigns));
        $this->setViewData('countCampaignsText' . $year, $model->getStatsText($countCampaigns, 'campaignsCount'));

        $this->setViewData('countCampaignsDiff' . $year, get_formatted_number($diff, 0, true));
        $this->setViewData('countCampaignsDiffColor' . $year, $this->getDiffColor($diff));


        // Počet zaregistrovaných příjemců.
        $countUsers = UsersModel::getCountOfActiveUsers($year);
        $yearsStats[$year]['countUsers'] = $countUsers;

        $diff = ($year != $this->startStatsYear) ? $countUsers - ($yearsStats[$year - 1]['countUsers'] ?? 0) : 0;

        $this->setViewData('countUsers' . $year, get_formatted_number($countUsers));
        $this->setViewData('countUsersText' . $year, $model->getStatsText($countUsers, 'recipientsCount'));

        $this->setViewData('countUsersDiff' . $year, get_formatted_number($diff, 0, true));
        $this->setViewData('countUsersDiffColor' . $year, $this->getDiffColor($diff));


        // Počet dobrovolníků.
        $countVolunteers = UsersModel::getCountOfVolunteers($year);
        $yearsStats[$year]['countVolunteers'] = $countVolunteers;

        $diff = ($year != $this->startStatsYear) ? $countVolunteers - ($yearsStats[$year - 1]['countVolunteers'] ?? 0) : 0;

        $this->setViewData('countVolunteers' . $year, get_formatted_number($countVolunteers));
        $this->setViewData('countVolunteersText' . $year, $model->getStatsText($countVolunteers, 'volunteersCount'));

        $this->setViewData('countVolunteersDiff' . $year, get_formatted_number($diff, 0, true));
        $this->setViewData('countVolunteersDiffColor' . $year, $this->getDiffColor($diff));


        // Počet odeslaných e-mailů.
        $countSentEmails = RecievedEmailModel::getCountOfSentEmails($year);
        $yearsStats[$year]['countSentEmails'] = $countSentEmails;

        $diff = ($year != $this->startStatsYear) ? $countSentEmails - ($yearsStats[$year - 1]['countSentEmails'] ?? 0) : 0;

        $this->setViewData('countSentEmails' . $year, get_formatted_number($countSentEmails));
        $this->setViewData('countSentEmailsText' . $year, $model->getStatsText($countSentEmails, 'sentEmails'));

        $this->setViewData('countSentEmailsDiff' . $year, get_formatted_number($diff, 0, true));
        $this->setViewData('countSentEmailsDiffColor' . $year, $this->getDiffColor($diff));


        // Počet běžících podvodných stránek.
        $countPhishingWebsites = count(PhishingWebsiteModel::getActivePhishingWebsites($year));
        $yearsStats[$year]['countPhishingWebsites'] = $countPhishingWebsites;

        $diff = ($year != $this->startStatsYear) ? $countPhishingWebsites - ($yearsStats[$year - 1]['countPhishingWebsites'] ?? 0) : 0;

        $this->setViewData('countPhishingWebsites' . $year, get_formatted_number($countPhishingWebsites));
        $this->setViewData('countPhishingWebsitesText' . $year, $model->getStatsText($countPhishingWebsites, 'websitesCount'));

        $this->setViewData('countPhishingWebsitesDiff' . $year, get_formatted_number($diff, 0, true));
        $this->setViewData('countPhishingWebsitesDiffColor' . $year, $this->getDiffColor($diff));


        // Data a legenda pro koláčový graf.
        $this->setViewData('chartLegend', $model->getLegendAsString('"'));
        $this->setViewData('chartColors', $model->getColorsAsString('"'));

        // Data pro koláčový graf (reakce uživatelů).
        $this->setViewData('chartDataUsersResponses' . $year, $model->getUsersResponses($campaingsId));

        // Data a legenda pro sloupcový graf (reakce uživatelů dle oddělení).
        $barChart = $model->getUsersResponsesByGroups($campaingsId);

        $this->setViewData('barChartLegend', $model->legend);
        $this->setViewData('barChartLegendColors', $model->colors);
        $this->setViewData('barChartLegendDesc' . $year, $barChart['legend']);
        $this->setViewData('barChartLegendData' . $year, $barChart['data']);
        $this->setViewData('barChartLegendDisplay' . $year, $barChart['legendDisplay']);
      }
    }


    /**
     * Vrátí barvu na základě vypočítaného rozdílu dvou hodnot.
     *
     * @param int $diff                Vypočítaný rozdíl
     * @return string                  Barva
     */
    private function getDiffColor($diff) {
      $color = MSG_CSS_SUCCESS;
      $threshold = -5;

      if ($diff >= $threshold && $diff < 0) {
        $color = MSG_CSS_WARNING;
      }
      elseif ($diff < $threshold) {
        $color = MSG_CSS_ERROR;
      }

      return $color;
    }
  }

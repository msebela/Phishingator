<?php
  /**
   * Třída sloužící k získávání informací o již zaslaných cvičných podvodných e-mailech,
   * a to především s vazbou na konkrétního uživatele.
   *
   * @author Martin Šebela
   */
  class RecievedEmailModel {
    /**
     * Vrátí celkový počet odeslaných cvičných podvodných e-mailů (případně do konkrétního roku).
     *
     * @param int $maxYear             Maximální rok, do kterého se bude počet odeslaných e-mailů zjišťovat (nepovinné)
     * @return mixed                   Počet odeslaných e-mailů
     */
    public static function getCountOfSentEmails($maxYear = []) {
      $queryFilter = (is_numeric($maxYear)) ? 'YEAR(`date_sent`) <= ?' : '`date_sent` IS NOT NULL';

      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              WHERE ' . $queryFilter . '
              AND phg_campaigns.visible = 1
      ', $maxYear);
    }


    /**
     * Vrátí celkový počet všech cvičných podvodných e-mailů, které obdržel konkrétní uživatel.
     *
     * @param int $idUser                     ID uživatele
     * @param bool $hideEmailsActiveCampaigns TRUE, pokud mají být skryty e-maily, které byly rozeslány v rámci
   * *                                        kampaní, které zatím ještě běží, jinak FALSE (výchozí)
     * @return int                            Celkový počet přijatých cvičných podvodných e-mailů
     */
    public static function getCountOfRecievedPhishingEmails($idUser, $hideEmailsActiveCampaigns = false) {
      $queryFilter = ($hideEmailsActiveCampaigns) ? 'AND TIMESTAMP(phg_campaigns.date_active_to, phg_campaigns.time_active_to) < NOW()' : '';

      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              WHERE `id_user` = ?
              AND phg_sent_emails.date_sent IS NOT NULL
              ' . $queryFilter . '
              AND phg_campaigns.visible = 1
      ', $idUser);
    }


    /**
     * Vrátí detailní informace o konkrétním cvičném podvodném e-mailu,
     * který obdržel daný uživatel ve zvolené kampani.
     *
     * @param int $idCampaign          ID kampaně
     * @param int $idEmail             ID e-mailu
     * @param int $idUser              ID uživatele
     * @return mixed                   Pole s informacemi o cvičném podvodném e-mailu
     */
    public static function getRecievedPhishingEmail($idCampaign, $idEmail, $idUser) {
      return Database::querySingle('
              SELECT phg_sent_emails.id_campaign, `date_sent`,
              phg_emails.id_email, `sender_name`, `sender_email`, `subject`, `body`,
              `id_template`,`url`,
              DATE_FORMAT(date_sent, "%e. %c. %Y") AS `date_sent_formatted`,
              DATE_FORMAT(date_sent, "%e. %c. %Y (%k:%i)") AS `datetime_sent_formatted`,
              phg_campaigns.id_website
              FROM `phg_sent_emails`
              JOIN `phg_emails`
              ON phg_sent_emails.id_email = phg_emails.id_email
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              JOIN `phg_websites`
              ON phg_campaigns.id_website = phg_websites.id_website
              WHERE phg_sent_emails.id_campaign = ?
              AND phg_sent_emails.id_email = ?
              AND phg_sent_emails.id_user = ?
              AND phg_sent_emails.date_sent IS NOT NULL
              AND phg_emails.visible = 1
              AND phg_campaigns.visible = 1
      ', [$idCampaign, $idEmail, $idUser]);
    }


    /**
     * Vrátí seznam všech cvičných podvodných e-mailů, které obdržel konkrétní uživatel.
     *
     * @param int $idUser                     ID uživatele
     * @param bool $hideEmailsActiveCampaigns TRUE, pokud mají být skryty e-maily, které byly rozeslány v rámci
     *                                        kampaní, které zatím ještě běží, jinak FALSE (výchozí)
     * @return mixed                          Pole e-mailů s informacemi o každém z nich
     */
    public static function getRecievedPhishingEmails($idUser, $hideEmailsActiveCampaigns = false) {
      $queryFilter = ($hideEmailsActiveCampaigns) ? 'AND TIMESTAMP(phg_campaigns.date_active_to, phg_campaigns.time_active_to) < NOW()' : '';

      return Database::queryMulti('
              SELECT phg_sent_emails.id_campaign, `date_sent`,
              phg_emails.id_email, `sender_name`, `sender_email`, `subject`,
              DATE_FORMAT(date_sent, "%e. %c. %Y %k:%i") AS `date_sent_formatted`
              FROM `phg_sent_emails`
              JOIN `phg_emails`
              ON phg_sent_emails.id_email = phg_emails.id_email
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              WHERE phg_sent_emails.id_user = ?
              AND phg_sent_emails.date_sent IS NOT NULL
              AND phg_emails.visible = 1
              ' . $queryFilter . '
              AND phg_campaigns.visible = 1
              ORDER BY `id_event` DESC
      ', $idUser);
    }


    /**
     * Na základě odeslaných e-mailů vrátí seznam kampaní a základních informací o nich,
     * kterých se zúčastnil konkrétní uživatel.
     *
     * @param int $idUser              ID uživatele
     * @return mixed                   Data o tom, kterých kampaních se uživatel zúčastnil
     */
    public static function getUserCampaignsParticipation($idUser) {
      $result = Database::queryMulti('
                SELECT phg_sent_emails.id_campaign, `date_sent`, phg_emails.id_email, `subject`,
                phg_campaigns.name,
                phg_websites.id_website, phg_websites.url,
                DATE_FORMAT(date_sent, "%e. %c. %Y %k:%i") AS `date_sent_formatted`
                FROM `phg_sent_emails`
                JOIN `phg_emails`
                ON phg_sent_emails.id_email = phg_emails.id_email
                JOIN `phg_campaigns`
                ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
                JOIN `phg_websites`
                ON phg_campaigns.id_website = phg_websites.id_website 
                WHERE phg_sent_emails.id_user = ?
                AND phg_sent_emails.date_sent IS NOT NULL
                AND phg_emails.visible = 1
                AND phg_campaigns.visible = 1
                ORDER BY `id_event` DESC', $idUser);

      if ($result != null) {
        foreach ($result as $key => $record) {
          $urlProtocol = get_protocol_from_url($record['url']);

          $result[$key]['url_protocol'] = $urlProtocol;
          $result[$key]['url_protocol_color'] = PhishingWebsiteModel::getColorURLProtocol($urlProtocol);
          $result[$key]['url'] = mb_substr($result[$key]['url'], mb_strlen($urlProtocol));
        }
      }

      return $result;
    }


    /**
     * Vrátí celkový počet odeslaných e-mailů v rámci konkrétní kampaně, případně v rámci několika kampaní.
     *
     * @param int|array $idCampaign    ID konkrétní kampaně nebo pole s ID několika kampaní
     * @return mixed                   Počet odeslaných e-mailů
     */
    public static function getCountOfSentEmailsInCampaign($idCampaign) {
      $cols = (is_array($idCampaign)) ? str_repeat(' OR `id_campaign` = ?', count($idCampaign) - 1) : '';

      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              WHERE `id_campaign` = ?' . $cols . '
              AND `date_sent` IS NOT NULL
      ', $idCampaign);
    }
  }

<?php
  /**
   * Třída sloužící k získávání informací o již zaslaných cvičných podvodných e-mailech
   * s vazbou na konkrétního uživatele.
   *
   * @author Martin Šebela
   */
  class RecievedEmailModel {
    /**
     * Vrátí detailní informace o konkrétním cvičném podvodném e-mailu,
     * který obdržel daný uživatel.
     *
     * @param int $idEmail             ID e-mailu
     * @param int $idUser              ID uživatele
     * @return mixed                   Pole s informacemi o cvičném podvodném e-mailu.
     */
    public static function getRecievedPhishingEmail($idEmail, $idUser) {
      return Database::querySingle('
              SELECT phg_sent_emails.id_campaign, `date_sent`,
              phg_emails.id_email, `sender_name`, `sender_email`, `subject`, `body`,
              `url`,
              DATE_FORMAT(date_sent, "%e. %c. %Y %k:%i") AS `date_sent`,
              phg_campaigns.id_website
              FROM `phg_sent_emails`
              JOIN `phg_emails`
              ON phg_sent_emails.id_email = phg_emails.id_email
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              JOIN `phg_websites`
              ON phg_campaigns.id_website = phg_websites.id_website
              WHERE phg_sent_emails.id_email = ?
              AND phg_sent_emails.id_user = ?
              AND phg_sent_emails.date_sent IS NOT NULL
              AND phg_emails.visible = 1
              AND phg_campaigns.visible = 1
      ', [$idEmail, $idUser]);
    }


    /**
     * Vrátí seznam všech cvičných podvodných e-mailů, které obdržel konkrétní uživatel.
     *
     * @param int $idUser              ID uživatele
     * @param int|null $from           Od jakého záznamu (pořadí) vrátit seznam e-mailů.
     * @return mixed                   Pole e-mailů s informacemi o každém z nich.
     */
    public static function getRecievedPhishingEmails($idUser, $from = null) {
      $query = '
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
              AND phg_campaigns.visible = 1
              ORDER BY `id_event` DESC';
      $args = $idUser;

      if (!is_null($from)) {
        $query .= ' LIMIT ?, 1';
        $args = [$idUser, $from];
      }

      return Database::queryMulti($query, $args);
    }


    /**
     * Vrátí seznam kampaní a základních informací o nich, kterých se zúčastnil konkrétní uživatel.
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
          $urlProtocol = get_protocol_from_url($result[$key]['url']);

          $result[$key]['url_protocol'] = $urlProtocol;
          $result[$key]['url_protocol_color'] = PhishingWebsiteModel::getColorURLProtocol($urlProtocol);
          $result[$key]['url'] = mb_substr($result[$key]['url'], mb_strlen($urlProtocol));
        }
      }

      return $result;
    }


    /**
     * Vrátí celkový počet všech přijatých cvičných podvodných e-mailů pro konkrétního uživatele.
     *
     * @param int $idUser              ID uživatele, pro kterého se počet přijatých e-mailů zjišťuje.
     * @return int                     Celkový počet přijatých cvičných podvodných e-mailů.
     */
    public static function getCountOfRecievedPhishingEmails($idUser) {
      return Database::queryCount('
              SELECT COUNT(*)
              FROM `phg_sent_emails`
              JOIN `phg_campaigns`
              ON phg_sent_emails.id_campaign = phg_campaigns.id_campaign
              WHERE `id_user` = ?
              AND phg_sent_emails.date_sent IS NOT NULL
              AND phg_campaigns.visible = 1
      ', $idUser);
    }
  }

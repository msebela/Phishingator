<?php
  /**
   * Třída zajišťující export statistiky z kampaní.
   *
   * @author Martin Šebela
   */
  class StatsExportModel {
    /**
     * Exportuje konečné akce uživatelů u konkrétní kampaně.
     *
     * @param int $id                  ID kampaně
     * @param string|null $filepath    Cesta na webovém serveru, kde má být exportovaný soubor uložen nebo NULL,
     *                                 pokud se soubor na webový server ukládat nemá.
     * @return string|null             Vrátí cestu k souboru (pokud měl být soubor uložen na webovém serveru),
     *                                 jinak NULL.
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public static function exportEndActions($id, $filepath = null) {
      Logger::info('Žádost o export dat (konečné akce uživatelů) u kampaně.', $id);

      $csvFilename = PHISHING_CAMPAIGN_EXPORT_FILENAME . '-' . $id . '-end-actions';
      $csvData = CampaignModel::getUsersEndActionInCampaign($id);

      // Informace o datech pro CSV export.
      $csvHeader = ['id_line', 'username', 'email', 'group', 'action', 'reported'];
      $csvDataIndexes = ['username', 'used_email', 'used_group', 'name', 'reported'];

      $data = [
        'csvFilename' => $csvFilename,
        'csvHeader' => $csvHeader,
        'csvData' => $csvData,
        'csvDataIndexes' => $csvDataIndexes
      ];

      return self::exportToCSV($data, $filepath);
    }


    /**
     * Exportuje počet jednotlivých akcí každého uživatele zaznamenaných na podvodné stránce u konkrétní kampaně.
     *
     * @param int $id                  ID kampaně
     * @param string|null $filepath    Cesta na webovém serveru, kde má být exportovaný soubor uložen nebo NULL,
     *                                 pokud se soubor na webový server ukládat nemá.
     * @return string|null             Vrátí cestu k souboru (pokud měl být soubor uložen na webovém serveru),
     *                                 jinak NULL.
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public static function exportCountUsersActions($id, $filepath = null) {
      Logger::info('Žádost o export dat (počet jednotlivých akcí každého uživatele) u kampaně.', $id);

      $csvFilename = PHISHING_CAMPAIGN_EXPORT_FILENAME . '-' . $id . '-count-users-actions';
      $csvData = [];

      $statsModel = new StatsModel();
      $actions = $statsModel->getEmptyArrayCountActions();

      $result = Database::queryMulti('
              SELECT phg_users.id_user, `name`, `username`, `used_email` AS `email`, `used_group`, phg_captured_data.id_action, `reported`
              FROM `phg_captured_data`
              JOIN `phg_captured_data_actions`
              ON phg_captured_data.id_action = phg_captured_data_actions.id_action
              JOIN `phg_users`
              ON phg_captured_data.id_user = phg_users.id_user
              WHERE `id_campaign` = ?
              ORDER BY `id_captured_data`
      ', $id);

      if (!empty($result) && !empty($actions)) {
        // Příprava dat z databáze do struktury, která se bude zapisovat do CSV.
        foreach ($result as $record) {
          if (!isset($csvData[$record['id_user']])) {
            $csvData[$record['id_user']] = [
              'username' => $record['username'],
              'email' => $record['email'],
              'group' => $record['used_group'],
              'reported' => $record['reported']
            ];

            // Místo multidimenzionálního pole změníme pole akcí na další prvky
            // původního pole (kvůli následným názvům indexů v poli).
            foreach ($actions as $idAction => $action) {
              $csvData[$record['id_user']]['action-' . $idAction] = 0;
            }
          }
          elseif (isset($csvData[$record['id_user']]['reported']) && !empty($record['reported'])) {
            // Pokud došlo k nahlášení phishingu až v některé z pozdějších akcí, tak u uživatele přepsat "nahlášení".
            $csvData[$record['id_user']]['reported'] = $record['reported'];
          }

          $csvData[$record['id_user']]['action-' . $record['id_action']] += 1;
        }
      }

      // Informace o datech pro CSV export.
      $csvHeader = [
        'id_line', 'username', 'email', 'group',
        'count_page_visited', 'count_invalid_credentials', 'count_valid_credentials', 'reported'
      ];
      $csvDataIndexes = ['username', 'email', 'group', 'action-2', 'action-3', 'action-4', 'reported'];

      $data = [
        'csvFilename' => $csvFilename,
        'csvHeader' => $csvHeader,
        'csvData' => $csvData,
        'csvDataIndexes' => $csvDataIndexes
      ];

      return self::exportToCSV($data, $filepath);
    }


    /**
     * Exportuje všechny zaznamenané uživatelské aktivity na podvodné stránce u konkrétní kampaně.
     *
     * @param int $id                  ID kampaně
     * @param string|null $filepath    Cesta na webovém serveru, kde má být exportovaný soubor uložen nebo NULL,
     *                                 pokud se soubor na webový server ukládat nemá.
     * @return string|null             Vrátí cestu k souboru (pokud měl být soubor uložen na webovém serveru),
     *                                 jinak NULL.
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public static function exportAllCapturedData($id, $filepath = null) {
      Logger::info('Žádost o export všech zaznamenaných dat u kampaně.', $id);

      $csvFilename = PHISHING_CAMPAIGN_EXPORT_FILENAME . '-' . $id . '-all-actions';
      $csvData = CampaignModel::getCapturedDataInCampaign($id);

      // Informace o datech pro CSV export.
      $csvHeader = [
        'id_line', 'username', 'email', 'group', 'action', 'action_datetime', 'reported', 'ip', 'user_agent', 'http_post_data_json'
      ];
      $csvDataIndexes = [
        'username', 'used_email', 'used_group', 'name', 'visit_datetime', 'reported', 'ip', 'browser_fingerprint', 'data_json'
      ];

      $data = [
        'csvFilename' => $csvFilename,
        'csvHeader' => $csvHeader,
        'csvData' => $csvData,
        'csvDataIndexes' => $csvDataIndexes
      ];

      return self::exportToCSV($data, $filepath);
    }


    /**
     * Exportuje všechna data do ZIP archivu.
     *
     * @param int $idCampaign          ID kampaně
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    public static function exportAllToZipArchive($idCampaign) {
      Logger::info('Žádost o vytvoření archivu se všemi zaznamenanými daty o kampani.', $idCampaign);

      $zip = new ZipArchive();

      $filepath = CORE_DOCUMENT_ROOT . '/temp/';
      $zipFilepath = $filepath . PHISHING_CAMPAIGN_EXPORT_FILENAME . '-' . $idCampaign . '-' . date('Y-m-d') . '.zip';

      $files = [];

      if ($zip->open($zipFilepath, ZipArchive::CREATE) === true) {
        // Získání jednotlivých souborů do archivu.
        $files[] = self::exportEndActions($idCampaign, $filepath);
        $files[] = self::exportAllCapturedData($idCampaign, $filepath);
        $files[] = self::exportCountUsersActions($idCampaign, $filepath);

        // Vložení všech souborů do archivu.
        foreach ($files as $file) {
          if (!empty($file)) {
            $zip->addFile($file, basename($file));
          }
        }

        $zip->close();

        // Vrácení archivu se soubory ke stažení uživateli.
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFilepath) . '"');
        header('Content-Length: ' . filesize($zipFilepath));

        readfile($zipFilepath);

        // Smazání souborů z dočasného adresáře.
        $files[] = $zipFilepath;

        foreach ($files as $file) {
          if (file_exists($file)) {
            unlink($file);
          }
        }

        exit();
      }
    }


    /**
     * Exportuje zvolená data do CSV souboru a ten buď vrátí uživateli ke stažení, nebo uloží
     * do konkrétního adresáře na webovém serveru.
     *
     * @param array $data              Data obsahující název CSV souboru, hlavičku, názvy indexů a samotná data.
     * @param string|null $newFilepath Nepovinný parametr určující cestu, kde má být exportovaný CSV soubor
     *                                 uložen na webovém serveru (jinak se ukládá do paměti).
     * @return string|null             Pokud má být exportovaný soubor uložený na webovém serveru (viz předchozí
     *                                 parametr), vrátí název exportovaného souboru.
     * @throws UserError               Výjimka obsahující textovou informaci o chybě pro uživatele.
     */
    private static function exportToCSV($data, $newFilepath = null) {
      if (!empty($data) && count($data) == 4) {
        // Vytvoření CSV souboru, hlavičky a její zápis do souboru.
        $csvFilename = $data['csvFilename'] . '-' . date('Y-m-d') . '.csv';

        // Vytvářet soubor v paměti nebo v konkrétním adresáři na webovém serveru...
        $filepath = (($newFilepath == null) ? 'php://memory' : $newFilepath . $csvFilename);

        $csvFile = fopen($filepath, 'w');
        fputcsv($csvFile, $data['csvHeader'], PHISHING_CAMPAIGN_EXPORT_DELIMITER);

        $firstIndex = 1;

        // Zápis připravených dat do CSV souboru.
        foreach ($data['csvData'] as $row) {
          $csvLineData = [];

          // První sloupec bude vždy ID záznamu.
          $csvLineData[] = $firstIndex;
          $firstIndex++;

          // Příprava zapisovaných dat na základě názvů předaných indexů
          // (aby byla data ve správném pořadí vůči hlavičce CSV souboru).
          foreach ($data['csvDataIndexes'] as $dataIndex) {
            $csvLineData[] = $row[$dataIndex];
          }

          fputcsv($csvFile, $csvLineData, PHISHING_CAMPAIGN_EXPORT_DELIMITER);
        }

        fseek($csvFile, 0);

        // Vrácení CSV souboru uživateli ke stažení.
        if ($newFilepath == null) {
          header('Content-Type: text/csv');
          header('Content-Disposition: attachment; filename="' . $csvFilename . '";');
        }

        fpassthru($csvFile);

        if (!$newFilepath) {
          exit();
        }
        else {
          return $filepath;
        }
      }
      else {
        throw new UserError('Ke zvolené kampani neexistují žádná data.', MSG_ERROR);
      }
    }
  }

<?php
  /**
   * Třída sdružující obecné funkce týkající se odesílání e-mailů.
   *
   * @author Martin Šebela
   */
  class EmailSender {
    /**
     * Pokud na poštovní server odejde určitý počet e-mailů, uspí na určitou dobu skript, aby poštovní server
     * e-maily mezitím odbavil.
     *
     * @param int $countSentMails      Počet odeslaných e-mailů v aktuální iteraci
     * @return int                     Nový počet odeslaných e-mailů (buď zvýšený o 1, pokud se ještě nedosáhlo limitu,
     *                                 popř. nastavený na 0)
     */
    protected function sleepSender($countSentMails) {
      // Pokud na poštovní server odešel daný počet e-mailů, uspat na určitou dobu skript,
      // aby poštovní server e-maily mezitím odbavil.
      if ($countSentMails >= EMAIL_SENDER_EMAILS_PER_CYCLE) {
        Logger::info(EMAIL_SENDER_EMAILS_PER_CYCLE . ' emails sent. Script will be suspended for ' . EMAIL_SENDER_DELAY_MS . ' seconds.');

        // Uspání skriptu.
        usleep(EMAIL_SENDER_DELAY_MS * 1000);

        // Prodloužení maximální doby běhu skriptu.
        set_time_limit(EMAIL_SENDER_CPU_TIME_S);

        return 0;
      }

      return $countSentMails + 1;
    }
  }

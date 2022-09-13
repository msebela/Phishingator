<?php
  /**
   * Třída zpracovává uživatelský vstup, který neobsloužila žádná jiná třída
   * na stejné úrovni, přičemž svůj výstup předává další vrstvě pro výpis.
   *
   * @author Martin Šebela
   */
  class ErrorNotFoundController extends Controller {
    /**
     * Zpracuje vstup z URL adresy a na základě toho zavolá odpovídající metodu.
     *
     * @param array $arguments         Uživatelský vstup.
     */
    public function process($arguments) {
      $this->setTitle('Sekce nenalezena');
      $this->setView('error-not-found');
    }
  }

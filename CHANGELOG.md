# Phishingator Changelog

## v1.5

- Podvodné stránky
  - Implementována ochrana před automatizovaným nahlášením cvičných podvodných stránek webovými prohlížeči do blacklistů
  - Implementována možnost pro ověřování hesla vůči webové službě
  - Implementována možnost pro ověřování hesla na základě heslové politiky
  - Vylepšení náhledu podvodných stránek pro administrátory
      - URL adresa náhledu podvodné stránky je nyní totožná s tou, kterou dostanou cíloví příjemci kampaně
  - Implementováno uvažování prefetch HTTP hlaviček `Sec-Purpose`, `Purpose`
- Phishingové kampaně
  - Vylepšení GUI dialogu pro výběr příjemců ve phishingové kampani
    - Přidáno tlačítko pro rozbalení všech skupin uživatelů
    - Uživatelé v každé skupině jsou řazeni podle abecedy
  - Změna formátu data v exportovaných souborech na ISO formát
  - Změna povolených znaků v uživatelském jméně z testované organizace
  - Oprava chyby při výběru většího počtu příjemců při vytváření kampaně
- Šablony podvodných stránek
    - Přidána nová základní šablona podvodné stránky (Ubohý přihlašovací formulář s logem)
- Konfigurace
    - Přidána možnost definování seznamu povolených domén, ze kterých mohou pocházet příjemci kampaní
    - Změna nastavení `OIDCXForwardedHeaders`
- Monitoring
    - Přidána možnost definování seznamu povolených IP, které mohou přistupovat k monitoringu
    - Změna mapování návratových kódů podle Nagios
- Logování webového serveru
    - Přidán nový způsob logování formou JSON
    - Přidáno logování ve formátu GELF pro GrayLog
- Vylepšení GUI
    - Vylepšené tabulky ve statistice kampaně (úprava rozmazávání identit a nezalamování nevhodných sloupců)
    - Vylepšení responzivní verze vzdělávací stránky 
  - Vylepšená hlavička náhledu podvodné stránky
  - Změna max. počtu záznamů na jedné stránce při výpisu uživatelů
- Upgrade externích knihoven
- Refactoring
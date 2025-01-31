# Phishingator Changelog

## v1.6

- Phishingové kampaně
  - Implementována možnost vyplnění času ukončení kampaně
  - Implementována možnost okamžitého (předčasného) ukončení kampaně
  - Implementováno ukládání stavu zapnutí/vypnutí rozmazání identit ve statistikách kampaní pro právě přihlášeného uživatele
  - Implementována možnost určující, zdali se má příjemcům kampaně odeslat po jejím ukončení notifikace u absolvování cvičného phishingu
  - Implementováno automatické skrytí sloupce s lístkem kampaně v seznamu kampaní, pokud není u žádné kampaně použit
  - Přidáno tlačítko pro náhled podvodné stránky v seznamu kampaní
  - Přidáno tlačítko pro zobrazení statistiky u již spuštěné kampaně ve formuláři při její úpravě
  - Přidány výsledky v procentech do administrátorské notifikace u ukončení phishingové kampaně a vylepšeno zarovnání vůči zbylému obsahu
  - Vylepšen způsob řazení v tabulce reakcí uživatelů (nyní řazeno nejprve podle názvu pracoviště, pak podle uživatelského jména příjemců)
  - Vylepšen způsob automatického skrytí popisků u sloupcových grafů při jejich větším počtu
  - Změna povolených znaků v uživatelském jméně z testované organizace
  - Úprava textu uživatelské notifikace o absolvování cvičného phishingu
  - Oprava chyby při odstraňování příjemců v již vytvořené kampani
  - Oprava chyby s neprovedeným nahrazením proměnné odesílatele za e-mail uživatele
  - Oprava chyby s importováním e-mailů z CSV souboru
  - Oprava časové zóny v ISO formátu v exportovaných souborech se statistikami kampaně
  - Oprava možné chyby při odeslání neúplné notifikace o absolvování cvičného phishingu uživateli, kterému se ve stejný okamžik změnil e-mail
- Podvodné e-maily
  - Implementována možnost základního HTML formátování e-mailů pomocí tagů a následného zaslání e-mailu ve formátu HTML
    - Přidán tag `[a href=(...)][/a]` pro skrytí odkazu do textu a základní tagy pro formátování textu `[b][/b]`, `[i][/i]`, `[u][/u]`, `[s][/s]`, `[br]`
  - Implementována možnost definovat pořadí indicií, na základě kterého budou vypsány v seznamu všech indicií na vzdělávací stránce
  - Přidány proměnné pro jméno a příjmení uživatele, které je možné používat v těle e-mailu
  - Přidáno ověření, zdali nově přidávaná indicie k podvodnému e-mailu již není součástí jiné existující indicie a zdali splňuje minimální délku
  - Zvýšení limitu počtu znaků pro popis indicie
  - Oprava chyby u nahrazení proměnné s datem, kde se doplňovalo aktuální datum místo data odeslání e-mailu
- Podvodné stránky
  - Implementována možnost ověřující existenci uživatelského jména v hesle při ověřování vůči heslové politice (v závislosti na konfiguraci)
  - Implementovány security HTTP hlavičky na podvodné stránky
  - Implementována možnost ignorování přístupů na podvodné stránky z vybraných IP rozsahů (v závislosti na konfiguraci)
  - Implementována možnost automatického vytvoření konfiguračního souboru podvodné stránky pouze na základě dat z databáze, pokud konfigurační soubor neexistuje (např. byl smazán)
  - Vylepšení všech univerzálních šablon pro podvodné stránky tak, aby v nich mohl být uveden nepovinný, editovatelný název služby, ke které se uživatel přihlašuje
  - Oprava chyby způsobující problém se zobrazením náhledu podvodné stránky pro administrátory
  - Oprava stylu hlavičky náhledu podvodné stránky pro administrátory
- Uživatelé
  - Implementována podpora multiskupin pro uživatele
     - Každý uživatel může být nyní členem několika skupin (např. různá oddělení v organizaci), které se zohledňují v grafech i statistikách 
  - Přidáno ověřování aktuálně přidělených uživatelských práv při každém požadavku uživatele
  - Přidáno tlačítko do profilu uživatele odkazující na seznam zvýrazněné aktivity uživatele v konkrétní kampani
- Roční statistiky/Úvodní stránka
  - Přidáno zaokrouhlování hodnot na desetiny pro lepší přesnost vykreslování sloupcových grafů s reakcemi uživatelů podle oddělení
- Úvodní stránka
  - Úprava zobrazení počtu dobrovolníků tak, aby se nezahrnovali neaktivní uživatelé (např. uživatelé již neexistující v LDAP)
- Přijaté phishingové e-maily
  - Implementováno možné skrytí cvičných podvodných e-mailů před uživateli u právě běžících kampaní v seznamu přijatých e-mailů do té doby, než budou dané kampaně ukončeny (v závislosti na konfiguraci)
  - Oprava chyby způsobující nemožnost zobrazit vzdělávací stránku u přihlášeného uživatele s oprávněním běžného uživatele
- Architektura
  - Implementována podpora mapování různých identit stejného uživatele získaných z SSO na jeden účet ve Phishingatoru
  - Implementována možnost definování, jaká podoba uživatelského jména se má zobrazovat napříč GUI (uživatelské jméno vs. uživatelské jméno získané z e-mailu)
  - Úprava CI/CD pipeline pro GitLab
  - Fixování portu a verze WAF
  - Vylepšena a optimalizována JavaScriptová volání
  - Změna nastavení `OIDCScope` za účelem získání alternativních identit uživatele
  - Odstranění HTTP hlaviček `X-Powered-By` a `X-XSS-Protection`
- Konfigurace
  - Změna výchozí volby "Akce po odeslání formuláře" na "Zobrazit vzdělávací stránku s indiciemi (po zadání čehokoliv)"
  - Odstranění direktivy `ServerAdmin` z konfigurace Phishingatoru i ze všech konfigurací podvodných stránek
  - Odstranění proměnné `APACHE_DOCUMENT_ROOT` z konfigurace
- Monitoring
  - Přidána možnost nepovinného sufixu u uživatelského jména testovacího účtu určeného pro monitoring
  - Přidána možnost přeskočení testu se zadáním neplatných údajů u testovacího účtu
  - Změna způsobu kontroly IP adresy u monitoringu
- Správa
  - Aktualizace ukázkových screenshotů
  - Úpravy manuálu
- Oprava chyby s nezobrazováním tlačítka pro přesun do horní části stránky
- Upgrade externích knihoven
- Refactoring a další drobné změny


## v1.5

- Phishingové kampaně
  - Vylepšení dialogu pro výběr příjemců ve phishingové kampani
    - Přidáno tlačítko pro rozbalení všech skupin uživatelů
    - Uživatelé v každé skupině jsou nově řazeni podle abecedy
  - Změna povolených znaků v uživatelském jméně z testované organizace
  - Změna formátu data v exportovaných souborech na ISO formát
  - Vylepšené tabulky ve statistice kampaně (úprava rozmazávání identit a nezalamování nevhodných sloupců)
  - Oprava chyby při výběru většího počtu příjemců při vytváření kampaně
- Podvodné e-maily
  - Oprava chyby s kontrolou e-mailu odesílatele vůči povoleným doménám při různých velikostech znaků 
- Podvodné stránky
  - Implementována ochrana před automatizovaným nahlášením cvičných podvodných stránek webovými prohlížeči do blacklistů
  - Implementováno uvažování prefetch HTTP hlaviček `Sec-Purpose`, `Purpose` pro detekci automatizovaných přístupů na podvodnou stránku
  - Implementována možnost pro ověřování hesla vůči webové službě
  - Implementována možnost pro ověřování hesla na základě heslové politiky
  - Přidána nová univerzální šablona podvodné stránky (ubohý přihlašovací formulář s logem)
  - Změna v umístění chybové hlášky v univerzální šabloně (obecný přihlašovací formulář) podvodné stránky tak, aby byla pod hlavičkou
  - Vylepšená hlavička náhledu podvodné stránky
- Uživatelé
  - Změna max. počtu záznamů na jedné stránce při výpisu uživatelů
- Konfigurace
  - Změna nastavení `OIDCXForwardedHeaders` za účelem získání `X-Forwarded` hlaviček
  - Přidána chybějící proměnná pro specifikování dalších povolených domén, ze kterých mohou pocházet příjemci
- Monitoring
  - Implementována možnost definování seznamu povolených IP, které mohou přistupovat k monitoringu
  - Změna mapování návratových kódů podle Nagios
- Logování webového serveru
  - Přidán nový způsob logování formou JSON
  - Přidáno logování ve formátu GELF pro GrayLog
- Vylepšení GUI
  - Vylepšení responzivní verze vzdělávací stránky
- Upgrade externích knihoven
- Refactoring


## v1.4

- Phishingové kampaně
  - Implementována možnost volitelně specifikovat v konfiguraci Phishingatoru seznam dalších povolených domén, ze kterých mohou pocházet příjemci
  - Implementována možnost importu příjemců kampaně z CSV a TXT souboru
  - Přidána možnost prokliknutí se do systému pro správu požadavků při vyplnění identifikátoru lístku (pokud je odkaz do systému definován v konfiguraci)
  - Vylepšena podpora ověřování platnosti přihlašovacích údajů vůči konfigurovatelnému LDAP serveru a portu s případným prefixem a sufixem k uživatelskému jménu
  - Změna povolených znaků v uživatelském jméně z testované organizace
  - Prohození URL adresy a názvu podvodné stránky při jejím výběru ve formuláři s kampaní
  - Přidání ošetření pro ověřování hesla u služby Kerberos
  - Oprava problému s mapováním akcí uživatelů v grafu, který znázorňuje všechny provedené akce v kampani
- Podvodné stránky
  - Implementován nový způsob nasazení podvodné stránky a vytváření konfiguračních souborů
  - Implementována možnost definovat u podvodných stránek nepovinný název služby, který se propíše do šablony podvodné stránky (pokud to šablona umožňuje)
  - Implementována možnost ignorování přístupů robotů na podvodné stránky na základě hlavičky `HTTP_USER_AGENT`
  - Přidán stav `cloned` (tj. stránka zkopírována) u šablon podvodných stránek
    - Na vzdělávací stránce se nově u šablon, které mají tento příznak nastaven, zobrazuje automatická indicie o tom, že útočníci jsou schopni zkopírovat vzhled stránky do posledního detailu
  - Přidána možnost zobrazení podvodné stránky na HTTP i HTTPS z důvodu HSTS
  - Vylepšena funkcionalita pro zjištění aktuálního stavu nasazení podvodné stránky o další možné stavy
  - Vylepšení náhledu podvodných stránek pro administrátory
    - Přidáno tlačítko pro ukončení náhledu a další drobné změny
  - Změna způsobu vytváření subdomén u podvodných stránek z důvody vazby na Traefik Proxy
  - Odstranění přesměrování na rozcestník Phishingatoru při přístupu na podvodnou stránku bez argumentů
  - Oprava chyby při vytvoření podvodné stránky obsahující v názvu (sub)domény kombinaci malých a velkých písmen
- Uživatelské skupiny
  - Vylepšení výběru zobrazovaných skupin z LDAP tak, aby je nebylo nutné vypisovat, ale bylo možné je jen zaškrtávat z připraveného seznamu
  - Zvýšení limitu počtu znaků u zobrazovaných LDAP skupin
  - Ošetření názvů LDAP skupin
  - Vylepšení GUI u delších názvů skupin
- Architektura
  - Přidána nová microservice zajišťující vlastní LDAP server
  - Přidána nová microservice zajišťující plánovač úloh
  - Implementován jednotný HTTP token umožňující přístup externích aplikací do Phishingatoru
  - Přidána podpora pro externí nástroj zajišťující správu podvodných domén v proxy
  - Změny v deployment procesu a síťování
- Správa
  - Vylepšení skriptů pro zálohování, obnovu a reset databáze a pro přidání nové šablony podvodné stránky
  - Úprava manuálu a odkazů na nápovědu
- Monitoring
  - Implementován endpoint určený pro monitoring
  - Implementován test ověřující přípojení k databázi, existenci záloh databáze, připojení k LDAP, test ověřující funkční ověřování platnosti přihlašovacích údajů zadaných do podvodných stránek
  - Implementován přístup pouze z konkrétní IP adresy a se zabezpečeným HTTP tokenem
- Konfigurace
  - Přidána možnost odesílání e-mailů z Phishingatoru přes jiný SMTP server nastavitelný v konfiguraci
  - Změna nastavení `OIDCSessionInactivityTimeout` pro zvýšení min. doby neaktivity uživatele
- Vylepšení responzivní verze frontendu interní části a vzdělávací stránky
- Upgrade externích knihoven
- Rozsáhlý refactoring a další drobná vylepšení


## v1.3

- Phishingové kampaně
  - Implementována možnost exportovat obrázky grafů se statistikami kampaně do PNG
  - Přidáno zobrazení počtu příjemců do formuláře u již přidaných kampaní a k jednotlivým skupinám v interaktivním dialogu pro výběr příjemců
  - Přidána možnost výchozího nastavení volby "Akce po odeslání formuláře" v konfiguraci
  - Zvýšení počtu e-mailů, které se odešlou za jednu iteraci
  - Zvýšena prodleva mezi jednotlivými iteracemi odesílání e-mailů
  - Změna řazení záznamů v exportu provedených akcí na podvodné stránce od nejstarších po nejnovější
  - Oprava sjednocení názvů exportovaných souborů do angličtiny
- Podvodné stránky
  - Implementována možnost definovat v URL adrese podvodné stránky fiktivní adresáře a GET argumenty
  - Implementována možnost definovat v URL adrese podvodné stránky umístění identifikátoru uživatele proměnnou `%id%`
  - Vylepšení náhledu podvodných stránek pro administrátory
    - URL adresa náhledu podvodné stránky je nyní totožná s tou, kterou dostanou cíloví příjemci kampaně
  - Vylepšení procesu správy podvodných stránek
  - Změna v přesměrování neoprávněných přístupů na rozcestník Phishingatoru
  - Odstraněno přesměrování při nadměrném počtu požadavků na podvodné stránce nahrazené ukončením skriptu
  - Oprava kontroly směrování DNS u subdomén podvodných stránek
- Roční statistiky
  - Implementována možnost exportovat obrázky grafů s ročními statistikami do PNG
  - Přidáno zobrazení trendu oproti předchozímu roku do každoroční statistiky
  - Odstranění grafu s počty dobrovolníků v pracovištích za jednotlivé roky
- Přijaté phishingové e-maily
  - Změna prokliknutí na detail podvodného e-mailu na prokliknutí na vzdělávací stránku s podrobnějšími informacemi
- Architektura
  - Implementováno Content Security Policy zakazující až na výjimky externí zdroje a `unsafe-inline` zápisy a s tím související refactoring všech nevyhovujících zápisů
  - Přidána nová microservice zajišťující činnost WAF
  - Přidáno ověření a zobrazení upozornění pro uživatele při použití identity nesprávné organizace
  - Odstraněno přesměrování při chybě, kdy se nepodaří získat informace o identitě uživatele 
  - Změna adresáře, do kterého se zapisují data Phishingatoru na `/phishingator-data/`
  - Přidáno ošetření LDAP dotazů
  - Změna názvů kontejnerů
- Správa
  - Vylepšení skriptů pro zálohování, obnovu a reset databáze a pro přidání nové šablony podvodné stránky
  - Přidání kapitoly o správě Phishingatoru do manuálu a další drobné úpravy
  - Změna textu logovacích hlášek do angličtiny
- Úprava tlačítka pro změnu role uživatele
- Upgrade externích knihoven a PHP na nejnovější verze
- Rozsáhlý refactoring


## v1.2

- Phishingové kampaně
  - Implementován nový způsob možné agregace výsledků kampaně, a to podle pracoviště příjemce získaného z LDAP (v závislosti na konfiguraci lze ponechat i původní agregaci na základě subdomény v e-mailu příjemce)
  - Implementována nová možnost pro volbu "Akce po odeslání formuláře" umožňující zobrazení vzdělávací stránky jen po zadání platných přihlašovacích údajů
  - Přidána možnost výchozího nastavení rozmazání identit uživatelů ve statistikách kampaně (v závislosti na konfiguraci)
    - IP adresa je nově také rozmazána
  - Vylepšen interaktivní dialog pro výběr příjemců kampaně
    - Přidán checkbox pro výběr všech příjemců ze skupiny (místo původního tlačítka, u kterého nebylo jasné, zdali je označené) 
    - Přidáno tlačítko pro výběr příjemců ze všech skupin
    - Další mírné úpravy GUI dialogu
  - Přidán název organizace do administrátorských notifikací
  - Úprava textu uživatelské notifikace o absolvování cvičného phishingu
  - Změna názvů exportovaných souborů do angličtiny
  - Oprava problikávání Bootstrap komponenty Tooltip ve statistice kampaně 
  - Opraven export statistik v CSV a ZIP formátu
  - Oprava prohození sloupce "email" a "group" v exportovaném CSV s reakcemi uživatelů na cvičný phishing
- Podvodné e-maily
  - Přidán podbarvený ukazatel počtu již přidaných indicií ke každému e-mailu v seznamu všech přidaných e-mailů
  - Přidáno automatické přesměrování do formuláře pro přidání indicií po úspěšném vytvoření nového podvodného e-mailu 
  - Změna e-mailové hlavičky ve všech e-mailech odesílaných z Phishingatoru na jednotnou hlavičku `X-CESNET-Phishingator`
- Podvodné stránky
  - Implementována možnost zakládat subdomény podvodných stránek vyhovujících regulárnímu výrazu 
  - Implementována funkcionalita zjišťující aktuální stav přesměrování podvodné domény v přehledu podvodných stránek (např. chybné DNS, nedokončené směrování v proxy)
  - Přidán seznam (sub)domén svázaných s Phishingatorem, na kterých může být podvodná stránka umístěna
  - Přidána kontrola DNS směrování domény podvodné stránky
  - Vylepšení grafické podoby chybové hlášky v univerzální šabloně podvodné stránky
  - Oprava chyby, kdy nedošlo ke generování nového konfiguračního souboru podvodné stránky po změně šablony
- Uživatelé
  - Implementováno zjišťování primárního pracoviště uživatele z LDAP, které se ukládá do databáze Phishingatoru
    - Používá se pro případnou agregaci statistik kampaní podle pracoviště
    - Implementována automatická aktualizace primárního pracoviště z LDAP
    - Přednost pro primární pracoviště mají pracoviště specifikovaná v seznamu LDAP skupin definovaných u administrátorského oprávnění v sekci "Uživatelské skupiny"
  - Přidána základní podpora e-mailových aliasů uložených v LDAP
- Úvodní stránka
  - Přidáno skrytí nulové úspěšnosti v odhalování phishingu v uživatelské sekci v případě, kdy uživatel zatím neobdržel žádný cvičný podvodný e-mail
- Nápověda
  - Přidána nová sekce "Jak poznat phishing" pro běžné uživatele
  - Přidána nová sekce "Jak připravit phishing" pro administrátory a správce testů
- Architektura
  - Implementována podpora autentizace uživatelů přes OIDC
  - Implementováno nasazení pomocí CI/CD
  - Nasazení Traefik Proxy směřujícího požadavky do kontejneru s Phishingatorem
    - Změna procesu správy a nasazování podvodných stránek
    - Změna způsobu získávání IP adresy
  - Přidání nastavení časové zóny v kontejnerech
  - Přidány databázové tabulky pro uložení archivních statistik
  - Změna kódování databázových sloupců na multibyte UTF-8
  - Změna přihlašovací identity uživatele na e-mail uživatele získané z proměnné `REMOTE_USER`
  - Odstranění přesměrování na rozcestník Phishingatoru v případě, kdy se nepodaří získat identitu uživatele
  - Odstranění databázových sloupců určených pro ukládání lokální IP adresy
- Správa
  - Vylepšení skriptů pro zálohování, obnovu a reset databáze a pro přidání nové šablony podvodné stránky
  - Přidány pomocné skripty pro build aplikace
  - Přidány ukázkové screenshoty
  - Úpravy manuálu
  - Zveřejnění licence Phishingatoru
- Vylepšení a mírné úpravy GUI
  - Přidána favicon
  - Vylepšení tabulek a definování minimální šířky jednotlivých sloupců
  - Vylepšení responzivní verze
- Upgrade externích knihoven
- Refactoring


## v1.1

- Úpravy a vylepšení Phishingatoru v rámci pilotního provozu na ZČU


## v1.0

- Verze Phishingatoru vydaná jako výsledek bakalářské práce
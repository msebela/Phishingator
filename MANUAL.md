# Phishingator – Uživatelská příručka

Uživatelská příručka je rozdělena na:

1. [příručku pro běžné uživatele](#1-pro-uživatele) Phishingatoru,
2. [příručku pro administrátory](#2-pro-administrátory), kteří budou ve Phishingatoru vytvářet nové phishingové kampaně, cvičné phishingové e-maily a podvodné stránky.

## 1 Pro uživatele


### 1.1 Úvodní stránka

Obsahuje **osobní statistiku** uživatele uvádějící počet cvičných phishingových zpráv, které byly uživateli odeslány a stejně tak **osobní úspěšnost v odhalování phishingu**, kterou si lze prohlédnout i na příslušném grafu (pokud jsou již k dispozici nějaká relevantní data).


### 1.2 Moje účast v programu

Uživatel si po přihlášení do systému může zvolit, zdali chce **dobrovolně přijímat cvičné phishingové e-maily**. Spolu s tím si může uživatel nastavit **limit** (resp. maximální počet) cvičných phishingových e-mailů, které chce ještě **do budoucna přijmout**. Svou účast v programu má uživatel možnost **kdykoliv zrušit**.


### 1.3 Přijaté phishingové e-maily

V této sekci má uživatel možnost si **detailně prohlédnout seznam všech cvičných phishingových e-mailů**, které mu byly v minulosti doručeny. U každého e-mailu navíc vidí i **svou reakci**, tedy to, jakým způsobem na konkrétní e-mail reagoval (například zdali navštívil podvodnou stránku přístupnou z tohoto e-mailu a zadal do formuláře platné přihlašovací údaje apod.) či nereagoval. U podvodného e-mailu je vždy vytvořen i **seznam indicií** (vyznačené pasáže v textu s popisem, co konkrétně vyvolává podezření), na základě kterých může uživatel **zpětně zjistit, zdali postupoval správně** nebo čeho by si měl do **budoucna všímat**.


## 2 Pro administrátory

*Administrátor* je nejvyšší oprávnění, které je ve Phishingatoru k dispozici. Obvykle má toto oprávnění zaměstnanec CERT/CSIRT týmu.


### 2.1 Změna role

Systém umožňuje uživatelům s vyšším oprávněním (tedy minimálně s oprávněním *správce testů*) přepínat mezi všemi ostatními, nižšími rolemi. Pro změnu role stačí v systému kliknout na tlačítko *Změnit roli* (příp. *Role*) v pravé horní části obrazovky.


#### 2.1.1 Úvodní stránka

Obsahuje **grafy** znázorňující **průběžnou úspěšnost všech přidaných kampaní**. Spolu s tím je k dispozici i další graf, který stejná data převádí do podoby sloupců symbolizujících **skupinu**, do které uživatelé (resp. příjemci) spadají (na základě jejich e-mailu). Dále je zde pak k dispozici i další graf znázorňující **počet dobrovolníků napříč skupinami**.


### 2.2 Kampaně

Umožňuje **přidávat nové a upravovat jakékoliv existující kampaně**. U již běžících a ukončených kampaní je možnost si prohlédnout **statistiku nebo průběžné výsledky** a **seznam všech akcí**, které uživatelé provedli na podvodných webových stránkách.


#### 2.2.1 Seznam kampaní

Obsahuje stručný přehled o všech přidaných kampaních. Data (sloupec *Aktivní od* a *Aktivní do*) svým barevným podbarvením znázorňují, zdali datum již proběhlo či nikoliv, a to následujícím způsobem:
* **zelené podbarvení** – datum ještě nenastalo (tzn. start kampaně ještě neproběhl nebo kampaň stále běží)
* **šedé podbarvení** – datum již nastalo (tzn. start kampaně již proběhl nebo byla kampaň již ukončena)
* **žluté podbarvení** – poslední den, během kterého bude kampaň aktivní


#### 2.2.2 Přidání nebo úprava kampaně

Vstupní pole, která se musí při vytváření nebo během upravování kampaně vyplnit, jsou následující:

* **název** – slouží k identifikaci v systému, resp. v seznamu přidaných kampaní
* **číslo lístku s kampaní** – nepovinný údaj, který reprezentuje číslo lístku (ticketu) v RT systému s evidencí o realizaci phishingové kampaně
* **rozesílaný podvodný e-mail** – cvičný phishingový e-mail, který bude zvoleným příjemcům kampaně doručen do jejich e-mailových schránek; z tohoto e-mailu se budou moci dostat na podvodnou webovou stránku (viz následující bod)
* **podvodná webová stránka přístupná z e-mailu** – stránka, která bude přístupná z odkazu umístěného ve cvičném podvodném e-mailu a na které je umístěn formulář pro sbírání dat v něm zadaných
* **akce po odeslání formuláře** – akce, která nastane na straně uživatele po odeslání formuláře na podvodné webové stránce
* **spustit rozesílání e-mailů v čase** – čas, ve kterém se začnou v den startu kampaně rozesílat cvičné phishingové e-maily
  * příjemcům dodatečně přidaným po tomto čase bude podvodný e-mail rozeslán následující den ve stejný čas
* **start kampaně** – datum spuštění kampaně (den, kdy se začnou rozesílat cvičné phishingové e-maily a den, od kterého začne být vybraným příjemcům dostupná podvodná webová stránka)
* **ukončení kampaně** – datum ukončení kampaně (den, kdy přestanou fungovat odkazy vedoucí na podvodnou stránku přístupnou z cvičných phishingových e-mailů a tedy den, kdy skončí zaznamenávání jakékoliv aktivity na této podvodné stránce)
* **seznam účastníků kampaně** – příjemci zasílaného cvičného podvodného e-mailu a zároveň jediní uživatelé, kteří budou mít přes vlastní a jedinečný odkaz přístup na zvolenou podvodnou webovou stránku
  * výběr příjemců probíhá buď manuálně vypsáním e-mailových adres, nebo po otevření dialogového okna stisknutím tlačítka *Vybrat příjemce* (pod vstupním polem se seznamem příjemců)



### 2.3 Podvodné e-maily a indicie

Obsahuje seznam všech přidaných podvodných e-mailů. Ke každému podvodnému e-mailu lze po jeho přidání vložit indicie, na základě kterých mohl uživatel phishingový e-mail rozpoznat.


#### 2.3.1 Přidání nebo úprava podvodného e-mailu

Vstupní pole při vytváření nebo úpravě podvodného e-mailu jsou následující:

* **název** – slouží k identifikaci v systému
* **jméno odesílatele** – nepovinný údaj, který v e-mailových klientech doplňuje e-mail odesílatele; při jeho nevyplnění bude v odeslaném podvodném e-mailu vidět pouze e-mail odesílatele
* **e-mail odesílatele** - umožňuje definovat e-mail, ze kterého budou odesílány podvodné e-maily, případně použít proměnnou `%recipient_email%`, místo které dojde ke vložení e-mailu příjemce (tzn. e-mail odesílatele i příjemce bude stejný)
* **předmět**
* **tělo** – v těle e-mailu je možné použít několik proměnných, které budou při odeslání podvodného e-mailu nahrazeny reálným (resp. personalizovaným) obsahem (pro vložení proměnné do těla e-mailu stačí kliknout na její název vedle vstupního pole):
  * `%recipient_username%` – uživatelské jméno příjemce
  * `%recipient_email%` – e-mail příjemce
  * `%date_cz%` – datum, ve kterém dochází k odeslání e-mailu v českém formátu (např. 1. 9. 2022)
  * `%date_en%` – datum, ve kterém dochází k odeslání e-mailu ve formátu `YYYY-MM-DD` (např. 2022-09-01)
  * `%url%` – URL podvodné stránky svázané s e-mailem


#### 2.3.2 Přidání nebo úprava indicií u podvodného e-mailu

Pro přidání indicií k phishingovému e-mailu stačí v souhrnném seznamu všech e-mailů kliknout na tlačítko *Nastavit indicie*. Následuje zobrazení náhledu přidaného podvodného e-mailu a formuláře pro přidání nových či úpravu dosud přidaných indicií. Vstupní pole jsou následující:

* **indicie (podezřelý řetězec)** – konkrétní pasáž v textu, která má být systémem označena (resp. zakroužkována) a se kterou má být svázán popis indicie; pokud není cílem odkázat na text v těle e-mailu, ale na jinou část e-mailu, je možné použít následující proměnné:
  * `%sender_name%` – pro označení jména odesílatele e-mailu
  * `%sender_email%` – pro označení e-mailu odesílatele
  * `%subject%` – pro označení předmětu e-mailu

* **nadpis** – stručný nadpis indicie nebo název kategorie (např. podezřelé oslovení, překlepy apod.)
* **popis** – nepovinný údaj obsahující podrobnější popis indicie

Po přidání či úpravě indicie je ihned v horní části obrazovky uveden náhled zvýrazněné pasáže. Tlačítkem *Náhled včetně indicií* je možné si e-mail prohlédnout včetně seznamu indicií a včetně personalizovaných proměnných vůči aktuálně přihlášenému uživateli.



### 2.4 Podvodné stránky

Sekce obsahuje seznam **všech podvodných stránek**, na které se mohou uživatelé dostat skrz odkazy v rozesílaných podvodných e-mailech.


#### 2.4.1 Přidání nebo úprava podvodné stránky

Vstup na podvodné stránky je omezen systémem, který je nechává přístupné pouze **po dobu běhu kampaně** a zároveň **jen přes jedinečné odkazy**, jež jsou dostupné pouze pro příjemce dané kampaně, popř. administrátorům/správcům testů pro případný **náhled** (všichni ostatní příchozí budou bez znalosti konkrétního odkazu automaticky přesměrováni na úvodní stránku projektu Phishingator).

Postup pro založení nové podvodné stránky je následující:

* u podvodné domény (popř. subdomény), na které bude provozována podvodná stránka, přidat v DNS záznam typu A, který bude nasměrován na IP adresu, kde běží aplikace Phishingator
* přidat podvodnou stránku v sekci *Podvodné stránky* (v adresáři nastaveném v konfiguračním souboru aplikace – konstanta `PHISHING_WEBSITE_APACHE_SITES_DIR` – dojte k vytvoření nového konfiguračního souboru pro podvodnou stránku, který je určený pro webový server – ten dané podvodné stránce nastavuje konkrétní `DocumentRoot`, tedy adresář, kde je umístěna šablona (vzhled) podvodné stránky a další parametry požadované pro svázání stránky s aplikací Phishingator), přičemž vstupní pole formuláře jsou následující:
  * **název** – slouží k identifikaci v systému
  * **URL** – URL adresa, která bude doplňována do podvodných e-mailů místo proměnné `%url%` a tedy URL adresa, jejíž konkrétní A záznam musí být v DNS směrován na IP adresu webového serveru, kde běží systém Phishingator
  * **šablona** – vzhled, který bude na dané podvodné stránce (přidání další šablony je popsáno v kapitole [Přidání nové šablony podvodné stránky](#244-pidn-nov-ablony-podvodn-strnky))
  * **aktivovat podvodnou stránku na webovém serveru** – nastavení, zdali má dojít k aktivaci podvodné stránky na webovém serveru (v Apache), čímž bude umožněno podvodnou stránku využívat v kampaních (aktivace nových nebo deaktivace neaktivních/smazaných podvodných stránek probíhá automaticky do 5 min.)

Po těchto krocích systém automaticky nad podvodnou stránkou (resp. konkrétní doménou/subdoménou) převezme kontrolu.


#### 2.4.2 Kontrola funkčnosti podvodné stránky

* využít možnosti náhledu podvodné stránky (v sekci *Podvodné stránky*), a ověřit tak její funkčnost
* při pokusu o přístup na URL adresu podvodné stránky (bez jakýchkoliv parametrů), musí automaticky dojít k přesměrování na úvodní stránku aplikace Phishingator
* voláním příkazu `apache2ctl -S` v terminálu lze získat informace o všech aktivních `VirtualHost` v Apache (tedy o všech podvodných stránkách) s tím, že výsledkem voláním příkazu je i informace o tom, zdali daná podvodná stránka běží na protokolu HTTP (80), nebo HTTPS (443)


#### 2.4.3 Odstranění podvodné stránky

* smazat stránku v sekci *Podvodné stránky*

Do 5 min. bude stránka automaticky deaktivována v nastavení webového serveru Apache.


#### 2.4.4 Přidání nové šablony podvodné stránky

Aby systém zachytával data zadaná do formuláře na podvodné stránce, je nutné, aby formulář splňoval následující podmínky:

* formulář (HTML tag `<form>`) musí mít jako metodu odesílání nastaveno `method="post"` (povoleny jsou pouze POST požadavky)
* vstupní pole pro zadání uživatelského jména musí obsahovat atribut `name="username"`
* vstupní pole pro zadání hesla musí obsahovat atribut `name="password"`
* ve formuláři musí existovat tlačítko obsahující atribut `type="submit"` sloužící pro odeslání formuláře (obvykle v rámci HTML tagu `<input>` nebo `<button>`)

Informace o nové šabloně (především lokaci zdrojových souborů na webovém serveru) je poté nutné manuálně přidat do databázové tabulky `phg_websites_templates` a všechny její soubory umístit do nového adresáře v lokaci nastavené v `PHISHING_WEBSITE_APACHE_SITES_DIR` (ve výchozím stavu `/templates/websites`).



### 2.5 Uživatelé

Umožňuje prohlížet **seznam všech uživatelů** evidovaných ve Phishingatoru.

Uživatelé se do Phishingatoru mohou přihlásit buď dobrovolně (průchodem přes SSO), případně tak, že je zaregistruje administrátor tím, že budou uvedeni mezi adresáty v některé z kampaní. Poslední možností je manuální přidání uživatele (viz podkapitola [Přidání nebo úprava uživatele](#251-pidn-nebo-prava-uivatele)).

Mezi všemi uživateli lze vyhledávat (pozn. vyhledává se ve sloupci *e-mail*) a dále filtrovat. Seznam dále administrátorovi zobrazuje, kolik cvičných phishingových e-mailů každý z uživatelů obdržel, jeho případný limit a také to, zdali se přihlásil k odebírání cvičných phishingových zpráv a kdy konkrétně.


#### 2.5.1 Přidání nebo úprava uživatele

Pro registraci uživatele do systému není třeba jej předem vytvářet na základě tohoto postupu. Využití tohoto postupu pro přidání nového uživatele dává smysl jen tehdy, pokud chce *administrátor* přidělit vyšší práva konkrétnímu uživateli dříve, než se do systému sám přihlásí, všechny ostatní případy řeší systém automaticky (například registrace příjemců do kampaně, kteří zatím nemají žádný záznam v systému). Vstupní pole pro přidání nebo úpravu uživatele jsou následující:

* **e-mail** – e-mail uživatele, na který budou zasílány cvičné phishingové e-maily a zároveň e-mail, který je uveden u daného uživatele v LDAP
* **skupina** – uživatelská skupina, na základě které uživatel získá oprávnění v systému, a to buď oprávnění *uživatel*, *správce testů* nebo *administrátor*



### 2.6 Uživatelské skupiny

Obsahuje **seznam všech uživatelských skupin**, na základě kterých uživatelé získávají **oprávnění v systému**.

Tři základní (rodičovské) skupiny nelze smazat a slouží jako záloha pro uživatele, kterým bude smazána jejich původní skupina (tzn. pokud bude uživatel členem skupiny, jejíž oprávnění je *uživatel* a *administrátor* tuto skupinu smaže, dojde k přesunu všech uživatelů mazané skupiny do rodičovské skupiny se stejným oprávněním).


#### 2.6.1 Přidání nebo úprava uživatelské skupiny

Formulář pro přidání nebo úpravu skupiny obsahuje následující vstupní pole:

* **název** – slouží k identifikaci v systému
* **popis** – nepovinný popis skupiny
* **oprávnění** – oprávnění, které budou mít všichni uživatelé této skupiny (výběr mezi *uživatel*, *správce testů*, přičemž možnosti, která každá z těchto skupin nabízí, lze zjistit na základě přepnutí role – viz podkapitola [Změna role](#21-zmna-role))
* **zobrazené LDAP skupiny příjemců v kampaních** – seznam názvů LDAP skupin (oddělených znakem `;`), které budou *administrátorovi* či *správci testů* zobrazovány při výběrů příjemců kampaně
  * *Poznámka:*
    * neznamená to, že tvůrce kampaně nebude moct vybrat jiné příjemce (může vybrat, resp. manuálně zadat kohokoliv i z jiných LDAP skupin, akorát mu takové skupiny nebudou zobrazovány v dialogovém okně při výběrů příjemců)
    * jedná se pouze o seznam uživatelů (resp. LDAP skupin, ve kterých jsou zařazeni), aby tvůrce kampaně mohl intuitivně vybrat příjemce z konkrétního pracoviště, oddělení, fakulty apod.


### 2.7 Konfigurace systému

Řadu možností systému (jako např. parametry pro připojení k databázi, LDAP, výchozí nastavení uživatelů k dobrovolnému odebírání cvičných phishingových zpráv apod.) lze konfigurovat v rámci souboru `config.php`. Vzhledem k tomu, že je detailně komentován, stejně jako zdrojový kód aplikace, nebudou zde jednotlivé možnosti popisovány.


### 2.8 Možné problémy

#### 2.8.1 Vyčištění fronty neodeslaných e-mailů

Poštovní server pravděpodobně **zamítne** odeslání e-mailů, u kterých se nepodaří resolve adresy (domény, subdomény, ...), která je uvedena v poli odesílatele. Takové e-maily tedy nebudou odeslány, i když se ve Phishingatoru tváří, že odeslány byly (chybová hláška `Sender address rejected: Domain not found (in reply to RCPT TO command)` v souboru `/var/log/mail.log` a plná fronta napoví, že tomu tak není).
Následně je třeba ručně smazat neodeslané e-maily z fronty a kampaň smazat (aby se ve frontě znovu neobjevily), postup kroků je tedy následující:

1. přihlásit se na server Phishingatoru
2. zadáním příkazu `mailq` zjistit, kolik e-mailů nebylo odesláno (výstup by neměl být prázdný)
3. zadáním příkazu `postsuper -d ALL` provést smazání všech neodeslaných e-mailů z fronty
4. znovu zadat příkaz `mailq` – tentokrát by měl být výstup prázdný
5. smazat problémovou kampaň ve Phishingatoru
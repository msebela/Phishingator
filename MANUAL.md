# Phishingator – Uživatelská příručka

## O aplikaci

Phishingator je webová aplikace, jejímž cílem je provádět **praktické školení uživatelů** v oblasti **phishingu a sociálního inženýrství**, a to odesíláním cvičných phishingových e-mailů, které obsahují typické znaky phishingu (podvodného jednání). Podrobnější popis aplikace je uveden v souboru [README.md](README.md).

Phishingator původně vznikl na [Západočeské univerzitě v Plzni](https://www.zcu.cz) (ZČU) v roce 2019, a to jako výsledek bakalářské práce [Systém pro rozesílání cvičných phishingových zpráv](https://theses.cz/id/0kk18p/), jejímž autorem je Martin Šebela a vedoucím pak Aleš Padrta. Phishingator byl následně dále rozvíjen v [Centru informatizace a výpočetní techniky](https://civ.zcu.cz) na ZČU.

Samotná uživatelská příručka je rozdělena na:

1. [příručku pro běžné uživatele](#1-příručka-pro-uživatele) Phishingatoru,
2. [příručku pro administrátory](#2-příručka-pro-administrátory), kteří budou ve Phishingatoru vytvářet nové phishingové kampaně, cvičné phishingové e-maily a podvodné stránky,
3. [správu Phishingatoru](#3-správa-phishingatoru) s detaily o nasazení, zálohování a další správě.

## 1 Příručka pro uživatele


### 1.1 Úvodní stránka

Obsahuje **osobní statistiku** uživatele uvádějící počet cvičných phishingových zpráv, které byly uživateli odeslány a stejně tak **osobní úspěšnost v odhalování phishingu**, kterou si lze prohlédnout i na příslušném grafu (pokud jsou již k dispozici nějaká relevantní data).


### 1.2 Moje účast v programu

Uživatel si po přihlášení do systému může zvolit, zdali chce **dobrovolně přijímat cvičné phishingové e-maily**. Spolu s tím si může uživatel nastavit **limit** (resp. maximální počet) cvičných phishingových e-mailů, které chce ještě **do budoucna přijmout**. Svou účast v programu má uživatel možnost **kdykoliv zrušit**.


### 1.3 Přijaté phishingové e-maily

V této sekci má uživatel možnost si **prohlédnout seznam všech cvičných phishingových e-mailů**, které mu byly v minulosti doručeny.

U každého e-mailu navíc vidí i **svou reakci**, tedy to, jakým způsobem na daný podvodný e-mail reagoval (například zdali navštívil podvodnou stránku přístupnou z tohoto e-mailu a zadal do formuláře platné přihlašovací údaje apod.) či nereagoval.

U podvodného e-mailu je také vždy odkaz na **vzdělávací stránku**, na níž je kromě původního e-mailu uveden i **seznam indicií** (vyznačené pasáže v textu s popisem, co konkrétně vyvolává podezření), na základě kterých může uživatel **zpětně zjistit, zdali postupoval správně** nebo čeho by si měl do **budoucna všímat**.


## 2 Příručka pro administrátory

*Administrátor* je nejvyšší oprávnění, které je ve Phishingatoru k dispozici. Obvykle má toto oprávnění zaměstnanec CERT/CSIRT týmu.


### 2.1 Změna role

Systém umožňuje uživatelům s vyšším oprávněním (tedy minimálně s oprávněním *správce testů*) přepínat mezi všemi ostatními, nižšími rolemi. Pro změnu role stačí ve Phishingatoru kliknout na název aktuálně vybrané role (příp. na tlačítko *Role*) v pravé horní části obrazovky.


#### 2.1.1 Úvodní stránka

Obsahuje **grafy** znázorňující **průběžnou úspěšnost všech phishingových kampaní**. Spolu s tím je k dispozici i další graf, který stejná data převádí do podoby sloupců symbolizujících **skupinu**, do které uživatelé (resp. příjemci) spadají (např. oddělení). Dále je zde pak k dispozici i další graf znázorňující **počet dobrovolníků napříč skupinami** (uživatelé, kteří se [dobrovolně přihlásili](#12-moje-účast-v-programu) k odebírání cvičného phishingu).


### 2.2 Kampaně

Umožňuje **přidávat nové a upravovat jakékoliv existující kampaně**. U již běžících a ukončených kampaní je možnost si prohlédnout **statistiku nebo průběžné výsledky** a **seznam všech akcí**, které uživatelé provedli na podvodných webových stránkách.


#### 2.2.1 Seznam kampaní

Obsahuje stručný přehled o všech přidaných kampaních. Data (sloupec *Aktivní od* a *Aktivní do*) svým barevným podbarvením znázorňují, zdali datum již proběhlo či nikoliv, a to následujícím způsobem:
* **zelené podbarvení** – datum ještě nenastalo (tzn. start kampaně ještě neproběhl nebo kampaň stále běží)
* **šedé podbarvení** – datum již nastalo (tzn. start kampaně již proběhl nebo byla kampaň již ukončena)
* **žluté podbarvení** – poslední den, během kterého bude kampaň aktivní


#### 2.2.2 Přidání nebo úprava kampaně

Vstupní pole, která se musí při vytváření nebo během upravování kampaně vyplnit, jsou následující:

* **název** – slouží pouze pro vlastní pojmenování kampaně
* **číslo lístku s kampaní** – nepovinný údaj, který reprezentuje číslo lístku (ticketu) v RT systému s evidencí o realizaci phishingové kampaně
* **rozesílaný podvodný e-mail** – cvičný phishingový e-mail, který bude zvoleným příjemcům kampaně doručen do jejich e-mailových schránek
  * z tohoto e-mailu se budou příjemci moci dostat na podvodnou stránku (viz následující bod)
* **podvodná webová stránka přístupná z e-mailu** – stránka, která bude přístupná z odkazu umístěného ve cvičném podvodném e-mailu a na které je umístěn formulář pro sbírání přihlašovacích (či jiných) údajů
* **akce po odeslání formuláře** – akce, která nastane na straně uživatele po odeslání formuláře na podvodné webové stránce
* **spustit rozesílání e-mailů v čase** – čas, ve kterém se začnou v den startu kampaně rozesílat cvičné phishingové e-maily
  * příjemcům dodatečně přidaným po tomto čase bude podvodný e-mail rozeslán následující den ve stejný čas
* **start kampaně** – datum spuštění kampaně (den, kdy se začnou rozesílat cvičné phishingové e-maily a den, od kterého začne být vybraným příjemcům dostupná podvodná stránka)
* **ukončení kampaně (včetně)** – datum ukončení kampaně (den, kdy přestanou fungovat odkazy vedoucí na podvodnou stránku přístupnou z cvičných phishingových e-mailů a tedy den, kdy skončí zaznamenávání jakékoliv aktivity na této podvodné stránce)
* **seznam účastníků kampaně** – příjemci zasílaného cvičného podvodného e-mailu a zároveň jediní uživatelé, kteří budou mít přes vlastní a jedinečný odkaz přístup na zvolenou podvodnou stránku
  * výběr příjemců lze provádět následovně (možnosti lze různě kombinovat):
    * **manuálně** vypsáním e-mailových adres,
    * **importem** z vybraného TXT/CSV souboru (tlačítko *Importovat příjemce*),
    * nebo **interaktivním výběrem** (tlačítko *Vybrat příjemce*).



### 2.3 Podvodné e-maily a indicie

Obsahuje seznam všech přidaných podvodných e-mailů. Ke každému podvodnému e-mailu lze po jeho přidání vložit indicie, na základě kterých mohl uživatel phishingový e-mail rozpoznat.


#### 2.3.1 Přidání nebo úprava podvodného e-mailu

Vstupní pole při vytváření nebo úpravě podvodného e-mailu jsou následující:

* **název** – slouží pouze pro vlastní pojmenování e-mailu
* **jméno odesílatele** – nepovinný údaj, který v e-mailových klientech doplňuje e-mail odesílatele
  * při nevyplnění bude v odeslaném podvodném e-mailu vidět pouze e-mail odesílatele
* **e-mail odesílatele** - umožňuje definovat e-mail, ze kterého budou odesílány podvodné e-maily, případně použít proměnnou `%recipient_email%`, místo které dojde ke vložení e-mailu příjemce (tzn. e-mail odesílatele i příjemce bude stejný)
* **předmět**
* **tělo** – obsah e-mailu, v němž je možné používat proměnné, které budou při odeslání podvodného e-mailu nahrazeny skutečným (resp. personalizovaným) obsahem
  * pro vložení proměnné do těla e-mailu stačí kliknout na její název (vedle vstupního pole)
  * vždy je nutné v těle e-mailu použít proměnnou `%url%`
  * **seznam proměnných**:
    * `%recipient_username%` – uživatelské jméno příjemce
    * `%recipient_email%` – e-mail příjemce
    * `%date_cz%` – datum, ve kterém dochází k odeslání e-mailu v českém formátu (např. 1. 9. 2022)
    * `%date_en%` – datum, ve kterém dochází k odeslání e-mailu ve formátu `YYYY-MM-DD` (např. 2022-09-01)
    * `%url%` – URL podvodné stránky svázané s e-mailem


#### 2.3.2 Přidání nebo úprava indicií u podvodného e-mailu

Pro přidání indicií k phishingovému e-mailu stačí v souhrnném seznamu všech e-mailů kliknout na tlačítko *Nastavit indicie* (pozn. číslo u tlačítka udává počet již přidaných indicií). Následuje zobrazení náhledu přidaného podvodného e-mailu a formuláře pro přidání nových či úpravu dosud přidaných indicií. Vstupní pole jsou následující:

* **indicie (podezřelý řetězec)** – konkrétní pasáž v textu, která má být označena (resp. zakroužkována) a se kterou má být svázán popis indicie
  * pokud není cílem odkázat na text v těle e-mailu, ale na jinou část e-mailu (předmět, odesílatel), je možné použít následující **proměnné**:
    * `%sender_name%` – pro označení jména odesílatele e-mailu
    * `%sender_email%` – pro označení e-mailu odesílatele
    * `%subject%` – pro označení předmětu e-mailu
* **nadpis** – stručný nadpis indicie nebo název kategorie (např. podezřelé oslovení, překlepy apod.)
* **popis** – nepovinný údaj obsahující podrobnější popis indicie

Po přidání či úpravě indicie je v horní části obrazovky uveden náhled zvýrazněné pasáže přímo v e-mailu.

Tlačítkem *Náhled včetně indicií* je možné si e-mail prohlédnout včetně seznamu indicií a včetně personalizovaných proměnných vůči aktuálně přihlášenému uživateli.



### 2.4 Podvodné stránky

Sekce obsahuje seznam **všech podvodných stránek**, na které se mohou uživatelé dostat skrz odkazy v rozesílaných podvodných e-mailech.


#### 2.4.1 Přidání nebo úprava podvodné stránky

Vstup na podvodné stránky je omezen systémem, který je nechává přístupné pouze **po dobu běhu kampaně** a zároveň **jen přes jedinečné odkazy**, jež jsou dostupné pouze pro příjemce dané kampaně, popř. administrátorům/správcům testů pro případný **náhled** (všichni ostatní příchozí budou bez znalosti konkrétního odkazu automaticky přesměrováni na úvodní stránku projektu Phishingator).

Postup pro založení nové podvodné stránky je následující:

* u podvodné domény (popř. subdomény), na které bude provozována podvodná stránka, přidat v DNS záznam typu A, který bude směrovat na IP adresu, kde běží Phishingator
* přidat podvodnou stránku v sekci *Podvodné stránky*, přičemž vstupní pole formuláře jsou následující:
  * **název** – slouží pouze pro vlastní pojmenování podvodné stránky
  * **URL** – URL adresa, která bude doplňována do podvodných e-mailů místo proměnné `%url%`
    * pro zadanou (sub)doménu musí být v DNS směrován A záznam na IP adresu, kde běží systém Phishingator
    * v URL adrese se musí vyskytnout proměnná `%id%`, která identifikuje konkrétního uživatele na podvodné stránce
      * může být uvedena buď jako některý z GET parametrů (...`?%id%` / ...`&%id%`), nebo jako hodnota některého z parametrů (...`?par=%id%`)
  * **šablona** – vzhled, který bude na dané podvodné stránce
    * přidání další šablony je popsáno v kapitole [Přidání nové šablony podvodné stránky](#37-přidání-nové-šablony-podvodné-stránky)
  * **aktivovat podvodnou stránku na webovém serveru** – nastavení, zdali má dojít k aktivaci podvodné stránky na webovém serveru, čímž bude umožněno využívat podvodnou stránku v kampaních (aktivace nových nebo deaktivace neaktivních/smazaných podvodných stránek probíhá automaticky do 5 min.)

Po těchto krocích Phishingator automaticky nad podvodnou stránkou převezme kontrolu.


#### 2.4.2 Kontrola funkčnosti podvodné stránky

* využít možnosti náhledu podvodné stránky (v sekci *Podvodné stránky*), a ověřit tak její funkčnost
* při pokusu o přístup na URL adresu podvodné stránky (bez jakýchkoliv parametrů), musí automaticky dojít k přesměrování na úvodní stránku aplikace Phishingator
* voláním příkazu `apache2ctl -S` v terminálu lze získat informace o všech aktivních `VirtualHost` na webovém serveru v Apache (tedy o všech podvodných stránkách)


#### 2.4.3 Odstranění podvodné stránky

* smazat stránku v sekci *Podvodné stránky*

Do 5 min. bude podvodná stránka automaticky deaktivována v nastavení webového serveru.



### 2.5 Uživatelé

Umožňuje prohlížet **seznam všech uživatelů** evidovaných ve Phishingatoru.

Uživatelé se do Phishingatoru mohou přihlásit buď dobrovolně (průchodem přes SSO), případně tak, že je zaregistruje administrátor tím, že budou uvedeni mezi adresáty v některé z kampaní. Poslední možností je manuální přidání uživatele (viz podkapitola [Přidání nebo úprava uživatele](#251-přidání-nebo-úprava-uživatele)).

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

* **název** – slouží pouze pro vlastní pojmenování skupiny
* **popis** – nepovinný popis skupiny
* **oprávnění** – oprávnění, které budou mít všichni uživatelé této skupiny
  * výběr mezi *uživatel*, *správce testů*, přičemž možnosti, která každá z těchto skupin nabízí, lze zjistit na základě přepnutí role – viz podkapitola [Změna role](#21-změna-role)
* **zobrazené LDAP skupiny příjemců v kampaních** – seznam názvů LDAP skupin (oddělených znakem `;`), které budou *administrátorovi* či *správci testů* zobrazovány při interaktivním výběrů příjemců ve phishingové kampani
  * neznamená to, že tvůrce kampaně nebude moct vybrat jiné příjemce (může vybrat, resp. manuálně zadat kohokoliv i z jiných LDAP skupin, akorát mu takové skupiny nebudou zobrazovány v dialogovém okně při interaktivním výběru příjemců)
  * jedná se pouze o seznam uživatelů (resp. LDAP skupin, ve kterých jsou zařazeni), aby tvůrce kampaně mohl intuitivně vybrat příjemce z konkrétního pracoviště, oddělení, fakulty apod.


## 3 Správa Phishingatoru


### 3.1 Konfigurace Phishingatoru

Řadu možností Phishingatoru (jako např. parametry pro připojení k databázi a k LDAP, rozmazání identit uživatelů ve statistikách kampaně, způsob anonymizace hesel z podvodných stránek apod.) lze nastavit v souboru [`config.php`](src/config.php), resp. pomocí **Environment variables** (`.env`), ze kterých `config.php` čerpá. Možnosti konfigurace jsou popsány v komentářích v souboru `config.php`.

Základní konfigurace, ze které lze vycházet, je umístěna v souboru [`.env.dist`](.env.dist). Tuto konfiguraci je nutné před spuštěním Phishingatoru doplnit a následně uložit pod názvem `.env`.


### 3.2 Build Phishingatoru

Před buildem Phishingatoru je nejprve nutné vytvořit [konfiguraci Phishingatoru](#31-konfigurace-phishingatoru) (soubor `.env`) na základě prostředí organizace.


#### 3.2.1 Testovací instance

Testovací instanci Phishingatoru lze spustit voláním skriptu `build-dev.sh`, a to například následovně:

```
./scripts/build-dev.sh
```

Při testování na lokálním prostředí je Phishingator po úspěšném vykonání skriptu dostupný na URL adrese <http://localhost:8000>.


#### 3.2.2 Ostrá instance

Před nasazením ostré verze je nutné nainstalovat a nakonfigurovat proxy (např. Traefik), která bude požadavky cílené na podvodné domény předávat do Phishingatoru.

Samotný build ostré verze pak zajišťuje soubor `build.sh`:

```
./scripts/build.sh
```


### 3.3 Data Phishingatoru

Veškerá data z Phishingatoru se na hostitelském serveru ukládají do adresáře `/phishingator-data/`, kde jsou dále členěna do jednotlivých podadresářů následovně:

- `database` – data databáze,
- `database-dumps` – mysqldump soubory,
- `logs` – logy,
- `websites-templates`
  - `sites-config` – konfigurační soubory (Apache VirtualHost) podvodných stránek,
  - `websites` – šablony podvodných stránek.


### 3.4 Zálohování databáze

Data z databáze Phishingatoru lze zálohovat pomocí skriptu [`backup-db.sh`](scripts/backup-db.sh), který vytvoří mysqldump databáze pro zvolenou instanci (organizaci). Je nutné, aby při zálohování běžel databázový kontejner `phishingator-<organizace>-database`.

Soubor se zálohou se vytvoří v adresáři `/phishingator-data/<organizace>/database-dumps`.

Příklad volání:

```
./scripts/backup-db.sh cesnet
```


### 3.5 Obnovení databáze

Data do databáze Phishingatoru lze importovat pomocí skriptu [`restore-db.sh`](scripts/restore-db.sh), který importuje strukturu a data z mysqldump souboru pro zvolenou instanci (organizaci). Je nutné, aby při obnovení databáze běžel databázový kontejner `phishingator-<organizace>-database`. 

Skript očekává, že mysqldump soubor (ve formátu `sql.gz`) je umístěn v adresáři `/phishingator-data/<organizace>/database-dumps/`.

Příklad volání:

```
./scripts/restore-db.sh cesnet 2023-01-17-09-55-25.sql.gz
```


### 3.6 Odstranění databáze

Databázové tabulky společně s uloženými daty lze smazat pomocí skriptu [`reset-db.sh`](scripts/reset-db.sh). Skript smaže obsah adresáře `/phishingator-data/<organizace>/database/phishingator/`.

Příklad volání:

```
./scripts/reset-db.sh cesnet
```

Při vypnutí Phishingatoru, smazání všech podadresářů v adresáři `/phishingator-data/<organizace>/database/` a opětovném spuštění Phishingatoru, dojde k vytvoření struktury databáze a k importu základních dat.


### 3.7 Přidání nové šablony podvodné stránky

Novou šablonu podvodné stránky lze do Phishingatoru přidat pomocí skriptu [`add-website-template.sh`](scripts/add-website-template.sh). Je nutné, aby při přidávání nové šablony běžel databázový kontejner `phishingator-<organizace>-database`.

Aby Phishingator zachytával data zadaná do formuláře na podvodné stránce, musí šablona podvodné stránky splňovat následující podmínky:

* vstupním souborem bude soubor `index.php` (může obsahovat přesměrování na jiný soubor)
* formulář (HTML tag `<form>`) musí mít jako metodu odesílání nastaveno `method="post"` (povoleny jsou pouze POST požadavky)
  * vstupní pole pro zadání uživatelského jména musí obsahovat atribut `name="username"`
  * vstupní pole pro zadání hesla musí obsahovat atribut `name="password"`
  * ve formuláři musí existovat tlačítko obsahující atribut `type="submit"` sloužící pro odeslání formuláře (obvykle uvnitř HTML tagu `<input>` nebo `<button>`)
* pokud má podvodná stránka umožňovat zobrazení chybové hlášky (např. po zadání neplatného jména a hesla), musí být v souboru uvedena podmínka `<?php if ($message): ?> (...) <text chybové hlášky> (...) <?php endif; ?>`, která bude obalovat samotnou chybovou hlášku
  * chybová hláška se zobrazí pouze tehdy, pokud administrátor během vytváření kampaně nastaví jako *akci po odeslání formuláře* tu, která obsahuje text *zobrazit chybovou hlášku*


#### Proměnné

Ve zdrojovém kódu podvodné stránky lze používat následující proměnné, které Phishingator při přístupu na podvodnou stránku automaticky předpřipraví a naplní obsahem:

| Proměnná    | Datový typ | Význam                                                                                                                                                                      |
|-------------|------------|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$username` | string     | Uživatelské jméno uživatele, který na podvodnou stránku přistoupil                                                                                                          |
| `$email`    | string     | E-mail uživatele, který na podvodnou stránku přistoupil                                                                                                                     |
| `$service`  | string     | Název služby, ke které se uživatel přihlašuje (může se jednat o prázdný řetězec – hodnotu nastavuje administrátor při vytváření podvodné stránky přímo v GUI Phishingatoru) |
| `$message`  | bool       | TRUE, pokud se má zobrazit na podvodné stránce chybová hláška (došlo k zadání neplatného jména a hesla), jinak FALSE                                                        |


#### Příklad zdrojového kódu

Příklad části zdrojového kódu podvodné šablony včetně použití proměnných:

```
(...)
<h1>Login</h1>

<?php if ($service): ?>
  <p>Please enter your credentials to access <?= $service ?>.</p>
<?php endif; ?>

<?php if ($message): ?>
  <p>Invalid username or password.</p>
<?php endif; ?>

<form method="post">
  <label for="username">Username</label>
  <input type="text" name="username" id="username">

  <label for="password">Password</label>
  <input type="password" name="password" id="password">

  <button type="submit">Login</button>
</form>
(...)
```


#### Adresářová struktura

Součástí souborů podvodné šablony musí být její screenshot pojmenovaný `thumbnail.png` uložený v kořenovém adresáři šablony. Screenshot by měl být široký přesně `800 px` a na výšku by měl zabírat celý formulář. Screenshot je použit při zobrazení indicií na vzdělávací stránce.

Typicky bude obsah adresáře podvodné šablony vypadat následovně:

```
.
├── images/
│   └── logo.svg
├── index.php
├── style.css
└── thumbnail.png
```


#### Aktivace podvodné šablony

Aktivovat šablonu přímo v GUI Phishingatoru je možné pomocí skriptu [`add-website-template.sh`](scripts/add-website-template.sh), který záznam o nové šabloně vloží do databáze (do tabulky `phg_websites_templates`) a zkopíruje soubory podvodné stránky do dané instance Phishingatoru.

Příklad volání:

```
./scripts/add-website-template.sh cesnet "CESNET SSO" /root/cesnet-sso/ 1
```

### 3.8 Možné problémy


#### 3.8.1 Vyčištění fronty neodeslaných e-mailů

Poštovní server pravděpodobně **zamítne** odeslání e-mailů, u kterých se nepodaří resolve adresy (domény, subdomény, ...), která je uvedena v poli odesílatele. Takové e-maily tedy nebudou odeslány, i když se ve Phishingatoru tváří, že odeslány byly (chybová hláška `Sender address rejected: Domain not found (in reply to RCPT TO command)` v souboru `/var/log/mail.log` a plná fronta napoví, že tomu tak není).
Následně je třeba ručně smazat neodeslané e-maily z fronty a kampaň smazat (aby se ve frontě znovu neobjevily), postup kroků je tedy následující:

1. přihlásit se na server Phishingatoru
2. zadáním příkazu `mailq` zjistit, kolik e-mailů nebylo odesláno (výstup by neměl být prázdný)
3. zadáním příkazu `postsuper -d ALL` provést smazání všech neodeslaných e-mailů z fronty
4. znovu zadat příkaz `mailq` – tentokrát by měl být výstup prázdný
5. smazat problémovou kampaň ve Phishingatoru
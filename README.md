# Phishingator

###### Systém pro rozesílání cvičných phishingových zpráv


### Co je Phishingator?

Phishingator je webová aplikace, jejímž cílem je provádět **praktické školení uživatelů** v oblasti **phishingu a sociálního inženýrství**, a to odesíláním cvičných phishingových e-mailů.

Administrátor si ve Phishingatoru jednoduše vytvoří **cvičný phishingový e-mail** a s ním svázanou **cvičnou phishingovou stránku** (např. napodobující přihlášení do skutečného systému organizace). Phishingator pak ve zvolený den a čas odešle administrátorem **vybraným příjemcům** cvičný phishing. Administrátor následně může **v reálném čase sledovat**, jak **uživatelé** na cvičný phishing a podvodnou stránku **reagují**. Phishingator informuje, zda adresáti podvodnou stránku navštívili, zda vyplnili a odeslali přihlašovací údaje a pokud ano, pak také zda jsou přihlašovací údaje platné či nikoliv.

Pokud uživatel do cvičné phishingové stránky předá své **přihlašovací údaje**, je mu obratem zobrazena informační stránka s původně odeslaným phishingem, a to včetně **vyznačených indicií**, na základě kterých bylo možné podvod rozpoznat. Uživatel se tak má šanci ihned **poučit** a zjistit, jak mohl daný phishing rozpoznat tak, aby podobnému nebo dokonce skutečnému phishingu příště odolal. Stejné indicie jsou zobrazeny i všem ostatním uživatelům po ukončení školení.

Phishingator byl navržen jako co nejvíce **intuitivní a automatizovaný systém** tak, aby jeho používání nevyžadovalo téměř **žádné technické znalosti**. Součástí systému je vedení jak **globální**, tak **osobní statistiky** u každého z uživatelů, a také vedení **podrobné statistiky** u každé phishingové kampaně. Phishingator lze jednoduše napojit na již **existující SSO** (např. *OIDC*).



### Klíčové vlastnosti

- **Vytvoření cvičné phishingové kampaně** (školení)
  - Jednoduchý formulář s vyplněním _"komu, kdy, v kolik, jaký phishing a jaká phishingová stránka"_
  - Způsob vkládání příjemců
    - Dobrovolná registrace uživatelů přihlášením do Phishingatoru
    - Výběr administrátorem systému
      - Manuálním vložením seznamu uživatelů
      - Interaktivním výběrem z LDAP
  - Předpřipravené šablony podvodných stránek
- **Průběh phishingové kampaně**
  - Rozeslání phishingových e-mailů, notifikací, vedení a ukončení kampaně automaticky zajišťuje Phishingator
  - Administrátor vidí reakce uživatelů
  - Informační stránka s vysvětlením a zobrazením indicií, na základě kterých bylo možné phishing rozpoznat
    - Obratem po vyplnění údajů na podvodné stránce (uživatel se má šanci ihned poučit)
- **Statistiky**
  - Podrobné statistiky u každé phishingové kampaně
  - Osobní a globální statistiky za celou organizaci
- **Modulární systém**
  - Jednoduché přidání nového podvodného e-mailu a podvodné stránky
  - Napojení na různé autentizační systémy pro ověření platnosti jména a hesla zadaného na cvičné podvodné stránce
    - Kerberos, LDAP, IMAP, případně další
- **Intuitivní**, téměř automatizovaný systém **vyžadující minimální obsluhu**
  - Optimalizováno pro mobilní zařízení
  - Živý vývoj



### Způsob nasazení

Phishingator **Vám můžeme nasadit** a pomoct s jeho **ovládáním a nastavením**, případně za Vás **můžeme realizovat** i cvičné **phishingové kampaně**, nebo si můžete Phishingator **nasadit sami** díky dostupným zdrojovým kódům. Všechny nabízené verze Phishingatoru obsahují stejné funkce (žádná z verzí není ochuzena).

**Možnosti konzultací**, **správy systému** ze strany CESNETu a **školení** pak ukazuje následující _tabulka_:


|                                               | Samostatný provoz | Phishingator+ | Phishingator++ | Phishingator Gold |
|-----------------------------------------------|:-----------------:|:-------------:|:--------------:|:-----------------:|
| Dostupnost zdrojových kódů                    |     &#10003;      |   &#10003;    |    &#10003;    |     &#10003;      |
| Instanci provozuje CESNET                     |                   |   &#10003;    |    &#10003;    |     &#10003;      |
|                                               |                   |               |                |                   |
| Konzultace technických problémů               |     &#10003;      |   &#10003;    |    &#10003;    |     &#10003;      |
| Konzultace s napojením na autentizační systém |                   |   &#10003;    |    &#10003;    |     &#10003;      |
| Vytvoření nových podvodných stránek __*__     |                   |   &#10003;    |    &#10003;    |     &#10003;      |
| Příprava phishingových kampaní __*__          |                   |               |    &#10003;    |     &#10003;      |
| Plánování testování uživatelů                 |                   |               |                |     &#10003;      |
|                                               |                   |               |                |                   |
| Školení administrátorů systému                |                   |   &#10003;    |    &#10003;    |     &#10003;      |
| Školení uživatelů                             |                   |               |    &#10003;    |     &#10003;      |

__*__ _Počet limitován dle dohody._



### Mám zájem o službu

Pokud **máte zájem o zprovoznění** Phishingatoru ve Vaší organizaci, **napište nám**, prosím, na e-mail _phishingator@cesnet.cz_. Následně spolu domluvíme **technické detaily** a **způsob nasazení** Phishingatoru.


#### Demo aplikace

Pokud nad službou váháte a chtěli byste si ji nejprve **nezávazně vyzkoušet**, můžete se do **ukázkové verze Phishingatoru** přihlásit přes stránku uvedenou níže v odkazech.

V ukázkové verzi Phishingatoru jsou ve výchozím stavu k dispozici **práva administrátora** – celý systém si tak můžete proklikat včetně několika **testovacích phishingových kampaní**, reakcí uživatelů a i samotných podvodných e-mailů a podvodných stránek.

*Pozn.:* Demo z praktických důvodů **neumožňuje odesílat e-maily**.



### Odkazy

- Demo Phishingatoru – **TODO**
- [Zdrojové kódy Phishingatoru](/src)
- [Uživatelská příručka](MANUAL.md)
- [Ukázkové screenshoty aplikace](/doc)



### O aplikaci

Phishingator původně vznikl na ZČU jako výsledek bakalářské práce [Systém pro rozesílání cvičných phishingových zpráv](https://theses.cz/id/0kk18p/), jejímž autorem je Martin Šebela a vedoucím pak Aleš Padrta.


#### Kontakt na vývojáře

- phishingator@cesnet.cz
- martin.sebela@cesnet.cz
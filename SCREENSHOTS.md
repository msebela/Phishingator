# Phishingator – Screenshoty aplikace

U jednotlivých screenshotů z Phishingatoru je vždy uveden krátký popis.



## 1. Vytvoření podvodného e-mailu

Ve Phishingatoru se cvičné podvodné e-maily vytvářejí podobně jako v e-mailovém klientovi – tj. stačí vyplnit **jméno odesílatele**, jeho **e-mail** a samozřejmě **obsah zasílané zprávy**.

Cvičný phishing lze navíc **personalizovat vůči konkrétnému příjemci**, a to díky proměnným (na screenshotu vyznačeny žlutým podbarvením), které budou při odeslání e-mailu nahrazeny skutečným obsahem (např. uživatelské jméno příjemce, e-mail, ...).

![Vytvoření podvodného e-mailu](doc/images/01-phishing-training-email.png)


## 2. Přidání indicií k podvodnému e-mailu

K vytvořenému podvodnému e-mailu se následně přidávají tzv. **indicie**, na základě kterých bylo možné podvodné jednání (phishing) rozpoznat. Indicie (resp. označené pasáže v obsahu e-mailu) jsou uživateli obratem zobrazeny při **podlehnutí phishingu**, případně **po ukončení phishingové kampaně** (viz screenshot č. 7).

![Přidání indicií k podvodnému e-mailu](doc/images/02-phishing-training-email-signs.png)



## 3. Vytvoření podvodné stránky

S podvodným e-mailem je spjata podvodná stránka, která se snaží příjemce přimět k vyplnění přihlašovacích údajů.

Do Phishingatoru lze vkládat **libovolné šablony podvodných stránek** (jednoduché HTML a CSS) a poté pouze upravit **DNS u (sub)domény**, která bude sloužit jako hostitel cvičné podvodné stránky.

![Vytváření podvodné stránky](doc/images/03-fraudulent-website-template.png)



## 4. Vytvoření phishingové kampaně

Cvičný podvodný e-mail a cvičná podvodná stránka je pak součástí tzv. **phishingové kampaně**, která je určena **konkrétním příjemcům** a trvá po **stanovenou dobu**. Administrátor zároveň určuje, k jaké akci má dojít **při vyplnění a odeslání formuláře** na podvodné stránce (například k zobrazení informační stránky s indiciemi).

![Vytvoření phishingové kampaně](doc/images/04-campaign.png)



## 5. Statistika kampaně

Po zahájení kampaně lze v **reálném čase sledovat**, jak uživatelé na cvičný phishing, potažmo podvodnou stránku **reagují** a zdali do formuláře na ni umístěné něco zadávají. **Zadané údaje** se obratem vyhodnocují (platnost přihlašovacích údajů vs. různé vzkazy pro útočníka apod.) a **promítají do grafů a tabulek**. 

![Statistika kampaně](doc/images/05-campaign-stats.png)



## 6. Statistika kampaně – reakce uživatelů

**Akce na podvodné stránce** včetně zadaných údajů, které provedli jednotliví příjemci, lze rovněž sledovat ve statistikách phishingové kampaně.

![Statistika kampaně – reakce uživatelů](doc/images/06-campaign-stats-user-reactions.png)



## 7. Zpětná vazba pro uživatele

Pokud příjemce cvičného phishingového e-mailu cokoliv vyplní do formuláře na podvodné stránce, je obratem přesměrován na **vzdělávací stránku** s původně odeslaným e-mailem a **indiciemi**, na základě kterých bylo možné phishing rozpoznat.

Uživatel je tak **nenásilně poučen**, jakým způsobem bylo možné phishing rozpoznat tak, aby příště podobnému pokusu (například tomu skutečnému) **nepodlehl**.

![Zpětná vazba pro uživatele](doc/images/07-campaign-stats-user-summary.png)
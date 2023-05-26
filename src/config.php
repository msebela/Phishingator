<?php
  /* --- NASTAVENÍ PHP DLE LOKALIZACE --- */
  /** Časová zóna, kde běží instance Phishingatoru. */
  define('PHP_TIME_ZONE', 'Europe/Prague');

  /** Multibyte kódování pro PHP funkce pracující s řetězci. */
  define('PHP_MULTIBYTE_ENCODING', 'utf-8');



  /* --- PŘIPOJENÍ K DATABÁZI --- */
  /** PDO řetězec sloužící pro připojení k databázi (včetně hostitele a názvu databáze). */
  define('DB_PDO_DSN', 'mysql:host=' . ((getenv('DB_HOST')) ? getenv('DB_HOST') : 'database') . ';dbname=' . ((getenv('DB_DATABASE')) ? getenv('DB_DATABASE') : 'phishingator'));

  /** Uživatelské jméno do databáze. */
  define('DB_USERNAME', getenv('DB_USERNAME'));

  /** Heslo do databáze. */
  define('DB_PASSWORD', getenv('DB_PASSWORD'));

  /** Kódování použité při práci s databází. */
  define('DB_ENCODING', 'utf8');



  /* --- PŘIPOJENÍ K LDAP --- */
  /** Hostitel LDAP. */
  define('LDAP_HOSTNAME', getenv('LDAP_HOSTNAME'));

  /** Port k připojení k LDAP. */
  define('LDAP_PORT', (getenv('LDAP_PORT')) ? getenv('LDAP_PORT') : 636);

  /** Uživatelské jméno pro přístup do LDAP. */
  define('LDAP_USERNAME', getenv('LDAP_USERNAME'));

  /** Heslo pro přístup do LDAP. */
  define('LDAP_PASSWORD', getenv('LDAP_PASSWORD'));

  /** Základní cesta v LDAP. */
  define('LDAP_BASE_DN', getenv('LDAP_BASE_DN'));

  /** Cesta k seznamu uživatelů v LDAP. */
  define('LDAP_USERS_DN', (getenv('LDAP_USERS_DN')) ? getenv('LDAP_USERS_DN') : 'ou=People');

  /** Název atributu v LDAP, ve kterém je uložen identifikátor uživatele (typicky "uid" / "samaccountname"). */
  define('LDAP_USER_ATTR_ID', (getenv('LDAP_USER_ATTR_ID')) ? getenv('LDAP_USER_ATTR_ID') : 'uid');

  /** Název atributu v LDAP, ve kterém je uloženo jméno a příjmení uživatele (typicky "displayName" / "cn"). */
  define('LDAP_USER_ATTR_NAME', (getenv('LDAP_USER_ATTR_NAME')) ? getenv('LDAP_USER_ATTR_NAME') : 'displayName');

  /** Název atributu v LDAP, ve kterém je uložen e-mail uživatele. */
  define('LDAP_USER_ATTR_EMAIL', (getenv('LDAP_USER_ATTR_EMAIL')) ? getenv('LDAP_USER_ATTR_EMAIL') : 'mail');

  /** Název atributu v LDAP, ze kterého lze získat název oddělení, do něhož je uživatel zařazen. */
  define('LDAP_USER_ATTR_PRIMARY_GROUP', (getenv('LDAP_USER_ATTR_PRIMARY_GROUP')) ? getenv('LDAP_USER_ATTR_PRIMARY_GROUP') : 'departmentnumber');

  /** Cesta k seznamu uživatelských skupin v LDAP. */
  define('LDAP_GROUPS_DN', (getenv('LDAP_GROUPS_DN')) ? getenv('LDAP_GROUPS_DN') : 'ou=Groups');

  /** Název atributu v LDAP, který obsahuje identifikátor uživatele patřícího do dané skupiny. */
  define('LDAP_GROUPS_ATTR_MEMBER', (getenv('LDAP_GROUPS_ATTR_MEMBER')) ? getenv('LDAP_GROUPS_ATTR_MEMBER') : 'member');

  /** Cesta k seznamu pracovišť v LDAP. */
  define('LDAP_DEPARTMENTS_DN', getenv('LDAP_DEPARTMENTS_DN'));

  /** Filtr pro získání nadřazených pracovišť v LDAP (např. fakulty). */
  define('LDAP_ROOT_DEPARTMENTS_FILTER_DN', getenv('LDAP_ROOT_DEPARTMENTS_FILTER_DN'));

  /** Zkratky pracovišť (odděleny čárkou), která sice spadají pod nadřazenější pracoviště, ale v grafech mají
   *  být zobrazeny jako samostatné jednotky (nikoliv jako součást nadřazeného pracoviště). */
  define('INDEPENDENT_DEPARTMENTS', getenv('INDEPENDENT_DEPARTMENTS'));



  /* --- PŘIPOJENÍ K SMTP SERVERU --- */
  /** Hostitel SMTP. */
  define('SMTP_HOST', getenv('SMTP_HOST'));

  /** Port k připojení k SMTP. */
  define('SMTP_PORT', getenv('SMTP_PORT'));

  /** Použít TLS při spojení. */
  define('SMTP_TLS', getenv('SMTP_TLS'));

  /** Uživatelské jméno k SMTP. */
  define('SMTP_USERNAME', getenv('SMTP_USERNAME'));

  /** Heslo k SMTP. */
  define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));



  /* --- OVĚŘENÍ PŘIHLAŠOVACÍ ÚDAJŮ ZADANÝCH NA PODVODNÝCH STRÁNKÁCH --- */
  /** Typ autentizace, který se bude používat při ověřování přihlašovací údajů:
   *    Možnosti: kerberos/ldap/imap
   */
  define('AUTHENTICATION_TYPE', getenv('AUTHENTICATION_TYPE'));

  /** Pokud se použije LDAP autentizace, je nutné uvést hostitele LDAP, vůči kterému se budou přihlašovací údaje z podvodných stránek ověřovat. */
  define('AUTHENTICATION_LDAP_HOST', (getenv('AUTHENTICATION_LDAP_HOST')) ? getenv('AUTHENTICATION_LDAP_HOST') : getenv('LDAP_HOSTNAME'));

  /** Port k připojení k autentizačnímu LDAP serveru. */
  define('AUTHENTICATION_LDAP_PORT', (getenv('AUTHENTICATION_LDAP_PORT')) ? getenv('AUTHENTICATION_LDAP_PORT') : getenv('LDAP_PORT'));

  /** Prefix automaticky přidávaný k uživatelskému jménu (např. "uid="). */
  define('AUTHENTICATION_LDAP_USER_PREFIX', getenv('AUTHENTICATION_LDAP_USER_PREFIX'));

  /** Sufix automaticky přidávaný k uživatelskému jménu (pokud již v uživatelském jménu není obsažen). */
  define('AUTHENTICATION_LDAP_USER_SUFFIX', getenv('AUTHENTICATION_LDAP_USER_SUFFIX'));

  /** Pokud se použije IMAP autentizace, je nutné uvést IMAP server (a port) a případné flagy (např. "{example.tld:993/imap/ssl/}"). */
  define('AUTHENTICATION_IMAP_ARGS', getenv('AUTHENTICATION_IMAP_ARGS'));



  /* --- LOGOVÁNÍ --- */
  /** Cesta a název souboru, do kterého se budou zapisovat všechny zaznamenané události. Pokud soubor nebude existovat,
      systém jej vytvoří. Záznamy se přidávají atomicky. */
  define('LOGGER_FILEPATH', '/var/www/phishingator/logs/log.log');

  /** Formát data, které se bude zapisovat u každé zaznamenané události do protokolu. */
  define('LOGGER_DATE_FORMAT', 'Y-m-d H:i:s');

  /** Úroveň události, od které se budou záznamy ukládat do protokolu, v pořadí: DEBUG < INFO < WARNING < ERROR. */
  define('LOGGER_LEVEL', 'DEBUG');



  /* --- TESTOVÁNÍ --- */
  /** Uživatelské jméno testovacího uživatele (např. sondy), který periodicky a automatizovaně (např. Selenium) testuje
   * funkčnost části aplikace (např. průchodem přes SSO přihlášení). Při vyplnění nebude docházet k logování přístupu
   * daného uživatele. */
  define('TEST_USERNAME', getenv('TEST_USERNAME'));

  /** Heslo testovacího uživatele. */
  define('TEST_PASSWORD', getenv('TEST_PASSWORD'));



  /* --- NASTAVENÍ WEBU --- */
  /** Verze Phishingatoru. */
  define('WEB_VERSION', '1.3');

  /** URL, na které Phishingator běží (slouží pro přesměrování v rámci systému). */
  define('WEB_URL', getenv('WEB_URL'));

  /** URL, která slouží jako úvodní stránka a rozcestník Phishingatoru. */
  define('WEB_BASE_URL', 'https://phishingator.cesnet.cz');



  /* --- SYSTÉM PRO SPRÁVU POŽADAVKŮ --- */
  /** URL obsahující parametr pro identifikátor do interního systému pro správu požadavků (např. Request Tracker),
   *  ve kterém mohou být evidovany phishingové kampaně.
   */
  define('ITS_URL', (getenv('ITS_URL')) ? getenv('ITS_URL') : 'https://rt.' . getenv('ORG_DOMAIN') . '/rt/Ticket/Display.html?id=');



  /* --- NOTIFIKACE --- */
  /** E-mail, který bude uveden jako odesílatel notifikací. */
  define('NOTIFICATION_SENDER', getenv('NOTIFICATION_SENDER'));



  /* --- VÝCHOZÍ HODNOTY NOVÝCH UŽIVATELŮ --- */
  /** Určuje, zdali je uživatel ve výchozím stavu po registraci přihlášen k odebírání cvičných
   *  phishingových zpráv (1 nebo 0).
   */
  define('NEW_USER_PARTICIPATION', 1);

  /** Určuje, zdali je uživatel ve výchozím stavu po nucené registraci v kampani (tzn. do systému se uživatel
   *  nepřihlásil sám, ale byl registrován někým jiným) přihlášen k odebírání cvičných phishingových zpráv (1 nebo 0).
   */
  define('NEW_USER_BY_CAMPAIGN_PARTICIPATION', 0);


  /** Výchozí limit počtu e-mailů, který mají uživatelé po registraci nastaven (pozn. jedná se o dobrovolný limit,
   *  tzn. pokud bude uživatel zapojen do kampaně někým, kdo má vyšší oprávnění, jeho limit počtu přijatých e-mailů
   *  nehraje roli.
   */
  define('NEW_USER_PARTICIPATION_EMAILS_LIMIT', 10);

  /** Výchozí limit počtu e-mailů, který mají uživatelé po nucené registraci (tzn. do systému se uživatel
   *  nepřihlásil sám, ale byl registrován někým jiným) nastaven (pozn. jedná se o dobrovolný limit, tzn. pokud bude
   *  uživatel zapojen do kampaně někým, kdo má vyšší oprávnění, jeho limit počtu přijatých e-mailů nehraje roli).
   */
  define('NEW_USER_BY_CAMPAIGN_PARTICIPATION_EMAILS_LIMIT', NULL);


  /** Výchozí skupina, která jsou uživatelům při registraci přidělena. */
  define('NEW_USER_DEFAULT_GROUP_ID', 3);

  /** Výchozí skupina, která jsou uživatelům při nucené registraci přidělena (tzn. do systému se uživatel
      nepřihlásil sám, ale byl registrován někým jiným). */
  define('NEW_USER_BY_CAMPAIGN_DEFAULT_GROUP_ID', 3);



  /* --- ADRESÁŘOVÁ STRUKTURA SYSTÉMU --- */
  /** Hlavní adresář, od kterého se budou odvíjet další adresáře. */
  define('CORE_DOCUMENT_ROOT', '/var/www/phishingator');

  /** Adresář obsahující soubory controllers (kontrolerů). */
  define('CORE_DIR_CONTROLLERS', 'core/controllers');

  /** Adresář obsahující soubory models (modelů). */
  define('CORE_DIR_MODELS', 'core/models');

  /** Adresář obsahující soubory views (pohledů). */
  define('CORE_DIR_VIEWS', 'core/views');

  /** Adresář obsahující další dodatečné knihovny. */
  define('CORE_DIR_EXTENSIONS', 'extensions');

  /** Adresář pro umístění dočasných (temp) souborů. */
  define('CORE_DIR_TEMP', '/tmp');

  /** Přípona view souborů. */
  define('CORE_VIEWS_FILE_EXTENSION', '.php');



  /* --- CROSS-SITE REQUEST FORGERY --- */
  /** Klíč přidávaný ke generovanému CSRF tokenu (libovolný, náhodný řetězec). */
  define('CSRF_KEY', (getenv('CSRF_KEY')) ? getenv('CSRF_KEY') : base64_encode(openssl_random_pseudo_bytes(32)));



  /* --- EXTERNÍ PŘÍSTUP --- */
  /** Token nutný pro externí přístup (libovolný, náhodný řetězec). */
  define('PHISHINGATOR_TOKEN', (getenv('PHISHINGATOR_TOKEN')) ? getenv('PHISHINGATOR_TOKEN') : base64_encode(openssl_random_pseudo_bytes(32)));

  /** Lokální IP adresa, které je jako jediné povoleno získat seznam podvodných domén. */
  define('DOMAINER_ALLOWED_IP', getenv('DOMAINER_ALLOWED_IP'));



  /* --- CONTENT SECURITY POLICY --- */
  /** Hodnota nonce pro Content Security Policy (CSP). */
  define('HTTP_HEADER_CSP_NONCE', base64_encode(openssl_random_pseudo_bytes(32)));



  /* --- SPECIÁLNÍ E-MAILOVÁ HLAVIČKA --- */
  /** Identifikátor speciální hlavičky, která se vkládá ke každému odeslanému e-mailu. */
  define('PHISHING_EMAIL_HEADER_ID', 'X-CESNET-Phishingator');

  /** Hodnota zobrazená u speciální hlavičky. */
  define('PHISHING_EMAIL_HEADER_VALUE', 'https://phishingator.cesnet.cz/.well-known/security.txt');



  /* --- ANONYMIZACE HESEL Z PODVODNÝCH STRÁNEK --- */
  /** Úroveň anonymizace hesel, které se získávají na podvodných stránkách.
     Možnosti:
       between - první a poslední písmeno nebude anonymizováno (počet znaků zachován)
       between3stars - první a poslední písmeno nebude anonymizováno (počet znaků bude vždy 5 - ostatní
                       znaky budou hvězdičky, i když bylo zadáno kratší heslo)
       full - kompletní anonymizace (bude zachován pouze počet znaků)
       nevyplněno - heslo bude zachováno v plain textu
  */
  define('PASSWORD_LEVEL_ANONYMIZATION', (getenv('PASSWORD_LEVEL_ANONYMIZATION')) ? getenv('PASSWORD_LEVEL_ANONYMIZATION') : 'between3stars');

  /** Znak, který se použije místo původního znaku hesla. */
  define('PASSWORD_CHAR_ANONYMIZATION', '*');



  /* --- IDENTIFIKÁTOR UŽIVATELŮ NA PODVODNÝCH STRÁNKÁCH --- */
  /** Délka identifikátoru, který se používá ke konkretizaci uživatele na podvodných stránkách.
   *  Pozor, musí se jednat o sudé číslo! */
  define('USER_ID_WEBSITE_LENGTH', 6);



  /* --- EXPORTY SOUBORŮ --- */
  /** Prefix souborů exportovaných z Phishingatoru. */
  define('PHISHING_CAMPAIGN_EXPORT_FILENAME', 'phishingator-campaign');

  /** Oddělovač používaný k oddělení hodnot v exportovaných CSV souborech. */
  define('PHISHING_CAMPAIGN_EXPORT_DELIMITER', ',');



  /* --- PODVODNÉ STRÁNKY --- */
  /** Adresář pro ukládání konfiguračních VirtualHost souborů pro Apache pro podvodné stránky. */
  define('PHISHING_WEBSITE_CONF_DIR', '/var/www/phishingator/templates/sites-config/');

  /** Adresář, kde se nacházejí dostupné konfigurační VirtualHost soubory pro Apache. */
  define('PHISHING_WEBSITE_APACHE_DIR', '/etc/apache2/sites-available/');

  /** Cesta k souboru, který slouží jako šablona konfiguračního souboru podvodné stránky. */
  define('PHISHING_WEBSITE_TEMPLATE_CONF_FILE', '/var/www/phishingator/templates/000-default.conf');

  /** ServerAdmin v konfiguračním souboru pro Apache VirtualHost pro podvodné stránky. */
  define('PHISHING_WEBSITE_SERVER_ADMIN', getenv('PHISHING_WEBSITE_SERVER_ADMIN'));

  /** Přípona konfiguračního souboru podvodné stránky. */
  define('PHISHING_WEBSITE_CONF_EXT', '.conf');

  /** Přípona konfiguračního souboru nově vytvořené podvodné stránky. */
  define('PHISHING_WEBSITE_CONF_EXT_NEW', '.conf.new');

  /** Přípona konfiguračního souboru podvodné stránky určené ke smazání. */
  define('PHISHING_WEBSITE_CONF_EXT_DEL', '.conf.delete');

  /** Cesta k souboru, který obsluhuje požadavky uživatelů na podvodných stránkách. */
  define('PHISHING_WEBSITE_PREPENDER', '/var/www/phishingator/websitePrepender.php');

  /** Proměnná, která bude v konfiguračním souboru nahrazena dalším aliasem pro podvodnou stránku. */
  define('PHISHING_WEBSITE_ANOTHER_ALIAS', '#PHISHINGATOR_ANOTHER_SERVER_ALIAS');

  /** Název souboru (včetně přípony), v němž je uložen screenshot (ve formátu PNG a o šířce 800 px) podvodné stránky. */
  define('PHISHING_WEBSITE_SCREENSHOT_FILENAME', 'thumbnail.png');

  /** Povolené znaky v názvech subdomén u podvodných stránek v proxy Phishingatoru. */
  define('PHISHING_WEBSITE_SUBDOMAINS_REGEXP', '/[^a-z0-9]/');

  /** Název vstupního pole na podvodné stránce, do kterého uživatel zadává uživatelské jméno. */
  define('PHISHING_WEBSITE_INPUT_FIELD_USERNAME', 'username');

  /** Název vstupního pole na podvodné stránce, do kterého uživatel zadává heslo. */
  define('PHISHING_WEBSITE_INPUT_FIELD_PASSWORD', 'password');

  /** Pole obsahující názvy webových prohlížečů, jejichž akce nemají být ukládány při návštěvě podvodné stránky. */
  define('PHISHING_WEBSITE_IGNORED_USER_AGENTS', ['MicrosoftPreview']);

  /** Délka hashe (v bajtech) pro náhled podvodné stránky. */
  define('PHISHING_WEBSITE_PREVIEW_HASH_BYTES', 32);



  /* --- PHISHINGOVÉ KAMPANĚ --- */
  /** Způsob agregace statistiky v kampaních – na základě:
   *  - 1 = LDAP skupiny uživatele (výchozí)
   *  - 2 = subdomény v e-mailu uživatele
   */
  define('CAMPAIGN_STATS_AGGREGATION', (getenv('CAMPAIGN_STATS_AGGREGATION')) ? getenv('CAMPAIGN_STATS_AGGREGATION') : 1);

  /** Rozmazání identit uživatelů ve statistikách kampaně. */
  define('CAMPAIGN_STATS_BLUR_IDENTITIES', (getenv('CAMPAIGN_STATS_BLUR_IDENTITIES') !== false) ? getenv('CAMPAIGN_STATS_BLUR_IDENTITIES') : true);

  /** Identifikátor výchozí akce po odeslání formuláře (bude ve formuláři při vytváření kampaně předvybrána). */
  define('CAMPAIGN_DEFAULT_ONSUBMIT_ACTION', (getenv('CAMPAIGN_DEFAULT_ONSUBMIT_ACTION')) ? getenv('CAMPAIGN_DEFAULT_ONSUBMIT_ACTION') : 0);

  /** Znak používaný k oddělování příjemců v kampani. */
  define('CAMPAIGN_EMAILS_DELIMITER', "\n");

  /** Čas (HH:MM:SS), kdy dochází k ukončení phishingových kampaní. */
  define('CAMPAIGN_END_TIME', '23:59:59');



  /* --- STRÁNKOVÁNÍ --- */
  /** Minimální počet záznamů, které si může uživatel nechat vypsat na stránce. */
  define('PAGING_MIN_RECORDS_ON_PAGE', 5);

  /** Maximální počet záznamů, které si může uživatel nechat vypsat na stránce. */
  define('PAGING_MAX_RECORDS_ON_PAGE', 200);

  /** Výchozí počet záznamů na jedné stránce. */
  define('PAGING_DEFAULT_COUNT_RECORDS_ON_PAGE', 20);



  /* --- PROMĚNNÉ POUŽÍVANÉ V PODVODNÝCH E-MAILECH, KTERÉ JSOU PŘI ODESLÁNÍ NAHRAZENY SKUTEČNÝM OBSAHEM --- */
  /** Uživatelské jméno příjemce e-mailu. */
  define('VAR_RECIPIENT_USERNAME', '%recipient_username%');

  /** E-mail příjemce. */
  define('VAR_RECIPIENT_EMAIL', '%recipient_email%');

  /** Datum v českém formátu. */
  define('VAR_DATE_CZ', '%date_cz%');

  /** Formát českého data. */
  define('VAR_DATE_CZ_FORMAT', 'j. n. Y');

  /** Datum v jiné formě zápisu. */
  define('VAR_DATE_EN', '%date_en%');

  /** Formát data pro jinou formu zápisu. */
  define('VAR_DATE_EN_FORMAT', 'Y-m-d');

  /** URL podvodné stránky. */
  define('VAR_URL', '%url%');

  /** Identifikátor příjemce pro URL podvodné stránky. */
  define('VAR_RECIPIENT_URL', '%id%');



  /* --- PROMĚNNÉ POUŽÍVANÉ U INDICIÍ V PODVODNÝCH E-MAILECH --- */
  /** Proměnná odkazující na jméno odesílatele e-mailu. */
  define('VAR_INDICATION_SENDER_NAME', '%sender_name%');

  /** Proměnná odkazující na e-mail odesílatele. */
  define('VAR_INDICATION_SENDER_EMAIL', '%sender_email%');

  /** Proměnná odkazující na předmět podvodného e-mailu. */
  define('VAR_INDICATION_SUBJECT', '%subject%');



  /* --- IDENTIFIKÁTORY AKCÍ V KAMPANI DÁLE VYUŽÍVANÝCH VE ZDROJOVÉM KÓDU --- */
  /** Identifikátor akce "bez reakce". */
  define('CAMPAIGN_NO_REACTION_ID', 1);

  /** Identifikátor akce "návštěva podvodné stránky". */
  define('CAMPAIGN_VISIT_FRAUDULENT_PAGE_ID', 2);

  /** Identifikátor akce "zadání neplatných (přihlašovacích) údajů". */
  define('CAMPAIGN_INVALID_CREDENTIALS_ID', 3);

  /** Identifikátor akce "zadání platných (přihlašovacích) údajů". */
  define('CAMPAIGN_VALID_CREDENTIALS_ID', 4);



  /* --- ODESÍLÁNÍ E-MAILŮ --- */
  /** Maximální počet e-mailů, které se pošlou v jedné iteraci cyklu (poté se skript na určitou dobu pozastaví,
      viz další konstanta). */
  define('EMAIL_SENDER_EMAILS_PER_CYCLE', 5);

  /** Zpoždění mezi tím, než se pošle další počet (sada) e-mailů (viz předchozí konstanta). */
  define('EMAIL_SENDER_DELAY_MS', 2000);

  /** O kolik sekund déle bude moct skript běžet poté, co byl pozastaven (viz předchozí konstanta). */
  define('EMAIL_SENDER_CPU_TIME_S', 10);



  /* --- DOMÉNA, Z NÍŽ MOHOU POCHÁZET PŘÍJEMCI --- */
  /** Název povolené domény, ze které mohou pocházet příjemci. */
  define('EMAILS_ALLOWED_DOMAIN', getenv('ORG_DOMAIN'));



  /* --- ODDĚLOVÁNÍ NÁZVŮ LDAP SKUPIN --- */
  /** Oddělovač LDAP skupin, které jsou zobrazené v seznamu příjemců. */
  define('LDAP_GROUPS_DELIMITER', ';');



  /* --- ODDĚLOVAČ DATA --- */
  /** Oddělovač data pro vstup od uživatele. */
  define('DATE_DELIMETER', '-');



  /* --- OPRÁVNĚNÍ V SYSTÉMU --- */
  /** Nejvyšší administrátorské oprávnění. */
  define('PERMISSION_ADMIN', 0);

  /** Název pro URL pro nejvyšší administrátorské oprávnění. */
  define('PERMISSION_ADMIN_URL', 'administrator');

  /** Název nejvyššího administrátorského oprávnění. */
  define('PERMISSION_ADMIN_TEXT', 'administrátor');

  /** Druhé nejvyšší oprávnění (správce testů). */
  define('PERMISSION_TEST_MANAGER', 1);

  /** Název pro URL pro druhé nejvyšší oprávnění (správce testů). */
  define('PERMISSION_TEST_MANAGER_URL', 'test-manager');

  /** Název druhého nejvyššího oprávnění (správce testů). */
  define('PERMISSION_TEST_MANAGER_TEXT', 'správce testů');

  /** Nejnižší oprávnění. */
  define('PERMISSION_USER', 2);

  /** Název pro URL pro nejnižší oprávnění (uživatel). */
  define('PERMISSION_USER_URL', 'user');

  /** Název nejnižšího oprávnění. */
  define('PERMISSION_USER_TEXT', 'uživatel');



  /* --- TYPY A CSS TŘÍDY SYSTÉMOVÝCH HLÁŠENÍ --- */
  /* Názvy hlášení a jejich přiřazení k Exception->getCode() používaných ve zdrojovém kódu. */

  /** Typ: error */
  define('MSG_ERROR', 1);

  /** CSS třída: error */
  define('MSG_CSS_ERROR', 'danger');

  /** Typ: success */
  define('MSG_SUCCESS', 2);

  /** CSS třída: success */
  define('MSG_CSS_SUCCESS', 'success');

  /** Typ: warning */
  define('MSG_WARNING', 3);

  /** CSS třída: warning */
  define('MSG_CSS_WARNING', 'warning');

  /** CSS třída: default */
  define('MSG_CSS_DEFAULT', 'secondary');



  /* --- URL AKCÍ POUŽÍVANÝCH VE ZDROJOVÉM KÓDU --- */
  /** Přepnutí role uživatele. */
  define('ACT_SWITCH_ROLE', 'switch-role');

  /** Vytvoření nového záznamu. */
  define('ACT_NEW', 'new');

  /** Úprava existujícího záznamu. */
  define('ACT_EDIT', 'edit');

  /** Smazání záznamu. */
  define('ACT_DEL', 'delete');

  /** Zobrazení statistiky. */
  define('ACT_STATS', 'stats');

  /** Zobrazení statistiky – reakce jednotlivých uživatelů. */
  define('ACT_STATS_USERS_RESPONSES', 'users-responses');

  /** Zobrazení statistiky – všechny akce provedené na podvodné stránce. */
  define('ACT_STATS_WEBSITE_ACTIONS', 'website-actions');

  /** Úprava nastavení uživatelského hlášení o phishingu. */
  define('ACT_STATS_REPORT_PHISH', 'report');

  /** Export dat. */
  define('ACT_EXPORT', 'export');

  /** Náhled. */
  define('ACT_PREVIEW', 'preview');

  /** Indicie. */
  define('ACT_INDICATIONS', 'indications');

  /** Stránka s informací o absolvování cvičného phishingu (na veřejné části webu). */
  define('ACT_PHISHING_TEST', 'phishing');

  /** Zobrazení screenshotu podvodné stránky. */
  define('ACT_PHISHING_IMG', 'image');
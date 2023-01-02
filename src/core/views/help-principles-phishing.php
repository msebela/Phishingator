<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Jak připravit phishing</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="help-circle"></span>
      Nápověda
    </a>
  </div>
</div>

<p>Podobně jako při psaní běžného e-mailu je nutné i&nbsp;při <strong>vytváření cvičného podvodného e-mailu</strong> pamatovat na určité <strong>zásady</strong>. Cílem je vždy sestavit cvičný podvodný e-mail, který uživatele <strong>naučí odhalovat</strong> a&nbsp;zaměřovat se na <strong>typické znaky a&nbsp;techniky</strong> používané ve <strong>phishingových e-mailech</strong>.</p>

<h4 class="pb-2 mb-3 border-bottom">
  <span class="badge badge-secondary">1.</span>
  Vymyslete téma
</h4>
<p>Mezi <strong>vhodná témata</strong> pro cvičné podvodné e-maily například patří:</p>
<ul>
  <li><strong>různé notifikace</strong>, které běžně přicházejí &ndash; <span class="text-secondary">vypršení hesla, žádost o&nbsp;sdílení online dokumentu, kontrola použití účtu z&nbsp;jiné lokality, nepřečtené zprávy apod.</span>,</li>
  <li><strong>kontrola nebo potvrzení</strong> údajů,</li>
  <li><strong>test nové aplikace</strong>,</li>
  <li><strong>elektronické odsouhlasení</strong> navýšení platu.</li>
</ul>

<h4 class="pb-2 mb-3 border-bottom">
  <span class="badge badge-secondary">2.</span> Zvolte obtížnost
</h4>
<p>Cvičný podvodný e-mail by měl být <strong>přizpůsoben zkušenostem příjemců</strong>:
<ul>
  <li><strong>začátečníkům</strong> a&nbsp;neproškoleným je vhodné rozesílat <strong>lehčí phishing</strong> (s&nbsp;třemi a&nbsp;více jasnými indiciemi),</li>
  <li><strong>pokročilým uživatelům</strong> je možné rozesílat složitější a&nbsp;cílenější <strong>spear phishing</strong>.</li>
</ul>
<p>V&nbsp;každém cvičném podvodném e-mailu by se však měly vyskytnout <strong>jasné indicie</strong>, na základě kterých bylo možné phishing rozpoznat.</p>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h4>
    <span class="badge badge-secondary">3.</span>
    Vytvořte podvodnou stránku
  </h4>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="/portal/phishing-websites/new" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="plus"></span>
      Nová podvodná stránka
    </a>
  </div>
</div>

<p>Volba <strong>vzhledu</strong> příslušné podvodné stránky a&nbsp;<strong>použité URL</strong> (domény) rovněž <strong>ovlivňuje obtížnost</strong>:</p>
<ul>
  <li>Podvodné stránky se mohou <strong>vizuálně shodovat</strong> se skutečnou, pravou webovou stránku (např. s&nbsp;<strong>přihlašovací stránkou</strong> organizace), a&nbsp;to včetně <strong>důvěryhodného HTTPS</strong> certifikátu.</li>
  <li><strong>Podvodná doména</strong> (tj. hostitel podvodné stránky) může ve svém názvu obsahovat například <strong>nevýrazný překlep</strong> oproti legitimní (správné) doméně nebo v&nbsp;ní mohou být například <strong>tečky nahrazeny pomlčkou</strong> (např. <code>login.<?= getenv('ORG_DOMAIN') ?></code> vs.  <code>login-<?= getenv('ORG_DOMAIN') ?></code>).</li>
</ul>

<div class="alert alert-with-icon alert-warning" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="alert-triangle"></span>
  </div>
  <div>
    Při použití podvodné stránky běžící na <strong>nezabezpečeném protokolu HTTP</strong> je možné, že data zadaná na podvodné stránce mohou být <strong>odposlechnuta</strong>. Uživatelům, kteří do takové podvodné stránky vyplnili platné přihlašovací údaje, by mělo být doporučeno <strong>provést změnu hesla</strong>.
  </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h4>
    <span class="badge badge-secondary">4.</span>
    Vytvořte podvodný e-mail
  </h4>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="/portal/phishing-emails/new" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="plus"></span>
      Nový podvodný e-mail
    </a>
  </div>
</div>
<p>Podle zvoleného tématu a&nbsp;podvodné stránky vytvořte cvičný phishingový e-mail, jehož cílem bude <strong>přimět uživatele kliknout na odkaz</strong> na podvodnou stránku a&nbsp;zadat do ni přihlašovací údaje.</p>
<p>Každý cvičný podvodný e-mail by měl obsahovat <strong>charakteristické znaky phishingu &ndash; indicie</strong> (viz dále), které lze nalézt i&nbsp;ve <strong>skutečném phishingovém e-mailu</strong>. Počtem indicí lze ovlivnit výslednou <strong>obtížnost cvičného phishingu</strong>.</p>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h4>
    <span class="badge badge-secondary">5.</span> Přidejte k&nbsp;e-mailu indicie
  </h4>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="/portal/phishing-emails" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="list"></span>
      Seznam e-mailů
    </a>
  </div>
</div>
<p>K&nbsp;<strong>existujícímu podvodnému e-mailu</strong> je nutné přidat tzv. <strong>indicie</strong> (vyznačené, podezřelé pasáže v&nbsp;textu), na základě kterých bylo možné podvod rozpoznat. Je vhodné, aby se ve cvičném phishingovém e-mailu vyskytly <strong>alespoň tři indicie</strong>, na základě kterých bylo možné <strong>podvod rozpoznat</strong>, typicky:</p>
<ul>
  <li><strong>podvržená adresa</strong> odesílatele <span class="text-secondary">(e-mail se podle jména tváří jako od někoho známého, e-mailová adresa se ale neshoduje)</span>,</li>
  <li><strong>odkaz na podvodnou stránku,</strong></li>
  <li>podle <strong>obsahu</strong> v&nbsp;e-mailu:
    <ul>
      <li><strong>použitý jazyk</strong> <span class="text-secondary">(strojová čeština, neočekávaný jazyk &ndash; např. česká banka píše anglicky)</span>,</li>
      <li><strong>relevantnost</strong> sdělení <span class="text-secondary">(obsah, který by měl být určen někomu jinému nebo jej posílá nekompetentní osoba &ndash; např. uklízečka posílá směrnici)</span>,</li>
      <li><strong>časový nátlak</strong> na uživatele <span class="text-secondary">(nutnost obratem reagovat &ndash; kliknout na podvodný odkaz)</span>,</li>
      <li><strong>hrozba ztráty, příležitosti</strong> <span class="text-secondary">(o&nbsp;co uživatel přijde &ndash; např. zablokování účtu, odměny zdarma)</span>,</li>
      <li><strong>vydávání se za autoritu</strong> <span class="text-secondary">(motivace na zprávu reagovat &ndash; např. vydávání se za administrátora serveru, za ředitele organizace)</span>.</li>
    </ul>
  </li>
</ul>

<div class="alert alert-with-icon alert-warning" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="alert-triangle"></span>
  </div>
  <div>
    Ve cvičném phishingu by <strong>nemělo být použito</strong> (ani překrouceno) <strong>jméno existující osoby</strong> nebo <strong>jméno existujícího oddělení</strong> v&nbsp;organizaci. Osoba nebo oddělení by mohla být následně <strong>mylně nařčena</strong> nebo <strong>zahlacena nevyžádanými dotazy</strong>.
  </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h4>
    <span class="badge badge-secondary">6.</span>
    Vytvořte phishingovou kampaň
  </h4>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="/portal/campaigns/new" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="plus"></span>
      Nová kampaň
    </a>
  </div>
</div>
<p>Vyberte <strong>seznam příjemců</strong> a&nbsp;již <strong>vytvořený podvodný e-mail a podvodnou stránku</strong>, kterým bude ve stanovené datum a&nbsp;čas cvičný phishing doručen. <strong>Čas odeslání e-mailu</strong> může být <strong>přizpůsoben zaměstnání příjemce</strong> &ndash; úředníkům v&nbsp;ranních hodinách, IT pracovníkům po době oběda apod.</p>

<p>Jedna z&nbsp;voleb při vytváření kampaně umožňuje zvolit <strong>akci po odeslání formuláře</strong> na podvodné stránce, kde je doporučeno volit akci, která uživateli <strong>zobrazí indicie</strong>, na základě kterých bylo možné phishing rozpoznat (z&nbsp;důvodu případného <strong>poučení při podlehnutí phishingu</strong>).</p>

<div class="alert alert-with-icon alert-primary" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="clock"></span>
  </div>
  <div>
    Phishingová kampaň by <strong>měla trvat po dobu 2 až 5&nbsp;dní</strong> &ndash; nejvíce akcí se odehraje během prvního a&nbsp;druhého dne. S&nbsp;přibývajícím počtem dní navíc uživatelé ztrácí přehled o&nbsp;tom, že jim byl doručen cvičný phishing. <strong>Zpětná vazba</strong> (ve formě e-mailové notifikace), která je uživatelům doručena <strong>den po ukončení kampaně</strong>, je přitom pro případné <strong>poučení uživatelů</strong> zásadní.
  </div>
</div>

<div class="alert alert-with-icon alert-primary" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="calendar"></span>
  </div>
  <div>
    Je vhodné cvičný phishing rozesílat <strong>3&times; až 4&times;&nbsp;ročně</strong>. Uživatelé tak o&nbsp;<strong>hrozbě phishingu</strong> (a&nbsp;jeho případných nových technikách) pravidelně budou <strong>informováni</strong>, ale zároveň je nebudou podobná cvičení <strong>obtěžovat</strong>.
  </div>
</div>
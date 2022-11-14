<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Jak poznat phishing</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="help-circle"></span>
      Nápověda
    </a>
  </div>
</div>

<p>Phishing <i>[fišing]</i> je <strong>podvodný e-mail</strong>, který uživatele láká na <strong>něco neuvěřitelného</strong>, nebo se mu snaží nějakým způsobem <strong>vyhrožovat</strong> či <strong>napodobovat</strong> jinou známou <strong>instituci, osobu</strong> a&nbsp;jejím jménem uživatele <strong>o&nbsp;něco žádat</strong>.</p>
<p>Útočníci phishingové e-maily rozesílají v&nbsp;<strong>obrovském množství</strong>, přičemž jejich <strong>cílem je poškodit uživatele</strong> (a&nbsp;často i&nbsp;instituci, se kterou je e-mail spojen). Z&nbsp;uživatelů se snaží <strong>získat přihlašovací</strong> či jiné <strong>důvěrné údaje</strong>, například <strong>číslo platební karty</strong>, a&nbsp;to nejčastěji skrze <strong>podvodné stránky</strong>. Metodám, které útočníci ve phishingu používají, se říká <strong>techniky sociálního inženýrství</strong>.</p>

<div class="alert alert-info" role="alert">
  Kromě phishingu se začíná rozmáhat i&nbsp;tzv. <strong>vishing</strong> (podvodné telefonáty) a&nbsp;<strong>smishing</strong> (podvodné zprávy a&nbsp;SMS).
</div>

<h4 class="pb-2 mb-3 border-bottom">Znaky phishingu</h4>
<p>Podvodný e-mail <strong>lze rozpoznat</strong> podle několika typických znaků:</p>
<ul>
  <li><strong>podvržená adresa</strong> odesílatele <span class="text-secondary">(e-mail se tváří např. jako od někoho známého)</span>,</li>
  <li><strong>odkaz na podvodnou stránku,</strong></li>
  <li>podle <strong>obsahu</strong> v&nbsp;e-mailu:
    <ul>
      <li><strong>použitý jazyk</strong> <span class="text-secondary">(strojová čeština, česká banka píše anglicky)</span>,</li>
      <li><strong>relevantnost</strong> sdělení <span class="text-secondary">(obsah, který by měl být určen někomu jinému)</span>,</li>
      <li><strong>časový nátlak</strong> na uživatele <span class="text-secondary">(nutnost obratem reagovat &ndash; kliknout na podvodný odkaz)</span>,</li>
      <li><strong>hrozba ztráty, příležitosti</strong> <span class="text-secondary">(o&nbsp;co uživatel přijde &ndash; např. zablokování účtu, odměny zdarma)</span>,</li>
      <li><strong>vydávání se za autoritou</strong> <span class="text-secondary">(motivace na zprávu reagovat &ndash; např. vydávání se za administrátora serveru)</span>.</li>
    </ul>
  </li>
</ul>
<p>Pokud se Vám nějaký e-mail nezdá, zkuste si postupně u každého z&nbsp;výše uvedených <strong>bodů odškrtávat</strong>, zdali se v&nbsp;e-mailu nevyskytuje právě některý ze znaků phishingu.</p>

<div class="alert alert-info" role="alert">
  Nezapomeňte, že s rozpoznáním phishingu od legitimního e-mailu Vám mohou <strong>pomoci i&nbsp;kolegové</strong> nebo se <strong>poraďte s&nbsp;pracovníky IT oddělení</strong>. Snažte se vyhnout automatickému a&nbsp;<strong>zbrklému klikání na odkazy</strong> nebo <strong>neznámé přílohy</strong> a&nbsp;vždy si <strong>nechte čas na promyšlenou</strong> &ndash; i&nbsp;za předpokladu, že odesílatel píše, abyste jednali rychle.
</div>

<h4 class="pb-2 mb-3 border-bottom">Příklad phishingu</h4>
<p>Konkrétní indicie pro rozpoznání phishingu ukazuje následující <strong>příklad podvodného e-mailu</strong>:</p>

<div class="container slide-phishing-example">
  <div class="window">
    <div class="row">
      <div class="column left">
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
      </div>
      <div class="column middle header"></div>
      <div class="column right">
        <div>
          <span class="bar"></span>
          <span class="bar"></span>
          <span class="bar"></span>
        </div>
      </div>
    </div>
    <div class="content">
      <div class="row">
        <div class="col-sm-3 col-md-2"><strong>Od:</strong></div>
        <div class="col"><?= $phishing['org'] ?> &lt;<a href="#indication-1-text" id="indication-1" class="indication anchor-link" onclick="markIndication(1)" onmouseover="markIndication(1)" onmouseout="markIndication(1)">admin@web<?= $phishing['orgDomain'] ?><div class="icons"><div><span data-feather="alert-triangle"></span></div><div><span data-feather="arrow-up-left"></span></div></div></a>&gt;</div>
      </div>
      <div class="row">
        <div class="col-sm-3 col-md-2"><strong>Předmět:</strong></div>
        <div class="col">Kalendář plánu mezd <?= date('Y') + 1 ?> je nyní k&nbsp;dispozici (důležitý)</div>
      </div>
      <div class="row">
        <div class="col-sm-3 col-md-2"><strong>Komu:</strong></div>
        <div class="col"><?= $phishing['recipient'] ?></div>
      </div>
      <div class="row">
        <div class="col-sm-3 col-md-2"><strong>Datum:</strong></div>
        <div class="col"><?= date('j. n. Y H:i') ?></div>
      </div>
      <hr>
      <div class="row">
        <div class="col">
          <p>Kalendář plánu mezd <?= date('Y') + 1 ?> je nyní k&nbsp;dispozici:</p>
          &ndash; <a href="#indication-2-text" id="indication-2" class="indication anchor-link" onclick="markIndication(2)" onmouseover="markIndication(2)" onmouseout="markIndication(2)">http://login.web<?= $phishing['orgDomain'] ?><div class="icons"><div><span data-feather="alert-triangle"></span></div><div><span data-feather="arrow-up-left"></span></div></div></a>
          <br><br>
          Pokud si plán nevyzvednete do 24&nbsp;hodin, odměny vám <a href="#indication-3-text" id="indication-3" class="indication anchor-link" onclick="markIndication(3)" onmouseover="markIndication(3)" onmouseout="markIndication(3)">nebudou přiděleny<div class="icons"><div><span data-feather="alert-triangle"></span></div><div><span data-feather="arrow-up-left"></span></div></div></a>.
          <br><br>
          Ředitel <?= $phishing['org'] ?>
        </div>
      </div>
    </div>
  </div>
</div>
<hr>
<div class="container card-columns text-dark">
  <div id="indication-1-text" class="card bg-light cursor-pointer" onmouseover="markIndication(1)" onmouseout="markIndication(1)">
    <a href="#indication-1" class="anchor-link">
      <div class="card-body">
        <h5 class="card-title">
          <span class="badge badge-pill badge-dark">1.&nbsp;indicie</span>
          E-mail odesílatele
        </h5>
        <p class="card-text">E-mail odesílatele nemá s&nbsp;organizací <?= $phishing['org'] ?> nic společného, byť se útočník snažil, aby byl v&nbsp;adrese odesílatele uveden její název.</p>

        <div class="clearfix">
          <button type="button" id="indication-1-btn" class="btn btn-sm btn-info float-right" onclick="markIndication(1)">
            <span data-feather="chevron-up"></span>
            <span>Označit</span>
          </button>
        </div>
      </div>
    </a>
  </div>
  <div id="indication-2-text" class="card bg-light cursor-pointer" onmouseover="markIndication(2)" onmouseout="markIndication(2)">
    <a href="#indication-2" class="anchor-link">
      <div class="card-body">
        <h5 class="card-title">
          <span class="badge badge-pill badge-dark">2.&nbsp;indicie</span>
          Podezřelá URL
        </h5>
        <p class="card-text">Nejedná se o&nbsp;oficiální adresu organizace <?= $phishing['org'] ?>, ale o&nbsp;snahu útočníka napodobit její název falešnou doménou <span class="text-monospace">web<?= $phishing['orgDomain'] ?></span>.</p>
        <p class="card-text">Celý odkaz navíc začíná zastaralým a&nbsp;nezabezpečeným protokolem HTTP místo bezpečného HTTPS.</p>

        <div class="clearfix">
          <button type="button" id="indication-2-btn" class="btn btn-sm btn-info float-right" onclick="markIndication(2)">
            <span data-feather="chevron-up"></span>
            <span>Označit</span>
          </button>
        </div>
      </div>
    </a>
  </div>
  <div id="indication-3-text" class="card bg-light cursor-pointer" onmouseover="markIndication(3)" onmouseout="markIndication(3)">
    <a href="#indication-3" class="anchor-link">
      <div class="card-body">
        <h5 class="card-title">
          <span class="badge badge-pill badge-dark">3.&nbsp;indicie</span>
          Nátlak, hrozba ztrátou
        </h5>
        <p class="card-text">Útočník se snaží donutit uživatele k&nbsp;okamžité akci &ndash; kliknutí na podvodný odkaz pod hrozbou nezískání odměn.</p>

        <div class="clearfix">
          <button type="button" id="indication-3-btn" class="btn btn-sm btn-info float-right" onclick="markIndication(3)">
            <span data-feather="chevron-up"></span>
            <span>Označit</span>
          </button>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="alert alert-info" role="alert">
  <strong>Uživatelé</strong> si bohužel často <strong>hrozbu phishingu nepřipouští</strong> nebo dokonce o&nbsp;ní <strong>vůbec neví</strong>, a&nbsp;<strong>nahrávají tak útočníkům</strong>.
</div>
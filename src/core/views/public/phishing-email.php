<div class="container mt-5">
  <div class="row pb-5">
    <div class="col-md-10 col-lg-11">
      <h2 class="mb-3">Právě jste absolvovali <strong>cvičný phishing</strong></h2>
      <?php if ($campaign_ended == 0): ?>
      <p class="par-intro"><strong>Kdyby</strong> se jednalo o&nbsp;<strong>skutečný phishing</strong>, <strong>útočníci</strong> by v&nbsp;tuto chvíli již velmi pravděpodobně <strong>znali údaje</strong>, které jste vyplnili do přihlašovacího formuláře na <strong>podvodné stránce</strong>.</p>
      <?php endif; ?>
      <?php if ($volunteer == 1): ?>
      <p class="par-intro"><strong>Děkujeme</strong>, že máte zájem <strong>vzdělávat se</strong> v&nbsp;oblasti <strong>phishingu</strong>. Jakékoliv změny včetně nastavení <strong>limitu cvičných e-mailů</strong> můžete provést po <a href="/portal">přihlášení</a> do Phishingatoru.</p>
      <?php else: ?>
      <p class="par-intro">Využijte projektu <a href="<?= WEB_BASE_URL ?>">Phishingator</a> a&nbsp;<strong><a href="<?= WEB_BASE_URL ?>">přihlaste se</a></strong> k&nbsp;odebírání <strong>cvičných</strong> podvodných e-mailů, které Vám názorně ukáží, <strong>na co se v&nbsp;e-mailu zaměřit</strong> a&nbsp;<strong>jak rozpoznat a&nbsp;nenaletět na opravdový phishing</strong>.</p>
      <?php endif; ?>
    </div>

    <div class="col-md-6 col-lg-5 mb-3 mb-md-0 d-flex align-items-center justify-content-end">
      <a href="<?= WEB_BASE_URL ?>" class="btn btn-primary with-icon mt-4 mt-md-0" role="button">
        Více informací&hellip;
      </a>
    </div>
  </div>

  <div class="pt-5 pb-4 mb-3 border-top">
    <h3 class="mb-4">Jak bylo možné <strong>phishing</strong> rozpoznat z&nbsp;<strong>e-mailu</strong></h3>

    <div class="window-wrapper">
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
            <div class="col-sm-3"><strong>Od:</strong></div>
            <div class="col"><?= $_email['sender'] ?></div>
          </div>
          <div class="row">
            <div class="col-sm-3"><strong>Předmět:</strong></div>
            <div class="col"><?= $_email['subject'] ?></div>
          </div>
          <div class="row">
            <div class="col-sm-3"><strong>Komu:</strong></div>
            <div class="col"><?= $email['recipient_email'] ?></div>
          </div>
          <div class="row">
            <div class="col-sm-3"><strong>Datum:</strong></div>
            <div class="col"><?= $email['datetime_sent_formatted'] ?></div>
          </div>
          <hr>
          <div class="row">
            <div class="col">
              <?= $_email['body'] ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($email['indications'])): ?>
  <div class="card-columns pb-5 mb-5 border-bottom text-dark">
    <?php foreach ($email['indications'] as $i => $indication): ?>
    <div id="indication-<?= $indication['id_indication'] ?>-text" class="card bg-light cursor-pointer mark-indication" data-indication="<?= $indication['id_indication'] ?>">
      <a href="#indication-<?= $indication['id_indication'] ?>" class="anchor-link">
        <div class="card-body">
          <h5 class="card-title">
            <span class="badge badge-pill badge-dark"><?= ($i + 1) ?>.&nbsp;indicie</span>
            <?= $indication['title'] ?>
          </h5>
          <p class="card-text"><?= $indication['description'] ?></p>

          <div class="clearfix">
            <button type="button" id="indication-<?= $indication['id_indication'] ?>-btn" class="btn btn-sm btn-info float-right mark-indication" data-indication="<?= $indication['id_indication'] ?>">
              <span data-feather="chevron-up"></span>
              <span>Označit</span>
            </button>
          </div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <h3 class="mb-4">Jak bylo možné <strong>phishing</strong> rozpoznat <strong>na stránce</strong></h3>

  <div class="window-wrapper mb-5">
    <div class="window">
      <div class="row">
        <div class="column left">
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
        </div>
        <div class="column middle header">
          <div class="url<?php if ($website['http']): ?> http<?php endif; ?>">
            <?php if ($website['http']): ?><span data-feather="alert-triangle"></span>&nbsp;Nezabezpečeno | <?php else: ?><span data-feather="lock"></span><?php endif; ?>
            <?= $website['url_before_domain'] ?><a href="#indication-url-text" id="indication-url" class="indication anchor-link mark-indication" data-indication="url"><?= $website['domain'] ?><div class="icons top"><div><span data-feather="alert-triangle"></span></div><div><span data-feather="arrow-up-left"></span></div></div></a><?= $website['url_after_domain'] ?>
          </div>
          <div class="phishing-sign bg-danger">
            <span data-feather="x"></span>
            Phishing
          </div>
        </div>
        <div class="column right">
          <div>
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
          </div>
        </div>
      </div>
      <div class="content phishing-website-detail">
        <img src="<?= $website['image_src'] ?>" alt="Screenshot podvodné stránky">
        <div class="screenshot-shadow"></div>
      </div>
    </div>
  </div>

  <?php $i = 1; ?>
  <div class="card-columns pb-5 mb-5 border-bottom text-dark">
    <div id="indication-url-text" class="card bg-light cursor-pointer mark-indication" data-indication="url">
      <a href="#indication-url" class="anchor-link">
        <div class="card-body">
          <h5 class="card-title">
            <span class="badge badge-pill badge-dark"><?= $i++; ?>.&nbsp;indicie</span>
            Špatná adresa stránky
          </h5>
          <p class="card-text">Snaha o&nbsp;napodobení adresy stránky &ndash; jedná se o&nbsp;typický trik útočníků, kdy se snaží využít překlepu nebo malé nesrovnalosti v&nbsp;adrese.
          <?php if (!empty($website['indication'])): ?>
          <p class="card-text"><?= $website['indication'] ?></p>
          <?php endif; ?>

          <div class="clearfix">
            <button type="button" id="indication-url-btn" class="btn btn-sm btn-info float-right">
              <span data-feather="chevron-up"></span>
              <span>Označit</span>
            </button>
          </div>
        </div>
      </a>
    </div>

    <?php if ($website['http']): ?>
    <div class="card bg-light cursor-pointer mark-indication">
      <a href="#indication-http" class="anchor-link">
        <div class="card-body">
          <h5 class="card-title">
            <span class="badge badge-pill badge-dark"><?= $i++; ?>.&nbsp;indicie</span>
            Nezabezpečeno
          </h5>
          <p class="card-text">
            Adresa stránky začíná zkratkou protokolu <abbr title="Hypertext Transfer Protocol" class="font-weight-bold">HTTP</abbr>, vždy je ale nutné pro přihlašování přistupovat na stránku se zabezpečeným protokolem se zkratkou <abbr title="Hypertext Transfer Protocol Secure" class="font-weight-bold">HTTPS</abbr> (<strong>S</strong> ve zkratce znamená <i>secure</i>, tedy zabezpečeno).
          </p>
          <p class="card-text">
            Vedle adresy může být také vidět popisek <strong class="d-inline-block">&bdquo;<span data-feather="alert-triangle"></span>&nbsp;Nezabezpečeno&ldquo;</strong> &ndash; to znamená, že všechno, co na webu děláte a&nbsp;zadáte, může kdokoliv sledovat.
          </p>
        </div>
      </a>
    </div>
    <?php endif; ?>

    <?php if ($website['cloned']): ?>
    <div class="card bg-light cursor-pointer mark-indication">
      <a href="#indication-cloned" class="anchor-link">
        <div class="card-body">
          <h5 class="card-title">
            <span class="badge badge-pill badge-dark"><?= $i++; ?>.&nbsp;indicie</span>
            Zkopírovaný vzhled
          </h5>
          <p class="card-text">
            Útočníci jsou schopni vzhled stránek zkopírovat do posledního detailu. Podvodná (falešná) přihlašovací stránka se může chovat i&nbsp;<strong>vypadat úplně stejně</strong> jako ta správná (legitimní).
          </p>
          <p class="card-text">
            Vždy si tedy především zkontrolujte adresu, na které stránka je.
          </p>
        </div>
      </a>
    </div>
    <?php endif; ?>
  </div>

  <footer>
    <div class="row">
      <div class="col">
        <h3>Důležité informace</h3>
        <ul class="mb-lg-5">
          <li>Zadané heslo nebylo <strong>nikde uloženo</strong>. Doporučujeme ale, abyste si ho změnili.</li>
          <li>Praktické phishingové testy probíhají ve spolupráci s&nbsp;Vaším IT oddělením.</li>
        </ul>
      </div>

      <div class="col-lg-3 footer-logo">
        <a href="https://www.cesnet.cz" target="_blank">
          <img src="/img/logo-cesnet.svg" alt="Logo sdružení CESNET, z. s. p. o.">
        </a>
      </div>
    </div>
  </footer>
</div>
<div class="container mt-5">
  <div class="row pb-5 mb-5 border-bottom">
    <div class="col-md-11 col-lg-12">
      <h2 class="mb-3">Právě jste absolvovali <strong>cvičný phishing</strong></h2>
      <?php if ($campaign_ended == 0): ?>
      <p class="par-intro"><strong>Kdyby</strong> se jednalo o&nbsp;<strong>skutečný phishing</strong>, <strong>útočníci</strong> by v&nbsp;tuto chvíli již velmi pravděpodobně <strong>znali údaje</strong>, které jste vyplnili do formuláře na <strong>podvodné stránce</strong>.</p>
      <?php endif; ?>
      <?php if ($volunteer == 1): ?>
      <p class="par-intro"><strong>Děkujeme</strong>, že máte zájem <strong>vzdělávat se</strong> v&nbsp;oblasti <strong>phishingu</strong>. Jakékoliv <strong>změny</strong> včetně nastavení <strong>limitu cvičných e-mailů</strong> můžete provést po <a href="/portal">přihlášení</a> do Phishingatoru.</p>
      <?php else: ?>
      <p class="par-intro">Využijte <strong>projektu</strong> <a href="<?= WEB_BASE_URL ?>">Phishingator</a> a&nbsp;<strong><a href="<?= WEB_BASE_URL ?>">přihlaste se</a></strong> k&nbsp;odebírání <strong>cvičných</strong> podvodných e-mailů, které Vám názorně ukáží, <strong>na co se v&nbsp;e-mailu zaměřit</strong> a&nbsp;<strong>jak rozpoznat a&nbsp;nenaletět na opravdový phishing</strong>.</p>
      <?php endif; ?>
    </div>

    <div class="col-md-5 col-lg-4 mb-3 mb-md-0 d-flex align-items-center justify-content-end">
      <a href="<?= WEB_BASE_URL ?>" class="btn btn-primary with-icon" role="button">
        Více informací&hellip;
      </a>
    </div>
  </div>

  <div class="pb-4 mb-3">
    <h3 class="mb-4">Jak bylo možné <strong>phishing</strong> rozpoznat z&nbsp;<strong>e-mailu</strong></h3>

    <div class="slide-phishing-example">
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
    <?php for ($i = 0; $i < count($email['indications']); $i++): ?>
    <div id="indication-<?= $email['indications'][$i]['id_indication'] ?>-text" class="card bg-light cursor-pointer mark-indication" data-indication="<?= $email['indications'][$i]['id_indication'] ?>">
      <a href="#indication-<?= $email['indications'][$i]['id_indication'] ?>" class="anchor-link">
        <div class="card-body">
          <h5 class="card-title">
            <span class="badge badge-pill badge-dark"><?= ($i + 1) ?>.&nbsp;indicie</span>
            <?= $email['indications'][$i]['title'] ?>
          </h5>
          <p class="card-text"><?= $email['indications'][$i]['description'] ?></p>

          <div class="clearfix">
            <button type="button" id="indication-<?= $email['indications'][$i]['id_indication'] ?>-btn" class="btn btn-sm btn-info float-right mark-indication" data-indication="<?= $email['indications'][$i]['id_indication'] ?>">
              <span data-feather="chevron-up"></span>
              <span>Označit</span>
            </button>
          </div>
        </div>
      </a>
    </div>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

  <h3 class="mb-4">Jak bylo možné <strong>phishing</strong> rozpoznat <strong>na stránce</strong></h3>

  <div class="slide-phishing-example mb-5">
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
          <div class="status bg-danger">
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
          <p class="card-text">Snaha o&nbsp;napodobení adresy stránky &ndash; je třeba sledovat adresu webu až do jejího konce. <?= $website['indication'] ?></p>

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
    <div class="card bg-light">
      <div class="card-body">
        <h5 class="card-title">
          <span class="badge badge-pill badge-dark"><?= $i ?>.&nbsp;indicie</span>
          Nezabezpečená stránka
        </h5>
        <p class="card-text">Adresa stránky začíná zkratkou <strong>HTTP</strong> místo zabezpečeného protokolu <strong>HTTP<span class="text-danger">S</span></strong>. Také může být vedle adresy vidět popisek <strong class="d-inline-block">&bdquo;<span data-feather="alert-triangle"></span>&nbsp;Nezabezpečeno&ldquo;</strong> &ndash; to znamená, že všechno, co na webu děláte a&nbsp;zadáte, může kdokoliv sledovat.</p>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="row">
    <div class="col">
      <h3>Důležité informace</h3>
      <ul class="mb-lg-5">
        <li>Zadané heslo nebylo <strong>nikde uloženo</strong>. Jestli nám však nevěříte, změňte si ho standardní cestou.</li>
        <li>Praktické phishingové testy probíhají ve spolupráci s&nbsp;Vaším IT oddělením.</li>
      </ul>
    </div>

    <div class="col-lg-3 footer-logo">
      <a href="https://www.cesnet.cz" target="_blank">
        <img src="/img/logo-cesnet.svg" alt="Logo sdružení CESNET, z. s. p. o.">
      </a>
    </div>
  </div>
</div>
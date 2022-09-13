<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Podvodné stránky</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group mr-2">
      <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
        <span data-feather="help-circle"></span>
      </a>
      <?php if (!isset($_GET['action'])): ?>
      <a href="/portal/<?= $urlSection . '/' . ACT_NEW ?>" id="btn-<?= ACT_NEW ?>" class="btn btn-info" role="button">
        <span data-feather="plus"></span>
        Nová stránka
      </a>
      <?php else: ?>
      <a href="/portal/<?= $urlSection ?>" class="btn btn-info" role="button">
        <span data-feather="list"></span>
        Seznam stránek
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<p>Tato sekce slouží k&nbsp;vytváření nových a&nbsp;správě dosud vytvořených podvodných stránek, které jsou dále využívány v&nbsp;tzv. <a href="/portal/campaigns">kampaních</a> a&nbsp;na které se uživatel dostane skrze odkazy v&nbsp;zaslaných <a href="/portal/phishing-emails">podvodných e-mailech</a> (phishingu). Účelem stránek je sbírat data, která uživatel zadá do formuláře na nich umístěných. Podrobnější informace jsou k&nbsp;dispozici v&nbsp;dostupné <a href="<?= $helpLink ?>" target="_blank">nápovědě</a>.</p>
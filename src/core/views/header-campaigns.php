<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Kampaně</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group mr-2">
      <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
        <span data-feather="help-circle"></span>
      </a>
      <?php if (!isset($_GET['action'])): ?>
      <a href="/portal/<?= $urlSection . '/' . ACT_NEW ?>" id="btn-<?= ACT_NEW ?>" class="btn btn-info" role="button">
        <span data-feather="plus"></span>
        Nová kampaň
      </a>
      <?php else: ?>
      <a href="/portal/<?= $urlSection ?>" class="btn btn-info" role="button">
        <span data-feather="list"></span>
        Seznam kampaní
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<p>Tato sekce slouží k&nbsp;vytváření nových a&nbsp;správě dosud vytvořených kampaní. Každá z&nbsp;kampaní je svázána se zvoleným <a href="/portal/phishing-emails">podvodným e-mailem</a> a&nbsp;<a href="/portal/phishing-websites">podvodnou webovou stránkou</a>, na kterou se příjemce e-mailu dostane právě z&nbsp;obsahu tohoto e-mailu (pokud bude následovat odkazy v&nbsp;něm uvedené). Podrobnější informace jsou k&nbsp;dispozici v&nbsp;<a href="<?= $helpLink ?>" target="_blank">nápovědě</a>.</p>
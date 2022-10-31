<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Podvodné e-maily</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group mr-2">
      <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
        <span data-feather="help-circle"></span>
      </a>
      <?php if (!isset($_GET['action']) && PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
      <a href="/portal/<?= $urlSection . '/' . ACT_NEW ?>" id="btn-<?= ACT_NEW ?>" class="btn btn-info" role="button">
        <span data-feather="plus"></span>
        Nový e-mail
      </a>
      <?php else: ?>
      <a href="/portal/<?= $urlSection ?>" class="btn btn-info" role="button">
        <span data-feather="list"></span>
        Seznam e-mailů
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
<p>Tato sekce slouží k&nbsp;vytváření nových a&nbsp;správě dosud vytvořených podvodných e-mailů (phishingu), které jsou dále využívány v&nbsp;tzv. <a href="/portal/campaigns">kampaních</a>. Stejně tak lze ke každému z&nbsp;podvodných e-mailů vložit indicie, které si bude moci příjemce po svém přihlášení do systému prohlédnout. Každý z&nbsp;e-mailů si lze rovněž prohlédnout v&nbsp;náhledu, který je již personalizován vůči přihlášenému uživateli. Podrobnější informace jsou k&nbsp;dispozici v&nbsp;<a href="<?= $helpLink ?>" target="_blank">nápovědě</a>.</p>
<?php else: ?>
<p>Tato sekce slouží k&nbsp;nahlížení na dosud vytvořené podvodné e-maily (phishing), které jsou dále využívány v&nbsp;tzv. <a href="/portal/campaigns">kampaních</a>. Vytvářet a&nbsp;upravovat tyto e-maily mohou pouze administrátoři. U každého náhledu podvodného e-mailu jsou navíc zobrazeny i&nbsp;související indicie, které si bude moci příjemce po svém přihlášení do systému prohlédnout.</p>
<?php endif; ?>

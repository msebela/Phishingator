<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Uživatelé</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group">
      <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
        <span data-feather="help-circle"></span>
      </a>
      <?php if (!isset($_GET['action'])): ?>
      <a href="/portal/<?= $urlSection . '/' . ACT_NEW ?>" id="btn-<?= ACT_NEW ?>" class="btn btn-info" role="button">
        <span data-feather="plus"></span>
        Nový uživatel
      </a>
      <?php else: ?>
      <a href="/portal/<?= $urlSection ?>" class="btn btn-info" role="button">
        <span data-feather="list"></span>
        Seznam uživatelů
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<p>Tato sekce slouží k&nbsp;ručnímu přidávání nových a&nbsp;správě dosud přidaných uživatelů. Ve standardním režimu se provádí přidávání nových uživatelů automaticky s&nbsp;vytvořením <a href="/portal/campaigns">kampaně</a> (resp. na základě vyplněných příjemců), popř. dobrovolným přihlášením uživatele přes přihlašovací stránku Phishingatoru.</p>
<hr>

<?php if ($action == ACT_EDIT && $phishingWebsite['status'] == 2): ?>
<div class="alert alert-with-icon alert-danger" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="alert-triangle"></span>
  </div>
  <div>
    <h4 class="alert-heading">Chybné DNS</h4>
    Doména, na které by měla být hostována podvodná stránka, není směrována na IP adresu serveru, kde běží Phishingator.
  </div>
</div>

<hr>
<?php elseif ($action == ACT_EDIT && $phishingWebsite['status'] == 1): ?>
<div class="alert alert-with-icon alert-warning" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="alert-triangle"></span>
  </div>
  <div>
    <h4 class="alert-heading">Nedokončené přesměrování</h4>
    Doména, na níž bude hostována podvodná strána, je správně směrována na IP adresu Phishingatoru, zatím ale nedošlo k&nbsp;aktivaci domény v&nbsp;proxy Phishingatoru.
  </div>
</div>

<hr>
<?php endif; ?>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $phishingWebsite['id_website'] : ''); ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="form-group">
    <label for="<?= $formPrefix ?>name">Název</label>
    <input type="text" class="form-control" id="<?= $formPrefix ?>name" name="<?= $formPrefix ?>name" maxlength="<?= $inputsMaxLengths['name'] ?>" value="<?= $inputsValues['name'] ?>" required>
    <small class="form-text text-muted">Název slouží pouze k&nbsp;identifikaci v&nbsp;rámci tohoto systému.</small>
  </div>

  <div class="form-row">
    <div class="form-group col-sm-9 col-lg-11 col-xl-13">
      <label for="<?= $formPrefix ?>url">URL</label>
      <input type="url" class="form-control" id="<?= $formPrefix ?>url" name="<?= $formPrefix ?>url" maxlength="<?= $inputsMaxLengths['url'] ?>" value="<?= $inputsValues['url'] ?>" required>
      <small class="form-text text-muted">URL adresa včetně protokolu <span class="badge badge-success cursor-pointer phishing-domain-protocol" data-var="https">HTTPS</span> nebo <span class="badge badge-danger cursor-pointer phishing-domain-protocol" data-var="http">HTTP</span> a&nbsp;volitelně i&nbsp;s&nbsp;konkretizováním cesty (názvy adresářů). Na dané URL adrese bude podvodná stránka přístupná (po automatickém doplnění parametru pro identifikaci uživatele). Použít lze (sub)domény uvedené v&nbsp;seznamu <span class="badge badge-info">Registrované domény</span>.</small>
    </div>

    <div class="form-group col-sm-7 col-lg-5 col-xl-3 text-right">
      <label class="d-none d-sm-block">&nbsp;</label>
      <div class="dropdown">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
          Registrované domény (<?= count ($domains) ?>)
        </button>
        <div class="dropdown-menu dropdown-menu-right" id="phishing-domains-dropdown">
          <?php foreach ($domains as $domain): ?>
          <a href="#" class="dropdown-item text-monospace" data-var="https://<?= $domain ?>"><?= $domain ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="form-group">
    <label class="my-1 mr-2" for="<?= $formPrefix ?>id-template">Šablona</label>
    <select class="custom-select my-1 mr-sm-2" id="<?= $formPrefix ?>id-template" name="<?= $formPrefix ?>id-template" required>
      <option value="0">Vyberte&hellip;</option>
      <?php foreach ($templates as $template): ?>
      <option value="<?= $template['id_website_template']; ?>"<?= (($inputsValues['id-template'] == $template['id_website_template']) ? ' selected': ''); ?>><?= $template['name'] ?></option>
      <?php endforeach; ?>
    </select>
    <small class="form-text text-muted">Šablona webové stránky s&nbsp;formulářem, která se zobrazí uživateli.</small>
  </div>

  <div class="form-group">
    <div class="custom-control custom-checkbox my-1 mr-sm-2">
      <input type="checkbox" class="custom-control-input" id="<?= $formPrefix ?>active" name="<?= $formPrefix ?>active"<?= (($inputsValues['active']) ? ' checked' : ''); ?>>
      <label class="custom-control-label" for="<?= $formPrefix ?>active">Aktivovat podvodnou stránku na webovém serveru (do 5&nbsp;min.)</label>
      <small class="form-text text-muted">Změna proběhne do 5&nbsp;minut. Předpokladem je, aby byl u&nbsp;domény (popř. subdomény) v&nbsp;DNS nasměrován záznam typu&nbsp;A na IP adresu serveru, kde běží Phishingator. Po aktivaci budou moci podvodnou stránku využívat ve svých phishingových kampaních i&nbsp;<span class="badge badge-warning">správci testů</span>.</small>
    </div>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary btn-lg" name="<?= $formPrefix . $action; ?>">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
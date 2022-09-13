<hr>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $phishingWebsite['id_website'] : ''); ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="form-group">
    <label for="<?= $formPrefix ?>name">Název</label>
    <input type="text" class="form-control" id="<?= $formPrefix ?>name" name="<?= $formPrefix ?>name" maxlength="<?= $inputsMaxLengths['name'] ?>" value="<?= $inputsValues['name'] ?>" required>
    <small class="form-text text-muted">Název slouží pouze k&nbsp;identifikaci v&nbsp;rámci tohoto systému.</small>
  </div>

  <div class="form-group">
    <label for="<?= $formPrefix ?>url">URL</label>
    <input type="url" class="form-control" id="<?= $formPrefix ?>url" name="<?= $formPrefix ?>url" maxlength="<?= $inputsMaxLengths['url'] ?>" value="<?= $inputsValues['url'] ?>" required>
    <small class="form-text text-muted">Adresa včetně protokolu, pod kterou bude podvodná stránka přístupná (na základě unikátního parametru pro identifikaci uživatele). Pro aplikaci změn je nutné restartovat server Apache a&nbsp;náležitě upravit DNS záznamy.</small>
  </div>

  <div class="form-group">
    <label class="my-1 mr-2" for="<?= $formPrefix ?>id-template">Šablona</label>
    <select class="custom-select my-1 mr-sm-2" id="<?= $formPrefix ?>id-template" name="<?= $formPrefix ?>id-template" required>
      <option value="0">Vyberte&hellip;</option>
      <?php foreach ($templates as $template): ?>
      <option value="<?= $template['id_website_template']; ?>"<?= (($inputsValues['id-template'] == $template['id_website_template']) ? ' selected': ''); ?>><?= $template['name'] ?></option>
      <?php endforeach; ?>
    </select>
    <small class="form-text text-muted">Webová stránka, která se zobrazí uživateli.</small>
  </div>

  <div class="form-group">
    <div class="custom-control custom-checkbox my-1 mr-sm-2">
      <input type="checkbox" class="custom-control-input" id="<?= $formPrefix ?>active" name="<?= $formPrefix ?>active"<?= (($inputsValues['active']) ? ' checked' : ''); ?>>
      <label class="custom-control-label" for="<?= $formPrefix ?>active">Aktivovat podvodnou stránku na webovém serveru (do 5 min.)</label>
      <small class="form-text text-muted">Změna proběhne do 5&nbsp;minut. Předpokladem je, aby byl u&nbsp;domény (popř. subdomény) v&nbsp;DNS nasměrován záznam typu <code>A</code> na IP adresu serveru, kde běží Phishingator &ndash; <code><?= $_SERVER['SERVER_ADDR'] ?></code>. Po aktivaci budou moci podvodnou stránku využívat ve svých phishingových kampaních i&nbsp;<span class="badge badge-warning">správci testů</span>.</small>
    </div>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary btn-lg" name="<?= $formPrefix . $action; ?>">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
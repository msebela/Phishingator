<hr>

<?php if ($action == ACT_EDIT && $phishingWebsite['status'] == 1): ?>
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
<?php elseif ($action == ACT_EDIT && $phishingWebsite['status'] == 2): ?>
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

  <?php $input = 'name'; ?>
  <div class="form-group">
    <label for="<?= $formPrefix . $input ?>">Název</label>
    <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>" required>
    <small class="form-text text-muted">Název slouží pouze pro vlastní pojmenování stránky.</small>
  </div>

  <div class="form-row">
    <?php $input = 'url'; ?>
    <div class="form-group col-lg-11 col-xl-13">
      <label for="<?= $formPrefix . $input ?>">URL</label>
      <input type="url" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>" required>
      <small class="form-text text-muted">URL adresa včetně protokolu <span class="badge badge-success cursor-pointer phishing-domain-protocol" data-var="https">HTTPS</span> nebo <span class="badge badge-danger cursor-pointer phishing-domain-protocol" data-var="http">HTTP</span> (volitelně lze použít i&nbsp;adresáře a&nbsp;GET parametry), na které bude podvodná stránka přístupná. V&nbsp;parametrech adresy se musí uvést proměnná <code class="insert-variable cursor-pointer" data-input="#<?= $formPrefix . $input ?>" data-var="<?= VAR_RECIPIENT_URL ?>"><?= VAR_RECIPIENT_URL ?></code>, která bude nahrazena identifikátorem uživatele. Použít lze domény uvedené v&nbsp;seznamu <span class="badge badge-info">Registrované domény</span>, subdomény je možné volit právě z&nbsp;těchto domén.</small>
    </div>

    <div class="form-group col-lg-5 col-xl-3 text-right">
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

  <div class="alert alert-info" role="alert">
    Proměnná <code class="insert-variable cursor-pointer" data-input="#<?= $formPrefix . $input ?>" data-var="<?= VAR_RECIPIENT_URL ?>"><?= VAR_RECIPIENT_URL ?></code> musí být součástí GET parametrů stránky, a&nbsp;to buď jako některý z&nbsp;parametrů (<span class="text-monospace">&hellip;?<code><?= VAR_RECIPIENT_URL ?></code></span> / <span class="text-monospace">&hellip;&amp;<code><?= VAR_RECIPIENT_URL ?></code></span>), nebo jako hodnota některého z&nbsp;parametrů (<span class="text-monospace">&hellip;?par=<code><?= VAR_RECIPIENT_URL ?></code></span>), například:
    <ul class="mt-2">
      <li class="text-monospace">https://phishingator.cz/?<code><?= VAR_RECIPIENT_URL ?></code></li>
      <li class="text-monospace">https://phishingator.cz/?par=<code><?= VAR_RECIPIENT_URL ?></code></li>
      <li class="text-monospace">https://phishingator.cz/?par1=value&amp;par2=<code><?= VAR_RECIPIENT_URL ?></code></li>
      <li class="text-monospace">https://phishingator.cz/?par1=value&amp;<code><?= VAR_RECIPIENT_URL ?></code>=value&amp;par3=value</li>
      <li class="text-monospace">https://phishingator.cz/dir1/dir2/?par1=value&amp;<code><?= VAR_RECIPIENT_URL ?></code>&amp;par3</li>
    </ul>
  </div>

  <div class="form-row">
    <?php $input = 'id-template'; ?>
    <div class="form-group col-xl-6">
      <label for="<?= $formPrefix . $input ?>">Šablona</label>
      <select name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="custom-select" required>
        <option value="0">Vyberte&hellip;</option>
        <?php foreach ($templates as $template): ?>
        <option value="<?= $template['id_website_template']; ?>"<?= (($inputsValues[$input] == $template['id_website_template']) ? ' selected': ''); ?>><?= $template['name'] ?></option>
        <?php endforeach; ?>
      </select>
      <small class="form-text text-muted">Šablona webové stránky s&nbsp;přihlašovacím formulářem, která se zobrazí uživateli na výše definované URL adrese.</small>
    </div>

    <?php $input = 'service-name'; ?>
    <div class="form-group col-xl-10">
      <label for="<?= $formPrefix . $input ?>">Název služby vypisovaný do šablony (nepovinné)</label>
      <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>">
      <small class="form-text text-muted">Název služby, ke které se uživatel přihlašuje. Pokud to šablona umožňuje, bude název zobrazen na podvodné stránce.</small>
    </div>
  </div>

  <?php $input = 'active'; ?>
  <div class="form-group">
    <div class="custom-control custom-checkbox my-1 mr-sm-2">
      <input type="checkbox" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="custom-control-input"<?= (($inputsValues[$input]) ? ' checked' : ''); ?>>
      <label for="<?= $formPrefix . $input ?>" class="custom-control-label">Aktivovat podvodnou stránku na webovém serveru (do 5&nbsp;min.)</label>
      <small class="form-text text-muted">Změna proběhne do 5&nbsp;minut. Předpokladem je, aby byl u&nbsp;domény (popř. subdomény) v&nbsp;DNS nasměrován záznam typu&nbsp;A&nbsp;na IP adresu serveru, kde běží Phishingator. Po aktivaci budou moci podvodnou stránku využívat ve svých phishingových kampaních i&nbsp;<span class="badge badge-warning">správci testů</span>.</small>
    </div>
  </div>

  <div class="text-center">
    <button type="submit" name="<?= $formPrefix . $action ?>" class="btn btn-primary btn-lg">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
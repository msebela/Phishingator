<hr>

<?php if ($action == ACT_EDIT && $phishingEmail['dns_mx_record'] == 0): ?>
<div class="alert alert-with-icon alert-warning" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="alert-triangle"></span>
  </div>
  <div>
    <h4 class="alert-heading">Chybějící DNS záznam</h4>
    Doména, která je použita&nbsp;v e-mailu odesílatele, nemá v&nbsp;DNS správně směrován MX záznam na Phishingator. E-mail tak ve phishingové kampani nebude poštovním serverem s&nbsp;největší pravděpodobností odeslán.
  </div>
</div>

<hr>
<?php endif; ?>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $phishingEmail['id_email'] : ''); ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="form-row">
    <div class="form-group col-md-9 col-xl-11">
      <label for="<?= $formPrefix ?>name">Název</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>name" name="<?= $formPrefix ?>name" maxlength="<?= $inputsMaxLengths['name'] ?>" value="<?= $inputsValues['name'] ?>" required>
      <small class="form-text text-muted">Název slouží pouze k&nbsp;identifikaci v&nbsp;rámci tohoto systému.</small>
    </div>

    <div class="form-group col-md-7 col-xl-5 pl-md-5">
      <label class="d-none d-md-block">&nbsp;</label>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="<?= $formPrefix ?>hidden" name="<?= $formPrefix ?>hidden"<?= (($inputsValues['hidden']) ? ' checked' : ''); ?>>
        <label class="custom-control-label" for="<?= $formPrefix ?>hidden">Skrýt před správci testů</label>
        <small class="form-text text-muted">E-mail uvidí a&nbsp;mohou rozesílat pouze administrátoři.</small>
      </div>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group col-md-8">
      <label for="<?= $formPrefix ?>sender-name">Jméno odesílatele (nepovinné)</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>sender-name" name="<?= $formPrefix ?>sender-name" maxlength="<?= $inputsMaxLengths['sender-name'] ?>" value="<?= $inputsValues['sender-name'] ?>">
      <small class="form-text text-muted">Při nevyplnění bude použit e-mail odesílatele z&nbsp;následujícího pole, v&nbsp;opačném případě bude odesílatel uveden ve tvaru <code>Jméno &lt;email@domain.tld&gt;</code>.</small>
    </div>

    <div class="form-group col-md-8">
      <label for="<?= $formPrefix ?>sender-email">E-mail odesílatele</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>sender-email" name="<?= $formPrefix ?>sender-email" maxlength="<?= $inputsMaxLengths['sender-email'] ?>" value="<?= $inputsValues['sender-email'] ?>" required>
      <small class="form-text text-muted">Při použití proměnné <code class="cursor-pointer" onclick="replaceVariable('#<?= $formPrefix; ?>sender-email', '<?= VAR_RECIPIENT_EMAIL ?>')"><?= VAR_RECIPIENT_EMAIL ?></code> bude jako odesílatel uveden e-mail příjemce.</small>
    </div>
  </div>

  <div class="form-group">
    <label for="<?= $formPrefix ?>subject">Předmět</label>
    <input type="text" class="form-control" id="<?= $formPrefix ?>subject" name="<?= $formPrefix ?>subject" maxlength="<?= $inputsMaxLengths['subject'] ?>" value="<?= $inputsValues['subject'] ?>" required>
  </div>

  <div class="form-row">
    <div class="form-group col-sm-10 col-lg-12">
      <label for="<?= $formPrefix ?>body">Tělo</label>
      <textarea class="form-control text-monospace" rows="11" id="<?= $formPrefix ?>body" name="<?= $formPrefix ?>body" maxlength="<?= $inputsMaxLengths['body'] ?>" required><?= $inputsValues['body'] ?></textarea>
      <small class="form-text text-muted">V&nbsp;těle e-mailu lze používat proměnné, které budou při odeslání e-mailu nahrazeny zvoleným obsahem.</small>
    </div>
    <div class="form-group col-sm-6 col-lg-4">
      <label>Proměnné</label>
      <p class="text-muted">Pro vložení proměnné do těla e-mailu můžete kliknout na její název v&nbsp;následujícím seznamu:</p>
      <ul class="form-text text-muted list-unstyled" id="<?= $formPrefix ?>variables">
        <li><code class="cursor-pointer" data-var="<?= VAR_RECIPIENT_USERNAME ?>"><?= VAR_RECIPIENT_USERNAME ?></code> &ndash; uživatelské jméno příjemce</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_RECIPIENT_EMAIL ?>"><?= VAR_RECIPIENT_EMAIL ?></code> &ndash; e-mail příjemce</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_DATE_CZ ?>"><?= VAR_DATE_CZ ?></code> &ndash; datum, ve kterém dochází k&nbsp;odeslání e-mailu v&nbsp;českém formátu (<?= date(VAR_DATE_CZ_FORMAT) ?>)</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_DATE_EN ?>"><?= VAR_DATE_EN ?></code> &ndash; datum, ve kterém dochází k&nbsp;odeslání e-mailu ve&nbsp;formátu <samp>YYYY-MM-DD</samp> (<?= date(VAR_DATE_EN_FORMAT) ?>)</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_URL ?>"><?= VAR_URL ?></code> &ndash; URL podvodné stránky svázané s&nbsp;e-mailem</li>
      </ul>
    </div>
  </div>

  <div class="text-center">
    <button type="submit" formtarget="_blank" class="btn btn-secondary btn-lg" name="<?= $formPrefix . ACT_PREVIEW; ?>">
      <span data-feather="eye"></span>
      Náhled
    </button>

    <button type="submit" class="btn btn-primary btn-lg" name="<?= $formPrefix . $action; ?>">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
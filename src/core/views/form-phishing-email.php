<hr>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $phishingEmail['id_email'] : ''); ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="form-row">
    <?php $input = 'name'; ?>
    <div class="form-group col-md-9 col-xl-11">
      <label for="<?= $formPrefix . $input ?>">Název</label>
      <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>" required>
      <small class="form-text text-muted">Název slouží pouze pro vlastní pojmenování e-mailu.</small>
    </div>

    <?php $input = 'hidden'; ?>
    <div class="form-group col-md-7 col-xl-5 pl-md-5">
      <label class="d-none d-md-block">&nbsp;</label>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="custom-control-input"<?= (($inputsValues[$input]) ? ' checked' : ''); ?>>
        <label for="<?= $formPrefix . $input ?>" class="custom-control-label">Skrýt před správci testů</label>
        <small class="form-text text-muted">E-mail uvidí a&nbsp;mohou rozesílat pouze administrátoři.</small>
      </div>
    </div>
  </div>

  <div class="form-row">
    <?php $input = 'sender-name'; ?>
    <div class="form-group col-md-8">
      <label for="<?= $formPrefix . $input ?>">Jméno odesílatele (nepovinné)</label>
      <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>">
      <small class="form-text text-muted">Při nevyplnění bude použit e-mail odesílatele z&nbsp;následujícího pole, v&nbsp;opačném případě bude odesílatel uveden ve tvaru <code>Jméno &lt;email@domain.tld&gt;</code>.</small>
    </div>

    <?php $input = 'sender-email'; ?>
    <div class="form-group col-md-8">
      <label for="<?= $formPrefix . $input ?>">E-mail odesílatele</label>
      <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>" required>
      <small class="form-text text-muted">Při použití proměnné <code class="replace-variable cursor-pointer" data-input="#<?= $formPrefix . $input ?>" data-var="<?= VAR_RECIPIENT_EMAIL ?>"><?= VAR_RECIPIENT_EMAIL ?></code> bude jako odesílatel uveden e-mail příjemce.</small>
    </div>
  </div>

  <?php $input = 'subject'; ?>
  <div class="form-group">
    <label for="<?= $formPrefix . $input ?>">Předmět</label>
    <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>" required>
  </div>

  <div class="form-row">
    <?php $input = 'body'; ?>
    <div class="form-group col-lg-10 col-xl-12">
      <label for="<?= $formPrefix . $input ?>">Tělo</label>
      <textarea name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control text-monospace" rows="11" maxlength="<?= $inputsMaxLengths[$input] ?>" required><?= $inputsValues[$input] ?></textarea>
      <small class="form-text text-muted">V&nbsp;těle e-mailu lze používat proměnné, které budou při odeslání e-mailu nahrazeny zvoleným obsahem.</small>
    </div>
    <div class="form-group col-lg-6 col-xl-4">
      <label>Proměnné</label>
      <p class="text-muted">Pro vložení proměnné do těla e-mailu můžete kliknout na její název v&nbsp;následujícím seznamu:</p>
      <ul class="form-text text-muted list-unstyled" id="<?= $formPrefix ?>variables">
        <li><code class="cursor-pointer" data-var="<?= VAR_RECIPIENT_USERNAME ?>"><?= VAR_RECIPIENT_USERNAME ?></code> &ndash; uživatelské jméno příjemce</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_RECIPIENT_EMAIL ?>"><?= VAR_RECIPIENT_EMAIL ?></code> &ndash; e-mail příjemce</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_DATE_CZ ?>"><?= VAR_DATE_CZ ?></code> &ndash; datum, ve kterém dochází k&nbsp;odeslání e-mailu v&nbsp;českém formátu (<?= date(VAR_DATE_CZ_FORMAT) ?>)</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_DATE_EN ?>"><?= VAR_DATE_EN ?></code> &ndash; datum, ve kterém dochází k&nbsp;odeslání e-mailu ve&nbsp;formátu <samp>YYYY-MM-DD</samp> (<?= date(VAR_DATE_EN_FORMAT) ?>)</li>
        <li><code class="cursor-pointer" data-var="<?= VAR_URL ?>"><?= VAR_URL ?></code> &ndash; URL podvodné stránky svázané s&nbsp;e-mailem (povinné)</li>
      </ul>
    </div>
  </div>

  <div class="d-flex justify-content-center">
    <button type="submit" name="<?= $formPrefix . $action ?>" class="btn btn-primary btn-lg ml-1 order-2">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>

    <button type="submit" name="<?= $formPrefix . ACT_PREVIEW ?>" formtarget="_blank" class="btn btn-secondary btn-lg">
      <span data-feather="eye"></span>
      Náhled
    </button>
  </div>
</form>
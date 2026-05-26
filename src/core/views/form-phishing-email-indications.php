<hr>

<div class="text-right">
  <a href="/portal/<?= $urlSection . '/' . ACT_EDIT . '/' . $phishingEmail['id_email'] ?>" class="btn btn-primary" role="button">
    <span data-feather="edit-2"></span>
    Upravit e-mail
  </a>
</div>

<div class="container email-preview">
  <div class="row mb-3 mb-sm-2">
    <div class="col-sm-3 text-sm-right">
      Od:
    </div>
    <div class="col-sm-10 text-monospace">
      <?= $_phishingEmail['sender']; ?>
    </div>
  </div>

  <div class="row mb-3 mb-sm-3">
    <div class="col-sm-3 text-sm-right">
      Předmět:
    </div>
    <div class="col-sm-10 text-monospace">
      <strong><?= $_phishingEmail['subject'] ?></strong>
    </div>
  </div>

  <div class="row mb-3 mb-sm-2">
    <div class="col-sm-3"></div>
    <div class="col-sm-10 text-monospace">
      <?= $_phishingEmail['body'] ?>
    </div>
  </div>
</div>

<div class="text-right">
  <a href="/portal/<?= $urlSection . '/' . ACT_PREVIEW . '/' . $phishingEmail['id_email'] ?>" class="btn btn-info" role="button">
    <span data-feather="eye"></span>
    Náhled<span class="d-none d-lg-inline"> včetně indicií</span>
  </a>
</div>

<hr>

<h4>Nová indicie</h4>
<form method="post" action="/portal/<?= $urlSection . '/' . $action . '/' . $phishingEmail['id_email'] ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="form-row">
    <?php $input = 'position'; ?>
    <div class="form-group col-4 col-sm-3 col-lg-2 col-xl-1">
      <label for="<?= $formPrefix . $input ?>">Pořadí</label>
      <input type="number" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" min="0" max="100" value="<?= !empty($inputsValues[$input]) ? $inputsValues[$input] : $emailIndicationsSum + 1 ?>" required>
    </div>

    <?php $input = 'expression'; ?>
    <div class="form-group col-12 col-sm-13 col-lg-14 col-xl-3">
      <label for="<?= $formPrefix . $input ?>">Zvýrazněný text (nepovinné)</label>
      <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>">
    </div>

    <?php $input = 'title'; ?>
    <div class="form-group col-lg-16 col-xl-3">
      <label for="<?= $formPrefix . $input ?>">Název indicie</label>
      <textarea name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control minh-4-rem" maxlength="<?= $inputsMaxLengths[$input] ?>" required><?= $inputsValues[$input] ?></textarea>
    </div>

    <?php $input = 'description'; ?>
    <div class="form-group col-lg-16 col-xl-8">
      <label for="<?= $formPrefix . $input ?>">Popis (nepovinné)</label>
      <textarea name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control minh-4-rem" maxlength="<?= $inputsMaxLengths[$input] ?>" rows="2"><?= $inputsValues[$input] ?></textarea>
    </div>

    <div class="form-group col-lg-16 col-xl-1 text-right">
      <div class="d-none d-xl-block">
        <label>&nbsp;</label><br>
      </div>

      <button type="submit" name="<?= $formPrefix . ACT_NEW ?>" class="btn btn-primary" title="Přidat" aria-label="Přidat">
        <span data-feather="plus"></span>
      </button>
    </div>
  </div>
</form>

<div class="alert alert-info" role="alert">
  Chcete-li jako indicii označit část mimo samotné tělo e-mailu (např. odesílatele nebo předmět), použijte v&nbsp;poli <span class="badge badge-secondary">Zvýrazněný text</span> některou z&nbsp;následujících proměnných:
  <ul class="mt-2">
    <li><code class="replace-variable cursor-pointer" data-input="#<?= $formPrefix . 'expression' ?>" data-var="<?= VAR_INDICATION_SENDER_NAME ?>"><?= VAR_INDICATION_SENDER_NAME ?></code> &ndash; označí jméno odesílatele,</li>
    <li><code class="replace-variable cursor-pointer" data-input="#<?= $formPrefix . 'expression' ?>" data-var="<?= VAR_INDICATION_SENDER_EMAIL ?>"><?= VAR_INDICATION_SENDER_EMAIL ?></code> &ndash; označí e-mail odesílatele,</li>
    <li><code class="replace-variable cursor-pointer" data-input="#<?= $formPrefix . 'expression' ?>" data-var="<?= VAR_INDICATION_SUBJECT ?>"><?= VAR_INDICATION_SUBJECT ?></code> &ndash; označí předmět.</li>
  </ul>
  Pokud chcete vytvořit <strong>obecnou indicii</strong> pro celý e-mail, ponechte pole <span class="badge badge-secondary">Zvýrazněný text</span> prázdné.
</div>

<?php if (!$existsUrlIndication): ?>
<div class="alert alert-with-icon alert-info" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="link"></span>
  </div>
  <div>
    <h4 class="alert-heading">Indicie k&nbsp;podvodnému odkazu</h4>
    Nezapomeňte přidat indicii k&nbsp;podvodnému odkazu &ndash; stačí uvést v&nbsp;poli <span class="badge badge-secondary">Zvýrazněný text</span> proměnnou <code class="replace-variable cursor-pointer" data-input="#<?= $formPrefix ?>expression" data-var="<?= VAR_URL ?>"><?= VAR_URL ?></code> a&nbsp;vyplnit název indicie. Po přidání dojde k&nbsp;vyznačení indicie u&nbsp;odkazu na podvodnou stránku.
  </div>
</div>
<?php endif; ?>

<?php if ($emailIndicationsSum > 0): ?>
<hr>

<h4>
  <span class="badge badge-<?= $emailIndicationsColor ?> cursor-help" title="Počet indicií pro rozpoznání phishingu přidaných k e-mailu">
    <?= $emailIndicationsSum ?>
  </span>
  Indicie k&nbsp;rozpoznání e-mailu
</h4>
<?php foreach ($emailIndications as $i => $indication): $i++; ?>
<form method="post" action="/portal/<?= $urlSection . '/' . ACT_INDICATIONS . '/' . $phishingEmail['id_email'] ?>" class="border-bottom pb-2 mb-2">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">
  <input type="hidden" name="<?= $formPrefix . ACT_EDIT ?>-id" value="<?= $indication['id_indication'] ?>">

  <div class="form-row">
    <?php $input = 'position'; ?>
    <div class="form-group col-4 col-sm-3 col-lg-2 col-xl-1">
      <label for="<?= $formPrefix . $input . '-' . $i ?>">Pořadí</label>
      <input type="number" name="<?= $formPrefix . ACT_EDIT . '-' . $input ?>" id="<?= $formPrefix . $input . '-' . $i ?>" class="form-control" min="0" max="100" value="<?= $indication[$input] ?>" required>
    </div>

    <?php $input = 'expression'; ?>
    <div class="form-group col-12 col-sm-13 col-lg-14 col-xl-3">
      <label for="<?= $formPrefix . $input . '-' . $i ?>">Zvýrazněný text (nepovinné)</label>
      <input type="text" name="<?= $formPrefix . ACT_EDIT . '-' . $input ?>" id="<?= $formPrefix . $input . '-' . $i ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $indication[$input] ?>">
    </div>

    <?php $input = 'title'; ?>
    <div class="form-group col-lg-16 col-xl-3">
      <label for="<?= $formPrefix . $input . '-' . $i ?>">Název indicie</label>
      <textarea name="<?= $formPrefix . ACT_EDIT . '-' . $input ?>" id="<?= $formPrefix . $input . '-' . $i ?>" class="form-control minh-4-rem" maxlength="<?= $inputsMaxLengths[$input] ?>" rows="2" required><?= $indication[$input] ?></textarea>
    </div>

    <?php $input = 'description'; ?>
    <div class="form-group col-lg-16 col-xl-7">
      <label for="<?= $formPrefix . $input . '-' . $i ?>">Popis (nepovinné)</label>
      <textarea name="<?= $formPrefix . ACT_EDIT . '-' . $input ?>" id="<?= $formPrefix . $input . '-' . $i ?>" class="form-control minh-4-rem" maxlength="<?= $inputsMaxLengths[$input] ?>" rows="2"><?= $indication[$input] ?></textarea>
    </div>

    <div class="form-group col-lg-16 col-xl-2 text-right">
      <div class="d-none d-xl-block">
        <label>&nbsp;</label><br>
      </div>

      <button type="submit" name="<?= $formPrefix . ACT_EDIT ?>" class="btn btn-primary float-right ml-1" title="Upravit" aria-label="Upravit">
        <span data-feather="edit-2"></span>
      </button>

      <a href="#indication-<?= $indication['id_indication'] ?>" class="anchor-link">
        <button type="button" id="indication-26-btn" class="btn btn-info float-right ml-1 mark-indication" title="Zvýraznit" aria-label="Zvýraznit" data-indication="<?= $indication['id_indication'] ?>"<?= ((empty($indication['expression'])) ? ' disabled' : '') ?>>
          <span data-feather="eye"></span>
        </button>
      </a>

      <button type="submit" name="<?= $formPrefix . ACT_DEL ?>" class="btn btn-secondary btn-confirm" data-confirm="Opravdu chcete odstranit tento záznam?" title="Odstranit" aria-label="Odstranit">
        <span data-feather="trash"></span>
      </button>
    </div>
  </div>
</form>
<?php endforeach; ?>
<?php endif; ?>
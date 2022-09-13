<hr>

<div class="container">
  <div class="row mb-3 mb-sm-1">
    <div class="col-sm-3 text-sm-right">
      Od:
    </div>
    <div class="col-10 text-monospace">
      <?= $_phishingEmail['sender']; ?>
    </div>
  </div>

  <div class="row mb-3 mb-sm-2">
    <div class="col-sm-3 text-sm-right">
      Předmět:
    </div>
    <div class="col-10 text-monospace">
      <strong><?= $_phishingEmail['subject'] ?></strong>
    </div>
  </div>

  <div class="row mb-3 mb-sm-2">
    <div class="col-sm-3"></div>
    <div class="col-10 text-monospace">
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
    <div class="form-group col-lg-3">
      <label for="<?= $formPrefix ?>expression">Indicie (podezřelý řetězec)</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>expression" name="<?= $formPrefix ?>expression" maxlength="<?= $inputsMaxLengths['expression'] ?>" value="<?= $inputsValues['expression'] ?>" required>
      <small class="form-text text-muted">Pro označení jména odesílatele lze použít proměnnou <code class="cursor-pointer" onclick="replaceVariable('#<?= $formPrefix; ?>expression', '<?= VAR_INDICATION_SENDER_NAME ?>')"><?= VAR_INDICATION_SENDER_NAME ?></code>, pro e-mail odesílatele <code class="cursor-pointer" onclick="replaceVariable('#<?= $formPrefix; ?>expression', '<?= VAR_INDICATION_SENDER_EMAIL ?>')"><?= VAR_INDICATION_SENDER_EMAIL ?></code> a&nbsp;pro předmět pak <code class="cursor-pointer" onclick="replaceVariable('#<?= $formPrefix; ?>expression', '<?= VAR_INDICATION_SUBJECT ?>')"><?= VAR_INDICATION_SUBJECT ?></code>.</small>
    </div>

    <div class="form-group col-lg-3">
      <label for="<?= $formPrefix ?>title">Nadpis</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>title" name="<?= $formPrefix ?>title" maxlength="<?= $inputsMaxLengths['title'] ?>" value="<?= $inputsValues['title'] ?>" required>
    </div>

    <div class="form-group col-lg-7">
      <label for="<?= $formPrefix ?>description">Popis (nepovinné)</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>description" name="<?= $formPrefix ?>description" maxlength="<?= $inputsMaxLengths['description'] ?>" value="<?= $inputsValues['description'] ?>">
    </div>

    <div class="form-group col-md-3 text-right">
      <label>&nbsp;</label><br>
      <button type="submit" class="btn btn-primary" name="<?= $formPrefix . ACT_NEW; ?>">
        <span data-feather="plus"></span>
        Přidat
      </button>
    </div>
  </div>
</form>

<?php if (count($emailIndications) > 0): ?>
<hr>

<h4>Přehled indicií k&nbsp;této zprávě</h4>
<?php for ($i = 0; $i < count($emailIndications); $i++): ?>
<form method="post" action="/portal/<?= $urlSection . '/' . ACT_INDICATIONS . '/' . $phishingEmail['id_email'] ?>" id="indication-<?= $emailIndications[$i]['id_indication'] ?>-text" onmouseover="markIndication(<?= $emailIndications[$i]['id_indication'] ?>)" onmouseout="markIndication(<?= $emailIndications[$i]['id_indication'] ?>)">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">
  <input type="hidden" name="<?= $formPrefix . ACT_EDIT ?>-id" value="<?= $emailIndications[$i]['id_indication'] ?>">

  <div class="form-row">
    <div class="form-group col-lg-3">
      <label for="<?= $formPrefix ?>expression-<?= ($i + 1) ?>">Indicie (podezřelý řetězec)</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>expression-<?= ($i + 1) ?>" name="<?= $formPrefix . ACT_EDIT ?>-expression" maxlength="<?= $inputsMaxLengths['expression'] ?>" value="<?= $emailIndications[$i]['expression'] ?>" required>
    </div>

    <div class="form-group col-lg-3">
      <label for="<?= $formPrefix ?>title-<?= ($i + 1) ?>">Nadpis</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>title-<?= ($i + 1) ?>" name="<?= $formPrefix . ACT_EDIT ?>-title" maxlength="<?= $inputsMaxLengths['title'] ?>" value="<?= $emailIndications[$i]['title'] ?>" required>
    </div>

    <div class="form-group col-lg-7">
      <label for="<?= $formPrefix ?>description-<?= ($i + 1) ?>">Popis (nepovinné)</label>
      <input type="text" class="form-control" id="<?= $formPrefix ?>description-<?= ($i + 1) ?>" name="<?= $formPrefix . ACT_EDIT ?>-description" maxlength="<?= $inputsMaxLengths['description'] ?>" value="<?= $emailIndications[$i]['description'] ?>">
    </div>

    <div class="form-group col-lg-3 text-right">
      <label>&nbsp;</label><br>

      <button type="submit" class="btn btn-primary float-right ml-1" name="<?= $formPrefix . ACT_EDIT; ?>">
        <span data-feather="edit-2"></span>
        Uložit změny
      </button>

      <button type="submit" class="btn btn-secondary" name="<?= $formPrefix . ACT_DEL; ?>" onclick="if (!confirm('Opravdu chcete odstranit tento záznam?')) return false;">
        <span data-feather="trash"></span>
        Smazat
      </button>
    </div>
  </div>
</form>
<?php endfor; ?>
<?php endif; ?>
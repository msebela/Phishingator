<hr>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $group['id_user_group'] : ''); ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <?php $input = 'name'; ?>
  <div class="form-group">
    <label for="<?= $formPrefix . $input ?>">Název</label>
    <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>" required>
    <small class="form-text text-muted">Název slouží pouze pro vlastní pojmenování skupiny.</small>
  </div>

  <?php $input = 'description'; ?>
  <div class="form-group">
    <label for="<?= $formPrefix . $input ?>">Popis (nepovinné)</label>
    <input type="text" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" maxlength="<?= $inputsMaxLengths[$input] ?>" value="<?= $inputsValues[$input] ?>">
  </div>

  <?php if (!isset($group) || $_group['id_parent_group'] !== NULL): ?>

  <?php $input = 'role'; ?>
  <div class="form-group">
    <label for="<?= $formPrefix . $input ?>" class="my-1 mr-2">Oprávnění</label>
    <select name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="custom-select my-1 mr-sm-2 user-groups-role" required>
      <option value="0">Vyberte&hellip;</option>
      <?php foreach ($roles as $role): ?>
      <option value="<?= $role['id_user_role'] ?>"<?= (($inputsValues[$input] == $role['id_user_role']) ? ' selected': ''); ?>><?= $role['name'] ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php /*
  <?php $input = 'emails-restrictions'; ?>
  <div class="form-group">
    <label for="<?= $formPrefix . $input ?>">Omezení skupiny na konkrétní sadu e-mailů &ndash; ukládá a&nbsp;vztahuje se pouze na oprávnění <span class="badge badge-warning">správce testů</span> (nepovinné)</label>
    <textarea name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control text-monospace" rows="2" maxlength="<?= $inputsMaxLengths[$input] ?>"><?= $inputsValues[$input] ?></textarea>
    <small class="form-text text-muted">Jednotlivé záznamy musí být odděleny znakem <code><?= EMAILS_RESTRICTIONS_SEPARATOR ?></code> a&nbsp;mohou být konkretizovány nejvýše do znaku <code>@</code>, přičemž povolenými doménami jsou <?= $_allowedDomains ?>. Vstup tedy bude například ve formátu <code>@oddeleni-1.phishingator.cz<?= EMAILS_RESTRICTIONS_SEPARATOR ?>@oddeleni-2.phishingator.cz</code>.</small>
  </div>
  */ ?>

  <?php endif; ?>

  <?php $input = 'ldap-groups'; ?>
  <div class="form-group<?php if ($action == ACT_EDIT && !$displayGroups && empty($inputsValues[$input])): ?> d-none<?php endif; ?>" id="groups">
    <label>LDAP skupiny zobrazené v&nbsp;dialogu se seznamem příjemců při <a href="/portal/campaigns/<?= ACT_NEW ?>">vytváření kampaně</a> (pouze pro oprávnění <span class="badge badge-danger">Administrátor</span> a&nbsp;<span class="badge badge-warning">Správce testů</span>)</label>

    <div class="row">
      <?php
        foreach ($groups as $group => $groupDescription):
          $checked = (!is_array($inputsValues[$input]) && in_array($group, explode(LDAP_GROUPS_DELIMITER, $inputsValues[$input]))) || (is_array($inputsValues[$input]) && in_array($group, $inputsValues[$input]));
      ?>
      <div class="col-16 col-md-8 col-xl-4 mb-2">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" name="<?= $formPrefix . $input ?>[]" id="<?= $formPrefix . $input . '-' . $group ?>" value="<?= $group ?>" class="custom-control-input"<?php if ($checked): ?> checked<?php endif; ?>>

          <label class="custom-control-label" for="<?= $formPrefix . $input . '-' . $group ?>">
            <strong><?= $group ?></strong>
            <?php if (!empty($groupDescription)): ?>
            <span class="text-muted"> &ndash; <?= $groupDescription ?></span>
            <?php endif; ?>
          </label>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-right mt-2">
      <button type="button" class="btn btn-outline-secondary btn-sm mark-all-checkboxes" data-checkboxes-group="#groups">
        <span data-feather="users"></span>
        Vybrat všechny skupiny
      </button>
    </div>
  </div>

  <div class="text-center">
    <button type="submit" name="<?= $formPrefix . $action ?>" class="btn btn-primary btn-lg">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
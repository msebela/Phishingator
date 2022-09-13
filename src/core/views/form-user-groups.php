<hr>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $group['id_user_group'] : ''); ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="form-group">
    <label for="<?= $formPrefix ?>name">Název</label>
    <input type="text" class="form-control" id="<?= $formPrefix ?>name" name="<?= $formPrefix ?>name" maxlength="<?= $inputsMaxLengths['name'] ?>" value="<?= $inputsValues['name'] ?>" required>
    <small class="form-text text-muted">Název slouží pouze k&nbsp;identifikaci v&nbsp;rámci tohoto systému.</small>
  </div>

  <div class="form-group">
    <label for="<?= $formPrefix ?>description">Popis (nepovinné)</label>
    <input type="text" class="form-control" id="<?= $formPrefix ?>description" name="<?= $formPrefix ?>description" maxlength="<?= $inputsMaxLengths['description'] ?>" value="<?= $inputsValues['description'] ?>">
  </div>

  <?php if (!isset($group) || $_group['id_parent_group'] !== NULL): ?>
  <div class="form-group">
    <label class="my-1 mr-2" for="<?= $formPrefix ?>role">Oprávnění</label>
    <select class="custom-select my-1 mr-sm-2" id="<?= $formPrefix ?>role" name="<?= $formPrefix ?>role" required>
      <option value="0">Vyberte&hellip;</option>
      <?php foreach ($roles as $role): ?>
      <option value="<?= $role['id_user_role'] ?>"<?= (($inputsValues['role'] == $role['id_user_role']) ? ' selected': ''); ?>><?= $role['name'] ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php /*
  <div class="form-group">
    <label for="<?= $formPrefix ?>emails-restrictions">Omezení skupiny na konkrétní sadu e-mailů (ukládá a&nbsp;vztahuje se pouze na oprávnění <i>správce testů</i>; nepovinné)</label>
    <textarea class="form-control text-monospace" rows="2" id="<?= $formPrefix ?>emails-restrictions" name="<?= $formPrefix ?>emails-restrictions" maxlength="<?= $inputsMaxLengths['emails-restrictions'] ?>"><?= $inputsValues['emails-restrictions'] ?></textarea>
    <small class="form-text text-muted">Jednotlivé záznamy musí být odděleny znakem <code><?= EMAILS_RESTRICTIONS_SEPARATOR ?></code> a&nbsp;mohou být konkretizovány nejvýše do znaku <code>@</code>, přičemž povolenou doménou je <code><?= EMAILS_ALLOWED_DOMAIN ?></code>. Vstup tedy bude například ve formátu <code>@oddeleni-1.phishingator.cz<?= EMAILS_RESTRICTIONS_SEPARATOR ?>@oddeleni-2.phishingator.cz</code>.</small>
  </div>
  */ ?>
  <?php endif; ?>

  <div class="form-group">
    <label for="<?= $formPrefix ?>ldap-groups">Zobrazené LDAP skupiny příjemců v&nbsp;<a href="/portal/campaigns">kampaních</a> (pouze u&nbsp;oprávnění <span class="badge badge-danger">Administrátor</span> a&nbsp;<span class="badge badge-warning">Správce testů</span>):</label>
    <textarea class="form-control text-monospace" rows="2" id="<?= $formPrefix ?>ldap-groups" name="<?= $formPrefix ?>ldap-groups" maxlength="<?= $inputsMaxLengths['ldap-groups'] ?>"><?= $inputsValues['ldap-groups'] ?></textarea>
    <small class="form-text text-muted">Jednotlivé skupiny musí být odděleny znakem <code><?= LDAP_GROUPS_DELIMITER ?></code>. Vstup tedy bude například ve formátu <code>civ<?= LDAP_GROUPS_DELIMITER ?>helps</code>.</small>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary btn-lg" name="<?= $formPrefix . $action; ?>">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
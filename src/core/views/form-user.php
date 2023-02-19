<hr>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $user['id_user'] : ''); ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <?php if ($action == ACT_EDIT && !empty($name)): ?>
  <div class="row mb-2">
    <div class="col-md">
      <div class="form-group">
        <label for="<?= $formPrefix ?>name">Jméno a&nbsp;příjmení</label>
        <input type="text" id="<?= $formPrefix ?>name" class="form-control-plaintext" value="<?= $name ?>" readonly>
      </div>
    </div>

    <div class="col-md">
      <div class="form-group">
        <label for="<?= $formPrefix ?>group">Primární skupina/oddělení</label>
        <input type="text" id="<?= $formPrefix ?>group" class="form-control-plaintext" value="<?= $user['primary_group'] ?>" readonly>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($action == ACT_EDIT && empty($name)): ?>
  <div class="alert alert-with-icon alert-warning" role="alert">
    <div class="alert-icon pr-1">
      <span data-feather="eye-off"></span>
    </div>
    <div>
      <h4 class="alert-heading">Pozor, uživatel již není ve Vaší organizaci!</h4>
      O&nbsp;uživateli se nepodařilo získat žádné informace z&nbsp;LDAP.
    </div>
  </div>
  <?php endif; ?>

  <div class="row mb-2">
    <div class="col-md">
      <div class="form-group">
        <label for="<?= $formPrefix ?>email">E-mail</label>
        <input type="email" name="<?= $formPrefix ?>email" id="<?= $formPrefix ?>email" class="form-control" maxlength="<?= $inputsMaxLengths['email'] ?>" value="<?= $inputsValues['email'] ?>" required>
        <small class="form-text text-muted">E-mail uživatele v&nbsp;doméně <code><?= EMAILS_ALLOWED_DOMAIN ?></code>, na který budou odesílány cvičné phishingové zprávy.</small>
      </div>
    </div>

    <div class="col-md">
      <div class="form-group">
        <label for="<?= $formPrefix ?>id-user-group">Skupina</label>
        <select name="<?= $formPrefix ?>id-user-group" id="<?= $formPrefix ?>id-user-group" class="custom-select" required>
          <option value="nothing">Vyberte&hellip;</option>
          <?php foreach ($groups as $group): ?>
          <option value="<?= $group['id_user_group'] ?>"<?= (($inputsValues['id-user-group'] == $group['id_user_group']) ? ' selected': ''); ?>><?= $group['name'] ?> (opr. <?= $group['role_name'] ?>)</option>
          <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Skupina, na základě které uživatel zdědí oprávnění do systému.</small>
      </div>
    </div>
  </div>

  <?php if ($action == ACT_EDIT): ?>
  <label id="<?= ACT_STATS ?>">Detaily o&nbsp;uživateli</label>

  <div class="table-responsive mb-2">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Registrován</th>
          <th>Poslední přihlášení</th>
          <th>Dobrovolník</th>
          <th>Úspěšnost</th>
          <th>Odesláno e-mailů</th>
          <th>Limit e-mailů</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <?= $user['date_added'] ?>
            <span class="badge badge-<?= $user['voluntary_registration_color'] ?>">
              <?= $user['voluntary_registration'] ?>
            </span>
          </td>
          <td><?= $user['date_login'] ?></td>
          <td>
            <span class="badge badge-<?= $user['recieve_email_color'] ?>">
              <?= $user['recieve_email_text'] ?>
            </span>
            <small><?= $user['date_participation'] ?></small>
          </td>
          <td>
            <span class="badge badge-<?= $user['success_rate_color'] ?>">
              <?= $user['success_rate'] ?>&nbsp;%
            </span>
          </td>
          <td><?= $user['recieved_emails_count'] ?></td>
          <td><?= $user['email_limit'] ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <label>Absolvované phishingové kampaně</label>

  <script src="/<?= CORE_DIR_EXTENSIONS ?>/table-sort.js"></script>

  <div class="table-responsive mb-5">
    <table class="table table-striped table-hover records-list table-sort table-arrows">
      <thead>
        <tr>
          <th colspan="2">Kampaň</th>
          <th colspan="2">Podvodný e-mail</th>
          <th colspan="2">Podvodná stránka</th>
          <th class="data-sort">Odesláno</th>
          <th>Reakce uživatele</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($campaigns as $campaign): ?>
        <tr>
          <td><?= $campaign['name'] ?></td>
          <td>
            <a href="/portal/campaigns/<?= ACT_STATS . '/' . $campaign['id_campaign'] ?>" class="badge badge-info text-nowrap">
              <span data-feather="bar-chart-2"></span>
              Statistika
            </a>
          </td>
          <td><?= $campaign['subject'] ?></td>
          <td>
            <a href="/portal/phishing-emails/<?= ACT_PREVIEW . '/' . $campaign['id_email'] ?>" class="badge badge-secondary" role="button" title="Náhled">
              <span data-feather="eye"></span>
            </a>
          </td>
          <td class="nowrap maxw-20-rem text-truncate">
            <span class="badge badge-<?= $campaign['url_protocol_color'] ?>">
              <?= $campaign['url_protocol'] ?>
            </span><?= str_replace(VAR_RECIPIENT_URL, '<code>' . VAR_RECIPIENT_URL . '</code>', $campaign['url']) ?>
          </td>
          <td>
            <a href="/portal/phishing-websites/<?= ACT_PREVIEW . '/' . $campaign['id_website'] ?>" target="_blank" class="badge badge-secondary" role="button" title="Náhled">
              <span data-feather="eye"></span>
            </a>
          </td>
          <td data-sort="<?= $campaign['date_sent'] ?>"><?= $campaign['date_sent_formatted'] ?></td>
          <td>
            <span class="badge badge-<?= $campaign['user_reaction']['css_color_class'] ?>">
              <?= $campaign['user_reaction']['name'] ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <div class="text-center">
    <button type="submit" class="btn btn-primary btn-lg" name="<?= $formPrefix . $action; ?>">
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
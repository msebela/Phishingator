<?php if (count($campaigns) > 0): ?>
<script src="/<?= CORE_DIR_EXTENSIONS ?>/table-sort.js"></script>

<div class="table-responsive">
  <table class="table table-striped table-hover records-list table-sort table-arrows">
    <thead>
      <tr>
        <th scope="col" class="order-by-desc">#</th>
        <th scope="col">Název</th>
        <th scope="col" class="data-sort">Přidáno</th>
        <th scope="col" class="minw-5-rem">Přidal</th>
        <th scope="col" colspan="2">E-mail</th>
        <th scope="col" class="minw-10-rem">Podvodná stránka</th>
        <th scope="col" class="minw-110-px">Příjemců</th>
        <th scope="col" class="data-sort minw-110-px">Aktivní od</th>
        <th scope="col" class="data-sort minw-110-px">Aktivní do</th>
        <th scope="col" class="minw-8-rem">RT kampaně</th>
        <th scope="col" colspan="3" class="disable-sort"></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($campaigns as $campaign): ?>
      <tr>
        <td>
          <abbr title="Identifikátor v aplikaci <?= WEB_HTML_BASE_TITLE ?>" class="initialism"><?= $campaign['id_campaign'] ?></abbr>
        </td>
        <td><?= $campaign['name'] ?></td>
        <td data-sort="<?= $campaign['date_added'] ?>"><?= insert_nonbreaking_spaces($campaign['date_added_formatted']) ?></td>
        <td>
          <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
          <a href="/portal/users/<?= ACT_EDIT . '/' . $campaign['id_by_user'] ?>">
          <?php endif; ?>
          <span class="badge badge-<?= $campaign['role_color'] ?>">
            <?= $campaign['username'] ?>
          </span>
          <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
          </a>
          <?php endif; ?>
        </td>
        <td>
          <?= $campaign['email_name'] ?>
        </td>
        <td class="pl-0">
          <a href="/portal/phishing-emails/<?= ACT_PREVIEW . '/' . $campaign['id_email'] ?>" class="badge badge-secondary" role="button" title="Náhled">
            <span data-feather="eye"></span>
          </a>
        </td>
        <td>
          <span class="badge badge-<?= $campaign['url_protocol_color'] ?>">
            <?= $campaign['url_protocol'] ?>
          </span><?= $campaign['url'] ?>
        </td>
        <td><?= $campaign['count_recipients'] ?></td>
        <td data-sort="<?= $campaign['active_since'] ?>">
          <span class="badge badge-<?= $campaign['active_since_color'] ?>">
            <?= insert_nonbreaking_spaces($campaign['active_since_formatted']) ?>
          </span>
        </td>
        <td data-sort="<?= $campaign['active_to'] ?>">
          <span class="badge badge-<?= $campaign['active_to_color'] ?>">
            <?= insert_nonbreaking_spaces($campaign['active_to_formatted']) ?>
          </span>
        </td>
        <td>
          <?php if ($campaign['id_ticket']): ?>
          <a href="<?= ITS_URL . $campaign['id_ticket'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm" role="button">
            <span data-feather="eye"></span>
            RT <?= $campaign['id_ticket'] ?>
          </a>
          <?php else: ?>
          &ndash;
          <?php endif; ?>
        </td>
        <td>
          <a href="/portal/<?= $urlSection . '/' . ACT_STATS . '/' . $campaign['id_campaign'] ?>" class="btn btn-info btn-sm text-nowrap" role="button">
            <span data-feather="bar-chart-2"></span>
            Statistika
          </a>
        </td>
        <td>
          <form method="post" action="/portal/<?= $urlSection . '/' . ACT_DEL . '/' . $campaign['id_campaign'] ?>" class="d-inline">
            <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

            <button type="submit" class="btn btn-secondary btn-sm" onclick="if (!confirm('Opravdu chcete odstranit tento záznam?')) return false;">
              <span data-feather="trash"></span>
              Smazat
            </button>
          </form>
        </td>
        <td>
          <a href="/portal/<?= $urlSection . '/' . ACT_EDIT . '/' . $campaign['id_campaign'] ?>" class="btn btn-primary btn-sm" role="button">
            <span data-feather="edit-2"></span>
            Upravit
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="14" class="font-italic">
          <?= $countRecordsText ?>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
<?php else: ?>
<hr>

<p class="font-italic"><?= $countRecordsText ?></p>
<?php endif; ?>

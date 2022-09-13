<hr>

<div class="container">
  <div class="row mb-3 mb-sm-1">
    <div class="col-sm-3 offset-xl-1 text-sm-right">
      Od:
    </div>
    <div class="col-sm-13 col-xl-10 text-monospace">
      <?= $_email['sender'] ?>
    </div>
  </div>

  <div class="row mb-3 mb-sm-2">
    <div class="col-sm-3 offset-xl-1 text-sm-right">
      Předmět:
    </div>
    <div class="col-sm-13 col-xl-10 text-monospace">
      <strong><?= $_email['subject'] ?></strong>
    </div>
  </div>

  <div class="row mb-3 mb-sm-2">
    <div class="col-sm-3 offset-xl-1 text-sm-right">
      Komu:
    </div>
    <div class="col-sm-13 col-xl-10 text-monospace">
      <?= $email['recipient_email'] ?>
    </div>
  </div>

  <div class="row mb-3 mb-sm-2">
    <div class="col-sm-3 offset-xl-1"></div>
    <div class="col-sm-13 col-xl-10 text-monospace">
      <?= $_email['body'] ?>
    </div>
  </div>

  <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
  <div class="row">
    <div class="col-sm-3 offset-xl-1 text-sm-right">
      <?= PHISHING_EMAIL_HEADER_ID ?>:
    </div>
    <div class="col-sm-13 col-xl-10 text-monospace">
      <?= PHISHING_EMAIL_HEADER_VALUE ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php if (!isset($email['indications'])): ?>
<hr>

<div class="text-center">
  <button type="button" class="btn btn-primary btn-lg" onclick="window.close()">
    <span data-feather="x"></span>
    Zavřít
  </button>
</div>
<?php endif; ?>

<?php if (!empty($email['indications'])): ?>
<hr>

<div class="container card-columns">
  <?php for ($i = 0; $i < count($email['indications']); $i++): ?>
  <div id="indication-<?= $email['indications'][$i]['id_indication'] ?>-text" class="card bg-light cursor-pointer" onmouseover="markIndication(<?= $email['indications'][$i]['id_indication'] ?>)" onmouseout="markIndication(<?= $email['indications'][$i]['id_indication'] ?>)">
    <a href="#indication-<?= $email['indications'][$i]['id_indication'] ?>" class="anchor-link">
      <div class="card-body">
        <h5 class="card-title">
          <span class="badge badge-pill badge-dark"><?= ($i + 1) ?>.&nbsp;indicie</span>
          <?= $email['indications'][$i]['title'] ?>
        </h5>
        <p class="card-text"><?= $email['indications'][$i]['description'] ?></p>

        <div class="clearfix">
          <button type="button" id="indication-<?= $email['indications'][$i]['id_indication'] ?>-btn" class="btn btn-sm btn-info float-right" onclick="markIndication(<?= $email['indications'][$i]['id_indication'] ?>)">
            <span data-feather="chevron-up"></span>
            <span>Označit</span>
          </button>
        </div>
      </div>
    </a>
  </div>
  <?php endfor; ?>
</div>
<?php endif; ?>

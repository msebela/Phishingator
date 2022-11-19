<hr>

<div class="container">
  <div class="row">
    <div class="col-5">
      <?php if ($prevPageButton): ?>
      <a href="/portal/<?= $urlSection . '/' . ACT_PREVIEW . $_prevPage; ?>" class="btn btn-primary btn-lg">
        <span data-feather="chevron-left"></span>
        <span class="d-none d-sm-inline">Novější</span>
      </a>
      <?php endif; ?>
    </div>

    <div class="col text-center">
      <span class="badge badge-dark">&#35;<?= $email['id_record'] ?></span><br>
      moje reakce:
    </div>

    <div class="col-5 text-right">
      <?php if ($nextPageButton): ?>
      <a href="/portal/<?= $urlSection . '/' . ACT_PREVIEW ?>?page=<?= $_nextPage ?>" class="btn btn-primary btn-lg">
        <span class="d-none d-sm-inline">Starší</span>
        <span data-feather="chevron-right"></span>
      </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <div class="col-16 text-center">
      <h4>
        <span class="badge badge-<?= $email['user_state']['css_color_class'] ?>">
          <?= $email['user_state']['name'] ?>
        </span>
      </h4>
      odesláno <strong><?= $email['date_sent'] ?></strong>
    </div>
  </div>
</div>

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
</div>

<?php if (!empty($email['indications'])): ?>
<hr>

<div class="container card-columns">
  <?php foreach ($email['indications'] as $i => $indication): ?>
  <div id="indication-<?= $indication['id_indication'] ?>-text" class="card bg-light cursor-pointer" onmouseover="markIndication(<?= $indication['id_indication'] ?>)" onmouseout="markIndication(<?= $indication['id_indication'] ?>)">
    <a href="#indication-<?= $indication['id_indication'] ?>" class="anchor-link">
      <div class="card-body">
        <h5 class="card-title">
          <span class="badge badge-pill badge-dark"><?= ($i + 1) ?>.&nbsp;indicie</span>
          <?= $indication['title'] ?>
        </h5>
        <p class="card-text"><?= $indication['description'] ?></p>

        <div class="clearfix">
          <button type="button" id="indication-<?= $indication['id_indication'] ?>-btn" class="btn btn-sm btn-info float-right" onclick="markIndication(<?= $indication['id_indication'] ?>)">
            <span data-feather="chevron-up"></span>
            <span>Označit</span>
          </button>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

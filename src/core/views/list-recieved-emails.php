<?php if (count($phishingEmails) > 0): ?>
<script src="/<?= CORE_DIR_EXTENSIONS ?>/table-sort.js"></script>

<div class="table-responsive">
  <table class="table table-striped table-hover records-list table-sort table-arrows">
    <thead>
      <tr>
        <th scope="col" class="order-by-desc">#</th>
        <th scope="col">Předmět</th>
        <th scope="col">Jméno odesílatele</th>
        <th scope="col">E-mail odesílatele</th>
        <th scope="col" class="data-sort datetime">Odesláno</th>
        <th scope="col" colspan="2">Moje reakce</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($phishingEmails as $i => $phishingEmail): ?>
      <tr>
        <td>
          <abbr title="Pořadí, v jakém byl cvičný phishing odeslán" class="initialism"><?= (count($phishingEmails) - $i) ?></abbr>
        </td>
        <td><?= $phishingEmail['subject'] ?></td>
        <td class="font-italic"><?= $phishingEmail['sender_name'] ?></td>
        <td>
          <code><?= $phishingEmail['sender_email'] ?></code>
        </td>
        <td data-sort="<?= $phishingEmail['date_sent'] ?>"><?= $phishingEmail['date_sent_formatted'] ?></td>
        <td>
          <span class="badge badge-<?= $phishingEmail['user_state']['css_color_class'] ?>">
            <?= $phishingEmail['user_state']['name'] ?>
          </span>
        </td>
        <td>
          <a href="/portal/<?= $urlSection . '/' . ACT_PREVIEW . (($i > 0) ? '?page=' . $i : '') ?>" class="btn btn-primary btn-sm" role="button">
            <span data-feather="eye"></span>
            Detaily
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7" class="font-italic">
          <?= $countRecordsText ?>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
<?php else: ?>
<hr>

<div class="container">
  <div class="alert alert-with-icon alert-success" role="alert">
    <div class="alert-icon pr-1">
      <span data-feather="send"></span>
    </div>
    <div>
      <h4 class="alert-heading">Zatím žádný cvičný phishing</h4>
      <p>Systém Vám zatím žádný cvičný phishing neposlal, ale pokud jste přihlášeni k&nbsp;jeho odebírání v&nbsp;sekci <a href="/portal/my-participation" class="alert-link">Moje účast v&nbsp;programu</a>, tak určitě někdy dorazí a&nbsp;právě tady jej pak vždy zpětně uvidíte včetně popisu, jak se dal poznat.</p>
    </div>
  </div>
</div>
<?php endif; ?>
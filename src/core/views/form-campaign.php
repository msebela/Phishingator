<hr>
<?php if ($action == ACT_EDIT && strtotime($campaign['active_since']) <= strtotime(date('Y-m-d'))): ?>
<div class="alert alert-with-icon alert-warning" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="activity"></span>
  </div>
  <div>
    <h4 class="alert-heading">Pozor, kampaň již <?= ((strtotime($campaign['active_to']) >= strtotime(date('Y-m-d'))) ? 'běží' : 'proběhla') ?>!</h4>
    Jakákoliv zásadní úprava kampaně po jejím spuštění (tedy po odeslání prvních e-mailů) může způsobit nevratné změny a&nbsp;zkreslení ve statistice a&nbsp;hodnocení kampaně!
  </div>
</div>

<hr>
<?php endif; ?>

<form method="post" action="/portal/<?= $urlSection . '/' . $action . (($action == ACT_EDIT) ? '/' . $campaign['id_campaign'] : ''); ?>">
  <div class="row">
    <div class="col-lg-10 col-xl-11">
      <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

      <div class="row">
        <div class="form-group col-xl-10">
          <label for="<?= $formPrefix ?>name">Název</label>
          <input type="text" class="form-control" id="<?= $formPrefix ?>name" name="<?= $formPrefix ?>name" maxlength="<?= $inputsMaxLengths['name'] ?>" value="<?= $inputsValues['name'] ?>" required>
          <small class="form-text text-muted">Název slouží pouze pro vlastní pojmenování kampaně.</small>
        </div>

        <div class="form-group col-xl-6">
          <label for="<?= $formPrefix ?>id-ticket">Číslo lístku s&nbsp;kampaní (nepovinné)</label>
          <input type="number" class="form-control" id="<?= $formPrefix ?>id-ticket" name="<?= $formPrefix ?>id-ticket" maxlength="<?= $inputsMaxLengths['id-ticket'] ?>" value="<?= $inputsValues['id-ticket'] ?>" min="1">
          <small class="form-text text-muted">Číslo lístku (ticketu) v&nbsp;RT systému ohledně vytvoření phishingové kampaně.</small>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-13">
          <label for="<?= $formPrefix ?>id-email">Rozesílaný podvodný e-mail</label>
          <select class="custom-select" id="<?= $formPrefix ?>id-email" name="<?= $formPrefix ?>id-email" required onchange="setButtonLink(this, '#btn-email-preview', 'emails')">
            <option value="0">Vyberte&hellip;</option>
            <?php foreach ($emails as $email): ?>
              <option value="<?= $email['id_email']; ?>"<?= (($inputsValues['id-email'] == $email['id_email']) ? ' selected': ''); ?>><?= $email['name'] ?></option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted">Podvodný e-mail, který účastníci kampaně dostanou do svých e-mailových schránek a&nbsp;ze kterého se budou moci dostat na podvodnou stránku.</small>
        </div>

        <div class="form-group col-sm-4 col-md-4 col-lg-4 col-xl-3 text-right">
          <label class="d-none d-sm-block">&nbsp;</label>
          <a href="/portal/phishing-emails<?= (($inputsValues['id-email']) ? '/' . ACT_PREVIEW . '/' . $inputsValues['id-email'] : '') ?>" target="_blank" class="btn btn-outline-secondary" id="btn-email-preview">
            <span data-feather="eye"></span>
            Náhled
          </a>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-13">
          <label for="<?= $formPrefix ?>id-website">Podvodná webová stránka přístupná z&nbsp;e-mailu</label>
          <select class="custom-select" id="<?= $formPrefix ?>id-website" name="<?= $formPrefix ?>id-website" required onchange="setButtonLink(this, '#btn-website-preview', 'websites')">
            <option value="0">Vyberte&hellip;</option>
            <?php foreach ($websites as $website): ?>
              <option value="<?= $website['id_website'] ?>"<?= (($inputsValues['id-website'] == $website['id_website']) ? ' selected': ''); ?>><?= $website['url'] . ' &ndash; ' . $website['name'] ?></option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted">Podvodná stránka, na kterou se uživatel dostane z&nbsp;podvodného e-mailu.</small>
        </div>

        <div class="form-group col-sm-4 col-md-4 col-lg-4 col-xl-3 text-right">
          <label class="d-none d-sm-block">&nbsp;</label>
          <a href="/portal/phishing-websites<?= (($inputsValues['id-website']) ? '/' . ACT_PREVIEW . '/' . $inputsValues['id-website'] : '') ?>" target="_blank" class="btn btn-outline-secondary" id="btn-website-preview">
            <span data-feather="eye"></span>
            Náhled
          </a>
        </div>
      </div>

      <div class="form-group">
        <label for="<?= $formPrefix ?>id-onsubmit">Akce po odeslání formuláře</label>
        <select class="custom-select" id="<?= $formPrefix ?>id-onsubmit" name="<?= $formPrefix ?>id-onsubmit" required>
          <option value="0">Vyberte&hellip;</option>
          <?php foreach ($websiteActions as $onsubmit): ?>
            <option value="<?= $onsubmit['id_onsubmit'] ?>"<?= (($inputsValues['id-onsubmit'] == $onsubmit['id_onsubmit'] || ($action == ACT_NEW && empty($inputsValues['id-onsubmit']) && $onsubmit['id_onsubmit'] == CAMPAIGN_DEFAULT_ONSUBMIT_ACTION)) ? ' selected': ''); ?>><?= $onsubmit['name'] ?></option>
          <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Co se stane tehdy, pokud uživatel na podvodné stránce vyplní formulář a&nbsp;klikne na tlačítko pro jeho odeslání.</small>
      </div>

      <div class="form-row">
        <div class="form-group col-md-8">
          <label for="<?= $formPrefix ?>active-since">Start kampaně</label>
          <input type="date" class="form-control" id="<?= $formPrefix ?>active-since" name="<?= $formPrefix ?>active-since" maxlength="<?= $inputsMaxLengths['active-since'] ?>" value="<?= $inputsValues['active-since']; ?>" min="<?= (($action == ACT_NEW) ? date('Y-m-d') : $inputsValues['active-since']) ?>" required>
          <small class="form-text text-muted">V&nbsp;jaký den započne rozesílání e-mailů zvoleným příjemcům a&nbsp;zároveň den, od kterého bude přístupná podvodná stránka.</small>
        </div>

        <div class="form-group col-md-8">
          <label for="<?= $formPrefix ?>time-send-since">Spustit rozesílání e-mailů v&nbsp;čase</label>
          <input type="time" class="form-control" id="<?= $formPrefix ?>time-send-since" name="<?= $formPrefix ?>time-send-since" maxlength="<?= $inputsMaxLengths['time-send-since'] ?>" value="<?= $inputsValues['time-send-since'] ?>" required>
          <small class="form-text text-muted">Od jakého času se zahájí rozesílání vybraného e-mailu zvoleným příjemcům.</small>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-8">
          <label for="<?= $formPrefix ?>active-to">Ukončení kampaně (včetně)</label>
          <input type="date" class="form-control" id="<?= $formPrefix ?>active-to" name="<?= $formPrefix ?>active-to" maxlength="<?= $inputsMaxLengths['active-to'] ?>" value="<?= $inputsValues['active-to']; ?>" min="<?= (($action == ACT_NEW) ? date('Y-m-d') : $inputsValues['active-since']) ?>" required>
          <small class="form-text text-muted">Do jakého data bude kampaň aktivní, tzn. do jakého data budou sbírány výsledky a&nbsp;do jakého data bude přístupná zvolená podvodná stránka.</small>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-xl-5">
      <div class="form-group">
        <label for="<?= $formPrefix ?>recipients">Seznam účastníků kampaně</label>
        <span class="float-right">
          Celkem: <b id="countRecipients"><?= $countRecipients ?></b>
          <button type="button" class="btn btn-outline-secondary btn-sm py-0" onclick="getCountOfEmails('#<?= $formPrefix ?>recipients', '#countRecipients');">
            <span data-feather="refresh-cw"></span>
          </button>
        </span>
        <textarea class="form-control text-monospace" id="<?= $formPrefix ?>recipients" name="<?= $formPrefix ?>recipients" rows="20" required onkeyup="getCountOfEmails('#<?= $formPrefix ?>recipients', '#countRecipients');"><?= $recipients ?></textarea>
        <small class="form-text text-muted">
          Každý z&nbsp;příjemců musí být umístěn na samostatném řádku.
          <button type="button" class="btn btn-secondary btn-sm float-right mt-2 mb-4" data-toggle="modal" data-target="#recipientsDialog">
            <span data-feather="user-check"></span>
            Vybrat příjemce
          </button>
        </small>
      </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id="recipientsDialog" tabindex="-1" role="dialog" aria-labelledby="recipientsDialogTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="recipientsDialogTitle">Výběr příjemců na základě skupin</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">

            <?php if ($recipientsVolunteers != null): ?>
            <div class="container-fluid pb-3 mb-3 border-bottom">
              <div class="row">
                <div class="col-md-8">
                  <h6>
                    <label>
                      <input type="checkbox" onclick="markCheckboxes('#cover-volunteers')">
                      Dobrovolně registrovaní příjemci
                    </label>
                  </h6>
                </div>

                <div class="col-md-8 text-right">
                  <button type="button" class="btn btn-outline-secondary btn-sm" onclick="$('#cover-volunteers').toggleClass('d-none')">
                    <span data-feather="user-check"></span>
                    Seznam příjemců <span class="badge badge-secondary"><?= count($recipientsVolunteers) ?></span>
                  </button>
                </div>
              </div>

              <div id="cover-volunteers" class="d-none">
                <small>Číslo u&nbsp;příjemce udává zbývající počet e-mailů, o&nbsp;které má zájem.</small>
                <div class="d-flex flex-row flex-wrap justify-content-between mt-2">
                  <?php foreach ($recipientsVolunteers as $volunteer): ?>
                  <label class="recipients-list-email text-truncate">
                    <input type="checkbox" name="<?= $volunteer['email'] ?>" value="<?= $volunteer['email'] ?>" onclick="checkSameCheckboxes(this.value, this.checked)"<?php if ($volunteer['checked']): ?> checked<?php endif; ?>>&nbsp;<?= ((!empty($volunteer['color'])) ? '<span class="badge badge-' . $volunteer['color'] . '">' . $volunteer['username'] . '</span>@' . $volunteer['domain'] : $volunteer['email']) . ((!empty($volunteer['email_limit'])) ? '&nbsp;<span class="badge badge-secondary">' . $volunteer['email_limit'] . '</span>' : '') ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <?php endif; ?>

            <?php foreach ($recipientsLdapGroups as $groupName => $group): ?>

            <div class="container-fluid pb-3 mb-3 border-bottom">
              <div class="row">
                <div class="col-md-8">
                  <h6>
                    <label>
                      <input type="checkbox" onclick="markCheckboxes('#group-<?= $groupName ?>')">
                      <span class="font-weight-normal text-muted">LDAP:</span> <?= $groupName ?>
                    </label>
                  </h6>
                </div>

                <div class="col-md-8 text-right">
                  <button type="button" class="btn btn-outline-secondary btn-sm" onclick="$('#group-<?= $groupName ?>').toggleClass('d-none')">
                    <span data-feather="user-check"></span>
                    Seznam příjemců <span class="badge badge-secondary"><?= count($group) ?></span>
                  </button>
                </div>
              </div>

              <div id="group-<?= $groupName ?>" class="d-none">
                <div class="d-flex flex-row flex-wrap justify-content-between mt-2">
                  <?php foreach ($group as $user): ?>
                  <label class="recipients-list-email text-truncate">
                    <input type="checkbox" name="<?= $user['email'] ?>" value="<?= $user['email'] ?>" onclick="checkSameCheckboxes(this.value, this.checked)"<?php if ($user['checked']): ?> checked<?php endif; ?>>&nbsp;<?= ((!empty($user['color'])) ? '<span class="badge badge-' . $user['color'] . '">' . $user['username'] . '</span>@' . $user['domain'] : $user['email']); ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>

            <div class="container-fluid pb-3 mb-3">
              <?php if ($recipientsVolunteers == null && count($recipientsLdapGroups) == 0): ?>
              Administrátorem nebyly nastaveny žádné skupiny.
              <?php else: ?>
              <button type="button" class="btn btn-outline-secondary btn-sm float-right" onclick="markCheckboxes()">
                <span data-feather="users"></span>
                Vybrat všechny příjemce
              </button>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              <span data-feather="x"></span>
              Zavřít
            </button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="insertEmails('#<?= $formPrefix ?>recipients'); getCountOfEmails('#<?= $formPrefix ?>recipients', '#countRecipients');">
              <span data-feather="save"></span>
              Uložit změny
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary btn-lg" name="<?= $formPrefix . $action; ?>"<?php if ($action == ACT_EDIT && strtotime($campaign['active_since']) <= strtotime(date('Y-m-d'))): ?> onclick="if (!confirm('Opravdu chcete upravit kampaň i přesto, že může mít vliv na statistiku a hodnocení kampaně?')) return false;"<?php endif; ?>>
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
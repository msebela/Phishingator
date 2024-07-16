<hr>
<?php if ($action == ACT_EDIT && strtotime($campaign['date_active_since'] . ' ' . $campaign['time_active_since']) <= strtotime('now')): ?>
<div class="alert alert-with-icon alert-warning" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="activity"></span>
  </div>
  <div>
    <h4 class="alert-heading">Pozor, kampaň již <?= ((strtotime($campaign['date_active_to'] . ' ' . $campaign['time_active_to']) >= strtotime('now')) ? 'běží' : 'proběhla') ?>!</h4>
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
          <select name="<?= $formPrefix ?>id-email" id="<?= $formPrefix ?>id-email" class="custom-select set-preview-btn" data-preview-btn="#btn-email-preview" data-preview-link="/portal/phishing-emails" required>
            <option value="0">Vyberte&hellip;</option>
            <?php foreach ($emails as $email): ?>
            <option value="<?= $email['id_email']; ?>"<?= (($inputsValues['id-email'] == $email['id_email']) ? ' selected': ''); ?>><?= $email['name'] ?></option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted">Podvodný e-mail, který bude doručen zvoleným příjemcům a&nbsp;ze kterého se budou moci dostat na podvodnou stránku.</small>
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
          <select name="<?= $formPrefix ?>id-website" id="<?= $formPrefix ?>id-website" class="custom-select set-preview-btn" data-preview-btn="#btn-website-preview" data-preview-link="/portal/phishing-websites" required>
            <option value="0">Vyberte&hellip;</option>
            <?php foreach ($websites as $website): ?>
            <option value="<?= $website['id_website'] ?>"<?= (($inputsValues['id-website'] == $website['id_website']) ? ' selected': ''); ?>><?= $website['name'] . ' &ndash; ' . $website['url'] ?></option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted">Podvodná stránka, na kterou se uživatel dostane přes odkaz v&nbsp;podvodném e-mailu.</small>
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
          <label for="<?= $formPrefix ?>date-active-since">Datum zahájení kampaně</label>
          <input type="date" class="form-control" id="<?= $formPrefix ?>date-active-since" name="<?= $formPrefix ?>date-active-since" maxlength="<?= $inputsMaxLengths['date-active-since'] ?>" value="<?= $inputsValues['date-active-since']; ?>" min="<?= (($action == ACT_NEW) ? date('Y-m-d') : $inputsValues['date-active-since']) ?>" required>
          <small class="form-text text-muted">Den, kdy započne rozesílání e-mailů zvoleným příjemcům a&nbsp;zároveň den, od kterého bude přístupná podvodná stránka.</small>
        </div>

        <div class="form-group col-md-8">
          <label for="<?= $formPrefix ?>time-active-since">Čas zahájení</label>
          <input type="time" class="form-control" id="<?= $formPrefix ?>time-active-since" name="<?= $formPrefix ?>time-active-since" maxlength="<?= $inputsMaxLengths['time-active-since'] ?>" value="<?= $inputsValues['time-active-since'] ?>" required>
          <small class="form-text text-muted">Čas, kdy se zahájí rozesílání vybraného e-mailu zvoleným příjemcům a&nbsp;kdy začne být dostupná podvodná stránka.</small>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-8">
          <label for="<?= $formPrefix ?>date-active-to">Datum ukončení kampaně</label>
          <input type="date" class="form-control" id="<?= $formPrefix ?>date-active-to" name="<?= $formPrefix ?>date-active-to" maxlength="<?= $inputsMaxLengths['date-active-to'] ?>" value="<?= $inputsValues['date-active-to']; ?>" min="<?= (($action == ACT_NEW) ? date('Y-m-d') : $inputsValues['date-active-since']) ?>" required>
          <small class="form-text text-muted">Do jakého data bude kampaň aktivní, tzn. do jakého data budou sbírány výsledky a&nbsp;do jakého data bude přístupná zvolená podvodná stránka.</small>
        </div>

        <div class="form-group col-md-8">
          <label for="<?= $formPrefix ?>time-active-to">Čas ukončení</label>
          <input type="time" class="form-control" id="<?= $formPrefix ?>time-active-to" name="<?= $formPrefix ?>time-active-to" maxlength="<?= $inputsMaxLengths['time-active-to'] ?>" value="<?= ($action == ACT_NEW && empty($inputsValues['time-active-to'])) ? '23:59' : $inputsValues['time-active-to'] ?>" required>
          <small class="form-text text-muted">Čas, kdy přestane být podvodná stránka dostupná a&nbsp;uživatel bude při jejím navštívení obratem přesměrován na vzdělávací stránku.</small>
        </div>
      </div>

      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="<?= $formPrefix ?>send-users-notification" name="<?= $formPrefix ?>send-users-notification"<?= (($action == ACT_NEW && empty($inputsValues['send-users-notification']) || $inputsValues['send-users-notification']) ? ' checked' : ''); ?>>
          <label class="custom-control-label" for="<?= $formPrefix ?>send-users-notification">Po ukončení kampaně odeslat příjemcům notifikaci</label>
          <small class="form-text text-muted">Notifikace obsahuje informaci o&nbsp;absolvování cvičného phishingu a&nbsp;odkaz na vzdělávací stránku s&nbsp;indiciemi.</small>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-xl-5">
      <div class="form-group">
        <label for="<?= $formPrefix ?>recipients">Seznam příjemců</label>
        <span class="float-right">
          Celkem: <strong id="countRecipients"><?= $countRecipients ?></strong>
        </span>
        <textarea name="<?= $formPrefix ?>recipients" id="<?= $formPrefix ?>recipients" class="form-control text-monospace" rows="24" required><?= $recipients ?></textarea>
        <div class="form-text text-muted">
          <div>
            <small>Každý z&nbsp;příjemců musí být umístěn na samostatném řádku.</small>
          </div>

          <div class="float-right mt-2 mb-4 text-right">
            <input type="file" id="file-recipients" class="d-none">

            <button type="button" class="btn btn-secondary btn-sm mb-1 import-recipients">
              <span data-feather="upload"></span>
              Importovat příjemce
            </button>

            <button type="button" class="btn btn-secondary btn-sm mb-1" data-toggle="modal" data-target="#recipientsDialog">
              <span data-feather="user-check"></span>
              Vybrat příjemce
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="recipientsDialog" tabindex="-1" role="dialog" aria-labelledby="recipientsDialogTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="recipientsDialogTitle">Výběr příjemců na základě skupin</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="container-fluid">
              <?php if ($recipientsVolunteers == null && count($recipientsLdapGroups) == 0): ?>
              Administrátorem nebyly nastaveny žádné skupiny.
              <?php else: ?>
              <div class="text-right">
                <button type="button" class="btn btn-outline-secondary btn-sm mark-checkboxes" data-checkboxes-group="">
                  <span data-feather="user-check"></span>
                  Vybrat všechny příjemce
                </button>

                <button type="button" class="btn btn-outline-secondary btn-sm expand-all-groups" data-toggle="button" aria-pressed="false">
                  <span data-feather="users"></span>
                  Rozbalit všechny skupiny
                </button>
              </div>
              <?php endif; ?>
            </div>

            <?php if ($recipientsVolunteers != null): ?>
            <div class="container-fluid pt-3 mt-3 border-top">
              <div class="row">
                <div class="col-md-11">
                  <h6>
                    <label>
                      <input type="checkbox" class="mark-checkboxes" data-checkboxes-group="#cover-volunteers">
                      Dobrovolně registrovaní příjemci
                    </label>
                  </h6>
                </div>

                <div class="col-md-5 text-right">
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-toggle-display" data-toggle="#cover-volunteers">
                    <span data-feather="user-check"></span>
                    Seznam příjemců <span class="badge badge-secondary"><?= count($recipientsVolunteers) ?></span>
                  </button>
                </div>
              </div>

              <div id="cover-volunteers" class="group-recipients d-none">
                <small>Číslo u&nbsp;příjemce udává zbývající počet e-mailů, o&nbsp;které má zájem.</small>
                <div class="d-flex flex-row flex-wrap justify-content-between mt-2">
                  <?php foreach ($recipientsVolunteers as $volunteer): ?>
                  <label class="recipients-list-email text-truncate">
                    <input type="checkbox" value="<?= $volunteer['email'] ?>" class="mark-same-checkboxes"<?php if ($volunteer['checked']): ?> checked<?php endif; ?>>&nbsp;<?= ((!empty($volunteer['color'])) ? '<span class="badge badge-' . $volunteer['color'] . '">' . $volunteer['username'] . '</span>@' . $volunteer['domain'] : $volunteer['email']) . ((!empty($volunteer['email_limit'])) ? '&nbsp;<span class="badge badge-secondary">' . $volunteer['email_limit'] . '</span>' : '') ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <?php endif; ?>

            <?php foreach ($recipientsLdapGroups as $groupName => $groupUsers): ?>

            <div class="container-fluid pt-3 mt-3 border-top">
              <div class="row">
                <div class="col-md-11">
                  <h6>
                    <label>
                      <input type="checkbox" class="mark-checkboxes" data-checkboxes-group="#group-<?= remove_special_chars($groupName) ?>">
                      <span class="font-weight-normal text-muted">LDAP:</span> <?= stripcslashes($groupName) ?>
                    </label>
                  </h6>
                </div>

                <div class="col-md-5 text-right">
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-toggle-display" data-toggle="#group-<?= remove_special_chars($groupName) ?>">
                    <span data-feather="user-check"></span>
                    Seznam příjemců <span class="badge badge-secondary"><?= count($groupUsers) ?></span>
                  </button>
                </div>
              </div>

              <div id="group-<?= remove_special_chars($groupName) ?>" class="group-recipients d-none">
                <div class="d-flex flex-row flex-wrap justify-content-between mt-2">
                  <?php foreach ($groupUsers as $user): ?>
                  <label class="text-truncate">
                    <input type="checkbox" value="<?= $user['email'] ?>" class="mark-same-checkboxes"<?php if ($user['checked']): ?> checked<?php endif; ?>>&nbsp;<?= ((!empty($user['color'])) ? '<span class="badge badge-' . $user['color'] . '">' . $user['username'] . '</span>@' . $user['domain'] : $user['email']); ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              <span data-feather="x"></span>
              Zavřít
            </button>

            <button type="button" class="btn btn-primary insert-recipients-emails" data-dismiss="modal">
              <span data-feather="save"></span>
              Uložit změny
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary btn-lg btn-confirm" name="<?= $formPrefix . $action; ?>"<?php if ($action == ACT_EDIT && strtotime($campaign['date_active_since'] . ' ' . $campaign['time_active_since']) <= strtotime('now')): ?> data-confirm="Opravdu chcete upravit kampaň i přesto, že může mít vliv na statistiku a hodnocení kampaně?"<?php endif; ?>>
      <span data-feather="save"></span>
      <?= ($action == ACT_NEW) ? 'Přidat' : 'Uložit změny'; ?>
    </button>
  </div>
</form>
<hr>

<?php if (strtotime($campaign['active_to']) >= strtotime(date('Y-m-d'))): ?>
<div class="alert alert-with-icon alert-warning" role="alert">
  <div class="alert-icon pr-1">
    <span data-feather="activity"></span>
  </div>
  <div>
    <h4 class="alert-heading">Pozor, kampaň stále běží!</h4>
    Zobrazená data jsou průběžné výsledky, nikoliv konečné.
  </div>
</div>

<hr>
<?php endif; ?>

<h3>Základní informace</h3>
<div class="table-responsive mb-5">
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Název</th>
        <th>Přidáno</th>
        <th>Přidal</th>
        <th colspan="2">Podvodný e-mail</th>
        <th>Podvodná stránka</th>
        <th colspan="2">URL podvodné stránky</th>
        <th>Odesláno e-mailů</th>
        <th>Spuštění rozesílání</th>
        <th>Aktivní od</th>
        <th>Aktivní do</th>
        <th>RT kampaně</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= $campaign['name'] ?></td>
        <td><?= insert_nonbreaking_spaces($campaign['date_added']) ?></td>
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
        <td><?= $campaign['email_name'] ?></td>
        <td>
          <a href="/portal/phishing-emails/<?= ACT_PREVIEW . '/' . $campaign['id_email'] ?>" class="badge badge-secondary" title="Náhled">
            <span data-feather="eye"></span>
          </a>
        </td>
        <td><?= $campaign['website_name'] ?></td>
        <td class="nowrap">
          <span class="badge badge-<?= $campaign['url_protocol_color'] ?>"><?= $campaign['url_protocol'] ?></span><?= $campaign['url'] ?>
        </td>
        <td>
          <a href="/portal/phishing-websites/<?= ACT_PREVIEW . '/' . $campaign['id_website'] ?>" target="_blank" class="badge badge-secondary" title="Náhled">
            <span data-feather="eye"></span>
          </a>
        </td>
        <td><?= $campaign['sent_emails'] . '/' . $campaign['count_recipients'] ?></td>
        <td>každý den od&nbsp;<?= $campaign['time_send_since'] ?></td>
        <td>
          <span class="badge badge-<?= $campaign['active_since_color'] ?>"><?= insert_nonbreaking_spaces($campaign['active_since_formatted']) ?></span>
        </td>
        <td>
          <span class="badge badge-<?= $campaign['active_to_color'] ?>"><?= insert_nonbreaking_spaces($campaign['active_to_formatted']) ?></span>
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
      </tr>
    </tbody>
  </table>
</div>

<div class="d-flex flex-wrap justify-content-around mb-4">
  <div class="chart-wrapper sm">
    <h4>Konečné akce uživatelů v&nbsp;kampani</h4>
    <canvas class="mt-4" id="campaign-end-actions-pie-chart"></canvas>

    <p class="text-center mt-5">
      <a href="?<?= ACT_STATS_END_ACTIONS ?>#list" class="btn btn-lg btn-info mt-5 text-wrap" role="button">
        <span data-feather="archive"></span>
        Tabulka konečných akcí
      </a>
    </p>
  </div>

  <div class="chart-wrapper">
    <h4>Konečné akce v&nbsp;kampani dle skupiny</h4>
    <div class="table-responsive">
      <canvas class="mt-4" id="campaign-actions-bar-chart"></canvas>
    </div>
  </div>

  <div class="chart-wrapper sm">
    <h4>Provedené akce v&nbsp;kampani</h4>
    <canvas class="mt-4" id="campaign-actions-pie-chart"></canvas>

    <p class="text-center mt-5">
      <a href="?<?= ACT_STATS_ALL_ACTIONS ?>#list" class="btn btn-lg btn-info mt-5 text-wrap" role="button">
        <span data-feather="activity"></span>
        Tabulka všech provedených akcí
      </a>
    </p>
  </div>
</div>

<?php if (isset($_GET[ACT_STATS_END_ACTIONS]) || isset($_GET[ACT_STATS_ALL_ACTIONS])): ?>
<a id="list"></a>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mt-5 mb-3 border-bottom">
  <h3><?php if (isset($_GET[ACT_STATS_END_ACTIONS])): ?>Konečné akce<?php else: ?>Akce na podvodné stránce podle času<?php endif; ?></h3>

  <div class="btn-toolbar mb-2 mb-md-0 align-items-center">
    <div class="custom-control custom-checkbox mr-2">
      <input type="checkbox" class="custom-control-input" id="blur-identity">
      <label class="custom-control-label" for="blur-identity" onclick="blurIdentity()">Rozmazat identity</label>
    </div>

    <?php if (isset($capturedData)): ?>
    <button id="exportDropdown" type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <span data-feather="save"></span>
      Export
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
      <a class="dropdown-item" href="/portal/<?= $urlSection . '/' . ACT_EXPORT ?>?data=users-end-actions&amp;id=<?= $campaign['id_campaign'] ?>">Konečné akce uživatelů <code>[CSV]</code></a>
      <a class="dropdown-item" href="/portal/<?= $urlSection . '/' . ACT_EXPORT ?>?data=all-users-actions&amp;id=<?= $campaign['id_campaign'] ?>">Všechna zaznamenaná data <code>[CSV]</code></a>
      <a class="dropdown-item" href="/portal/<?= $urlSection . '/' . ACT_EXPORT ?>?data=count-users-actions&amp;id=<?= $campaign['id_campaign'] ?>">Počet akcí každého uživatele <code>[CSV]</code></a>
      <a class="dropdown-item" href="/portal/<?= $urlSection . '/' . ACT_EXPORT ?>?data=all&amp;id=<?= $campaign['id_campaign'] ?>">Vše v&nbsp;archivu <code>[ZIP]</code></a>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if (isset($_GET[ACT_STATS_END_ACTIONS])): ?>

<p>Výpis konečných akcí všech uživatelů, přičemž jako konečná akce je myšlena ta nejzávažnější akce, kterou mohl uživatel v&nbsp;kampani provést.</p>

<div class="row">
  <?php foreach ($endActionsLegend as $idAction => $nameAction): ?>
  <div class="col-xl-4">
    <h4>
      <span class="badge badge-<?= $endActionsLegendCssClasses[$idAction] ?>"><?= $nameAction ?></span>
    </h4>
    <br>

    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Uživatel</th>
            <th>Pracoviště</th>
            <th colspan="3">Záznamy</th>
          </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($endActions as $data): ?>
          <?php if ($data['id_action'] != $idAction) continue; ?>
          <tr class="campaign-user-action">
            <td>
              <small><?= $i++; ?></small>
            </td>
            <td class="identity">
              <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
              <a href="/portal/users/<?= ACT_EDIT . '/' . $data['id_user'] ?>">
                <span title="<?= $data['used_email'] ?>" data-toggle="tooltip"><?= $data['username'] ?></span>
              </a>
              <?php else: ?>
              <abbr title="<?= $data['used_email'] ?>" data-toggle="tooltip"><?= $data['username'] ?></abbr>
              <?php endif; ?>
            </td>
            <td>
              <small><?= strtoupper(StatsModel::removeAllFromEmailExceptSubdomains($data['used_email'])) ?></small>
            </td>
            <td>
              <form method="post" action="/portal/<?= $urlSection . '/' . ACT_STATS . '/' . $campaign['id_campaign'] ?>">
                <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

                <input type="hidden" name="<?= ACT_STATS_REPORT_PHISH ?>" value="<?= $data['id_captured_data'] ?>">

                <div class="text-success<?php if ($data['reported'] == 0): ?> user-phishing-report<?php endif; ?>" data-toggle="tooltip" data-placement="top" data-html="true" data-original-title="Uživatel nahlásil pokus o&nbsp;cvičný phishing<br>(např. na helpdesk, správci IT, &hellip;)">
                  <button type="submit" class="border-0 bg-transparent" onclick="if (!confirm('Opravdu chcete u uživatele upravit nastavení o nahlášení cvičného phishingu?')) return false;">
                    <span data-feather="message-circle" class="text-purple"></span>
                  </button>
                </div>
              </form>
            </td>
            <td>
              <?php if (isset($testPageData[$data['id_user']])): ?>
              <div class="cursor-help text-success" data-toggle="tooltip" data-placement="top" data-html="true" data-original-title="Uživatel navštívil stránku o&nbsp;absolvování phishingu<br><?= $testPageData[$data['id_user']] ?>">
                <span data-feather="flag"></span>
              </div>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($idAction != CAMPAIGN_NO_REACTION_ID): ?>
              <a href="?<?= ACT_STATS_ALL_ACTIONS ?>&amp;rows=<?= $data['id_user'] ?>#d<?= $data['id_captured_data'] ?>" class="text-dark">
                <span data-feather="eye"></span>
              </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php elseif (isset($_GET[ACT_STATS_ALL_ACTIONS])): ?>

<?php if (count($capturedData) > 0): ?>
<p>Výpis všech provedených akcí na podvodné stránce, která je přístupná ze zasílaného podvodného e-mailu. Zastoupení všech těchto akcí znázorňuje také graf <i>Provedené akce v&nbsp;kampani</i>.</p>

<script src="/<?= CORE_DIR_EXTENSIONS ?>/table-sort.js"></script>

<div class="table-responsive">
  <table class="table table-striped table-hover records-list table-sort table-arrows">
    <thead>
      <tr>
        <th class="order-by-desc">#</th>
        <th colspan="2" class="minw-5-rem">Uživatel</th>
        <th>Akce</th>
        <th class="data-sort datetime">Datum a&nbsp;čas</th>
        <th>IP adresa</th>
        <th>User agent</th>
        <th>HTTP POST data v&nbsp;JSON</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = count($capturedData); foreach ($capturedData as $data): ?>
      <tr id="r<?= $i ?>"<?php if (isset($_GET['rows']) && $_GET['rows'] == $data['id_user']): ?> class="table-primary"<?php endif; ?>>
        <td id="d<?= $data['id_captured_data'] ?>">
          <a href="#r<?= $i ?>" class="text-dark">
            <abbr title="Identifikátor a odkaz na tento záznam v rámci této kampaně" class="initialism cursor-help"><?= $i--; ?></abbr>
          </a>
        </td>
        <td class="identity">
          <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
          <a href="/portal/users/<?= ACT_EDIT . '/' . $data['id_user'] ?>">
            <span title="<?= $data['used_email'] ?>" data-toggle="tooltip"><?= $data['username'] ?></span>
          </a>
          <?php else: ?>
          <abbr title="<?= $data['used_email'] ?>" data-toggle="tooltip"><?= $data['username'] ?></abbr>
          <?php endif; ?>
        </td>
        <td>
          <small><?= strtoupper(StatsModel::removeAllFromEmailExceptSubdomains($data['used_email'])) ?></small>
        </td>
        <td>
          <span class="badge badge-<?= $data['css_color_class'] ?>"><?= $data['name'] ?></span>
          <?php if ($data['reported'] == 1): ?>
          <div class="text-success d-inline" data-toggle="tooltip" data-placement="top" data-html="true" data-original-title="Uživatel nahlásil pokus o&nbsp;cvičný phishing<br>(např. na helpdesk, lokálnímu správci, &hellip;)">
            <span class="badge badge-purple">ohlášeno</span>
          </div>
          <?php endif; ?>
        </td>
        <td data-sort="<?= $data['visit_datetime'] ?>"><?= $data['visit_datetime_formatted'] ?></td>
        <td class="identity"><?= $data['ip'] ?></td>
        <td class="maxw-40-rem text-truncate">
          <small class="cursor-help" title="<?= $data['browser_fingerprint'] ?>" data-toggle="tooltip" data-placement="left">
            <?= $data['browser_fingerprint'] ?>
          </small>
        </td>
        <td class="maxw-20-rem text-truncate identity">
          <?php if (!empty($data['data_json'])): ?>
          <code class="cursor-help" title="<?= $data['data_json'] ?>" data-toggle="tooltip" data-placement="left">
            <?= $data['data_json'] ?>
          </code>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<p>Podvodná stránka zatím nebyla navštívena žádným z&nbsp;příjemců kampaně.</p>
<?php endif; ?>

<?php endif; ?>

<script src="/<?= CORE_DIR_EXTENSIONS ?>/chartjs/chart.min.js?3.9.1"></script>
<script src="/<?= CORE_DIR_EXTENSIONS ?>/chartjs/chartjs-plugin-datalabels.min.js?2.1.0"></script>
<script>
  let campaignChart1 = new Chart(document.getElementById('campaign-end-actions-pie-chart'), {
    plugins: [ChartDataLabels],
    type: 'doughnut',
    data: {
      labels: [<?= $_chartLegend ?>],
      datasets: [{
        data: [<?= $chartDataUserEndAction ?>],
        backgroundColor: [<?= $_chartColors ?>],
        datalabels: {
          anchor: function(context) {
            let totalCount = 0;
            let countLowValues = 0;
            const minValue = 5;

            for (let i = 0; i < context.dataset.data.length; i++) {
              totalCount += context.dataset.data[i];

              if (context.dataset.data[i] < minValue) {
                countLowValues++;
              }
            }

            if (countLowValues > 2 && totalCount > 20 && context.dataset.data[context.dataIndex] < minValue) {
              return context.dataIndex % 2 ? 'start' : 'end';
            }

            return 'end';
          },
          clamp: 'true'
          /*,
          align: function(context) {
            if (context.dataIndex == 0) {
              return 'left';
            }
            else if (context.dataIndex == 1) {
              return 'bottom';
            }
            else if (context.dataIndex == 2) {
              return 'right';
            }
          },
          offset: function(context) {
            if (context.dataIndex == 1) {
              return 45;
            }
          }*/
        }
      }]
    },
    options: {
      responsive: true,
      aspectRatio: 2,
      layout: {padding: {top: 10, left: 15, bottom: 10}},
      plugins: {
        legend: {position: 'right'},
        datalabels: {
          backgroundColor: function(context) {
            return context.dataset.backgroundColor;
          },
          borderColor: '#fff',
          borderRadius: 25,
          borderWidth: 2,
          padding: 5,
          color: '#fff',
          font: {weight: 'bold'},
          textAlign: 'center',
          display: function(context) {
            let dataset = context.dataset;
            let value = dataset.data[context.dataIndex];

            return value > 0;
          },
          formatter: function(value, context) {
            let totalCount = 0;

            for (let i = 0; i < context.dataset.data.length; i++) {
              totalCount += context.dataset.data[i];
            }

            return value + ' / ' + Math.round((value * 100 / totalCount) * 10) / 10 + ' %';
          }
        }
      }}
  });

  let campaignChart2 = new Chart(document.getElementById('campaign-actions-pie-chart'), {
    plugins: [ChartDataLabels],
    type: 'doughnut',
    data: {
      labels: [<?= $_chartLegend ?>],
      datasets: [{
        data: [<?= $chartData ?>],
        backgroundColor: [<?= $_chartColors ?>],
        datalabels: {
          anchor: 'end', clamp: 'true'
        }
      }]
    },
    options: {
      responsive: true,
      aspectRatio: 2,
      layout: {padding: {top: 10, left: 15, bottom: 10}},
      plugins: {
        legend: {position: 'right'},
        datalabels: {
          backgroundColor: function(context) {
            return context.dataset.backgroundColor;
          },
          borderColor: '#fff',
          borderRadius: 25,
          borderWidth: 2,
          padding: 5,
          color: '#fff',
          font: {weight: 'bold'},
          textAlign: 'center',
          display: function(context) {
            let dataset = context.dataset;
            let value = dataset.data[context.dataIndex];

            return value > 0;
          },
          formatter: function(value, context) {
            let totalCount = 0;

            for (let i = 0; i < context.dataset.data.length; i++) {
              totalCount += context.dataset.data[i];
            }

            return value + ' / ' + Math.round((value * 100 / totalCount) * 10) / 10 + ' %';
          }
        }
      }}
  });

  let campaignChart3 = new Chart(document.getElementById('campaign-actions-bar-chart'), {
    type: 'bar',
    data: {
      labels: [<?= $_barChartLegendDesc ?>],
      datasets: [
        <?php for ($i = 1; $i <= count($barChartLegend); $i++): ?>
        {
          label: '<?= $barChartLegend[$i] ?>',
          backgroundColor: '#<?= $barChartLegendColors[$i] ?>',
          data: [<?= $barChartLegendData[$i] ?>]
        },
        <?php endfor; ?>
      ]
    },
    options: {
      responsive: false,
      tooltips: {mode: 'index', intersect: false},
      maintainAspectRatio: true,
      plugins: {
        legend: {position: '<?= ((count(explode(',', $barChartLegendDesc)) > 2) ? 'bottom' : 'right') ?>'}
      },
      scales: {
        x: {
          stacked: true, ticks: {autoSkip: false}
        },
        y: {
          stacked: true, ticks: {min: 0, precision: 0}
        }
      }
    }
  });
</script>
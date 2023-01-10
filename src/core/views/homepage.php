<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Úvodní stránka</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="help-circle"></span>
      Nápověda
    </a>
  </div>
</div>

<p>Vítejte v&nbsp;systému pro <strong>rozesílání cvičných phishingových zpráv</strong>.</p>
<?php if (PermissionsModel::getUserPermission() == PERMISSION_ADMIN || PermissionsModel::getUserPermission() == PERMISSION_TEST_MANAGER): ?>
  <p><strong>Menu</strong>, které máte k&nbsp;dispozici, je generováno v&nbsp;závislosti na tom, <strong>jakou roli</strong> máte právě nastavenou (viz tlačítko <span class="badge badge-info">Změnit roli</span> v&nbsp;<strong>pravé horní části</strong> obrazovky).</p>
<?php endif; ?>

<div class="card-group cards-homepage pb-2 mb-3">
  <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
  <div class="card bg-light text-center">
    <a href="/portal/campaigns">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countCampaigns ?></span><br>
          <?= $countCampaignsText ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/users">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countUsers ?></span><br>
          <?= $countUsersText ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/users?records=<?= $countVolunteers ?>&amp;only-volunteers=1">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countVolunteers ?></span><br>
          <?= $countVolunteersText ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/phishing-emails">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countSentEmails ?></span><br>
          <?= $countSentEmailsText ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/phishing-websites">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countPhishingWebsites ?></span><br>
          <?= $countPhishingWebsitesText ?>
        </h4>
      </div>
    </a>
  </div>

  <?php elseif (PermissionsModel::getUserRole() == PERMISSION_TEST_MANAGER): ?>

  <div class="card bg-light text-center">
    <a href="/portal/campaigns">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countCampaigns ?></span><br>
          <?= $countCampaignsText ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/phishing-emails">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countSentEmails ?></span><br>
          <?= $countSentEmailsText ?>
        </h4>
      </div>
    </a>
  </div>

  <?php else: ?>

  <div class="card bg-light text-center">
    <a href="/portal/recieved-phishing-emails">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= $countRecievedEmails ?></span><br>
          <?= $countRecievedEmailsText ?>
        </h4>
      </div>
    </a>
  </div>

  <?php if ($countRecievedEmails > 0): ?>
  <div class="card bg-light text-center">
    <div class="card-body">
      <h4 class="card-title mb-0">
        <span class="badge-pill badge-dark py-1"><?= $countSuccessRate ?>%</span><br>
        úspěšnost v&nbsp;odhalování phishingu
      </h4>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; ?>
</div>

<hr>

<div class="d-flex flex-wrap justify-content-around">
  <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
  <div class="chart-wrapper">
    <h3>Konečné akce uživatelů v&nbsp;kampaních</h3>
    <p>V&nbsp;potaz se berou všechna nasbíraná data, přičemž přednost má ta vážnější akce, kterou mohl uživatel v&nbsp;každé z&nbsp;kampaní udělat (podle pořadí legendy).</p>
    <canvas class="my-4" id="chart-end-actions"></canvas>
  </div>

  <div class="chart-wrapper">
    <h3>Konečné akce v&nbsp;kampaních dle skupiny [%]</h3>
    <p>Data se získávají stejným způsobem jako předchozí graf s&nbsp;tím rozdílem, že vše je rozděleno do sloupců symbolizujících skupinu, do které spadá e-mail příjemce.</p>
    <div class="table-responsive">
      <canvas class="my-4" id="chart-end-actions-groups"></canvas>
    </div>
  </div>

  <div class="chart-wrapper-vertical">
    <h3>Dobrovolníci dle skupiny</h3>
    <canvas class="my-4" id="chart-volunteers"></canvas>
  </div>

  <?php elseif (PermissionsModel::getUserRole() == PERMISSION_TEST_MANAGER): ?>

  <div class="chart-wrapper">
    <h3>Konečné akce uživatelů v&nbsp;kampaních, ke kterým mám oprávnění</h3>
    <p>V&nbsp;potaz se berou všechna nasbíraná data, přičemž přednost má ta vážnější akce, kterou mohl uživatel v&nbsp;každé z&nbsp;kampaní udělat (podle pořadí legendy).</p>
    <canvas class="my-4" id="chart-end-actions"></canvas>
  </div>

  <div class="chart-wrapper">
    <h3>Konečné akce v&nbsp;kampaních dle skupiny [%]</h3>
    <p>Data se získávají stejným způsobem jako předchozí graf s&nbsp;tím rozdílem, že vše je rozděleno do sloupců symbolizujících skupinu, do které spadá e-mail příjemce (např. studenti vs. zaměstnanci katedry).</p>
    <div class="table-responsive">
      <canvas class="my-4" id="chart-end-actions-groups"></canvas>
    </div>
  </div>

  <?php else: ?>

  <div class="chart-wrapper">
    <h3>Moje souhrnná úspěšnost v&nbsp;kampaních</h3>
    <p>V&nbsp;potaz se berou všechna nasbíraná data, přičemž přednost má ta vážnější akce, kterou mohl uživatel v&nbsp;každé z&nbsp;kampaní udělat (podle pořadí legendy).</p>
    <canvas class="my-4" id="chart-end-actions"></canvas>
  </div>
  <?php endif; ?>
</div>

<script src="/<?= CORE_DIR_EXTENSIONS ?>/chartjs/chart.umd.js?4.1.2"></script>
<script src="/<?= CORE_DIR_EXTENSIONS ?>/chartjs/chartjs-plugin-datalabels.min.js?2.2.0"></script>
<script>
  let chartEndActions = new Chart(document.getElementById('chart-end-actions'), {
    plugins: [ChartDataLabels],
    type: 'doughnut',
    data: {
      labels: [<?= $_chartLegend ?>],
      datasets: [{
        data: [<?= $chartDataUserEndAction ?>],
        backgroundColor: [<?= $_chartColors ?>],
        datalabels: {
          anchor: 'end', clamp: 'true'
        }
      }]
    },
    options: {
      responsive: true,
      aspectRatio: 2,
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
      },
      layout: {
        padding: 10
      },
    }
  });
  <?php if (PermissionsModel::getUserRole() <= PERMISSION_TEST_MANAGER): ?>

  let chartEndActionsGroups = new Chart(document.getElementById('chart-end-actions-groups'), {
    plugins: [ChartDataLabels],
    type: 'bar',
    data: {
      labels: [<?= $_barChartLegendDesc ?>],
      datasets: [
        <?php for ($i = 1; $i <= count($barChartLegend); $i++): ?>
        {
          label: '<?= $barChartLegend[$i] ?>',
          data: [<?= $barChartLegendData[$i] ?? 0 ?>],
          backgroundColor: '#<?= $barChartLegendColors[$i] ?>'
        },
        <?php endfor; ?>
      ]
    },
    options: {
      responsive: false,
      tooltips: {mode: 'index', intersect: false},
      maintainAspectRatio: true,
      aspectRatio: 1.9,
      plugins: {
        legend: {position: 'bottom'},
        datalabels: {
          color: '#fff',
          rotation: -40,
          display: function(context) {
            return context.dataset.data[context.dataIndex] > 10;
          },
          formatter: Math.round
        }
      },
      scales: {
        x: {
          stacked: true, ticks: {autoSkip: false}
        },
        y: {
          stacked: true, min: 0, max: 100, ticks: {callback: function(value, index, values) {return value + ' %';}}
        }
      },
    }
  });

  <?php endif; if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>
  let chartVolunteers = new Chart(document.getElementById('chart-volunteers'), {
    plugins: [ChartDataLabels],
    type: 'bar',
    data: {
      labels: [<?= $_chartVolunteers ?>],
      datasets: [{
        label: 'Počet dobrovolníků',
        backgroundColor: '#00c851',
        data: [<?= $chartVolunteersData ?>]
      }]
    },
    options: {
      responsive: true,
      tooltips: {mode: 'index', intersect: false},
      indexAxis: 'y',
      maintainAspectRatio: false,
      plugins: {
        legend: {display: false},
        datalabels: {
          color: '#fff',
          display: function(context) {
            return context.dataset.data[context.dataIndex] > 1;
          }
        }
      }
    }
  });
  <?php endif; ?>
</script>
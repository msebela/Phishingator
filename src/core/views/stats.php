<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Roční statistiky</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
      <span data-feather="help-circle"></span>
      Nápověda
    </a>
  </div>
</div>

<p>Tato sekce zobrazuje automaticky vygenerovanou, souhrnnou statistiku v&nbsp;jednotlivých letech. Statistika <strong>začíná rokem <?= $statsStartYear ?></strong>, kdy došlo k&nbsp;nasazení systému <i><?= WEB_HTML_BASE_TITLE ?></i>.</p>

<?php for ($year = date('Y'); $year >= $statsStartYear; $year--): ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h3>Rok <?= $year ?></h3>
</div>

<div class="card-group cards-homepage pb-2 mb-3">
  <div class="card bg-light text-center">
    <a href="/portal/campaigns">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= ${"countCampaigns$year"} ?></span><br>
          <?= ${"countCampaignsText$year"} ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/users">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= ${"countUsers$year"} ?></span><br>
          <?= ${"countUsersText$year"} ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/users?only-volunteers=1">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= ${"countVolunteers$year"} ?></span><br>
          <?= ${"countVolunteersText$year"} ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/phishing-emails">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= ${"countSentEmails$year"} ?></span><br>
          <?= ${"countSentEmailsText$year"} ?>
        </h4>
      </div>
    </a>
  </div>

  <div class="card bg-light text-center">
    <a href="/portal/phishing-websites">
      <div class="card-body">
        <h4 class="card-title mb-0">
          <span class="badge-pill badge-dark py-1"><?= ${"countPhishingWebsites$year"} ?></span><br>
          <?= ${"countPhishingWebsitesText$year"} ?>
        </h4>
      </div>
    </a>
  </div>
</div>

<div class="d-flex flex-wrap justify-content-around mt-5">
  <div class="chart-wrapper">
    <h3>Reakce uživatelů na cvičný phishing</h3>
    <p>Konečné reakce uživatelů na všechny phishingové kampaně v&nbsp;roce <?= $year ?>.</p>
    <canvas class="my-4" id="chart-end-actions-<?= $year ?>"></canvas>
  </div>

  <div class="chart-wrapper">
    <h3>Reakce uživatelů dle oddělení [%]</h3>
    <p>Konečné reakce uživatelů podle oddělení na všechny phishingové kampaně v&nbsp;roce <?= $year ?>.</p>
    <div class="table-responsive">
      <canvas class="my-4" id="chart-end-actions-groups-<?= $year ?>"></canvas>
    </div>
  </div>

  <div class="chart-wrapper-vertical">
    <h3>Noví dobrovolníci</h3>
    <canvas class="my-4" id="chart-volunteers-<?= $year ?>"></canvas>
  </div>
</div>
<?php endfor; ?>

<script src="/<?= CORE_DIR_EXTENSIONS ?>/chartjs/chart.umd.js?4.1.2"></script>
<script src="/<?= CORE_DIR_EXTENSIONS ?>/chartjs/chartjs-plugin-datalabels.min.js?2.2.0"></script>
<script>
  <?php for ($year = date('Y'); $year >= $statsStartYear; $year--): ?>
  let chartEndActions<?= $year ?> = new Chart(document.getElementById('chart-end-actions-<?= $year ?>'), {
    plugins: [ChartDataLabels],
    type: 'doughnut',
    data: {
      labels: [<?= $_chartLegend ?>],
      datasets: [{
        data: [<?= ${"chartDataUserEndAction$year"} ?>],
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
  <?php if (PermissionsModel::getUserRole() == PERMISSION_ADMIN): ?>

  let chartEndActionsGroups<?= $year ?> = new Chart(document.getElementById('chart-end-actions-groups-<?= $year ?>'), {
    plugins: [ChartDataLabels],
    type: 'bar',
    data: {
      labels: [<?= ${"_barChartLegendDesc$year"} ?>],
      datasets: [
        <?php for ($i = 1; $i <= count($barChartLegend); $i++): ?>
        {
          label: '<?= ${"barChartLegend$year"}[$i] ?>',
          data: [<?= ${"barChartLegendData$year"}[$i] ?? 0 ?>],
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

  let chartVolunteers<?= $year ?> = new Chart(document.getElementById('chart-volunteers-<?= $year ?>'), {
    plugins: [ChartDataLabels],
    type: 'bar',
    data: {
      labels: [<?= ${"_chartVolunteers$year"} ?>],
      datasets: [{
        label: 'Počet dobrovolníků',
        backgroundColor: '#00c851',
        data: [<?= ${"chartVolunteersData$year"} ?>]
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
  <?php endfor; ?>
</script>
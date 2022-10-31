<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h2>Přijaté cvičné phishingové e-maily</h2>
  <div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group mr-2">
      <a href="<?= $helpLink ?>" target="_blank" class="btn btn-outline-info" role="button">
        <span data-feather="help-circle"></span>
        <?php echo ((!isset($_GET['action'])) ? 'Nápověda' : '') ?>
      </a>
      <?php if (isset($_GET['action'])): ?>
      <a href="/portal/<?= $urlSection ?>" class="btn btn-info" role="button">
        <span data-feather="list"></span>
        Seznam všech e-mailů
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<p>V této sekci si lze prohlédnout jakýkoliv z&nbsp;cvičných phishingových e-mailů, který byl doručen do e-mailové schránky uživatele. U&nbsp;každého e-mailu jsou navíc k&nbsp;dispozici indicie, na základě kterých bylo možné phishing rozpoznat. Podrobnější informace jsou k&nbsp;dispozici v&nbsp;<a href="<?= $helpLink ?>" target="_blank">nápovědě</a>.</p>
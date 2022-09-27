<!DOCTYPE html>
<html lang="cs"<?php if ($phishingPage): ?> class="phishing-email-detail"<?php endif; ?>>
<head>
  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Phishingator je systém pro rozesílání cvičných phishingových zpráv, jehož cílem je naučit uživatele odhalovat reálný phishing.">
  <meta name="keywords" content="phishingator, cvičný phishing, sociální inženýrství, kyberbezpečnost">
  <meta name="author" content="Martin Šebela">

  <meta property="og:url" content="<?= WEB_URL ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Phishingator">
  <meta property="og:description" content="Cvičný phishing na českých univerzitách">

  <title>Phishingator · Cvičný phishing na českých univerzitách</title>

  <link rel="icon" type="image/png" href="/favicon.png">

  <link rel="stylesheet" href="/<?= CORE_DIR_EXTENSIONS ?>/bootstrap-4.6.1/bootstrap.min.css">
  <link rel="stylesheet" href="/style-intro.css">
</head>
<body<?php if ($phishingPage): ?> class="phishing-email-detail"<?php endif; ?>>
  <header>
    <nav class="navbar navbar-expand-lg">
      <?php if ($phishingPage): ?>
      <div class="container">
      <?php endif; ?>

      <a href="/" class="navbar-brand shifted">
        <h1>
          <span data-feather="anchor" class="logo"></span>
          Phishingator
        </h1>
      </a>

      <?php if ($phishingPage): ?>
      </div>
      <?php endif; ?>
    </nav>
  </header>

  <?php $this->controller->displayView(); ?>

  <script src="/<?= CORE_DIR_EXTENSIONS ?>/jquery-3.6.1.min.js"></script>
  <script src="/<?= CORE_DIR_EXTENSIONS ?>/bootstrap-4.6.1/bootstrap.bundle.min.js"></script>
  <?php if ($phishingPage): ?>
  <script src="/js.js"></script>
  <?php endif; ?>
  <script src="/<?= CORE_DIR_EXTENSIONS ?>/feather.min.js"></script>
  <script>
    feather.replace()
  </script>
</body>
</html>
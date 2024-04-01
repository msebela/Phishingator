<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Přihlášení</title>

    <link href="style.css" rel="stylesheet">
    <link href="login.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <form method="post" class="form-wrapper">
      <h1 class="h3 mb-3 font-weight-normal">Přihlášení</h1>
      <?php if ($service): ?>
      <h2 class="h5 font-weight-normal mb-4"><?= $service ?></h2>
      <?php endif; ?>

      <p class="text-muted">Pro pokračování je nutno se přihlásit.</p>

      <?php if ($message): ?>
      <div role="alert" class="alert alert-danger w-100 mt-4 mb-4">Invalid username or password!</div>
      <?php endif; ?>

      <label for="username" class="sr-only">Uživatelské jméno</label>
      <input type="text" id="username" name="username" class="form-control mb-2" placeholder="Uživatelské jméno" required autofocus>

      <label for="password" class="sr-only">Heslo</label>
      <input type="password" id="password" name="password" class="form-control mb-4" placeholder="Heslo" required>

      <div class="text-right">
        <button class="btn btn-lg btn-secondary" type="submit">Přihlásit</button>
      </div>
    </form>
  </body>
</html>
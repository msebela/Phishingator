<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Login</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  </head>

  <body>
    <form method="post">
      <p>
        <a href="#">
          <img src="organization-logo.png" alt="" style="max-height: 200px;">
        </a>
      </p>

      <?php if ($message): ?>
      <div>Nesprávné jméno nebo heslo!</div>
      <?php endif; ?>

      <div>
        <div>
          <strong>Uživatelské jméno:</strong>
          <input type="text" name="username" size="18" required autofocus><br>
        </div>

        <div>
          <strong>Heslo:</strong>
          <input type="password" name="password" size="18" required>
        </div>

        <p>
          <input type="submit" value="Přihlásit">
        </p>

        <p>
          <a href="#">Zapomenuté heslo</a> | <a href="#">Nápověda</a>
        </p>
      </div>
    </form>
  </body>
</html>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>

<body>
  <form method="post">
    <a href="#">
      <img src="org.png" alt="">
    </a>

    <?php if ($message): ?>
    <div>Nesprávné jméno nebo heslo!</div>
    <?php endif; ?>

    <div>
      <div>
        <strong>Vaše uživatelské jméno:</strong>
        <input type="text" name="username" size="18" required autofocus><br>
      </div>

      <div>
        <strong>Heslo:</strong>
        <input type="password" name="password" size="18" required>
      </div>

      <input type="submit" value="Přihlásit">

      <div>
        <a href="#">Nápověda</a> | <a href="#">Nechci se přihlásit</a>
      </div>
    </div>
  </form>
</body>
</html>
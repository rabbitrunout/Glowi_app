<?php
session_start();
// Если пользователь уже вошёл — перенаправим
if (isset($_SESSION['parentID'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Login — Glowi</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
  <?php  'header.php'; ?>

  <main class="container">
    <h2>Login to your personal account</h2>

    <?php if (!empty($_GET['error'])): ?>
      <p style="color: red;">Invalid username or password</p>
    <?php endif; ?>

  <form method="POST" action="login.php">
    
    <input type="text" name="userName" placeholder="Логин" required><br>
    <input type="password" name="password" placeholder="Пароль" required><br>
    <button type="submit">Login</button>
</form>

    <p>No account? <a href="register.php ">Register</a></p>
  </main>

  <?php 'footer.php'; ?>
</body>
</html>

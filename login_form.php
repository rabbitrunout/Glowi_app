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
  <title>Вход — Glowi</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
  <?php  'header.php'; ?>

  <main class="container">
    <h2>Вход в личный кабинет</h2>

    <?php if (!empty($_GET['error'])): ?>
      <p style="color: red;">Неверный логин или пароль</p>
    <?php endif; ?>

  <form method="POST" action="login.php">
    
    <input type="text" name="userName" placeholder="Логин" required><br>
    <input type="password" name="password" placeholder="Пароль" required><br>
    <button type="submit">Войти</button>
</form>

    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
  </main>

  <?php 'footer.php'; ?>
</body>
</html>

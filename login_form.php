<?php
session_start();
if (isset($_SESSION['parentID'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login — Glowi</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
  <?php include 'header.php'; ?>

  <main class="container glowi-card">
    <h2>Login to your personal account</h2>

    <?php if (!empty($_GET['error'])): ?>
      <p style="color: red;">Invalid username or password</p>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <label for="userName">Username / login</label>
      <input type="text" id="userName" name="userName" placeholder="Enter your username / login" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required>

      <button type="submit" class="btn-save">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
  </main>

  <?php include 'footer.php'; ?>
</body>
</html>

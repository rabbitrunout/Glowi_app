<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
  <nav>
    <div class="nav-left">
      <a href="index.php" class="brand">
        <img src="assets/img/mainlogo.png" alt="GLOWI Logo" class="logo">
        <h1 class="animate__animated animate__pulse">GLOWI</h1>
      </a>
    </div>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="index.php#how-it-works">How It Works</a>
      <a href="index.php#support">Support</a>

      <?php if (isset($_SESSION['parentID'])): ?>
        <a href="dashboard.php">My Account</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="index.php#login">Login</a>
      <?php endif; ?>
    </div>
  </nav>
</header>

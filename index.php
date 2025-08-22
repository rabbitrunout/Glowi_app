<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Home — Glowi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Glowi — платформа для родителей гимнасток">
  <link rel="stylesheet" href="css/main.css" />
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <script src="scripts/jquery.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="scripts/app.js" defer></script>
</head>
<body>

<?php include 'header.php'; ?>

  <section id="home" class="hero card section">
    <h1> Glowi </h1>
    <p>Inspiring workouts, convenient monitoring, fun classes, and motivation — all in one place for children and parents.</p>
    <a href="#login" class="btn">Get Started</a>
  </section>

  <section id="how-it-works" class="card section how-glowi-works" >
    <img src="assets/img/lamp.png" alt="lamp">
    <h2>How does Glowi work?</h2>
    <div class="cards-container-main">
      <div class="card-main">
        <img src="assets/img/profile.png" alt="Registration">
        <h4>1. Registration</h4>
        <p>Create a parent's account in 2 minutes</p>
      </div>
      <div class="card-main">
        <img src="assets/img/gymnast.png" alt="Add a child">
        <h4>2. Add a child</h4>
        <p>Enter the data and select a level</p>
      </div>
      <div class="card-main">
        <img src="assets/img/cup.png" alt="Awards">
        <h4>3. Track accomplishments</h4>
        <p>Progress and medals motivate children</p>
      </div>
    </div>
  </section>

  <section id="support" class="card section">
    <img src="assets/img/support1.png" alt="support" style="display: block; margin: 0 auto;">
    <h2>Support for parents</h2>
    <p style="text-align:center; max-width: 600px; margin: auto;">
      From communicating with coaches to tracking progress, Glowi helps you keep up to date with everything.
    </p>
  </section>

  <section id="login" class="card section login-section">
    <img src="assets/img/enter.png" alt="Login Icon"> 
    <h2>Login to your personal account</h2>
    <form method="POST" action="login.php">
      <input type="text" name="userName" placeholder="Login" autocomplete="username" required><br>
      <input type="password" name="password" placeholder="Password" autocomplete="current-password" required><br>
      <button class="btn" type="submit">Login</button>
    </form>
    <p><a href="register.php" class="button">Registration</a></p>
  </section>

<?php include 'footer.php'; ?>

</body>
</html>

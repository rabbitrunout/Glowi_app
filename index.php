<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Home — Glowi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Glowi — платформа для родителей гимнасток">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <link rel="stylesheet" href="css/main.css" />
  <script src="scripts/jquery.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="scripts/app.js" defer></script>
</head>
<body>

<?php include 'header.php'; ?>
    <section id="home" class="parallax-section card hero">
      <h1>🌟 Glowi</h1>
      <p>Inspiring workouts, convenient monitoring, fun classes, and motivation—all in one place for children and parents.</p>
    </section>

<section id="how-it-works" class="parallax-section card section how-glowi-works">
   <img src="assets/img/lamp.png" alt="lamp"><h2>How does Glowi work?</h2>
   <div class="steps-grid">
    <div class="step">
      <img src="assets/img/profile.png" alt="Registration">
      <h4>1. Registration</h4>
      <p>Create a parent's account in 2 minutes</p>
    </div>
    <div class="step">
      <img src="assets/img/gymnast.png" alt="Add a child">
      <h4>2. Add a child</h4>
      <p>Enter the data and select a level</p>
    </div>
    <div class="step">
      <img src="assets/img/heart.png" alt="Training">
      <h4>3. Keep an eye on the classes</h4>
      <p>Calendar and feedback from the coach</p>
    </div>
    <div class="step">
      <img src="assets/img/cup.png" alt="Награды">
      <h4>4. Track accomplishments</h4>
      <p>Progress and medals motivate children</p>
    </div>
  </div>
</section>


<section id="support" class="card section">
    <img src="assets/img/support1.png" alt="support">
    <h2>Support for parents</h2>
  <p style="text-align:center; max-width: 600px; margin: auto;">
    From communicating with coaches to tracking progress, Glowi helps you keep up to date with everything.
  </p>
</section>

<section id="login" class="card section login-section">
  <img src="assets/img/enter.png" alt=""> 
  <h2> Вход в личный кабинет</h2>
  <form method="POST" action="login.php">
    <input type="text" name="userName" placeholder="Логин" required><br>
    <input type="password" name="password" placeholder="Пароль" required><br>
    <button class="btn" type="submit">Войти</button>
  </form>
  <p><a href="register.php" class="button">Регистрация</a></p>
</section>

<?php include 'footer.php'; ?>
</body>
</html>

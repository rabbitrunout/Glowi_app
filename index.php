<?php
session_start();
// require("database.php");

// Если пользователь уже вошёл — перенаправляем его в личный кабинет
// if (isset($_SESSION["parentID"])) {
//     header("Location: dashboard.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Home — Glowi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Glowi — современная платформа для родителей гимнасток. Управление расписанием, достижениями и оплатами.">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="css/main.css" />
  <script src="scripts/jquery.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="scripts/app.js" defer></script>
</head>
<body>

<?php include 'header.php'; ?>

<nav>
  <a href="#home">Home</a>
  <a href="#activities">Schedule</a>
  <a href="#support">Success</a>
  <a href="#payment">Payment</a>
  <a href="#booking">Booking</a>
  <a href="#login">Login</a>
</nav>

<section class="hero" id="home">
  <h2>Welcome to Glowi</h2>
  <p>Bright activities for children — confidence and control for parents</p>
</section>

<section id="activities" class="section">
  <h3>Популярные активности</h3>
  <div class="swiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,sports" alt="Sports">
        <h4>Sports Day</h4>
        <p>Fun workouts, movement, and team spirit!</p>
      </div>
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,science" alt="Science">
        <h4>Научный клуб</h4>
        <p>Эксперименты и открытия для юных гениев.</p>
      </div>
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,art" alt="Art">
        <h4>Creativity</h4>
        <p>Drawing, crafts, creativity without borders!</p>
      </div>
    </div>
  </div>
</section>

<section id="support" class="section">
  <h3>Support for parents</h3>
  <p style="text-align:center; max-width: 600px; margin: auto;">
    From communicating with coaches to tracking progress, Glowi helps you keep up to date with everything.
  </p>
</section>

<section id="login" class="login-section">
  <h3>Login to your personal account</h3>
  <form method="POST" action="login.php">
   

     <input type="text" name="userName" placeholder="Логин" required><br>
    <input type="password" name="password" placeholder="Пароль" required><br>
    
    <button class="button" type="submit">Login</button>
  </form>
  <p><a href="register.php" class="btn">Registration</a></p>
</section>

<?php include  'footer.php'; ?>



</body>
</html>

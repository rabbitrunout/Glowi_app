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
  <title>Главная — Glowi</title>
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
  <a href="#home">Главная</a>
  <a href="#activities">Расписание</a>
  <a href="#support">Успехи</a>
  <a href="#payment">Оплата</a>
  <a href="#booking">Бронирование</a>
  <a href="#login">Вход</a>
</nav>

<section class="hero" id="home">
  <h2>Добро пожаловать в Glowi</h2>
  <p>Яркие занятия для детей — уверенность и контроль для родителей</p>
</section>

<section id="activities" class="section">
  <h3>Популярные активности</h3>
  <div class="swiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,sports" alt="Sports">
        <h4>День спорта</h4>
        <p>Весёлые тренировки, движение и командный дух!</p>
      </div>
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,science" alt="Science">
        <h4>Научный клуб</h4>
        <p>Эксперименты и открытия для юных гениев.</p>
      </div>
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,art" alt="Art">
        <h4>Творчество</h4>
        <p>Рисование, поделки, креатив без границ!</p>
      </div>
    </div>
  </div>
</section>

<section id="support" class="section">
  <h3>Поддержка для родителей</h3>
  <p style="text-align:center; max-width: 600px; margin: auto;">
    От общения с тренерами до отслеживания прогресса — Glowi помогает быть в курсе всего.
  </p>
</section>

<section id="login" class="login-section">
  <h3>Вход в личный кабинет</h3>
  <form method="POST" action="login.php">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Пароль" required><br>
    <button class="button" type="submit">Войти</button>
  </form>
  <p><a href="register.php" class="btn">Регистрация</a></p>
</section>

<?php include 'footer.php'; ?>



</body>
</html>

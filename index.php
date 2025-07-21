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

<nav style="text-align:center; margin: 20px 0;">
  <a href="#home" class="btn">🏠 Home</a>
  <a href="#activities" class="btn">📅 Schedule</a>
  <a href="#support" class="btn">🏆 Success</a>
  <a href="#payment" class="btn">💳 Payment</a>
  <a href="#booking" class="btn">📝 Booking</a>
  <a href="#login" class="btn">🔐 Login</a>
</nav>

<section id="home" class="parallax-section card hero">
  <h1>🌟 Glowi</h1>
  <p>Вдохновляющие тренировки, удобный контроль, веселые занятия и мотивация — всё в одном месте для детей и родителей.</p>
</section>

<section id="how-it-works" class="parallax-section card section how-glowi-works">
  <h2>📌 Как работает Glowi?</h2>
  <div class="steps-grid">
    <div class="step">
      <img src="assets/icons/register.svg" alt="Регистрация">
      <h4>1. Регистрация</h4>
      <p>Создайте аккаунт родителя за 2 минуты</p>
    </div>
    <div class="step">
      <img src="assets/icons/add-child.svg" alt="Добавить ребёнка">
      <h4>2. Добавьте ребёнка</h4>
      <p>Укажите данные и выберите секцию</p>
    </div>
    <div class="step">
      <img src="assets/icons/training.svg" alt="Тренировки">
      <h4>3. Следите за занятиями</h4>
      <p>Календарь и обратная связь от тренера</p>
    </div>
    <div class="step">
      <img src="assets/icons/awards.svg" alt="Награды">
      <h4>4. Получайте награды</h4>
      <p>Прогресс и медали мотивируют детей</p>
    </div>
  </div>
</section>

<section class="parallax-section card section feature-icons">
  <h2>✨ Особенности платформы</h2>
  <div class="feature-grid">
    <div>
      <img src="assets/icons/ribbon.svg" alt="Лента">
      <p>Интерактивные задания с лентами</p>
    </div>
    <div>
      <img src="assets/icons/hoop.svg" alt="Обруч">
      <p>Обручи, мячи и булавы в тренировках</p>
    </div>
    <div>
      <img src="assets/icons/medal.svg" alt="Награды">
      <p>Достижения и мотивация</p>
    </div>
    <div>
      <img src="assets/icons/video.svg" alt="Видео">
      <p>Видео уроки в формате игры</p>
    </div>
  </div>
</section>

<section id="activities" class="card section">
  <h2>🔥 Популярные активности</h2>
  <div class="swiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,gymnastics" alt="Гимнастика">
        <h4>Гимнастика</h4>
        <p>Весёлые тренировки и гибкость!</p>
      </div>
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,science" alt="Наука">
        <h4>Наука</h4>
        <p>Эксперименты и открытия для юных гениев.</p>
      </div>
      <div class="swiper-slide">
        <img src="https://source.unsplash.com/300x200/?kids,art" alt="Творчество">
        <h4>Творчество</h4>
        <p>Рисование, поделки, воображение без границ!</p>
      </div>
    </div>
  </div>
</section>

<section id="support" class="card section">
  <h2>🤝 Поддержка для родителей</h2>
  <p style="text-align:center; max-width: 600px; margin: auto;">
    От общения с тренерами до отслеживания прогресса — Glowi помогает быть в курсе всего.
  </p>
</section>

<section id="login" class="card section login-section">
  <h2>🔐 Вход в личный кабинет</h2>
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

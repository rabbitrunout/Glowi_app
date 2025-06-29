<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : die("Некорректный ID ребенка.");

// Получение данных ребенка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC) ?: die("Ребенок не найден или доступ запрещён.");

// Достижения
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE childID = ? ORDER BY dateAwarded DESC LIMIT 5");
$stmt->execute([$childID]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Платежи
$stmt = $pdo->prepare("SELECT * FROM payments WHERE childID = ? ORDER BY paymentDate DESC LIMIT 5");
$stmt->execute([$childID]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// События
$filter = $_GET['filter'] ?? 'all';
$allowed = ['training', 'competition'];
if (in_array($filter, $allowed)) {
    $stmt = $pdo->prepare("
        SELECT e.* FROM events e
        JOIN child_event ce ON e.eventID = ce.eventID
        WHERE ce.childID = ? AND e.eventType = ?
        ORDER BY e.date DESC, e.time DESC
    ");
    $stmt->execute([$childID, $filter]);
} else {
    $stmt = $pdo->prepare("
        SELECT e.* FROM events e
        JOIN child_event ce ON e.eventID = ce.eventID
        WHERE ce.childID = ?
        ORDER BY e.date DESC, e.time DESC
    ");
    $stmt->execute([$childID]);
}
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Профиль: <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">

  <!-- FullCalendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/ru.js"></script>

  <!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

  

  
</head>
<body>
<?php include 'header.php'; ?>

<main class="container profile-container">

  <div class="left-column">
    <section class="card profile-header">
      <div class="avatar">
        <img src="<?= !empty($child['photoImage']) ? htmlspecialchars($child['photoImage']) : 'images/default_avatar.png' ?>" alt="Фото" style="max-width:100px; border-radius:50%;">
      </div>
      <div>
        <h1>👤 <?= htmlspecialchars($child['name']) ?></h1>
        <p><strong>🎂 Возраст:</strong> <?= htmlspecialchars($child['age']) ?> лет</p>
        <p><strong>🎓 Группа:</strong> <?= htmlspecialchars($child['groupLevel']) ?></p>
        <p><strong>🚻 Пол:</strong> <?= ($child['gender'] === 'male' ? '👦' : ($child['gender'] === 'female' ? '👧' : '❓')) . ' ' . htmlspecialchars($child['gender']) ?></p>
        <p><a href="edit_child.php?childID=<?= $childID ?>" class="button">✏️ Редактировать</a></p>
      </div>
    </section>

    <section class="card schedule-events-section">
      <h2>📅 Расписание и события</h2>

      <form method="get" style="margin-bottom: 10px;">
        <input type="hidden" name="childID" value="<?= $childID ?>">
        <label>Фильтр:</label>
        <select name="filter" onchange="this.form.submit()">
          <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Все</option>
          <option value="training" <?= $filter === 'training' ? 'selected' : '' ?>>Тренировки</option>
          <option value="competition" <?= $filter === 'competition' ? 'selected' : '' ?>>Соревнования</option>
        </select>
      </form>

      <?php if (empty($events)): ?>
        <p>Событий пока нет.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($events as $event): ?>
            <li>
              <strong><?= htmlspecialchars($event['title']) ?></strong>
              (<?= $event['eventType'] === 'training' ? '🏋️‍♂️ Тренировка' : '🏆 Соревнование' ?>),
              <?= htmlspecialchars($event['date']) ?> в <?= htmlspecialchars($event['time']) ?>,
              📍 <?= htmlspecialchars($event['location']) ?><br>
              <em><?= nl2br(htmlspecialchars($event['description'])) ?></em>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section class="card payments-section">
      <h2>💳 Последние платежи</h2>
      <?php if (empty($payments)): ?>
        <p>Платежей пока нет</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Дата</th><th>Сумма ($)</th><th>Статус</th></tr></thead>
          <tbody>
            <?php foreach ($payments as $payment): ?>
              <tr>
                <td><?= htmlspecialchars($payment['paymentDate']) ?></td>
                <td><?= number_format($payment['amount'], 2, ',', ' ') ?></td>
                <td><?= htmlspecialchars($payment['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p><a href="child_payments.php?childID=<?= $childID ?>">💰 Все платежи →</a></p>
      <?php endif; ?>
    </section>
  </div>

  <div class="right-column">
    
  <section class="card calendar-section">
  <h2>Календарь событий</h2>
  <div id='calendar'> </div>
   </section>

  


    <section class="card achievements-section">
      <h2>🏅 Достижения</h2>
      <?php if (empty($achievements)): ?>
        <p>Нет добавленных достижений.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($achievements as $ach): ?>
            <li>
              <strong><?= htmlspecialchars($ach['title']) ?></strong> (<?= htmlspecialchars($ach['type']) ?>),
              <?= htmlspecialchars($ach['dateAwarded']) ?>
              <?php if (!empty($ach['medal']) && $ach['medal'] !== 'none'): ?>
                &nbsp;<img src="images/medals/<?= htmlspecialchars($ach['medal']) ?>.png" alt="<?= htmlspecialchars($ach['medal']) ?>" title="<?= ucfirst($ach['medal']) ?>" style="width: 20px;">
              <?php endif; ?>
              <?php if (!empty($ach['place'])): ?>
                , место: <?= (int)$ach['place'] ?>
              <?php endif; ?>
              <?php if (!empty($ach['fileURL'])): ?>
                <br><a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank">📎 Файл</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p><a href="child_achievements.php?childID=<?= $childID ?>">🏅 Все достижения →</a></p>
      <?php endif; ?>
      <p><a href="add_achievement.php?childID=<?= $childID ?>" class="button">ADD achievement</a></p>
    </section>
  </div>

</main>

<p><a href="dashboard.php">← Назад в личный кабинет</a></p>

<?php include 'footer.php'; ?>



<script src="scripts/app.js"></script>
</body>
</html>

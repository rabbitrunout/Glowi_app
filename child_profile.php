<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : die("Некорректный ID ребенка.");

$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC) ?: die("Ребенок не найден или доступ запрещён.");

$stmt = $pdo->prepare("SELECT * FROM achievements WHERE childID = ? ORDER BY dateAwarded DESC LIMIT 5");
$stmt->execute([$childID]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Расписание по неделям
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

$stmt = $pdo->prepare("
  SELECT e.date, e.time, e.title AS activity, e.location
  FROM events e
  JOIN child_event ce ON ce.eventID = e.eventID
  WHERE ce.childID = ? AND e.date BETWEEN ? AND ?
  ORDER BY e.date ASC, e.time ASC
");
$stmt->execute([$childID, $startOfWeek, $endOfWeek]);
$weeklyEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groupedSchedule = [];
foreach ($weeklyEvents as $event) {
    $dayName = strftime('%A', strtotime($event['date']));
    $groupedSchedule[$dayName][] = $event;
}

// Все события ребёнка (и от родителя, и от тренера)
$stmt = $pdo->prepare("
  SELECT e.*, ce.createdBy
  FROM events e
  JOIN child_event ce ON e.eventID = ce.eventID
  WHERE ce.childID = ?
");
$stmt->execute([$childID]);
$fcEvents = [];

while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fcEvents[] = [
        'id' => $event['eventID'],
        'title' => $event['title'],
        'start' => $event['date'] . 'T' . $event['time'],
        'allDay' => false,
        'color' => $event['createdBy'] === 'parent' ? '#3788d8' : '#34a853', // синий - пользователь, зелёный - тренер
    ];
}

$fcEvents = [];
$stmt = $pdo->prepare("
  SELECT e.*, ce.createdBy
  FROM events e
  JOIN child_event ce ON e.eventID = ce.eventID
  WHERE ce.childID = ?
");
$stmt->execute([$childID]);

while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fcEvents[] = [
        'id' => (int)$event['eventID'],
        'title' => $event['title'],
        'start' => $event['date'] . 'T' . $event['time'],
        'allDay' => false,
        'color' => $event['createdBy'] === 'parent' ? '#1E90FF' : '#2ECC71',
        'extendedProps' => [
            'description' => $event['description'],
            'location' => $event['location'],
            'eventType' => $event['eventType']
        ]
    ];
}



$fcEventsJson = json_encode($fcEvents, JSON_UNESCAPED_UNICODE);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Профиль: <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">
  <!-- <link rel="stylesheet" href="assets/css/glowi-pages-style.css"> -->
  <link rel="stylesheet" href="css/child_profile_neon.css">

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/ru.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container profile-container">
  <div class="left-column">
    <section class="card profile-header">
      <div class="avatar">
        <img src="<?= !empty($child['photoImage']) ? htmlspecialchars($child['photoImage']) : 'placeholder_100.jpg' ?>" alt="Фото" style="max-width:100px; border-radius:50%;">
      </div>
      <div>
        <h1><i data-lucide="user"></i> <?= htmlspecialchars($child['name']) ?></h1>
        <p><strong><i data-lucide="cake"></i> Возраст:</strong> <?= htmlspecialchars($child['age']) ?> лет</p>
        <p><strong><i data-lucide="school"></i> Группа:</strong> <?= htmlspecialchars($child['groupLevel']) ?></p>
        <p><strong><i data-lucide="user-circle"></i> Пол:</strong> <?= htmlspecialchars($child['gender']) ?></p>
        <p><a href="edit_child.php?childID=<?= $childID ?>" class="button"><i data-lucide="edit-3"></i> Редактировать</a></p>
      </div>
    </section>

    <section class="card weekly-schedule">
      <h2><i data-lucide="calendar-clock"></i> Расписание на неделю</h2>
      <?php if (empty($groupedSchedule)): ?>
        <p>На этой неделе нет запланированных занятий.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>День</th><th>Время</th><th>Активность</th><th>Место</th></tr>
          </thead>
          <tbody>
            <?php foreach ($groupedSchedule as $day => $events): ?>
              <?php foreach ($events as $i => $ev): ?>
                <tr>
                  <?php if ($i === 0): ?>
                    <td rowspan="<?= count($events) ?>"><strong><?= ucfirst($day) ?></strong></td>
                  <?php endif; ?>
                  <td><?= substr($ev['time'], 0, 5) ?></td>
                  <td><?= htmlspecialchars($ev['activity']) ?></td>
                  <td><?= htmlspecialchars($ev['location']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <section class="card schedule-events-section">
      <h2><i data-lucide="calendar-days"></i> Все события</h2>
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
              (<?= $event['eventType'] === 'training' ? '<i data-lucide="dumbbell"></i> Тренировка' : '<i data-lucide="trophy"></i> Соревнование' ?>),
              <?= htmlspecialchars($event['date']) ?> в <?= htmlspecialchars($event['time']) ?>,
              <i data-lucide="map-pin"></i> <?= htmlspecialchars($event['location']) ?><br>
              <em><?= nl2br(htmlspecialchars($event['description'])) ?></em>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <a href="event_list_child.php?childID=<?= $childID ?>">📅 Event List</a>
    </section>

    <section class="card payments-section">
      <h2><i data-lucide="credit-card"></i> Последние платежи</h2>
      <?php if (empty($payments)): ?>
        <p>Платежей пока нет</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Дата</th><th>Сумма (₽)</th><th>Статус</th></tr></thead>
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
        <p><a href="child_payments.php?childID=<?= $childID ?>"><i data-lucide="wallet"></i> Все платежи →</a></p>
      <?php endif; ?>
    </section>
  </div>

  <div class="right-column">
    <section class="card calendar-section">
      
      <h2><i data-lucide="calendar-check-2"></i> Календарь событий</h2>
      <div id='calendar'></div>
      <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
  
</div>
    </section>

    <section class="card achievements-section">
      <h2><i data-lucide="medal"></i> Достижения</h2>
      <?php if (empty($achievements)): ?>
        <p>Нет добавленных достижений.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($achievements as $ach): ?>
            <li>
              <strong><?= htmlspecialchars($ach['title']) ?></strong> (<?= htmlspecialchars($ach['type']) ?>),
              <?= htmlspecialchars($ach['dateAwarded']) ?>
              <?php if (!empty($ach['fileURL'])): ?>
                <br><a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank"><i data-lucide="paperclip"></i> Файл</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p><a href="child_achievements.php?childID=<?= $childID ?>"><i data-lucide="trophy"></i> Все достижения →</a></p>
      <?php endif; ?>
      <p><a href="add_achievement.php?childID=<?= $childID ?>" class="button"><i data-lucide="plus-circle"></i> Добавить достижение</a></p>
    </section>
  </div>
</main>

<p><a href="dashboard.php"><i data-lucide="arrow-left"></i> Назад в личный кабинет</a></p>

<?php include 'footer.php'; ?>

<script>
  const fcEventsFromPHP = <?= json_encode($fcEvents, JSON_UNESCAPED_UNICODE) ?>;
  const childID = <?= (int)$childID ?>;
</script>
<script src="scripts/app.js"></script>
<script>
  lucide.createIcons();
</script>
</body>
</html>

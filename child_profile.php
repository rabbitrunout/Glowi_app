<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : die("Некорректный ID ребенка.");

// Получаем ребенка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) die("Ребенок не найден или доступ запрещен.");

// Фото
$imageFile = $child['photoImage'] ?? '';
$uploadDir = 'uploads/avatars/';
$placeholder = 'assets/img/placeholder.png';

if (!empty($imageFile)) {
    $thumbPath = $uploadDir . pathinfo($imageFile, PATHINFO_FILENAME) . '_100.' . pathinfo($imageFile, PATHINFO_EXTENSION);
    $originalPath = $uploadDir . $imageFile;
    if (file_exists($thumbPath)) $imagePath = $thumbPath;
    elseif (file_exists($originalPath)) $imagePath = $originalPath;
    else $imagePath = $placeholder;
} else $imagePath = $placeholder;

// Расписание
$stmt = $pdo->prepare("SELECT * FROM schedule WHERE childID = ? ORDER BY FIELD(dayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), startTime ASC");
$stmt->execute([$childID]);
$scheduleList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Private request
$stmt = $pdo->prepare("SELECT * FROM private_lesson_requests WHERE childID = ? ORDER BY requestDate DESC");
$stmt->execute([$childID]);
$requests = $stmt->fetchAll();

// Достижения
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE childID = ? ORDER BY dateAwarded DESC LIMIT 5");
$stmt->execute([$childID]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Платежи
$stmt = $pdo->prepare("SELECT * FROM payments WHERE childID = ? ORDER BY paymentDate DESC LIMIT 5");
$stmt->execute([$childID]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// События
$stmt = $pdo->prepare("SELECT e.*, ce.createdBy FROM events e JOIN child_event ce ON e.eventID = ce.eventID WHERE ce.childID = ?");
$stmt->execute([$childID]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Формируем fcEvents
$fcEvents = [];

// 1. Добавляем расписание как повторяющиеся события на месяц
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth  = date('Y-m-t');
foreach ($scheduleList as $sched) {
    $startDate = new DateTime($firstDayOfMonth);
    $endDate = new DateTime($lastDayOfMonth);
    while ($startDate <= $endDate) {
        if ($startDate->format('l') === $sched['dayOfWeek']) {
            $fcEvents[] = [
                'id' => 'sched_' . $sched['scheduleID'] . '_' . $startDate->format('Ymd'),
                'title' => $sched['activity'],
                'start' => $startDate->format('Y-m-d') . 'T' . $sched['startTime'],
                'end' => $startDate->format('Y-m-d') . 'T' . $sched['endTime'],
                'allDay' => false,
                'extendedProps' => [
                    'description' => $sched['activity'] . " (Regular schedule)",
                    'eventType' => 'schedule'
                ]
            ];
        }
        $startDate->modify('+1 day');
    }
}

// 2. Добавляем обычные события с отдельной логикой для соревнований
foreach ($events as $event) {
    $eventColor = '#34a853'; // стандартный зелёный
    $eventTitlePrefix = '';   // иконка перед названием

    if (isset($event['eventType'])) {
        switch ($event['eventType']) {
            case 'competition':
                $eventColor = '#FF6347'; // ярко-красный для соревнований
                $eventTitlePrefix = '🏆 ';
                break;
            case 'training':
                $eventColor = '#3788d8'; // синий для тренировок
                break;
            case 'private_lesson':
                $eventColor = '#fffd69ff'; // ярко-розовый для приватных уроков
                $eventTitlePrefix = '🎯 ';
                break;
            case 'event':
            default:
                $eventColor = $event['createdBy'] === 'parent' ? '#3788d8' : '#34a853';
        }
    }

    $fcEvents[] = [
        'id' => $event['eventID'],
        'title' => $eventTitlePrefix . $event['title'],
        'start' => $event['date'] . 'T' . $event['time'],
        'allDay' => false,
        'color' => $eventColor,
        'extendedProps' => [
            'description' => $event['description'] ?? '',
            'eventType' => $event['eventType'] ?? 'event'
        ]
    ];
}

// 3. Добавляем достижения
foreach ($achievements as $ach) {
    $color = match ($ach['medal']) {
        'gold' => '#FFD700',
        'silver' => '#C0C0C0',
        'bronze' => '#CD7F32',
        default => '#61b659ff'
    };
    $fcEvents[] = [
        'id' => 'ach_' . $ach['achievementID'],
        'title' => ' ' . $ach['title'],
        'start' => $ach['dateAwarded'],
        'allDay' => true,
        'color' => $color,
        'extendedProps' => [
            'description' => implode("\n", array_filter([
                '' . $ach['title'],
                'Type: ' . ucfirst($ach['type']),
                (!empty($ach['medal']) && $ach['medal'] !== 'none') ? 'Medal: ' . ucfirst($ach['medal']) : null,
                (!empty($ach['place'])) ? 'Place: ' . (int)$ach['place'] : null
            ])),
            'eventType' => 'achievement'
        ]
    ];
}

// 4. Добавляем приватные уроки отдельно
$stmt = $pdo->prepare("SELECT e.*, ce.createdBy FROM events e
    LEFT JOIN child_event ce ON e.eventID = ce.eventID
    WHERE e.eventType='private_lesson' AND ce.childID=?");
$stmt->execute([$childID]);
$privateLessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($privateLessons as $event) {
    $fcEvents[] = [
        'id' => $event['eventID'],
        'title' => '🎯 ' . $event['title'],
        'start' => $event['date'] . 'T' . $event['time'],
        'allDay' => false,
        'color' => '#fffd69ff', // ярко-розовый для приватного урока
        'extendedProps' => [
            'description' => $event['description']
        ]
    ];
}

$fcEventsJson = json_encode($fcEvents, JSON_UNESCAPED_UNICODE);
?>



<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title> Profile Child: <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/child_profile.css">
  <!-- <link rel="stylesheet" href="css/main.css"> -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
 <!-- Подключаем сам FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<!-- Подключаем только английскую локаль (en-gb или en) -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/en-gb.global.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <?php include 'header.php'; ?>

  <?php if (basename($_SERVER['PHP_SELF']) == 'child_profile.php'): ?>
  
<?php endif; ?>


<main class="profile-container">
  <div class="left-column">
    <section class="card profile-header">
      <div class="avatar">
  <img src="<?= htmlspecialchars($imagePath) ?>" alt="Фото ребенка" class="avatar-preview" id="imagePreview">
  </div>
      <div>
        <h1> <?= htmlspecialchars($child['name']) ?></h1>
        <p><strong><i data-lucide="cake"></i> Age:</strong> <?= htmlspecialchars($child['age']) ?> y.o.</p>
        <p><strong><i data-lucide="school"></i> Level:</strong> <?= htmlspecialchars($child['groupLevel']) ?></p>
        <p><strong><i data-lucide="user-circle"></i> Gender:</strong> <?= htmlspecialchars($child['gender']) ?></p>
        <p><a href="edit_child.php?childID=<?= $childID ?>" class="button"><i data-lucide="edit-3"></i> Edit</a></p>
      </div>
    </section>

    <section class="card monthly-schedule">
  <h2><i data-lucide="calendar"></i> Schedule Management</h2>

  <?php if(empty($scheduleList)): ?>
    <p>No schedule added yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Day</th>
          <th>Start</th>
          <th>End</th>
          <th>Activity</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($scheduleList as $sched): ?>
          <tr>
            <td><?= htmlspecialchars($sched['dayOfWeek']) ?></td>
            <td><?= substr($sched['startTime'],0,5) ?></td>
            <td><?= substr($sched['endTime'],0,5) ?></td>
            <td><?= htmlspecialchars($sched['activity']) ?></td>
            <td>
              <a href="edit_schedule.php?scheduleID=<?= $sched['scheduleID'] ?>" class="btn-small"><i data-lucide="edit-3"></i></a>
              <a href="delete_schedule.php?scheduleID=<?= $sched['scheduleID'] ?>&childID=<?= $childID ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this schedule?');">
                <i data-lucide="trash-2"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <p><a href="add_schedule.php?childID=<?= $childID ?>" class="button">
    <i data-lucide="plus-circle"></i> Add new schedule</a></p>
  <?php endif; ?>
 <!-- <h2><i data-lucide="target"></i> Запросы на уроки</h2> -->
  
  <!-- Кнопка для открытия модального окна -->
  <!-- <button class="button neon-btn" onclick="openModal()">
    <i data-lucide="plus-circle"></i> Сделать запрос
  </button> -->
  
</section>

<!-- Кнопка для открытия модального окна -->
<!-- Секция на странице профиля -->



    <section class="card schedule-events-section">
      <h2><i data-lucide="calendar-days"></i> Events</h2>

      <?php if (empty($events)): ?>
        <p>There are no added events.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($events as $event): ?>
            <li>
              <strong><?= htmlspecialchars($event['title']) ?></strong>
              <br><i data-lucide="calendar"></i> <?= htmlspecialchars($event['date']) ?> в <?= htmlspecialchars($event['time']) ?>

              <?php if (!empty($event['location'])): ?>
                <br><i data-lucide="map-pin"></i> <?= htmlspecialchars($event['location']) ?>
              <?php endif; ?>

              <br><i data-lucide="<?= $event['eventType'] === 'training' ? 'dumbbell' : 'trophy' ?>"></i>
              <?= ucfirst($event['eventType']) ?>

              <?php if (!empty($event['description'])): ?>
                <br><i data-lucide="info"></i> <?= nl2br(htmlspecialchars($event['description'])) ?>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <p><a href="event_list_child.php?childID=<?= $childID ?>"><i data-lucide="calendar-search"></i> All events →</a></p>
      <?php endif; ?>

      <p><a href="event_add_child.php?childID=<?= $childID ?>" class="button">
        <i data-lucide="plus-circle"></i> Add an event </a></p>
    </section>

    

   <section class="card payments-section">
  <h2><i data-lucide="credit-card"></i> Recent payments</h2>
  <?php if (empty($payments)): ?>
    <p>No payments yet</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Date</th><th>Amount ($)</th><th>Status</th></tr></thead>
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

    
  <?php endif; ?>
   <p><a href="child_payments.php?childID=<?= $childID ?>"><i data-lucide="wallet"></i> All payments →</a></p>
   <p><a href="add_payment.php?childID=<?= $childID ?>" class="button">
        <i data-lucide="plus-circle"></i> Add payment </a></p>
</section>

  </div>

  <div class="right-column">

    <section class="card calendar-section">
      <h2><i data-lucide="calendar-check-2"></i> Calendar </h2>
      <div id='calendar'></div>

      <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true"></div>

      <div class="glowi-modal-overlay" id="modalOverlay" style="display: none;"></div>
      <div class="glowi-modal" id="viewEventModal" style="display: none;">
        <div class="modal-header">
          <h3 id="viewEventTitle"><i data-lucide="calendar-days"></i> Event</h3>
          <button class="close-button" onclick="closeGlowiModal()">✖</button>
        </div>
        <div class="modal-body">
          <p id="viewEventDetails"> Loading...</p>
        </div>
      </div>
 <br/>
 <br/>
      <h2><i data-lucide="target"></i> Request private class</h2>
  
  <!-- Кнопка для открытия модального окна -->
<button class="button neon-btn" onclick="openModal('lessonModal')">
  <i data-lucide="plus-circle"></i> Do request
</button>

<div class="requests-block">
  <br/>
  <h3>My requests</h3>
  <?php if ($requests): ?>
    <ul>
      <?php foreach ($requests as $req): ?>
        <li class="request <?= $req['status'] ?>">
          <p><strong><?= htmlspecialchars($req['message']) ?></strong></p>
          <p>Status: <?= ucfirst($req['status']) ?></p>
          <?php if ($req['response']): ?>
            <p><em>Answer: <?= htmlspecialchars($req['response']) ?></em></p>
          <?php endif; ?>
          <small><?= $req['requestDate'] ?></small>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>There are no requests yet.</p>
  <?php endif; ?>
</div>

<!-- Overlay -->
<div id="modalOverlay" class="modal-overlay"></div>

<!-- Модальное окно -->
<div id="lessonModal" class="modal">
  <div class="modal-content neon-form">
    <span class="close" onclick="closeModal('lessonModal')">&times;</span>
    <h2><i data-lucide="send"></i> New lesson request</h2>
    <form method="POST" action="send_private_lesson_request.php?childID=<?= $childID ?>">
      <label for="lessonDate">Date:</label>
      <input type="date" name="lessonDate" id="lessonDate" required>

      <label for="lessonTime">Time:</label>
      <input type="time" name="lessonTime" id="lessonTime" required>

      <label for="message">Comment / Notes:</label>
      <textarea name="message" id="message" rows="3"></textarea>

      <div class="actions">
        <button type="submit" class="btn-save"><i data-lucide="send"></i> Send</button>
        <button type="button" class="btn-save" onclick="closeModal('lessonModal')">✖️ Cancel</button>
      </div>
    </form>
  </div>
</div>
    </section>



    <section class="card achievements-section">
      <h2><i data-lucide="medal"></i> Achievements</h2>

      <?php if (empty($achievements)): ?>
        <p>There are no added achievements.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($achievements as $ach): ?>
            <li>
              <strong><?= htmlspecialchars($ach['title']) ?></strong>
              <?= htmlspecialchars($ach['dateAwarded']) ?>

              <?php if (!empty($ach['place'])): ?>
                <br><i data-lucide="award"></i> Place: <strong><?= (int)$ach['place'] ?></strong>
              <?php endif; ?>

              <?php if (!empty($ach['medal']) && $ach['medal'] !== 'none'): ?>
                <br><i data-lucide="star"></i> Award:
                <strong>
                  <?php
                    switch ($ach['medal']) {
                      case 'gold': echo '🥇 Gold '; break;
                      case 'silver': echo '🥈 Silver '; break;
                      case 'bronze': echo '🥉 Bronze '; break;
                      case 'fourth': echo '🎗️ 4th '; break;
                      case 'fifth': echo '🎗️ 5th '; break;
                      case 'sixth': echo '🎗️ 6th '; break;
                      case 'seventh': echo '🎗️ 7th'; break;
                      case 'eighth': echo '🎗️ 8th '; break;
                      case 'honorable': echo 'Certificate of honor 🏵️'; break;
                      default: echo ucfirst($ach['medal']);
                    }
                  ?>
                </strong>
              <?php endif; ?>

              <?php if (!empty($ach['fileURL'])): ?>
                <br><a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank"><i data-lucide="paperclip"></i> File</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <p><a href="child_achievements.php?childID=<?= $childID ?>"><i data-lucide="trophy"></i> All achievements →</a></p>
      <?php endif; ?>

      <p><a href="add_achievement.php?childID=<?= $childID ?>" class="button">
        <i data-lucide="plus-circle"></i> Add Achievement</a></p>
    </section>
  </div>
</main>

<p class="arrow"><a href="dashboard.php" ><i data-lucide="arrow-left"></i> Back to your personal account</a></p>

<?php include 'footer.php'; ?>

<script>
  const childID = <?php echo (int)$childID; ?>;
  const fcEventsFromPHP = <?= json_encode($fcEvents, JSON_UNESCAPED_UNICODE) ?>; 
</script>
<script src="scripts/app.js"></script>
<script>
  lucide.createIcons();
</script>
</body>
</html>
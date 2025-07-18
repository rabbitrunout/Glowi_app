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

$filter = $_GET['filter'] ?? 'all';
$allowed = ['training', 'competition'];

$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');

if (in_array($filter, $allowed)) {
    $stmt = $pdo->prepare("
        SELECT e.*
        FROM events e
        JOIN child_event ce ON e.eventID = ce.eventID
        WHERE ce.childID = ? 
          AND e.eventType = ?
          AND e.date BETWEEN ? AND ?
        ORDER BY e.date DESC, e.time DESC
    ");
    $stmt->execute([$childID, $filter, $firstDayOfMonth, $lastDayOfMonth]);
} else {
    $stmt = $pdo->prepare("
        SELECT e.*
        FROM events e
        JOIN child_event ce ON e.eventID = ce.eventID
        WHERE ce.childID = ? 
          AND e.date BETWEEN ? AND ?
        ORDER BY e.date DESC, e.time DESC
    ");
    $stmt->execute([$childID, $firstDayOfMonth, $lastDayOfMonth]);
}
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        'color' => $event['createdBy'] === 'parent' ? '#3788d8' : '#34a853',
    ];
}

foreach ($achievements as $ach) {
    $color = match ($ach['medal']) {
        'gold' => '#FFD700',
        'silver' => '#C0C0C0',
        'bronze' => '#CD7F32',
        default => '#9b59b6'
    };

    $fcEvents[] = [
        'id' => 'ach_' . $ach['achievementID'],
        'title' => '🏅 ' . $ach['title'],
        'start' => $ach['dateAwarded'],
        'allDay' => true,
        'color' => $color,
        'extendedProps' => [
            'description' => implode("\n", array_filter([
                '🏅 ' . $ach['title'],
                '------------------',
                'Тип: ' . ucfirst($ach['type']),
                (!empty($ach['medal']) && $ach['medal'] !== 'none') ? 'Медаль: ' . ucfirst($ach['medal']) : null,
                (!empty($ach['place'])) ? 'Место: ' . (int)$ach['place'] : null
            ])),
            'eventType' => 'achievement'
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
  <link rel="stylesheet" href="css/main.css">
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
  <?php if (basename($_SERVER['PHP_SELF']) == 'child_profile.php'): ?>
  <link rel="stylesheet" href="/css/child_profile_neon.css">
<?php endif; ?>


<main class="container profile-container">
  <div class="left-column">
    <section class="card profile-header">
      <div class="avatar">
        <?php 
          $imagePath = $child['photoImage'] ?? '';
          if ($imagePath === null || $imagePath === '' || $imagePath === 'placeholder_100.jpg') {
              $imagePath = 'uploads/avatars/placeholder_100.jpg';
          } else {
              if (!str_contains($imagePath, '/')) {
                  $imagePath = 'uploads/avatars/' . $imagePath;
              }
          }
        ?>
        <?php if (!empty($imagePath)): ?>
          <img src="<?= htmlspecialchars($imagePath) ?>" class="child-profile-img" alt="Child Photo">
        <?php endif; ?>
      </div>
      <div>
        <h1><i data-lucide="user"></i> <?= htmlspecialchars($child['name']) ?></h1>
        <p><strong><i data-lucide="cake"></i> Age:</strong> <?= htmlspecialchars($child['age']) ?> y.o.</p>
        <p><strong><i data-lucide="school"></i> Level:</strong> <?= htmlspecialchars($child['groupLevel']) ?></p>
        <p><strong><i data-lucide="user-circle"></i> Gender:</strong> <?= htmlspecialchars($child['gender']) ?></p>
        <p><a href="edit_child.php?childID=<?= $childID ?>" class="button"><i data-lucide="edit-3"></i> Edit</a></p>
      </div>
    </section>

    <section class="card weekly-schedule">
      <h2><i data-lucide="calendar-clock"></i> Weekly schedule</h2>
      <?php if (empty($groupedSchedule)): ?>
        <p>There are no scheduled classes for this week.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>DAY</th><th>TIME</th><th>ACTIVITY</th><th>LOCATION</th></tr>
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
          <thead><tr><th>Day</th><th>Sum ($)</th><th>Status</th></tr></thead>
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
        <p><a href="child_payments.php?childID=<?= $childID ?>"><i data-lucide="wallet"></i> All payments →</a></p>
      <?php endif; ?>
    </section>
  </div>

  <div class="right-column">
    <section class="card calendar-section">
      <h2><i data-lucide="calendar-check-2"></i> Calendar of Events</h2>
      <div id='calendar'></div>

      <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true"></div>

      <div class="glowi-modal-overlay" id="modalOverlay" style="display: none;"></div>
      <div class="glowi-modal" id="viewEventModal" style="display: none;">
        <div class="modal-header">
          <h3 id="viewEventTitle"><i data-lucide="calendar-days"></i> Событие</h3>
          <button class="close-button" onclick="closeGlowiModal()">✖</button>
        </div>
        <div class="modal-body">
          <p id="viewEventDetails"> Loading...</p>
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
                      case 'gold': echo 'Gold 🥇'; break;
                      case 'silver': echo 'Silver 🥈'; break;
                      case 'bronze': echo 'Bronze 🥉'; break;
                      case 'fourth': echo '4th 🎗️'; break;
                      case 'fifth': echo '5th 🎗️'; break;
                      case 'sixth': echo '6th 🎗️'; break;
                      case 'seventh': echo '7th🎗️'; break;
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

<p><a href="dashboard.php"><i data-lucide="arrow-left"></i> Back to your personal account</a></p>

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

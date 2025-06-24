<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Некорректный ID ребенка.");
}

$childID = (int)$_GET['childID'];

// Проверяем, что ребёнок принадлежит родителю
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("Ребенок не найден или доступ запрещён.");
}

// Обработка добавления достижения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_achievement'])) {
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $fileURL = trim($_POST['fileURL'] ?? '');
    $dateAwarded = $_POST['dateAwarded'] ?? date('Y-m-d');

    // Новые поля
    $medal = $_POST['medal'] ?? 'none';
    $place = !empty($_POST['place']) ? (int)$_POST['place'] : null;

    if ($title && $type) {
        $stmt = $pdo->prepare("INSERT INTO achievements (childID, title, type, fileURL, dateAwarded, medal, place) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$childID, $title, $type, $fileURL, $dateAwarded, $medal, $place]);
        header("Location: child_profile.php?childID=$childID");
        exit;
    } else {
        $achievement_error = "Пожалуйста, заполните все обязательные поля.";
    }
}

// Получаем последние 5 достижений
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE childID = ? ORDER BY dateAwarded DESC LIMIT 5");
$stmt->execute([$childID]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем последние 5 платежей
$stmt = $pdo->prepare("SELECT * FROM payments WHERE childID = ? ORDER BY paymentDate DESC LIMIT 5");
$stmt->execute([$childID]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем события, связанные с этим ребёнком
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

// Получаем последние 5 событий, связанных с ребенком
$stmt = $pdo->prepare("
    SELECT e.* FROM events e
    JOIN child_event ce ON e.eventID = ce.eventID
    WHERE ce.childID = ?
    ORDER BY e.date DESC
    LIMIT 5
");
$stmt->execute([$childID]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль ребёнка — <?= htmlspecialchars($child['name']) ?></title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php 'header.php'; ?>

<main class="container profile-container">

  <div class="left-column">
    <section class="card profile-header">
      <div class="avatar"></div>
      <div>
        <h1>Профиль ребёнка: <?= htmlspecialchars($child['name']) ?></h1>
        <p><strong>Возраст:</strong> <?= htmlspecialchars($child['age']) ?> лет</p>
        <p><strong>Группа / уровень:</strong> <?= htmlspecialchars($child['groupLevel']) ?></p>
        <?php if (!empty($child['photoImage'])): ?>
          <p><img src="<?= htmlspecialchars($child['photoImage']) ?>" alt="Фото <?= htmlspecialchars($child['name']) ?>" style="max-width:200px; border-radius: 12px;"></p>
        <?php endif; ?>
      </div>
    </section>

    <section class="card schedule-events-section">
      <h2>Расписание и события</h2>

      <form method="get" style="margin-bottom: 15px;">
          <input type="hidden" name="childID" value="<?= $childID ?>">
          <label for="filter">Фильтр событий:</label>
          <select name="filter" id="filter" onchange="this.form.submit()">
              <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Все</option>
              <option value="training" <?= $filter === 'training' ? 'selected' : '' ?>>Тренировки</option>
              <option value="competition" <?= $filter === 'competition' ? 'selected' : '' ?>>Соревнования</option>
          </select>
      </form>

      <?php if (empty($events)): ?>
        <p>Нет запланированных событий.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($events as $event): ?>
            <li>
              <strong><?= htmlspecialchars($event['title']) ?></strong>
              (<?= $event['eventType'] === 'training' ? 'Тренировка' : 'Соревнование' ?>),
              <?= htmlspecialchars($event['date']) ?> в <?= htmlspecialchars($event['time']) ?>,
              место: <?= htmlspecialchars($event['location']) ?>
              <br>
              <em><?= nl2br(htmlspecialchars($event['description'])) ?></em>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section class="card payments-section">
      <h2>Последние платежи</h2>
      <?php if (empty($payments)): ?>
        <p>Платежи не найдены.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Дата</th>
              <th>Сумма (₽)</th>
              <th>Статус</th>
            </tr>
          </thead>
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
        <p><a href="child_payments.php?childID=<?= $childID ?>">Все платежи →</a></p>
      <?php endif; ?>
    </section>
  </div>

  <div class="right-column">
    <section class="card calendar-section">
      <h2>Календарь событий</h2>
      <!-- Тут вставьте календарь или его рендеринг -->
      <div id="calendar">
        <!-- Пример: простой календарь / события -->
        <p>(Календарь пока не реализован)</p>
      </div>
    </section>

    <section class="card achievements-section">
      <h2>Последние достижения</h2>
      <?php if (empty($achievements)): ?>
        <p>Достижения не добавлены.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($achievements as $ach): ?>
            <li class="achievement">
              <strong><?= htmlspecialchars($ach['title']) ?></strong> (<?= htmlspecialchars($ach['type']) ?>),
              <?= htmlspecialchars($ach['dateAwarded']) ?>
              <?php if (!empty($ach['medal']) && $ach['medal'] !== 'none'): ?>
                &nbsp;<img src="images/medals/<?= htmlspecialchars($ach['medal']) ?>.png" alt="<?= htmlspecialchars($ach['medal']) ?> медаль" title="<?= ucfirst(htmlspecialchars($ach['medal'])) ?> медаль" style="vertical-align: middle; width: 20px; height: 20px;">
              <?php endif; ?>
              <?php if (!empty($ach['place'])): ?>
                , место: <?= (int)$ach['place'] ?>
              <?php endif; ?>
              <?php if (!empty($ach['fileURL'])): ?>
                <br><a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank" rel="noopener noreferrer">Просмотреть файл</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p><a href="child_achievements.php?childID=<?= $childID ?>">Все достижения →</a></p>
      <?php endif; ?>

      <h3>Добавить достижение</h3>
      <?php if (!empty($achievement_error)): ?>
        <p style="color:red;"><?= htmlspecialchars($achievement_error) ?></p>
      <?php endif; ?>
      <form method="POST" action="child_profile.php?childID=<?= $childID ?>">
        <input type="hidden" name="add_achievement" value="1">

        <label>Название:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Тип достижения:</label><br>
        <input type="text" name="type" required placeholder="Например, 'соревнование', 'тренировка'"><br><br>

        <label>Медаль:</label><br>
        <select name="medal">
          <option value="none">Без медали</option>
          <option value="gold">Золото</option>
          <option value="silver">Серебро</option>
          <option value="bronze">Бронза</option>
        </select><br><br>

        <label>Занятое место:</label><br>
        <input type="number" name="place" min="1" max="100" placeholder="1, 2, 3 ..."><br><br>

        <label>Ссылка на файл (URL):</label><br>
        <input type="url" name="fileURL"><br><br>

        <label>Дата награждения:</label><br>
        <input type="date" name="dateAwarded" value="<?= date('Y-m-d') ?>"><br><br>

        <button type="submit">Добавить достижение</button>
      </form>
    </section>
  </div>

</main>

<p><a href="dashboard.php">← Назад в личный кабинет</a></p>



<?php 'footer.php'; ?>
</body>
</html>

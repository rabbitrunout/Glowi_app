<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : die("Некорректный ID ребенка.");

// Проверка, что ребенок принадлежит родителю (для безопасности)
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("Ребенок не найден или доступ запрещён.");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dayOfWeek = $_POST['dayOfWeek'] ?? '';
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';
    $activity = trim($_POST['activity'] ?? '');

    // Валидация
    $allowedDays = ['Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Воскресенье'];
    if (!in_array($dayOfWeek, $allowedDays)) {
        $errors[] = "Неверный день недели.";
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $startTime)) {
        $errors[] = "Неверное время начала.";
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $endTime)) {
        $errors[] = "Неверное время окончания.";
    }
    if (empty($activity)) {
        $errors[] = "Введите название активности.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO schedule (childID, dayOfWeek, startTime, endTime, activity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$childID, $dayOfWeek, $startTime.':00', $endTime.':00', $activity]);
        header("Location: profile.php?childID=$childID");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Добавить расписание для <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css" />
</head>
<body>
<?php include 'header.php'; ?>

<main class="container">
  <h1>Добавить расписание для <?= htmlspecialchars($child['name']) ?></h1>

  <?php if ($errors): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <label for="dayOfWeek">День недели:</label>
    <select name="dayOfWeek" id="dayOfWeek" required>
      <option value="">-- выберите день --</option>
      <?php
        $days = ['Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Воскресенье'];
        foreach ($days as $day) {
          $selected = (isset($_POST['dayOfWeek']) && $_POST['dayOfWeek'] === $day) ? 'selected' : '';
          echo "<option value=\"$day\" $selected>$day</option>";
        }
      ?>
    </select>

    <label for="startTime">Время начала (HH:MM):</label>
    <input type="time" id="startTime" name="startTime" value="<?= htmlspecialchars($_POST['startTime'] ?? '') ?>" required />

    <label for="endTime">Время окончания (HH:MM):</label>
    <input type="time" id="endTime" name="endTime" value="<?= htmlspecialchars($_POST['endTime'] ?? '') ?>" required />

    <label for="activity">Активность:</label>
    <input type="text" id="activity" name="activity" value="<?= htmlspecialchars($_POST['activity'] ?? '') ?>" required />

    <button type="submit">Добавить</button>
  </form>

  <p><a href="child_profile.php?childID=<?= $childID ?>">← Вернуться к профилю</a></p>
</main>

<?php include 'footer.php'; ?>
</body>
</html>

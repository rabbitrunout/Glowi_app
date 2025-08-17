<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header("Location: login_form.php");
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : die("Некорректный ID ребенка.");

// Проверка принадлежности ребёнка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) die("Ребёнок не найден или доступ запрещён.");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dayOfWeek = $_POST['dayOfWeek'] ?? '';
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';
    $activity = trim($_POST['activity'] ?? '');

    $allowedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    if ($dayOfWeek && in_array($dayOfWeek, $allowedDays) && $startTime && $endTime && $activity) {
        $stmt = $pdo->prepare("INSERT INTO schedule (childID, dayOfWeek, startTime, endTime, activity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$childID, $dayOfWeek, $startTime, $endTime, $activity]);
        $success = "Занятие успешно добавлено!";
    } else {
        $error = "Пожалуйста, заполните все поля корректно.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Add Schedule — <?= htmlspecialchars($child['name']) ?></title>
<link rel="stylesheet" href="css/main.css">
<script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card">
  <h2><i data-lucide="plus-circle"></i> Add schedule — <?= htmlspecialchars($child['name']) ?></h2>

  <?php if ($error): ?>
    <div class="glowi-message error"><i data-lucide="alert-triangle"></i> <?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
    <div class="glowi-message success"><i data-lucide="check-circle"></i> <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST" class="styled-form">
    <label>Day of the week:</label>
    <select name="dayOfWeek" required>
      <option value="">Select a day</option>
      <option value="Monday">Monday</option>
      <option value="Tuesday">Tuesday</option>
      <option value="Wednesday">Wednesday</option>
      <option value="Thursday">Thursday</option>
      <option value="Friday">Friday</option>
      <option value="Saturday">Saturday</option>
      <option value="Sunday">Sunday</option>
    </select>

    <label>Start Time:</label>
    <input type="time" name="startTime" required>

    <label>End Time:</label>
    <input type="time" name="endTime" required>

    <label>Activity:</label>
    <input type="text" name="activity" placeholder="e.g., Ballet, Swimming" required>

    <button type="submit" class="btn-save"><i data-lucide="plus-circle"></i> Add Schedule</button>
  </form>

  <p><a href="child_profile.php?childID=<?= $childID ?>" class="btn-secondary">← Back to profile</a></p>
</main>

<?php include 'footer.php'; ?>
<script> lucide.createIcons(); </script>
</body>
</html>

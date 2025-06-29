<?php
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

// Проверяем, что ребенок принадлежит родителю
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("Ребенок не найден или доступ запрещён.");
}

// Получаем достижения ребенка
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE childID = ? ORDER BY dateAwarded DESC");
$stmt->execute([$childID]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
    <title>Progress <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/achievements.css" />
</head>
<body>
<?php include 'header.php'; ?>

<h1>Child's achievements: <?= htmlspecialchars($child['name']) ?></h1>

<section class="achievements-section card">
  <?php if (empty($achievements)): ?>
      <p>There are no added achievements yet.</p>
  <?php else: ?>
      <ul>
          <?php foreach ($achievements as $ach): ?>
              <li class="achievement">
                  <strong><?= htmlspecialchars($ach['title']) ?></strong>
                  <span>(<?= htmlspecialchars($ach['type']) ?>)</span>, date: <?= htmlspecialchars($ach['dateAwarded']) ?><br>
                  <a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank" class="button">View the file</a>
              </li>
          <?php endforeach; ?>
      </ul>
  <?php endif; ?>
</section>

<p><a href="child_profile.php?childID=<?= $childID ?>" class="button">← Back to the child's profile</a></p>


<?php include 'footer.php'; ?>
</body>
</html>

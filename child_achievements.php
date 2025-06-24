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
  <title>Достижения <?= htmlspecialchars($child['name']) ?></title>
</head>
<body>
<?php  'header.php'; ?>

<h1>Достижения ребенка: <?= htmlspecialchars($child['name']) ?></h1>

<?php if (empty($achievements)): ?>
    <p>Пока нет добавленных достижений.</p>
<?php else: ?>
    <ul>
        <?php foreach ($achievements as $ach): ?>
            <li>
                <strong><?= htmlspecialchars($ach['title']) ?></strong> (<?= htmlspecialchars($ach['type']) ?>), дата: <?= htmlspecialchars($ach['dateAwarded']) ?><br>
                <a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank">Просмотреть файл</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="dashboard.php">← Назад в личный кабинет</a></p>

<?php  'footer.php'; ?>
</body>
</html>

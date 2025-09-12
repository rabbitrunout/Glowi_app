<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header("Location: login_form.php");
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = (int)($_POST['childID'] ?? 0);
$achievementID = (int)($_POST['achievementID'] ?? 0);

// Проверяем, что достижение принадлежит именно этому родителю
$stmt = $pdo->prepare("
    SELECT a.*
    FROM achievements a
    JOIN children c ON a.childID = c.childID
    WHERE a.achievementID = ? AND a.childID = ? AND c.parentID = ?
");
$stmt->execute([$achievementID, $childID, $parentID]);
$ach = $stmt->fetch();

if (!$ach) {
    die("Achievement not found or access denied.");
}

// Получаем данные формы
$title = trim($_POST['title'] ?? '');
$type = $_POST['type'] ?? 'medal';
$dateAwarded = $_POST['dateAwarded'] ?? '';
$place = $_POST['place'] !== '' ? (int)$_POST['place'] : null;
$medal = $_POST['medal'] ?? 'none';

// Обновляем
$stmt = $pdo->prepare("
    UPDATE achievements
    SET title = ?, type = ?, dateAwarded = ?, place = ?, medal = ?
    WHERE achievementID = ? AND childID = ?
");
$stmt->execute([$title, $type, $dateAwarded, $place, $medal, $achievementID, $childID]);

// Возвращаем обратно на страницу ребёнка
header("Location: child_achievements.php?childID=$childID");
exit;

?>
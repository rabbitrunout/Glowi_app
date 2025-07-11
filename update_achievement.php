<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header("Location: login_form.php");
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = $_POST['childID'] ?? 0;
$achievementID = $_POST['achievementID'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM achievements WHERE achievementID = ? AND childID = ?");
$stmt->execute([$achievementID, $childID]);
$ach = $stmt->fetch();

if (!$ach) {
    die("Достижение не найдено или доступ запрещен.");
}

$title = $_POST['title'] ?? '';
$type = $_POST['type'] ?? 'medal';
$dateAwarded = $_POST['dateAwarded'] ?? '';
$place = $_POST['place'] ?? null;
$medal = $_POST['medal'] ?? 'none';

$stmt = $pdo->prepare("
    UPDATE achievements
    SET title = ?, type = ?, dateAwarded = ?, place = ?, medal = ?
    WHERE achievementID = ? AND childID = ?
");
$stmt->execute([$title, $type, $dateAwarded, $place ?: null, $medal, $achievementID, $childID]);

header("Location: child_achievements.php?childID=$childID");
exit;
?>
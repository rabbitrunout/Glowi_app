<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$childID = $_POST['childID'] ?? null;
if (!$childID || !isset($_FILES['photoImage'])) {
    die("Ошибка загрузки.");
}

$parentID = $_SESSION['parentID'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['photoImage']['type'], $allowedTypes)) {
    die("Разрешены только изображения JPEG, PNG и GIF.");
}

$ext = pathinfo($_FILES['photoImage']['name'], PATHINFO_EXTENSION);
$targetDir = 'uploads/avatars/';
if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

$filename = uniqid('photoImage_', true) . '.' . $ext;
$targetPath = $targetDir . $filename;

if (move_uploaded_file($_FILES['photoImage']['tmp_name'], $targetPath)) {
    $stmt = $pdo->prepare("UPDATE children SET photoImage = ? WHERE childID = ? AND parentID = ?");
    $stmt->execute([$targetPath, $childID, $parentID]);
    header("Location: child_profile.php?childID=$childID");
} else {
    die("Ошибка при сохранении файла.");
}
?>
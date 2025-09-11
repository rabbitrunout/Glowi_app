<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = $_POST['childID'] ?? null;

if (!$childID) {
    die("Child ID is not specified.");
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
    die("No new photo uploaded.");
}

// Получаем текущее имя фото из базы
$stmt = $pdo->prepare("SELECT photoImage FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    die("Child not found or no access.");
}

// Удаляем старое фото и миниатюры
if (!empty($child['photoImage'])) {
    $targetDir = 'uploads/avatars/';
    $oldFile = $targetDir . $child['photoImage'];
    if (file_exists($oldFile)) unlink($oldFile);
}

// Загружаем новое фото с **оригинальным именем**
$uploadDir = 'uploads/avatars/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$originalName = basename($_FILES['photo']['name']); // оригинальное имя без изменений
$targetFile = $uploadDir . $originalName;

// ⚡ Если файл с таким именем уже существует, можно его перезаписать или добавить проверку
if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
    die("Failed to upload new photo.");
}

// Обновляем запись в базе
$stmt = $pdo->prepare("UPDATE children SET photoImage = ? WHERE childID = ? AND parentID = ?");
$stmt->execute([$originalName, $childID, $parentID]);

header('Location: child_profile.php?childID=' . $childID);
exit;
?>

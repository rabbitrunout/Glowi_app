<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$childID = $_POST['childID'] ?? null;
$parentID = $_SESSION['parentID'];

if (!$childID) {
    die("ID the child is not specified.");
}

// Получаем имя файла фото
$stmt = $pdo->prepare("SELECT photoImage FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    die("The child is not found or there is no access.");
}

// Удаляем изображение, если оно есть
if (!empty($child['photoImage'])) {
    $targetDir = 'uploads/avatars/';
    $baseName = pathinfo($child['photoImage'], PATHINFO_FILENAME);
    $ext = '.' . pathinfo($child['photoImage'], PATHINFO_EXTENSION);

    foreach (["", "_100", "_400"] as $suffix) {
        $file = $targetDir . $baseName . $suffix . $ext;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

// Удаляем ребёнка из базы данных
$stmt = $pdo->prepare("DELETE FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);

header('Location: children_list.php');
exit;
?>

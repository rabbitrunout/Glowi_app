<?php
session_start();
require 'database.php';
require 'image_functions.php'; // Помести функции process_image и resize_image сюда

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$childID = $_POST['childID'] ?? null;
if (!$childID || !isset($_FILES['photoImage'])) {
    die("Ошибка загрузки.");
}

$parentID = $_SESSION['parentID'];
$allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
$allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

$fileMime = $_FILES['photoImage']['type'];
$fileExt = strtolower(pathinfo($_FILES['photoImage']['name'], PATHINFO_EXTENSION));

if (!in_array($fileMime, $allowedMime) || !in_array($fileExt, $allowedExt)) {
    die("Разрешены только изображения JPEG, PNG и GIF.");
}

$targetDir = 'uploads/avatars/';
if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

$filename = uniqid('photoImage_', true) . '.' . $fileExt;
$targetPath = $targetDir . $filename;

// Проверим, есть ли старое фото
$stmt = $pdo->prepare("SELECT photoImage FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    die("Ребёнок не найден или нет доступа.");
}

if (move_uploaded_file($_FILES['photoImage']['tmp_name'], $targetPath)) {
    // Обработка изображения: создаём версии 400 и 100
    process_image($targetDir, $filename);

    // Удалим старое изображение и его версии, если были
    if (!empty($child['photoImage'])) {
        $oldBase = $targetDir . pathinfo($child['photoImage'], PATHINFO_FILENAME);
        $oldExt = '.' . pathinfo($child['photoImage'], PATHINFO_EXTENSION);

        foreach (["", "_100", "_400"] as $suffix) {
            $oldFile = $oldBase . $suffix . $oldExt;
            if (file_exists($oldFile)) unlink($oldFile);
        }
    }

    // Сохраняем новое имя оригинального файла
    $stmt = $pdo->prepare("UPDATE children SET photoImage = ? WHERE childID = ? AND parentID = ?");
    $stmt->execute([$filename, $childID, $parentID]);

    header("Location: child_profile.php?childID=$childID");
    exit;
} else {
    die("Ошибка при сохранении файла.");
}
?>

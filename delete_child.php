<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

// Проверка CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_POST['csrf_token']) ||
    $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')
) {
    die("Ошибка безопасности. Повторите попытку.");
}

$childID = $_POST['childID'] ?? null;
$parentID = $_SESSION['parentID'];

if (!$childID || !is_numeric($childID)) {
    die("Некорректный запрос.");
}

// Получаем фото ребёнка
$stmt = $pdo->prepare("SELECT photoImage FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    die("Ребёнок не найден или нет доступа.");
}

// Удаляем фото (все размеры)
if (!empty($child['photoImage'])) {
    $targetDir = 'uploads/avatars/';
    $base = pathinfo($child['photoImage'], PATHINFO_FILENAME);
    $ext = '.' . pathinfo($child['photoImage'], PATHINFO_EXTENSION);

    foreach (["", "_100", "_400"] as $suffix) {
        $file = $targetDir . $base . $suffix . $ext;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

// Удаляем запись из базы
$stmt = $pdo->prepare("DELETE FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);

// Очистка CSRF токена (опционально)
unset($_SESSION['csrf_token']);

header('Location: children_list.php');
exit;
?>

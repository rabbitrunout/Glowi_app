<?php
session_start();
require 'database.php';

// Для отладки (убрать на проде)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if (!isset($_SESSION['parentID'])) {
        header('Location: login_form.php');
        exit;
    }

    $parentID = $_SESSION['parentID'];
    $childID  = isset($_GET['childID']) ? (int)$_GET['childID'] : null;
    if (!$childID) {
        throw new Exception("Child ID missing.");
    }

    // Данные из формы
    $message = $_POST['message'] ?? '';
    $date    = $_POST['lessonDate'] ?? '';
    $time    = $_POST['lessonTime'] ?? '';

    if (empty($date) || empty($time)) {
        throw new Exception("Date or time not provided.");
    }

    // Формируем дату/время урока
    $lessonDateTime = date('Y-m-d H:i:s', strtotime("$date $time"));

    // Проверим принадлежность ребёнка
    $stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
    $stmt->execute([$childID, $parentID]);
    if (!$stmt->fetch()) {
        throw new Exception("Child not found or access denied.");
    }

    // Вставляем запрос
    $stmt = $pdo->prepare("
        INSERT INTO private_lesson_requests (childID, lessonDateTime, message) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$childID, $lessonDateTime, $message]);

    // Редирект в профиль
    header("Location: child_profile.php?childID=" . $childID);
    exit;

} catch (Exception $e) {
    error_log("Lesson request error: " . $e->getMessage());
    echo "<h2>⚠️ Error</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}


?>
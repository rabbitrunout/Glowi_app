<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $childID = $_POST['childID'] ?? null;
    $lessonDate = $_POST['lessonDate'] ?? null;
    $lessonTime = $_POST['lessonTime'] ?? null;
    $message = trim($_POST['message'] ?? '');

    if (!$childID || !$lessonDate || !$lessonTime) {
        http_response_code(400);
        echo "Missing required fields.";
        exit;
    }

    // Проверяем, что это действительно ребенок этого родителя
    $stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
    $stmt->execute([$childID, $_SESSION['parentID']]);
    $child = $stmt->fetch();
    if (!$child) {
        http_response_code(403);
        echo "Child not found or access denied.";
        exit;
    }

    // Объединяем дату и время в один datetime
    $lessonDateTime = $lessonDate . ' ' . $lessonTime; // YYYY-MM-DD HH:MM

    // Сохраняем в базу
    $stmt = $pdo->prepare("
        INSERT INTO private_lesson_requests (childID, lessonDateTime, message, status, requestDate)
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$childID, $lessonDateTime, $message]);

    echo "ok";
} else {
    http_response_code(405);
    echo "Method not allowed.";
}
?>
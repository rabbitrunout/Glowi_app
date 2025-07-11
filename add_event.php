<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = $_POST['childID'] ?? null;

$title = $_POST['title'] ?? '';
$type = $_POST['eventType'] ?? '';
$desc = $_POST['description'] ?? '';
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$location = $_POST['location'] ?? '';

if ($childID && $title && $type && $date && $time && $location) {
    // Добавляем событие
    $stmt = $pdo->prepare("INSERT INTO events (title, eventType, description, date, time, location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $type, $desc, $date, $time, $location]);
    $eventID = $pdo->lastInsertId();

    // Привязываем к ребёнку
    $stmt = $pdo->prepare("INSERT INTO child_event (eventID, childID, createdBy) VALUES (?, ?, 'parent')");
    $stmt->execute([$eventID, $childID]);

    header("Location: event_list_child.php?childID=$childID");
    exit;
} else {
    die("Ошибка: не все поля заполнены.");
}
?>
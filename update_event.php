<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$eventID = $_POST['eventID'] ?? null;
$childID = $_POST['childID'] ?? null;

$title = $_POST['title'] ?? '';
$type = $_POST['eventType'] ?? '';
$desc = $_POST['description'] ?? '';
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$location = $_POST['location'] ?? '';

if ($eventID && $title && $type && $date && $time && $location) {
    $stmt = $pdo->prepare("
        UPDATE events 
        SET title = ?, eventType = ?, description = ?, date = ?, time = ?, location = ?
        WHERE eventID = ?
    ");
    $stmt->execute([$title, $type, $desc, $date, $time, $location, $eventID]);

    header("Location: event_list_child.php?childID=$childID");
    exit;
} else {
    die("Ошибка обновления: не все данные заполнены.");
}
?>
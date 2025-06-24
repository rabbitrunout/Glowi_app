<?php
require 'database.php';

$data = [
    'title' => $_POST['title'] ?? '',
    'eventType' => $_POST['eventType'] ?? '',
    'description' => $_POST['description'] ?? '',
    'date' => $_POST['date'] ?? '',
    'time' => $_POST['time'] ?? '',
    'location' => $_POST['location'] ?? '',
];

if (isset($_POST['eventID'])) {
    // Обновление
    $stmt = $pdo->prepare("UPDATE events SET title = ?, eventType = ?, description = ?, date = ?, time = ?, location = ? WHERE eventID = ?");
    $stmt->execute([$data['title'], $data['eventType'], $data['description'], $data['date'], $data['time'], $data['location'], $_POST['eventID']]);
} else {
    // Создание
    $stmt = $pdo->prepare("INSERT INTO events (title, eventType, description, date, time, location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['title'], $data['eventType'], $data['description'], $data['date'], $data['time'], $data['location']]);
}

header("Location: event_list.php"); // список всех событий
exit;
?>
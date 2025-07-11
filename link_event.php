<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$childID = $_POST['childID'] ?? null;
$eventID = $_POST['eventID'] ?? null;

if ($childID && $eventID) {
    // Проверим, не привязано ли уже
    $stmt = $pdo->prepare("SELECT * FROM child_event WHERE eventID = ? AND childID = ?");
    $stmt->execute([$eventID, $childID]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO child_event (eventID, childID, createdBy) VALUES (?, ?, 'parent')");
        $stmt->execute([$eventID, $childID]);
    }

    header("Location: event_list_child.php?childID=$childID");
    exit;
} else {
    die("Ошибка привязки события.");
}
?>
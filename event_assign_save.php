<?php
require 'database.php';

$eventID = $_POST['eventID'];
$selectedChildren = $_POST['children'] ?? [];

// Удалим старые связи
$stmt = $pdo->prepare("DELETE FROM child_event WHERE eventID = ?");
$stmt->execute([$eventID]);

// Добавим новые
if (!empty($selectedChildren)) {
    $stmt = $pdo->prepare("INSERT INTO child_event (eventID, childID) VALUES (?, ?)");
    foreach ($selectedChildren as $childID) {
        $stmt->execute([$eventID, $childID]);
    }
}

header("Location: event_assign.php?eventID=$eventID");
exit;
?>
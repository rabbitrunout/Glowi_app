<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header("Location: login_form.php");
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_POST['childID']) ? (int)$_POST['childID'] : 0;

// Проверка прав доступа
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("Error: The child has not been found or access is denied..");
}

// Данные из формы
$eventID = isset($_POST['eventID']) ? (int)$_POST['eventID'] : null;
$title = trim($_POST['title'] ?? '');
$eventType = $_POST['eventType'] ?? '';
$description = trim($_POST['description'] ?? '');
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$location = trim($_POST['location'] ?? '');

if (!$title || !$eventType || !$date || !$time || !$location) {
    die("Please fill in all required fields..");
}

try {
    if ($eventID) {
        // 🔄 Обновление события
        $stmt = $pdo->prepare("
            UPDATE events SET 
              title = ?, eventType = ?, description = ?, date = ?, time = ?, location = ?, updated_at = NOW()
            WHERE eventID = ?
        ");
        $stmt->execute([$title, $eventType, $description, $date, $time, $location, $eventID]);
    } else {
        // ➕ Создание события
        $stmt = $pdo->prepare("
            INSERT INTO events (title, eventType, description, date, time, location) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $eventType, $description, $date, $time, $location]);
        $eventID = $pdo->lastInsertId();

        // 🔗 Привязка к ребёнку
        $stmt = $pdo->prepare("
            INSERT INTO child_event (eventID, childID, createdBy)
            VALUES (?, ?, 'parent')
        ");
        $stmt->execute([$eventID, $childID]);
    }

    header("Location: child_profile.php?childID=$childID");
    exit;

} catch (PDOException $e) {
    echo "Error when saving:" . $e->getMessage();
}
?>
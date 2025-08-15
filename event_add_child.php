<?php
session_start();
require 'database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Включить отображение ошибок PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : die("Некорректный ID ребенка.");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $eventType = strtolower(trim($_POST['eventType']));
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $location = trim($_POST['location']);

    if ($title && $eventType && $eventDate && $eventTime) {
        try {
            // Добавляем событие в таблицу events
            $stmt = $pdo->prepare("INSERT INTO events (title, description, eventType, date, time, location) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $eventType, $eventDate, $eventTime, $location]);

            $eventID = $pdo->lastInsertId();

            // Привязываем событие к ребёнку через таблицу child_event
            $stmt2 = $pdo->prepare("INSERT INTO child_event (eventID, childID, createdBy) VALUES (?, ?, 'parent')");
            $stmt2->execute([$eventID, $childID]);

            // Можно перенаправить или отобразить сообщение
            $message = '<div class="glowi-message success"><i data-lucide="check-circle"></i> Событие успешно добавлено!</div>';
        } catch (PDOException $e) {
            $message = '<div class="glowi-message error"><i data-lucide="x-circle"></i> Ошибка БД: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $message = '<div class="glowi-message error"><i data-lucide="alert-triangle"></i> Пожалуйста, заполните все обязательные поля.</div>';
    }
}

// Загрузка всех событий ребёнка
$stmt = $pdo->prepare("
    SELECT e.*
    FROM events e
    JOIN child_event ce ON e.eventID = ce.eventID
    WHERE ce.childID = ?
    ORDER BY e.date DESC, e.time DESC
");
$stmt->execute([$childID]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
    <link rel="stylesheet" href="css/event_add_child.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="form-wrapper">
    <main class="container glowi-card">
        <form method="POST">
            <h2><i data-lucide="calendar-plus"></i> Добавить событие</h2>
            <?= $message ?>

            <label for="title">Название</label>
            <input type="text" name="title" id="title" required>

            <label for="eventType">Тип события</label>
            <select name="eventType" id="eventType" required>
                <option value="">-- Выберите тип --</option>
                <option value="training">Тренировка</option>
                <option value="competition">Соревнование</option>
            </select>

            <label for="description">Описание</label>
            <input type="text" name="description" id="description">

            <label for="eventDate">Дата</label>
            <input type="date" name="eventDate" id="eventDate" required>

            <label for="eventTime">Время</label>
            <input type="time" name="eventTime" id="eventTime" required>

            <label for="location">Место проведения</label>
            <input type="text" name="location" id="location">

            <button type="submit" class="btn-save">Создать событие</button>
            <a href="child_profile.php?childID=<?= $childID ?>">← Назад в профиль</a>
        </form>
    </main>
</div>




<?php include 'footer.php'; ?>

<script src="https://unpkg.com/lucide@0.292.0"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        lucide.createIcons();
    });
</script>
</body>
</html>

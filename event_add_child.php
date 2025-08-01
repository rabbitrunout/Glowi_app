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
    $eventType = $_POST['eventType'];
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $location = trim($_POST['location']);

    if ($title && $eventType && $eventDate && $eventTime) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (childID, title, description, eventType, eventDate, eventTime, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$childID, $title, $description, $eventType, $eventDate, $eventTime, $location]);

            $message = '<div class="glowi-message success"><i data-lucide="check-circle"></i> Событие успешно добавлено!</div>';
        } catch (PDOException $e) {
            $message = '<div class="glowi-message error"><i data-lucide="x-circle"></i> Ошибка БД: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $message = '<div class="glowi-message error"><i data-lucide="alert-triangle"></i> Пожалуйста, заполните все обязательные поля.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить событие</title>
    <link rel="stylesheet" href="css/event_add_child.css">
</head>
<body>
  <?php include 'header.php'; ?>
    <div class="form-wrapper">
        <form class="glowi-form" method="POST">
            <h2><i data-lucide="calendar-plus"></i> Добавить событие</h2>
            <?= $message ?>

            <input type="text" name="title" placeholder="Название" required>
            <select name="eventType" required>
                <option value="">-- Тип события --</option>
                <option value="Training">Тренировка</option>
                <option value="Competition">Соревнование</option>
                <option value="Other">Другое</option>
            </select>
            <input type="text" name="description" placeholder="Описание">
            <input type="date" name="eventDate" required>
            <input type="time" name="eventTime" required>
            <input type="text" name="location" placeholder="Место">

            <button type="submit">Создать событие</button>
            <a href="child_profile.php?childID=<?= $childID ?>" class="back-link">← Назад к профилю</a>
        </form>
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

<!-- request_lesson.php -->
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['parentID'])) {
    header("Location: login.php");
    exit;
}

$childID = $_GET['childID'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);

    $stmt = $pdo->prepare("INSERT INTO private_lesson_requests (childID, message) VALUES (?, ?)");
    $stmt->execute([$childID, $message]);

    header("Location: child_profile.php?childID=$childID&success=1");
    exit;
}
?>

<form method="POST" class="glowi-form">
  <h2>Запрос на приватное занятие</h2>
  <textarea name="message" required placeholder="Опишите пожелания к занятию"></textarea>
  <button type="submit">Отправить запрос</button>
</form>

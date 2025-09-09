<!-- request_lesson.php -->
<?php
session_start();
require 'database.php';

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


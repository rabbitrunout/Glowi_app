<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) ? (int)$_GET['childID'] : die("Child ID missing");

$message = $_POST['message'] ?? '';
$date = $_POST['lessonDate'] ?? '';
$time = $_POST['lessonTime'] ?? '';

$datetime = $date . ' ' . $time;

$stmt = $pdo->prepare("INSERT INTO private_lesson_requests (childID, message, requestDate) VALUES (?, ?, ?)");
$stmt->execute([$childID, $message, $datetime]);

header("Location: child_profile.php?childID=$childID");
exit;
?>
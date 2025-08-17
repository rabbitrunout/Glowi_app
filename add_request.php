<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $childID = (int)$_POST['childID'];
    $message = trim($_POST['message']);

    if ($childID && $message !== '') {
        $stmt = $pdo->prepare("INSERT INTO private_lesson_requests (childID, message) VALUES (?, ?)");
        $stmt->execute([$childID, $message]);
        header("Location: child_requests.php?childID=" . $childID);
        exit;
    } else {
        echo "Пожалуйста, заполните сообщение.";
    }
}
?>

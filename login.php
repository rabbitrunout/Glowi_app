<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['userName'];
    $password = $_POST['password'];

    // Поиск пользователя по userName (логину)
    $stmt = $pdo->prepare("SELECT * FROM parents WHERE userName = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() === 0) {
        header("Location: login_form.php?error=1");
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($password, $user['password'])) {
        $_SESSION['parentID'] = $user['parentID'];
        $_SESSION['userName'] = $user['userName'];
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: login_form.php?error=1");
        exit;
    }
}
?>

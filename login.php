<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['userName'] ?? '');
    $password = $_POST['password'] ?? '';

    // Если поля пустые — возвращаем ошибку
    if ($username === '' || $password === '') {
        header("Location: login_form.php?error=1");
        exit;
    }

    // Поиск пользователя по userName
    $stmt = $pdo->prepare("SELECT * FROM parents WHERE userName = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Устанавливаем сессию
        $_SESSION['parentID'] = $user['parentID'];
        $_SESSION['userName'] = $user['userName'];

        // Перенаправляем сразу в кабинет
        header("Location: dashboard.php");
        exit;
    } else {
        // Неверный логин или пароль
        header("Location: login_form.php?error=1");
        exit;
    }
} else {
    // Если открыли напрямую, а не через POST
    header("Location: login_form.php");
    exit;
}

?>

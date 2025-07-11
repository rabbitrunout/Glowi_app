<?php
session_start();
require 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['userName'] ?? '');
    $email = trim($_POST['emailAddress'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        $error = "Пожалуйста, заполните все поля.";
    } else {
        // Проверяем, есть ли уже такой email
        $stmt = $pdo->prepare("SELECT * FROM parents WHERE emailAddress = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Этот email уже зарегистрирован.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO parents (userName, emailAddress, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed]);

            $parentID = $pdo->lastInsertId();

            $_SESSION['parentID'] = $parentID;
            $_SESSION['userName'] = $username;

            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Регистрация родителя – Glowi</title>
    <link rel="stylesheet" href="css/main.css" />
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <h2>Регистрация родителя</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>Имя пользователя:</label><br>
        <input type="text" name="userName" required value="<?= htmlspecialchars($username ?? '') ?>"><br><br>

        <label>Email:</label><br>
        <input type="email" name="emailAddress" required value="<?= htmlspecialchars($email ?? '') ?>"><br><br>

        <label>Пароль:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <p>Уже зарегистрированы? <a href="login_form.php">Войти</a></p>
</main>

<?php  include 'footer.php'; ?>
</body>
</html>

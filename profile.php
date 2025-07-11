<?php
session_start();

if (!isset($_SESSION['parentID'])) {
    header("Location: login_form.php");
    exit;
}

require 'database.php';

$parentID = $_SESSION['parentID'];

// Получаем все данные о родителе
$stmt = $pdo->prepare("SELECT * FROM parents WHERE parentID = ?");
$stmt->execute([$parentID]);
$parent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parent) {
    die("Пользователь не найден.");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Профиль родителя — Glowi</title>
    <link rel="stylesheet" href="css/main.css" />
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Профиль родителя</h1>

        <p><strong>Имя пользователя:</strong> <?= htmlspecialchars($parent['userName']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($parent['emailAddress']) ?></p>
        <!-- Добавь другие поля из таблицы parents, если есть -->
        <!-- Например, если есть телефон -->
        <?php if (isset($parent['phone'])): ?>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($parent['phone']) ?></p>
        <?php endif; ?>

        <!-- Если есть даты создания/обновления -->
        <p><strong>Дата регистрации:</strong> <?= htmlspecialchars($parent['created_at']) ?></p>
        <p><strong>Последнее обновление:</strong> <?= htmlspecialchars($parent['updated_at']) ?></p>

        <!-- Кнопка выхода -->
        <p><a href="logout.php">Выйти</a></p>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>

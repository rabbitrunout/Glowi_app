<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Некорректный ID ребенка.");
}

$childID = (int)$_GET['childID'];

// Проверяем, что ребенок принадлежит родителю
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("Ребенок не найден или доступ запрещён.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $dateAwarded = $_POST['dateAwarded'];
    $fileURL = trim($_POST['fileURL']); // например, URL на файл

    if ($title && in_array($type, ['medal', 'diploma', 'rating']) && $dateAwarded && $fileURL) {
        $stmt = $pdo->prepare("INSERT INTO achievements (childID, title, type, dateAwarded, fileURL) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$childID, $title, $type, $dateAwarded, $fileURL]);
        header("Location: child_achievements.php?childID=$childID");
        exit;
    } else {
        $error = "Пожалуйста, заполните все поля корректно.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Добавить достижение — <?= htmlspecialchars($child['name']) ?></title>
</head>
<body>
<?php 'header.php'; ?>

<h1>Добавить достижение для <?= htmlspecialchars($child['name']) ?></h1>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Название:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Тип:</label><br>
    <select name="type" required>
        <option value="">Выберите тип</option>
        <option value="medal">Медаль</option>
        <option value="diploma">Диплом</option>
        <option value="rating">Рейтинг</option>
    </select><br><br>

    <label>Дата присуждения:</label><br>
    <input type="date" name="dateAwarded" required><br><br>

    <label>Ссылка на файл (URL):</label><br>
    <input type="url" name="fileURL" required><br><br>

    <button type="submit">Добавить достижение</button>
</form>

<p><a href="child_achievements.php?childID=<?= $childID ?>">← Назад к достижениям</a></p>

<?php  'footer.php'; ?>
</body>
</html>

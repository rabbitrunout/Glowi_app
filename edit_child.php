<?php
session_start();
require 'database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Некорректный ID ребенка.");
}

$childID = (int)$_GET['childID'];

// Получаем данные ребенка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("Ребенок не найден или доступ запрещён.");
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    $groupLevel = trim($_POST['groupLevel']);
    $gender = $_POST['gender'] ?? 'unknown';

    // Обновление текста
    $stmt = $pdo->prepare("UPDATE children SET name = ?, age = ?, groupLevel = ?, gender = ? WHERE childID = ?");
    $stmt->execute([$name, $age, $groupLevel, $gender, $childID]);

    // Загрузка фото
    if (!empty($_FILES['photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['photo']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $targetDir = 'uploads/avatars/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

            $filename = uniqid('photo_', true) . '.' . $ext;
            $targetPath = $targetDir . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("UPDATE children SET photoImage = ? WHERE childID = ? AND parentID = ?");
                $stmt->execute([$targetPath, $childID, $parentID]);
            }
        }
    }

    header("Location: child_profile.php?childID=$childID");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование профиля: <?= htmlspecialchars($child['name']) ?></title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="container card" style="max-width: 500px;">
    <h1>✏️ Редактировать профиль</h1>
    <form method="POST" action="edit_child.php?childID=<?= $childID ?>" enctype="multipart/form-data">

        <label>Имя:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($child['name']) ?>" required><br><br>

        <label>Возраст:</label><br>
        <input type="number" name="age" value="<?= htmlspecialchars($child['age']) ?>" min="1" required><br><br>

        <label>Уровень группы:</label><br>
        <select name="groupLevel" required>
            <option value="Beginner" <?= $child['groupLevel'] === 'Beginner' ? 'selected' : '' ?>>Начинающий</option>
            <option value="Intermediate" <?= $child['groupLevel'] === 'Intermediate' ? 'selected' : '' ?>>Средний</option>
            <option value="Advanced" <?= $child['groupLevel'] === 'Advanced' ? 'selected' : '' ?>>Продвинутый</option>
        </select><br><br>

        <label>Пол:</label><br>
        <select name="gender">
            <option value="male" <?= $child['gender'] === 'male' ? 'selected' : '' ?>>Мальчик</option>
            <option value="female" <?= $child['gender'] === 'female' ? 'selected' : '' ?>>Девочка</option>
            <option value="unknown" <?= $child['gender'] === 'unknown' ? 'selected' : '' ?>>Не указано</option>
        </select><br><br>

        <label>Фото (аватар):</label><br>
        <?php if (!empty($child['photoImage'])): ?>
            <img src="<?= htmlspecialchars($child['photoImage']) ?>" alt="Фото ребенка" style="max-width:100px; border-radius: 50%;"><br>
        <?php endif; ?>
        <input type="file" name="photo" accept="image/*"><br><br>

        <button type="submit">💾 Сохранить изменения</button>
    </form>

    <p><a href="child_profile.php?childID=<?= $childID ?>" class="button">← Назад к профилю</a></p>
</main>

<?php include 'footer.php'; ?>
</body>
</html>

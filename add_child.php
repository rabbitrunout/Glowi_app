<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header("Location: login_form.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $groupLevel = $_POST['groupLevel'];
    $parentID = $_SESSION['parentID'];

    $stmt = $pdo->prepare("INSERT INTO children (parentID, name, age, groupLevel) VALUES (?, ?, ?, ?)");
    $stmt->execute([$parentID, $name, $age, $groupLevel]);

    $success = 'Ребёнок успешно добавлен!';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Добавить ребёнка</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php include 'header.php'; ?>

<h2>Добавить ребёнка</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php elseif ($success): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>Имя:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Возраст:</label><br>
    <input type="number" name="age" min="1" required><br><br>

    <label>Уровень группы:</label><br>
    <input type="text" name="groupLevel"><br><br>

    <button type="submit">Добавить</button>
</form>

<p><a href="dashboard.php">← Назад</a></p>
<?php include 'footer.php'; ?>
</body>
</html>

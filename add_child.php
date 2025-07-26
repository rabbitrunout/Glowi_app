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

<!-- <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add a child</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php include 'header.php'; ?>

<h2>Add a child</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php elseif ($success): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Age:</label><br>
    <input type="number" name="age" min="1" required><br><br>

    <label>Level/Group:</label><br>
    <input type="text" name="groupLevel"><br><br>

    <button type="submit">Add</button>
</form>

<?php include 'footer.php'; ?>
</body>
</html> -->

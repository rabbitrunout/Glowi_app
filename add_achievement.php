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
        $error = "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Add Achievement— <?= htmlspecialchars($child['name']) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<h1>Add an achievement for <?= htmlspecialchars($child['name']) ?></h1>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Type:</label><br>
    <select name="type" required>
        <option value="">Select the type</option>
        <option value="medal">Medal</option>
        <option value="diploma">Diploma</option>
        <option value="rating">Rating</option>
    </select><br><br>

    <label>Date of award:</label><br>
    <input type="date" name="dateAwarded" required><br><br>

    <label>Link to the file (URL):</label><br>
    <input type="url" name="fileURL"><br><br>

    <button type="submit">Add Achievement</button>
</form>

<p><a href="child_achievements.php?childID=<?= $childID ?>">← Back to achievements</a></p>

<?php include 'footer.php'; ?>
</body>
</html>

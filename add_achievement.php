<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Invalid child's ID.");
}

$childID = (int)$_GET['childID'];

// Проверяем, что ребёнок принадлежит родителю
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("The child has not been found or access is denied.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $dateAwarded = $_POST['dateAwarded'];
    $fileURL = trim($_POST['fileURL']);
    $place = isset($_POST['place']) ? (int)$_POST['place'] : null;
    $medal = $_POST['medal'] ?? 'none';

    if ($title && in_array($type, ['medal', 'diploma', 'competition']) && $dateAwarded) {
        $stmt = $pdo->prepare("
            INSERT INTO achievements (childID, title, type, dateAwarded, fileURL, place, medal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$childID, $title, $type, $dateAwarded, $fileURL, $place ?: null, $medal]);

        header("Location: child_achievements.php?childID=$childID");
        exit;
    } else {
        $error = "Please fill in all required fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title> Add Achievement — <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">

</head>
<body>
<?php include 'header.php'; ?>

<main class="container card">
  <h1>🏅 Add an achievement for <?= htmlspecialchars($child['name']) ?></h1>

  <?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" class="add-ach-form">
    <label>Achievement name:</label>
    <input type="text" name="title" required>

    <label>Type:</label>
    <select name="type" required>
        <option value="">--Chose type --</option>
        <option value="medal">Medal</option>
        <option value="diploma">Diploma</option>
        <option value="competition"> Competition</option>
    </select>

    <label>Day Awarded:</label>
    <input type="date" name="dateAwarded" required>

    <label>Awarding Place:</label>
    <input type="number" name="place" min="1" placeholder="например, 1">

    <label>Type of Medal:</label>
    <select name="medal">
        <option value="none">-----</option>
        <option value="gold">🥇 Gold</option>
        <option value="silver"> 🥈 Silver</option>
        <option value="bronze"> 🥉 Bronze</option>
        <option value="forth">🎗️ 4th  </option>
        <option value="fifth"> 🎗️ 5th  </option>
        <option value="sixth"> 🎗️ 6th</option>
        <option value="seventh"> 🎗️ 7th  </option>
        <option value="eighth"> 🎗️ 8th</option>
        <option value="honorable"> 🏵️ Certificate of honor </option>
        <option value="cup"> 🏆 Cup </option>
    </select>

    <label>Link to the file (if available):</label>
    <input type="url" name="fileURL" placeholder="https://...">

    <button type="submit">➕ Add Achievement</button>
  </form>

  <p><a href="child_achievements.php?childID=<?= $childID ?>" class="button">← Back to achievements</a></p>
</main>

<?php include 'footer.php'; ?>
</body>
</html>

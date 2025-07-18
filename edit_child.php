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

    // Загрузка изображения
    if (isset($_FILES['childImage']) && $_FILES['childImage']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['childImage']['tmp_name'];
        $original_name = basename($_FILES['childImage']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $new_name = uniqid('photoImage_', true) . '.' . $ext;

        $upload_dir = 'uploads/avatars/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $destination = $upload_dir . $new_name;

        if (move_uploaded_file($tmp_name, $destination)) {
            $image_path = '/' . $destination;

            $stmt = $pdo->prepare("UPDATE children SET photoImage = ? WHERE childID = ?");
            $stmt->execute([$image_path, $childID]);
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
    <title>Profile Editing: <?= htmlspecialchars($child['name']) ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/child_profile_neon.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card" style="max-width: 480px; margin: 3rem auto;">
    <h2>✏️ Edit a child's profile</h2>

    <form method="POST" action="edit_child.php?childID=<?= $childID ?>" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input id="name" type="text" name="name" value="<?= htmlspecialchars($child['name']) ?>" required />

        <label for="age">Age:</label>
        <input id="age" type="number" name="age" min="1" value="<?= htmlspecialchars($child['age']) ?>" required />

        <label for="groupLevel">Level:</label>
        <select id="groupLevel" name="groupLevel" required>
            <?php
            $levels = [
                "Novice", "Junior", "Senior",
                "Level 2A", "Level 2B", "Level 2C",
                "Level 3A", "Level 3B", "Level 3C",
                "Level 4A", "Level 4B", "Level 4C",
                "Level 5A", "Level 5B", "Level 5C",
                "Interclub 2A", "Interclub 2B", "Interclub 2C",
                "Interclub 3A", "Interclub 3B", "Interclub 3C"
            ];
            foreach ($levels as $level) {
                $selected = ($child['groupLevel'] === $level) ? 'selected' : '';
                echo "<option value=\"$level\" $selected>$level</option>";
            }
            ?>
        </select>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender">
            <option value="male" <?= $child['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= $child['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="unknown" <?= $child['gender'] === 'unknown' ? 'selected' : '' ?>>Unknown</option>
        </select>

        <label for="childImage">Child Image:</label>
        <input id="childImage" type="file" name="childImage" accept="image/*" onchange="previewImage(event)" />

        <img src="<?= htmlspecialchars($imagePath) ?>" alt="Фото ребенка" class="avatar-preview" id="imagePreview" />

        <button type="submit" class="btn-save"> Save</button>
    </form>

    <p><a href="child_profile.php?childID=<?= $childID ?>" class="button">← Back to profile</a></p>
</main>

<?php include 'footer.php'; ?>

<script>
function previewImage(event) {
  const input = event.target;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const output = document.getElementById('imagePreview');
      output.src = e.target.result;
    }
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

</body>
</html>

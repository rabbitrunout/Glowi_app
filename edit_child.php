<?php
session_start();
require 'database.php';
require 'image_functions.php'; // <-- подключаем функции ресайза

ini_set('display_errors', 1);
error_reporting(E_ALL);

$uploadDir = "uploads/avatars/";
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $childID = $_POST['childID'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $groupLevel = $_POST['groupLevel'];

    // Получаем старое изображение
    $stmt = $pdo->prepare("SELECT photoImage FROM children WHERE childID = ?");
    $stmt->execute([$childID]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    $old_image = $old['photoImage'] ?? 'placeholder_100.png';

    $imageToSave = $old_image;

    if (isset($_FILES['childImage']) && $_FILES['childImage']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['childImage']['tmp_name'];
        $original_name = basename($_FILES['childImage']['name']);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (in_array($extension, $allowedExtensions)) {
            $new_image = uniqid('child_', true) . '.' . $extension;
            $destination = $uploadDir . $new_image;

            if (move_uploaded_file($tmp_name, $destination)) {
                // Удаляем старые изображения, если есть
                if (!empty($old_image) && $old_image !== 'placeholder_100.png') {
                    $baseOld = pathinfo($old_image, PATHINFO_FILENAME);
                    foreach (glob($uploadDir . $baseOld . '*') as $oldFile) {
                        if (is_file($oldFile)) unlink($oldFile);
                    }
                }

                // Создаем версии с ресайзом
                process_image($uploadDir, $new_image);

                $imageToSave = $new_image;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE children SET name = ?, age = ?, gender = ?, groupLevel = ?, photoImage = ? WHERE childID = ?");
    $stmt->execute([$name, $age, $gender, $groupLevel, $imageToSave, $childID]);

    header("Location: child_profile.php?childID=" . $childID);
    exit;
}

// Для GET-запроса или первого открытия формы:
$childID = $_GET['childID'] ?? $_POST['childID'] ?? null;
if (!$childID) die("Ошибка: childID не передан.");

$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ?");
$stmt->execute([$childID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) die("Ребенок не найден.");

$imagePath = $child['photoImage'] ?? 'placeholder_100.png';
if (!str_contains($imagePath, '/')) {
    $imagePath = $uploadDir . $imagePath;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование профиля: <?= htmlspecialchars($child['name']) ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/child_profile_neon.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card" style="max-width: 480px; margin: 3rem auto;">
    <h2>✏️ Edit a child's profile</h2>

    <form method="POST" action="edit_child.php" enctype="multipart/form-data">
        <input type="hidden" name="childID" value="<?= $childID ?>">

        <label for="name">Name:</label>
        <input id="name" type="text" name="name" value="<?= htmlspecialchars($child['name']) ?>" required>

        <label for="age">Age:</label>
        <input id="age" type="number" name="age" min="1" value="<?= htmlspecialchars($child['age']) ?>" required>

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
        <input id="childImage" type="file" name="childImage" accept="image/*" onchange="previewImage(event)">

        <img src="<?= htmlspecialchars($imagePath) ?>" alt="Фото ребенка" class="avatar-preview" id="imagePreview">

        <button type="submit" class="btn-save">Save</button>
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

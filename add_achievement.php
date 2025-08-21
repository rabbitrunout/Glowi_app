<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Проверка принадлежности ребенка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    die("The child has not been found or access is denied.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Получаем значения из формы и делаем базовую очистку
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $dateAwarded = $_POST['dateAwarded'] ?? '';
    $place = isset($_POST['place']) && is_numeric($_POST['place']) ? (int)$_POST['place'] : null;
    $medal = $_POST['medal'] ?? 'none';

    // Проверяем обязательные поля
    if (!$title || !$type || !$dateAwarded || !in_array($type, ['medal','diploma','competition'])) {
        $error = "Please fill in all required fields correctly.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO achievements (childID, title, type, dateAwarded, place, medal)
                VALUES (:childID, :title, :type, :dateAwarded, :place, :medal)
            ");

            $stmt->execute([
                ':childID' => $childID,
                ':title' => $title,
                ':type' => $type,
                ':dateAwarded' => $dateAwarded,
                ':place' => $place,
                ':medal' => $medal
            ]);

            // После успешного добавления — редирект
            header("Location: child_achievements.php?childID=$childID");
            exit;

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Add Achievement — <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card">
  <h2><i data-lucide="plus-circle"></i> Add an achievement — <?= htmlspecialchars($child['name']) ?></h2>

  <?php if ($error): ?>
    <div class="glowi-message error">
      <i data-lucide="alert-triangle"></i>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <label>Achievement name:</label>
    <input type="text" name="title" placeholder="Название достижения" required>

    <label>Type:</label>
    <select name="type" required>
      <option value="">-- Select type --</option>
      <option value="medal">Medal</option>
      <option value="diploma">Diploma</option>
      <option value="competition">Competition</option>
    </select>

    <label>Date Awarded:</label>
    <input type="date" name="dateAwarded" required>

    <label>Awarding Place:</label>
    <input type="number" name="place" min="1" placeholder="for example, 1">

    <label>Type of Medal:</label>
    <select name="medal">
      <option value="none">-----</option>
      <option value="gold">🥇 Gold</option>
      <option value="silver">🥈 Silver</option>
      <option value="bronze">🥉 Bronze</option>
      <option value="forth">🎗️ 4th</option>
      <option value="fifth">🎗️ 5th</option>
      <option value="sixth">🎗️ 6th</option>
      <option value="seventh">🎗️ 7th</option>
      <option value="eighth">🎗️ 8th</option>
      <option value="honorable">🏵️ Certificate of honor</option>
      <option value="cup">🏆 Cup</option>
    </select>

    <button type="submit" class="btn-save"><i data-lucide="save"></i> Save</button>
  </form>

  <p style="margin-top:.8rem;">
    <a href="child_achievements.php?childID=<?= $childID ?>">← Back to achievements</a>
  </p>
  <p>
    <a href="child_profile.php?childID=<?= $childID ?>">← Back to profile</a>
  </p>
</main>

<?php include 'footer.php'; ?>
<script>lucide.createIcons();</script>
</body>
</html>

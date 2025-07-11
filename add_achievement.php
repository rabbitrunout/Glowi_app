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

// Проверяем, что ребёнок принадлежит родителю
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
        $error = "Пожалуйста, заполните все обязательные поля корректно.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Добавить достижение — <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">
  <style>
    form.add-ach-form input,
    form.add-ach-form select,
    form.add-ach-form textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 12px;
        box-sizing: border-box;
    }
    form.add-ach-form {
        max-width: 500px;
        margin: 0 auto;
    }
  </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container card">
  <h1>🏅 Добавить достижение для <?= htmlspecialchars($child['name']) ?></h1>

  <?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" class="add-ach-form">
    <label>Название достижения:</label>
    <input type="text" name="title" required>

    <label>Тип:</label>
    <select name="type" required>
        <option value="">-- Выберите тип --</option>
        <option value="medal">Медаль</option>
        <option value="diploma">Диплом</option>
        <option value="competition">Соревнование</option>
    </select>

    <label>Day Awarded:</label>
    <input type="date" name="dateAwarded" required>

    <label>Awarding Place (if any):</label>
    <input type="number" name="place" min="1" placeholder="например, 1">

    <label>Type of Medal:</label>
    <select name="medal">
        <option value="none">Без медали</option>
        <option value="gold">🥇 Gold</option>
        <option value="silver"> 🥈 Silver</option>
        <option value="bronze"> 🥉 Bronze</option>
    </select>

    <label>Ссылка на файл (если есть):</label>
    <input type="url" name="fileURL" placeholder="https://...">

    <button type="submit">➕ Добавить достижение</button>
  </form>

  <p><a href="child_achievements.php?childID=<?= $childID ?>" class="button">← Назад к достижениям</a></p>
</main>

<?php include 'footer.php'; ?>
</body>
</html>

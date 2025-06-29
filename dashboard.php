<?php
session_start();

if (!isset($_SESSION['parentID'])) {
    header("Location: login_form.php");
    exit;
}

require 'database.php';

$parentID = $_SESSION['parentID'];

$stmt = $pdo->prepare("SELECT * FROM parents WHERE parentID = ?");
$stmt->execute([$parentID]);
$parent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parent) {
    die("Пользователь не найден.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['childName'] ?? '';
    $age = intval($_POST['childAge'] ?? 0);
    $groupLevel = $_POST['groupLevel'] ?? '';

    if ($name && $age > 0 && $groupLevel) {
        $stmt = $pdo->prepare("INSERT INTO children (parentID, name, age, groupLevel) VALUES (?, ?, ?, ?)");
        $stmt->execute([$parentID, $name, $age, $groupLevel]);
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Пожалуйста, заполните все поля при добавлении ребенка.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM children WHERE parentID = ?");
$stmt->execute([$parentID]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Personal account — Glowi</title>
  <link rel="stylesheet" href="css/main.css">
  <script src="https://unpkg.com/lucide@latest"></script>

</head>
<body>
  <?php include 'header.php'; ?>

  <main class="dashboard-wrapper">
  <div class="dashboard-block">
    <h2><i data-lucide="user" class="icon"></i> Parent's profile</h2>
    <p><strong><i data-lucide="user" class="icon"></i> Username:</strong> <?= htmlspecialchars($parent['userName']) ?></p>
    <p><strong><i data-lucide="mail" class="icon"></i> Email:</strong> <?= htmlspecialchars($parent['emailAddress']) ?></p>
    <?php if (!empty($parent['phone'])): ?>
      <p><strong><i data-lucide="phone" class="icon"></i> Phone:</strong> <?= htmlspecialchars($parent['phone']) ?></p>
    <?php endif; ?>
    <p><strong><i data-lucide="calendar" class="icon"></i> Registration date:</strong> <?= htmlspecialchars($parent['created_at']) ?></p>
    <!-- <p><strong><i data-lucide="clock" class="icon"></i> Последнее обновление:</strong> <?= htmlspecialchars($parent['updated_at']) ?></p> -->
  </div>

  <div class="dashboard-block">
    <h2><i data-lucide="baby" class="icon"></i> Your children</h2>
    <?php if (count($children) === 0): ?>
      <p>There are no added children yet.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($children as $child): ?>
          <li>
            <i data-lucide="arrow-right" class="icon"></i>
            <a href="child_profile.php?childID=<?= $child['childID'] ?>">
              <?= htmlspecialchars($child['name']) ?>, <?= (int)$child['age'] ?> y.o. — <?= htmlspecialchars($child['groupLevel']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="dashboard-block">
    <h2><i data-lucide="plus-circle" class="icon"></i> Add a child</h2>
    <?php if (!empty($error)): ?>
      <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="dashboard.php">
      <label>Child's name:</label><br>
      <input type="text" name="childName" required><br><br>

      <label>Age:</label><br>
      <input type="number" name="childAge" min="1" max="18" required><br><br>

      <label>Level:</label><br>
      <input type="text" name="groupLevel" required><br><br>

      <button type="submit"><i data-lucide="check-circle" class="icon"></i> Add</button>
    </form>
  </div>

  <div class="actions">
    <a href="logout.php"><i data-lucide="log-out" class="icon"></i> Logout </a>
  </div>
</main>

  <?php include 'footer.php'; ?>

  <script>
  lucide.createIcons();
  </script>

</body>
</html>

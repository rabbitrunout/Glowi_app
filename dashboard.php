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
    // Обработка удаления ребёнка
    if (isset($_POST['delete_child_id'])) {
        $deleteID = intval($_POST['delete_child_id']);
        $delStmt = $pdo->prepare("DELETE FROM children WHERE childID = ? AND parentID = ?");
        $delStmt->execute([$deleteID, $parentID]);
        header("Location: dashboard.php");
        exit;
    }

    // Обработка добавления ребёнка
    $name = $_POST['childName'] ?? '';
    $age = intval($_POST['childAge'] ?? 0);
    $groupLevel = $_POST['groupLevel'] ?? '';

   if ($name && $age > 0 && $groupLevel) {
    // Обработка изображения
  $filename = 'placeholder.png'; // значение по умолчанию
if (!empty($_FILES['photoImage']['name'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['photoImage']['type'], $allowedTypes)) {
        $ext = pathinfo($_FILES['photoImage']['name'], PATHINFO_EXTENSION);
        $targetDir = 'uploads/avatars/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $filename = uniqid('child_', true) . '.' . $ext;
        $targetPath = $targetDir . $filename;
        if (!move_uploaded_file($_FILES['photoImage']['tmp_name'], $targetPath)) {
            // загрузка не удалась, вернуть к placeholder
            $filename = 'placeholder.png';
        }
    }
}

    $stmt = $pdo->prepare("INSERT INTO children (parentID, name, age, groupLevel, photoImage) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$parentID, $name, $age, $groupLevel, $filename]);

    header("Location: dashboard.php");
    exit;
}
 else {
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
  <meta charset="UTF-8" />
  <title> Parent's profile — Glowi</title>
  <link rel="stylesheet" href="css/main.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<?php include 'header.php'; ?>

<main class="dashboard-wrapper container">

  <!-- Parent's profile -->

  <section class="dashboard-block card">
    <h2><i data-lucide="user" class="icon"></i> Parent's profile</h2>
    <p><strong><i data-lucide="user" class="icon"></i> Username:</strong> <?= htmlspecialchars($parent['userName']) ?></p>
    <p><strong><i data-lucide="mail" class="icon"></i> Email:</strong> <?= htmlspecialchars($parent['emailAddress']) ?></p>
    <?php if (!empty($parent['phone'])): ?>
      <p><strong><i data-lucide="phone" class="icon"></i> Phone:</strong> <?= htmlspecialchars($parent['phone']) ?></p>
    <?php endif; ?>
    <p><strong><i data-lucide="calendar" class="icon"></i> Registration date: </strong> <?= htmlspecialchars($parent['created_at']) ?></p>
  </section>

  <!-- List of children -->
  <section class="dashboard-block card">
    <h2><i data-lucide="baby" class="icon"></i> Your children </h2>
    <?php if (count($children) === 0): ?>
      <p>Children have not been added yet.</p>
    <?php else: ?>
      <div class="children-list">
        <?php foreach ($children as $child): ?>
        <div class="child-card card">
         <?php
          $photoFile = $child['photoImage']; // ← вот этого не хватало
          $photoSrc = (!empty($photoFile) && file_exists('uploads/avatars/' . $photoFile))
                    ? 'uploads/avatars/' . htmlspecialchars($photoFile)
                    : 'assets/img/placeholder.png';
         ?>
         <img src="<?= htmlspecialchars($photoSrc) ?>" alt="Photo of the child" />

  <h3><?= htmlspecialchars($child['name']) ?></h3>
    <p>Age: <?= (int)$child['age'] ?> лет</p>
    <p>Level: <?= htmlspecialchars($child['groupLevel']) ?></p>

   <div class="child-actions">
  <a class="button" href="child_profile.php?childID=<?= $child['childID'] ?>">
    <img src="assets/img/porfoliolist.png" alt="Open profile">
  </a>

  <form method="post" onsubmit="return confirm('Delete this child?');">
    <input type="hidden" name="delete_child_id" value="<?= $child['childID'] ?>" />
    <button type="submit" class="button" style="background-color: #ff3366;">
      <img src="assets/img/delete.png" alt="Delete">
    </button>
  </form>
</div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Child's addition form -->
  <section class="dashboard-block card neon-form">
    <h2><i data-lucide="plus-circle" class="icon"></i> Add a child</h2>
    <?php if (!empty($error)): ?>
      <p style="color:#ff66cc; font-weight:bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
   <form method="POST" action="dashboard.php" enctype="multipart/form-data" novalidate>
      <label for="childName">Child's name:</label>
      <input type="text" id="childName" name="childName" required placeholder="Enter a name" />

      <label for="childAge">Age:</label>
      <input type="number" id="childAge" name="childAge" min="1" max="18" required placeholder="From 1 to 18" />

      <label for="groupLevel">Level:</label>
      <input type="text" id="groupLevel" name="groupLevel" required placeholder="For example, 'Beginner'" />

       <!-- <label> Photo:</label><br>
       <input type="file" name="photoImage" accept="image/*"><br><br> -->
<br/>
      <button type="submit" class="neon-button">
        <i data-lucide="check-circle"></i> Add
      </button>
    </form>
  </section>

  <!-- Действия -->
  <div class="actions">
    <a href="logout.php" class="button">
      <i data-lucide="log-out"></i> Logout
    </a>
  </div>
  <br/>
</main>

<?php include 'footer.php'; ?>

<script>
  lucide.createIcons();
</script>
</body>
</html>

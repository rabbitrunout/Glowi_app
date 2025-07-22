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
  <meta charset="UTF-8" />
  <title>Личный кабинет — Glowi</title>
   <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/child_profile_neon.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="dashboard-wrapper container">
  <!-- Профиль родителя -->
  <section class="dashboard-block card">
    <h2><i data-lucide="user" class="icon"></i> Профиль родителя</h2>
    <p><strong><i data-lucide="user" class="icon"></i> Имя пользователя:</strong> <?= htmlspecialchars($parent['userName']) ?></p>
    <p><strong><i data-lucide="mail" class="icon"></i> Email:</strong> <?= htmlspecialchars($parent['emailAddress']) ?></p>
    <?php if (!empty($parent['phone'])): ?>
      <p><strong><i data-lucide="phone" class="icon"></i> Телефон:</strong> <?= htmlspecialchars($parent['phone']) ?></p>
    <?php endif; ?>
    <p><strong><i data-lucide="calendar" class="icon"></i> Дата регистрации:</strong> <?= htmlspecialchars($parent['created_at']) ?></p>
  </section>

  <!-- Список детей -->
  <section class="dashboard-block card">
    <h2><i data-lucide="baby" class="icon"></i> Ваши дети</h2>
    <?php if (count($children) === 0): ?>
      <p>Дети ещё не добавлены.</p>
    <?php else: ?>
      <div class="children-list">
        <?php foreach ($children as $child): ?>
          <div class="child-card card">
            <img src="uploads/avatars/<?= htmlspecialchars($child['photoImage'] ?: 'placeholder_100.png') ?>" alt="Avatar" class="child-avatar"  width="65" high="65"/>
            <h3><?= htmlspecialchars($child['name']) ?></h3>
            <p>Возраст: <?= (int)$child['age'] ?> лет</p>
            <p>Уровень: <?= htmlspecialchars($child['groupLevel']) ?></p>
            <div class="child-actions">
              <a class="button" href="child_profile.php?childID=<?= $child['childID'] ?>">
                <i data-lucide="info"></i> Подробнее
              </a>
              <form method="post" onsubmit="return confirm('Удалить этого ребёнка?');" style="display:inline-block; margin-top:8px;">
  <input type="hidden" name="delete_child_id" value="<?= $child['childID'] ?>" />
  <button type="submit" class="button" style="background:#ff3366;">
    <i data-lucide="trash-2"></i> Удалить
  </button>
</form>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Форма добавления ребенка -->
  <section class="dashboard-block card neon-form">
    <h2><i data-lucide="plus-circle" class="icon"></i> Добавить ребёнка</h2>
    <?php if (!empty($error)): ?>
      <p style="color:#ff66cc; font-weight:bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="dashboard.php" novalidate>
      <label for="childName">Имя ребенка:</label>
      <input type="text" id="childName" name="childName" required placeholder="Введите имя" />

      <label for="childAge">Возраст:</label>
      <input type="number" id="childAge" name="childAge" min="1" max="18" required placeholder="От 1 до 18" />

      <label for="groupLevel">Уровень группы:</label>
      <input type="text" id="groupLevel" name="groupLevel" required placeholder="Например, 'Начинающий'" />

      <button type="submit" class="neon-button">
        <i data-lucide="check-circle"></i> Добавить
      </button>
    </form>
  </section>

  <!-- Действия -->
  <div class="actions">
    <a href="logout.php" class="button">
      <i data-lucide="log-out"></i> Выйти
    </a>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>
  lucide.createIcons();
</script>
</body>
</html>

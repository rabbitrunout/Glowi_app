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

// Удаление достижения
if (isset($_GET['deleteID']) && is_numeric($_GET['deleteID'])) {
    $deleteID = (int)$_GET['deleteID'];
    $stmt = $pdo->prepare("DELETE FROM achievements WHERE achievementID = ? AND childID = ?");
    $stmt->execute([$deleteID, $childID]);
    header("Location: child_achievements.php?childID=$childID");
    exit;
}

// Получаем достижения ребенка
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE childID = ? ORDER BY dateAwarded DESC");
$stmt->execute([$childID]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Progress <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/achievements.css" />
  <script src="https://unpkg.com/lucide@latest"></script>

</head>
<body>

<h1>Child's achievements: <?= htmlspecialchars($child['name']) ?></h1>



<section class="achievements-section card">
  <?php if (empty($achievements)): ?>
      <p>There are no added achievements yet.</p>
  <?php else: ?>
      <ul>
         <?php foreach ($achievements as $ach): ?>
    <li class="achievement" data-id="<?= $ach['achievementID'] ?>">
      <strong><?= htmlspecialchars($ach['title']) ?></strong>
      date: <?= htmlspecialchars($ach['dateAwarded']) ?><br>

      <?php if (!empty($ach['place'])): ?>
        <span><strong> Place: <?= (int)$ach['place'] ?></strong> </span><br>
      <?php endif; ?>

      <?php if (!empty($ach['medal']) && $ach['medal'] !== 'none'): ?>
        <span><strong> Type:</strong>
          <?php
            switch ($ach['medal']) {
              case 'gold': echo 'Золотая 🥇'; break;
              case 'silver': echo 'Серебряная 🥈'; break;
              case 'bronze': echo 'Бронзовая 🥉'; break;
              case 'fourth': echo '4 место (лента) 🎗️'; break;
              case 'fifth': echo '5 место (лента) 🎗️'; break;
              case 'sixth': echo '6 место (лента) 🎗️'; break;
              case 'seventh': echo '7 место (лента) 🎗️'; break;
              case 'honorable': echo 'Почётная грамота 🏵️'; break;
              default: echo ucfirst($ach['medal']);
            }
          ?>
        </span><br>
      <?php endif; ?>

      <?php if (!empty($ach['fileURL'])): ?>
        <a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank" class="button">📎 View file</a><br>
      <?php endif; ?>

      <button onclick="editAchievement(<?= htmlspecialchars(json_encode($ach)) ?>)">✏️ Edit</button>
      <a href="?childID=<?= $childID ?>&deleteID=<?= $ach['achievementID'] ?>" onclick="return confirm('Удалить это достижение?')">❌ Delete</a>
    </li>
  <?php endforeach; ?>
      </ul>
  <?php endif; ?>
</section>

<div class="button-row">
  <a href="add_achievement.php?childID=<?= $childID ?>" class="button">
    <i data-lucide="plus-circle"></i> Добавить достижение
  </a>
  <a href="child_profile.php?childID=<?= $childID ?>" class="button">
    ← Back to the child's profile
  </a>
</div>


<!-- Редактирование -->
<div class="modal-overlay" id="overlay"></div>
<div class="modal" id="editModal">
  <h3>Редактировать достижение</h3>
  <form method="POST" action="update_achievement.php">
    <input type="hidden" name="achievementID" id="editID">
    <input type="hidden" name="childID" value="<?= $childID ?>">

    <label>Название:</label>
    <input type="text" name="title" id="editTitle" required>

    <label>Тип:</label>
    <select name="type" id="editType">
      <option value="medal">Medal</option>
      <option value="diploma">Diploma</option>
      <option value="competition">Competition</option>

      
    </select>

    <label>Дата:</label>
    <input type="date" name="dateAwarded" id="editDate" required>

    <label>Место:</label>
    <input type="number" name="place" id="editPlace" min="1" placeholder="optional">

    <label>Медаль:</label>
    <select name="medal" id="editMedal">
      <option value="none">Без медали</option>
        <option value="gold">🥇 Gold</option>
        <option value="silver"> 🥈 Silver</option>
        <option value="bronze"> 🥉 Bronze</option>
    </select>

    <button type="submit">💾 Сохранить</button>
    <button type="button" onclick="closeModal()">✖️ Отмена</button>
  </form>
</div>

<!-- <?php include 'footer.php'; ?> -->
<script>
function editAchievement(data) {
  document.getElementById('overlay').style.display = 'block';
  document.getElementById('editModal').style.display = 'block';

  document.getElementById('editID').value = data.achievementID;
  document.getElementById('editTitle').value = data.title;
  document.getElementById('editType').value = data.type;
  document.getElementById('editDate').value = data.dateAwarded;
  document.getElementById('editPlace').value = data.place || '';
  document.getElementById('editMedal').value = data.medal;
}

function closeModal() {
  document.getElementById('overlay').style.display = 'none';
  document.getElementById('editModal').style.display = 'none';
}
</script>
</body>
</html>

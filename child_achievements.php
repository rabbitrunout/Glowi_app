<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Incorrect child ID.");
}

$childID = (int)$_GET['childID'];

// Проверяем, что ребенок принадлежит родителю
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("The child has not been found or access is prohibited.");
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
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Achievements — <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card">
  <h2><i data-lucide="star"></i> Achievements — <?= htmlspecialchars($child['name']) ?></h2>

  <?php if (empty($achievements)): ?>
    <p style="color:#ffccff;">There are no added achievements yet.</p>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="glowi-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Place</th>
            <th>Medal</th>
            <th>File</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($achievements as $ach): ?>
          <tr>
            <td><?= htmlspecialchars($ach['title']) ?></td>
            <td><?= htmlspecialchars($ach['dateAwarded']) ?></td>
            <td><?= $ach['place'] ? (int)$ach['place'] : '-' ?></td>
            <td>
              <?php
                switch ($ach['medal']) {
                  case 'gold': echo '🥇 Gold'; break;
                  case 'silver': echo '🥈 Silver'; break;
                  case 'bronze': echo '🥉 Bronze'; break;
                  case 'fourth': echo '🎗️ 4th'; break;
                  case 'fifth': echo '🎗️ 5th'; break;
                  case 'sixth': echo '🎗️ 6th'; break;
                  case 'seventh': echo '🎗️ 7th'; break;
                  case 'honorable': echo '🏵️ Honor'; break;
                  default: echo '—';
                }
              ?>
            </td>
            <td>
              <?php if (!empty($ach['fileURL'])): ?>
                <a href="<?= htmlspecialchars($ach['fileURL']) ?>" target="_blank">📎 File</a>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td>
              <button type="button" class="btn-save" onclick='editAchievement(<?= json_encode($ach) ?>)'>✏️ Edit</button>
              <a href="?childID=<?= $childID ?>&deleteID=<?= $ach['achievementID'] ?>" class="btn-save" onclick="return confirm('Delete this achievement?')">❌ Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <p style="margin-top:1rem;">
    <a href="add_achievement.php?childID=<?= $childID ?>" class="btn-save">
      <i data-lucide="plus-circle"></i> Add Achievement
    </a>
  </p>

  <p><a href="child_profile.php?childID=<?= $childID ?>">← Back to profile</a></p>
</main>

<!-- Модалка -->
<div class="modal-overlay" id="overlay"></div>
<div class="modal glowi-card" id="editModal">
  <h3>Edit achievement</h3>
  <form method="POST" action="update_achievement.php">
    <input type="hidden" name="achievementID" id="editID">
    <input type="hidden" name="childID" value="<?= $childID ?>">

    <label>Title:</label>
    <input type="text" name="title" id="editTitle" required>

    <label>Type:</label>
    <select name="type" id="editType">
      <option value="medal">Medal</option>
      <option value="diploma">Diploma</option>
      <option value="competition">Competition</option>
    </select>

    <label>Date:</label>
    <input type="date" name="dateAwarded" id="editDate" required>

    <label>Place:</label>
    <input type="number" name="place" id="editPlace" min="1" placeholder="optional">

    <label>Medal:</label>
    <select name="medal" id="editMedal">
      <option value="none">None</option>
      <option value="gold">🥇 Gold</option>
      <option value="silver">🥈 Silver</option>
      <option value="bronze">🥉 Bronze</option>
      <option value="fourth">🎗️ 4th</option>
      <option value="fifth">🎗️ 5th</option>
      <option value="sixth">🎗️ 6th</option>
      <option value="seventh">🎗️ 7th</option>
      <option value="honorable">🏵️ Honor</option>
    </select>

    <button type="submit" class="btn-save">💾 Save</button>
    <button type="button" class="btn-save" onclick="closeAchievementModal()">✖️ Cancel</button>

  </form>
</div>

<?php include 'footer.php'; ?>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="scripts/child_achievements.js"></script>
</body>
</html>

<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) ? (int)$_GET['childID'] : 0;
if ($childID <= 0) die("Неверный ID ребенка.");

// Проверка доступа к ребенку
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) die("Ребенок не найден или не принадлежит вам.");

// Удаление привязки события
if (isset($_GET['unlinkEventID'])) {
    $unlinkEventID = (int)$_GET['unlinkEventID'];
    $delStmt = $pdo->prepare("DELETE FROM child_event WHERE eventID = ? AND childID = ?");
    $delStmt->execute([$unlinkEventID, $childID]);
    header("Location: event_list_child.php?childID=$childID");
    exit;
}

// Фильтрация
$filter = $_GET['filter'] ?? 'all';
$allowedTypes = ['training', 'competition'];

if (in_array($filter, $allowedTypes)) {
    $stmt = $pdo->prepare("
        SELECT e.* FROM events e
        JOIN child_event ce ON ce.eventID = e.eventID
        WHERE ce.childID = ? AND e.eventType = ?
        ORDER BY e.date DESC, e.time DESC
    ");
    $stmt->execute([$childID, $filter]);
} else {
    $stmt = $pdo->prepare("
        SELECT e.* FROM events e
        JOIN child_event ce ON ce.eventID = e.eventID
        WHERE ce.childID = ?
        ORDER BY e.date DESC, e.time DESC
    ");
    $stmt->execute([$childID]);
}
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Все доступные события для привязки
$stmt = $pdo->query("SELECT eventID, title, date FROM events ORDER BY date DESC");
$allEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>События — <?= htmlspecialchars($child['name']) ?></title>
<link rel="stylesheet" href="css/main.css">
<style>
/* ===== Таблица Glowi ===== */
main.container.glowi-card {
  max-width: 700px; /* вместо 400px */
  padding: 2rem;
}

.table-wrapper {
  overflow-x: auto;
  margin-top: 1rem;
}
.glowi-table {
  width: 100%;
  border-collapse: collapse;
  min-width:600px;
  margin-top: 1rem;
  color: #fff;
  font-size: 0.9rem;
}

.glowi-table th, .glowi-table td {
  padding: 0.6rem 0.9rem;
  border: 1px solid rgba(255,255,255,0.2);
  text-align: center;
}

.glowi-table th {
  background: rgba(255, 0, 255, 0.2);
  color: #ffd700;
}

.glowi-table tr:nth-child(even) {
  background: rgba(255,255,255,0.05);
}

.glowi-table tr:hover {
  background: rgba(255,0,255,0.1);
  box-shadow: 0 0 8px #ff66ff;
  transition: background 0.3s, box-shadow 0.3s;
}

/* ===== Модальное окно Glowi ===== */
.modal.glowi-card {
  display: none;
  position: fixed;
  top: 10%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  padding: 1.2rem;
  border-radius: 16px;
  box-shadow: 0 0 25px #ff00cc;
  backdrop-filter: blur(10px);
  background: rgba(0,0,0,0.8);
  color: #fff;
  max-width: 500px;
}

/* Кнопки */
.edit-btn {
  cursor: pointer;
  background: #ff00cc;
  border: none;
  padding: 6px 12px;
  border-radius: 10px;
  color: #fff;
  font-weight: 600;
  box-shadow: 0 0 10px #ff00cc;
  transition: 0.3s;
}

.edit-btn:hover {
  box-shadow: 0 0 18px #ff66ff;
  transform: scale(1.05);
}

.close {
  float: right;
  cursor: pointer;
  font-weight: bold;
  font-size: 1.2rem;
}

select, input, textarea {
  width: 100%;
  padding: 0.5rem 0.9rem;
  margin-bottom: 0.8rem;
  border-radius: 10px;
  border: 1px solid #ff66ff;
  background: rgba(0,0,0,0.28);
  color: #fff;
  font-size: 0.9rem;
  outline: none;
}

select:focus, input:focus, textarea:focus {
  border-color: #ffd700;
  box-shadow: 0 0 5px #ffd700;
}

button.btn-save {
  width: 100%;
  padding: 0.6rem;
  font-weight: 700;
  font-size: 1rem;
  border: none;
  border-radius: 25px;
  background: #ff00cc;
  color: #212222;
  cursor: pointer;
  box-shadow: 0 0 15px #ff00cc;
  transition: background 0.3s ease, transform 0.2s ease;
}

button.btn-save:hover {
  background: #cc00cc;
  box-shadow: 0 0 25px #ff66ff;
  transform: scale(1.04);
}
</style>
</head>
<body>
<?php include 'header.php'; ?>
<main class="container glowi-card">
  <h1> Events — <?= htmlspecialchars($child['name']) ?></h1>

  <form method="get">
    <input type="hidden" name="childID" value="<?= $childID ?>">
    <label>Filter:</label>
    <select name="filter" onchange="this.form.submit()">
      <!-- <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
      <option value="training" <?= $filter === 'training' ? 'selected' : '' ?>>Training</option> -->
      <option value="competition" <?= $filter === 'competition' ? 'selected' : '' ?>>Competition</option>
    </select>
  </form>

  <?php if (empty($events)): ?>
    <p class="glowi-message error">No events</p>
  <?php else: ?>
    <div class="table-wrapper">
    <table class="glowi-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Title</th>
          <th>Type</th>
          <th>Location</th>
          <!-- <th>Действие</th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $event): ?>
          <tr>
            <td><?= $event['date'] ?></td>
            <td><?= $event['time'] ?></td>
            <td><?= htmlspecialchars($event['title']) ?></td>
            <td><?= $event['eventType'] === 'training' ? 'Training' : 'Competition' ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <!-- <td>
              <a href="?childID=<?= $childID ?>&unlinkEventID=<?= $event['eventID'] ?>" onclick="return confirm('Удалить событие из списка ребёнка?')">❌ Delete</a><br>
              <button class="edit-btn"
                data-event='<?= json_encode($event, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>'>✏️ Edit</button>
            </td> -->
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>

  <p><a href="event_add_child.php?childID=<?= $childID ?>" class="button">
        <i data-lucide="plus-circle"></i> Add an event </a></p>

  <p><a href="child_profile.php?childID=<?= $childID ?>">← Back to profile</a></p>
</main>

<!-- Модальное окно -->
<div id="editModal" class="modal glowi-card">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('editModal').style.display='none'">×</span>
    <h3>Edit Event</h3>
    <form method="post" action="update_event.php">
      <input type="hidden" name="eventID" id="editEventID">
      <input type="hidden" name="childID" value="<?= $childID ?>">
      <label>Name:</label><input type="text" name="title" id="editTitle" required>
      <label>Type:</label>
      <select name="eventType" id="editType" required>
        <option value="training">Training</option>
        <option value="competition">Competition</option>
      </select>
      <label>Description:</label><textarea name="description" id="editDescription"></textarea>
      <label>Date:</label><input type="date" name="date" id="editDate" required>
      <label>Time:</label><input type="time" name="time" id="editTime" required>
      <label>Location:</label><input type="text" name="location" id="editLocation" required>
      <button type="submit" class="btn-save">💾 SAVE</button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
const editBtns = document.querySelectorAll('.edit-btn');
const modal = document.getElementById('editModal');

editBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const e = JSON.parse(btn.dataset.event);
    document.getElementById('editEventID').value = e.eventID;
    document.getElementById('editTitle').value = e.title;
    document.getElementById('editType').value = e.eventType;
    document.getElementById('editDescription').value = e.description;
    document.getElementById('editDate').value = e.date;
    document.getElementById('editTime').value = e.time;
    document.getElementById('editLocation').value = e.location;
    modal.style.display = 'block';
  });
});

window.onclick = function(event) {
  if (event.target == modal) modal.style.display = 'none';
};
</script>
<script src="scripts/app.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>

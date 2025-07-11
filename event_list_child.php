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
    .modal { display: none; position: fixed; top: 10%; left: 50%; transform: translateX(-50%); background: #fff; padding: 20px; z-index: 999; border-radius: 8px; box-shadow: 0 0 20px #999; }
    .modal-content { width: 100%; max-width: 500px; }
    .modal .close { float: right; cursor: pointer; font-weight: bold; }
    .edit-btn { cursor: pointer; background: #e6e6ff; border: none; padding: 5px 10px; border-radius: 6px; }
  </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container">
  <h1>📅 События — <?= htmlspecialchars($child['name']) ?></h1>

  <form method="get">
    <input type="hidden" name="childID" value="<?= $childID ?>">
    <label>Фильтр:</label>
    <select name="filter" onchange="this.form.submit()">
      <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Все</option>
      <option value="training" <?= $filter === 'training' ? 'selected' : '' ?>>Тренировки</option>
      <option value="competition" <?= $filter === 'competition' ? 'selected' : '' ?>>Соревнования</option>
    </select>
  </form>

  <?php if (empty($events)): ?>
    <p>Нет событий.</p>
  <?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
      <thead>
        <tr>
          <th>Дата</th><th>Время</th><th>Название</th><th>Тип</th><th>Место</th><th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $event): ?>
          <tr>
            <td><?= $event['date'] ?></td>
            <td><?= $event['time'] ?></td>
            <td><?= htmlspecialchars($event['title']) ?></td>
            <td><?= $event['eventType'] === 'training' ? 'Тренировка' : 'Соревнование' ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <td>
              <a href="?childID=<?= $childID ?>&unlinkEventID=<?= $event['eventID'] ?>" onclick="return confirm('Удалить событие из списка ребёнка?')">❌ Удалить</a><br>
              <button class="edit-btn" data-event='<?= json_encode($event, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'>✏️ Редактировать</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- <h3>🔗 Привязать событие</h3>
  <form method="post" action="link_event.php">
    <input type="hidden" name="childID" value="<?= $childID ?>">
    <select name="eventID" required>
      <option value="">Выберите...</option>
      <?php foreach ($allEvents as $e): ?>
        <option value="<?= $e['eventID'] ?>"><?= htmlspecialchars($e['title']) ?> (<?= $e['date'] ?>)</option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Привязать</button>
  </form> -->

  <h3>➕ Новое событие</h3>
  <form method="post" action="add_event.php">
    <input type="hidden" name="childID" value="<?= $childID ?>">
    <input type="text" name="title" placeholder="Название" required><br>
    <select name="eventType" required>
      <option value="training">Тренировка</option>
      <option value="competition">Соревнование</option>
    </select><br>
    <textarea name="description" placeholder="Описание"></textarea><br>
    <input type="date" name="date" required>
    <input type="time" name="time" required><br>
    <input type="text" name="location" placeholder="Место" required><br>
    <button type="submit">Создать</button>
  </form>

  <p><a href="child_profile.php?childID=<?= $childID ?>">← Назад к профилю</a></p>
</main>

<!-- Модальное окно -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('editModal').style.display='none'">×</span>
    <h3>Редактировать событие</h3>
    <form method="post" action="update_event.php">
      <input type="hidden" name="eventID" id="editEventID">
      <input type="hidden" name="childID" value="<?= $childID ?>">
      <label>Название:</label><input type="text" name="title" id="editTitle" required><br>
      <label>Тип:</label>
      <select name="eventType" id="editType" required>
        <option value="training">Тренировка</option>
        <option value="competition">Соревнование</option>
      </select><br>
      <label>Описание:</label><textarea name="description" id="editDescription"></textarea><br>
      <label>Дата:</label><input type="date" name="date" id="editDate" required><br>
      <label>Время:</label><input type="time" name="time" id="editTime" required><br>
      <label>Место:</label><input type="text" name="location" id="editLocation" required><br>
      <button type="submit">💾 Сохранить</button>
    </form>
  </div>
</div>



<?php include 'footer.php'; ?>

<script>
    const editBtns = document.querySelectorAll('.edit-btn');
  const modal = document.getElementById('editModal');

  editBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      modal.style.display = 'block';

      document.getElementById('editEventID').value = btn.dataset.eventId;
      document.getElementById('editTitle').value = btn.dataset.title;
      document.getElementById('editType').value = btn.dataset.type;
      document.getElementById('editDescription').value = btn.dataset.description;
      document.getElementById('editDate').value = btn.dataset.date;
      document.getElementById('editTime').value = btn.dataset.time;
      document.getElementById('editLocation').value = btn.dataset.location;
    });
  });

  function closeModal() {
    modal.style.display = 'none';
  }

  window.onclick = function(event) {
    if (event.target == modal) closeModal();
  };
</script>
<script src="scripts/app.js"></script>
<script>
  lucide.createIcons();
</script>
</body>
</html>

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
    <title>Привязка события</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php include 'header.php'; ?>

<main>


<h3>➕ New event</h3>
  <form method="post" action="add_event.php">
    <input type="hidden" name="childID" value="<?= $childID ?>">
    <input type="text" name="title" placeholder="Название" required><br>
    <select name="eventType" required>
      <option value="training">Training</option>
      <option value="competition">Competition</option>
    </select><br>
    <textarea name="description" placeholder="Описание"></textarea><br>
    <input type="date" name="date" required>
    <input type="time" name="time" required><br>
    <input type="text" name="location" placeholder="Место" required><br>
    <button type="submit">Create</button>
  </form>

  <p><a href="child_profile.php?childID=<?= $childID ?>">← Back on profile</a></p>
</main>

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
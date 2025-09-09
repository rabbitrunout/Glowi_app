<?php
session_start();
require 'database.php';

// --- ЗАПРОСЫ ---
$stmt = $pdo->query("SELECT r.*, c.name AS childName 
                     FROM private_lesson_requests r 
                     JOIN children c ON r.childID = c.childID 
                     ORDER BY r.requestDate DESC");
$requests = $stmt->fetchAll();

// --- РАСПИСАНИЕ ---
$scheduleStmt = $pdo->query("SELECT s.*, c.name AS childName
                             FROM schedule s
                             JOIN children c ON s.childID = c.childID
                             ORDER BY s.eventDate, s.startTime");
$schedule = $scheduleStmt->fetchAll();
?>


<h2>Запросы на приватные занятия</h2>
<table border="1" cellpadding="5">
  <tr>
    <th>Ребёнок</th>
    <th>Сообщение</th>
    <th>Дата</th>
    <th>Статус</th>
    <th>Ответ</th>
  </tr>
  <?php foreach ($requests as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['childName']) ?></td>
      <td><?= htmlspecialchars($r['message']) ?></td>
      <td><?= $r['requestDate'] ?></td>
      <td><?= $r['status'] ?></td>
      <td>
        <form method="POST" action="update_request.php">
          <input type="hidden" name="requestID" value="<?= $r['requestID'] ?>">
          <select name="status">
            <option value="pending" <?= $r['status']=='pending'?'selected':'' ?>>Ожидание</option>
            <option value="approved" <?= $r['status']=='approved'?'selected':'' ?>>Одобрено</option>
            <option value="declined" <?= $r['status']=='declined'?'selected':'' ?>>Отклонено</option>
          </select>
          <input type="text" name="response" value="<?= htmlspecialchars($r['response']) ?>" placeholder="Ответ тренера">
          <button type="submit">Сохранить</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<hr>

<h2>Расписание</h2>
<form method="POST" action="add_event.php">
  <label>Ребёнок:</label>
  <select name="childID" required>
    <?php
    $children = $pdo->query("SELECT childID, name FROM children")->fetchAll();
    foreach ($children as $child) {
      echo "<option value='{$child['childID']}'>".htmlspecialchars($child['name'])."</option>";
    }
    ?>
  </select>
  <input type="text" name="title" placeholder="Название занятия" required>
  <input type="date" name="eventDate" required>
  <input type="time" name="startTime">
  <input type="time" name="endTime">
  <input type="text" name="notes" placeholder="Заметки">
  <button type="submit">Добавить</button>
</form>

<table border="1" cellpadding="5">
  <tr>
    <th>Ребёнок</th>
    <th>Занятие</th>
    <th>Дата</th>
    <th>Время</th>
    <th>Заметки</th>
    <th>Действия</th>
  </tr>
  <?php foreach ($schedule as $ev): ?>
    <tr>
      <td><?= htmlspecialchars($ev['childName']) ?></td>
      <td><?= htmlspecialchars($ev['title']) ?></td>
      <td><?= $ev['eventDate'] ?></td>
      <td><?= $ev['startTime']." - ".$ev['endTime'] ?></td>
      <td><?= htmlspecialchars($ev['notes']) ?></td>
      <td>
        <a href="edit_event.php?id=<?= $ev['eventID'] ?>">✏️</a>
        <a href="delete_event.php?id=<?= $ev['eventID'] ?>" onclick="return confirm('Удалить событие?')">🗑️</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'ru',
    events: 'fetch_requests_events.php', // отдельный PHP-файл, который вернёт JSON
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    }
  });
  calendar.render();
});
</script>


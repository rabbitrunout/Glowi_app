<?php
require 'database.php';

$editMode = isset($_GET['eventID']);
$event = [
    'title' => '',
    'eventType' => 'training',
    'description' => '',
    'date' => '',
    'time' => '',
    'location' => ''
];

if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE eventID = ?");
    $stmt->execute([$_GET['eventID']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        die("Событие не найдено.");
    }
}
?>

<h2><?= $editMode ? 'Редактировать' : 'Добавить' ?> событие</h2>

<form method="POST" action="event_save.php">
    <?php if ($editMode): ?>
        <input type="hidden" name="eventID" value="<?= $event['eventID'] ?>">
    <?php endif; ?>

    <label>Название:</label><br>
    <input type="text" name="title" required value="<?= htmlspecialchars($event['title']) ?>"><br><br>

    <label>Тип:</label><br>
    <select name="eventType" required>
        <option value="training" <?= $event['eventType'] === 'training' ? 'selected' : '' ?>>Тренировка</option>
        <option value="competition" <?= $event['eventType'] === 'competition' ? 'selected' : '' ?>>Соревнование</option>
    </select><br><br>

    <label>Описание:</label><br>
    <textarea name="description" rows="4"><?= htmlspecialchars($event['description']) ?></textarea><br><br>

    <label>Дата:</label><br>
    <input type="date" name="date" required value="<?= $event['date'] ?>"><br><br>

    <label>Время:</label><br>
    <input type="time" name="time" required value="<?= $event['time'] ?>"><br><br>

    <label>Место:</label><br>
    <input type="text" name="location" required value="<?= htmlspecialchars($event['location']) ?>"><br><br>

    <button type="submit"><?= $editMode ? 'Обновить' : 'Создать' ?></button>
</form>

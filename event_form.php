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
        die("The event was not found.");
    }
}
?>

<h2><?= $editMode ? 'Edit' : 'Add' ?> event </h2>

<form method="POST" action="event_save.php">
    <?php if ($editMode): ?>
        <input type="hidden" name="eventID" value="<?= $event['eventID'] ?>">
    <?php endif; ?>

    <label>Title:</label><br>
    <input type="text" name="title" required value="<?= htmlspecialchars($event['title']) ?>"><br><br>

    <label>Type:</label><br>
    <select name="eventType" required>
        <option value="training" <?= $event['eventType'] === 'training' ? 'selected' : '' ?>>Training</option>
        <option value="competition" <?= $event['eventType'] === 'competition' ? 'selected' : '' ?>>Competition</option>
    </select><br><br>

    <label>Description:</label><br>
    <textarea name="description" rows="4"><?= htmlspecialchars($event['description']) ?></textarea><br><br>

    <label>Date:</label><br>
    <input type="date" name="date" required value="<?= $event['date'] ?>"><br><br>

    <label>Time:</label><br>
    <input type="time" name="time" required value="<?= $event['time'] ?>"><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" required value="<?= htmlspecialchars($event['location']) ?>"><br><br>

    <button type="submit"><?= $editMode ? 'Update' : 'Create' ?></button>
</form>

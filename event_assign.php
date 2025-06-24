<?php
require 'database.php';

$eventID = $_GET['eventID'] ?? 0;
if (!$eventID) {
    die("Не указано событие.");
}

// Получаем всех детей
$children = $pdo->query("SELECT * FROM children ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Получаем уже привязанных
$stmt = $pdo->prepare("SELECT childID FROM child_event WHERE eventID = ?");
$stmt->execute([$eventID]);
$assigned = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'childID');
?>

<h2>Привязать детей к событию</h2>

<form method="POST" action="event_assign_save.php">
    <input type="hidden" name="eventID" value="<?= $eventID ?>">

    <?php foreach ($children as $child): ?>
        <label>
            <input type="checkbox" name="children[]" value="<?= $child['childID'] ?>"
                <?= in_array($child['childID'], $assigned) ? 'checked' : '' ?>>
            <?= htmlspecialchars($child['name']) ?> (<?= htmlspecialchars($child['age']) ?> лет)
        </label><br>
    <?php endforeach; ?>

    <br><button type="submit">Сохранить</button>
</form>

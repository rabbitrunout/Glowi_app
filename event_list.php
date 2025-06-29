<?php
require 'database.php';

// Обработка фильтра
$filter = $_GET['filter'] ?? 'all';
$allowedTypes = ['training', 'competition'];

if (in_array($filter, $allowedTypes)) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE eventType = ? ORDER BY date DESC, time DESC");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY date DESC, time DESC");
}
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>List Event's</title>
</head>
<body>
    <h1>List Event's</h1>

    <p><a href="event_form.php">➕ Add a new event</a></p>

    <form method="get" style="margin-bottom: 20px;">
        <label for="filter">Filter by type:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="training" <?= $filter === 'training' ? 'selected' : '' ?>>Training</option>
            <option value="competition" <?= $filter === 'competition' ? 'selected' : '' ?>>Competition</option>
        </select>
        <noscript><button type="submit">Apply</button></noscript>
    </form>

    <?php if (empty($events)): ?>
        <p>Events not found</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['date']) ?></td>
                        <td><?= htmlspecialchars($event['time']) ?></td>
                        <td><?= htmlspecialchars($event['title']) ?></td>
                        <td><?= $event['eventType'] === 'training' ? 'Training' : 'Competition' ?></td>
                        <td><?= htmlspecialchars($event['location']) ?></td>
                        <td>
                            <a href="event_form.php?eventID=<?= $event['eventID'] ?>">✏️ Edit</a><br>
                            <a href="event_assign.php?eventID=<?= $event['eventID'] ?>">🔗 Link children</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="dashboard.php">← Back</a></p>
</body>
</html>

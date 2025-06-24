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
    <title>Список событий</title>
</head>
<body>
    <h1>Список событий</h1>

    <p><a href="event_form.php">➕ Добавить новое событие</a></p>

    <form method="get" style="margin-bottom: 20px;">
        <label for="filter">Фильтр по типу:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Все</option>
            <option value="training" <?= $filter === 'training' ? 'selected' : '' ?>>Тренировки</option>
            <option value="competition" <?= $filter === 'competition' ? 'selected' : '' ?>>Соревнования</option>
        </select>
        <noscript><button type="submit">Применить</button></noscript>
    </form>

    <?php if (empty($events)): ?>
        <p>События не найдены.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Время</th>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Место</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['date']) ?></td>
                        <td><?= htmlspecialchars($event['time']) ?></td>
                        <td><?= htmlspecialchars($event['title']) ?></td>
                        <td><?= $event['eventType'] === 'training' ? 'Тренировка' : 'Соревнование' ?></td>
                        <td><?= htmlspecialchars($event['location']) ?></td>
                        <td>
                            <a href="event_form.php?eventID=<?= $event['eventID'] ?>">✏️ Редактировать</a><br>
                            <a href="event_assign.php?eventID=<?= $event['eventID'] ?>">🔗 Привязать детей</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="dashboard.php">← Назад</a></p>
</body>
</html>

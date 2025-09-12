<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];
$eventID = isset($_GET['eventID']) ? (int)$_GET['eventID'] : 0;

if ($eventID <= 0) {
    die("Некорректный ID события.");
}

// Получаем список детей родителя
$stmt = $pdo->prepare("SELECT * FROM children WHERE parentID = ?");
$stmt->execute([$parentID]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем уже привязанных детей
$stmt = $pdo->prepare("SELECT childID FROM child_event WHERE eventID = ?");
$stmt->execute([$eventID]);
$linked = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'childID');

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['children'] ?? [];

    // Удаляем старые связи
    $stmt = $pdo->prepare("DELETE FROM child_event WHERE eventID = ? AND childID IN (
        SELECT childID FROM children WHERE parentID = ?
    )");
    $stmt->execute([$eventID, $parentID]);

    // Добавляем новые связи
    $insertStmt = $pdo->prepare("INSERT INTO child_event (eventID, childID, createdBy) VALUES (?, ?, 'parent')");
    foreach ($selected as $childID) {
        $insertStmt->execute([$eventID, (int)$childID]);
    }

    header("Location: event_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Event Binding</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="container">
    <h1>🔗 Event Binding #<?= $eventID ?> к детям</h1>

    <form method="post">
        <fieldset>
            <legend>Select the children to link to:</legend>
            <?php foreach ($children as $child): ?>
                <label>
                    <input type="checkbox" name="children[]" value="<?= $child['childID'] ?>"
                        <?= in_array($child['childID'], $linked) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($child['name']) ?> (<?= htmlspecialchars($child['groupLevel']) ?>)
                </label><br>
            <?php endforeach; ?>
        </fieldset>

        <br>
        <button type="submit">💾 Save the link</button>
        <a href="event_list.php" class="button">← Back to the list of events</a>
    </form>
</main>

<?php include 'footer.php'; ?>
</body>
</html>

<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Некорректный ID ребенка.");
}

$childID = (int)$_GET['childID'];

// Проверка принадлежности ребенка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("Ребенок не найден или доступ запрещен.");
}

// Получаем платежи
$stmt = $pdo->prepare("SELECT * FROM payments WHERE childID = ? ORDER BY paymentDate DESC");
$stmt->execute([$childID]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Платежи для <?= htmlspecialchars($child['name']) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<h1>Платежи ребенка: <?= htmlspecialchars($child['name']) ?></h1>

<?php if (empty($payments)): ?>
    <p>Платежи отсутствуют.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?= htmlspecialchars($payment['paymentDate']) ?></td>
                <td><?= number_format($payment['amount'], 2, ',', ' ') ?> руб.</td>
                <td><?= htmlspecialchars($payment['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="dashboard.php">← Back to your personal account</a></p>

<?php include 'footer.php'; ?>
</body>
</html>

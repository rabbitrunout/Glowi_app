<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Invalid child's ID.");
}

$childID = (int)$_GET['childID'];

// Проверка принадлежности ребенка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("The child has not been found or access is denied.");
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
  <title>Payments for <?= htmlspecialchars($child['name']) ?></title>
   <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/child_profile_neon.css">
</head>
<body>
<?php include 'header.php'; ?>

<h1>Child's payments: <?= htmlspecialchars($child['name']) ?></h1>

<?php if (empty($payments)): ?>
    <p>There are no payments.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?= htmlspecialchars($payment['paymentDate']) ?></td>
                <td><?= number_format($payment['amount'], 2, ',', ' ') ?> $ CAD</td>
                <td><?= htmlspecialchars($payment['status']) ?></td>
            </tr>
            
            <?php endforeach; ?>
        </tbody>
        <p><a href="add_payment.php?childID=<?= $childID ?>" class="button">
        <i data-lucide="plus-circle"></i> Add payment </a></p>
    </table>
<?php endif; ?>


      

<p><a href="child_profile.php?childID=<?= $childID ?>">← Back to profile</a></p>

<?php include 'footer.php'; ?>
</body>
</html>

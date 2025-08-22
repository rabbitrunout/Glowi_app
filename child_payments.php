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

// Проверка принадлежности ребёнка
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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payments — <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card">
  <h2><i data-lucide="credit-card"></i> Payments — <?= htmlspecialchars($child['name']) ?></h2>

  <?php if (empty($payments)): ?>
    <p style="color:#ffccff;">There are no payments.</p>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="glowi-table">
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
      </table>
    </div>
  <?php endif; ?>

  <p style="margin-top:1rem;">
    <a href="add_payment.php?childID=<?= $childID ?>" class="btn-save">
      <i data-lucide="plus-circle"></i> Add payment
    </a>
  </p>

  <p><a href="child_profile.php?childID=<?= $childID ?>">← Back to profile</a></p>
</main>

<?php include 'footer.php'; ?>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();
</script>
</body>
</html>

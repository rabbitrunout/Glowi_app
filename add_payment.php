<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    header('Location: login_form.php');
    exit;
}

$parentID = $_SESSION['parentID'];

if (!isset($_GET['childID']) || !is_numeric($_GET['childID'])) {
    die("Invalid child ID.");
}

$childID = (int)$_GET['childID'];

// Проверка принадлежности ребенка
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    die("The child has not been found or access is denied.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $status = $_POST['status'] ?? '';
    $paymentDate = $_POST['paymentDate'] ?? '';

    if ($amount > 0 && in_array($status, ['paid', 'unpaid']) && $paymentDate) {
        $stmt = $pdo->prepare("INSERT INTO payments (childID, amount, status, paymentDate) VALUES (?, ?, ?, ?)");
        $stmt->execute([$childID, $amount, $status, $paymentDate]);
        header("Location: child_payments.php?childID=$childID");
        exit;
    } else {
        $error = "Please fill in all fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Add a payment — <?= htmlspecialchars($child['name']) ?></title>
  <link rel="stylesheet" href="css/main.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card">
  <h2><i data-lucide="plus-circle"></i> Add a payment — <?= htmlspecialchars($child['name']) ?></h2>

  <?php if ($error): ?>
    <div class="glowi-message error">
      <i data-lucide="alert-triangle"></i>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <label>Amount (CAD):</label>
    <input type="number" step="0.01" min="0.01" name="amount" required>

    <label>Status:</label>
    <select name="status" required>
      <option value="">Select the status</option>
      <option value="paid">Paid</option>
      <option value="unpaid">Not paid</option>
    </select>

    <label>Payment date:</label>
    <input type="date" name="paymentDate" required>

    <button type="submit" class="btn-save">💾 Save</button>
  </form>

  <p style="margin-top:.8rem;">
    <a href="child_payments.php?childID=<?= $childID ?>">← Back to payments</a>
  </p>
  <p><a href="child_profile.php?childID=<?= $childID ?>">← Back to profile</a></p>
</main>

<?php include 'footer.php'; ?>
<script> lucide.createIcons(); </script>
</body>
</html>

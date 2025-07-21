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
    die("Ребенок не найден или доступ запрещён.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $status = $_POST['status'];
    $paymentDate = $_POST['paymentDate'];

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
  <title>Add a payment— <?= htmlspecialchars($child['name']) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<h1>Add a payment for <?= htmlspecialchars($child['name']) ?></h1>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Amount (CAD):</label><br>
    <input type="number" step="0.01" min="0.01" name="amount" required><br><br>

    <label>Status:</label><br>
    <select name="status" required>
        <option value="">Select the status</option>
        <option value="paid">Paid</option>
        <option value="unpaid">Not paid</option>
    </select><br><br>

    <label>Payment date:</label><br>
    <input type="date" name="paymentDate" required><br><br>

    <button type="submit">Add a payment</button>
</form>

<p><a href="child_payments.php?childID=<?= $childID ?>">← Back to payments</a></p>

<p><a href="child_profile.php?childID=<?= $childID ?>">← Вернуться к профилю</a></p>

<?php include 'footer.php'; ?>
</body>
</html>

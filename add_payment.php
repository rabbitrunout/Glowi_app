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
        $error = "Пожалуйста, заполните все поля корректно.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Добавить платеж — <?= htmlspecialchars($child['name']) ?></title>
</head>
<body>
<?php  'header.php'; ?>

<h1>Добавить платеж для <?= htmlspecialchars($child['name']) ?></h1>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Сумма (руб):</label><br>
    <input type="number" step="0.01" min="0.01" name="amount" required><br><br>

    <label>Статус:</label><br>
    <select name="status" required>
        <option value="">Выберите статус</option>
        <option value="paid">Оплачено</option>
        <option value="unpaid">Не оплачено</option>
    </select><br><br>

    <label>Дата платежа:</label><br>
    <input type="date" name="paymentDate" required><br><br>

    <button type="submit">Добавить платеж</button>
</form>

<p><a href="child_payments.php?childID=<?= $childID ?>">← Назад к платежам</a></p>

<?php 'footer.php'; ?>
</body>
</html>

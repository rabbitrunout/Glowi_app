<?php
session_start();
require 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['userName'] ?? '');
    $email = trim($_POST['emailAddress'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        $error = "Please complete all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM parents WHERE emailAddress = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "This email address is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO parents (userName, emailAddress, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed]);

            $parentID = $pdo->lastInsertId();

            $_SESSION['parentID'] = $parentID;
            $_SESSION['userName'] = $username;

            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Parent registration – Glowi</title>
    <link rel="stylesheet" href="css/main.css" />
   
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card">
    <h2>Parent registration</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>Username:</label>
        <input type="text" name="userName" required value="<?= htmlspecialchars($username ?? '') ?>">

        <label>Email:</label>
        <input type="email" name="emailAddress" required value="<?= htmlspecialchars($email ?? '') ?>">

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn-save">Sign up</button>
    </form>

    <p>Already registered? <a href="login_form.php">Login</a></p>
</main>

<?php include 'footer.php'; ?>
</body>
</html>

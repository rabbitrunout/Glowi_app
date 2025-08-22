
<?php
session_start();
require_once('mail_template.php');

$body = build_email_template($user_name);

send_email($to_address, $to_name, $from_address, $from_name, $subject, $body, true);


// Получение данных из формы
$user_name = filter_input(INPUT_POST, 'user_name');
$password = filter_input(INPUT_POST, 'password');
$email_address = filter_input(INPUT_POST, 'email_address');

if ($user_name === null || $password === null || $email_address === null) {
    $_SESSION["add_error"] = "Нever registration data. Check all fields.";
    header("Location: error.php");
    exit;
}

require_once('db.php');

// Проверка дубликатов по email и username
$query = 'SELECT * FROM parents WHERE userName = :userName OR emailAddress = :emailAddress';
$statement = $pdo->prepare($query);
$statement->bindValue(':userName', $user_name);
$statement->bindValue(':emailAddress', $email_address);
$statement->execute();
$existing = $statement->fetch();
$statement->closeCursor();

if ($existing) {
    $_SESSION["add_error"] = "A user with the same email address or name already exists.";
    header("Location: error.php");
    exit;
}

// Хеширование пароля
$hash = password_hash($password, PASSWORD_DEFAULT);

// Добавление родителя
$query = 'INSERT INTO parents (userName, password, emailAddress)
          VALUES (:userName, :password, :emailAddress)';
$statement = $pdo->prepare($query);
$statement->bindValue(':userName', $user_name);
$statement->bindValue(':password', $hash);
$statement->bindValue(':emailAddress', $email_address);
$statement->execute();
$statement->closeCursor();

$_SESSION["parentID"] = $pdo->lastInsertId();
$_SESSION["userName"] = $user_name;

// Настройка email
$to_address = $email_address;
$to_name = $user_name;
$from_address = 'your_email@example.com';
$from_name = 'Glowi Team';
$subject = 'Glowi – Registration is completed';
$body = '<p>Thank you for registering in the system Glowi.</p><p>With respect,<br>team Glowi</p>';
$is_body_html = true;

// Отправка email
try {
    send_email($to_address, $to_name, $from_address, $from_name, $subject, $body, $is_body_html);
} catch (Exception $ex) {
    $_SESSION["add_error"] = $ex->getMessage();
    header("Location: error.php");
    exit;
}

// Переход на страницу подтверждения
header("Location: register_confirmation.php");
exit;
?>


<?php
// Заглушка для отправки email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to_address, $to_name, $from_address, $from_name, $subject, $body, $is_body_html = true) {
    // Вы можете заменить этот блок реальной реализацией через PHPMailer или mail()
    // Для тестов просто логируем в файл
    $log = "TO: $to_address\nSUBJECT: $subject\nBODY:\n$body\n\n";
    file_put_contents("mail_log.txt", $log, FILE_APPEND);
}
?>

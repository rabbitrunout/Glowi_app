<?php
require 'database.php';

$requestID = $_POST['requestID'];
$status = $_POST['status'];
$response = $_POST['response'];

$stmt = $pdo->prepare("UPDATE private_lesson_requests SET status=?, response=? WHERE requestID=?");
$stmt->execute([$status, $response, $requestID]);

header("Location: manage_requests.php?updated=1");
exit;
?>



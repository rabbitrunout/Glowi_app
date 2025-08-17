<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) header("Location: login_form.php");

$parentID = $_SESSION['parentID'];
$scheduleID = $_GET['scheduleID'] ?? die("No schedule ID");
$childID = $_GET['childID'] ?? die("No child ID");

// Проверка доступа
$stmt = $pdo->prepare("
    SELECT s.*, c.parentID 
    FROM schedule s 
    JOIN children c ON c.childID = s.childID 
    WHERE s.scheduleID = ?
");
$stmt->execute([$scheduleID]);
$sched = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sched || $sched['parentID'] != $parentID) die("Access denied.");

// Удаление
$stmt = $pdo->prepare("DELETE FROM schedule WHERE scheduleID=?");
$stmt->execute([$scheduleID]);

header("Location: child_profile.php?childID=$childID");
exit;

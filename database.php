<?php 
$host = 'localhost'; 
$db = 'glowi'; 
$user = 'root';
 $pass = ''; 
// ← замени на свой пароль

try {
   $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass); 
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } 
   catch (PDOException $e) { die("DB connection failed: " . $e->getMessage()); 
   } 
?>
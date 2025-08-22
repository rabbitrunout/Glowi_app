
<?php
session_start();    
?>
<!DOCTYPE html>
<html>
<head>
    <title>Glowi – confirmation of registration</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
</head>
<body>
<?php include("header.php"); ?>

<main>
    <h2>Registration completed successfully</h2>
    <p>thamk you, <?= htmlspecialchars($_SESSION["userName"] ?? 'гость') ?>, you have successfully registered.</p>
    <p>Now you can go to <a href="dashboard.php">personal account</a>.</p>
</main>

<?php include("footer.php"); ?>
</body>
</html>

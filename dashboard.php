<?php
session_start();

// Sjekk om brukeren er logget inn
if (!isset($_SESSION['username'])) {
    header('Location: index.php'); // Send tilbake til innlogging hvis ikke logget inn
    exit();
}

// Håndter logg ut
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<h1>Velkommen, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
<p>Du er nå logget inn.</p>
<a href="?logout=true">Logg ut</a>
</body>
</html>

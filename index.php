<?php
// Databasekonfigurasjon
$dsn = 'mysql:host=localhost;dbname=testdb;charset=utf8';
$username = 'root';
$password = 'password';

$errors = [];
$success = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Opprett tabellen hvis den ikke finnes
    $createTableSQL = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )";
    $pdo->exec($createTableSQL);
} catch (PDOException $e) {
    $errors[] = "Databasefeil: " . $e->getMessage();
}

// Håndtering av registrering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    // Valider brukernavn
    if (empty($user)) {
        $errors[] = "Brukernavn kan ikke være tomt.";
    } elseif (strlen($user) < 3) {
        $errors[] = "Brukernavn må være minst 3 tegn langt.";
    }

    // Valider passord
    if (empty($pass)) {
        $errors[] = "Passord kan ikke være tomt.";
    } elseif (strlen($pass) < 6) {
        $errors[] = "Passord må være minst 6 tegn langt.";
    }

    // Hvis ingen feil, fortsett registrering
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
            $stmt->execute(['username' => $user, 'password' => $hashedPassword]);
            $success = "Registrering vellykket!";
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Unique constraint violation
                $errors[] = "Brukernavnet er allerede i bruk.";
            } else {
                $errors[] = "Registreringsfeil: " . $e->getMessage();
            }
        }
    }
}

// Håndtering av innlogging
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    // Validere input
    if (empty($user)) {
        $errors[] = "Vennligst skriv inn brukernavn.";
    }

    if (empty($pass)) {
        $errors[] = "Vennligst skriv inn passord.";
    }

    // Hvis ingen input-feil, sjekk innlogging
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
            $stmt->execute(['username' => $user]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userRow && password_verify($pass, $userRow['password'])) {
                session_start();
                $_SESSION['username'] = $user;
                header('Location: dashboard.php');
                exit();
            } else {
                $errors[] = "Feil brukernavn eller passord.";
            }
        } catch (PDOException $e) {
            $errors[] = "Innloggingsfeil: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrering og Innlogging</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<h1>Registrering</h1>
<form method="post">
    <label for="username">Brukernavn:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Passord:</label>
    <input type="password" id="password" name="password" required><br>

    <button type="submit" name="register">Registrer</button>
</form>

<h1>Innlogging</h1>
<form method="post">
    <label for="username">Brukernavn:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Passord:</label>
    <input type="password" id="password" name="password" required><br>

    <button type="submit" name="login">Logg inn</button>
</form>

<?php
// Vis feilmeldinger
if (!empty($errors)) {
    echo '<div class="error-message">';
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo '</div>';
}

// Vis suksessmelding
if (!empty($success)) {
    echo '<div class="success-message">' . $success . '</div>';
}
?>
</body>
</html>

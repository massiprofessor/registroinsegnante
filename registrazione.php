<?php
// Connessione al database
$host = "localhost";
$db_name = "regsx_class";
$db_user = "root";
$db_password = "";

$conn = new mysqli($host, $db_user, $db_password, $db_name);

// Verifica connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Variabili per eventuali messaggi di errore
$error_message = "";

// Verifica che il form sia stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm-password']);
    $codice = $conn->real_escape_string($_POST['codice']);  // Codice inserito dall'utente

    // Controllo password
    if ($password !== $confirm_password) {
        $error_message = "Le password non corrispondono!";
    } else {
        // Hash della password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Verifica del codice "Accademia"
        // Se il codice è vuoto o diverso da "Accademia", imposta root su "NO", altrimenti su "SI"
        $root = (empty($codice) || $codice !== "Accademia") ? "NO" : "SI";

        // Inserimento nel database
        $sql = "INSERT INTO docente (Username, Email, Password, root) VALUES ('$username', '$email', '$password_hash', '$root')";

        if ($conn->query($sql) === TRUE) {
            header("Location: login_reg.html");  // Reindirizza al login
            exit;
        } else {
            $error_message = "Errore: " . $conn->error;
        }
    }

    // Chiudi connessione
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina di Registrazione</title>
    <!-- Collega il file CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="registration-container">
        <h2>Registrati</h2>

        <!-- Messaggio di errore, se presente -->
        <?php if ($error_message): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Form di registrazione -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Nome Utente</label>
                <input type="text" id="username" name="username" placeholder="Inserisci il tuo nome utente" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Inserisci la tua email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Inserisci la tua password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Conferma Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Conferma la tua password" required>
            </div>
            <div class="form-group">
                <label for="codice">Codice (Opzionale)</label>
                <input type="text" id="codice" name="codice" placeholder="Inserisci il codice 'Accademia' per ottenere l'accesso root">
            </div>
            <button type="submit" class="register-btn">Registrati</button>
        </form>

        <div class="login-link">
            <p>Hai già un account? <a href="login.html">Accedi</a></p>
        </div>
    </div>
</body>
</html>

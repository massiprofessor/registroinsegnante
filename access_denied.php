<?php
session_start(); // Avvia la sessione

// Controlla se l'utente è loggato
if (!isset($_SESSION['User_id'])) {
    // Se l'utente non è loggato, reindirizza alla pagina di login
    header("Location: login.html");
    exit;
}

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

// Recupera i dettagli dell'utente
$user_id = $_SESSION['User_id'];
$sql = "SELECT Username, root FROM docente WHERE ID_Docente = '$user_id'";
$result = $conn->query($sql);

// Recupera il nome dell'utente e il suo privilegio
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row['Username'];
    $root = $row['root'];  // Prendi il valore della colonna root
} else {
    $username = "Utente sconosciuto";
    $root = "NO";
}

// Verifica se l'utente ha privilegi di amministratore (root)
if ($root !== "SI") {
    // Se l'utente non è root, mostra la pagina di accesso negato
    echo "
    <!DOCTYPE html>
    <html lang='it'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Accesso Negato</title>
        <link rel='stylesheet' href='style.css'> <!-- Collegamento al file CSS -->
    </head>
    <body>
        <div class='access-denied-container'>
            <h1>Accesso Negato</h1>
            <p>Ciao " . htmlspecialchars($username) . ",</p>
            <p>Non sei autorizzato a visualizzare questa pagina. La tua sessione non ha i privilegi di amministratore (root).</p>
            <p>Se pensi di aver ricevuto questo messaggio per errore, contatta l'amministratore del sistema.</p>
            <div class='back-button'>
                <a href='dashboard.php'>Torna alla Pagina Principale</a>
            </div>
        </div>
    </body>
    </html>
    ";
    exit;
}

// Chiudi connessione
$conn->close();
?>

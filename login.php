<?php
session_start();
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

// Verifica che il form sia stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // Controllo se l'email esiste nel database
    $sql = "SELECT * FROM docente WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifica della password
        if (password_verify($password, $user['Password'])) {
			// Memorizza l'utente nella sessione
            $_SESSION['User_id'] = $user['ID_Docente'];
            $_SESSION['username'] = $user['Username'];
            header("Location: dashboard.php");
        } else {
            echo "Password errata.";
        }
    } else {
        echo "Email non trovata.";
    }

    // Chiudi connessione
    $conn->close();
} else {
    echo "Richiesta non valida.";
}
?>
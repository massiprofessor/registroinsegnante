<?php
// Connessione al database (modifica con i tuoi dati)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "regsx_class";

$conn = new mysqli($servername, $username, $password, $dbname);

// Controllo della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Controllo del metodo POST e dell'esistenza del campo 'id_materia'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_materia'])) {
    $idMateria = $conn->real_escape_string($_POST['id_materia']);

    // Eliminazione dalla tabella classi_materie
    $sql_classi = "DELETE FROM classi_materie WHERE ID_M = '$idMateria'";
    if ($conn->query($sql_classi) === TRUE) {
        // Eliminazione dalla tabella materie
        $sql_materie = "DELETE FROM materie WHERE ID_Materia = '$idMateria'";
        if ($conn->query($sql_materie) === TRUE) {
            // Reindirizzamento alla pagina principale
            header('Location: materie.php');
            exit;
        } else {
            echo "Errore nell'eliminazione della materia: " . $conn->error;
        }
    } else {
        echo "Errore nell'eliminazione dalle classi: " . $conn->error;
    }
} else {
    echo "Richiesta non valida.";
}

// Chiusura della connessione
$conn->close();
?>
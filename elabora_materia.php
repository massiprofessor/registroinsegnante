<?php
// Configurazione del database
$host = 'localhost';     // Host del database
$dbname = 'regsx_class'; // Nome del database
$user = 'root';      // Username del database
$password = '';  // Password del database

try {
    // Connessione al database con PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Controlla se il modulo è stato inviato
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome_materia = trim($_POST['nome_materia']); // Recupera e pulisce il dato

        // Controlla che il campo non sia vuoto
        if (!empty($nome_materia)) {
            // Query SQL per inserire il dato
            $stmt = $conn->prepare("INSERT INTO materie (Materia) VALUES (:nome_materia)");
            $stmt->bindParam(':nome_materia', $nome_materia, PDO::PARAM_STR);
            $stmt->execute();
			header("Location: materie.php");
        } else {
            echo "Il campo Nome Materia è vuoto!";
        }
    }
} catch (PDOException $e) {
    echo "Errore nella connessione al database: " . $e->getMessage();
}
?>
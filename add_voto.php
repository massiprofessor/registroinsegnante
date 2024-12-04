<?php
session_start();
if (!isset($_SESSION['User_id'])) {
    header("Location: login.html");
    exit;
}

// Connessione al database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "regsx_class";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Gestione invio del form
if (isset($_GET['studente'], $_GET['classe'], $_GET['materia']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $studente_id = $_GET['studente'];
    $classe_id = $_GET['classe'];
    $materia_id = $_GET['materia'];
    $voto = $_POST['voto'];
    $data = $_POST['data'];

    $query = "INSERT INTO voti (ID_Studente, ID_Materia, Voto, Data) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $studente_id, $materia_id, $voto, $data);

    if ($stmt->execute()) {
        header("Location: gestione_voti.php?classe=$classe_id");
        exit;
    } else {
        $error = "Errore durante l'inserimento del voto: " . $conn->error;
    }
}

// Recupera la materia precompilata
if (isset($_GET['materia'])) {
    $materia_id = $_GET['materia'];

    $query_materia = "SELECT Materia FROM materie WHERE ID_Materia = ?";
    $stmt_materia = $conn->prepare($query_materia);
    $stmt_materia->bind_param("i", $materia_id);
    $stmt_materia->execute();
    $result_materia = $stmt_materia->get_result();
    $materia = $result_materia->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Aggiungi Voto</h1>
        <hr>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="materia" class="form-label">Materia</label>
                <input type="text" id="materia" class="form-control" value="<?= htmlspecialchars($materia['Materia']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="voto" class="form-label">Voto</label>
                <input type="number" name="voto" id="voto" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="data" class="form-label">Data</label>
                <input type="date" name="data" id="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Aggiungi</button>
            <a href="gestione_voti.php?classe=<?= htmlspecialchars($_GET['classe']) ?>" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
</body>
</html>


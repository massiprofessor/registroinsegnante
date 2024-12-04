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

// Controllo parametri GET
if (!isset($_GET['voto'], $_GET['classe'])) {
    die("Errore: parametri mancanti.");
}

$voto_id = intval($_GET['voto']);
$classe_id = intval($_GET['classe']);

// Gestione invio del form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voto = intval($_POST['voto']);
    $data = $_POST['data'];

    if ($voto > 0 && !empty($data)) {
        $query = "UPDATE voti SET Voto = ?, Data = ? WHERE ID_Voto = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $voto, $data, $voto_id);

        if ($stmt->execute()) {
            header("Location: gestione_voti.php?classe=$classe_id");
            exit;
        } else {
            $error = "Errore durante la modifica del voto: " . $conn->error;
        }
    } else {
        $error = "Il voto e la data sono obbligatori e devono essere validi.";
    }
}

// Recupera informazioni sul voto
$query = "SELECT Voto, Data FROM voti WHERE ID_Voto = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $voto_id);
$stmt->execute();
$result = $stmt->get_result();
$voto_data = $result->fetch_assoc();

if (!$voto_data) {
    die("Errore: voto non trovato.");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Modifica Voto</h1>
        <hr>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="voto" class="form-label">Voto</label>
                <input type="number" name="voto" id="voto" class="form-control" value="<?= htmlspecialchars($voto_data['Voto']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="data" class="form-label">Data</label>
                <input type="date" name="data" id="data" class="form-control" value="<?= htmlspecialchars($voto_data['Data']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salva</button>
            <a href="gestione_voti.php?classe=<?= $classe_id ?>" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
</body>
</html>

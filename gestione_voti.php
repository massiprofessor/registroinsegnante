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

// Ottieni ID docente dalla sessione
$docente_id = $_SESSION['User_id'];

// Seleziona le classi associate al docente
$query_classi = "
    SELECT c.ID_Classe, c.Nome 
    FROM classi c 
    INNER JOIN classi_docenti cd ON c.ID_Classe = cd.ID_C
    WHERE cd.ID_D = ?";
$stmt_classi = $conn->prepare($query_classi);
$stmt_classi->bind_param("i", $docente_id);
$stmt_classi->execute();
$result_classi = $stmt_classi->get_result();
$classi = $result_classi->fetch_all(MYSQLI_ASSOC);

// Ottieni gli alunni e i voti per una classe specifica
$alunni = [];
$materie = [];
if (isset($_GET['classe'])) {
    $classe_id = $_GET['classe'];

    // Recupera gli alunni della classe
    $query_alunni = "
        SELECT s.ID_Studente, s.Nome, s.Cognome
        FROM studenti s
        WHERE s.ID_Classe = ?";
    $stmt_alunni = $conn->prepare($query_alunni);
    $stmt_alunni->bind_param("i", $classe_id);
    $stmt_alunni->execute();
    $result_alunni = $stmt_alunni->get_result();
    $alunni = $result_alunni->fetch_all(MYSQLI_ASSOC);

    // Recupera le materie della classe
    $query_materie = "
        SELECT m.ID_Materia, m.Materia
        FROM classi_materie cm
        INNER JOIN materie m ON cm.ID_M = m.ID_Materia
        WHERE cm.ID_C = ?";
    $stmt_materie = $conn->prepare($query_materie);
    $stmt_materie->bind_param("i", $classe_id);
    $stmt_materie->execute();
    $result_materie = $stmt_materie->get_result();
    $materie = $result_materie->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Voti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Gestione Voti</h1>
            <a href="dashboard.php" class="btn btn-outline-secondary">Torna alla Dashboard</a>
        </div>

        <!-- Selezione classe -->
        <form method="GET" class="mb-4">
            <label for="classe" class="form-label">Seleziona una classe:</label>
            <select name="classe" id="classe" class="form-select" onchange="this.form.submit()">
                <option value="" selected disabled>-- Seleziona --</option>
                <?php foreach ($classi as $classe): ?>
                    <option value="<?= $classe['ID_Classe'] ?>" <?= isset($classe_id) && $classe_id == $classe['ID_Classe'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($classe['Nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Tabella studenti e voti -->
        <?php if (!empty($alunni) && !empty($materie)): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Studente</th>
                        <?php foreach ($materie as $materia): ?>
                            <th><?= htmlspecialchars($materia['Materia']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunni as $alunno): ?>
                        <tr>
                            <td>
                                <a href="scheda_alunno.php?studente=<?= $alunno['ID_Studente'] ?>&classe=<?= $classe_id ?>" class="text-decoration-none">
    <?= htmlspecialchars($alunno['Nome'] . ' ' . $alunno['Cognome']) ?>
</a>
                            </td>
                            <?php foreach ($materie as $materia): ?>
                                <?php
                                // Recupera il voto per questa materia e studente
                                $query_voto = "
                                    SELECT ID_Voto, Voto
                                    FROM voti
                                    WHERE ID_Studente = ? AND ID_Materia = ?";
                                $stmt_voto = $conn->prepare($query_voto);
                                $stmt_voto->bind_param("ii", $alunno['ID_Studente'], $materia['ID_Materia']);
                                $stmt_voto->execute();
                                $result_voto = $stmt_voto->get_result();
                                $voto = $result_voto->fetch_assoc();
                                ?>
                                <td>
                                    <?php if ($voto): ?>
                                        <span class="badge bg-success"><?= htmlspecialchars($voto['Voto']) ?></span>
                                        <a href="edit_voto.php?voto=<?= $voto['ID_Voto'] ?>&classe=<?= $classe_id ?>" class="btn btn-sm btn-primary mt-1">Edita</a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                        <a href="add_voto.php?studente=<?= $alunno['ID_Studente'] ?>&classe=<?= $classe_id ?>&materia=<?= $materia['ID_Materia'] ?>" class="btn btn-sm btn-success mt-1">Aggiungi</a>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($classe_id)): ?>
            <div class="alert alert-warning">Nessun alunno o materia trovata per questa classe.</div>
        <?php endif; ?>
    </div>
</body>
</html>

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

// Recupera l'ID dello studente e della classe
if (!isset($_GET['studente'], $_GET['classe'])) {
    die("Errore: parametri mancanti.");
}

$studente_id = intval($_GET['studente']);
$classe_id = intval($_GET['classe']);

// Recupera i dettagli dello studente
$query_studente = "
    SELECT s.Nome, s.Cognome, c.Nome AS Classe, s.Commento
    FROM studenti s
    INNER JOIN classi c ON s.ID_Classe = c.ID_Classe
    WHERE s.ID_Studente = ?";
$stmt_studente = $conn->prepare($query_studente);
$stmt_studente->bind_param("i", $studente_id);
$stmt_studente->execute();
$result_studente = $stmt_studente->get_result();
$studente = $result_studente->fetch_assoc();

if (!$studente) {
    die("Errore: studente non trovato.");
}

// Gestione aggiornamento commento
$editing_commento = isset($_GET['edit_commento']) && $_GET['edit_commento'] === '1';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commento'])) {
    $commento = trim($_POST['commento']);

    $query_update_commento = "UPDATE studenti SET Commento = ? WHERE ID_Studente = ?";
    $stmt_update = $conn->prepare($query_update_commento);
    $stmt_update->bind_param("si", $commento, $studente_id);

    if ($stmt_update->execute()) {
        $studente['Commento'] = $commento; // Aggiorna localmente il commento
        $success = "Commento aggiornato con successo.";
        $editing_commento = false; // Esci dalla modalità di modifica
    } else {
        $error = "Errore durante l'aggiornamento del commento.";
    }
}

// Recupera i voti dello studente
$query_voti = "
    SELECT m.Materia, v.Voto, v.Data
    FROM voti v
    INNER JOIN materie m ON v.ID_Materia = m.ID_Materia
    WHERE v.ID_Studente = ?
    ORDER BY m.Materia ASC, v.Data ASC";
$stmt_voti = $conn->prepare($query_voti);
$stmt_voti->bind_param("i", $studente_id);
$stmt_voti->execute();
$result_voti = $stmt_voti->get_result();
$voti = $result_voti->fetch_all(MYSQLI_ASSOC);

// Prepara i dati per il grafico
$grafico_dati = [];
foreach ($voti as $voto) {
    $materia = $voto['Materia'];
    if (!isset($grafico_dati[$materia])) {
        $grafico_dati[$materia] = [];
    }
    $grafico_dati[$materia][] = $voto['Voto'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheda Alunno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Scheda Alunno</h1>
            <a href="gestione_voti.php?classe=<?= $classe_id ?>" class="btn btn-outline-secondary">Torna alla Classe</a>
        </div>

        <!-- Dettagli alunno -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($studente['Nome'] . ' ' . $studente['Cognome']) ?></h5>
                <p class="card-text"><strong>Classe:</strong> <?= htmlspecialchars($studente['Classe']) ?></p>
                <?php if ($editing_commento): ?>
                    <form method="POST" class="mt-3">
                        <label for="commento" class="form-label"><strong>Modifica Commento:</strong></label>
                        <textarea name="commento" id="commento" rows="3" class="form-control"><?= htmlspecialchars($studente['Commento'] ?? '') ?></textarea>
                        <button type="submit" class="btn btn-primary mt-2">Salva Commento</button>
                        <a href="scheda_alunno.php?studente=<?= $studente_id ?>&classe=<?= $classe_id ?>" class="btn btn-secondary mt-2">Annulla</a>
                    </form>
                <?php else: ?>
                    <p><strong>Commento:</strong> <?= htmlspecialchars($studente['Commento'] ?? 'Nessun commento disponibile.') ?></p>
                    <a href="scheda_alunno.php?studente=<?= $studente_id ?>&classe=<?= $classe_id ?>&edit_commento=1" class="btn btn-sm btn-primary">Modifica Commento</a>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success mt-3"><?= htmlspecialchars($success) ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Grafico voti -->
        <h2>Andamento Voti</h2>
        <?php if (!empty($grafico_dati)): ?>
            <div class="mb-4">
                <canvas id="graficoVoti"></canvas>
            </div>
            <script>
                const ctx = document.getElementById('graficoVoti').getContext('2d');
                const graficoDati = <?= json_encode($grafico_dati) ?>;

                const labels = Object.keys(graficoDati);
                const datasets = labels.map((materia, index) => ({
                    label: materia,
                    data: graficoDati[materia],
                    backgroundColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.7)`,
                }));

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                enabled: true
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Materie'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Voti'
                                },
                                beginAtZero: true,
                                max: 10
                            }
                        }
                    }
                });
            </script>
        <?php else: ?>
            <p class="text-muted">Nessun voto disponibile per questo studente.</p>
        <?php endif; ?>
    </div>
</body>
</html>

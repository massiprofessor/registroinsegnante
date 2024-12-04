<?php
// Configurazione del database
$host = 'localhost';
$dbname = 'regsx_class';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recupera tutte le materie
    $stmt = $conn->query("SELECT * FROM materie ORDER BY ID_Materia DESC");
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elenco Materie</title>
    <!-- Aggiungi Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .materie-list {
            margin-top: 20px;
        }

        .materie-item {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .materie-item h5 {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Elenco Materie</h1>

        <div class="materie-list">
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="materie-item">
                    <h5><?= htmlspecialchars($row['Materia']) ?></h5>
                    <form action="elimina_materia.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id_materia" value="<?= $row['ID_Materia'] ?>">
                        <button type="submit" class="delete-btn">Elimina</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


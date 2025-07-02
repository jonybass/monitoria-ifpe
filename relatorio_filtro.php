<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include __DIR__ . '/conexao.php';

$usuario_monitor = $_SESSION['usuario'];

// Buscar matérias do monitor para o select
$stmt = $conn->prepare("SELECT id, nome FROM materias WHERE id_monitor = (SELECT id FROM monitores WHERE usuario = ?)");
$stmt->bind_param("s", $usuario_monitor);
$stmt->execute();
$result = $stmt->get_result();
$materias = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Filtro Relatório de Frequência</title>
</head>
<body>
 <style>
        body {
            font-family: Arial;
            background: #f0f0f0;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin: auto;
            box-shadow: 0 0 10px #ccc;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn {
            background-color: #006400;
            color: white;
            border: none;
            padding: 12px;
            margin-top: 20px;
            cursor: pointer;
            width: 100%;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #008000;
        }

        .mensagem {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }

        .voltar {
            text-align: center;
            margin-top: 20px;
        }

        .voltar a {
            text-decoration: none;
            color: #006400;
        }
    </style>
<h1>Gerar Relatório de Frequência</h1>

<form action="relatorio.php" method="GET">
    <label for="id_materia">Matéria:</label>
    <select name="id_materia" id="id_materia" required>
        <option value="">-- Selecione --</option>
        <?php foreach($materias as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label for="mes_ano">Mês/Ano (ex: 2025-07):</label>
    <input type="month" name="mes_ano" id="mes_ano" required>
    <br><br>

    
    <button type="submit" class="btn">Gerar Relatório</button>
</form>
<div class="voltar">
        <a href="painel_monitor.php">← Voltar ao Painel</a>
    </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include '../conexao.php';

// Busca o ID do monitor logado
$usuario = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome_materia'] ?? '';
    $turno = $_POST['turno'] ?? '';
    $local = $_POST['local'] ?? '';

    if ($nome && $turno && $local) {
        $stmt = $conn->prepare("INSERT INTO materias (id_monitor, nome, turno, local) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_monitor, $nome, $turno, $local);

        if ($stmt->execute()) {
            $mensagem = "✅ Matéria cadastrada com sucesso!";
        } else {
            $mensagem = "❌ Erro ao cadastrar: " . $conn->error;
        }

        $stmt->close();
    } else {
        $mensagem = "❗ Todos os campos são obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Matéria</title>
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
</head>
<body>

<div class="container">
    <h2>Cadastrar Nova Matéria</h2>

    <?php if ($mensagem): ?>
        <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="nome_materia">Nome da Matéria:</label>
        <input type="text" name="nome_materia" required>

        <label for="turno">Turno:</label>
        <select name="turno" required>
            <option value="">Selecione</option>
            <option value="Manhã">Manhã</option>
            <option value="Tarde">Tarde</option>
            <option value="Noite">Noite</option>
        </select>

        <label for="local">Local da Monitoria:</label>
        <select name="local" required>
            <option value="">Selecione</option>
            <option value="Sala IF">Sala no IF</option>
            <option value="Laboratório IF">Laboratório do IF</option>
            <option value="Auditório IF">Auditório do IF</option>
            <option value="Google Meet">Call (Google Meet)</option>
            <option value="Zoom">Call (Zoom)</option>
            <option value="Outro">Outro</option>
        </select>

        <button type="submit" class="btn">Cadastrar</button>
    </form>

    <div class="voltar">
        <a href="painel_monitor.php">← Voltar ao Painel</a>
    </div>
</div>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include  '../conexao.php';

// Pega o usuário logado do monitor
$usuario_monitor = $_SESSION['usuario'];

// Primeiro, pegar o ID do monitor pelo usuário
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario_monitor);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Monitor não encontrado.");
}
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];
$stmt->close();


// Agora buscar alunos que confirmaram presença nas monitorias do monitor
$sql = "
    SELECT DISTINCT a.id, a.usuario
    FROM alunos a
    JOIN confirmacoes c ON a.id = c.aluno_id
    JOIN horarios_monitoria h ON c.monitoria_id = h.id
    JOIN materias m ON h.id_materia = m.id
    WHERE m.id_monitor = ?
    ORDER BY a.usuario
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alunos da Monitoria</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background-color: #006400; color: white; }
    </style>
</head>
<body>
<style>
     .voltar {
            text-align: center;
            margin-top: 20px;
        }

        .voltar a {
            text-decoration: none;
            color: #006400;
        }
</style>
<h1>Alunos das suas Monitorias</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome do Aluno</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($aluno = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($aluno['id']) ?></td>
            <td><?= htmlspecialchars($aluno['usuario']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
    
</table>

<div class="voltar">
        <a href="painel_monitor.php">← Voltar ao Painel</a>
    </div>
</body>
</html>

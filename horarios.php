<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include 'conexao.php';

$usuario = $_SESSION['usuario'];
// Busca ID do monitor logado
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];

$mensagem = "";

// Ao enviar o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_materia = $_POST['id_materia'] ?? '';
    $dia = $_POST['dia_semana'] ?? '';
    $inicio = $_POST['horario_inicio'] ?? '';
    $fim = $_POST['horario_fim'] ?? '';

    if ($id_materia && $dia && $inicio && $fim) {
        $stmt = $conn->prepare("INSERT INTO horarios_monitoria (id_materia, dia_semana, horario_inicio, horario_fim) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_materia, $dia, $inicio, $fim);

        if ($stmt->execute()) {
            $mensagem = "✅ Horário cadastrado com sucesso!";
        } else {
            $mensagem = "❌ Erro ao cadastrar: " . $conn->error;
        }
        $stmt->close();
    } else {
        $mensagem = "❗ Todos os campos são obrigatórios.";
    }
}

// Busca matérias do monitor logado
$materias = [];
$stmt = $conn->prepare("SELECT id, nome FROM materias WHERE id_monitor = ?");
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $materias[] = $row;
}

// Listar horários cadastrados
$horarios = [];
$sql = "SELECT h.*, m.nome AS nome_materia
        FROM horarios_monitoria h
        JOIN materias m ON h.id_materia = m.id
        WHERE m.id_monitor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $horarios[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Horários de Monitoria</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { max-width: 700px; background: white; padding: 25px; border-radius: 10px; margin: auto; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; margin-bottom: 20px; }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; }
        .btn { background-color: #006400; color: white; border: none; padding: 12px; margin-top: 20px; cursor: pointer; width: 100%; border-radius: 6px; font-weight: bold; }
        .btn:hover { background-color: #008000; }
        .mensagem { text-align: center; margin-top: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #006400; color: white; }
        .voltar { text-align: center; margin-top: 20px; }
        .voltar a { text-decoration: none; color: #006400; }
    </style>
</head>
<body>

<div class="container">
    <h2>Gerenciar Horários</h2>

    <?php if ($mensagem): ?>
        <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="id_materia">Matéria:</label>
        <select name="id_materia" required>
            <option value="">Selecione</option>
            <?php foreach ($materias as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="dia_semana">Dia da Semana:</label>
        <select name="dia_semana" required>
            <option value="">Selecione</option>
            <option value="Segunda">Segunda-feira</option>
            <option value="Terça">Terça-feira</option>
            <option value="Quarta">Quarta-feira</option>
            <option value="Quinta">Quinta-feira</option>
            <option value="Sexta">Sexta-feira</option>
            <option value="Sábado">Sábado</option>
        </select>

        <label for="horario_inicio">Horário de Início:</label>
        <input type="time" name="horario_inicio" required>

        <label for="horario_fim">Horário de Término:</label>
        <input type="time" name="horario_fim" required>

        <button type="submit" class="btn">Cadastrar Horário</button>
    </form>

    <?php if (count($horarios) > 0): ?>
        <h3 style="margin-top: 40px;">Horários Cadastrados</h3>
        <table>
            <tr>
                <th>Matéria</th>
                <th>Dia</th>
                <th>Início</th>
                <th>Fim</th>
            </tr>
            <?php foreach ($horarios as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['nome_materia']) ?></td>
                    <td><?= $h['dia_semana'] ?></td>
                    <td><?= $h['horario_inicio'] ?></td>
                    <td><?= $h['horario_fim'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="voltar">
        <a href="painel_monitor.php">← Voltar ao Painel</a>
    </div>
</div>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include 'conexao.php';

$usuario = $_SESSION['usuario'];

// Buscar ID do monitor logado
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];

// Excluir matéria (se solicitado)
if (isset($_GET['excluir'])) {
    $idExcluir = intval($_GET['excluir']);
    
    // Remove a matéria e os horários vinculados
    $conn->query("DELETE FROM horarios_monitoria WHERE id_materia = $idExcluir");
    $conn->query("DELETE FROM materias WHERE id = $idExcluir AND id_monitor = $id_monitor");

    header("Location: minhas_monitorias.php");
    exit;
}

// Buscar matérias e horários
$stmt = $conn->prepare("
    SELECT m.id, m.nome, m.turno, m.local, h.dia_semana, h.horario_inicio, h.horario_fim
    FROM materias m
    LEFT JOIN horarios_monitoria h ON m.id = h.id_materia
    WHERE m.id_monitor = ?
    ORDER BY m.nome, FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo')
");
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();

$materias = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    if (!isset($materias[$id])) {
        $materias[$id] = [
            'nome' => $row['nome'],
            'turno' => $row['turno'],
            'local' => $row['local'],
            'horarios' => []
        ];
    }

    if ($row['dia_semana']) {
        $materias[$id]['horarios'][] = "{$row['dia_semana']} - " . substr($row['horario_inicio'], 0, 5) . " às " . substr($row['horario_fim'], 0, 5);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Monitorias</title>
    <style>
        body {
            font-family: Arial;
            max-width: 900px;
            margin: auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
        }
        .materia {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 0 8px #ccc;
        }
        .materia h2 {
            margin: 0;
            color: #006400;
        }
        .info {
            margin-top: 10px;
        }
        .horarios {
            margin-top: 10px;
            font-style: italic;
        }
        .botoes {
            margin-top: 15px;
        }
        .botoes a {
            text-decoration: none;
            padding: 8px 12px;
            margin-right: 10px;
            background-color: #006400;
            color: white;
            border-radius: 5px;
        }
        .botoes a.excluir {
            background-color: #8B0000;
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

<h1>Minhas Monitorias</h1>

<?php if (empty($materias)): ?>
    <p>❗ Nenhuma matéria cadastrada ainda.</p>
<?php else: ?>
    <?php foreach ($materias as $id => $m): ?>
        <div class="materia">
            <h2><?= htmlspecialchars($m['nome']) ?></h2>
            <div class="info">Turno: <strong><?= htmlspecialchars($m['turno']) ?></strong></div>
            <div class="info">Local: <strong><?= htmlspecialchars($m['local']) ?></strong></div>
            <?php if (!empty($m['horarios'])): ?>
                <div class="horarios">
                    <strong>Horários:</strong><br>
                    <?= implode('<br>', array_map('htmlspecialchars', $m['horarios'])) ?>
                </div>
            <?php else: ?>
                <div class="horarios"><em>Nenhum horário cadastrado.</em></div>
            <?php endif; ?>
            <div class="botoes">
                <a href="horarios.php?id=<?= $id ?>">✏️ Editar Horários</a>
                <a href="minhas_monitorias.php?excluir=<?= $id ?>" class="excluir" onclick="return confirm('Tem certeza que deseja excluir esta matéria?');">🗑️ Excluir</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="voltar">
    <a href="painel_monitor.php">← Voltar ao Painel</a>
</div>

</body>
</html>

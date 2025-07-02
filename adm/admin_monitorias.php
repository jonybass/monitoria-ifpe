<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'professor') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../conexao.php';

// Processar exclusão lógica
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);
    $stmt = $conn->prepare("UPDATE materias_monitoria SET ativo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Monitoria excluída com sucesso.";
    } else {
        $msg = "Erro ao excluir monitoria.";
    }
}

// Filtros
$filtro_materia = $_GET['filtro_materia'] ?? '';
$filtro_monitor = $_GET['filtro_monitor'] ?? '';
$filtro_dia = $_GET['filtro_dia'] ?? '';

// Query com join para pegar nome do monitor
$sql = "SELECT mm.*, mon.usuario AS monitor_nome 
        FROM materias_monitoria mm
        LEFT JOIN monitores mon ON mm.monitor_id = mon.id
        WHERE mm.ativo = 1";

$params = [];
$types = "";

if ($filtro_materia) {
    $sql .= " AND mm.materia LIKE ?";
    $params[] = "%$filtro_materia%";
    $types .= "s";
}

if ($filtro_monitor) {
    $sql .= " AND mon.usuario LIKE ?";
    $params[] = "%$filtro_monitor%";
    $types .= "s";
}

if ($filtro_dia) {
    $sql .= " AND mm.dia LIKE ?";
    $params[] = "%$filtro_dia%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$monitorias = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Administração de Monitorias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        h2 {
            color: #006400;
        }
        form input, form button {
            padding: 8px;
            margin: 5px;
            border-radius: 5px;
        }
        form button {
            background: #006400;
            color: white;
            border: none;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        th {
            background: #006400;
            color: white;
        }
        a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .editar {
            background: #28a745;
            color: white;
        }
        .excluir {
            background: #dc3545;
            color: white;
        }
        .mensagem {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Gerenciar Monitorias</h2>

<?php if (isset($msg)): ?>
    <p class="mensagem"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<form method="GET" action="">
    <input type="text" name="filtro_materia" placeholder="Filtrar por matéria" value="<?= htmlspecialchars($filtro_materia) ?>">
    <input type="text" name="filtro_monitor" placeholder="Filtrar por monitor" value="<?= htmlspecialchars($filtro_monitor) ?>">
    <input type="text" name="filtro_dia" placeholder="Filtrar por dia" value="<?= htmlspecialchars($filtro_dia) ?>">
    <button type="submit">Filtrar</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Matéria</th>
            <th>Monitor</th>
            <th>Dia</th>
            <th>Horário Início</th>
            <th>Horário Fim</th>
            <th>Local</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($monitorias)): ?>
            <tr><td colspan="8">Nenhuma monitoria encontrada.</td></tr>
        <?php else: ?>
            <?php foreach ($monitorias as $m): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= htmlspecialchars($m['materia']) ?></td>
                    <td><?= htmlspecialchars($m['monitor_nome']) ?></td>
                    <td><?= htmlspecialchars($m['dia']) ?></td>
                    <td><?= htmlspecialchars($m['horario_inicio']) ?></td>
                    <td><?= htmlspecialchars($m['horario_fim']) ?></td>
                    <td><?= htmlspecialchars($m['local']) ?></td>
                    <td>
                        <a class="editar" href="editar_monitoria.php?id=<?= $m['id'] ?>">Editar</a>
                        <a class="excluir" href="?excluir_id=<?= $m['id'] ?>" onclick="return confirm('Deseja realmente excluir esta monitoria?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>

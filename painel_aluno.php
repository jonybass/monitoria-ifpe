<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: index.php");
    exit;
}

include 'conexao.php';

$usuario = $_SESSION['usuario'];

// Buscar ID do aluno logado
$stmt = $conn->prepare("SELECT id FROM alunos WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$aluno = $result->fetch_assoc();
$aluno_id = $aluno['id'];
$stmt->close();

// Confirmar presença
$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['monitoria_id'])) {
    $monitoria_id = $_POST['monitoria_id'];

    // Verifica se já confirmou
    $stmt = $conn->prepare("SELECT id FROM confirmacoes WHERE aluno_id = ? AND monitoria_id = ?");
    $stmt->bind_param("ii", $aluno_id, $monitoria_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO confirmacoes (aluno_id, monitoria_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $aluno_id, $monitoria_id);
        $stmt->execute();
        $mensagem = "✅ Presença confirmada!";
    } else {
        $mensagem = "⚠️ Você já confirmou presença nesta monitoria.";
    }

    $stmt->close();
}

// Buscar lista de matérias para filtro
$materias = [];
$res = $conn->query("SELECT DISTINCT m.id, m.nome FROM materias m JOIN horarios_monitoria h ON h.id_materia = m.id");
while ($row = $res->fetch_assoc()) {
    $materias[] = $row;
}

// Aplicar filtro
$filtro_materia = $_GET['id_materia'] ?? '';

// Buscar horários disponíveis
$sql = "
    SELECT h.id, m.nome AS materia, h.dia_semana, h.horario_inicio, h.horario_fim, m.local
    FROM horarios_monitoria h
    JOIN materias m ON h.id_materia = m.id
";
if ($filtro_materia) {
    $sql .= " WHERE m.id = " . intval($filtro_materia);
}
$sql .= " ORDER BY h.dia_semana, h.horario_inicio";

$horarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Aluno</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        h1 { text-align: center; }
        .mensagem { text-align: center; margin-top: 10px; color: green; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #006400; color: white; }
        form { display: inline; }
        .filtro { margin-bottom: 20px; text-align: center; }
        select { padding: 6px; }
        button { background: #006400; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #008000; }
        .logout { position: absolute; top: 20px; right: 30px; }
        .logout a { text-decoration: none; color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="logout">
        <a href="logout.php">Sair</a>
    </div>
    <h1>Monitorias Disponíveis</h1>

    <?php if ($mensagem): ?>
        <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <div class="filtro">
        <form method="GET">
            <label for="id_materia">Filtrar por Matéria:</label>
            <select name="id_materia" id="id_materia" onchange="this.form.submit()">
                <option value="">Todas</option>
                <?php foreach ($materias as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= ($filtro_materia == $m['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <table>
        <tr>
            <th>Matéria</th>
            <th>Dia</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Local</th>
            <th>Presença</th>
        </tr>
        <?php foreach ($horarios as $h): ?>
    <?php
        // Verifica se a presença já foi confirmada
        $stmt = $conn->prepare("SELECT id FROM confirmacoes WHERE aluno_id = ? AND monitoria_id = ?");
        $stmt->bind_param("ii", $aluno_id, $h['id']);
        $stmt->execute();
        $stmt->store_result();
        $ja_confirmado = ($stmt->num_rows > 0);
        $stmt->close();
    ?>
    <tr>
        <td><?= htmlspecialchars($h['materia']) ?></td>
        <td><?= htmlspecialchars($h['dia_semana']) ?></td>
        <td><?= substr($h['horario_inicio'], 0, 5) ?></td>
        <td><?= substr($h['horario_fim'], 0, 5) ?></td>
        <td><?= htmlspecialchars($h['local'] ?? '---') ?></td>
        <td>
            <?php if ($ja_confirmado): ?>
                <span style="color: green; font-weight: bold;">Presença confirmada ✅</span>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="monitoria_id" value="<?= $h['id'] ?>">
                    <button type="submit">Confirmar</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>

    </table>
</div>

</body>
</html>

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

// Mensagem para feedback
$mensagem = "";

// Processar confirmação ou cancelamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['monitoria_id'], $_POST['acao'])) {
    $monitoria_id = (int) $_POST['monitoria_id'];
    $acao = $_POST['acao'];

    if ($acao === 'confirmar') {
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
    } elseif ($acao === 'cancelar') {
        $stmt = $conn->prepare("DELETE FROM confirmacoes WHERE aluno_id = ? AND monitoria_id = ?");
        $stmt->bind_param("ii", $aluno_id, $monitoria_id);
        $stmt->execute();
        $stmt->close();
        $mensagem = "❌ Presença cancelada com sucesso.";
    }
}

// Buscar matérias para filtro
$materias = [];
$res = $conn->query("SELECT DISTINCT m.id, m.nome FROM materias m JOIN horarios_monitoria h ON h.id_materia = m.id");
while ($row = $res->fetch_assoc()) {
    $materias[] = $row;
}

// Aplicar filtro
$filtro_materia = $_GET['id_materia'] ?? '';

$sql = "
    SELECT h.id, m.nome AS materia, h.dia_semana, h.horario_inicio, h.horario_fim, m.local
    FROM horarios_monitoria h
    JOIN materias m ON h.id_materia = m.id
";

if ($filtro_materia) {
    // Buscar o nome da matéria selecionada pelo ID
    $stmtMat = $conn->prepare("SELECT nome FROM materias WHERE id = ?");
    $stmtMat->bind_param("i", $filtro_materia);
    $stmtMat->execute();
    $resMat = $stmtMat->get_result();
    $matSelecionada = $resMat->fetch_assoc();
    $stmtMat->close();

    if ($matSelecionada) {
        $nomeMat = $matSelecionada['nome'];
        // Alterar filtro para usar o nome da matéria, mostrando todas as com nome igual
        $sql .= " WHERE m.nome = ?";
        $stmtHorarios = $conn->prepare($sql . " ORDER BY h.dia_semana, h.horario_inicio");
        $stmtHorarios->bind_param("s", $nomeMat);
        $stmtHorarios->execute();
        $resultHorarios = $stmtHorarios->get_result();
        $horarios = $resultHorarios->fetch_all(MYSQLI_ASSOC);
        $stmtHorarios->close();
    } else {
        // Se não encontrar a matéria, buscar todos
        $sql .= " ORDER BY h.dia_semana, h.horario_inicio";
        $horarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
} else {
    // Sem filtro, buscar todos
    $sql .= " ORDER BY h.dia_semana, h.horario_inicio";
    $horarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Aluno</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        h1 { text-align: center; }
        .mensagem { text-align: center; margin: 10px 0; color: green; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #006400; color: white; }
        button { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; }
        .tabs { text-align: center; margin-top: 20px; }
        .tab-btn { background-color: #eee; padding: 10px 20px; margin: 5px; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .tab-btn.ativo { background-color: #006400; color: white; }
        .aba { display: none; }
        .logout { text-align: right; margin-bottom: 10px; }
        .logout a { color: red; font-weight: bold; text-decoration: none; }
        .filtro { margin: 15px 0; text-align: center; }
    </style>
    <script>
        function mostrarAba(id) {
            document.querySelectorAll('.aba').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('ativo'));
            document.getElementById(id).style.display = 'block';
            document.querySelector(`[onclick="mostrarAba('${id}')"]`).classList.add('ativo');
        }
        window.onload = () => mostrarAba('todas');
    </script>
</head>
<body>
<div class="container">
    <div class="logout">
        <a href="logout.php">Sair</a>
    </div>
    <h1>Painel do Aluno</h1>

    <?php if ($mensagem): ?>
        <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-btn" onclick="mostrarAba('todas')">📋 Todas as Monitorias</button>
        <button class="tab-btn" onclick="mostrarAba('confirmadas')">✅ Monitorias Confirmadas</button>
    </div>

    <!-- Aba: Todas as Monitorias -->
    <div id="todas" class="aba">
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
                <th>Ação</th>
            </tr>
            <?php foreach ($horarios as $h): ?>
                <?php
                $stmt = $conn->prepare("SELECT id FROM confirmacoes WHERE aluno_id = ? AND monitoria_id = ?");
                $stmt->bind_param("ii", $aluno_id, $h['id']);
                $stmt->execute();
                $stmt->store_result();
                $ja_confirmado = $stmt->num_rows > 0;
                $stmt->close();
                ?>
                <tr>
                    <td><?= htmlspecialchars($h['materia']) ?></td>
                    <td><?= htmlspecialchars($h['dia_semana']) ?></td>
                    <td><?= substr($h['horario_inicio'], 0, 5) ?></td>
                    <td><?= substr($h['horario_fim'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($h['local']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="monitoria_id" value="<?= $h['id'] ?>">
                            <input type="hidden" name="acao" value="<?= $ja_confirmado ? 'cancelar' : 'confirmar' ?>">
                            <button type="submit" style="background-color: <?= $ja_confirmado ? '#B22222' : '#006400' ?>; color: white;">
                                <?= $ja_confirmado ? 'Cancelar' : 'Confirmar' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Aba: Confirmadas -->
    <div id="confirmadas" class="aba">
        <h2>Monitorias Confirmadas</h2>
        <table>
            <tr>
                <th>Matéria</th>
                <th>Dia</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Local</th>
                <th>Ação</th>
            </tr>
            <?php
            $sqlConfirmadas = "
                SELECT h.id, m.nome AS materia, h.dia_semana, h.horario_inicio, h.horario_fim, m.local
                FROM confirmacoes c
                JOIN horarios_monitoria h ON c.monitoria_id = h.id
                JOIN materias m ON h.id_materia = m.id
                WHERE c.aluno_id = ?
                ORDER BY h.dia_semana, h.horario_inicio
            ";
            $stmt = $conn->prepare($sqlConfirmadas);
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $confirmadas = $stmt->get_result();
            while ($c = $confirmadas->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($c['materia']) ?></td>
                    <td><?= htmlspecialchars($c['dia_semana']) ?></td>
                    <td><?= substr($c['horario_inicio'], 0, 5) ?></td>
                    <td><?= substr($c['horario_fim'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($c['local']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Deseja cancelar sua presença?');">
                            <input type="hidden" name="monitoria_id" value="<?= $c['id'] ?>">
                            <input type="hidden" name="acao" value="cancelar">
                            <button type="submit" style="background-color: #B22222; color: white;">Cancelar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile;
            $stmt->close();
            ?>
        </table>
    </div>
</div>
</body>
</html>

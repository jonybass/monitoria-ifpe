<?php
session_start();
$aluno_id = $_SESSION['aluno_id'] ?? null;
if (!$aluno_id) {
    header("Location: login.php");
    exit;
}

require 'conexao.php'; // arquivo com conexão ao banco $conn

// Filtrar matéria
$filtro_materia = $_GET['materia'] ?? '';

// Buscar matérias disponíveis com filtro
$sql = "SELECT * FROM materias_monitoria";
$params = [];

if ($filtro_materia) {
    $sql .= " WHERE materia LIKE ?";
    $params[] = "%$filtro_materia%";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param("s", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$monitorias = $result->fetch_all(MYSQLI_ASSOC);

// Processar confirmação enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $confirmados = $_POST['confirmados'] ?? [];
    foreach ($confirmados as $monitoria_id) {
        // Verifica se já existe confirmação para evitar duplicata
        $check = $conn->prepare("SELECT id FROM confirmacoes WHERE aluno_id = ? AND monitoria_id = ?");
        $check->bind_param("ii", $aluno_id, $monitoria_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            $ins = $conn->prepare("INSERT INTO confirmacoes (aluno_id, monitoria_id) VALUES (?, ?)");
            $ins->bind_param("ii", $aluno_id, $monitoria_id);
            $ins->execute();
        }
    }
    header("Location: aluno_monitorias.php?msg=confirmado");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monitorias Disponíveis</title>
</head>
<body>
    <h1>Monitorias Disponíveis</h1>

    <form method="GET">
        <input type="text" name="materia" placeholder="Filtrar por matéria" value="<?= htmlspecialchars($filtro_materia) ?>">
        <button type="submit">Filtrar</button>
    </form>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'confirmado'): ?>
        <p style="color:green;">Confirmação registrada com sucesso!</p>
    <?php endif; ?>

    <form method="POST">
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Confirmar</th>
                    <th>Matéria</th>
                    <th>Dia</th>
                    <th>Horário</th>
                    <th>Local</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monitorias as $m): ?>
                <tr>
                    <td><input type="checkbox" name="confirmados[]" value="<?= $m['id'] ?>"></td>
                    <td><?= htmlspecialchars($m['materia']) ?></td>
                    <td><?= htmlspecialchars($m['dia']) ?></td>
                    <td><?= htmlspecialchars($m['horario_inicio']) ?> - <?= htmlspecialchars($m['horario_fim']) ?></td>
                    <td><?= htmlspecialchars($m['local']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($monitorias) === 0): ?>
                <tr><td colspan="5">Nenhuma monitoria encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <button type="submit" name="confirmar">Confirmar Presença</button>
    </form>
</body>
</html>

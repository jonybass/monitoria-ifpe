<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'professor') {
    header("Location: ../index.php");
    exit;
}

// Ajusta o caminho absoluto para o arquivo de conexão
require_once __DIR__ . '/../conexao.php';

// Filtros
$filtro_materia = $_GET['filtro_materia'] ?? '';
$filtro_aluno = $_GET['filtro_aluno'] ?? '';

// Monta query base com joins para trazer as informações completas
$sql = "SELECT c.id, a.usuario AS aluno_nome, m.materia, m.dia, m.horario_inicio, m.horario_fim, c.confirmado_em
        FROM confirmacoes c
        INNER JOIN alunos a ON c.aluno_id = a.id
        INNER JOIN materias_monitoria m ON c.monitoria_id = m.id
        WHERE 1=1";

$params = [];
$types = "";

if ($filtro_materia) {
    $sql .= " AND m.materia LIKE ?";
    $params[] = "%$filtro_materia%";
    $types .= "s";
}

if ($filtro_aluno) {
    $sql .= " AND a.usuario LIKE ?";
    $params[] = "%$filtro_aluno%";
    $types .= "s";
}

$sql .= " ORDER BY c.confirmado_em DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$confirmacoes = $result->fetch_all(MYSQLI_ASSOC);

?>

<h2>Confirmações de Presença</h2>

<form method="GET" action="">
    <input type="text" name="filtro_materia" placeholder="Filtrar por matéria" value="<?= htmlspecialchars($filtro_materia) ?>">
    <input type="text" name="filtro_aluno" placeholder="Filtrar por aluno" value="<?= htmlspecialchars($filtro_aluno) ?>">
    <button type="submit">Filtrar</button>
</form>

<table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%; background:#fff;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Aluno</th>
            <th>Matéria</th>
            <th>Dia</th>
            <th>Horário</th>
            <th>Confirmado em</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($confirmacoes) === 0): ?>
            <tr><td colspan="6">Nenhuma confirmação encontrada.</td></tr>
        <?php else: ?>
            <?php foreach ($confirmacoes as $conf): ?>
                <tr>
                    <td><?= $conf['id'] ?></td>
                    <td><?= htmlspecialchars($conf['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($conf['materia']) ?></td>
                    <td><?= htmlspecialchars($conf['dia']) ?></td>
                    <td><?= htmlspecialchars($conf['horario_inicio']) ?> - <?= htmlspecialchars($conf['horario_fim']) ?></td>
                    <td><?= htmlspecialchars($conf['confirmado_em']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

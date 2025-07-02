<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'professor') {
    header("Location: ../index.php");
    exit;
}

// Use caminho absoluto para evitar erro de caminho
require_once __DIR__ . '/../conexao.php';

$filtro_materia = $_GET['filtro_materia'] ?? '';
$filtro_monitor = $_GET['filtro_monitor'] ?? '';
$filtro_mes = $_GET['filtro_mes'] ?? date('m');
$filtro_ano = $_GET['filtro_ano'] ?? date('Y');

$relatorio = [];
$msg = '';

// Query base com JOIN para pegar nome do monitor
$sql = "SELECT mm.*, mon.usuario AS monitor_nome FROM materias_monitoria mm
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
    $sql .= " AND mon.nome LIKE ?";
    $params[] = "%$filtro_monitor%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$monitorias = $result->fetch_all(MYSQLI_ASSOC);

function buscarConfirmacoes($conn, $monitoria_id, $mes, $ano) {
    $sql = "SELECT a.nome AS aluno_nome, c.confirmado_em
            FROM confirmacoes c
            INNER JOIN alunos a ON c.aluno_id = a.id AND a.ativo = 1
            WHERE c.monitoria_id = ? AND MONTH(c.confirmado_em) = ? AND YEAR(c.confirmado_em) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $monitoria_id, $mes, $ano);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Exportar CSV
if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_monitorias.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Matéria', 'Monitor', 'Dia', 'Horário Início', 'Horário Fim', 'Local', 'Aluno', 'Data Confirmação']);

    foreach ($monitorias as $m) {
        $conf = buscarConfirmacoes($conn, $m['id'], $filtro_mes, $filtro_ano);
        foreach ($conf as $c) {
            fputcsv($output, [
                $m['materia'],
                $m['monitor_nome'],
                $m['dia'],
                $m['horario_inicio'],
                $m['horario_fim'],
                $m['local'],
                $c['aluno_nome'],
                $c['confirmado_em']
            ]);
        }
    }
    fclose($output);
    exit;
}
?>

<h2>Gerar Relatório de Monitorias</h2>

<form method="GET" action="">
    <input type="text" name="filtro_materia" placeholder="Filtrar por matéria" value="<?= htmlspecialchars($filtro_materia) ?>">
    <input type="text" name="filtro_monitor" placeholder="Filtrar por monitor" value="<?= htmlspecialchars($filtro_monitor) ?>">
    <label>Mês:
        <select name="filtro_mes">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($m == intval($filtro_mes)) ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
            <?php endfor; ?>
        </select>
    </label>
    <label>Ano:
        <select name="filtro_ano">
            <?php 
            $anoAtual = date('Y');
            for ($y = $anoAtual; $y >= $anoAtual - 5; $y--): ?>
                <option value="<?= $y ?>" <?= ($y == intval($filtro_ano)) ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </label>
    <button type="submit">Gerar</button>
    <button type="submit" name="exportar" value="csv">Exportar CSV</button>
</form>

<?php if ($monitorias): ?>
    <table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%; background:#fff;">
        <thead>
            <tr>
                <th>Matéria</th>
                <th>Monitor</th>
                <th>Dia</th>
                <th>Horário Início</th>
                <th>Horário Fim</th>
                <th>Local</th>
                <th>Alunos Confirmados</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($monitorias as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['materia']) ?></td>
                    <td><?= htmlspecialchars($m['monitor_nome']) ?></td>
                    <td><?= htmlspecialchars($m['dia']) ?></td>
                    <td><?= htmlspecialchars($m['horario_inicio']) ?></td>
                    <td><?= htmlspecialchars($m['horario_fim']) ?></td>
                    <td><?= htmlspecialchars($m['local']) ?></td>
                    <td>
                        <?php
                        $conf = buscarConfirmacoes($conn, $m['id'], $filtro_mes, $filtro_ano);
                        if ($conf) {
                            echo "<ul style='padding-left:20px; margin:0;'>";
                            foreach ($conf as $c) {
                                echo "<li>" . htmlspecialchars($c['aluno_nome']) . " (Confirmado em " . htmlspecialchars($c['confirmado_em']) . ")</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "Nenhum aluno confirmado neste período.";
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhuma monitoria encontrada com esses filtros.</p>
<?php endif; ?>

<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
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
    $stmt = $conn->prepare("UPDATE alunos SET ativo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Aluno excluído (inativado) com sucesso.";
    } else {
        $msg = "Erro ao excluir aluno.";
    }
    $stmt->close();
}

// Filtro por nome de usuário
$filtro_nome = $_GET['filtro_nome'] ?? '';

$sql = "SELECT * FROM alunos WHERE ativo = 1";
$params = [];
$types = "";

if ($filtro_nome) {
    $sql .= " AND usuario LIKE ?";
    $params[] = "%$filtro_nome%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$alunos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<h2>Gerenciar Alunos</h2>

<?php if (isset($msg)): ?>
    <p style="color: green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<form method="GET" action="">
    <input type="text" name="filtro_nome" placeholder="Filtrar por nome de usuário" value="<?= htmlspecialchars($filtro_nome) ?>">
    <button type="submit">Filtrar</button>
</form>

<table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%; background:#fff;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuário</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($alunos) === 0): ?>
            <tr><td colspan="3">Nenhum aluno encontrado.</td></tr>
        <?php else: ?>
            <?php foreach ($alunos as $aluno): ?>
                <tr>
                    <td><?= $aluno['id'] ?></td>
                    <td><?= htmlspecialchars($aluno['usuario']) ?></td>
                    <td>
                        <a href="editar_aluno.php?id=<?= $aluno['id'] ?>" style="background:#28a745; color:#fff; padding:5px 10px; text-decoration:none; border-radius:3px;">Editar</a>
                        <a href="?excluir_id=<?= $aluno['id'] ?>" onclick="return confirm('Deseja realmente excluir este aluno?');" style="background:#dc3545; color:#fff; padding:5px 10px; text-decoration:none; border-radius:3px;">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

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
    $stmt = $conn->prepare("UPDATE alunos SET ativo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: /monitoria-ifpe/painel_professor.php?pagina=alunos&msg=sucesso");
        exit;
    } else {
        $msg = "Erro ao excluir aluno.";
    }
}

// Filtro por nome
$filtro_nome = $_GET['filtro_nome'] ?? '';

$sql = "SELECT * FROM alunos WHERE ativo = 1";
$params = [];
$types = '';

if ($filtro_nome) {
    $sql .= " AND usuario LIKE ?";
    $params[] = "%$filtro_nome%";
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$alunos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Alunos</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        table { border-collapse: collapse; width: 100%; background: white; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #006400; color: white; }
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }
        .btn-edit { background-color: #28a745; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
        .filtro { margin-top: 15px; }
        input[type="text"] { padding: 6px; width: 250px; }
        button { padding: 6px 12px; background-color: #006400; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #008000; }
    </style>
</head>
<body>

<h2>Gerenciar Alunos</h2>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
    <p style="color: green;">Aluno excluído com sucesso.</p>
<?php elseif (isset($msg)): ?>
    <p style="color: red;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<form method="GET" class="filtro" action="/monitoria/painel_professor.php">
    <input type="hidden" name="pagina" value="alunos">
    <input type="text" name="filtro_nome" placeholder="Filtrar por nome de usuário" value="<?= htmlspecialchars($filtro_nome) ?>">
    <button type="submit">Filtrar</button>
</form>

<table>
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
                        <a href="/monitoria-ifpe/editar/editar_aluno.php?id=<?= $aluno['id'] ?>" class="btn-edit">Editar</a>
                        <a href="/monitoria-ifpe/adm/admin_alunos.php?pagina=alunos&excluir_id=<?= $aluno['id'] ?>"
                           onclick="return confirm('Deseja realmente excluir este aluno?');"
                           class="btn-delete">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>

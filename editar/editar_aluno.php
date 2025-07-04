<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'professor') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) {
    header("Location: ../painel_professor.php?pagina=alunos");
    exit;
}

$id = intval($_GET['id']);
$msg = "";

// EXCLUIR CONFIRMAÇÃO (se veio parâmetro)
if (isset($_GET['excluir_confirmacao_id'])) {
    $confirmacao_id = intval($_GET['excluir_confirmacao_id']);
    $stmt = $conn->prepare("DELETE FROM confirmacoes WHERE id = ? AND aluno_id = ?");
    $stmt->bind_param("ii", $confirmacao_id, $id);
    if ($stmt->execute()) {
        $msg = "Confirmação excluída com sucesso.";
    } else {
        $msg = "Erro ao excluir confirmação.";
    }
}

// Buscar dados do aluno
$stmt = $conn->prepare("SELECT usuario FROM alunos WHERE id = ? AND ativo = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$aluno = $result->fetch_assoc();

if (!$aluno) {
    header("Location: ../painel_professor.php?pagina=alunos&msg=aluno_nao_encontrado");
    exit;
}

// Atualizar nome e senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario)) {
        $msg = "O campo usuário é obrigatório.";
    } else {
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE alunos SET usuario = ?, senha = ? WHERE id = ?");
            $stmt->bind_param("ssi", $usuario, $senha_hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE alunos SET usuario = ? WHERE id = ?");
            $stmt->bind_param("si", $usuario, $id);
        }
        if ($stmt->execute()) {
            $msg = "Dados atualizados com sucesso.";
            $aluno['usuario'] = $usuario; // Atualiza nome na tela
        } else {
            $msg = "Erro ao atualizar aluno. Talvez o usuário já exista.";
        }
    }
}

// Buscar confirmações do aluno (com nome da monitoria)
$sql = "
    SELECT c.id AS confirmacao_id, m.nome AS materia_nome, h.dia_semana, h.horario_inicio, h.horario_fim
    FROM confirmacoes c
    JOIN horarios_monitoria h ON c.monitoria_id = h.id
    JOIN materias m ON h.id_materia = m.id
    WHERE c.aluno_id = ?
    ORDER BY c.confirmado_em DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$confirmacoes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Editar Aluno</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        form { background: white; padding: 20px; max-width: 400px; margin: auto; border-radius: 5px; }
        label { display: block; margin-top: 10px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; background: #006400; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #008000; }
        .msg { color: green; margin-top: 10px; text-align: center; }
        a { display: block; margin-top: 15px; text-align: center; text-decoration: none; color: #006400; }
        table { margin: 20px auto; border-collapse: collapse; width: 90%; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #006400; color: white; }
        .btn-delete { background: #dc3545; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; }
        .btn-delete:hover { background: #b3242f; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Editar Aluno</h2>

<?php if ($msg): ?>
    <p class="msg"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<form method="POST">
    <label for="usuario">Usuário:</label>
    <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($aluno['usuario']) ?>" required>

    <label for="senha">Senha (deixe em branco para manter a atual):</label>
    <input type="password" id="senha" name="senha" placeholder="Nova senha">

    <button type="submit">Salvar Alterações</button>
</form>

<h3 style="text-align:center; margin-top:40px;">Confirmações de Presença</h3>

<?php if (count($confirmacoes) === 0): ?>
    <p style="text-align:center;">Nenhuma confirmação encontrada.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Matéria</th>
                <th>Dia</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($confirmacoes as $conf): ?>
                <tr>
                    <td><?= htmlspecialchars($conf['materia_nome']) ?></td>
                    <td><?= htmlspecialchars($conf['dia_semana']) ?></td>
                    <td><?= substr($conf['horario_inicio'], 0, 5) ?></td>
                    <td><?= substr($conf['horario_fim'], 0, 5) ?></td>
                    <td>
                        <a href="?id=<?= $id ?>&excluir_confirmacao_id=<?= $conf['confirmacao_id'] ?>" class="btn-delete" onclick="return confirm('Deseja excluir essa confirmação?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="../painel_professor.php?pagina=alunos">Voltar para lista de alunos</a>

</body>
</html>

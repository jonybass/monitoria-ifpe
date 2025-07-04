<?php
include __DIR__ . '/conexao.php';

$usuario = $_POST['usuario'] ?? '';
$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';
$tipo = $_POST['tipo_usuario'] ?? '';

if (!$usuario || !$login || !$senha || !$tipo) {
    die("Todos os campos são obrigatórios.");
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

switch ($tipo) {
    case 'aluno':
        $sql = "INSERT INTO alunos (usuario, login, senha) VALUES (?, ?, ?)";
        break;
    case 'monitor':
        $sql = "INSERT INTO monitores (usuario, login, senha) VALUES (?, ?, ?)";
        break;
    case 'professor':
        $sql = "INSERT INTO professores (usuario, login, senha) VALUES (?, ?, ?)";
        break;
    default:
        die("Tipo de usuário inválido.");
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da query: " . $conn->error);
}

$stmt->bind_param("sss", $usuario, $login, $senhaHash);

if ($stmt->execute()) {
    echo "Usuário cadastrado com sucesso. <a href='index.php'>Voltar</a>";
} else {
    echo "Erro: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

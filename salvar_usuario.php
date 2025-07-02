<?php
include __DIR__ . '/conexao.php';


$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';
$tipo = $_POST['tipo_usuario'] ?? '';

if (!$usuario || !$senha || !$tipo) {
    die("Todos os campos são obrigatórios.");
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

switch ($tipo) {
    case 'aluno':
        $sql = "INSERT INTO alunos (usuario, senha) VALUES (?, ?)";
        break;
    case 'monitor':
        $sql = "INSERT INTO monitores (usuario, senha) VALUES (?, ?)";
        break;
    case 'professor':
        $sql = "INSERT INTO professores (usuario, senha) VALUES (?, ?)";
        break;
    default:
        die("Tipo de usuário inválido.");
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $senhaHash);

if ($stmt->execute()) {
    echo "Usuário cadastrado com sucesso. <a href='index.php'>Voltar</a>";
} else {
    echo "Erro: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

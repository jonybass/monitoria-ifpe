<?php
session_start();
include __DIR__ . '/conexao.php';

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';
$tipo = $_POST['tipo_usuario'] ?? '';

if (!$usuario || !$senha || !$tipo) {
    mostrarMensagem("❗ Todos os campos são obrigatórios.");
    exit;
}

switch ($tipo) {
    case 'aluno':
        $sql = "SELECT * FROM alunos WHERE usuario = ?";
        break;
    case 'monitor':
        $sql = "SELECT * FROM monitores WHERE usuario = ?";
        break;
    case 'professor':
        $sql = "SELECT * FROM professores WHERE usuario = ?";
        break;
    default:
        mostrarMensagem("❌ Tipo de usuário inválido.");
        exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($senha, $user['senha'])) {
        $_SESSION['usuario'] = $usuario;
        $_SESSION['tipo'] = $tipo;

        // Redireciona
        switch ($tipo) {
            case 'aluno':
                header("Location: painel_aluno.php");
                break;
            case 'monitor':
                header("Location: painel_monitor.php");
                break;
            case 'professor':
                header("Location: painel_professor.php");
                break;
        }
        exit;
    } else {
        mostrarMensagem(" Senha incorreta.");
    }
} else {
    mostrarMensagem(" Usuário não encontrado.");
}

$stmt->close();
$conn->close();

// Função para exibir mensagem com estilo
function mostrarMensagem($mensagem) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Erro de Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .mensagem {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            text-align: center;
        }
        .mensagem p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #c00;
        }
        .mensagem a {
            text-decoration: none;
            background: #006400;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .mensagem a:hover {
            background: #008000;
        }
    </style>
</head>
<body>
    <div class="mensagem">
        <p>{$mensagem}</p>
        <a href="index.php">← Voltar para o login</a>
    </div>
</body>
</html>
HTML;
}
?>

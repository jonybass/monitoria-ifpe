<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include '../conexao.php';

$usuario = $_SESSION['usuario'];

// Buscar ID do monitor logado
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];

// Criar pasta se não existir
$uploadDir = __DIR__ . '/arquivos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$mensagem = "";

// Buscar matérias do monitor
$stmt = $conn->prepare("SELECT id, nome FROM materias WHERE id_monitor = ?");
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
$materias = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Upload de arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_materia = $_POST['id_materia'] ?? '';
    $arquivo = $_FILES['arquivo'] ?? null;

    if ($id_materia && $arquivo && $arquivo['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($arquivo['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $permitidos = ['pdf', 'docx', 'pptx', 'zip'];

        if (!in_array($ext, $permitidos)) {
            $mensagem = "❌ Tipo de arquivo não permitido.";
        } else {
            $nomeUnico = uniqid('arquivo_') . '.' . $ext;
            $destino = $uploadDir . $nomeUnico;

            if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                $stmt = $conn->prepare("INSERT INTO arquivos (id_materia, id_monitor, nome_arquivo, caminho_arquivo) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $id_materia, $id_monitor, $fileName, $nomeUnico);
                if ($stmt->execute()) {
                    $mensagem = "✅ Arquivo enviado com sucesso!";
                } else {
                    $mensagem = "❌ Erro ao salvar no banco.";
                }
            } else {
                $mensagem = "❌ Erro ao mover o arquivo.";
            }
        }
    } else {
        $mensagem = "❗ Preencha todos os campos e envie um arquivo válido.";
    }
}

// Listar arquivos enviados
$stmt = $conn->prepare("
    SELECT a.*, m.nome AS materia_nome
    FROM arquivos a
    JOIN materias m ON a.id_materia = m.id
    WHERE a.id_monitor = ?
    ORDER BY a.data_envio DESC
");
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
$arquivos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Arquivos de Apoio</title>
    <style>
        body {
            font-family: Arial;
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
        }

        .mensagem {
            margin-top: 15px;
            font-weight: bold;
            color: green;
        }

        form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        select,
        input[type="file"] {
            width: 100%;
            margin-top: 5px;
            padding: 6px;
        }

        button {
            margin-top: 15px;
            padding: 10px;
            background-color: #006400;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #008000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #006400;
            color: white;
        }

        a.download {
            color: #006400;
            text-decoration: underline;
        }

        .btn {
            background-color: #006400;
            color: white;
            border: none;
            padding: 12px;
            margin-top: 20px;
            cursor: pointer;
            width: 100%;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #008000;
        }

        .voltar {
            text-align: center;
            margin-top: 20px;
        }

        .voltar a {
            text-decoration: none;
            color: #006400;
        }
    </style>
</head>

<body>

    <h1>Arquivos de Apoio</h1>

    <?php if ($mensagem): ?>
        <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="id_materia">Matéria:</label>
        <select name="id_materia" id="id_materia" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($materias as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="arquivo">Selecione o arquivo:</label>
<input type="file" name="arquivo" id="arquivo" accept=".pdf,.docx,.pptx,.zip" required>


        <button type="submit" class="">Enviar Arquivo</button>
    </form>

    <?php if (count($arquivos) > 0): ?>
        <h2 style="margin-top: 40px;">Arquivos Enviados</h2>
        <table>
            <tr>
                <th>Matéria</th>
                <th>Arquivo</th>
                <th>Data</th>
                <th>Download</th>
            </tr>
            <?php foreach ($arquivos as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['materia_nome']) ?></td>
                    <td><?= htmlspecialchars($a['nome_arquivo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($a['data_envio'])) ?></td>
                    <td><a class="download" href="arquivos/<?= htmlspecialchars($a['caminho_arquivo']) ?>" target="_blank">🔽
                            Baixar</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <div class="voltar">
        <a href="painel_monitor.php">← Voltar ao Painel</a>
    </div>

</body>

</html>
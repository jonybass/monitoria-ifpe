<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

include 'conexao.php';

$usuario = $_SESSION['usuario'];

// Busca id do monitor logado
$stmt = $conn->prepare("SELECT id FROM monitores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$monitor = $result->fetch_assoc();
$id_monitor = $monitor['id'];

// Pasta onde as fotos serão salvas
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755);
}

$mensagem = "";

// Buscar matérias do monitor para selecionar na hora do upload
$stmt = $conn->prepare("SELECT id, nome FROM materias WHERE id_monitor = ?");
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
$materias = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Upload da foto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_materia = $_POST['id_materia'] ?? '';
    if (!$id_materia) {
        $mensagem = "Selecione a matéria.";
    } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] != UPLOAD_ERR_OK) {
        $mensagem = "Erro no upload da foto.";
    } else {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = basename($_FILES['foto']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExt, $allowedExts)) {
            $mensagem = "Formato inválido. Aceito: jpg, jpeg, png, gif.";
        } else {
            // Nome único para evitar sobrescrever arquivos
            $newFileName = uniqid('foto_') . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Salvar no banco usando os nomes corretos das colunas
                $stmt = $conn->prepare("INSERT INTO fotos (id_materia, id_monitor, caminho_foto, data_envio) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iis", $id_materia, $id_monitor, $newFileName);


                if ($stmt->execute()) {
                    $mensagem = "Foto enviada com sucesso!";
                } else {
                    $mensagem = "Erro ao salvar no banco: " . $conn->error;
                }
                $stmt->close();
            } else {
                $mensagem = "Erro ao mover o arquivo.";
            }
        }
    }
}

// Buscar fotos enviadas pelo monitor
$stmt = $conn->prepare("
    SELECT f.*, m.nome AS materia_nome 
    FROM fotos f 
    JOIN materias m ON f.id_materia = m.id 
    WHERE f.id_monitor = ? 
    ORDER BY f.data_envio DESC
");
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
$fotos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Evidências em Fotos</title>
<style>
    body { font-family: Arial; max-width: 900px; margin: auto; padding: 20px; }
    h1 { text-align: center; }
    form { background: #f9f9f9; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
    label { display: block; margin-top: 10px; font-weight: bold; }
    select, input[type="file"] { width: 100%; margin-top: 5px; }
    button { margin-top: 15px; padding: 10px; background-color: #006400; color: white; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background-color: #008000; }
    .mensagem { margin-top: 15px; font-weight: bold; color: green; }
    .fotos-grid { margin-top: 30px; display: flex; flex-wrap: wrap; gap: 15px; }
    .foto-item { border: 1px solid #ddd; padding: 8px; border-radius: 6px; width: 180px; text-align: center; }
    .foto-item img { max-width: 100%; height: auto; border-radius: 4px; }
    .foto-info { font-size: 0.9em; margin-top: 6px; }
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

<h1>Evidências em Fotos - Monitorias</h1>

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

    <label for="foto">Selecione a foto:</label>
    <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,.gif" required>

    <button type="submit">Enviar Foto</button>
</form>

<?php if (count($fotos) > 0): ?>
    <h2>Fotos Enviadas</h2>
    <div class="fotos-grid">
        <?php foreach ($fotos as $foto): ?>
            <div class="foto-item">
                <img src="uploads/<?= htmlspecialchars($foto['caminho_foto']) ?>" alt="Foto de monitoria">
                <div class="foto-info">
                    <strong><?= htmlspecialchars($foto['materia_nome']) ?></strong><br>
                <?= date('d/m/Y H:i', strtotime($foto['data_envio'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="voltar">
    <a href="painel_monitor.php">← Voltar ao Painel</a>
</div>

</body>
</html>

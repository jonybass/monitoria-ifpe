<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'professor') {
    header("Location: ../index.php");
    exit;
}

// Use caminho absoluto para evitar problemas de diretório
require_once __DIR__ . '/../conexao.php';

// Processar exclusão lógica
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);
    $stmt = $conn->prepare("UPDATE materiais SET ativo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Material excluído com sucesso.";
    } else {
        $msg = "Erro ao excluir material.";
    }
}

// Filtros
$filtro_titulo = $_GET['filtro_titulo'] ?? '';
$filtro_tipo = $_GET['filtro_tipo'] ?? '';

// Buscar materiais ativos com filtros
$sql = "SELECT * FROM materiais WHERE ativo = 1";
$params = [];
$types = "";

if ($filtro_titulo) {
    $sql .= " AND titulo LIKE ?";
    $params[] = "%$filtro_titulo%";
    $types .= "s";
}
if ($filtro_tipo) {
    $sql .= " AND tipo LIKE ?";
    $params[] = "%$filtro_tipo%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$materiais = $result->fetch_all(MYSQLI_ASSOC);
?>

<h2>Gerenciar Materiais de Apoio</h2>

<?php if (isset($msg)): ?>
    <p style="color: green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<form method="GET" action="">
    <input type="text" name="filtro_titulo" placeholder="Filtrar por título" value="<?= htmlspecialchars($filtro_titulo) ?>">
    <input type="text" name="filtro_tipo" placeholder="Filtrar por tipo" value="<?= htmlspecialchars($filtro_tipo) ?>">
    <button type="submit">Filtrar</button>
</form>

<table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%; background:#fff;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Tipo</th>
            <th>Data Envio</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($materiais) === 0): ?>
            <tr><td colspan="5">Nenhum material encontrado.</td></tr>
        <?php else: ?>
            <?php foreach ($materiais as $mat): ?>
                <tr>
                    <td><?= $mat['id'] ?></td>
                    <td><?= htmlspecialchars($mat['titulo']) ?></td>
                    <td><?= htmlspecialchars($mat['tipo']) ?></td>
                    <td><?= htmlspecialchars($mat['data_envio']) ?></td>
                    <td>
                        <a href="editar_material.php?id=<?= $mat['id'] ?>" style="background:#28a745; color:#fff; padding:5px 10px; text-decoration:none; border-radius:3px;">Editar</a>
                        <a href="?excluir_id=<?= $mat['id'] ?>" onclick="return confirm('Deseja realmente excluir este material?');" style="background:#dc3545; color:#fff; padding:5px 10px; text-decoration:none; border-radius:3px;">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'professor') {
    header("Location: index.php");
    exit;
}

$professor = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Professor (Admin)</title>
    <style>
        body { font-family: Arial; background: #eef2f3; margin: 0; padding: 0; }
        header { background: #004080; color: white; padding: 15px; text-align: center; }
        nav { background: #0066cc; padding: 10px; }
        nav a {
            color: white; margin: 0 15px; text-decoration: none; font-weight: bold;
        }
        nav a:hover { text-decoration: underline; }
        main { padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        .btn { padding: 5px 10px; color: white; border: none; cursor: pointer; border-radius: 3px; }
        .btn-edit { background: #28a745; }
        .btn-delete { background: #dc3545; }
        .logout { position: absolute; top: 15px; right: 20px; color: white; text-decoration: underline; }
    </style>
</head>
<body>

<header>
    <h1>Bem-vindo, Professor <?= htmlspecialchars($professor) ?></h1>
    <a href="logout.php" class="logout">Sair</a>
</header>

<nav>
    <a href="?pagina=monitorias">Monitorias</a>
    <a href="?pagina=monitores">Monitores</a>
    <a href="?pagina=alunos">Alunos</a>
    <a href="?pagina=materiais">Materiais</a>
    <a href="?pagina=confirmacoes">Confirmações</a>
    <a href="?pagina=relatorios">Relatórios</a>
</nav>

<main>
<?php
$pagina = $_GET['pagina'] ?? 'monitorias';

switch ($pagina) {
    case 'monitorias':
        include 'adm/admin_monitorias.php';
        break;
    case 'monitores':
        include 'adm/admin_monitores.php';
        break;
    case 'alunos':
        include 'adm/admin_alunos.php';
        break;
    case 'materiais':
      

        include(__DIR__ . '/adm/admin_materiais.php');

        break;
    case 'confirmacoes':
        include 'adm/admin_confirmacoes.php';
        break;
    case 'relatorios':
        include 'adm/admin_relatorios.php';
        break;
    default:
        echo "<p>Seja bem-vindo ao painel administrativo.</p>";
        break;
}

?>
</main>

</body>
</html>

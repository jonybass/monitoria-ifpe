<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'monitor') {
    header("Location: index.php");
    exit;
}

$monitor = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Monitor</title>
    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #006400;
            color: white;
            padding: 15px;
            text-align: center;
        }

        main {
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h3 {
            margin-top: 0;
        }

        .card a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #006400;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout {
            position: absolute;
            top: 15px;
            right: 20px;
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <h1>Bem-vindo, <?= htmlspecialchars($monitor) ?></h1>
    <a href="logout.php" class="logout">Sair</a>
</header>

<main>
    <div class="card">
        <h3>📚 Cadastrar Matéria</h3>
        <p>Adicione uma nova matéria que você irá monitorar.</p>
        <a href="cadastro_materia.php">Cadastrar Matéria</a>
    </div>

    <div class="card">
    <h3>📋 Minhas Monitorias</h3>
    <p>Veja as matérias que você cadastrou.</p>
    <a href="minhas_monitorias.php">Ver Monitorias</a>
    </div>


    <div class="card">
        <h3>🕒 Gerenciar Horários</h3>
        <p>Configure os dias e horários das suas monitorias.</p>
        <a href="horarios.php">Gerenciar Horários</a>
    </div>

    <div class="card">
        <h3>👨‍🎓 Alunos da Monitoria</h3>
        <p>Adicione os alunos inscritos nas suas matérias.</p>
        <a href="alunos.php">Gerenciar Alunos</a>
    </div>

    <div class="card">
        <h3>🖼️ Evidências em Fotos</h3>
        <p>Envie fotos para comprovar a realização da monitoria.</p>
        <a href="fotos.php">Enviar Fotos</a>
    </div>

    <div class="card">
        <h3>📎 Arquivos de Apoio</h3>
        <p>Anexe arquivos (PDF, slides, exercícios...)</p>
        <a href="arquivos.php">Enviar Arquivos</a>
    </div>

    <div class="card">
        <h3>📄 Relatórios Mensais</h3>
        <p>Gere o relatório oficial de frequência (Anexo III) em PDF.</p>
        <a href="relatorio_filtro.php">Gerar Relatório</a>
    </div>
</main>

</body>
</html>

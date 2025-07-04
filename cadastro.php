<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - IFPE Monitoria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .cadastro-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 340px;
            text-align: center;
        }

        .cadastro-container input,
        .cadastro-container select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .cadastro-container input[type="submit"] {
            background-color: #006400;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .cadastro-container input[type="submit"]:hover {
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

<div class="cadastro-container">
    <h2>Cadastro de Usuário</h2>
    <form action="salvar_usuario.php" method="post">
        <input type="text" name="usuario" placeholder="Nome do usuário (exibido)" required>
        <input type="text" name="login" placeholder="Login de acesso" required>
        <input type="password" name="senha" placeholder="Senha" required>
        
        <select name="tipo_usuario" required>
            <option value="">Selecione o tipo de usuário</option>
            <option value="aluno">Aluno</option>
            <option value="monitor">Monitor</option>
            <option value="professor">Professor (ADM)</option>
        </select>

        <input type="submit" value="Cadastrar">
    </form>
    <div class="voltar">
        <a href="index.php">Página inicial</a>
    </div>
</div>

</body>
</html>

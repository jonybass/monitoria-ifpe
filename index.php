<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - IFPE Monitoria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            width: 320px;
        }

        .login-container img {
            max-width: 120px;
            margin-bottom: 20px;
        }

        .login-container input[type="text"],
        .login-container input[type="password"],
        .login-container select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .login-container input[type="submit"],
        .login-container .cadastro-btn {
            width: 100%;
            background-color: #006400;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
        }

        .login-container input[type="submit"]:hover,
        .login-container .cadastro-btn:hover {
            background-color: #008000;
        }
    </style>
</head>
<body>

<div class="login-container">
    <img src="img/logo_ifpe.png" alt="Logo IFPE Monitoria">
    <h2>Login Monitoria</h2>
    
    <form action="login.php" method="post">
        <input type="text" name="usuario" placeholder="Usuário" required>
        <input type="password" name="senha" placeholder="Senha" required>
        
        <select name="tipo_usuario" required>
            <option value="">Selecione o tipo de usuário</option>
            <option value="aluno">Aluno</option>
            <option value="monitor">Monitor</option>
            <option value="professor">Professor (ADM)</option>
        </select>
        
        <input type="submit" value="Entrar">
    </form>

    <a class="cadastro-btn" href="cadastro.php">Cadastrar Usuário</a>
</div>

</body>
</html>

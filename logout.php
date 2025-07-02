<?php
session_start();        // Inicia a sessão
session_unset();        // Limpa todas as variáveis de sessão
session_destroy();      // Destroi a sessão
header("Location: index.php"); // Redireciona para a página inicial/login
exit;
?>

<?php
$host = "localhost:3306";
$db = "ifpe_monitoria";
$user = "root"; // Altere se necessário
$pass = "root";     // Altere se necessário

$conn = new mysqli($host, $user, $pass, $db);

// Verifica conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>

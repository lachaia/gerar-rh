<?php
#
# INCLUDE - CONEXÃO AO BANCO DE DADOS PRODUÇÃO
#
// Credenciais vêm de variáveis de ambiente (.env na raiz) — ver env.php e .env.example
require_once __DIR__ . '/env.php';

    $servername = $_ENV['DB_HOST'];    //- tunel para banco RH na SaveInCloud
    $username   = $_ENV['DB_USER'];
    $password   = $_ENV['DB_PASS'];
    $myDB       = $_ENV['DB_NAME'];
    $_SESSION['BD'] = 'PRODUCAO';

global $conn;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$myDB;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } 
    catch(PDOException $e) {
        include "includes/f_erros.php";
        $texto = "Connection failed: " . $e->getMessage();
        f_erro( $texto );
        $conn = null;
        $vetor = '{"status":"0", "mensagem":"'.$texto.'"}'; 
        die( $vetor );
    }

?>
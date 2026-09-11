<?php
//
//- rh_ficha_pessoa_aj4.php | Devolve dados da AÇÃO específica
//- (C)haia, 20/03/2025
//

session_start();

$idModulo = 2; // Pessoas
$idUsuario = $_SESSION['idUsuario'];

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados

$idAcao = filter_input(INPUT_POST, 'idAcao', FILTER_VALIDATE_INT);

if ( empty($idAcao) ) {
    die(json_encode(["status" => false, "msg" => "ID da Ação inválido!"]));
}

$sql = "SELECT A.*
            FROM rh_pessoas_ldt A
            WHERE idAcao = :idAcao";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":idAcao", $idAcao, PDO::PARAM_INT);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

//include "debug.php";
//debug( json_encode($dados, JSON_PRETTY_PRINT) );

if( $dados['idUsuario'] != $idUsuario ) $soleitura = true; else $soleitura = false;

if ($dados) {
    echo json_encode(["status" => true, "dados" => $dados, 'soleitura' => $soleitura]);
} else {
    echo json_encode(["status" => false, "msg" => "Nenhuma Informação encontrada!"]);
}

$conn = null;

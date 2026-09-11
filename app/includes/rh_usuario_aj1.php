<?php
//-----
// rh_usuario_aj1.php | Grava novo grupo
// (C)haia, 12/02/2025

session_start();

$idModulo  = 1; // rh_usuarios.php

$idEmpresa = $_SESSION['idEmpresa'];
$idLogin = $_SESSION['idLogin'];

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( $dados ) extract($dados);
/*
include "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => false, "msg" => "Teste de inclusão de GRUPO"]);

rh_usuario_aj1.php | 2025-02-19 00:50:28 
{
    "g_descricao": "RH",
    "g_sigla": "RH"
}
*/
if( ! empty($g_descricao) && ! empty($g_sigla) ){
    include_once "conexao_gerar.php";
    include_once "f_logs.php";
}else{
    $resposta = [
        "status"=> false,
        "msg"=>'<div class="alert alert-danger"><strong>ERRO: </strong> Faltou Parâmetros!</div>'
    ];
    die( json_encode( $respost, JSON_PRETTY_PRINT) );
}

$sql = "INSERT INTO rh_usuariosgrupo (idEmpresa, sigla, descricao, idLogin, ativo) VALUES ($idEmpresa, :sigla, :descricao, $idLogin, 1)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':sigla', $g_sigla);
$stmt->bindParam(':descricao', $g_descricao);

if( $stmt->execute() ){
    $idGrupo = $conn->lastInsertId();
    f_log("INC", "INCLUSÃO de GRUPO de Usuaários: Dados( $dados )", "rh_documentos", $idModulo, $idGrupo);
    $resposta = [
        "status"=> true,
        "msg"=>'<div class="alert alert-success"><strong>Sucesso: </strong> Incluído</div>',
        "idGrupo"=> $idGrupo
    ];
}

$conn = null;
die( json_encode( $resposta, JSON_PRETTY_PRINT) );
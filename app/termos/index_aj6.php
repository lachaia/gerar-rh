<?php
//
//- index_aj6.php | Atualiza Modelo
//- (C)haia, 22/03/2025, GPT
//

session_start();

$idModulo = 19; // Equipamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

if (empty($id) || empty($nome) || empty($html)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

/*
include_once "../includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-primary">
        <strong>OK!</strong> Sucesso no Teste de Sistema!
        </div>'
];
die( json_encode( $retorno ) );

 index_aj4.php | 2025-08-22 14:13:32 
{
    "id": "5",
    "nome": "Nome do modelo",
    "html": "<p>texto do modelo<\/p>"
}
*/

$sql = "UPDATE rh_equip_modelos 
        SET nome = :nome, texto = :texto, alterado_por = :alterado_por, alterado_em = NOW()
        WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':nome', $nome);
$stmt->bindParam(':texto', $html);
$stmt->bindParam(':alterado_por', $nmLogin);

if ($stmt->execute()) {
    $retorno = [
        "status" => true,
        "msg" => '<div class="alert alert-success">
        <strong>OK!</strong> Modelo atualizado com sucesso!
        </div>'
    ];
} else {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
        <strong>ERRO!</strong> Falha ao atualizar modelo!
        </div>'
    ];    
}

die(json_encode($retorno));

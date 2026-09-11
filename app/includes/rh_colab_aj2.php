<?php
//
//- rh_colab_aj2.php | Salva Nova Função de COLABORADOR
//- (C)haia, 07/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

/*
// TESTE DE RECEBIMENTO DE DADOS
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );


rh_colab_aj2.php | 2025-04-07 17:59:16 
{
    "nmFuncao": "teste",
    "dsFuncao": "<p>teste<\/p>"
}
*/

if ( empty($nmFuncao) || empty($dsFuncao) ) {
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
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

$sql = "INSERT INTO rh_funcoes ( idEmpresa, nome, descricao, ativo, idLogin) 
            values ( :idEmpresa, :nome, :descricao, 1, :idLogin )";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
$stmt->bindValue(':idLogin',   $idLogin,  PDO::PARAM_INT);
$stmt->bindValue(':nome',      $nmFuncao,  PDO::PARAM_STR);
$stmt->bindValue(':descricao', $dsFuncao,  PDO::PARAM_STR);

if( $stmt->execute() ) {
    $idFuncao = $conn->lastInsertId();
    $retorno = [
        'status'=> true,
        "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Função cadastrada com sucesso!.</div>"
    ];
} else {
    $retorno = [
        'status'=> false,
        "msg" => "<div class='alert alert-danger'><strong>Erro!</strong> Não foi possível cadastrar a função!.</div>"
    ];
}

$dados = [
    "idFuncao" => $idFuncao,
    "nmFuncao" => $nmFuncao
];
$retorno['dados'] = $dados;

die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
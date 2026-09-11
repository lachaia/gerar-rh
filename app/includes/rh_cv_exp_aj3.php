<?php
//
//- rh_cv_exp_aj3.php | CURRÍCULO | Salva nova EXPERIÊNCIA PROFISSIONAL) 
//- (C)haia, 26/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

/*
// TESTE DE RECEBIMENTO DE DADOS
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );

rh_cv_exp_aj3.php | 2025-03-26 17:22:29 
{
    "idPessoa": "1",
    "empresa": "AGROMALTE S\/A",
    "ativo": "0",
    "cargo": "Gerente de TI",
    "ano_ini": "1991",
    "ano_fim": "1994",
    "descricao": "<p>Respons\u00e1vel pela <b>gest\u00e3o <\/b>do departamento de tecnologia da informa\u00e7\u00e3o, desenvolvimente e manuten\u00e7\u00e3o dos sistemas, etc<\/p>"
}

*/
if( empty( $empresa ) || empty( $cargo ) || empty( $ano_ini ) || empty( $ano_fim ) || empty( $descricao ) ){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

if( isset($_SESSION['idLogin']) ){
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}

$sql = "INSERT INTO rh_cv_exp ( idEmpresa, idPessoa, empresa, cargo, descricao, ano_ini, ano_fim, ativo, idLogin) 
            VALUES (:idEmpresa, :idPessoa, :empresa, :cargo, :descricao, :ano_ini, :ano_fim, :ativo, :idLogin)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idEmpresa', $idEmpresa );
$consulta->bindParam(':idPessoa', $idPessoa );
$consulta->bindParam(':empresa', $empresa );
$consulta->bindParam(':cargo', $cargo );
$consulta->bindParam(':descricao', $descricao );
$consulta->bindParam(':ano_ini', $ano_ini );
$consulta->bindParam(':ano_fim', $ano_fim );
$consulta->bindParam(':ativo', $ativo );
$consulta->bindParam(':idLogin', $idLogin );

// Executa a inserção
if ($consulta->execute()) {
    $idExp = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idExp</div>";
    //
    $sql = "SELECT * FROM rh_cv_exp WHERE id = $idExp";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados = implode(", ", $linha);
    //
    $response = ["status" => true, "msg" => $msg, "dados"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("INC", "INCLUSÃO no CV de Experiência Profissional: Dados( $dados )", "rh_cargos", $idModulo, $idExp);

$conn = null;
die(json_encode($response));
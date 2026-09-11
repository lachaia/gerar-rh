<?php
//
//- rh_cv_af_aj5.php | CURRÍCULO | Salva ALTERAÇÃO de Experiência Profissional 
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

/*
 rh_cv_exp_aj5.php | 2025-03-27 10:32:06 
{
    "idExp": "2",
    "empresa": "AGROMALTE S\/A",
    "ativo": "1",
    "cargo": "GERENTE DE TI",
    "ano_ini": "1991",
    "ano_fim": "1994",
    "descricao": "<p>ATIVIDADES DE DESENVOLVIMENTO DE SISTEMAS<\/p>"
}
*/

if( empty($idExp) || empty($empresa) || empty( $cargo ) || empty( $ano_ini ) || empty( $ano_fim ) || empty( $descricao ) ){
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

//
// Carrega dados antigos
$sql = "SELECT E.*, P.nome 
            FROM rh_cv_exp E 
            INNER JOIN rh_pessoas P on P.idPessoa = E.idPessoa
            WHERE id = $idExp";
$stmt = $conn->prepare($sql);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
$dados_old = implode(", ", $linha);

$sql = "UPDATE rh_cv_exp SET empresa = :empresa, cargo = :cargo, descricao = :descricao, 
                    ano_ini = :ano_ini, ano_fim = :ano_fim, ativo = :ativo  
                    WHERE id = :idExp";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idExp', $idExp );
$consulta->bindParam(':empresa', $empresa );
$consulta->bindParam(':cargo', $cargo );
$consulta->bindParam(':descricao', $descricao );
$consulta->bindParam(':ano_ini', $ano_ini );
$consulta->bindParam(':ano_fim', $ano_fim );
$consulta->bindParam(':ativo', $ativo );

// Executa a inserção
if ($consulta->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao alterar registro. ID: $idExp</div>";
    //
    $dados = implode(", ", $parametros);
    //
    $response = ["status" => true, "msg" => $msg, "dados"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}
$nome = $linha['nome'];
f_log("ALT", "ALTERAÇÃO de CURRÍCULO (Experiência Profissional) de $nome, Dados Anteriores ($dados_old), Dados Novos ($dados)", "rh_cv_exp", $idModulo, $idExp);

$conn = null;
die(json_encode($response));
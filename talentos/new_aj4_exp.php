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
include "../app/includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
/*
new_aj4_exp.php | 2026-04-13 13:44:43 
{
    "empresa": "GERAR",
    "ativo": "1",
    "cargo": "GERENTE COMERCIAL",
    "ano_ini": "2020",
    "ano_fim": "2026",
    "descricao": "TEESTE",
    "idPessoa": "28"
}
*/

if( ! isset($ativo) || empty($ativo) ) $ativo = 0;

if( empty( $empresa ) || empty( $cargo ) || empty( $ano_ini ) || ($ativo == 0 && empty( $ano_fim )) || empty( $descricao ) ){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

    if( isset($_SESSION['idLogin'])   && !empty($_SESSION['idLogin']) )   $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
    if( isset($_SESSION['idEmpresa']) && !empty($_SESSION['idEmpresa']) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;

    include_once "../app/includes/conexao_gerar.php";
    include_once "../app/includes/f_logs.php";

//- idPessoa vem da sessão aberta em new_aj1.php (dedupe por CPF) - nunca de um
//- campo do cliente, senão dá pra incluir experiência no currículo de qualquer
//- pessoa só sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

if( empty($ano_fim) ) $ano_fim = null;

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
    $response = ["status" => true, "msg" => $msg, "id"=> $idExp ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("INC", "INCLUSÃO no CV de Experiência Profissional: Dados( $dados )", "rh_cargos", $idModulo, $idExp);

$conn = null;
die(json_encode($response));
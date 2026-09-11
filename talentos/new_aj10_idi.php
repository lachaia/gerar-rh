<?php
//
//- rh_cv_exp_aj3.php | CURRÍCULO | Salva nova Habilidade: IDIOMA 
//- (C)haia, 26/03/2025 | (U) 14/04/2026
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
 rh_cv_idi_aj3.php | 2025-03-28 16:11:03 
{
    "idPessoa": "1",
    "idIdioma": "19",
    "idFluencia": "1"
}

*/
if( empty( $idIdioma ) || empty( $idFluencia ) ){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

if( isset( $_SESSION['idLogin'] ) ) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
if( isset( $_SESSION['idEmpresa'] ) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;

include_once "../app/includes/conexao_gerar.php";
include_once "../app/includes/f_logs.php";

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra incluir
//- idioma no currículo de qualquer pessoa só sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

$sql = "INSERT INTO rh_cv_idiomas ( idEmpresa, idPessoa, idIdioma, idFluencia, idLogin) 
            VALUES ( :idEmpresa, :idPessoa, :idIdioma, :idFluencia, :idLogin)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idEmpresa', $idEmpresa );
$consulta->bindParam(':idPessoa', $idPessoa );
$consulta->bindParam(':idIdioma', $idIdioma );
$consulta->bindParam(':idFluencia', $idFluencia );
$consulta->bindParam(':idLogin', $idLogin );

// Executa a inserção
if ($consulta->execute()) {
    $idIdi = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idIdi</div>";
    //
    $sql = "SELECT C.*, I. nome as idioma, F.nome as fluencia
            FROM rh_cv_idiomas C
            INNER JOIN rh_idiomas I on I.idIdioma = C.idIdioma
            INNER JOIN rh_fluencias F on F.idFluencia = C.idFluencia
            WHERE C.id = $idIdi";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados = implode(", ", $linha);
    //
    $response = ["status" => true, "msg" => $msg, "d"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("INC", "INCLUSÃO no CV de Idioma: Dados( $dados )", "rh_cv_idiomas", $idModulo, $idIdi);

$conn = null;
die(json_encode($response));
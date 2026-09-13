<?php
//
//- rh_ponto_aj4.php | EXCLUI evento de calendário
//- (C)haia, 19/12/2025
//

ob_start();
header('Content-Type: application/json; charset=utf-8');

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

$idUsuario = $_SESSION['idUsuario'];

include_once "conexao_gerar.php";
include_once "f_logs.php";
//include_once "debug.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idUsuario = $_SESSION['idUsuario'];

if( empty($id)){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    ob_clean();
    die(json_encode($retorno));
}

//
//- RECUPERA DADOS ANTIGOS
//
    $sql = "SELECT E.*, C.nome as nmCidade 
            FROM rh_ponto_calendario E
            LEFT OUTER JOIN rh_cidades C ON C.idCidade = E.cidade_id 
            WHERE id = :id";
    $params = [':id' => $id_calendario_evento];
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $dados_old = $stmt->fetch();
//
//- EXCLUI REGISTRO
//
    $sql =  "DELETE FROM rh_ponto_calendario WHERE id = :id";
    $delete = $conn->prepare($sql);
    $delete->bindParam(':id', $id);

    if( $delete->execute() ){
        //
        //- REGISTRA LOG DE SISTEMA
        //
            $historico = "Excluído Registro de Evento de Calendário | Dados Antigos:  " .
                        json_encode($dados_old, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $tabela = "rh_ponto_calendario";
            $idOperacao = $id;
            f_log( "DEL", $historico, $tabela, $idModulo, $idOperacao, "rh_ponto_aj4.php" );
        //
        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-success">
                <strong>Successo!</strong> Registro excluído.
                </div>'];
        ob_clean();
        die(json_encode($retorno));
    }

$retorno = [
    "status" => false,
    "msg" => '<div class="alert alert-danger">
        <strong>Erro!!</strong> Registro NÃO foi excluído!
        </div>'];
ob_clean();
die(json_encode($retorno));
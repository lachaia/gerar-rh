<?php 
//
//- rh_ponto_aj3.php | (C)haia, 19/12/2025 | Retorna dados do Evento de Calendário
//

header('Content-Type: application/json');

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

$idUsuario = $_SESSION['idUsuario'];

include_once "conexao_gerar.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);

if( empty($id)){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

$sql = "SELECT E.*, C.nome as nmCidade 
        FROM rh_ponto_calendario E
        LEFT OUTER JOIN rh_cidades C ON C.idCidade = E.cidade_id 
        WHERE id = :id";
$params = [':id' => $id];
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetch();

die( json_encode($dados) );
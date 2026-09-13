<?php
//
//- unidade_ext_aj.php | EXCLUI REGISTRO DE UNIDADE
//- (C)haia, 15/07/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
} else {
    $idLogin = $_SESSION['idLogin'];
    $criado_por = $_SESSION['nmLogin'];
}
require_once "conexao_gerar.php";
include_once "f_logs.php";
//

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idLogin = $_SESSION['idLogin'];
$criado_por = $_SESSION['nmLogin'];

//- VERIFICA SE TEVE MOVIMENTO

    $sql = "SELECT * 
                FROM rh_colaboradores C
                INNER JOIN rh_polos P on P.id = C.polo_id
            WHERE P.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => false, "msg" => "ERRO: Polo j&aacute; Movimentado!"]);
        $conn = null;
        die;
    }

//- EXCLUI REGISTRO
//
    $sql = "DELETE FROM rh_polos WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $retorno = [
            "status" => true,
            "msg" => "Polo Excluído com sucesso!"
        ];
    } else{
        $retorno = [
            "status" => false,
            "msg" => "Polo n&atilde;o excluído!"
        ];
    }

die( json_encode($retorno) );
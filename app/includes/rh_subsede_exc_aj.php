<?php
//
//- subsede_ext_aj.php | EXCLUI REGISTRO DE SUBSEDE
//- (C)haia, 14/07/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
} else {
    $idLogin = $_SESSION['idLogin'];
    $criado_por = $_SESSION['nmLogin'];
}
include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";
//

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);

//- VERIFICA SE TEVE MOVIMENTO

    $sql = "SELECT C.idColab
                FROM rh_colaboradores C
                INNER JOIN rh_subsedes S on S.subsede_id = C.idSubSede
            WHERE S.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => false, "msg" => "ERRO: SubSede j&aacute; Movimentada!"]);
        $conn = null;
        die;
    }

//- EXCLUI REGISTRO
//
    $sql = "DELETE FROM rh_subsedes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $retorno = [
            "status" => true,
            "msg" => "SubSede Excluída com sucesso!"
        ];
    } else{
        $retorno = [
            "status" => false,
            "msg" => "SubSede n&atilde;o excluída!"
        ];
    }

die( json_encode($retorno) );
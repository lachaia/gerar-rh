<?php
//
//- rh_ctr_exp_aj1.php | Recupera dados do Contrato de Experiência
//- (C)haia, 22/09/2025
//

session_start();

$idModulo = 20; // Contratos de Experiência

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "conexao_gerar.php";
    include_once "f_logs.php";
} else {
    header("location: logout.php");
}

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

if (!isset($id) || empty($id)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou o ID do Contrato de Experiência!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

$pesquisa = "SELECT CT.*, ifnull(CT.data_prorrogacao, 'ND') as data_prorrogacao, P.nome, P.idPessoa, 
				CG.nome as dsCargo, O.descricao as dsOrgao
                FROM rh_ctr_exp CT 
                INNER JOIN rh_colaboradores C on C.idColab = CT.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_cargos CG on CG.idCargo = C.idCargo
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                WHERE CT.id = :idCtrExp";
$stmt = $conn->prepare($pesquisa);
$stmt->bindParam(':idCtrExp', $id, PDO::PARAM_INT);
$stmt->execute();
if ($stmt->rowCount() == 0) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Contrato de Experiência não encontrado!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
die( json_encode($linha) );

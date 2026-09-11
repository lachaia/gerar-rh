<?php
//
//- rh_rescisao_aj4.php | EXCLUIR Registro do RESCISÃO
//- (C)haia, 25/04/2025
//

session_start();

$idModulo = 9; // RESCISÕES

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "<div class='alert alert-primary'><strong>OK: </strong> Teste Realizado com Sucesso!</div>"];
die(json_encode($response));

*/
// Validação básica
if (!isset($id )) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Parâmetros!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "SELECT R.*, P.nome, T.descricao as dsTipoRescisao
            FROM rh_rescisoes R
            INNER JOIN rh_colaboradores C on C.idColab = R.idColab
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            INNER JOIN rh_rescisao_tipos T on T.idTipoRescisao = R.idTipoRescisao
            WHERE R.idRescisao = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idRescisao pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$antigos = json_encode($dados); // Converte os dados para JSON

$sql = "DELETE FROM rh_rescisoes WHERE idRescisao = $id";
$stmt = $conn->prepare($sql);

// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
    //
    //- Desfazer a rescisão do colaborador
    //
    $sql = "UPDATE rh_colaboradores SET idRescisao = null, data_rescisao = null, idRescisaoTipo = null, 
    idStatus = idStatusOld WHERE idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $dados['idColab'], PDO::PARAM_INT);
    $stmt->execute();
    //
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("EXC", "EXCLUSÃO de Rescisão de ID: $id, Dados ( $antigos )", "rh_rescisoes", $idModulo, $id);

$conn = null;
die(json_encode($response));
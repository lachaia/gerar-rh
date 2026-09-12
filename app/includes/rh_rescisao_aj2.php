<?php
//
//- rh_rescisao_aj2.php | Salva Registro da RESCISÃO
//- (C)haia, 24/04/2025
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
/*
 rh_rescisao_aj2.php | 2025-04-24 15:43:02 
{
    "nmPessoa": "Maria Eduarda Taborda Da Luz",
    "idColab": "4",
    "idPessoa": "62",
    "idTipoRescisao": "1",
    "_tipoAviso": "Trabalhado",
    "_dtAviso": "2025-04-01",
    "_dtDesligamento": "2025-04-30",
    "_status": "Pendente",
    "_saldoSalario": "10000.05",
    "_feriasVencidas": "4500",
    "_feriasProporcionais": "1800",
    "_decimoTerceiro": "2800",
    "_multaFgts": "4500",
    "_descontos": "5000",
    "_totalLiquido": "18600.05",
    "_motivo": "<p><br><\/p>"
}

*/

extract($parametros);

// Validação básica
if (!isset($idPessoa, $idColab, $_dtAviso, $_tipoAviso, $_dtDesligamento, $_status, $_saldoSalario, $_feriasVencidas, 
        $_feriasProporcionais, $_decimoTerceiro, $_multaFgts, $_descontos, $_totalLiquido)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Todos os Campos são necessários!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [1, 9], true)) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

$sql = "INSERT INTO rh_rescisoes 
        (idColab, idTipoRescisao, motivoRescisao, dtAviso, tipoAviso, dtDesligamento, saldoSalario, 
        feriasVencidas, feriasProporcionais, decimoTerceiro, multaFgts, descontos, totalLiquido, status, idLogin, 
        dtRegistro) 
        VALUES 
        ( :idColab, :idTipoRescisao, :motivoRescisao, :dtAviso, :tipoAviso, :dtDesligamento, :saldoSalario, 
          :feriasVencidas, :feriasProporcionais, :decimoTerceiro, :multaFgts, :descontos, :totalLiquido, :status, 
          :idLogin, :dtRegistro)";
$stmt = $conn->prepare($sql);

$stmt->bindValue(':idColab', $_POST['idColab'], PDO::PARAM_INT);
$stmt->bindValue(':idTipoRescisao', $_POST['idTipoRescisao'], PDO::PARAM_INT);
$stmt->bindValue(':motivoRescisao', $_POST['_motivo'], PDO::PARAM_STR);
$stmt->bindValue(':dtAviso', $_POST['_dtAviso'] ?: null);
$stmt->bindValue(':tipoAviso', $_POST['_tipoAviso'], PDO::PARAM_STR);
$stmt->bindValue(':dtDesligamento', $_POST['_dtDesligamento'], PDO::PARAM_STR);
$stmt->bindValue(':saldoSalario', $_POST['_saldoSalario'], PDO::PARAM_STR);
$stmt->bindValue(':feriasVencidas', $_POST['_feriasVencidas'], PDO::PARAM_STR);
$stmt->bindValue(':feriasProporcionais', $_POST['_feriasProporcionais'], PDO::PARAM_STR);
$stmt->bindValue(':decimoTerceiro', $_POST['_decimoTerceiro'], PDO::PARAM_STR);
$stmt->bindValue(':multaFgts', $_POST['_multaFgts'], PDO::PARAM_STR);
$stmt->bindValue(':descontos', $_POST['_descontos'], PDO::PARAM_STR);
$stmt->bindValue(':totalLiquido', $_POST['_totalLiquido'], PDO::PARAM_STR);
$stmt->bindValue(':status', $_POST['_status'], PDO::PARAM_STR);
$stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
$stmt->bindValue(':dtRegistro', date('Y-m-d H:i:s'));

// Executa a inserção
if ($stmt->execute()) {
    $idRescisao = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idRescisao</div>";
    $response = ["status" => true, "msg" => $msg ];
    //
    //- Recupera status anterior do colaborador
    //
        $sql = "SELECT idStatus FROM rh_colaboradores WHERE idColab = :idColab";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':idColab', $_POST['idColab'], PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        $idStatusOld = $linha['idStatus'];
    //
    //- Atualiza o status do colaborador
    //
    $idStatus = 7; // Demitido
    if($idTipoRescisao == 8) $idStatus = 13; // falecimento
    if($idTipoRescisao == 7) $idStatus = 9;  // aposentadoria
    if($idTipoRescisao == 3) $idStatus = 8;  // Pediu demissão
    $sql = "UPDATE rh_colaboradores SET idRescisao = :idRescisao, data_rescisao = :data_rescisao, 
                    idRescisaoTipo = :idRescisaoTipo, idStatus = :status, idStatusOld = :idStatusOld 
                    WHERE idColab = :idColab";
    $stmt = $conn->prepare($sql);   
    $stmt->bindValue(':status', $idStatus, PDO::PARAM_STR);
    $stmt->bindValue(':idColab', $_POST['idColab'], PDO::PARAM_INT);
    $stmt->bindValue(':idRescisaoTipo', $_POST['idTipoRescisao'], PDO::PARAM_INT);
    $stmt->bindValue(':data_rescisao', $_POST['_dtDesligamento'], PDO::PARAM_STR);
    $stmt->bindValue(':idRescisao', $idRescisao, PDO::PARAM_INT);
    $stmt->bindValue(':idStatusOld', $idStatusOld, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

$dados = implode(", ", $parametros);
f_log("INC", "INCLUSÃO de RESCISÃO de $nmPessoa: Dados( $dados )", "rh_exames", $idModulo, $idRescisao);

$conn = null;
die(json_encode($response));
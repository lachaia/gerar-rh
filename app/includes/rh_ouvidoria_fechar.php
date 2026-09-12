<?php
//
//- rh_ouvidoria_fechar.php | Salva FECHAMENTO de Denúncia
//- (C)haia, 18/08/2025
//

session_start();

$idModulo = 15; // Acolhimento do RH

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO"];
die(json_encode($response));
/*
 rh_acolhimento_aj1.php | 2025-08-18 10:55:04 
{
    "id": "2",
    "motivo": "asdf"
}
*/

// Validação básica
if (!isset($id, $motivo)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Todos os Campos são necessários!</div>";
    $response = ["status" => false, "msg" => $msg];
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [3, 9], true)) {
    $status_por = $_SESSION['nmLogin'];
    $motivo = addslashes($motivo); // Protege contra SQL Injection
    //
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

//
//- Fechar a denuncia é setar o status = 9 + motivo.
//
$sql = "UPDATE rh_ouvidoria 
            SET status = 9, 
                status_motivo = :motivo, 
                status_em = NOW(), 
                status_por = :status_por 
            WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
$stmt->bindParam(':status_por', $status_por, PDO::PARAM_STR);

// Executa a inserção
if ($stmt->execute()) {

    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao atualizar o registro. ID: $id</div>";
    $response = ["status" => true, "msg" => $msg];

} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao atualizar registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}

$dados = implode(", ", $parametros);
f_log("FEC", "Baixa/Fechamento de Denúncia/Acolhimento de Ouvidoria", "rh_ouvidoria", $idModulo, $id);

$conn = null;
die(json_encode($response));


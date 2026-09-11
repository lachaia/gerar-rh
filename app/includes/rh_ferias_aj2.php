<?php
//
//- rh_ferias_aj2.php | SALVAR Registro do FÉRIAS (editar)
//- (C)haia, 02/05/2025
//

session_start();

$idModulo = 12; // férias

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
// Para depuração, descomente a linha abaixo
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$msg = "<div class='alert alert-primary'><strong>OK: </strong> TESTE REALIZADO COM SUCESSO!</div>";
$response = ["status" => true, "msg" => $msg ]; 
die(json_encode($response));
//
rh_ferias_aj2.php | 2025-05-02 17:00:47 
{
    "e_id": "1",
    "e_agenda1": "2024-08-01",
    "e_dias1": "15",
    "e_agenda2": "2025-01-03",
    "e_dias2": "15",
    "e_agenda3": "",
    "e_dias3": "",
    "e_fruido1": "2024-08-01",
    "e_fruido2": "2025-01-03",
    "e_fruido3": "",
    "e_obs": "<p>teste<\/p>"
}
*/

// Validação básica
if (!isset($e_id )) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Parâmetros!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "SELECT  P.nome, 
                F.*, 
                LEAST(12, TIMESTAMPDIFF(MONTH, F.inicio_aquisitivo, CURDATE())) * 2.5 AS dias_adquiridos,
                DATEDIFF(F.fim_concessivo, CURDATE()) AS dias_para_vencer
             FROM rh_ferias F
             INNER JOIN rh_colaboradores C ON C.idColab = F.idColab
             INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
             WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $e_id]); // Substitua $e_id pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$dados_old = implode(", ", $dados); // Cria uma string com os valores separados por vírgula

$nome = $dados['nome'];

$e_agenda1 = empty($e_agenda1) ? null : $e_agenda1;
$e_agenda2 = empty($e_agenda2) ? null : $e_agenda2;
$e_agenda3 = empty($e_agenda3) ? null : $e_agenda3;

$agendado_em1  = empty($e_agenda1) ? null : $agora;
$agendado_em2  = empty($e_agenda2) ? null : $agora;
$agendado_em3  = empty($e_agenda3) ? null : $agora;
$agendado_por1 = empty($e_agenda1) ? null : $nmLogin;
$agendado_por2 = empty($e_agenda2) ? null : $nmLogin;
$agendado_por3 = empty($e_agenda3) ? null : $nmLogin;

$e_fruido1 = empty($e_fruido1) ? null : $e_fruido1;
$e_fruido2 = empty($e_fruido2) ? null : $e_fruido2;
$e_fruido3 = empty($e_fruido3) ? null : $e_fruido3;

$e_dias1 = ($e_dias1 === "") ? null : $e_dias1;
$e_dias2 = ($e_dias2 === "") ? null : $e_dias2;
$e_dias3 = ($e_dias3 === "") ? null : $e_dias3;

$sql = "UPDATE rh_ferias 
    SET agenda_parte1 = :agenda_parte1, agenda_parte2 = :agenda_parte2, agenda_parte3 = :agenda_parte3,     
        data_parte1 = :data_parte1, data_parte2 = :data_parte2, data_parte3 = :data_parte3,     
        dias_parte1 = :dias_parte1, dias_parte2 = :dias_parte2, dias_parte3 = :dias_parte3,
        observacao = :observacao, agendado_em1 = :agendado_em1, agendado_em2 = :agendado_em2, agendado_em3 = :agendado_em3,
        agendado_por1 = :agendado_por1, agendado_por2 = :agendado_por2, agendado_por3 = :agendado_por3
    Where id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $e_id);
$stmt->bindParam(':agenda_parte1', $e_agenda1);
$stmt->bindParam(':agenda_parte2', $e_agenda2);
$stmt->bindParam(':agenda_parte3', $e_agenda3);
$stmt->bindParam(':dias_parte1', $e_dias1);
$stmt->bindParam(':dias_parte2', $e_dias2);
$stmt->bindParam(':dias_parte3', $e_dias3);
$stmt->bindParam(':data_parte1', $e_fruido1);
$stmt->bindParam(':data_parte2', $e_fruido2);
$stmt->bindParam(':data_parte3', $e_fruido3);
$stmt->bindParam(':observacao', $e_obs);
$stmt->bindParam(':agendado_em1', $agendado_em1);
$stmt->bindParam(':agendado_em2', $agendado_em2);
$stmt->bindParam(':agendado_em3', $agendado_em3);
$stmt->bindParam(':agendado_por1', $agendado_por1);
$stmt->bindParam(':agendado_por2', $agendado_por2);
$stmt->bindParam(':agendado_por3', $agendado_por3);

// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao atualizar o registro. ID: $e_id</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao atualizar registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

$dados = implode(", ", $parametros);
f_log("ALT", "ALTERAÇÃO de Período de Férias de $nome: Dados anteriores ($dados_old) |  Dados Novos( $dados )", "rh_ferias", $idModulo, $e_id);

$conn = null;
die(json_encode($response));
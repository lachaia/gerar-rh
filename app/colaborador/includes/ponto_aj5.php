<?php
//
//- ponto_aj5.php | (C)haia, 09/12/2025 | Altera Registro de batida
//

header("Content-Type: application/json");

session_start();

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($parametros)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

extract($parametros);

include "../../includes/conexao_gerar.php";
include "../../includes/debug.php";
//
//debug( json_encode($parametros, JSON_PRETTY_PRINT) );
/*
 ponto_aj5.php | 2025-12-09 09:56:59 
{
    "batidaId": "9451",
    "tipoSolicitacao": "ALT",
    "dataBatida": "2025-12-05",
    "horaBatida": "11:11",
    "motivo": "teste"
}
*/

$idLogin = $_SESSION['idLogin'];;
$idColab = $_SESSION['idColab'];

$data_hora = "$dataBatida $horaBatida:00";

//
//- Busca dados do Supervisor na API
//
    $url = "https://rh.gerar.org.br/api/api_supervisor.php?id=" . $idColab;
    $resposta = file_get_contents($url);
    $dados = json_decode($resposta, true); // converte JSON em array associativo

//
//- INSERE DADOS
//
    $sql = "INSERT 
                INTO rh_ponto_solicitacoes 
                (batida_id, colaborador_id, supervisor_id, data_hora, motivo, idLogin, status, tipo)
                VALUES
                (:batida_id, :colaborador_id, :supervisor_id, :data_hora, :motivo, :idLogin, 'AGUARDANDO', :tipo)";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':batida_id', $batidaId, PDO::PARAM_INT);
    $stmt->bindValue(':colaborador_id', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':supervisor_id', $dados['gestor_id'], PDO::PARAM_INT);
    $stmt->bindValue(':data_hora', $data_hora, PDO::PARAM_STR);
    $stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->bindValue(':tipo', $tipoSolicitacao, PDO::PARAM_STR);
    $stmt->execute();


$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-success">
        <strong>OK!</strong> Solicitação inserida com sucesso!
        </div>'
];

die(json_encode($retorno));
<?php 
//
//- edita_cartao_aj1.php | SALVA SOLICITAÇÃO DE BATIDA
// (C)haia, 12/11/2025
//

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

$idColab = $_SESSION['idColab'];
$idLogin = $_SESSION['idLogin'];

$data_hora = "$data $hora:00";

/*
// Para depuração, descomente a linha abaixo
include dirname(__DIR__) . '/../includes/debug.php';
debug(json_encode($parametros, JSON_PRETTY_PRINT));
$resposta = [
    "status" => true,
    "msg" => "Teste de Sistema Realizado com Sucesso!"
];
die( json_encode( $resposta, JSON_PRETTY_PRINT) );
/*
 edita_cartao_aj1.php | 2025-11-13 10:00:11 
{
    "id": "14",
    "data": "2025-11-08",
    "hora": "21:29",
    "motivo": "teste",
    "tipo": "ALT"
}
*/
//
//- Busca dados do Supervisor na API
//
    $url = "https://rh.gerar.org.br/api/api_supervisor.php?id=" . $idColab;
    $resposta = file_get_contents($url);
    $dados = json_decode($resposta, true); // converte JSON em array associativo

$sql = "INSERT 
            INTO rh_ponto_solicitacoes 
            (batida_id, colaborador_id, supervisor_id, data_hora, motivo, idLogin, status, tipo)
            VALUES
            (:batida_id, :colaborador_id, :supervisor_id, :data_hora, :motivo, :idLogin, 'AGUARDANDO', :tipo)";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':batida_id', $id, PDO::PARAM_INT);
$stmt->bindValue(':colaborador_id', $idColab, PDO::PARAM_INT);
$stmt->bindValue(':supervisor_id', $dados['gestor_id'], PDO::PARAM_INT);
$stmt->bindValue(':data_hora', $data_hora, PDO::PARAM_STR);
$stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
$stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
$stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
$stmt->execute();
//
$resposta = [
    "status" => true,
    "msg" => "Solicitação enviada com sucesso!"
];
die( json_encode( $resposta, JSON_PRETTY_PRINT) );
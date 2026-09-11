<?php
//
//- rh_espelhos_aj1.php | Retorna dados do Espelho ID | Modulo Ponto Eletrônico
//

header('Content-Type: application/json');

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include_once "conexao_gerar.php";

$id = $_POST['id'];

if( empty($id)){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die(json_encode($retorno));
}

$pesquisa = "SELECT P.nome, E.* 
                FROM rh_ponto_espelhos E
                INNER JOIN rh_colaboradores C on C.idColab = E.colaborador_id
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa 
                WHERE id = $id";
$stmt = $conn->prepare( $pesquisa );
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

$colaborador_id = $dados['colaborador_id'];

$url = "d:/xampp/htdocs/rh/ponto/docs/$colaborador_id/" . rawurlencode($dados['arquivo']);

$dsStatus = $dados['status'];
if( $dados['status'] == 'GERADO') $dsStatus = "<span class='badge bg-primary w-100 status'>".'<i class="fa-solid fa-triangle-exclamation"></i> '."Falta ASSINAR...</span>";
if( $dados['status'] == 'ASSINADO') $dsStatus = "<span class='badge bg-success w-100 status'>ASSINADO</span>";
if( $dados['status'] == 'ALERTA') $dsStatus = "<span class='badge bg-warning text-dark w-100 status'>" . '<i class="fa-solid fa-triangle-exclamation"></i> ' . "ALERTA</span>";

$retorno = [
    "status" => true,
    "msg" => $dados,
    'colaborador_id' => $colaborador_id,
    'nome'    => $dados['nome'],
    'mes_ref' => $dados['ano'] . '/' . $dados['dsMes'],
    'arquivo' => $dados['arquivo'],
    'dsStatus'  => $dsStatus,
    '_status'   => $dados['status'],
    'horas_normal' => formata_hora($dados['horas_normal']),
    'horas_extras' => formata_hora($dados['extras']),
    'horas_faltas' => formata_hora($dados['faltas']),
    'periodo_ini'  => date('d/m/Y', strtotime($dados['periodo_ini'])),
    'periodo_fim'  => date('d/m/Y', strtotime($dados['periodo_fim'])),
    'assinado_em'  => date('d/m/Y H:i', strtotime($dados['assinado_em'])),
];
die(json_encode($retorno));

function formata_hora($segundos) {
    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos % 3600) / 60);
    return sprintf('%02d:%02d', $horas, $minutos);
}
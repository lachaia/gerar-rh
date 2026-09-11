<?php
// rh_ouvidoria_aj.php | Consulta Denuncias
// by (C)haia, 24/07/2025
//

$idModulo = 15; // Acolhimento do RH

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else{
    header("location: logout.php");
}

f_log("CON", "Consulta grade do Cargos", "rh_cargos", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT O.*,
                    CASE 
                        WHEN O.encerrado_em IS NULL THEN NULL
                        ELSE TIMESTAMPDIFF(DAY, O.data_envio, O.encerrado_em)
                    END AS dias_ate_encerramento,
                    CASE 
                        WHEN O.status_em IS NULL THEN NULL
                        ELSE TIMESTAMPDIFF(DAY, O.data_envio, O.status_em)
                    END AS dias_ate_status,
                    TIMESTAMPDIFF(DAY, O.data_envio, NOW()) AS dias_ate_hoje
                FROM rh_ouvidoria O";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm me-1' onClick='ficha($id)'>Ficha </a>"; 
    if( $status == 1 || $status == 2 ) {
        $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm me-1' onClick='excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
        $acoes .=  "<a href='#!' class='btn btn-outline-dark btn-sm' onClick='fechar($id)'><i class='fa-solid fa-lock'></i></a>";
    } else{
        $acoes .=  "<a href='#!' class='btn btn-outline-secondary btn-sm me-1' disabled><i class='fa-solid fa-trash-can'></i></a>";
        $acoes .=  "<a href='#!' class='btn btn-outline-secondary btn-sm' disabled'><i class='fa-solid fa-lock'></i></a>";
    }
    //
    if( $status == 1 )      $dsStatus = "<span class='badge bg-primary w-100 status'>Novo</span>";
    else if( $status == 2 ) $dsStatus = "<span class='badge bg-warning w-100 status'>Processo</span>";
    else if( $status == 9 ) $dsStatus = "<span class='badge bg-success w-100 status'>Concluído</span>";
    else if( $status == 0 ) $dsStatus = "<span class='badge bg-danger w-100 status'>Cancelado</span>";
    //
    if( $acompanhamento =='sim') $acompanhamento = "<span class='badge bg-success fc13 status'>$acompanhamento</span>";
    else if( $acompanhamento =='não') $acompanhamento = "<span class='badge bg-danger fc13 status'>$acompanhamento</span>";
    //
    if($identificacao=='anonimo') $identificacao = "<span class='badge bg-danger w-100 status'>Anônimo</span>";
    else if($identificacao=='identificado') $identificacao = "<span class='badge bg-primary w-100 status'>Identificado</span>";
    //
    //- TMA
    //
        if( $dias_ate_status > 0 ){
            $tma = $dias_ate_status;
        }
        elseif( $encerrado_em == null ){
            $tma = $dias_ate_hoje;
        } else{
            $tma = $dias_ate_encerramento;
        }
    //
    if($status == 0) $cor = "red";
    if($status == 1) $cor = "blue";
    if($status == 2) $cor = "black";
    if($status == 9) $cor = "DarkMagenta";
    //
    $dado[] = "<spam style='color: $cor'>$id</spam>";
    $dado[] = "<spam style='color: $cor'>$data_envio</spam>";
    $dado[] = "<spam style='color: $cor'>$identificacao</spam>";
    $dado[] = "<spam style='color: $cor'>$tipo_assedio</spam>";
    $dado[] = "<spam style='color: $cor'>$acompanhamento</spam>";
    $dado[] = $dsStatus;
    $dado[] = "<spam style='color: $cor'>$tma dias</spam>";
    $dado[] = $acoes;
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript
//

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
$conteudo = json_encode($output);
echo $conteudo;
exit;
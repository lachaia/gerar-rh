<?php
//
//- rh_ferias_aj1.php | Recupera dados FERIAS
//- (C)haia, 30/04/2025
//

session_start();

$idModulo = 12; // férias

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

if (empty($id)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

//- Recupera dados do Órgão
//
$sql = "SELECT 
                    P.nome, 
                    F.*, 
                    LEAST(12, TIMESTAMPDIFF(MONTH, F.inicio_aquisitivo, CURDATE())) * 2.5 AS dias_adquiridos,
                    DATEDIFF(F.fim_concessivo, CURDATE()) AS dias_para_vencer
                FROM rh_ferias F
                INNER JOIN rh_colaboradores C ON C.idColab = F.idColab
                INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                WHERE id = :id
                ORDER BY P.nome, F.inicio_aquisitivo";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $id);
$consulta->execute();
$linha = $consulta->fetch(PDO::FETCH_ASSOC);
extract( $linha );

if (isset($origem) && $origem == "visualizar") {
    $dados = implode(", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Período de Férias de $nome : Dados:( $dados )", "rh_ferias", $idModulo, $id);
}

if ($linha['dias_adquiridos'] > 0) {

    $d1 = (int) $linha['dias_parte1'];
    $d2 = (int) $linha['dias_parte2'];
    $d3 = (int) $linha['dias_parte3'];
    $total_usufruido = $d1 + $d2 + $d3;
    //
    $hoje = date('Y-m-d');
    $limite_alerta = date('Y-m-d', strtotime('+60 days'));
    $fimConcessivo = $linha['fim_concessivo'];
    //
    if ($total_usufruido >= 30) {
        $alerta = "<span class='badge bg-primary'>completas</span>";
    } else {
        //
        if ($fimConcessivo < $hoje && empty($linha['data_parte1'])) {
            $alerta = "<span class='badge bg-danger'>Restam " . (30 - $total_usufruido) . " dias a usufruir - Período vencido!</span>";
        } elseif ($fimConcessivo <= $limite_alerta) {
            $alerta = "<span class='badge bg-warning text-dark'>Restam " . (30 - $total_usufruido) . " dias - Menos de 60 dias</span>";
            //
        } elseif ($fimConcessivo > $hoje && $total_usufruido < 30) {
            $alerta = "<span class='badge bg-secondary'>Restam " . (30 - $total_usufruido) . " dias para fruir</span>";
        } else {
            $alerta = "<span class='badge bg-secondary'>Restam " . (30 - $total_usufruido) . " dias para fruir</span>";
        }
    }

} else {
    $alerta = "<span class='badge bg-danger'>Aguardando aquisição</span>";
}
$linha['alerta'] = $alerta;

$conn = null;
die(json_encode($linha, JSON_PRETTY_PRINT));

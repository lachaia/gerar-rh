<?php
//
//- rh_colab_aj8.php | Salva CARTÕES
//- (C)haia, 10/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

if ( empty($idColab) ) {
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
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
} else {
    header("location: logout.php");
}

// Monta a query
$sql = "UPDATE rh_colaboradores 
        SET vale_transporte = :vale_transporte, 
            vale_refeicao   = :vale_refeicao,
            idPlanoSaude    = :idPlanoSaude,
            idPlanoOdonto   = :idPlanoOdonto,
            planoFarmacia   = :planoFarmacia
        WHERE idColab = :idColab";

$stmt = $conn->prepare($sql);

// Executa passando os parâmetros
$sucesso = $stmt->execute([
    ':vale_transporte' => $vale_transporte,
    ':vale_refeicao'   => $vale_refeicao,
    ':idPlanoSaude'    => $idPlanoSaude,
    ':idPlanoOdonto'   => $idPlanoOdonto,
    ':idColab'         => $idColab,
    ':planoFarmacia'   => $planoFarmacia
]);

$retorno = [];

if ($sucesso) {
    $retorno['status'] = 1;
    $retorno['msg'] = '<div class="alert alert-success"><strong>Sucesso:</strong> Dados Atualizados!</div>';
} else {
    $retorno['status'] = 0;
    $retorno['msg'] = '<div class="alert alert-danger"><strong>Erro:</strong> ao Atualizar Dados!</div>';
}

echo json_encode($retorno);

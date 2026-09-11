<?php
//
//- rh_cv_aj2.php | CURRÍCULO | Salva CV - DIVERSIDADE
//- (C)haia, 02/04/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

/*
// TESTE DE RECEBIMENTO DE DADOS
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
/*
 rh_cv_aj2.php | 2025-04-02 17:41:19 
{
    "idPessoa": "26",               |     "idPessoa": "26",
    "idCV": "4",                    |     "idCV": "4",
    "cidade": "Guarapuava\/PR",     |     "cidade": "",
    "cidade_id": "3323",            |     "cidade_id": "",
    "cor": "Branca",                |     "cor": "",
    "pronome": "ele",               |     "pronome": "",
    "orientacao": "Heterossexual",  |     "orientacao": "",
    "identgenero": "Cisg\u00eanero" |     "identgenero": ""
}
*/
if (empty($idCV) || empty($idPessoa)) {
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
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

$sql = "SELECT idCV, idPessoa, idCidade, cor, pronome, orientacao, idGenero FROM rh_cv WHERE idCV = $idCV";
$consulta = $conn->prepare($sql);
$consulta->execute();
$registro = $consulta->fetch(PDO::FETCH_ASSOC);

if ($registro) {
    $dados_old = implode(", ", array_map('strval', $registro)); // Garante que todos sejam strings
}

if( empty($cidade_id)) $cidade_id = 0;

$sql = "UPDATE rh_cv SET 
            ultimaAtualizacao = :ultimaAtualizacao, 
            idCidade = :idCidade, 
            cor = :cor, 
            pronome = :pronome,
            orientacao = :orientacao,
            idGenero = :idGenero
        WHERE idCV = :idCV";

$stmt = $conn->prepare($sql);

$stmt->bindParam(':ultimaAtualizacao', $agora);
$stmt->bindParam(':idCidade', $d_cidade_id);
$stmt->bindParam(':cor', $cor);
$stmt->bindParam(':pronome', $pronome);
$stmt->bindParam(':orientacao', $orientacao);
$stmt->bindParam(':idGenero', $identgenero);
$stmt->bindParam(':idCV', $idCV);

$stmt->execute();

f_log("ALT", "ALTERAÇÃO no CV - Dados de Diversidade Dados Anteriores: ( $dados_old ) para: ($dados_novos)", "rh_cv", $idModulo, $idCV);

$msg = "<div class='alert alert-success'><strong>Successo!</strong> ao atualizar registro. ID: $idCV</div>";
$response = ["status" => false, "msg" => $msg];

$conn = null;

echo json_encode($response, JSON_PRETTY_PRINT);
exit;

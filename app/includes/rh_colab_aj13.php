<?php
//
//- rh_cargo_aj13.php | RECUPERA e devolve Registro do COLABORADORES
//- (C)haia, 15/04/2025
//

session_start();

$idModulo = 4; // COLABORADORES

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

extract($parametros);

// Validação básica
if (!isset($idColab) || empty($idColab)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Parâmetros!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "SELECT C.*, P.nome as nmColab 
FROM rh_colaboradores C
INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
WHERE C.idColab = $idColab";
$stmt = $conn->prepare($sql);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

die(json_encode($dados, JSON_PRETTY_PRINT)); // Retorna os dados em formato JSON para o JavaScript
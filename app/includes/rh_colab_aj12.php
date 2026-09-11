<?php
//
//- rh_colab_aj12.php | EXCLUIR Registro do COLABORADORES
//- (C)haia, 14/04/2025
//

session_start();

$idModulo = 4; // COLABORADORES

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

extract($parametros);

// Validação básica
if (!isset($id )) {
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

$sql = "SELECT * FROM rh_colaboradores WHERE idColab = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); 
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
} else {
    $stringDados = "Nenhum dado encontrado.";
}

$sql = "DELETE FROM rh_colaboradores WHERE idColab = $id";
$stmt = $conn->prepare($sql);

// Executa a EXCLUSÃO
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("EXC", "EXCLUSÃO de Colaborador no Sistema, ID: $id | Dados( $stringDados )", "rh_colaboradores", $idModulo, $id);

//
//- EXCLUI REGISTROS FILHOS - TABELAS AUXILIARES
//
    $sql = "DELETE FROM rh_dependentes WHERE idColab = $id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

$conn = null;
die(json_encode($response));
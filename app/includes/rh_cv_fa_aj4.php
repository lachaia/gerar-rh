<?php
//
//- rh_cv_af_aj4.php | EXCLUIR Registro do CURRICULUM - Formação Acadêmica
//- (C)haia, 26/03/2025
//

session_start();

$idModulo = 7; // Currículo Vitae

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

$sql = "SELECT C.*, P.nome 
            FROM rh_cv_fa C
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); 
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
} else {
    $stringDados = "Nenhum dado encontrado.";
}

$sql = "DELETE FROM rh_cv_fa WHERE id = $id";
$stmt = $conn->prepare($sql);

// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}
$nome = $dados['nome'];
f_log("EXC", "EXCLUSÃO de Formação Acadêmica no CV do $nome | Dados( $stringDados )", "rh_cf_fa", $idModulo, $id);

$conn = null;
die(json_encode($response));
<?php
//
//- new_aj5_exp.php | EXCLUIR Registro do CURRICULUM - Experiência Profissional
//- (C)haia, 27/03/2025 | (U) 2026-04-13 
//

session_start();

$idModulo = 7; // Currículo Vitae

require_once "../app/includes/conexao_gerar.php"; 
require_once "../app/includes/f_logs.php";

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);
/*
require_once "../app/includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
*/
// Validação básica
if (!isset($id )) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Parâmetros!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if( isset($_SESSION['idLogin'])   && !empty($_SESSION['idLogin']) )   $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
if( isset($_SESSION['idEmpresa']) && !empty($_SESSION['idEmpresa']) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra excluir
//- experiência do currículo de qualquer pessoa só sabendo o id do registro.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];
$id = (int) $id;

$sql = "SELECT E.*, P.nome
            FROM rh_cv_exp E
            INNER JOIN rh_pessoas P on P.idPessoa = E.idPessoa
            WHERE E.id = :id AND E.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id, 'idPessoa' => $idPessoa]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if (!$dados) {
    die(json_encode(["status" => false, "msg" => "Registro não encontrado."]));
}

$stringDados = implode(", ", $dados);

$sql = "DELETE FROM rh_cv_exp WHERE id = :id AND idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);

// Executa a exclusão
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}
$nome = $dados['nome'];
f_log("EXC", "EXCLUSÃO de Experiência Profissional no CV do $nome | Dados( $stringDados )", "rh_cv_exp", $idModulo, $id);

$conn = null;
die(json_encode($response));
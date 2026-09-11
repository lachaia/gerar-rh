<?php
//
//- new_aj11_idi.php | EXCLUIR Registro do CURRICULUM - IDIOMAS
//- (C)haia, 26/03/2025 | (U) 14/04/2026
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

if( isset( $_SESSION['idLogin'] ) ) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
if( isset( $_SESSION['idEmpresa'] ) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;

include_once "../app/includes/conexao_gerar.php";
include_once "../app/includes/f_logs.php";

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra excluir
//- idioma do currículo de qualquer pessoa só sabendo o id do registro.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoaSessao = (int) $_SESSION['candidato_idPessoa'];
$id = (int) $id;

$sql = "SELECT I.*, P.nome
            FROM rh_cv_idiomas I
            INNER JOIN rh_pessoas P on P.idPessoa = I.idPessoa
            WHERE I.id = :id AND I.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id, 'idPessoa' => $idPessoaSessao]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if (!$dados) {
    die(json_encode(["status" => false, "msg" => "Registro não encontrado."]));
}

$stringDados = implode(", ", $dados);
$nome = $dados['nome'];

$sql = "DELETE FROM rh_cv_idiomas WHERE id = :id AND idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':idPessoa', $idPessoaSessao, PDO::PARAM_INT);

// Executa a exclusão
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("EXC", "EXCLUSÃO de Idioma no CV do $nome | Dados( $stringDados )", "rh_cv_idiomas", $idModulo, $id);

$conn = null;
die(json_encode($response));
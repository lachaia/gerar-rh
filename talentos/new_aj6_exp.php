<?php
//
//- new_ah6_exp.php | Recupera dados CURRÍCULO: EXPERIÊNCIA PROFISSIONAL para Edição/Visualização
//- (C)haia, 26/03/2025 | (U) 2026-04-13
//

session_start();

$idModulo = 7; // CURRICULUM

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

    if( isset($_SESSION['idLogin']) ) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
    include_once "../app/includes/conexao_gerar.php";
    include_once "../app/includes/f_logs.php";

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra ler
//- experiência do currículo de qualquer pessoa só sabendo o id do registro.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

$sql = "SELECT E.*, P.nome, DATE_FORMAT(E.criado_em, '%d/%m/%Y %H:%i') AS dtLogin
            FROM rh_cv_exp E
            INNER JOIN rh_pessoas P ON P.idPessoa = E.idPessoa WHERE E.id = :id AND E.idPessoa = :idPessoa";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $id, PDO::PARAM_INT);
$consulta->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$consulta->execute();
$linha = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$linha) {
    die(json_encode(["status" => false, "msg" => "Registro não encontrado."]));
}

$nome = $linha['nome'];

if (isset($origem) && $origem == "visualizar") {
    $dados = implode( ", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Experiência Profissional de $nome: Dados:( $dados )", "rh_cv_exp", $idModulo, $id);
}

$conn = null;
die(json_encode($linha, JSON_PRETTY_PRINT));

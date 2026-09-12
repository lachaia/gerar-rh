<?php
//
//- rh_docs_del_aj.php | Exclui DOCUMENTO
//- (C) 2024-07-02 by Chaia. | (U) 2025-06-11
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

$caminho = __DIR__ . '/chaves/gerarocr-ba77063bf0b6.json';
putenv("GOOGLE_APPLICATION_CREDENTIALS=$caminho");

include_once "../includes/conexao_gerar.php";
include_once "../includes/debug.php";
include_once "../includes/f_logs.php";

$idModulo = 14; // RH-GED
$agora = date("Y-m-d H:i:s");

$idUsuario = $_SESSION['idUsuario'];
$idLogin   = $_SESSION['idLogin'];

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
extract($dados);

//
//- Recupera dados antigos
//
$sql = "SELECT * FROM rh_documentos WHERE idDoc = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
$dadosAntigos = "Dados Antigos: " . implode(', ', $dados);
// idPessoa do arquivo físico é sempre o dono real do documento (na tabela),
// nunca o da sessão de quem está excluindo — já era um bug latente que
// deixava o arquivo físico órfão em disco.
$idPessoa = $dados['idPessoa'] ?? null;

try {
    $sql = "DELETE FROM rh_documentos WHERE idDoc = :id";
    $stmt_files = $conn->prepare($sql);
    $stmt_files->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_files->execute();

    $mensagem = '<div class="alert alert-success text-center">Arquivo excluído com <b>sucesso</b>.</div > ';
} catch (PDOException $e) {
    $mensagem = '<div class="alert alert-danger text-center"><strong>Erro!</strong> Não foi possível processar a requisição! ' . $e->getMessage() . '</div > ';
}

//
//- Apaga o arquivo físico
//
    $arquivo = "../docs/pessoa_{$idPessoa}/" . basename($dados['arquivo'] ?? '');

    if (is_file($arquivo)) {
        unlink($arquivo);
    }

//
//- REGISTRA LOG 
//
$historico = "Excluído documento $arquivo | Dados Antigos: $dadosAntigos";
$sql = "INSERT INTO rh_logs (idLogin, dtOper, oper, historico, tabela, idModulo, idOperacao) 
        VALUES (:idLogin, :dtOper, 'DEL', :historico, 'rh_documentos', :idModulo, :idOperacao)";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':idLogin'     => $idLogin,
    ':dtOper'      => $agora,
    ':historico'   => $historico,
    ':idModulo'    => $idModulo,
    ':idOperacao'  => $id
]);


$conn = null;
die(json_encode(["status" => true, "msg" => $mensagem]));

<?php
//
//- new_aj9_fa.php | EXCLUIR Registro do CURRICULUM - Formação Acadêmica
//- (C)haia, 26/03/2025 | (U) 2026-04-14
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

    if( isset($_SESSION['idLogin']) ) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
    if( isset($_SESSION['idEmpresa']) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;
    include_once "../app/includes/conexao_gerar.php";
    include_once "../app/includes/f_logs.php";

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra excluir
//- formação do currículo de qualquer pessoa só sabendo o id do registro.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoaSessao = (int) $_SESSION['candidato_idPessoa'];
$id = (int) $id;

$sql = "SELECT C.*, P.nome, D.arquivo AS arquivo_doc
            FROM rh_cv_fa C
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT JOIN rh_documentos D ON D.idDoc = C.idDoc
            WHERE C.id = :id AND C.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id, 'idPessoa' => $idPessoaSessao]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
} else {
    $stringDados = "Nenhum dado encontrado.";
}

$idPessoa = (int)($dados['idPessoa'] ?? 0);
$idDoc = (int)($dados['idDoc'] ?? 0);
$arquivoDoc = $dados['arquivo_doc'] ?? '';

if (!$dados) {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> registro nao encontrado!</div>";
    $response = ["status" => false, "msg" => $msg ];
} else {
    $ok = true;

    if ($idDoc > 0) {
        if ($idPessoa > 0 && !empty($arquivoDoc)) {
            $arquivoFisico = "../app/docs/pessoa_$idPessoa/" . $arquivoDoc;
            if (file_exists($arquivoFisico) && !@unlink($arquivoFisico)) {
                $ok = false;
            }
        }

        $sql = "DELETE FROM rh_documentos WHERE idDoc = :idDoc";
        $stmt = $conn->prepare($sql);
        if (!$stmt->execute(['idDoc' => $idDoc])) {
            $ok = false;
        }
    }

    if ($ok) {
        $sql = "DELETE FROM rh_cv_fa WHERE id = :id AND idPessoa = :idPessoa";
        $stmt = $conn->prepare($sql);
        $ok = $stmt->execute(['id' => $id, 'idPessoa' => $idPessoaSessao]);
    }

    if ($ok) {
        $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
        $response = ["status" => true, "msg" => $msg ];
    } else {
        $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir documento/registro!</div>";
        $response = ["status" => false, "msg" => $msg ];
    }
}
$nome = $dados['nome'];
f_log("EXC", "EXCLUSÃO de Formação Acadêmica no CV do $nome | Dados( $stringDados )", "rh_cf_fa", $idModulo, $id);

$conn = null;
die(json_encode($response));
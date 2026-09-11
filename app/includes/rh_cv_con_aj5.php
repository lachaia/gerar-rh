<?php
//
//- rh_cv_con_aj5.php | CURRÍCULO | Salva ALTERAÇÃO de Conquista
//- (C)haia, 26/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM
$agora = date("Y-m-d H:i:s");

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

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
 rh_cv_con_aj5.php | 2025-03-31 17:43:43 
{
    "idPessoa": "1",
    "idConq": "1",
    "idConqTipo": "3",
    "ano": "2012",
    "titulo": "Funcion\u00e1rio do Ano",
    "descricao": "Cooperatiava Coolaeste | Eleito colaborador do ano pelos colegas de trabalho"
}

*/

if (empty($idConq) || empty($idPessoa) || empty($idConqTipo) || empty($ano) || empty($titulo) || empty($descricao)) {
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
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

//
// Carrega dados antigos
$sql = "SELECT C.*, P.nome, D.arquivo
            FROM rh_cv_conq C 
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT OUTER JOIN rh_documentos D ON D.idDoc = C.idDoc
            WHERE C.id = $idConq";
$stmt = $conn->prepare($sql);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
$dados_old = implode(", ", $linha);

$idDocOld = $linha['idDoc'];

//- TRATAMENTO DO ARQUIVO ENVIADO
//

if (isset($_FILES['arquivoCert']) && $_FILES['arquivoCert']['error'] === UPLOAD_ERR_OK) {
    // Habemos Files
    $arquivo = $_FILES['arquivoCert'];
    //
    $uploadDir = "../docs/pessoa_$idPessoa/";
    $fileName = basename($arquivo['name']);
    $fileSize = $arquivo['size'];
    $extensao = pathinfo($arquivo["name"], PATHINFO_EXTENSION);
    //
    $nome_temporario = $_FILES['arquivoCert']['tmp_name'];
    $nome_arquivo = uniqid("cert_") . "." . strtolower($extensao);
    $destino = $uploadDir . $nome_arquivo;

    if (!move_uploaded_file($nome_temporario, $destino)) {
        throw new Exception("Erro ao mover o arquivo enviado.");
    }
    //--- SALVA RH_DOCUMENTOS
    //
    $idTipoDoc = 9; // Tipo: 9 --> Certificado
    $sql = "SELECT * FROM rh_docs_tipo WHERE idTipoDoc = $idTipoDoc";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($result['validade'])) $meses = 120;
    else $meses = $result['validade']; // número de meses a adicionar à data

    $validade = new DateTime($agora); // Converter a string em um objeto DateTime
    if ($meses > 0) $validade->modify("+$meses months");
    $validade_str = $validade->format("Y-m-d"); // Converte para string no formato YYYY-MM-DD

    $status = 1; // Sobe como Válido (pois foi o próprio RH que subiu)

    $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, data_validade, arquivo, extensao, tamanho, status, idLoginAprova, nome_original)
                    VALUES ( :idEmpresa, :idPessoa, :idTipoDoc, :data, :data_validade, :arquivo, :extensao, :tamanho, :status, :idLogin, :original )";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':idEmpresa',     $idEmpresa,    PDO::PARAM_INT);
    $stmt->bindParam(':idLogin',       $idLogin,      PDO::PARAM_INT);
    $stmt->bindParam(':idPessoa',      $idPessoa,     PDO::PARAM_INT);
    $stmt->bindParam(':idTipoDoc',     $idTipoDoc,    PDO::PARAM_INT);
    $stmt->bindParam(':data',          $agora,        PDO::PARAM_STR);
    $stmt->bindParam(':data_validade', $validade_str, PDO::PARAM_STR);
    $stmt->bindParam(':arquivo',       $nome_arquivo, PDO::PARAM_STR);
    $stmt->bindParam(':extensao',      $extensao,     PDO::PARAM_STR);
    $stmt->bindParam(':tamanho',       $fileSize,     PDO::PARAM_STR);
    $stmt->bindParam(':status',        $status,       PDO::PARAM_INT);
    $stmt->bindParam(':original',      $fileName,     PDO::PARAM_STR);
    $stmt->execute();
    $idDoc = $conn->lastInsertId();
    //
    // - Exclui arquivo anterior, já que substitui
    //
    if ($idDocOld > 0) {
        $sql = "DELETE FROM rh_documentos WHERE idDoc = $idDocOld";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        //
        $arquivo = "../docs/pessoa_$idPessoa/" . $linha['arquivo'];
        if (file_exists($arquivo)) {
            unlink($arquivo);
        }
    }
    //
    $sql = "UPDATE rh_cv_conq SET idConqTipo = :idConqTipo, titulo = :titulo, ano = :ano, 
                descricao = :descricao, idDoc = $idDoc WHERE id = :idConq";
} else {
    $sql = "UPDATE rh_cv_conq SET idConqTipo = :idConqTipo, titulo = :titulo, ano = :ano, 
                descricao = :descricao WHERE id = :idConq";
}
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idConq', $idConq);
$consulta->bindParam(':idConqTipo', $idConqTipo);
$consulta->bindParam(':titulo', $titulo);
$consulta->bindParam(':ano', $ano);
$consulta->bindParam(':descricao', $descricao);

// Executa a inserção
if ($consulta->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao alterar registro. ID: $idConq</div>";
    //
    $dados = implode(", ", $parametros);
    //
    $response = ["status" => true, "msg" => $msg, "dados" => $linha];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}
$nome = $linha['nome'];
f_log("ALT", "ALTERAÇÃO de CURRÍCULO (Certificados e Conquistas) de $nome, Dados Anteriores ($dados_old), Dados Novos ($dados)", "rh_cv_exp", $idModulo, $idConq);

$conn = null;
die(json_encode($response));

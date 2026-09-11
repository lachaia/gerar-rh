<?php
//- new_aj3.php | SALVA ARQUIVO DO CV
//- (C)haia, 2025-04-03 | (U) 2026-04-13

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$idModulo = 7; // Currículo Vitae

session_start();

    $agora = date("Y-m-d H:i:s");
    //
    include_once "../app/includes/conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "../app/includes/f_linha_do_tempo.php";
    include_once "../app/includes/f_logs.php";

$idLogin = 0;
$idEmpresa = 1;

// Verifica se um arquivo foi enviado
if (!isset($_FILES["arquivo"]) || $_FILES["arquivo"]["error"] != UPLOAD_ERR_OK) {
    $resposta = [
        "status" => false, 
        "msg" => "Nenhum arquivo enviado ou erro no upload."
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}

//- idPessoa vem da sessão aberta em new_aj1.php (dedupe por CPF) - nunca de um
//- campo do cliente, senão dá pra trocar o currículo de qualquer pessoa só
//- sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    $resposta = [
        "status" => false,
        "msg" => "Sessão expirada. Verifique o CPF novamente."
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

if (!empty($_FILES['arquivo']['name'])) {
    $arquivo = $_FILES['arquivo'];
} else {
    $resposta = [
        "status" => false, 
        "msg" => "Nenhum arquivo enviado."
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}
//
//- RECUPERA DADOS DO ARQUIVO ANTERIOR
//
    $sql = "SELECT C.arquivo as nmArquivo, P.nome as nmPessoa
                FROM RH.rh_cv C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                WHERE C.idPessoa = :idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($result);
//
//- DADOS ENVIADOS PELO POST[]
//
    $fileName = basename($arquivo['name']);
    $fileSize = $arquivo['size'];
    $extensao = pathinfo($arquivo["name"], PATHINFO_EXTENSION);
    $extensoesPermitidas = ["pdf", "doc", "docx"];
    $fileTmpPath = $arquivo['tmp_name'];

if (!in_array(strtolower($extensao), $extensoesPermitidas)) {
    $resposta = [
        "status" => false, 
        "msg" => "Formato de arquivo não permitido."
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}

// Nomeia o arquivo de forma única
$nomeArquivo = "CV_" . $idPessoa . "_" . time() . "." . $extensao;

$uploadDir = "../app/docs/pessoa_$idPessoa/";

//
//- Se já existe arquivo de CV anterior, exclui
if (!empty($nmArquivo)) {
    $caminhoArquivo = $uploadDir . $nmArquivo;
    if (file_exists($caminhoArquivo)) {
        unlink($caminhoArquivo); // Remove o arquivo anterior
    }
    //
    //- EXCLUI de RH_DOCUMENTOS
    //
        $sql = "DELETE FROM RH.rh_documentos WHERE idPessoa = :idPessoa AND idTipoDoc = 4";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();
}
//
// Criando o diretório se não existir
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Definindo o caminho completo do arquivo
$destPath = $uploadDir . $nomeArquivo;

if (move_uploaded_file($fileTmpPath, $destPath)) {

    // Aqui você pode salvar $caminhoArquivo no banco de dados associado ao idPessoa
    $resposta = [
        "status" => true, 
        "msg" => "Upload realizado com sucesso!", "caminho" => $fileName];
        $ok = true;
    //
    //- LINHA DO TEMPO
    //
    $tipo = 14; // Documento anexado
    $descricao = "Inserido arquivo Currículo Vitae: $fileName";
    f_ldt($tipo, $idPessoa, $descricao);
} else {

    $resposta = [
        "status" => false, 
        "msg" => "Erro ao mover o arquivo para o servidor."];
    $ok = false;
}

//-- INSERE DADOS NO BANCO DE DADOS
if ($ok==true) {
    //
    $sql = "UPDATE rh_cv SET arquivo = :arquivo WHERE idPessoa = :idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':arquivo', $nomeArquivo, PDO::PARAM_STR);
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->execute();
    //
    //- DOCUMENTO DO CV
    // 

    $idTipoDoc = 4; // Tipo: 4 --> CV
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

    $descricao = "Currículo de Candidato: $nmPessoa [ $fileName ]";

    $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, descricao, idTipoDoc, data, data_validade, arquivo, extensao, tamanho, status, idLoginAprova, nome_original, origem)
                    VALUES ( :idEmpresa, :idPessoa, :descricao, :idTipoDoc, :data, :data_validade, :arquivo, :extensao, :tamanho, :status, :idLogin, :original, 'CVC' )";
    $stmt = $conn->prepare($sql);

    // Legenda: CVC - Currículo Vitae de Candidato

    $stmt->bindParam(':idEmpresa',     $idEmpresa,    PDO::PARAM_INT);
    $stmt->bindParam(':idLogin',       $idLogin,      PDO::PARAM_INT);
    $stmt->bindParam(':idPessoa',      $idPessoa,     PDO::PARAM_INT);
    $stmt->bindParam(':descricao',     $descricao,    PDO::PARAM_STR);
    $stmt->bindParam(':idTipoDoc',     $idTipoDoc,    PDO::PARAM_INT);
    $stmt->bindParam(':data',          $agora,        PDO::PARAM_STR);
    $stmt->bindParam(':data_validade', $validade_str, PDO::PARAM_STR);
    $stmt->bindParam(':arquivo',       $nomeArquivo,  PDO::PARAM_STR);
    $stmt->bindParam(':extensao',      $extensao,     PDO::PARAM_STR);
    $stmt->bindParam(':tamanho',       $fileSize,     PDO::PARAM_STR);
    $stmt->bindParam(':status',        $status,       PDO::PARAM_INT);
    $stmt->bindParam(':original',      $fileName,     PDO::PARAM_STR);

    $stmt->execute();
    $idDoc = $conn->lastInsertId();

    f_log("INC", "INCLUSÃO de Documento: Currículo Vitae de $nmPessoa - Dados( $fileName )", "rh_documentos", $idModulo, $idDoc);
}

$msg = '<div class="alert alert-success"><strong>Sucesso!</strong> Arquivo inserido com sucesso!</div>';
$resposta = [
    'status'=> true,
    'msg' => $msg,
    'idPessoa' => $idPessoa,
    'arquivo' => $nomeArquivo
];
die( json_encode( $resposta) );
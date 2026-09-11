<?php
// rh_colab_aj4.php - Upload de arquivo da DDIR-Declaração de Dependentes par IR
// (C)haia, 08/04/2025

//header("Content-Type: application/json");

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

// TESTE DE RECEBIMENTO DE DADOS
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
$retorno['dsArquivo'] = "teste.pdf";
die ( json_encode( $retorno, JSON_PRETTY_PRINT ) );
*/

$retorno = ["status" => 0, "msg" => "Erro inesperado."];

// Validações
if (!isset($_FILES['arquivo_ddir']) || $_FILES['arquivo_ddir']['error'] != 0) {
    $retorno['msg'] = "Nenhum arquivo enviado ou erro no upload.";
    die(json_encode($retorno));
}

if (!isset($_POST['idPessoa']) || !is_numeric($_POST['idPessoa'])) {
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
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_upload_seguro.php";
} else {
    header("location: logout.php");
    exit();
}

$validacao = upload_seguro_validar($_FILES['arquivo_ddir'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
if ($validacao !== true) {
    die(json_encode(["status" => 0, "msg" => $validacao]));
}

$sql = "SELECT P.nome as nmPessoa, ifnull(C.arquivo_ddir,'') as nmArquivo 
            FROM rh_pessoas P 
            LEFT OUTER JOIN rh_colaboradores C on C.idPessoa = P.idPessoa
            WHERE P.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
extract($result);

$arquivo = $_FILES['arquivo_ddir'];
$fileSize = $arquivo['size'];
$extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
$original = basename($arquivo['name']);

// Caminho de destino (ajuste conforme sua estrutura)

$pastaDestino = "../docs/pessoa_$idPessoa/";
if (!is_dir($pastaDestino)) {
    mkdir($pastaDestino, 0777, true);
}

$substituido = 0;

// Verifica se o arquivo já existe e apaga o antigo
if (!empty($nmArquivo)) {
    $arquivoAntigo = $pastaDestino . $nmArquivo;
    if (file_exists($arquivoAntigo)) {
        unlink($arquivoAntigo); // Apaga o antigo
    }
    $sql = "DELETE FROM rh_documentos WHERE idPessoa = :idPessoa AND arquivo like :nmArquivo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->bindParam(':nmArquivo', $nmArquivo, PDO::PARAM_STR);
    $stmt->execute();
    $substituido = 1;
}

// Gera novo nome de arquivo

$nomeArquivo = "ddir_" . $idPessoa . "_" . time() . "." . $extensao;
$caminhoCompleto = $pastaDestino . $nomeArquivo;

// Move o novo arquivo
if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
    //
    // Atualiza no banco
    //
    $sql = "UPDATE rh_colaboradores SET arquivo_ddir = ? WHERE idPessoa = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$nomeArquivo, $idPessoa])) {
        $retorno['status'] = 1;
        $retorno['msg'] = '<div class="alert alert-success">
            <strong>Erro!</strong> Salvo com sucessso!
            </div>';
    } else {
        $retorno['msg'] = '<div class="alert alert-danger">
            <strong>Erro!</strong> Erro ao Atualizar BD!
            </div>';
    }
    //
    //- LINHA DO TEMPO
    //
    $tipo = 14; // Documento anexado
    if( $substituido == 0 ) $descricao = "Inserido arquivo (DDIR) Declaração de Dependentes para efeito de IR: $nomeArquivo";
        else $descricao = "Substituído arquivo (DDIR) Declaração de Dependentes para efeito de IR: $nomeArquivo";
    f_ldt($tipo, $idPessoa, $descricao);
    //
    //- DOCUMENTOS ANEXADOS
    //
    //
    //- DOCUMENTO DO CV
    // 

    $idTipoDoc = 6; // Tipo: 6 --> Declaração de Dependendentes para IR
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
    $stmt->bindParam(':arquivo',       $nomeArquivo,  PDO::PARAM_STR);
    $stmt->bindParam(':extensao',      $extensao,     PDO::PARAM_STR);
    $stmt->bindParam(':tamanho',       $fileSize,     PDO::PARAM_STR);
    $stmt->bindParam(':status',        $status,       PDO::PARAM_INT);
    $stmt->bindParam(':original',      $original,     PDO::PARAM_STR);

    $stmt->execute();
    $idDoc = $conn->lastInsertId();

    f_log("INC", "INCLUSÃO de Declaração de Dependentes para IR de $nmPessoa - Dados( $original )", "rh_documentos", $idModulo, $idDoc);
    //
    $retorno['dsArquivo'] = $nomeArquivo;
    //
} else {
    $retorno['msg'] = '<div class="alert alert-danger">
            <strong>Erro!</strong> Erro ao Mover o Arquivo!
            </div>';
}

echo json_encode($retorno, JSON_PRETTY_PRINT);

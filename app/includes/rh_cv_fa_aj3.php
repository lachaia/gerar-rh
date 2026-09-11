<?php
//
//- rh_cv_af_aj3.php | CURRÍCULO | Salva novO CURSO (formação acadêmica) 
//- (C)haia, 26/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );



/*
// TESTE DE RECEBIMENTO DE DADOS
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );

rh_pessoa_cv_aj3.php | 2025-03-26 09:20:36 
{
    "idPessoa": "1",
    "curso": "ENGENHARIA CIVIL",
    "idInstituicao": "1",
    "idNivel": "3",
    "ano": "2004"
}
*/

if( ! isset($curso) || empty( $idInstituicao ) || empty( $idNivel ) || empty( $ano ) ){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

if( isset($_SESSION['idLogin']) ){
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}

// Upload do DIPLOMA (se enviado)
$caminho_arquivo = null;
if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
    //
    $arquivo = $_FILES['arquivo'];
    //
    $uploadDir = "../docs/pessoa_$idPessoa/";
    $fileName = basename($arquivo['name']);
    $fileSize = $arquivo['size'];
    $extensao = pathinfo($arquivo["name"], PATHINFO_EXTENSION);
    //
    $nome_temporario = $_FILES['arquivo']['tmp_name'];
    $nome_arquivo = uniqid("cert_") . "." . strtolower($extensao);
    $destino = $uploadDir . $nome_arquivo;

    if (!move_uploaded_file($nome_temporario, $destino)) {
        throw new Exception("Erro ao mover o arquivo enviado.");
    }

    $caminho_arquivo = $destino;
    
    //
    //-- SALVA ARQUIVO - Tabela de Arquivos
    //

    $idTipoDoc = 10; // Tipo: 10 --> Diploma
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
}else{
    $idDoc = 0;
}

$sql = "INSERT INTO rh_cv_fa ( idEmpresa, idPessoa, idInstituicao, idNivel, curso, ano_conclusao, idLogin, idDoc) 
            VALUES (:idEmpresa, :idPessoa, :idInstituicao, :idNivel, :curso, :ano_conclusao, :idLogin, :idDoc)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idEmpresa', $idEmpresa );
$consulta->bindParam(':idPessoa', $idPessoa );
$consulta->bindParam(':idInstituicao', $idInstituicao );
$consulta->bindParam(':idNivel', $idNivel );
$consulta->bindParam(':curso', $curso );
$consulta->bindParam(':ano_conclusao', $ano );
$consulta->bindParam(':idLogin', $idLogin );
$consulta->bindParam(':idDoc', $idDoc );

// Executa a inserção
if ($consulta->execute()) {
    $idCurso = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idCurso</div>";
    //
    $sql = "SELECT * FROM rh_cv_fa WHERE id = $idCurso";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados = implode(", ", $linha);
    //
    $response = ["status" => true, "msg" => $msg, "dados"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("INC", "INCLUSÃO no CV de Formação Adadêmica): Dados( $dados )", "rh_cv_fa", $idModulo, $idCurso);

$conn = null;
die(json_encode($response));
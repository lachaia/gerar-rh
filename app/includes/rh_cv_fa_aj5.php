<?php
//
//- rh_cv_af_aj5.php | CURRÍCULO | Salva ALTERAÇÃO de CURSO (formação acadêmica) 
//- (C)haia, 26/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

$agora = date("Y-m-d H:i:s");

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
 rh_cv_fa_aj5.php | 2025-05-14 16:17:18 
{
    "idCurso": "6",
    "curso": "M\u00e9todos Num\u00e9ricos em Engenharia",
    "idInstituicao": "4",
    "idNivel": "5",
    "ano": "2004"
}
*/

if( empty($idCurso) || empty($curso) || empty( $idInstituicao ) || empty( $idNivel ) || empty( $ano ) ){
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
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}

//
// Carrega dados antigos
$sql = "SELECT C.*, P.nome, D.arquivo 
            FROM rh_cv_fa C 
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT OUTER JOIN rh_documentos D ON D.idDoc = C.idDoc
            WHERE id = $idCurso";
$stmt = $conn->prepare($sql);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);
$dados_old = implode(", ", $linha);

$idDocOld = $linha['idDoc'];
$idPessoa = $linha['idPessoa'];

if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
    // Habemos Files
    $arquivo = $_FILES['arquivo'];
    //
    $uploadDir = "../docs/pessoa_$idPessoa/";
    $fileName = basename($arquivo['name']);
    $fileSize = $arquivo['size'];
    $extensao = pathinfo($arquivo["name"], PATHINFO_EXTENSION);
    //
    $nome_temporario = $_FILES['arquivo']['tmp_name'];
    $nome_arquivo = uniqid("dipl_") . "." . strtolower($extensao);
    $destino = $uploadDir . $nome_arquivo;

    if (!move_uploaded_file($nome_temporario, $destino)) {
        throw new Exception("Erro ao mover o arquivo enviado.");
    }
    //--- SALVA RH_DOCUMENTOS
    //
    $idTipoDoc = 10; // Tipo: 10 --> Diplomas
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
    $sql = "UPDATE rh_cv_fa SET idInstituicao=:idInstituicao, idNivel=:idNivel, curso=:curso, 
            ano_conclusao=:ano_conclusao, idDoc = $idDoc WHERE id = :idCurso";  
} else {
    $sql = "UPDATE rh_cv_fa SET idInstituicao=:idInstituicao, idNivel=:idNivel, curso=:curso, 
            ano_conclusao=:ano_conclusao WHERE id = :idCurso";
}

$consulta = $conn->prepare($sql);
$consulta->bindParam(':idCurso', $idCurso );
$consulta->bindParam(':idInstituicao', $idInstituicao );
$consulta->bindParam(':idNivel', $idNivel );
$consulta->bindParam(':curso', $curso );
$consulta->bindParam(':ano_conclusao', $ano );

// Executa a inserção
if ($consulta->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao alterar registro. ID: $idCurso</div>";
    //
    $dados = implode(", ", $parametros);
    //
    $response = ["status" => true, "msg" => $msg, "dados"=> $linha ];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}
$nome = $linha['nome'];
f_log("ALT", "ALTERAÇÃO de Formação Acadêmica de $nome, Dados Anteriores ($dados_old), Dados Novos ($dados)", "rh_cargos", $idModulo, $idCurso);

$conn = null;
die(json_encode($response));
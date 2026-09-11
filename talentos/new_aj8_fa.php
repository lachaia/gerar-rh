<?php
//
//- new_aj8_af.php | CURRÍCULO | Salva formação acadêmica
//- (C)haia, 26/03/2025 | (U) 2026-04-14
//

//header('Content-Type: application/json');

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

    if( isset($_SESSION['idLogin']) ) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
    if( isset($_SESSION['idEmpresa']) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;
    include_once "../app/includes/conexao_gerar.php";
    include_once "../app/includes/f_logs.php";
    include_once "../app/includes/f_upload_seguro.php";

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra incluir
//- formação no currículo de qualquer pessoa só sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

    $agora = date("Y-m-d H:i:s");
    //

// Upload do DIPLOMA (se enviado)
$caminho_arquivo = null;
if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
    $validacao = upload_seguro_validar($_FILES['arquivo'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
    if ($validacao !== true) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => $validacao]));
    }
    $arquivo = $_FILES['arquivo'];

    // 1. Defina o caminho da pasta
    $uploadDir = "../app/docs/pessoa_$idPessoa/";
    
    // 2. VERIFICAÇÃO CRÍTICA: Se a pasta não existe, cria ela
    if (!is_dir($uploadDir)) {
        // o parâmetro true permite criar pastas recursivamente (ex: cria 'docs' e depois 'pessoa_X')
        mkdir($uploadDir, 0777, true); 
    }

    $fileName = basename($arquivo['name']);
    $fileSize = $arquivo['size'];
    $extensao = pathinfo($arquivo["name"], PATHINFO_EXTENSION);
    
    $nome_temporario = $_FILES['arquivo']['tmp_name'];
    $nome_arquivo = uniqid("cert_") . "." . strtolower($extensao);
    $destino = $uploadDir . $nome_arquivo;

    // 3. Agora o movimento deve funcionar
    if (!move_uploaded_file($nome_temporario, $destino)) {
        throw new Exception("Erro ao mover o arquivo enviado.");
    }

    $caminho_arquivo = $destino;
    
    //
    //-- SALVA ARQUIVO - Tabela de Arquivos
    //

    //- Busca Tipo do Arquivo
        $idTipoDoc = 10; // Tipo: 10 --> Diploma
        $sql = "SELECT * FROM rh_docs_tipo WHERE idTipoDoc = $idTipoDoc";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result['validade'])) $meses = 120;
        else $meses = $result['validade']; // número de meses a adicionar à data
        //
        $validade = new DateTime($agora); // Converter a string em um objeto DateTime
        if ($meses > 0) $validade->modify("+$meses months");
        $validade_str = $validade->format("Y-m-d"); // Converte para string no formato YYYY-MM-DD

    //- Busca nome do Candidato
        $sql = "SELECT nome as nmPessoa FROM rh_pessoas WHERE idPessoa = $idPessoa";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nmPessoa = $result['nmPessoa'];

    $status = 1; // Sobe como Válido (pois foi o próprio RH que subiu)
    $descricao = "Diploma de $curso de $nmPessoa";

    $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, data_validade, arquivo, extensao, tamanho, status, idLoginAprova, nome_original, descricao)
                    VALUES ( :idEmpresa, :idPessoa, :idTipoDoc, :data, :data_validade, :arquivo, :extensao, :tamanho, :status, :idLogin, :original, :descricao )";
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
    $stmt->bindParam(':descricao',     $descricao,    PDO::PARAM_STR);

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
// Executa a inserção da Formação Acadêmica
if ($consulta->execute()) {
    $idCurso = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Sucesso!</strong> Registro inserido.</div>";
    
    // Busca os dados para retornar à grid (JOINs corrigidos)
    $sql = "SELECT F.id, F.idPessoa, F.curso, F.ano_conclusao, I.sigla, N.nivel 
            FROM rh_cv_fa F
            INNER JOIN rh_fa_instituicoes I on I.idInstituicao = F.idInstituicao
            INNER JOIN rh_fa_niveis N on N.idNivel = F.idNivel
            WHERE F.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idCurso, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    // Converte para string apenas para o LOG
    $log_dados = $linha ? implode(", ", $linha) : "Erro ao recuperar dados para log";

    // Registra o LOG dentro do sucesso
    f_log("INC", "INCLUSÃO no CV de Formação Acadêmica: Dados ($log_dados)", "rh_cv_fa", $idModulo, $idCurso);

    $response = ["status" => true, "msg" => $msg, "d" => $linha];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro</strong> ao inserir registro de formação!</div>";
    $response = ["status" => false, "msg" => $msg];
}

$conn = null;
die(json_encode($response));


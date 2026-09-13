<?php
//
//- new_aj13_conq.php | CURRÍCULO | Salva nova CONQUISTA/CERTIFICADO) 
//- (C)haia, 31/03/2025 | (U) 28/04/2026
//

session_start();

$idModulo = 7; // CURRICULUM

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
 rh_cv_con_aj3.php | 2025-03-31 15:59:27 
{
    "idPessoa": "1",
    "idConqTipo": "1",
    "ano": "2024",
    "titulo": "teste",
    "descricao": "<p>GERAR teste teste teste<\/p>"
}

*/
if (empty($idConqTipo) || empty($ano) || empty($titulo) || empty($descricao)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

    if( isset($_SESSION['idLogin']) ) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
    if( isset($_SESSION['idEmpresa']) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;
    $agora = date("Y-m-d H:i:s");

    include_once "../app/includes/conexao_gerar.php";
    include_once "../app/includes/f_logs.php";
    include_once "../app/includes/f_upload_seguro.php";

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra incluir
//- conquista/certificado no currículo de qualquer pessoa só sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

//
//- RECUPERA NOME DO CONDIDATO
//
    $sql = "SELECT nome as nmPessoa FROM rh_pessoas WHERE idPessoa = $idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($linha);

// Upload do certificado (se enviado)
$caminho_arquivo = null;
if (isset($_FILES['arquivoCert']) && $_FILES['arquivoCert']['error'] === UPLOAD_ERR_OK) {
    $validacao = upload_seguro_validar($_FILES['arquivoCert'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
    if ($validacao !== true) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => $validacao]));
    }
    //
    $arquivo = $_FILES['arquivoCert'];
    //
    $uploadDir = "../app/docs/pessoa_$idPessoa/";
    $fileName = basename($arquivo['name']);
    $fileSize = $arquivo['size'];
    $extensao = pathinfo($arquivo["name"], PATHINFO_EXTENSION);
    //
    $nome_temporario = $_FILES['arquivoCert']['tmp_name'];
    $nome_arquivo = uniqid("cert_") . "." . strtolower($extensao);
    $destino = $uploadDir . $nome_arquivo;

    if (!move_uploaded_file($nome_temporario, $destino)) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Erro ao mover o arquivo enviado!
                </div>'
        ];
        throw new Exception( json_encode($retorno) );
    }

    $caminho_arquivo = $destino;
    //
    //-- SALVA ARQUIVO - Tabela de Arquivos
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
    $descricao = "Certificado: $titulo de $nmPessoa";

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

$sql = "INSERT INTO rh_cv_conq ( idEmpresa, idPessoa, idConqTipo, titulo, ano, descricao, idLogin, idDoc) 
            VALUES (:idEmpresa, :idPessoa, :idConqTipo, :titulo, :ano, :descricao, :idLogin, :idDoc)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idEmpresa', $idEmpresa);
$consulta->bindParam(':idPessoa', $idPessoa);
$consulta->bindParam(':idConqTipo', $idConqTipo);
$consulta->bindParam(':titulo', $titulo);
$consulta->bindParam(':ano', $ano);
$consulta->bindParam(':descricao', $descricao);
$consulta->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);
$consulta->bindParam(':idDoc', $idDoc);

// Executa a inserção
if ($consulta->execute()) {
    $idCon = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idCon</div>";
    //
    $sql = "SELECT C.*, P.nome, T.nome as dsTipo, DATE_FORMAT(C.criado_em, '%d/%m/%Y %H:%i') AS criado_em,
                ifnull(D.arquivo,'') as arquivo
                FROM rh_cv_conq C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_conqTipos T on T.idConqTipo = C.idConqTipo
                LEFT OUTER JOIN rh_documentos D on D.idDoc = C.idDoc
            WHERE C.id = $idCon";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados = implode(", ", $linha);
    //
    $response = ["status" => true, "msg" => $msg, "d" => $linha];
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao inserir registro!</div>";
    $response = ["status" => false, "msg" => $msg];
}

f_log("INC", "INCLUSÃO no CV de Certificado ou Conquista: Dados( $dados )", "rh_cv_conq", $idModulo, $idCon);

$conn = null;
die(json_encode($response));

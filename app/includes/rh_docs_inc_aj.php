<?php
//
//- rh_docs_inc_aj.php | Grava Formulário no BD | Inclusão de DOCUMENTO
//- (C) 2024-03-08 by Chaia. | (U) 2025-06-09
//

session_start();

$idModulo  = 32;  //- GED
$agora     = date("Y-m-d H:i:s");

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    include_once "conexao_gerar.php";
    include_once "f_upload_seguro.php";
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);

// extract() acima roda por cima de QUALQUER chave do POST, inclusive
// nomes que não fazem parte do formulário normal (ex.: idEmpresa,
// idUsuario) — sem isso, um POST forjado poderia trocar identidade/tenant.
// idPessoa fica de fora de propósito: este console de documentos permite
// que quem estiver logado escolha para qual pessoa o upload vai (mesmo
// padrão já usado em rh_docs_edt_aj.php, que tem "troca de proprietário"
// explícita) — decisão confirmada com o usuário.
$idUsuario = $_SESSION['idUsuario'];
$idLogin   = $_SESSION['idLogin'  ];
$idEmpresa = $_SESSION['idEmpresa'];
$nmLogin   = $_SESSION['nmLogin'  ];

// idPessoa/idTipo vêm do cliente (de propósito), mas ainda assim precisam
// ser inteiros: são usados para montar caminho de arquivo em disco
// ("../docs/pessoa_$idPessoa"), e uma string como "../../etc" ali seria
// path traversal.
$idPessoa = (int) ($idPessoa ?? 0);
$idTipo   = (int) ($idTipo ?? 0);
if ($idPessoa <= 0) {
    $conn = null;
    die(json_encode(["status" => false, "msg" => "Pessoa inválida."]));
}

$mensagemSucesso = '<div class="alert alert-success text-center">Arquivo Inserido com <b>sucesso</b>.</div > ';
$mensagemErro    = '<div class="alert alert-danger text-center"><strong>Erro!</strong> Não foi possível processar a requisição!</div > ';

/*
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
$conn = null;
die(json_encode(["status" => true, "msg" => $mensagemSucesso]));
/*
 rh_docs_inc_aj.php | 2025-12-10 17:38:46 
{
    "nmPessoa": "LUIZ AUGUSTO CHAIA",
    "idPessoa": "1",
    "dataDoc": "2025-12-10",
    "idTipo": "29",
    "doc_descricao": "Aviso de F\u00e9rias",
    "tags": "F\u00e9rias, Aviso",
    "dsTipoDoc": "",
    "inc_ocr": "Arquivo n\u00e3o encontrado #1"
}
*/
//
//- SALVA DOCS NO BD
//    

if (!empty($_FILES['doc_arquivo']['tmp_name'])) {
    $validacao = upload_seguro_validar($_FILES['doc_arquivo'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    if ($validacao !== true) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => $validacao]));
    }

    // Diretório para salvar o anexo
    //- D:\xampp\htdocs\rh\includes/docs/pessoa_1/doc_250612142553_684b0da1d6ddc.pdf
    $diretorio = "../docs/pessoa_$idPessoa";

    // nome único para o arquivo
    $nome_original = $_FILES['doc_arquivo']['name'];
    $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
    $arquivo = "doc_" . date("ymdHis") . "_" . uniqid() . "." . $extensao;
    $file_tmp = $_FILES['doc_arquivo']['tmp_name'];
    $tamanho = $_FILES['doc_arquivo']['size']; // Tamanho do arquivo em bytes

    // Move o arquivo para o diretório
    $target_file = $diretorio . '/' . $arquivo;

    //debug( $target_file );

    if (move_uploaded_file($file_tmp, $target_file)) {
        //
        if( empty($inc_ocr) ){
            $texto_ocr = ocr($target_file);
        }
        else{
            $texto_ocr = $inc_ocr;
        }
    } else {
        header('HTTP/1.0 403 Forbidden');
        die("Sorry, there was an error uploading your file.");
    }

    $texto_ocr = "$nome_original | " . addslashes($texto_ocr); 
    //
    $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, descricao, data_validade, arquivo, nome_original, ocr,
                tags, extensao, tamanho, status, idLoginAprova, origem)
                values    (:idEmpresa, :idPessoa, :idTipo, :data, :descricao, :data2, :arquivo, :nome_original, :texto_ocr,
                :tags, :extensao, :tamanho, 1, :idLogin, 'GED')";
    //
    $stmt_files = $conn->prepare($sql);
    $stmt_files->bindParam('idEmpresa',     $idEmpresa);
    $stmt_files->bindParam('idPessoa',      $idPessoa);
    $stmt_files->bindParam('idTipo',        $idTipo);
    $stmt_files->bindParam('descricao',     $doc_descricao, PDO::PARAM_STR);
    $stmt_files->bindParam('data',          $dataDoc,       PDO::PARAM_STR);
    $stmt_files->bindParam('data2',         $dataDoc,       PDO::PARAM_STR);
    $stmt_files->bindParam('arquivo',       $arquivo,       PDO::PARAM_STR);
    $stmt_files->bindParam('nome_original', $nome_original, PDO::PARAM_STR);
    $stmt_files->bindParam('texto_ocr',     $texto_ocr,     PDO::PARAM_STR);
    $stmt_files->bindParam('tags',          $tags,          PDO::PARAM_STR);
    $stmt_files->bindParam('extensao',      $extensao,      PDO::PARAM_STR);
    $stmt_files->bindParam('tamanho',       $tamanho);
    $stmt_files->bindParam('idLogin',       $idLogin);
    $stmt_files->execute();

    if (!$stmt_files) {
        $conn = null;
        $mensagemErro = '<div class="alert alert-danger text-center"><strong>Erro!</strong> Falha ao INSERIR DOC de CRM!</div > ';
        die(json_encode(["status" => false, "msg" => $mensagemErro]));
    }
    $idDoc = $conn->lastInsertId();

    //
    //- REGISTRA LOG 
    //
    $historico = "Inserido documentos $nome_original de $nmPessoa";
    $sql = "INSERT INTO rh_logs (idLogin, dtOper, oper, historico, tabela, idModulo, idOperacao)
                    VALUES (:idLogin, :agora, 'INC', :historico, 'rh_docs', :idModulo, :idDoc)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam('idLogin', $idLogin);
    $stmt->bindParam('agora', $agora);
    $stmt->bindParam('historico', $historico);
    $stmt->bindParam('idModulo', $idModulo);
    $stmt->bindParam('idDoc', $idDoc);
    $stmt->execute();

    include_once "f_logs.php";
    f_log("INC", "Inclusão de Documento GED: $nmLogin ($nome_original)", "rh_documentos", $idModulo, 0);  

    $conn = null;
    die(json_encode(["status" => true, "msg" => $mensagemSucesso]));
    //
} else {
    $conn = null;
    die(json_encode(["status" => true, "msg" => $mensagemErro]));
}

function ocr($origem)
{
    //
    //- Função para extrair texto de documentos - com cURL
    //
    $url = 'http://rh.gerar.org.br/ocr_gerar.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário

    // Prepara o arquivo para envio
    $file = curl_file_create($origem, mime_content_type($origem), basename($origem));

    // Configura os parâmetros POST
    $postParams = [
        'arquivo' => $file,
    ];

    // Configura as opções da solicitação cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: multipart/form-data',
    ]);
    //
    $response = curl_exec($ch); // Executa a solicitação cURL e obtém a resposta
    //
    // Verifica se ocorreu algum erro na solicitação cURL
    if (curl_errno($ch)) {
        echo 'Erro na solicitação cURL: ' . curl_error($ch);
    } else {
        //echo 'Resposta cURL: ' . $response;
        curl_close($ch); // Fecha a sessão cURL
        return $response;
    }
}




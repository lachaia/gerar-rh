<?PHP
//
//- rh_cipa_aj27.php | Grava UPLOAD de DOCUMENTO
// (C)haia, 16/07/2025

session_start();

$idModulo = 11; // CIPA

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if ($dados) {
    extract($dados);
    $stringDados = json_encode($dados, JSON_UNESCAPED_UNICODE);
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_upload_seguro.php";
    //
    $idEmpresa = $_SESSION['idEmpresa'];
    $idLogin = $_SESSION['idLogin'];
    $idPessoa = $_SESSION['idPessoa'];
    $idSubSede = $_SESSION['idSubSede'] ?? 0; // Pega a SubSede do usuário logado
    //    
} else {
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

include_once "../includes/debug.php";
/*
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de upload de documento"]);
$conn = null;
die;
/*
 rh_cipa_aj27.php | 2025-07-16 17:37:54 
{
    "data": "2025-07-16",
    "idTipoDoc": "25",
    "assunto": "PCMCO",
    "tags": "CIPA"
}
//
die( json_encode(["status" => true, "msg" => "<div class='alert alert-primary'>Teste de UPLOAD de DOCUMENTOS</div>"]));
*/

// Verifica se o arquivo foi enviado
if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger'>Arquivo inválido ou não enviado.</div>"]));
}

// Diretório onde o arquivo será salvo
$dirDestino = "../docs/cipa"; // Ajuste o caminho conforme necessário

// Verifica se o diretório existe, senão cria
if (!is_dir($dirDestino)) {
    mkdir($dirDestino, 0755, true);
}

// Verifica se o arquivo foi enviado
if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
    $validacao = upload_seguro_validar($_FILES['documento'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    if ($validacao !== true) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => $validacao]));
    }
    $arquivoTmp = $_FILES['documento']['tmp_name'];
    $nomeOriginal = basename($_FILES['documento']['name']);
    $tamanho = $_FILES['documento']['size']; // em bytes

    // Gera nome seguro (com data e hash para evitar duplicidade)
    $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
    $nomeSeguro = 'doc_' . date('Ymd_His') . '_' . uniqid() . '.' . strtolower($ext);

    $caminhoFinal = $dirDestino . '/' . $nomeSeguro;

    // Move o arquivo
    if (move_uploaded_file($arquivoTmp, $caminhoFinal)) {
        // Arquivo salvo com sucesso
        $ata_arquivo_nome = $nomeSeguro; // salvar esse valor no banco
    } else {
        die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger'>Erro ao mover o arquivo enviado.</div>"]));
    }
    //

    //
    //- INSERE na tabela DOCUMENTOS
    //
    //
    $dataOriginal = $_POST['data']; // Exemplo: "2025-06-26"
    $data = new DateTime($dataOriginal);
    $data->modify('+10 years');
    $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
    //
    $idPessoa = $_SESSION['idPessoa'];
    //$tags = '#CIPA';
    //
    $y = $tamanho / 1024; // Tamanho em KB
    if( $y < 8192) $texto_ocr = ocr($caminhoFinal); else $texto_ocr = "Arquivo muito grande para o OCR";
    $texto_ocr = "$nomeOriginal | " . addslashes($texto_ocr);
    //
    $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, descricao, data_validade, arquivo,
                            nome_original, ocr, tags, extensao, tamanho, status, idLoginAprova, origem)
                            VALUES
                            (:idEmpresa, :idPessoa, :idTipoDoc, :data, :descricao, :data_validade, :arquivo, :nome_original,
                            :ocr, :tags, :extensao, :tamanho, 1, :idLogin, 'GIP')";
    //debug( $sql );
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idEmpresa', $idEmpresa);
    $stmt->bindParam(':idPessoa', $idPessoa);
    $stmt->bindParam(':idTipoDoc', $idTipoDoc);
    $stmt->bindParam(':data', $dataOriginal);
    $stmt->bindParam(':descricao', $assunto);
    $stmt->bindParam(':data_validade', $dataMais10Anos);
    $stmt->bindParam(':arquivo', $nomeSeguro);
    $stmt->bindParam(':nome_original', $nomeOriginal);
    $stmt->bindParam(':ocr', $texto_ocr);
    $stmt->bindParam(':tags', $tags);
    $stmt->bindParam(':extensao', $ext);
    $stmt->bindParam(':tamanho', $tamanho);
    $stmt->bindParam(':idLogin', $idLogin);
    $stmt->execute();
    //

} else{
    die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger'>Erro ao enviar arquivo!</div>"]));
}

$conn = null;
die(json_encode(["status" => true, "msg" => "<div class='alert alert-success'>Upload com sucesso!</div>"]));

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

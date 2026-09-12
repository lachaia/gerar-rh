<?PHP
//
//- rh_brigada_aj30.php | Grava ALTERAÇÃO de DOCUMENTO
// (C)haia, 22/07/2025

session_start();

$idModulo = 10; // Brigada

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

/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de upload de documento"]);
$conn = null;
die;
/*
 rh_cipa_aj30.php | 2025-07-17 10:28:11 
{
    "idDoc": "149",
    "data": "2025-07-17",
    "idTipoDoc": "25",
    "assunto": "TESTE",
    "tags": "TESTE"
}
//
die( json_encode(["status" => true, "msg" => "<div class='alert alert-primary'>Teste de UPLOAD de DOCUMENTOS</div>"]));
*/

//
//- RECUPERA DADOS DO DOCUMENTO ANTES DA ALTERAÇÃO
//
    $sql = "SELECT D.*, T.nome as dsTipoDoc
            FROM rh_documentos D
            INNER JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
            WHERE D.idDoc = :idDoc";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['idDoc' => $idDoc]); // Substitua $idCargo
    $dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = json_encode($dados);
    $arquivoAnterior = $dados['arquivo'] ?? '';
} else {
    $stringDados = "Nenhum dado encontrado.";
}

//
//- ALTERA DADOS DO DOCUMENTO
//
    $dataOriginal = $_POST['data']; // Exemplo: "2025-06-26"
    $data = new DateTime($dataOriginal);
    $data->modify('+10 years');
    $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
    //
    $idPessoa = $_SESSION['idPessoa'];
    //
    $sql = "UPDATE rh_documentos SET 
            idTipoDoc = :idTipoDoc, 
            data = :data, 
            descricao = :descricao, 
            tags = :tags,
            data_validade = :data_validade
            WHERE idDoc = :idDoc";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'idTipoDoc' => $idTipoDoc,
        'data' => $dataOriginal,
        'descricao' => $assunto,
        'tags' => $tags,
        'data_validade' => $dataMais10Anos,
        'idDoc' => $idDoc
    ]);


// Verifica se o arquivo foi enviado
if (isset($_FILES['documento_edit']) && $_FILES['documento_edit']['error'] === UPLOAD_ERR_OK) {
    $validacao = upload_seguro_validar($_FILES['documento_edit'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    if ($validacao !== true) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => $validacao]));
    }
    //
    // Diretório onde o arquivo será salvo
    $dirDestino = "../docs/brigada"; // Ajuste o caminho conforme necessário

    // Verifica se o diretório existe, senão cria
    if (!is_dir($dirDestino)) {
        mkdir($dirDestino, 0755, true);
    }    
    //
    $arquivoTmp = $_FILES['documento_edit']['tmp_name'];
    $nomeOriginal = basename($_FILES['documento_edit']['name']);
    $tamanho = $_FILES['documento_edit']['size']; // em bytes

    // Gera nome seguro (com data e hash para evitar duplicidade)
    $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
    $nomeSeguro = 'doc_' . date('Ymd_His') . '_' . uniqid() . '.' . strtolower($ext);

    $caminhoFinal = $dirDestino . '/' . $nomeSeguro;

    // Move o arquivo
    if (move_uploaded_file($arquivoTmp, $caminhoFinal)) {
        // Arquivo salvo com sucesso
        $ata_arquivo_nome = $nomeSeguro; // salvar esse valor no banco
        //
        $y = $tamanho / 1024; // Tamanho em KB
        if( $y < 8192) $texto_ocr = ocr($caminhoFinal); else $texto_ocr = "Arquivo muito grande para o OCR";
        $texto_ocr = "$nomeOriginal | " . addslashes($texto_ocr);
        //
        //
        $sql = "UPDATE rh_documentos SET arquivo = :arquivo,
                    nome_original = :nome_original,
                    ocr = :ocr, extensao = :extensao, tamanho = :tamanho
                    WHERE idDoc = :idDoc";
        //debug( $sql );
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':arquivo', $nomeSeguro);
        $stmt->bindParam(':nome_original', $nomeOriginal);
        $stmt->bindParam(':ocr', $texto_ocr);
        $stmt->bindParam(':extensao', $ext);
        $stmt->bindParam(':tamanho', $tamanho);
        $stmt->bindParam(':idDoc', $idDoc);
        $stmt->execute();
        //
        //- EXCLUI ARQUIVO ANTERIOR
        //
            $destino = "../docs/brigada/" . basename($arquivoAnterior);
            if (file_exists($destino) && !empty($arquivoAnterior)) {
                unlink($destino); // Exclui o arquivo do servidor
            }
    } else {
        die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger'>Erro ao mover o arquivo enviado.</div>"]));
    }
   //

} 

$conn = null;
die(json_encode(["status" => true, "msg" => "<div class='alert alert-success'>Atualização com sucesso!</div>"]));

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

<?php
//
//- ocr_gerar.php | Rotina para Extrair TEXTO de documento para indexação em Upload
//- (C) Chaia, 22/02/2024
//
// Este endpoint só é chamado internamente (via cURL, servidor->servidor) por
// outros _aj.php já autenticados — a chamada não carrega sessão/cookie do
// usuário. Como não dá pra exigir login aqui sem quebrar quem já chama certo,
// a proteção é: só aceitar requisição vinda do próprio servidor.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/f_upload_seguro.php';

if (($_SERVER['REMOTE_ADDR'] ?? '') !== ($_SERVER['SERVER_ADDR'] ?? '!')
    && ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1') {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

// Permitir múltiplas origens
$allowed_origins = array(
    "http://localhost",
    "https://rh.gerar.org.br"
);

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
}

header('Content-Type: text/plain; charset=UTF-8');

$caminho = __DIR__ . '/chaves/gerarocr-ba77063bf0b6.json';
putenv("GOOGLE_APPLICATION_CREDENTIALS=$caminho");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $validacao = upload_seguro_validar($_FILES['arquivo'], ['pdf', 'gif', 'tiff', 'tif', 'jpeg', 'jpg', 'png', 'bmp', 'webp', 'docx', 'xlsx']);
    if ($validacao !== true) {
        die(json_encode(["status" => false, "msg" => $validacao]));
    }

    $diretorio_destino = "temp";  // ajuste conforme necessário

    $arquivo_temporario = $_FILES['arquivo']['tmp_name'];
    $nome_arquivo = basename($_FILES['arquivo']['name']);
    $caminho_destino = $diretorio_destino . '/' . $nome_arquivo;

    if (move_uploaded_file($arquivo_temporario, $caminho_destino)) {
        // Aqui você pode realizar qualquer processamento adicional necessário
        $arquivo = $caminho_destino;
    } else {
        die(json_encode(["status" => false, "msg" => "Erro ao salvar o arquivo."]));
    }
} else {
    die(json_encode(["status" => false, "msg" => "Requisição inválida."]));
}

//
//-- VERIFICA A EXTENSÃO DO ARQUIVO e ESCOLHE ENGINE
//
$extensao = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

if ($extensao == 'docx') {
    //- execute rotina para extração do MS-Word
    //$url = 'localhost/gerar/ocr_docx.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário        
    $url = 'https://rh.gerar.org.br/ocr_docx.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário        
    
} elseif ($extensao == 'xlsx') {
    //- execute rotina para extração do MS-Excel
    //$url = 'localhost/gerar/ocr_xlxs.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário        
    $url = 'https://rh.gerar.org.br/ocr_xlxs.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário        

//} elseif ($extensao == 'pptx') {
//    //- execute rotina para extração do MS-Excel
//    //$url = 'localhost/gerar/ocr_pptx.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário        
//    $url = 'https://ti.gerar.org.br/ocr_pptx.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário           
//
} elseif (in_array($extensao, ['pdf', 'gif', 'tiff', 'tif', 'jpeg', 'jpg', 'png', 'bmp', 'webp'])) {
    //- é uma das extenções que o Google Document AI consegue processar
    //$url = 'localhost/rh/ocr_google.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário 
    $url = 'https://rh.gerar.org.br/ocr_google.php'; // Ajuste o caminho do arquivo ocr_gerar.php conforme necessário 

} else {
    //- executar rotina diversa, pois a extensão não está na lista fornecida
    die($arquivo); //- devolve apenas o nome do arquivo original.
}

// Configura os parâmetros POST
$postParams = [
    'arquivo' => basename($arquivo), // Envie apenas o nome do arquivo
];

// Codifica os parâmetros como uma string de consulta (query string)
$postData = http_build_query($postParams);

// Configura as opções da solicitação cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded', // Alterado para o tipo de conteúdo adequado
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erro na solicitação cURL: ' . curl_error($ch);
} else {
    echo $response;
    curl_close($ch);
    //return $response;
}

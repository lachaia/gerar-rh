<?php
//
//-- Inicia a Extração do Texto de Arquivos Word usando PHPWORD
//-- Chaia, 27/02/2024
//
// Só aceita chamada vinda do próprio servidor (ver ocr_gerar.php, que é
// quem invoca este arquivo internamente via cURL, sem sessão de usuário).
if (($_SERVER['REMOTE_ADDR'] ?? '') !== ($_SERVER['SERVER_ADDR'] ?? '!')
    && ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1') {
    http_response_code(403);
    die("Acesso negado.");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

//echo "<br>ocr_docx.php";

require 'vendor/autoload.php'; // Caminho para o arquivo de autoload gerado pelo Composer
use PhpOffice\PhpWord\Reader\Word2007;

// O arquivo enviado por ocr_gerar.php fica em app/temp/. basename() impede
// path traversal (ex.: "../../.env"), e a checagem de realpath garante que
// o caminho final continua dentro da pasta esperada.
$diretorio_base = __DIR__ . '/temp/';
$nome_arquivo = basename($_POST['arquivo'] ?? '');
$caminho_arquivo = $diretorio_base . $nome_arquivo;

$caminho_real = realpath($caminho_arquivo);
$base_real = realpath($diretorio_base);

if ($nome_arquivo === '' || $caminho_real === false || $base_real === false
    || strncmp($caminho_real, $base_real, strlen($base_real)) !== 0) {
    die("Arquivo não encontrado #1");
}

$extensao = strtolower(pathinfo($caminho_arquivo, PATHINFO_EXTENSION));

if( ! $extensao == 'docx'){
    die("Arquivo não é do tipo docx");
}

try {

    // Cria um objeto leitor para o Word 2007
    $reader = new Word2007();

    // Carrega o documento Word
    $phpWord = $reader->load($caminho_arquivo);
    $sections = $phpWord->getSections(); // Obtém todas as seções do documento
    $texto_extraido = ''; // Inicializa uma variável para armazenar o texto extraído

    // Loop através de todas as seções e parágrafos
    foreach ($sections as $section) {
        $paragraphs = $section->getElements();

        foreach ($paragraphs as $paragraph) {
            $texto_extraido .= $paragraph->getText() . "\n";
        }
    }

    // Imprime o texto extraído
    echo nl2br( $texto_extraido);
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}

?>

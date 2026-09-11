<?php
//
//-- Inicia a Extração do Texto de Arquivos Word usando PHPWORD
//-- Chaia, 27/02/2024

error_reporting(E_ALL);
ini_set('display_errors', 1);

//echo "<br>ocr_docx.php";

require 'vendor/autoload.php'; // Caminho para o arquivo de autoload gerado pelo Composer
use PhpOffice\PhpWord\Reader\Word2007;

$caminho_arquivo = "documentos/" . $_POST['arquivo'];

if( ! file($caminho_arquivo)){
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

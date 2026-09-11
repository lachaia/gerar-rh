<?php
//
//-- Inicia a Extração do Texto de Arquivos PowerPoint usando PHPPresentation
//-- Chaia, 27/02/2024

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // Caminho para o arquivo de autoload gerado pelo Composer
use PhpOffice\PhpPresentation\IOFactory;

$caminho_arquivo = "documentos/" . $_POST['arquivo'];

if (!file($caminho_arquivo)) {
    die("Arquivo não encontrado #1");
}

$extensao = strtolower(pathinfo($caminho_arquivo, PATHINFO_EXTENSION));

if ($extensao != 'pptx') {
    die("Arquivo não é do tipo pptx");
}

try {
    // Carrega o arquivo PowerPoint
    $presentation = IOFactory::load($caminho_arquivo);

    // Inicializa uma variável para armazenar o texto extraído
    $texto_extraido = '';

    // Percorre todas as slides no arquivo
    foreach ($presentation->getAllSlides() as $slide) {
        // Percorre todos os objetos de texto na slide
        foreach ($slide->getShapeCollection() as $shape) {
            if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                $texto_extraido .= $shape->getPlainText() . "\n";
            }
        }
        
    }

    // Imprime o texto extraído
    echo nl2br($texto_extraido);
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>

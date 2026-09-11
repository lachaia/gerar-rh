<?php
//
//-- Inicia a Extração do Texto usando Google Cloud Vision API
//-- Chaia, 27/02/2024

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclui o autoloader para as bibliotecas instaladas com o Composer
require __DIR__ . '/vendor/autoload.php';

// Imports das bibliotecas do Google Cloud Vision API
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

$caminho_arquivo = "downloads/2023 - Apresentação T.I feitos.pptx";

if (!file($caminho_arquivo)) {
    die("Arquivo não encontrado #1");
}

// Caminho para o seu arquivo JSON de credenciais local
$credentialsPath = 'gerarocr-6366e20f9747.json';

// Cria um cliente para o Vision API
$imageAnnotator = new ImageAnnotatorClient([
    'credentials' => json_decode(file_get_contents($credentialsPath), true),
]);

// Lê o conteúdo do arquivo
$contents = file_get_contents($caminho_arquivo);

// Executa a solicitação de OCR
$response = $imageAnnotator->documentTextDetection($contents);

// Verifica se há texto detectado
if ($response->hasFullTextAnnotation()) {
    // Extrai o texto do documento
    $text = $response->getFullTextAnnotation()->getText();
    // Exibe o texto extraído (ou faz outra operação com ele)
    echo nl2br($text);
} else {
    echo "Nenhum texto foi detectado no documento.";
}

// Fecha o cliente do Vision API
$imageAnnotator->close();

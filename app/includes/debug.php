<?php

function debug( $conteudo ){
    $programa = basename($_SERVER['PHP_SELF']);
    $agora = date("Y-m-d H:i:s");
    $nomeArquivo = 'debug.log'; // Nome do arquivo TXT
    $cabecalho = "$programa | $agora";
    if (!file_exists($nomeArquivo)) {
        $arquivo = fopen($nomeArquivo, 'w');
    } else {
        $arquivo = fopen($nomeArquivo, 'a');
    }
    fwrite($arquivo, "\n $cabecalho \n");   // Escreve o texto no final do arquivo
    fwrite($arquivo, "$conteudo\n");   // Escreve o texto no final do arquivo
    fclose($arquivo);  // Fecha o arquivo 
}
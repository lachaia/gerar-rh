<?php

function f_erro( $texto ){
    $agora = date("Y-m-d H:i:s");
    $nomeArquivo = 'erros.log'; // Nome do arquivo TXT
    if (!file_exists($nomeArquivo)) {
        $arquivo = fopen($nomeArquivo, 'w');
    } else {
        $arquivo = fopen($nomeArquivo, 'a');
    }
    fwrite($arquivo, "\n Em $agora informo: \n");   // Escreve o texto no final do arquivo
    fwrite($arquivo, "$texto\n");   // Escreve o texto no final do arquivo
    fclose($arquivo);  // Fecha o arquivo
}

?>
<?php

include_once "includes/conexao_gerar.php";

// Caminho do arquivo CSV
$arquivo = 'teste.csv';

if (($handle = fopen($arquivo, 'r')) !== false) {
    $linha = 0;

    $sql = "INSERT INTO importacao VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";
    $stmt = $conn->prepare($sql);

    while (($dados = fgetcsv($handle, 1000, ';')) !== false) {
        if ($linha++ == 0) continue; // pula cabeçalho

        // Remove espaços
        $dados = array_map('trim', $dados);

        // Executa o insert diretamente
        $stmt->execute($dados);
    }

    fclose($handle);
    echo "✅ Importação concluída com sucesso!";
} else {
    echo "❌ Não foi possível abrir o arquivo.";
}
?>

<?php
// Configurações de acesso ao banco

include_once "includes/conexao_gerar.php";

$sql = "SELECT * FROM imp_pessoas";
$stmt = $conn->prepare($sql);
$stmt->execute();

while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $valido = validarCPF( $cpf );
    if( $valido ) $resposta = "ok"; else $resposta = "INVÁLIDO";
    echo "<p>$cpf | $resposta</p>";
}

function validarCPF($cpf) {
    // Remove tudo que não for número
    $cpf = preg_replace('/\D/', '', $cpf);

    // Verifica se o número tem 11 dígitos ou é uma sequência inválida
    if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    // Calcula o primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = ($soma * 10) % 11;
    if ($resto == 10 || $resto == 11) {
        $resto = 0;
    }
    if ($resto != intval($cpf[9])) {
        return false;
    }

    // Calcula o segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = ($soma * 10) % 11;
    if ($resto == 10 || $resto == 11) {
        $resto = 0;
    }
    if ($resto != intval($cpf[10])) {
        return false;
    }

    return true;
}

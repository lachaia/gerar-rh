<?php
//
//- rh_ficha_denuncia_aj.php | exibe Ficha Deuncia RH
//- (C)haia, 24/07/2025
//

session_start();

$idModulo = 15; // Acolhimento do RH

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if ($parametros) extract($parametros);
if (empty($id)) {
    die("FALTOU PARÂMETROS ");
}

include_once "includes/conexao_gerar.php";

$sql = "SELECT * FROM rh_denuncias WHERE id = :id";
$stmt = $conn->prepare($sql);  
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

define('CHAVE_CRIPTO', 'minha_senha_32_chars_segura_x!'); // Troque por uma chave forte real
define('VETOR_IV', substr(hash('sha256', 'vetor-unico'), 0, 16));

function criptografar($texto) {
    return openssl_encrypt($texto, 'AES-256-CBC', CHAVE_CRIPTO, 0, VETOR_IV);
}

function descriptografar($textoCriptografado) {
    return openssl_decrypt($textoCriptografado, 'AES-256-CBC', CHAVE_CRIPTO, 0, VETOR_IV);
}

if ($dados) {
    //- Decriptografar os campos que foram criptografados

    $dados['nome'               ] = descriptografar( $dados['nome'] );
    $dados['email'              ] = descriptografar( $dados['email'] );
    $dados['telefone'           ] = descriptografar( $dados['telefone'] );
    $dados['relato'             ] = descriptografar( $dados['relato'] );
    $dados['envolvidos'         ] = descriptografar( $dados['envolvidos'] );
    $dados['nomes_testemunhas'  ] = descriptografar( $dados['nomes_testemunhas'] );
    $dados['resposta_comunicado'] = descriptografar( $dados['resposta_comunicado'] );
    $dados['nome_contato'       ] = descriptografar( $dados['nome_contato'] );
    $dados['email_contato'      ] = descriptografar( $dados['email_contato'] );
    $dados['telefone_contato'   ] = descriptografar( $dados['telefone_contato'] );

    //- Retornar os dados como JSON
    //header('Content-Type: application/json');
    echo json_encode($dados);
} else {
    echo json_encode(['error' => 'Registro não encontrado.']);
}
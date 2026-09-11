<?php
//
//- rh_ficha_denuncia_aj3.php | Devolve dados da AÇÃO específica
//- (C)haia, 25/07/2025
//

session_start();

define('CHAVE_CRIPTO', 'minha_senha_32_chars_segura_x!'); // Troque por uma chave forte real
define('VETOR_IV', substr(hash('sha256', 'vetor-unico'), 0, 16));

$idModulo = 15; // Acolhimento do RH

$idUsuario = $_SESSION['idUsuario'];

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados

$idAcao = filter_input(INPUT_POST, 'idAcao', FILTER_VALIDATE_INT);

if ( empty($idAcao) ) {
    die(json_encode(["status" => false, "msg" => "ID da Ação inválido!"]));
}

$sql = "SELECT A.*, T.nome as dsTipoAcao, U.login
                FROM rh_ouvidoria_ldt A
                INNER JOIN rh_ouvidoria_tldt T on T.idAcaoTipo = A.idAcaoTipo
                INNER JOIN rh_usuarios U on U.idUsuario = A.idUsuario
            WHERE idAcao = :idAcao";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":idAcao", $idAcao, PDO::PARAM_INT);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

$dados['descricao'] = descriptografar($dados['descricao']);

//include "debug.php";
//debug( json_encode($dados, JSON_PRETTY_PRINT) );

if ($dados) {
    if( $dados['idUsuario'] != $idUsuario ) $soleitura = true; else $soleitura = false;
    echo json_encode(["status" => true, "dados" => $dados, 'soleitura' => $soleitura]);
} else {
    echo json_encode(["status" => false, "msg" => "Nenhuma Informação encontrada!"]);
}

$conn = null;

function criptografar($texto)
{
    return openssl_encrypt($texto, 'AES-256-CBC', CHAVE_CRIPTO, 0, VETOR_IV);
}

function descriptografar($textoCriptografado)
{
    return openssl_decrypt($textoCriptografado, 'AES-256-CBC', CHAVE_CRIPTO, 0, VETOR_IV);
}

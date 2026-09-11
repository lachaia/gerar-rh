<?php
//
//- rh_ficha_denuncia_aj2.php | Salva Cartão de Ação da Linha do Tempo
//- (C)haia, 25/07/2025
//

session_start();

define('CHAVE_CRIPTO', 'minha_senha_32_chars_segura_x!'); // Troque por uma chave forte real
define('VETOR_IV', substr(hash('sha256', 'vetor-unico'), 0, 16));

if (!isset($_SESSION['idLogin'])) {
    header("location: logout.php");
} else {
    include_once "conexao_gerar.php";

    $idLogin = $_SESSION['idLogin'];
    $idUsuario = $_SESSION['idUsuario'];
    $idEmpresa = $_SESSION['idEmpresa'];
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) extract($dados);
/*
include "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO"];
die(json_encode($response));
 rh_ficha_denuncia_aj2.php | 2025-08-18 11:56:05 
{
    "idTipoAcao": "2",
    "data": "2025-08-18T11:55",
    "descricao": "<p>teste<\/p>",
    "idDenuncia": "1"
}
*/
$sql = "INSERT INTO rh_ouvidoria_ldt 
        (idAcaoTipo, idDenuncia, idEmpresa, idUsuario, data, descricao, idLogin) 
        VALUES (:idTipoAcao, :idDenuncia, :idEmpresa, :idUsuario, :data, :descricao, :idLogin)";
$stmt = $conn->prepare($sql);

$stmt->bindParam(':idTipoAcao', $idTipoAcao, PDO::PARAM_INT);
$stmt->bindParam(':idDenuncia', $idDenuncia, PDO::PARAM_INT);
$stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
$stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
$stmt->bindParam(':data', $data); // se for string tipo '2025-07-22 10:00:00'

$cripto = criptografar($descricao);

$stmt->bindParam(':descricao', $cripto, PDO::PARAM_STR);
$stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);

//die( json_encode($resposta) );

$sucesso = $stmt->execute();

$id = $conn->lastInsertId();

//
//- Atualiza status da ficha: 2 - Em Andamento
//
    $status_por = $_SESSION['nmLogin'];
    $sql = "UPDATE rh_ouvidoria 
                SET status = 2, 
                    status_em = NOW(), 
                    status_por = :status_por 
                WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idDenuncia, PDO::PARAM_INT);
    $stmt->bindParam(':status_por', $status_por, PDO::PARAM_STR);
    $stmt->execute();

$resposta = [
    'status' => true,
    'msg' => 'Linha do tempo salva com sucesso!'
];

die( json_encode($resposta) );

function criptografar($texto)
{
    return openssl_encrypt($texto, 'AES-256-CBC', CHAVE_CRIPTO, 0, VETOR_IV);
}

function descriptografar($textoCriptografado)
{
    return openssl_decrypt($textoCriptografado, 'AES-256-CBC', CHAVE_CRIPTO, 0, VETOR_IV);
}

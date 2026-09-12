<?php
//
//- rh_ficha_denuncia_aj3.php | Devolve dados da AÇÃO específica
//- (C)haia, 25/07/2025
//

session_start();

include_once "f_ouvidoria_cripto.php";

if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [3, 9], true)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

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

<?php
//
// - g_usuario_alt1_aj.php - Recupera dados para preencher campos de modal para editar usuário
// - (C)haia, 22/08/2023 | 19/02/2025
//

session_start();

$grupo = $_SESSION['idGrupo'] ?? null;
if (!isset($_SESSION['idLogin']) || ($grupo > 2 && $grupo != 9 && $grupo != 7)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

$idModulo  = 1; // rh_usuarios.php

include_once "../includes/conexao_gerar.php";

try {
    $sql = "SELECT * FROM rh_usuarios WHERE idUsuario = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam( 'id', $_POST["idUsuario"], PDO::PARAM_INT );
    $stmt->execute();

    if( $stmt AND $stmt->rowCount()>0 ){
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        unset($dados['senha']); // nunca devolver o hash da senha ao cliente
        $retorna = ['status' => true, "dados" => $dados ];
    } else{
        $retorna = ['status' => false, "msg" => "<div class='alert alert-danger' role='alert'>Erro: Nenhum Usuário Encontrado!</div>"];
    }    

} catch (PDOException $e) {
    // Caso ocorra algum erro na execução da consulta
    echo "Erro: " . $e->getMessage();
}
echo json_encode($retorna);
$conn = null;
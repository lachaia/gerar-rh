<?php
// - g_usuarios_inc_aj3.php | Inclui dados em rh_pessoas vindo da Modal Inc da Tela de Inclusão de Usuários
// - 2023-08-18 By Chaia. | 19/02/2025

$idModulo = 4; // rh_usuarios.php

session_start();
include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( empty( $dados['nome'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar o Nome da Pessoa!"]) );
}
if( empty( $dados['nomeSocial'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar o Nome Social do Usuário!"]) );
}
if( empty( $dados['cpf'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar o CPF do usuário!"]) );
}

//
//- Verifica se Nome de Pessoa ou CPF já existe
//
    $sql = "SELECT idPessoa FROM rh_pessoas WHERE nome like :nome OR cpf like :cpf";
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( ':nome', $dados['nome'], PDO::PARAM_STR );
    $stmt->bindParam( ':cpf', $dados['cpf'],   PDO::PARAM_STR );
    $result = $stmt->execute();
    if($stmt->rowCount()>0){
        die( json_encode(["status" => false, "msg" => "Pessoa já cadastrada, tente outra!"]) );
    }

$sql = "INSERT INTO rh_pessoas
            ( nome, nomeSocial, cpf, telefone, email, ativo )
            VALUES (:nome, :nomeSocial, :cpf, :telefone, :email, 1);";

try{
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( ':nome',       $dados['nome'],       PDO::PARAM_STR );
    $stmt->bindParam( ':nomeSocial', $dados['nomeSocial'], PDO::PARAM_STR );
    $stmt->bindParam( 'cpf',         $dados['cpf'],        PDO::PARAM_STR );
    $stmt->bindParam( 'telefone',    $dados['telefone'],   PDO::PARAM_STR );
    $stmt->bindParam( 'email',       $dados['email'],      PDO::PARAM_STR );
    $result = $stmt->execute();
    $idPessoa = $conn->lastInsertId();
    //
    f_log("INC", "Incluiu pessoa ID: " . $idPessoa . $dados['nome'] . " | " . $dados['cpf'] . " | " . $dados['nomeSocial'], "pessoas", $idModulo, $idPessoa);
    echo json_encode(["status" => true, "msg" => "Inclusão bem sucedida!", "idPessoa" => $idPessoa, "nome" => $dados['nome']]);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    $errorInfo = $stmt->errorInfo(); // Obtém informações do erro
    $retorno = "Sinto muito, deu erro ao atualizar! Detalhes: " . $errorInfo[2];
    echo json_encode(["status" => false, "msg" => "$retorno"]);
}

$conn = null;

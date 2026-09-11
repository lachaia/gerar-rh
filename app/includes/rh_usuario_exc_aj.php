<?php
//- rh_usuario_exc_aj.php - Exclusão do Registro de Usuário.
//- Só exclui se não houver login no sistema.
//- (C) Chaia, 24/08/2023 | 19/02/2025

//$retorno = ["status"=> false, "msg"=> "deu zika" ];
//die( json_encode($retorno) );

session_start();

$idModulo  = 1; // rh_usuarios.php
$idUsuario = $_POST["id"];

if( empty($idUsuario)){
    $retorno = ["status"=> false, "msg"=> "PARÂMETRO ID NÃO INFORMADO" ];
    die( json_encode($retorno) );
}

include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";

//
//- RECUPERA DADOS ANTIGOS
//
try{
    //
    $sql = "SELECT U.idUsuario, U.login, U.foto, U.ativo as uAtivo, U.idPessoa, P.nome, P.nomeSocial, P.cpf, P.ativo as pAtivo
                FROM rh_usuarios U
                INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa WHERE idUsuario = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam( 'id', $idUsuario, PDO::PARAM_INT );
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    $dadosAntigos = "Dados Antigos: " . implode(', ', $dados);
    //
} catch (PDOException $e) {
    // Caso ocorra algum erro na execução da consulta
    $mensagem = "Erro: " . $e->getMessage();
    $retorno = ["status"=> false, "msg"=> $mensagem ];
    die( json_encode($retorno) );    
}
//
//- Verifica se já houve movimentos
//

try {
    $sql = "SELECT * FROM rh_logins WHERE idUsuario = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam( 'id', $idUsuario, PDO::PARAM_INT );
    $stmt->execute();

    if( $stmt->rowCount()>0 ){
        //
        //- Não pode excluir pois tem movimento em rh_logins 
        //
            f_log("EXC", "Tentativa Proibida de Exclusão do Usuário $dadosAntigos" , "usuarios", $idModulo, $idUsuario);
            $conn = null;
            $retorna = ['status' => false, "msg" => "Não pode Excluir pois já teve movimentos!" ];
            die( json_encode($retorna) );
    } else{
        // Pode excluir - não teve movimentação
        try {
            //
            $sql = "DELETE FROM rh_usuarios WHERE idUsuario = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam( 'id', $idUsuario, PDO::PARAM_INT );
            $stmt->execute();
            //
            $retorna = ['status' => true, "msg" => "Excluído com Sucesso!" ];
            f_log("EXC", "Excluiu Usuário $dadosAntigos" , "usuario", $idModulo, $idUsuario);
        //
        //- Tratamento do arquivo de fotos
        //
            if( ! empty( $dados['foto'] ) ){
                $caminhoDestino = "../fotos/" . $dados['foto'];
                if (file_exists($caminhoDestino)) {
                    unlink( $caminhoDestino );
                }
            }
            $conn = null;
            die( json_encode($retorna) );
            //      
        } catch (PDOException $e) {
            $mensagem = "Erro: " . $e->getMessage();
            $retorno = ["status"=> false, "msg"=> $mensagem ];
            die( json_encode($retorno) ); 
        }
    }    

} catch (PDOException $e) {
    // Caso ocorra algum erro na execução da consulta
    $mensagem = "Erro: " . $e->getMessage();
    $retorno = ["status"=> false, "msg"=> $mensagem ];
    die( json_encode($retorno) ); 
}
$conn = null;
die( ['status' => true, "msg" => $retorno ] );
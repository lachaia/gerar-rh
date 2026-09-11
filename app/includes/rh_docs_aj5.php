<?php 
//
//- rh_docs_aj5.php | Retorna dados do DOCUMENTO
//- (C) 2024-03-07 by Chaia | (U) 2025-06-11
//

session_start();

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
extract( $parametros );

//include_once "../includes/f_logs.php";
include_once "../includes/conexao_gerar.php";

//
//- Recupera dados do Documento
//
    $sql = "SELECT D.*, T.nome as nmTipoDoc, P.nome as nmPessoa
                FROM rh_documentos D
                INNER JOIN rh_docs_tipo as T on T.idTipoDoc = D.idTipoDoc
                INNER JOIN rh_pessoas P on P.idPessoa = D.idPessoa
                WHERE D.idDoc = $idDoc";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dados) {
        //
        // Envia os dados como JSON
            header('Content-Type: application/json');
            echo json_encode($dados);
    } else {
        // Caso não haja dados, retorna um JSON indicando que não foi encontrado
        echo json_encode(['erro' => 'Registro não encontrado']);
    }
    $conn = null;
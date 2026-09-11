<?php 
//
//- rh_docs_vis_aj.php | Retorna dados do DOCUMENTO
//- (C) 2024-03-07 by Chaia | (U) 2025-06-02
//

session_start();

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
extract( $parametros );

//include_once "../includes/f_logs.php";
include_once "../includes/conexao_gerar.php";

//
//- Recupera dados da Visita
//
    $sql = "SELECT P.nome, T.nome as dsTipo, D.*,
                (select count(E.idDoc) from rh_emails E where E.idDoc = D.idDoc) as qtdEmails
                FROM rh_documentos D
                INNER JOIN rh_pessoas P ON P.idPessoa = D.idPessoa
                INNER JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
                WHERE D.idDoc = $idDoc";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dados) {
        //
        $dataFormatada        = DateTime::createFromFormat('Y-m-d H:i:s', $dados['criado_em']);
        $dados['criado_em']     = $dataFormatada->format('d/m/Y H:i'); // Formatar a data e hora como desejado

        $dataFormatada        = DateTime::createFromFormat('Y-m-d', $dados['data']);
        $dados['data']        = $dataFormatada->format('d/m/Y'); // Formatar a data e hora como desejado
        
        $dados['inseridoPor'] = "Inserido em " . $dados['criado_em']; 
        //
        //- Incrementa descrição do arquivo
        //
            if( empty($dados['descricao']) ) $dados['descricao'] = "nd";

            // Envia os dados como JSON
            header('Content-Type: application/json');
            echo json_encode($dados);
    } else {
        // Caso não haja dados, retorna um JSON indicando que não foi encontrado
        echo json_encode(['erro' => 'Registro não encontrado']);
    }
    $conn = null;
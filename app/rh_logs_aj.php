<?php
// g_logs_aj.php
// by LAChaia, 02/08/2023
//

$idModulo = 6; // g_logs.php

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "./includes/conexao_gerar.php";
    include_once "./includes/f_logs.php";
    //include_once "./includes/debug.php";
} else{
    header("location: logout.php");
}

if( isset($_POST['idUsuario']) ) $idUsuario = $_POST['idUsuario']; else $idUsuario = 0;
if( isset($_POST['dtFrom']) ) $dtFrom = $_POST['dtFrom']; else $dtFrom = "";
if( isset($_POST['dtTo']) ) $dtTo = $_POST['dtTo']; else $dtTo = "";

//$idUsuario = $_SESSION['idUsuario'];

f_log("CON", "Consulta grade de rh_logs do Sistema", "logs", $idModulo, 0);

$status = $_SESSION['status'];

// - Receber a Requisição de Pesquisa

    $requestData = $_REQUEST;

//- LISTA DE COLUNAS DA TABELA

    $colunas = [
         '0' => 'idLog',
         '1' => 'login',
         '2' => 'idLogin',
         '3' => 'dtOper',
         '4' => 'oper',
         '5' => 'historico',
         '6' => 'tabela',
         '7' => 'idModulo',
         '8' => 'idOperacao'
    ];

//- Quantos registro tem no banco de dados

    $sql_qtd_logs = "SELECT count(idLog) AS qtd_logs FROM rh_logs";
    $stmt = $conn->prepare( $sql_qtd_logs );
    $stmt->execute(); 
    $recordsTotal = $stmt->rowCount();


//- Obter dados a serem apresentados

    $pesquisa = "SELECT U.login, L.idLog, L.idLogin, L.dtOper, L.oper, L.historico, L.tabela, L.idModulo, L.idOperacao
                    FROM rh_logs L
                    LEFT OUTER JOIN rh_logins E ON E.idLogin = L.idLogin
                    LEFT OUTER JOIN rh_usuarios U ON U.idUsuario = E.idUsuario WHERE 1=1 ";

    if( $status == 'pessoal' ){
        $pesquisa .= " AND E.idUsuario = " . $_SESSION['idUsuario'];
    }else{
        if( $idUsuario>0 ) $pesquisa .= " AND E.idUsuario = $idUsuario ";
    }

    if( ! empty($dtFrom)) $pesquisa .= " AND dtOper >= '$dtFrom 00:00:00' ";
    if( ! empty($dtTo)) $pesquisa .= " AND dtOper <= '$dtTo 23:59:59' ";

    if( !empty( $requestData['search']['value'] ) ){
        //
        $pesquisa .= " AND ( idLog LIKE '%"   . $requestData['search']['value'] . "%' ";
        $pesquisa .= " OR login LIKE '%"      . $requestData['search']['value'] . "%' ";
        $pesquisa .= " OR L.idLogin LIKE '%"    . $requestData['search']['value'] . "%' ";
        $pesquisa .= " OR dtOper LIKE '%"     . $requestData['search']['value'] . "%' ";
        $pesquisa .= " OR oper LIKE '%"       . $requestData['search']['value'] . "%' ";
        $pesquisa .= " OR historico LIKE '%"  . $requestData['search']['value'] . "%' ";
        $pesquisa .= " OR tabela LIKE '%"     . $requestData['search']['value'] . "%' ";
        $pesquisa .= " OR idModulo LIKE '%"   . $requestData['search']['value'] . "%' )";
    } 

    $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();    
    $recordsFiltered = $stmt->rowCount();

//- Odenando o resultado
//
    $pesquisa .= " ORDER BY " . $colunas[ $requestData['order'][0]['column']] . "    " . 
                 $requestData['order'][0]['dir'] . "  LIMIT  " . $requestData['start'] . " ," . $requestData['length'] . "    ";
    
//- EXCUTAR A QUERY
    //debug( $pesquisa );
    $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();

//- LER OS Registros e preencher o array

    $dados = array();
    while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
        $dado = array();
        $dado[] = $linha['idLog'];
        $dado[] = $linha['login'];
        $dado[] = $linha['idLogin'];
        $dado[] = $linha['dtOper'];
        $dado[] = $linha['oper'];
        $dado[] = $linha['historico'];
        $dado[] = $linha['tabela'];
        $dado[] = $linha['idModulo'];
        $dado[] = $linha['idOperacao'];
        //
        $dados[] = $dado;
    }

    //- Criar um vetor para retornar ao Javascript
    //

        $output = array(
            "draw" => intval( $requestData['draw'] ),
            "recordsTotal" => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data" => $dados
        );        
        $conteudo = json_encode( $output );
        echo $conteudo;

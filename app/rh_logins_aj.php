<?php
// g_login_aj.php
// by LAChaia, 02/08/2023
//

$idModulo = 5; // g_logins.php

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
    //include_once "./includes/debug.php";
} else{
    header("location: logout.php");
}

if( isset($_POST['idUsuario']) ) $idUsuario = $_POST['idUsuario']; else $idUsuario = 0;
if( isset($_POST['dtFrom']) ) $dtFrom = $_POST['dtFrom']; else $dtFrom = "";
if( isset($_POST['dtTo']) ) $dtTo = $_POST['dtTo']; else $dtTo = "";

f_log("CON", "Consulta grade de Logins do Sistema", "rh_logins", $idModulo, 0);

$status = $_SESSION['status'];

// - Receber a Requisição de Pesquisa

$requestData = $_REQUEST;

//- LISTA DE COLUNAS DA TABELA

$colunas = [
    '0' => 'nome',
    '1' => 'login',
    '2' => 'idLogin',
    '3' => 'dtLogin',
    '4' => 'dtLogout',
    '5' => 'ip',
    '6' => 'sisoper',
    '7' => 'browser',
    '8' => 'hardware',
    '9' => 'qtd'
];

//- Quantos registro tem no banco de dados

$sql_qtd_logins = "SELECT count(idLogin) AS qtd_logins FROM rh_logins";
$stmt = $conn->prepare($sql_qtd_logins);
$stmt->execute();
$recordsTotal = $stmt->rowCount();


//- Obter dados a serem apresentados

$pesquisa = "SELECT U.login, P.nome, L.idLogin, L.dtLogin, L.dtLogout, L.ip, L.sisoper, L.browser, L.hardware, 
                    ( SELECT count(rh_logs.idLog) FROM rh_logs WHERE rh_logs.idLogin = L.idLogin ) as qtd
                    FROM rh_logins L
                    INNER JOIN rh_usuarios U ON U.idUsuario = L.idUsuario
                    INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa WHERE 1=1";

if ($status == 'pessoal') {
    $pesquisa .= " AND L.idUsuario = " . $_SESSION['idUsuario'];
} else {
    if ($idUsuario > 0) $pesquisa .= " AND L.idUsuario = $idUsuario ";
}

if( ! empty($dtFrom)) $pesquisa .= " AND dtLogin >= '$dtFrom 00:00:00' ";
if( ! empty($dtTo)) $pesquisa .= " AND dtLogin <= '$dtTo 23:59:59' ";

if (!empty($requestData['search']['value'])) {
    //
    $pesquisa .= " AND ( P.nome LIKE '%"  . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR U.login LIKE '%"    . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR L.idLogin LIKE '%"  . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR L.dtLogin LIKE '%"  . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR L.dtLogout LIKE '%" . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR L.ip LIKE '%"       . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR L.sisoper LIKE '%"  . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR L.browser LIKE '%"  . $requestData['search']['value'] . "%' ";
    $pesquisa .= " OR L.hardware LIKE '%" . $requestData['search']['value'] . "%') ";
}

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- Odenando o resultado
//
$pesquisa .= " ORDER BY " . $colunas[$requestData['order'][0]['column']] . "    " .
    $requestData['order'][0]['dir'] . "  LIMIT  " . $requestData['start'] . " ," . $requestData['length'] . "    ";

//- EXCUTAR A QUERY
$stmt = $conn->prepare($pesquisa);
$stmt->execute();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dado = array();
    $dado[] = $linha['nome'];
    $dado[] = $linha['login'];
    $dado[] = $linha['idLogin'];
    $dado[] = $linha['dtLogin'];
    $dado[] = empty($linha['dtLogout']) ? 'ativo' : $linha['dtLogout'];
    $dado[] = $linha['ip'];
    $dado[] = $linha['sisoper'];
    $dado[] = $linha['browser'];
    $dado[] = $linha['hardware'];
    $dado[] = $linha['qtd'];
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript
//

$output = array(
    "draw" => intval($requestData['draw']),
    "recordsTotal" => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
$conteudo = json_encode($output);
echo $conteudo;

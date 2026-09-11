<?php
// rh_cargos.php | Consulta Cargos 
// by (C)haia, 20/03/2025
//

$idModulo = 5; // Cargos

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else{
    header("location: logout.php");
}

//f_log("CON", "Consulta grade do Cargos", "rh_cargos", $idModulo, 0);

$data_ini = $_POST['data_ini'] . " 00:00:00";
$data_fim = $_POST['data_fim'] . " 23:59:59";

if( empty($data_ini )) $data_ini = date("Y-01-01");
if( empty($data_fim )) $data_fim = date("Y-m-d");

$onde = " A.avaliado_em >= '$data_ini' AND A.avaliado_em <= '$data_fim' ";

//- Obter dados a serem apresentados

$pesquisa = "SELECT
                    C.idColab,
                    P.nome, 
                    AVG(A.score) AS media
                FROM rh_avaliacoes A
                INNER JOIN rh_colaboradores C 
                    ON C.idColab = A.idColab
                INNER JOIN rh_pessoas P 
                    ON P.idPessoa = C.idPessoa
                WHERE $onde 
                GROUP BY C.idColab, P.nome
                ORDER BY media DESC";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar_medias($idColab)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    //
    $dado[] = $idColab;                                      
    $dado[] = $nome;
    $dado[] = $media;
    $dado[] = $acoes;
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript
//

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
$conteudo = json_encode($output);
echo $conteudo;
exit;
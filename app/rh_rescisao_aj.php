<?php
// rh_cargos.php | Consulta Cargos 
// by (C)haia, 20/03/2025
//

$idModulo = 9; // Rescisões

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else{
    header("location: logout.php");
}

f_log("CON", "Consulta grade das Rescisões", "rh_rescisoes", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT R.*, P.nome, T.descricao as dsTipoRescisao
                FROM rh_rescisoes R
                INNER JOIN rh_colaboradores C on C.idColab = R.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_rescisao_tipos T on T.idTipoRescisao = R.idTipoRescisao";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($idRescisao)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($idRescisao)'><i class='fa-solid fa-pen'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($idRescisao)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    if( $status == 'Pendente' ){
        $status = "<span class='badge bg-success status'>$status</span>";
    } else {
        $status = "<span class='badge bg-danger status'>$status</span>";
    }
    //
    $dado[] = $idRescisao;                                      
    $dado[] = $nome;
    $dado[] = $dsTipoRescisao;
    $dado[] = $dtAviso;
    $dado[] = $dtDesligamento;
    $dado[] = $status;
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
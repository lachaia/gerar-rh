<?php
// rh_termos_aj.php | Grid Consulta Afastamentos
// by (C)haia, 08/08/2025
//

$idModulo = 18; // Termos Gerais

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else{
    header("location: logout.php");
}

//f_log("CON", "Consulta grade do Afastamentos", "rh_cargos", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT A.*,  P.nome, T.descricao
                FROM RH.rh_termos A
                INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_termos_tipos T on T.id = A.idTipoTermo";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm me-1' onClick='f_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm me-1' onClick='f_editar($id)'><i class='fa-solid fa-pen'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
    // $acoes .=  "<a href='#!' class='btn btn-outline-secondary btn-sm disabled'><i class='fa-solid fa-trash-can'></i></a>";
    //
    $descricao = strip_tags( $descricao );
    //"x"; // 
    $dado[] = $id;                                      
    $dado[] = $nome;
    $dado[] = $descricao;
    $dado[] = $data;
    $dado[] = $acoes;
    //
    $dados[] = $dado;
}

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
$conteudo = json_encode($output);
echo $conteudo;
exit;
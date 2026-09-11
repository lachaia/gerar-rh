<?php
// rh_funcoes.php | Consulta Cargos 
// by (C)haia, 20/03/2025
//

$idModulo = 6;// Cargos

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else{
    header("location: logout.php");
}

f_log("CON", "Consulta grade de Funções", "rh_funcoes", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT A.*, LEFT(A.descricao, 150) as descricao,
                (select count(idFuncao) from rh_colaboradores B where B.idFuncao = A.idFuncao) as qtd
                FROM rh_funcoes A";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($idFuncao)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($idFuncao)'><i class='fa-solid fa-pen'></i></a>"; 
    if( $linha['qtd'] == 0 ){ 
        $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($idFuncao)'><i class='fa-solid fa-trash-can'></i></a>";
    } else{
        $acoes .=  "<a href='#!' class='btn btn-outline-secondary btn-sm disabled'><i class='fa-solid fa-trash-can'></i></a>";
    }
    //
    if( $ativo == 1 ) $ativo = "<i class='fa-regular fa-thumbs-up'></i>"; else $ativo = "<i class='fa-regular fa-thumbs-down text-danger'></i>";
    //
    $descricao = strip_tags( $descricao );
    //"x"; // 
    $dado[] = $idFuncao;                                      
    $dado[] = $nome;
    $dado[] = $descricao;
    $dado[] = $qtd == 0 ? "-" : $qtd;
    $dado[] = $ativo;
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
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

if( empty($data_ini )) $data_ini = date("Y-01-01 00:00:00");
if( empty($data_fim )) $data_fim = date("Y-m-d 23:59:59");

$onde = " A.criado_em >= '$data_ini' AND A.criado_em <= '$data_fim' ";

//- Obter dados a serem apresentados

$pesquisa = "SELECT A.*, P.nome, T.descricao, PS.nome as nmSupervisor
                FROM rh_avaliacoes A
                INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                LEFT OUTER JOIN rh_colaboradores CS on CS.idColab = A.idColabSuper
                LEFT OUTER JOIN rh_pessoas PS ON PS.idPessoa = CS.idPessoa
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_avaliacao_tipos T on T.id = A.idTipo
                WHERE $onde ";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    /*
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($idCargo)'><i class='fa-solid fa-pen'></i></a>"; 
    if( $linha['qtd'] == 0 ){ 
        $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($idCargo)'><i class='fa-solid fa-trash-can'></i></a>";
    } else{
        $acoes .=  "<a href='#!' class='btn btn-outline-secondary btn-sm disabled'><i class='fa-solid fa-trash-can'></i></a>";
    }
        */
    //
    $descricao = strip_tags( $descricao );
    if( $idTipo == 2 ) $descricao = "<kbd class='bg-warning text-dark'>$descricao</kbd>";
    //
    if( empty($avaliado_em) ){
        $avaliado_em = "<kbd>Pendente</kbd>";
        if( $idTipo == 1) $avaliado_por = $nmSupervisor;
        elseif( $idTipo == 2 ) $avaliado_por = $nome;
        else $avaliado_por = "Sem Registro";
        $score = "ND";
    }
    if( empty($avaliado_por)) $avaliado_por = '<kbd class="bg-danger">Sem Registro</kbd>';
    //
    $dado[] = $id;                                      
    $dado[] = $avaliado_em;
    $dado[] = $descricao;
    $dado[] = $avaliado_por;
    $dado[] = $nome;
    $dado[] = $score;
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
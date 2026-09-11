<?php
// rh_polos_aj.php | Popula Grid dos Polos
// (C)haia, 2026-07-27
//

session_start();

$idModulo = 24; // Polos

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
} else{
    header("location: ../logout.php");
}

//- Obter dados a serem apresentados

$onde = "1=1";

$pesquisa = "SELECT P.*, S.identificador as dsSubSede, C.nome as cidade, C.uf as uf, C.pais as pais,
                (select count(C.polo_id) from rh_colaboradores C where C.polo_id = P.id) as qtd
                FROM rh_polos P
                left outer join rh_cidades C on C.idCidade = P.cidade_id
                inner join rh_subsedes S on S.subsede_id = P.subsede_id
                WHERE $onde
                order by P.identificador asc";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    //
    $acoes = '<div class="btn-group">';
    $acoes .= '<button type="button" class="btn btn-outline-primary btn-sm" onClick="f_visualizar('.$id.')">' . '<i class="fas fa-search" data-bs-toggle="tooltip" title="Visualizar!"></i> ' . "</button>";    
    if( $ativo == 0){
        $acoes .= '<button type="button" class="btn btn-outline-warning btn-sm" disabled>' . '<i class="fas fa-edit" data-bs-toggle="tooltip" title="Editar!"></i> ' . "</button>"; 
        $acoes .= '<button type="button" class="btn btn-outline-danger btn-sm" disabled>' . '<i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i> ' . "</button>";
    } else{
        $acoes .= '<button type="button" class="btn btn-outline-warning btn-sm" onClick="f_editar('.$id.')">' . '<i class="fas fa-edit" data-bs-toggle="tooltip" title="Editar!"></i> ' . "</button>";
        $acoes .= '<button type="button" class="btn btn-outline-danger btn-sm" onClick="f_excluir('.$id.')">' . '<i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i> ' . "</button>";
    }
    //
    $acoes .= '</div>';    
    //
    $dado = array();
    //
    if( $ativo == 0 ) $classe = " inativo"; else $classe = "";
    //
    $qtd = ($qtd > 0) ? $qtd : "-";
    $dado[] = "<spam class='$classe'>" . $dsSubSede . "</spam>";
    $dado[] = "<spam class='$classe'>" . $identificador . "</spam>";
    $dado[] = "<spam class='$classe'>" . $polo_id . "</spam>";
    $dado[] = "<spam class='$classe'>" . $telefone . "</spam>";
    $dado[] = "<spam class='$classe'>" . $email . "</spam>";
    $dado[] = "<spam class='$classe'>" . $cidade . "</spam>";
    $dado[] = "<spam class='$classe'>" . $uf . "</spam>";
    $dado[] = "<spam class='$classe'>" . $pais . "</spam>";
    $dado[] = "<spam class='$classe'>" . $qtd . "</spam>";
    $dado[] = "<spam class='$classe'>" . $criado_em . "</spam>";
    $dado[] = "<spam class='$classe'>" . $acoes . "</spam>";
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

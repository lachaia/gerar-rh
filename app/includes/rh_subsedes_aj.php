<?php
// subsedes_aj.php
// (C)haia, 2026-06-16
//

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: ../logout.php");
}

f_log("CON", "Consulta grade de SubSedes de Auditoria", "contatos", 'SubSedes_aj', 0);

//- Obter dados a serem apresentados

$onde = "1=1";

$pesquisa = "SELECT S.*, C.nome as cidade, C.uf as uf, C.pais as pais,
	            (select count(C.idSubSede) from rh_colaboradores C where C.idSubSede = S.subsede_id) as qtd
                FROM rh_subsedes S
                left outer join rh_cidades C on C.idCidade = S.cidade_id
                WHERE $onde
                order by S.identificador asc";

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
    $acoes .= '<button type="button" class="btn btn-outline-warning btn-sm" onClick="f_editar('.$id.')">' . '<i class="fas fa-edit" data-bs-toggle="tooltip" title="Editar!"></i> ' . "</button>";
    if( $qtd > 0 ){
        $acoes .= '<button type="button" class="btn btn-outline-secondary btn-sm"><i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i> ' . "</button>";
    } else{
        $acoes .= '<button type="button" class="btn btn-outline-danger btn-sm" onClick="f_excluir('.$id.')">' . '<i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i> ' . "</button>";        
    }
    //
    $acoes .= '</div>';    
    //
    $dado = array();
    //
    if( $ativo == 0 ) $classe = " inativo"; else $classe = "";
    $qtd = ($qtd > 0) ? $qtd : "-";
    //
    $dado[] = "<spam class='$classe'>" . $identificador . "</spam>";
    $dado[] = "<spam class='$classe'>" . $telefone . "</spam>";
    $dado[] = "<spam class='$classe'>" . $email . "</spam>";
    $dado[] = "<spam class='$classe'>" . $cidade . "</spam>";
    $dado[] = "<spam class='$classe'>" . $uf . "</spam>";
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

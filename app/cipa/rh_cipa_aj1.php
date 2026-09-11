<?php
// rh_cipa_aj1.php | Dados para a Grid de Membros da CIPA 
// by (C)haia, 16/06/2025
//

$idModulo = 11; // CIPA

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: ../logout.php");
}

f_log("CON", "Consulta grade dos Cipeiros", "rh_cipeiros", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT P.nome, P.idPessoa, B.id as idMembro, B.*, C.dsCargo, S.identificador as dsSubSede
                FROM rh_cipeiros B
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                INNER JOIN rh_cipa_cargos C on C.idCargo = B.idCargo
                INNER JOIN rh_subsedes S on S.subsede_id = B.idSubSede
                ORDER BY B.data_final, P.nome";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $cor = !empty( $data_final) ? "text-danger" : "text-dark";
    //
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_ver_membro($idMembro)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar_membro($idMembro)'><i class='fa-solid fa-pen'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir_membro($idMembro)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    if( empty( $data_final) ){
        $data_final = "<span class='text-success'>Ativo</span>";
    } else {
        $data_final = date("Y-m-d", strtotime($data_final));
    }
    //
    $caminhoFoto = !empty($foto) ? "../docs_view.php?pasta=cipa&arquivo=" . rawurlencode($foto) : "../fotos/perfil.png";
    
    //
    $dado[] = "<span class='$cor'>$dsSubSede</span>";
    $dado[] = "<span class='$cor'>$nome</span>";
    $dado[] = "<img src='$caminhoFoto' alt='foto' class='img-thumbnail' style='max-width: 40px; height: auto;'>";
    $dado[] = "<span class='$cor'>$dsCargo</span>";
    $dado[] = "<span class='$cor'>$data_inicio</span>";
    $dado[] = "<span class='$cor'>$data_final</span>";
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
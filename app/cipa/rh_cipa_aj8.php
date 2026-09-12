<?php
// rh_cipa_aj8.php | Dados para a Grid de Atendimentos da CIPA 
// by (C)haia, 23/06/2025
//

$idModulo = 11; // CIPA de Emergência

session_start();

if( isset($_SESSION['idLogin']) && (!empty($_SESSION['dcCIPA']) || (int) ($_SESSION['idGrupo'] ?? 0) === 9) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: ../logout.php");
    exit();
}

f_log("CON", "Consulta grade dos Atendimentos da CIPA", "rh_cipa_atendimentos", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT S.identificador as dsSubSede, P.nome as nmcipeiro, A.*, O.descricao as dsOcorrencia
                FROM RH.rh_cipa_atendimentos A
                INNER JOIN rh_subsedes S on S.subsede_id = A.idSubSede
                INNER JOIN rh_cipeiros B on B.id = A.idCipeiro
                INNER JOIN rh_pessoas P on P.idPessoa = B.idPessoa
                INNER JOIN rh_cipa_tipo_ocorrencia O on O.id = A.tipo_ocorrencia";

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
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm'    onClick='f_ver_atende($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar_atende($id)'><i class='fa-solid fa-pen'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir_atende($id)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    $dado[] = "<span class='$cor'>$dsSubSede</span>";
    $dado[] = "<span class='$cor'>$data_ocorrencia</span>";
    $dado[] = "<span class='$cor'>$nmcipeiro</span>";
    $dado[] = "<span class='$cor'>$nome_paciente</span>";
    $dado[] = "<span class='$cor'>$dsOcorrencia</span>";
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
<?php
// rh_brigada_aj1.php | Dados para a Grid de Membros da Brigada 
// by (C)haia, 16/06/2025
//

$idModulo = 10; // Brigada de Emergência

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "conexao_gerar.php";
    include_once "f_logs.php";
} else{
    header("location: logout.php");
}

f_log("CON", "Consulta grade dos Brigadistas", "rh_brigadistas", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT P.nome, P.idPessoa, B.id as idMembro, B.*, C.dsCargo, S.dsSubSede
                FROM rh_brigadistas B
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                INNER JOIN rh_brigada_cargos C on C.idCargoBrigada = B.idCargoBrigada
                INNER JOIN rh_subsedes S on S.idSubSede = B.idSubSede";

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
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_ver($idMembro)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($idMembro)'><i class='fa-solid fa-pen'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($idMembro)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    if( empty( $data_final) ){
        $data_final = "<span class='text-success'>Ativo</span>";
    } else {
        $data_final = date("Y-m-d", strtotime($data_final));
    }
    $dado[] = "<span class='$cor'>$dsSubSede</span>";
    $dado[] = "<span class='$cor'>$nome</span>";
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
<?php
// rh_brigada_aj19.php | Dados para a Grid de AÇÕES da Brigada 
// by (C)haia, 27/06/2025
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

f_log("CON", "Consulta grade das AÇÕES da Brigada", "rh_brigada_acoes", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT R.*, S.dsSubSede,
                (select count(idBrigadista) 
                    from rh_brigada_acoes_membros m 
                    WHERE m.idAcao = R.id and m.presente = 1  
                ) AS qtdParticipantes 
                FROM rh_brigada_acoes R
                INNER JOIN rh_subsedes S on S.idSubSede = R.idSubSede";

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
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm m-1'    onClick='f_ver_acao($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm m-1' onClick='f_editar_acao($id)'><i class='fa-solid fa-pen'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm m-1' onClick='f_excluir_acao($id)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    if( !empty($ata_arquivo)){
        $acoes .= "<a href='#!' class='btn btn-outline-success btn-sm m-1' onClick='f_ver_ata(`$ata_arquivo`)'><i class='fa-solid fa-file-pdf'></i></a>"; 
    } else{
        $ata .= "<a href='#!' class='btn btn-outline-secondary btn-sm m-1'><i class='fa-solid fa-file-pdf'></i></a>"; 
    }
    //
    $dado[] = "<span class='$cor'>$id</span>";
    $dado[] = "<span class='$cor'>$dsSubSede</span>";
    $dado[] = "<span class='$cor'>$data_reuniao</span>";
    $dado[] = "<span class='$cor'>$assunto</span>";
    $dado[] = "<span class='$cor'>$qtdParticipantes</span>";
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
<?php
// index_aj1.php | Era: rh_equipamentos_aj2.php
// by (C)haia, 21/08/2025
//

$idModulo = 19; // Equipamentos

session_start();

if( isset($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [4, 7, 9], true) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
    exit();
}

//f_log("CON", "Consulta grade do Organograma Empresarial", "rh_organograma", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT S.*, P.nome 
                FROM RH.rh_equip_solic S
                INNER JOIN rh_pessoas P on P.idPessoa = S.idPessoa";
$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dado = array();
    extract( $linha );
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_ver_solicitacao($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    //
    $dado[] = $id;
    $dado[] = substr($criado_em,0,10);
    $dado[] = $nome;
    $dado[] = $usuario_final;
    $dado[] = $equipamentos;
    $dado[] = $glpi_id;
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
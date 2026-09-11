<?php
// rh_equipamentos_aj4.php | GRID dos TERMOS
// by (C)haia, 29/08/2025
//

$idModulo = 19; // Equipamentos

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "conexao_gerar.php";
    include_once "f_logs.php";
} else{
    header("location: logout.php");
}

//f_log("CON", "Consulta grade do Organograma Empresarial", "rh_organograma", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT T.*, P.nome 
                FROM rh_equip_termos T
                INNER JOIN rh_pessoas P on P.idPessoa = T.idPessoa";
$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dado = array();
    extract( $linha );
    //
    $dsStatus = "Indefinido";
    if( $status == 'Assinado') $dsStatus = "<span class='badge bg-success w-100 status'>Assinado</span>";
    if( $status == 'Pendente') $dsStatus = "<span class='badge bg-warning text-dark w-100 status'>Pendente</span>";
    if( $status == 'Baixado') $dsStatus = "<span class='badge bg-danger w-100 status'>Baixado</span>";
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_ver_termo($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    //
    $dado[] = substr($criado_em,0,10);
    $dado[] = $termo_nome;
    $dado[] = $dsStatus;
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
<?php
// index_aj3.php | Dados da Grid dos MODELOS de Termos de Responsabilidade
// by (C)haia, 21/08/2025
//

$idModulo = 19; // Equipamentos

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

//f_log("CON", "Consulta grade do Organograma Empresarial", "rh_organograma", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT M.*,
                (select count(t.idModelo) from rh_equip_termos t where t.idModelo = M.id  ) as qtd
                FROM rh_equip_modelos M";
$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dado = array();
    extract($linha);
    //
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_ver_modelo($id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
    if ($qtd == 0) {
        $acoes .= "<a href='#!' class='btn btn-outline-danger  btn-sm' onClick='f_excluir_modelo($id)'><i class='fa-regular fa-trash-can'></i></a>";
    } else {
        $acoes .= "<a href='#!' class='btn btn-outline-secondary btn-sm'><i class='fa-regular fa-trash-can'></i></a>";
    }
    $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar_modelo($id)'><i class='fa-solid fa-pen'></i></a>";
    //
    $dado[] = $id;
    $dado[] = $criado_em;
    $dado[] = $nome;
    $dado[] = $qtd;
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

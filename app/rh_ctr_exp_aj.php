<?php
// rh_ctr_exp_aj.php | GRID de Contratos de Experiência
// by (C)haia, 19/09/2025
//

$idModulo = 20; // Contratos de Experiência

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else {
    header("location: logout.php");
}

f_log("CON", "Consulta grade de Contratos de Experiência", "rh_ctr_exp", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT C.*, P.nome, CG.nome as dsCargo, O.descricao as dsOrgao
                FROM rh_ctr_exp C 
                INNER JOIN rh_colaboradores X on X.idColab = C.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = X.idPessoa
                INNER JOIN rh_cargos CG on CG.idCargo = X.idCargo
                INNER JOIN rh_organograma O on O.idOrgao = X.idOrgao";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>";
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($id)'><i class='fa-solid fa-pen'></i></a>";
    $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm'  onClick='f_excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
    //

    $hoje = new DateTime();
    $data_final = new DateTime($data_fim); // já vem do extract
    if( $status =='Ativo') $status = "<div class='badge bg-success w-100 status'>ATIVO</div>";

    if (!empty($data_prorrogacao)) {
        $prorrog = new DateTime($data_prorrogacao);

        // ainda dentro do 1º período
        if ($hoje <= $prorrog) {
            $diff = $hoje->diff($prorrog)->days;
            if ($diff < 10) {
                $status = "<div class='badge bg-warning text-dark w-100 status'>VENCENDO</div>";
            }
        }
    }

    // verifica prazo final
    if ($hoje <= $data_final) {
        $diff = $hoje->diff($data_final,)->days;
        if ($diff < 10) {
            $status = "<div class='badge bg-warning text-dark w-100 status'>VENCENDO</div>";
        }
    } else {
        $status = "<div class='badge bg-danger w-100 status'>ENCERRADO</div>";
    }


    //
    $dado[] = $nome;
    $dado[] = $dsCargo;
    $dado[] = $dsOrgao;
    $dado[] = $data_inicio;
    $dado[] = $data_prorrogacao ?: "N/D";
    $dado[] = $data_fim;
    $dado[] = $duracao . " dias";
    $dado[] = $status;
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

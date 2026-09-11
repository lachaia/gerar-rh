<?php
// rh_organoagrama_aj.php
// by (C)haia, 24/02/2025
//

$idModulo = 3; // Organograma

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else{
    header("location: logout.php");
}

f_log("CON", "Consulta grade do Organograma Empresarial", "rh_organograma", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT O.*, (select count(idColab) FROM rh_colaboradores C WHERE C.idOrgao = O.idOrgao) as qtd
                    FROM rh_organograma O
                    ORDER BY nivel_1, nivel_2, nivel_3, nivel_4, nivel_5, nivel_6, nivel_7";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dado = array();
    //
    $id = $linha['idOrgao'];
    //
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($id)'><i class='fa-solid fa-pen'></i></a>"; 
    if( $linha['qtd'] == 0 ){ 
        $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
    } else{
        $acoes .=  "<a href='#!' class='btn btn-outline-secondary btn-sm disabled'><i class='fa-solid fa-trash-can'></i></a>";
    }
    //
    $nivel = $linha['nivel'];
    if($nivel==3) $nivel = "<span class='badge bg-primary text-white px-2 py-1'>$nivel</span'>";
    if($nivel==7) $nivel = "<span class='badge bg-success text-white px-2 py-1'>$nivel</span'>";
    //
    $margem = $linha['nivel'] * 3;
    if( $linha['staff'] == 1 ) $staff = "<kbd>Staff</kbd>"; else $staff = "-";
    if( $linha['estrategico'] == 1 ) $estrategico = "Sim"; else $estrategico = "-";
    if( $linha['ativo'] == 1 ) $ativo = "<i class='fa-regular fa-thumbs-up'></i>"; else $ativo = "<i class='fa-regular fa-thumbs-down text-danger'></i>";
    if( $linha['nivel'] <= 3 ) $estilo = "font-weight: bold;"; else $estilo = "";
    if( $linha['staff'] == 1 ) $estilo = "color: blue"; 
    if( $linha['staff'] == 0  && $linha['nivel']==3) $estilo = "font-weight: 500; color: blue"; 
    if( $linha['nivel'] == 4 ) $estilo = "font-weight: bold; color: #0095B6"; 
    //
    $dado[] = $linha['idOrgao'];                                      
    $dado[] = "<span style='$estilo'>" . str_repeat("&nbsp;", $margem ) . $linha['descricao'] . "</span>";
    $dado[] = $linha['qtd'] == 0 ? "-" : $linha['qtd'];
    $dado[] = $nivel;
    $dado[] = $linha['idSupervisor'];
    $dado[] = $staff;
    $dado[] = $estrategico;
    $dado[] = $ativo;
    $dado[] = $linha['nivel_1'];
    $dado[] = $linha['nivel_2'];
    $dado[] = $linha['nivel_3'];
    $dado[] = $linha['nivel_4'];
    $dado[] = $linha['nivel_5'];
    $dado[] = $linha['nivel_6'];
    $dado[] = $linha['nivel_7'];
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
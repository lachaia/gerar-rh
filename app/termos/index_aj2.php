<?php
// index_aj2.php | Dados da Grid dos Termos de Responsabilidade
// by (C)haia, 21/08/2025
//

$idModulo = 19; // Equipamentos

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}

//f_log("CON", "Consulta grade do Organograma Empresarial", "rh_organograma", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT S.*, P.nome 
                FROM rh_equip_termos S
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
    $acoes =   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_ver_termo($id)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
//
// Define as opções e qual delas está selecionada
// Mapeamento de cores (Bootstrap)
$cores = [
    'Assinado'  => 'bg-success text-white',
    'Pendente'  => 'bg-warning text-dark',
    'Baixado'   => 'bg-danger text-white',
    'Devolvido' => 'bg-danger text-white' // Adicionado conforme sua pergunta
];

$classeCorAtual = $cores[$status] ?? 'bg-light text-dark';

$dsStatus = "<select class='form-select form-select-sm shadow-sm $classeCorAtual' 
                     style='width:130px; font-weight: bold;' 
                     onchange='f_alterar_status($id, this.value, this)'>";

foreach ($cores as $val => $class) {
    $selected = ($status == $val) ? "selected" : "";
    // No option, mantemos o fundo branco e texto preto para facilitar a leitura ao abrir o menu
    $dsStatus .= "<option value='$val' $selected class='bg-white text-dark'>$val</option>";
}

$dsStatus .= "</select>";
//
if( $status != 'Baixado') $acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm m-1' onClick='f_baixar_termo($id)'><i class='fa-solid fa-people-carry-box'></i></a>"; 
    else $acoes .= "<a href='#!' class='btn btn-outline-secondary btn-sm disabled m-1'><i class='fa-solid fa-people-carry-box'></i></a>";
//
    $dado[] = $id;
    $dado[] = substr($criado_em,0,10);
    $dado[] = $nome;
    $dado[] = $data_devolucao ?? "Aberto";
    $dado[] = $user_devolucao ?? "Aberto";
    $dado[] = $dsStatus;
    $dado[] = $origem == 'd' ? '<i class="fa-solid fa-paperclip"></i>' : '<i class="fa-solid fa-display"></i>';
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
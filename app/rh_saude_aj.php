<?php
// rh_saude.php | Exames (Saúde Ocupacional) 
// by (C)haia, 23/04/2025
//

$idModulo = 8; // Saúde Ocupacional

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once __DIR__ . "/includes/conexao_gerar.php";
    include_once __DIR__ . "/includes/f_logs.php";
} else{
    header("location: logout.php");
}

f_log("CON", "Consulta Exames Ocupacionais", "rh_exames", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT E.*, T.nmExame as dsExame, P.idPessoa, P.nome, D.arquivo
    FROM rh_exames E
    INNER JOIN rh_exames_tipos T on T.idExameTipo = E.idExameTipo
    INNER JOIN rh_colaboradores C on C.idColab = E.idColab
    INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
    LEFT OUTER JOIN rh_documentos D ON D.idDoc = E.idDoc";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    if( ! empty($idDoc)){
        $acoes =   "<a href='#!' class='btn btn-outline-success btn-sm' onClick='f_ver_doc($idPessoa,`$arquivo`)'><i class='fa-regular fa-file-pdf'></i></a></a>";     
    } else{
        $acoes =   "<a href='#!' class='btn btn-outline-secondary btn-sm'><i class='fa-regular fa-file-pdf'></i></a>"; 
    }
    $acoes .=   "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_visualizar($idExame)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar($idExame)'><i class='fa-solid fa-pen'></i></a>"; 
    $acoes .=  "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir($idExame)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    //  $descricao = strip_tags( $descricao );
    //
    // idExame, idColab, idExameTipo, data, dtValidade, nmExame, medico, status, observacoes, idDocumento, idLogin, nmExame, nome
    // 
    $dado[] = $idExame;                                      
    $dado[] = $nome;
    $dado[] = $data;
    $dado[] = ucfirst($nmExame);
    $dado[] = $dsExame;
    $dado[] = $dtValidade;
    $dado[] = $status;
    $dado[] = $nmClinica;
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
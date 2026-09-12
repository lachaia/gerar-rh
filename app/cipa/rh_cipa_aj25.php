<?php
// rh_cipa_aj25.php | Dados para a Grid de DOCUMENTOS da CIPA 
// by (C)haia, 16/07/2025
//

$idModulo = 11; // CIPA

session_start();

if( isset($_SESSION['idLogin']) && (!empty($_SESSION['dcCIPA']) || (int) ($_SESSION['idGrupo'] ?? 0) === 9) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: ../logout.php");
    exit();
}

f_log("CON", "Consulta grade dos Documentos da CIPA", "rh_cipeiros", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT D.*, T.nome as dsTipoDoc
                FROM rh_documentos D
                INNER JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
                WHERE origem = 'CIP' or origem='GIP'";
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
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_ver_doc($idDoc)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    if( $origem == 'GIP'){
        $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm' onClick='f_editar_doc($idDoc)'><i class='fa-solid fa-pen'></i></a>"; 
        $acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm' onClick='f_excluir_doc($idDoc)'><i class='fa-solid fa-trash-can'></i></a>";
    } else{
        $acoes .= "<a href='#!' class='btn btn-outline-secondary btn-sm'><i class='fa-solid fa-pen'></i></a>"; 
        $acoes .= "<a href='#!' class='btn btn-outline-secondary btn-sm'><i class='fa-solid fa-trash-can'></i></a>";
    }
    $acoes .= "<a href='../docs_view.php?pasta=cipa&arquivo=" . rawurlencode($arquivo) . "' class='btn btn-outline-primary btn-sm' download><i class='fa-solid fa-download'></i></a>";

    //
    if( empty( $data_final) ){
        $data_final = "<span class='text-success'>Ativo</span>";
    } else {
        $data_final = date("Y-m-d", strtotime($data_final));
    }
    //
    $caminhoFoto = !empty($foto) ? "../docs/cipa/$foto" : "../fotos/perfil.png";
    if( strlen($descricao)>60) $descricao = substr($descricao, 0, 60)."...";
    //
    $dado[] = $data;
    $dado[] = $dsTipoDoc;
    $dado[] = $descricao;
    $dado[] = $extensao;
    //
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
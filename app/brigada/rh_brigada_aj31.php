<?php
// rh_brigada_aj19.php | Dados para a Grid de AÇÕES da Brigada 
// by (C)haia, 27/06/2025
//

$idModulo = 10; // Brigada de Emergência

session_start();

if( isset($_SESSION['idLogin']) && (!empty($_SESSION['dcBrigada']) || (int) ($_SESSION['idGrupo'] ?? 0) === 9) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: ../logout.php");
    exit();
}

f_log("CON", "Consulta Usuarios do Módulo da Brigada", "rh_usuarios", $idModulo, 0);

//- Obter dados a serem apresentados

$pesquisa = "SELECT U.*, G.descricao as dsGrupo, G.sigla as siglaGrupo, P.nome, B.idSubSede, 
                    B.data_inicio, B.data_final, C.dsCargo, ifnull(S.identificador, 'ND') as dsSubSede
                FROM rh_usuarios U
                INNER JOIN rh_pessoas P on P.idPessoa = U.idPessoa
                INNER JOIN rh_usuariosgrupo G on G.idUsuarioGrupo = U.idUsuarioGrupo
                LEFT OUTER JOIN rh_brigadistas B on B.idPessoa = P.idPessoa
                LEFT OUTER JOIN rh_brigada_cargos C on C.idCargoBrigada = B.idCargoBrigada
                LEFT OUTER JOIN rh_subsedes S on S.subsede_id = B.idSubSede
                WHERE U.dcBrigada = 1";

$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    $cor = ( $ativo == 0) ? "text-danger" : "text-dark";
    //
    $acoes =  "<a href='#!' class='btn btn-outline-primary btn-sm m-1'    onClick='f_usuario_visualizar($idUsuario)'><i class='fa-solid fa-magnifying-glass'></i></a>"; 
    $acoes .= "<a href='#!' class='btn btn-outline-warning btn-sm m-1' onClick='f_usuario_editar($idUsuario)'><i class='fa-solid fa-pen'></i></a>"; 
    //$acoes .= "<a href='#!' class='btn btn-outline-danger btn-sm m-1' onClick='f_excluir_acao($idUsuario)'><i class='fa-solid fa-trash-can'></i></a>";
    //
    $dado[] = "<span class='$cor'>$idUsuario</span>";
    $dado[] = "<span class='$cor'>$dsSubSede</span>";
    $dado[] = "<span class='$cor'>$login</span>";
    $dado[] = "<span class='$cor'>$nome</span>";
    $dado[] = "<span class='$cor'>$dsCargo</span>";
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
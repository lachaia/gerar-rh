<?php
//
//- rh_espelhos_aj.php | Dados da Grid - Espelhos | Modulo Ponto Eletrônico
//

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include_once "conexao_gerar.php";

$status = $_POST['status'];

$onde = "";
if( $status != '' ) $onde = " WHERE E.status = '$status' ";

//- Obter dados a serem apresentados

    $pesquisa = "SELECT P.nome, E.* 
                    FROM rh_ponto_espelhos E
                    INNER JOIN rh_colaboradores C on C.idColab = E.colaborador_id
                    INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                    $onde";
    $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();    
    $recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

    $dados = array();
    while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
        extract( $linha );
        //
        $botao = "<button type='button' class='btn btn-outline-primary btn-sm' onClick='f_ver_espelho($id)'><i class='fas fa-search' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
        if( $status == 'ALERTA') $botao = "<button type='button' class='btn btn-outline-secondary btn-sm'><i class='fas fa-search' data-bs-toggle='tooltip' title='Sem PDF!'></i></button>";
        //
        $dsStatus = $status;
        if( $status == 'ASSINADO') $dsStatus = "<span class='badge bg-success w-100'>ASSINADO</span>";
        if( $status == 'ALERTA') $dsStatus = "<span class='badge bg-warning w-100 text-dark'>".'<i class="fa-solid fa-triangle-exclamation"></i> '."ALERTA</span>";
        if( $status == 'GERADO') $dsStatus = "<span class='badge bg-primary w-100'>ASSINAR</span>";
        //
        $dado = array();
        $dado[] = $periodo_fim;
        $dado[] = $ano . "-" . $dsMes;
        $dado[] = $nome;
        $dado[] = $dsStatus;
        $dado[] = $botao;
        //
        $dados[] = $dado;
    }

    //- Criar um vetor para retornar ao Javascript
    //

        $output = array(
            "draw" => 1,
            "recordsTotal" => intval( $recordsFiltered ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data" => $dados
        );    
   
        $conteudo = json_encode( $output );
        echo $conteudo;

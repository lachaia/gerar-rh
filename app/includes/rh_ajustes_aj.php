<?php
//
//- rh_ajustes_aj.php | Dados da Grid - AJUSTES | Modulo Ponto Eletrônico
// (C)haia, 23/12/2025

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include_once "conexao_gerar.php";

$status = $_POST['status'];

$onde = "";
if( $status != '' ) $onde = " WHERE S.status = '$status' ";

//- Obter dados a serem apresentados

    $pesquisa = "SELECT 
                        P.nome,
                        S.*,
                        DATEDIFF(
                            COALESCE(S.aprovado_em, NOW()),
                            S.solicitado_em
                        ) AS dias_decorridos
                    FROM rh_ponto_solicitacoes S
                    INNER JOIN rh_colaboradores C ON C.idColab = S.colaborador_id
                    INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                    $onde";
    $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();    
    $recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

    $dados = array();
    while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
        extract( $linha );
        //
        $botao = "<button type='button' class='btn btn-outline-primary btn-sm' onClick='f_ver_ajuste($id)'><i class='fas fa-search' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
        //
        $dsStatus = $status;
        if( $status == 'AGUARDANDO') $dsStatus = "<span class='badge bg-warning w-100 text-dark'>Aguardando</span>";
        if( $status == 'APROVADO'  ) $dsStatus = "<span class='badge bg-success w-100'>Aprovado</span>";
        if( $status == 'REJEITADO' ) $dsStatus = "<span class='badge bg-danger  w-100'>Rejeitado</span>";
        //
        $dado = array();
        $dado[] = $nome;
        $dado[] = $data_hora;
        $dado[] = $dias_decorridos;
        $dado[] = $tipo;
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

<?php
//
//- rh_ponto_aj1.php | Dados da Grid - Calendário | Modulo Ponto Eletrônico
//

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

$idUsuario = $_SESSION['idUsuario'];

include_once "conexao_gerar.php";

//- Obter dados a serem apresentados

    $pesquisa = "SELECT C.*, ifnull(CD.nome,'*todas') as dsCidade 
                    FROM rh_ponto_calendario C
                    LEFT OUTER JOIN rh_cidades CD on CD.idCidade = C.cidade_id
                    ORDER BY data";
    $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();    
    $recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

    $dados = array();
    while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
        extract( $linha );
        //
        $botao = "<button type='button' class='btn btn-outline-primary btn-sm' onClick='f_ver($id)'><i class='fas fa-search' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
        //
        $cor = $feriado = "";
        if( ! $ativo ) $cor = "text-danger";
        if( $tipo == 'FERIADO') $feriado = "feriado";
        if( $tipo == 'COMPENSADO') $feriado = "compensado";
        $estado = $estado ?? "BR";
        //
        $dado = array();
        $dado[] = "<spam class='$cor $feriado'>$data</spam>";
        $dado[] = "<spam class='$cor $feriado'>$tipo</spam>";
        $dado[] = "<spam class='$cor'>$dsCidade</spam>";
        $dado[] = "<spam class='$cor'>$estado</spam>";
        $dado[] = "<spam class='$cor'>$botao</spam>";
        $dado[] = $descricao;
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

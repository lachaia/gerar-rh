<?php
// g_docs_aj.php
// by LAChaia, 06/03/24 | (u) 30/05/2025
//

$idModulo = 14; // RH-GED 

session_start();

$_idGrupo  = $_SESSION['idGrupo' ];
$_idPessoa = $_SESSION['idPessoa'];
$email     = $_SESSION['email'   ];

include_once "includes/conexao_gerar.php";
include_once "includes/debug.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract( $dados );

$onde = " 1=1 ";
if( ! empty($idTipo) )   $onde .= " AND D.idTipoDoc = " . $_POST['idTipo'];

if( ! empty($idPessoa) && $idPessoa>0 ) $onde .= " AND P.idPessoa = " . $_POST['idPessoa'];
if( $idPessoa == -1 )  $onde .= " AND P.nome is null ";

if( ! empty($busca) ){
    $busca = "%$busca%";
    $onde .= " AND ( P.nome like '$busca' OR T.nome like '$busca' OR D.idDoc like '$busca' OR D.descricao like '$busca' 
            OR D.nome_original like '$busca' OR D.data like '$busca' OR D.ocr like '$busca' 
            OR D.origem like '$busca') ";
}

//- Quantos registro tem no banco de dados

    $sql = "SELECT count(idDoc) AS qtd FROM rh_documentos";
    $stmt = $conn->prepare( $sql );
    $stmt->execute();
    $d =  $stmt->fetch(PDO::FETCH_ASSOC);
    $recordsTotal = $d['qtd'];

//- Obter dados a serem apresentados

    $pesquisa = "SELECT P.nome, T.nome as dsTipo, D.*,
                    (select count(E.idDoc) from rh_emails E where E.idDoc = D.idDoc) as qtdEmails
                    FROM rh_documentos D
                    LEFT JOIN rh_pessoas P ON P.idPessoa = D.idPessoa
                    LEFT JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
                    WHERE $onde ";
    $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();    
    $recordsFiltered = $stmt->rowCount();

    //debug( $pesquisa);

//- LER OS Registros e preencher o array

    $dados = array();
    while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
        //
        //debug( json_encode($linha, JSON_PRETTY_PRINT) );
        extract( $linha );
        $qtdEmails = ( empty($qtdEmails) ) ? "" :  "<span style='font-size: 7px; position: absolute; bottom: 0; right: 1 '><b>$qtdEmails</b></span>"; // transform: translate(3px,12px);
        //
        $acoes = "<div class='btn-group'>";
        $acoes .= "<button type='button' class='btn btn-outline-primary btn-sm' onClick='f_ver( $idDoc )'><i class='fas fa-search' data-bs-toggle='tooltip' title='Visualizar!'></i></button>";
        //
        if( $origem == 'GED' ){
        // if( 1 == 1 ){
            //if( ( $idPessoa == $_idPessoa)){
                $acoes .= "<button type='button' class='btn btn-outline-danger btn-sm' onClick='f_exclui($idDoc,\"$nome_original\")'><i class='fa-regular fa-trash-can' data-bs-toggle='tooltip' title='Exclui!'></i></button>";
                $acoes .= "<button type='button' class='btn btn-outline-warning btn-sm' onClick='f_edita($idDoc)'><i class='fa-regular fa-pen-to-square' data-bs-toggle='tooltip' title='Exclui!'></i></button>";
            /*
            }else{
                $acoes .= "<button type='button' disabled class='btn btn-outline-danger btn-sm' onClick=''><i class='fa-regular fa-trash-can' data-bs-toggle='tooltip' title='Exclui!'></i></button>";    
                $acoes .= "<button type='button' disabled class='btn btn-outline-warning btn-sm' onClick=''><i class='fa-regular fa-pen-to-square' data-bs-toggle='tooltip' title='Exclui!'></i></button>";    
            }
            */        
        }else{
            $acoes .= "<button type='button' disabled class='btn btn-outline-secondary btn-sm' onClick=''><i class='fa-regular fa-trash-can' data-bs-toggle='tooltip' title='Exclui!'></i></button>";
            $acoes .= "<button type='button' disabled class='btn btn-outline-secondary btn-sm' onClick=''><i class='fa-regular fa-pen-to-square' data-bs-toggle='tooltip' title='Exclui!'></i></button>";                
        }
        //
        $acoes .= '<button type="button" class="btn btn-outline-success btn-sm" onClick="f_email(' . $idDoc . ')">' . '<i class="fa-regular fa-envelope"></i> ' . $qtdEmails . "</button>";
        $acoes .= '</div>';   
        //
        $dado = array();
        //
        $dado[] = $idDoc;
        $dado[] = $data; 
        $dado[] = $nome;  
        $dado[] = $dsTipo;  
        $dado[] = $descricao ?? "Nada informado...";  
        $dado[] = $nome_original;
        $dado[] = $extensao;  
        $dado[] = $acoes;
        //
        $dados[] = $dado;
    }

    //- Criar um vetor para retornar ao Javascript
    //

    $output = array(
        "draw" => 1,
        "recordsTotal" => intval( $recordsTotal ),
        "recordsFiltered" => intval( $recordsFiltered ),
        "data" => $dados
    );        

    echo json_encode( $output );

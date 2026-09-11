<?php
// g_login_aj.php
// by LAChaia, 02/08/2023
//
session_start();

$idUsuario = $_SESSION['idUsuario'];
$idModulo = 2; // rh_pessoas

include_once __DIR__ . "/includes/conexao_gerar.php";

//- Obter dados a serem apresentados

    $pesquisa = "SELECT idPessoa, UPPER(nome) as nome, nomeSocial, cpf, ativo, telefone, email,
                    (select count(idEmail) from rh_emails WHERE idPessoa = P.idPessoa) as qtdEmails,
                    (select count(idPessoa) from rh_colaboradores WHERE idPessoa = P.idPessoa ) as qtdColaboradores
                    FROM rh_pessoas P";
     $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();    
    $recordsFiltered = $stmt->rowCount();

 
//- EXCUTAR A QUERY

    try {
        $stmt = $conn->prepare($pesquisa);
        $stmt->execute();
        // Aqui você pode continuar com o processamento dos resultados
    } catch (PDOException $e) {
        // Caso ocorra algum erro na execução da consulta
        echo "Erro: " . $e->getMessage();
    }

//- LER OS Registros e preencher o array

    $dados = array();
    while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
        extract( $linha );
        //
        
        if($ativo==1){
            $dsAtivo = "<i class='far fa-thumbs-up text-success'></i>";
        } else{
            $dsAtivo = "<i class='far fa-thumbs-down text-danger'></i>";
        }
        //
        if( empty($telefone) ) $linkw = ""; else $linkw = f_linkw( $telefone );
        
        if( ! $qtdEmails>0 ) {
            $qtdEmails = "";
        } else{
            $qtdEmails = "<span style='font-size: 7px; position: absolute; bottom: 0; right: 1; color: blue'><b>$qtdEmails</b></span>";
        }
       
        $acoes = '<div class="btn-group">';
        $acoes .= '<button type="button" class="btn btn-outline-primary btn-sm" onClick="f_ver('.$idPessoa.')">'     . '<i class="fas fa-search" data-bs-toggle="tooltip" title="Visualizar!"></i> ' . "</button>";
        $acoes .= '<button type="button" class="btn btn-outline-warning btn-sm" onClick="f_editar('.$idPessoa.')">'  . '<i class="fas fa-edit" data-bs-toggle="tooltip" title="Editar!"></i> ' . "</button>";
        if( $qtdColaboradores>0 || $qtdEmails>0){
            $acoes .= '<button type="button" class="btn btn-outline-secondary btn-sm"><i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i></button>';
        } else{
            $acoes .= '<button type="button" class="btn btn-outline-danger  btn-sm" onClick="f_excluir('.$idPessoa.')">' . '<i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i> ' . "</button>";
        }
        $acoes .= "<button type='button' class='btn btn-outline-primary btn-sm' onClick='cv($idPessoa)' data-bs-toggle='tooltip' title='Curriculum vitae'><i class='fa-solid fa-user-tie'></i></button>";
        if( empty($linha['email'])){
            $acoes .= '<button type="button" class="btn btn-outline-secondary btn-sm"><i class="fa-regular fa-envelope"></i></button>';
        } else{
            $acoes .= "<button type='button' class='btn btn-outline-success btn-sm' onClick='f_email($idPessoa, \"$email\")' data-bs-toggle='tooltip' title='envia e-mail'><i class='fa-regular fa-envelope'></i>$qtdEmails</button>";
        }        
        $acoes .= '</div>';
        //
        $dado = array();
        $dado[] = $idPessoa;
        $dado[] = $nome;
        $dado[] = $nomeSocial;
        $dado[] = mascara($cpf);
        $dado[] = (empty($telefone)) ? "ND" : $telefone ." ". $linkw;
        $dado[] = (empty($email)) ? "ND" : $email;
        $dado[] = $dsAtivo;
        $dado[] = $qtdColaboradores ? "<kbd>$qtdColaboradores</kbd>" : "-";
        $dado[] = $acoes;
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

function mascara( $cpf ){
    $cpf = str_replace(".","",$cpf);
    $cpf = str_replace("-","",$cpf);
    $c = str_pad($cpf , 11 , '0' , STR_PAD_LEFT);
    $x = substr($c,0,3) . "." . substr($c,3,3) . "." . substr($c,6,3) . "-" . substr($c,9,2);
    return $x;
}

function f_linkw($numero) {
    if (!(substr($numero, 0, 3) == '+55')) {
        $numero = '+55' . $numero;
    }
    $numero = str_replace(" ", "", $numero);
    $numero = str_replace("-", "", $numero);
    $numero = str_replace("/", "", $numero);
    $numero = str_replace(".", "", $numero);
    $numero = str_replace(";", "", $numero);
    $numero = str_replace(":", "", $numero);
    $url = "https://wa.me/$numero";
    $link = '<a href="' . $url . '" target="_blank"><i class="fa-brands fa-whatsapp" style="color: green"></i></a>';
    return $link;
}
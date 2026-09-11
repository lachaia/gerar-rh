<?php
// rh_usuarios_aj.php | Grid de Usuários
// by LAChaia, 01/02/2025
//

session_start();

$modulo = 1; // rh_usuarios

$idUsuario = $_SESSION['idUsuario'];

include_once __DIR__ . "/includes/conexao_gerar.php";

//- Obter dados a serem apresentados

    $pesquisa = "SELECT E.nome as nmEmpresa, G.sigla as grupo, U.idUsuario, U.login, U.foto, P.idPessoa, P.nome, P.cpf, U.ativo, V3.ultima, V1.qtd as entradas, 
                    V2.qtd as operacoes, S.dsSubSede
                    FROM rh_usuarios U
                    LEFT OUTER JOIN VW_Usuarios_logins V1 ON V1.idUsuario = U.idUsuario
                    LEFT OUTER JOIN VW_Usuarios_logs   V2 ON V2.idUsuario = U.idUsuario
                    LEFT OUTER JOIN VW_Usuarios_ultima V3 ON V3.idUsuario = U.idUsuario
                    INNER JOIN rh_usuariosgrupo G on G.idUsuarioGrupo = U.idUsuarioGrupo
                    INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa 
                    INNER JOIN rh_empresas E on E.idEmpresa = U.idEmpresa
                    LEFT OUTER JOIN rh_subsedes as S on S.idSubSede = U.idSubSede
                    WHERE 1=1  ";

    $stmt = $conn->prepare( $pesquisa );
    $stmt->execute();    
    $recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

    $dados = array();
    while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
        $ativo = "<i class='far fa-thumbs-down' style='color:red'></i>";
        if($linha['ativo']==1){
            $ativo = "<i class='far fa-thumbs-up'></i>";
        }
        if( empty($linha['ultima'])) $ultima = "ND"; else $ultima = $linha['ultima'];
        //
        $id = $linha['idUsuario'];
        //
        $acoes = '<div class="btn-group">';
        $acoes .= '<button type="button" class="btn btn-outline-primary   btn-sm" onClick="f_visualisar('.$id.')">' . '<i class="fas fa-search"    data-bs-toggle="tooltip" title="Visualizar!"></i> ' . "</button>";
        $acoes .= '<button type="button" class="btn btn-outline-warning   btn-sm" onClick="f_editar('.$id.')">' . '<i class="fas fa-edit"      data-bs-toggle="tooltip" title="Editar!"></i> '     . "</button>";
        $acoes .= '<button type="button" class="btn btn-outline-danger    btn-sm" onClick="f_excluir('.$id.')">' . '<i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i> '    . "</button>";
        $acoes .= '<button type="button" class="btn btn-outline-secondary btn-sm" onClick="f_chat('.$id.')">' . '<i class="far fa-comment"   data-bs-toggle="tooltip" title="Chat!"></i> '       . "</button>";
        $acoes .= '</div>';
        //
        $dado = array();
        $dado[] = $linha['nmEmpresa'];
        $dado[] = $linha['grupo'];
        $dado[] = $id;
        $dado[] = $linha['login'];
        $dado[] = $linha['idPessoa'];
        $dado[] = $linha['nome'];
        $dado[] = $linha['cpf'];
        $dado[] = $ultima;
        $dado[] = $ativo;
        $dado[] = "<img src='fotos/".$linha['foto']."' style='width:20px;height:20px;' class='rounded'>";
        $dado[] = (empty($linha['entradas'])) ? "ND" : $linha['entradas'];
        $dado[] = (empty($linha['operacoes'])) ? "ND" : $linha['operacoes'];
        $dado[] = $linha['dsSubSede'];
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

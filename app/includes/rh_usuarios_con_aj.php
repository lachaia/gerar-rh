<?php
//
//- rh_usuarios_con_aj.php | Retorna dados do USUARIO
//- (C)haia, 2026-05-28
//

session_start();

$grupo = $_SESSION['idGrupo'] ?? null;
if (!isset($_SESSION['idLogin']) || ($grupo > 2 && $grupo != 9 && $grupo != 7)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";

$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);

if( !empty($id) ){
    $sql = "SELECT U.idUsuario, U.foto, U.criado_por, U.criado_em, U.idUsuarioGrupo, U.idPessoa, U.login, U.ativo as uAtivo, U.chaveApp,
                   P.nome, P.nomeSocial, P.cpf, P.ativo as pAtivo, G.descricao as dsGrupo, P.email, U.dcCIPA, U.dcBrigada  
                    FROM rh_usuarios U 
                    INNER JOIN rh_pessoas P on P.idPessoa = U.idPessoa 
                    LEFT OUTER JOIN rh_usuariosgrupo G ON G.idUsuarioGrupo = U.idUsuarioGrupo
                    WHERE idUsuario = :id LIMIT 1";
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( ':id', $id, PDO::PARAM_INT );
    $stmt->execute();

    if( $stmt AND $stmt->rowCount()>0 ){
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        $retorna = ['status' => true, "dados" => $dados ];
    } else{
        $retorna = ['status' => false, "msg" => "<div class='alert alert-danger' role='alert'>Erro: Nenhum Usuário Encontrado!</div>"];
    }
} else{
    $retorna = ['status' => false, "msg" => "<div class='alert alert-danger' role='alert'>Erro: Nenhum Usuário Encontrado!</div>"];
}

f_log("CON", "Consulta usuario do Sistema: " . $dados['idUsuario'] . "-" . $dados['login'] . " | " . $dados['nome'], "usuarios", 0, 0);
echo json_encode($retorna);
$conn = null;
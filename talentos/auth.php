<?php
//
// auth.php - Gerar autenticação do Candidato - módulo: talentos
// (C)haia, 16/04/2026
//

session_start();

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);

if( empty($login) || empty($senha) ) {
    echo json_encode([
        'status' => false,
        'msg' => 'Campos obrigatórios faltando.'
    ]);
    exit;
}

$login_cpf = preg_replace('/\D/', '', $login); // limpa o cpf

include "../app/includes/conexao_gerar.php";

//- Login de candidato usa rh_user_candidatos - tabela própria, separada de
//- rh_usuarios (login de staff/colaborador), pra um bug no lado do candidato
//- nunca ter como enxergar/afetar credencial de funcionário.
$sql = "SELECT P.idPessoa, U.senha
            FROM rh_user_candidatos U
            INNER JOIN rh_pessoas P on P.idPessoa = U.pessoa_id
            WHERE (P.cpf = :login_cpf) or ( P.email = :login )
            LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->bindParam(':login_cpf', $login_cpf, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll();

if( empty($rows) ) {
    echo json_encode([
        'status' => false,
        'msg' => 'Usuário ou Senha errada!'
    ]);
    exit;
}
//
    $idPessoa = $rows[0]['idPessoa'];

    if (! password_verify($senha, $rows[0]['senha'])) {
        $retorno = [
            'status' => false,
            'msg' => 'Usuário ou Senha errada!'
        ];
    }else{
        //- Sessão real do candidato - as páginas seguintes (candidatos.php e os
        //- candidatos_aj*.php) confiam nisso, não em pessoa_id vindo do cliente.
        session_regenerate_id(true);
        $_SESSION['candidato_idPessoa'] = (int) $idPessoa;
        $retorno = [
            'status' => true,
            'msg' => 'Autenticado com sucesso.'
        ];
    }

$conn = null;
die( json_encode($retorno, JSON_PRETTY_PRINT) );
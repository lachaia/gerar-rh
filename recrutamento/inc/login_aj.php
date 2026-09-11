<?php
//
//- login_aj.php | Autentica Gestor para solicitar nova vaga de colaborador
//- (C)haia, 13/08/2026
//

session_start();

include_once "../../app/includes/conexao_gerar.php";    //- Conecta ao banco
include_once "../../app/includes/f_login.php";      //- Origem do Usuário

$acao = $_POST['acao'] ?? '';

if ($acao === 'autenticar') {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha   = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos obrigatórios não informados.']);
        exit;
    }

    //
    //- TODO: Lógica de autenticação
    //
    
    $login_cpf = preg_replace('/\D/', '', $usuario); //- limpa se login é CPF

    $sql = "SELECT  U.idColab, C.idOrgao, U.idEmpresa, U.idUsuario, P.idPessoa, P.nome, U.login, U.senha, U.idUsuarioGrupo, 
                    U.foto, P.email_corporativo as email, U.chaveApp, U.idSubSede, S.identificador as dsSubSede, U.dcCIPA, U.dcBrigada
                FROM rh_usuarios U
                INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa
                LEFT OUTER JOIN rh_subsedes as S on S.subsede_id = U.idSubSede
                LEFT OUTER JOIN rh_colaboradores as C on C.idColab = U.idColab
            WHERE U.ativo =1 AND ((U.login = :login ) OR (P.cpf = :login_cpf) OR (P.email_corporativo = :login))";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':login', $usuario, PDO::PARAM_STR);
    $stmt->bindParam(':login_cpf', $login_cpf, PDO::PARAM_STR);
    $stmt->execute();

    $rows = $stmt->fetchAll();

    if (count($rows) > 0) {
        //- verifica se a senha confere
        //
        if (! password_verify($senha, $rows[0]['senha'])) {
            $conn = null;
            die('{"status":"error", "message":"Usuário ou Senha errada!"}');
        }  
        //
        $idUsuario = $rows[0]["idUsuario"];
        $_SESSION['idUsuario'] = $idUsuario;
        $_SESSION['idColab'  ] = $rows[0]["idColab"];   //-- necessário para SUPERVISOR e Colaborador
        $_SESSION['idOrgao'  ] = $rows[0]["idOrgao"];   //-- necessário para SUPERVISOR e Colaborador
        $_SESSION['idPessoa' ] = $rows[0]["idPessoa"];
        $_SESSION['nmLogin'  ] = $rows[0]["login"];
        $_SESSION['nmUsuario'] = $rows[0]["nome"];
        $_SESSION['idGrupo'  ] = $rows[0]["idUsuarioGrupo"];
        $_SESSION['email'    ] = $rows[0]["email"];      //-- necessária para a resposta de e-mail
        $_SESSION['idSubSede'] = $rows[0]["idSubSede"];
        $_SESSION['dsSubSede'] = $rows[0]["dsSubSede"];
        $_SESSION['idEmpresa'] = $rows[0]["idEmpresa"];
        //
        $idUsuarioGrupo = $rows[0]["idUsuarioGrupo"];
        //
        if (! empty($rows[0]["foto"])) {
            $_SESSION['perfil'] = $rows[0]["foto"];
        } else {
            $_SESSION['perfil'] = "perfil.png";
        }  

        //
        //- CRIA O LOGIN_ID
        //

            $sql = "INSERT INTO rh_logins ( idUsuario, dtLogin, ip, sisoper, browser, hardware )
                                VALUES ( :idUsuario, :agora, :ip, :sisoper, :browser, :hardware )";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':idUsuario', $idUsuario);
            $stmt->bindParam(':agora', $agora);
            $stmt->bindParam(':ip', $ip);
            $stmt->bindParam(':sisoper', $sisoper);
            $stmt->bindParam(':browser', $browser);
            $stmt->bindParam(':hardware', $hardware);
            $stmt->execute();
            $_SESSION['idLogin'] = $conn->lastInsertId();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Acesso liberado!', 
            'redirect' => 'solicitacao.php'
        ]); 
        exit;                
    } else{
        echo json_encode([
            'status' => 'error', 
            'message' => 'Credenciais de acesso incorretas.'
        ]);
        exit;
    }

}

if ($acao === 'recuperar_senha') {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // TODO: Lógica para envio de e-mail de redefinição
    echo json_encode([
        'status' => 'success', 
        'message' => 'Instruções de recuperação enviadas para o e-mail cadastrado.'
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Ação inválida.']);
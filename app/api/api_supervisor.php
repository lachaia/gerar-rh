<?PHP 
//
// api_supervisor.php | API - Retorna os dados do supervisor
// (C)haia, 12/11/2025;
//

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include dirname(__DIR__) . '/includes/conexao_gerar.php';

// Valida parâmetro
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['erro' => 'Parâmetro id inválido.']);
    exit;
}

$colaborador_id = (int) $_GET['id'];

//
//- Recupera dados do Colaborador
//
    $sql = "SELECT  C.idColab as colaborador_id, 
                        P.nome as colaborador_nome,
                        P.email_corporativo as colaborador_email,
                        P.celular_corporativo as colaborador_telefone, 
                        C.idOrgao as colaborador_orgao_id, 
                        O.descricao as colaborador_orgao_nome,  
                        O.nivel as colaborador_orgao_nivel,
                        O.idSupervisor as gestor_orgao_id,
                        O2.nivel as gestor_orgao_nivel, 
                        O2.descricao as gestor_orgao_nome,

                    -- DADOS DO SUPERVISOR
                    CASE 
                        WHEN O.nivel = O2.nivel THEN 
                            (SELECT C2.idColab 
                            FROM rh_colaboradores C2 
                            WHERE C2.idOrgao = O.idOrgao AND C2.dcLider = 1 LIMIT 1)
                        ELSE 
                            (SELECT C3.idColab 
                            FROM rh_colaboradores C3 
                            WHERE C3.idOrgao = O.idSupervisor LIMIT 1)
                    END AS gestor_id,

                    CASE 
                        WHEN O.nivel = O2.nivel THEN 
                            (SELECT U2.foto 
                            FROM rh_colaboradores C2
                            LEFT OUTER JOIN rh_usuarios U2 ON U2.idColab = C2.idColab
                            WHERE C2.idOrgao = O.idOrgao AND C2.dcLider = 1 LIMIT 1)
                        ELSE 
                            (SELECT C3.idColab 
                            FROM rh_colaboradores C3 
                            LEFT OUTER JOIN rh_usuarios U3 ON U3.idColab = C3.idColab
                            WHERE C3.idOrgao = O.idSupervisor LIMIT 1)
                    END AS gestor_foto,

                    CASE 
                        WHEN O.nivel = O2.nivel THEN 
                            (SELECT P2.nome 
                            FROM rh_colaboradores C2 
                            INNER JOIN rh_pessoas P2 ON P2.idPessoa = C2.idPessoa 
                            WHERE C2.idOrgao = O.idOrgao AND C2.dcLider = 1 LIMIT 1)
                        ELSE 
                            (SELECT P3.nome 
                            FROM rh_colaboradores C3 
                            INNER JOIN rh_pessoas P3 ON P3.idPessoa = C3.idPessoa 
                            WHERE C3.idOrgao = O.idSupervisor LIMIT 1)
                    END AS gestor_nome,

                    CASE 
                        WHEN O.nivel = O2.nivel THEN 
                            (SELECT P2.email_corporativo 
                            FROM rh_colaboradores C2 
                            INNER JOIN rh_pessoas P2 ON P2.idPessoa = C2.idPessoa 
                            WHERE C2.idOrgao = O.idOrgao AND C2.dcLider = 1 LIMIT 1)
                        ELSE 
                            (SELECT P3.email_corporativo FROM rh_colaboradores C3 
                            INNER JOIN rh_pessoas P3 ON P3.idPessoa = C3.idPessoa 
                            WHERE C3.idOrgao = O.idSupervisor LIMIT 1)
                    END AS gestor_email,

                    CASE 
                        WHEN O.nivel = O2.nivel THEN 
                            (SELECT P2.celular_corporativo 
                            FROM rh_colaboradores C2 
                            INNER JOIN rh_pessoas P2 ON P2.idPessoa = C2.idPessoa 
                            WHERE C2.idOrgao = O.idOrgao AND C2.dcLider = 1 LIMIT 1)
                        ELSE 
                            (SELECT P3.celular_corporativo 
                            FROM rh_colaboradores C3 
                            INNER JOIN rh_pessoas P3 ON P3.idPessoa = C3.idPessoa 
                            WHERE C3.idOrgao = O.idSupervisor LIMIT 1)
                    END AS gestor_telefone,

                    CASE 
                        WHEN O.nivel = O2.nivel THEN 
                            (SELECT F2.nome 
                            FROM rh_colaboradores C2 
                            INNER JOIN rh_funcoes F2 ON F2.idFuncao = C2.idFuncao 
                            WHERE C2.idOrgao = O.idOrgao AND C2.dcLider = 1 LIMIT 1)
                        ELSE 
                            (SELECT F3.nome 
                            FROM rh_colaboradores C3 
                            INNER JOIN rh_funcoes F3 ON F3.idFuncao = C3.idFuncao 
                            WHERE C3.idOrgao = O.idSupervisor LIMIT 1)
                    END AS gestor_funcao

                FROM RH.rh_colaboradores C
                INNER JOIN RH.rh_pessoas P ON P.idPessoa = C.idPessoa
                INNER JOIN RH.rh_organograma O ON O.idOrgao = C.idOrgao
                INNER JOIN RH.rh_organograma O2 ON O2.idOrgao = O.idSupervisor
                WHERE C.idColab = :colaborador_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':colaborador_id', $colaborador_id, PDO::PARAM_INT);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

if( ! $dados ){
    //
    //- COLABORADOR NÃO ENCONTRADO
    //
    http_response_code(404);
    echo json_encode([
        'status' => 404,
        'mensagem' => 'Colaborador não encontrado.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;    
}

extract( $dados );

if( ! empty( $gestor_id )){
    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'gestor_id' => $gestor_id,
        'gestor_nome' => $gestor_nome,
        'gestor_orgao_id' => $gestor_orgao_id,
        'gestor_orgao_nome' => $gestor_orgao_nome,
        'gestor_orgao_nivel' => $gestor_orgao_nivel,        
        'gestor_email' => $gestor_email,
        'gestor_telefone' => $gestor_telefone,
        'gestor_foto' => gestor_foto($gestor_id, $conn),
        'gestor_funcao' => $gestor_funcao
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

//
//- Guarda dados iniciais
//
    $_gestor_nome = $gestor_nome ?? "Não Cadastrado";
    $_gestor_orgao_id = $gestor_orgao_id;
    $_gestor_orgao_nivel = $gestor_orgao_nivel;
    $_gestor_orgao_nome = $gestor_orgao_nome;

//
//- PROCURA O PRIMEIRO GESTOR DA HIERARQUIA
//
    for( $i = $gestor_orgao_nivel; $i > 0; $i-- ) {
        // echo "<br>Nivel = $i | Orgao Gestor ID: $gestor_orgao_id<br>";
        $sql = "SELECT  C.idColab as colaborador_id, 
                        P.nome as colaborador_nome,
                        P.email_corporativo as colaborador_email,
                        P.celular_corporativo as colaborador_telefone,
                        F.nome as colaborador_funcao, 
                        C.idOrgao as orgao_id, 
                        O.descricao as orgao_nome,  
                        O.nivel as orgao_nivel,
                        O.idSupervisor as gestor_orgao_id,
                        O2.nivel as gestor_orgao_nivel, 
                        O2.descricao as gestor_orgao_nome
                    FROM RH.rh_colaboradores C
                    INNER JOIN RH.rh_pessoas P ON P.idPessoa = C.idPessoa
                    INNER JOIN RH.rh_organograma O ON O.idOrgao = C.idOrgao
                    INNER JOIN RH.rh_organograma O2 ON O2.idOrgao = O.idSupervisor
                    INNER JOIN RH.rh_funcoes F ON F.idFuncao = C.idFuncao
                    WHERE O.idOrgao = :idOrgao";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idOrgao', $gestor_orgao_id, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        //
        if( $linha ){
            extract($linha);
            break;
        } else{
            $sql = "SELECT idSupervisor as gestor_orgao_id FROM rh_organograma WHERE idOrgao = :idOrgao";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':idOrgao', $gestor_orgao_id, PDO::PARAM_INT);
            $stmt->execute();
            $linha = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($linha);
        }

    }

// Retorno JSON
if ($linha && $i>0 ) {
    extract( $linha );
    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'gestor_id' => $colaborador_id,
        'gestor_nome' => $colaborador_nome,
        'gestor_orgao_id' => $orgao_id,
        'gestor_orgao_nivel' => $orgao_nivel,        
        'gestor_email' => $colaborador_email,
        'gestor_telefone' => $colaborador_telefone,
        'gestor_foto' => gestor_foto($colaborador_id, $conn),
        'gestor_funcao' => $colaborador_funcao,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 404,
        'gestor_nome' => $_gestor_nome,
        'gestor_orgao_id' => $_gestor_orgao_id,
        'gestor_orgao_nome' => $_gestor_orgao_nome,
        'gestor_orgao_nivel' => $_gestor_orgao_nivel,
        'gestor_email' => "",
        'gestor_telefone' => "",
        'gestor_foto' => "",
        'gestor_funcao' => "",
        'mensagem' => 'Supervisor não encontrado.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

exit;

function gestor_foto( $id, $conn ){
    $sql = "SELECT foto FROM rh_usuarios WHERE idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $id, PDO::PARAM_INT);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    $foto = null;
    if( $linha ) $foto = $linha['foto'];
    return $foto;
}
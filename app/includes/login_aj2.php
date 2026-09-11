<?PHP 
//
// login_aj2.php | Verifica o PERFIL do colaborador
//

session_start();

$login = $_POST['login'];

include "conexao_gerar.php";
//include "debug.php";

global $conn;   

// 1: Identificar se o usuário é do RH ou não
//
$login_cpf = preg_replace('/\D/', '', $login);
if(empty($login_cpf)) $login_cpf = $login;

$sql = "SELECT U.idColab, U.idUsuarioGrupo, P.email_corporativo, U.login, P.cpf, C.dcLider, O.nivel, O.staff,
            U.dcCIPA, U.dcBrigada
        FROM rh_usuarios U
        INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa
        LEFT OUTER JOIN rh_colaboradores C on C.idColab = U.idColab
        LEFT OUTER JOIN rh_organograma O on O.idOrgao = C.idOrgao
        WHERE login like '%$login%' 
            OR P.cpf like '%$login_cpf%' 
            OR P.email_corporativo like '%$login%' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();

//debug( $sql );

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$rh = $colab = $gestor = $recrutamento = false;

if ($stmt->rowCount() > 0) {
    extract($row);
    
    //debug( json_encode($row, JSON_PRETTY_PRINT) );
    /*
    login_aj2.php | 2026-07-24 09:41:15 
    {
        "idColab": 1,
        "idUsuarioGrupo": 9,
        "email_corporativo": "luiz.chaia@gerar.org.br",
        "login": "luiz.chaia",
        "cpf": "57160600991",
        "dcLider": 0,
        "nivel": 4,
        "staff": 0,
        "dcCIPA": 1,
        "dcBrigada": 1
    }
    */
    // Grupos dos Usuários
    // 1 - Usuários do RH
    // 3 - Psicólogos do RH
    // 4 - Termos de Responsabilidade de Equipamentos
    // 6 - Recrutamento & Seleção (Vagas)
    // 7 - Usuários de TI
    // 8 - Candidatos (Externos)
    // 9 - Super Usuário (manutenção)
    //
    if(empty($nivel)) $nivel = 99;
    if(empty($staff)) $staff = 0;
    //
    if ( $idUsuarioGrupo == 1 || $idUsuarioGrupo == 3 || $idUsuarioGrupo == 5 || $idUsuarioGrupo == 9 ) $rh = true; 
    if ( ! empty($idColab)) $colab = true; 
    if ( $dcLider ==1 || ($nivel<6 && $staff==0 ) ) $gestor = true; 
    if ($idUsuarioGrupo == 5 || $idUsuarioGrupo == 9) $recrutamento = true;
    //
    $retorna = [
        "status" => true,
        "msg" => '<div class="alert alert-success" role="alert">Sucesso: Usuário Encontrado!</div>',
        "rh" => $rh, "colab" => $colab, "gestor" => $gestor, "recrutamento" => $recrutamento,
        "idColab" => $idColab, 'dcCIPA' => $dcCIPA, 'dcBrigada' => $dcBrigada
    ];
}else{
    $retorna = [
        "status" => false,
        "msg" => '<div class="alert alert-danger" role="alert">ERRO: Usuário Inválido!</div>'
    ];
}

$conn = null;
die(json_encode($retorna, JSON_PRETTY_PRINT));
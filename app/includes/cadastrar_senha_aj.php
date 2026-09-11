<?PHP
//
// cadastrar_senha_aj.php | SALVA Auto-Cadastro de Usuário Colaborador no Sistema de RH
// (C)haia, 07/05/2025, (RH)

session_start();

$idModulo = 1; // USUÁRIOS

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

if ( empty($idColab) || empty($login) || empty($senha) ) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO"];
die(json_encode($response));
//
cadastrar_senha_aj.php | 2025-05-07 17:20:54 
{
    "idColab": "10",
    "login": "jose.toledo",
    "senha": "asd",
    "token: 7"
}
*/

include_once("conexao_gerar.php");

$agora = date("Y-m-d H:i:s");

$sql = "UPDATE rh_token SET data_reset = '$agora' WHERE idToken = :idToken";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idToken', $idToken );
$consulta->execute();

//-- verifica se usuário já existe
//

$sql = "SELECT * FROM rh_usuarios WHERE idColab = :idColab";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idColab', $idColab );
$consulta->execute();

if ($consulta->rowCount() > 0) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> este colaborador já possui um usuário cadastrado.!
            </div>'
    ];
    die(json_encode($retorno));
}

//-- carrega dados essenciais
//

$sql = "SELECT idEmpresa, idPessoa, idSubSede
            FROM rh_colaboradores C
            WHERE C.idColab = :idColab";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idColab', $idColab );
$consulta->execute();
$linha = $consulta->fetch(PDO::FETCH_ASSOC);
extract( $linha );

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$idUsuarioGrupo = 2; //- Usuário Geral 
$ativo = 1;

$sql = "INSERT INTO rh_usuarios (idEmpresa, idUsuarioGrupo, idPessoa, idColab, login, senha, ativo, idSubSede) VALUES 
            (:idEmpresa, :idUsuarioGrupo, :idPessoa, :idColab, :login, :senha, :ativo, :idSubSede)";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idEmpresa', $idEmpresa );
$consulta->bindParam(':idUsuarioGrupo', $idUsuarioGrupo );
$consulta->bindParam(':idPessoa', $idPessoa );
$consulta->bindParam(':idColab', $idColab );
$consulta->bindParam(':login', $login );
$consulta->bindParam(':senha', $senhaHash );
$consulta->bindParam(':ativo', $ativo );
$consulta->bindParam(':idSubSede', $idSubSede );
// 
if( $consulta->execute() ){
    $retorno = [
        "status"=> true,
        "msg"=> "Usuário criado com sucesso!"
    ];
} else{
    $retorno = [
        "status"=> false,
        "msg"=> "Erro ao criar Usuário!"
    ];
}
$conn = null;
die( json_encode( $retorno ) );
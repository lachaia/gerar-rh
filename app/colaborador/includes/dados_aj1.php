<?PHP 
//
// dados_aj1.php | Devolve os dados do Endereço para Edição
// (C)haia, 16/05/2025 | 08/10/2025
//

session_start();

$idModulo = 10; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
require_once "../../includes/conexao_gerar.php"; // Inclua sua conexão com o banco de dados

if( $parametros ){
    extract( $parametros );
} else{
    $retorno = [
        "status" => false,
        "msg" => "Nenhum parâmetro recebido!"
    ];
    die( json_encode($retorno) );
}

if( !isset($idEndereco) || empty($idEndereco) ){
    $retorno = [
        "status" => false,
        "msg" => "ID do Endereço inválido!"
    ];
    die( json_encode($retorno) );
}

if (empty($_SESSION['idLogin']) || empty($_SESSION['idPessoa'])) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Sessão inválida."]));
}

$sql = "SELECT E.*, T.dsTipoEndereco
            FROM RH.rh_enderecos E
            INNER JOIN rh_enderecos_tipo T on T.idTipoEndereco = E.idTipoEndereco
            WHERE idEndereco = :idEndereco";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":idEndereco", $idEndereco, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

// Só pode ler o endereço que é da própria pessoa logada.
if ($dados && (int) $dados['idPessoa'] !== (int) $_SESSION['idPessoa']) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

if ($dados) {
    $retorno = [
        "status" => true,
        "dados" => $dados
    ];
} else {
    $retorno = [
        "status" => false,
        "msg" => "Nenhum endereço encontrado!"
    ];
}
$conn = null;
echo json_encode($retorno);

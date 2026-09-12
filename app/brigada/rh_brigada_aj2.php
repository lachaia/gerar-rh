<?PHP
//
//- rh_brigada_aj2.php | INCLUIR Pessoa
// (C)haia, 17/06/2025 | 18/07/2025

$idModulo = 10; // Brigada

session_start();
if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcBrigada']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( empty( $dados['nome'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar o Nome da Pessoa!"]) );
}
if( empty( $dados['nomeSocial'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar o Nome Social do Usuário!"]) );
}
if( empty( $dados['cpf'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar o CPF do usuário!"]) );
}

//
//- Verifica se Nome de Pessoa ou CPF já existe
//
    $sql = "SELECT idPessoa FROM rh_pessoas WHERE nome like :nome OR cpf like :cpf";
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( ':nome', $dados['nome'], PDO::PARAM_STR );
    $stmt->bindParam( ':cpf', $dados['cpf'],   PDO::PARAM_STR );
    $result = $stmt->execute();
    if($stmt->rowCount()>0){
        die( json_encode(["status" => false, "msg" => "Pessoa já cadastrada, tente outra!"]) );
    }

$sql = "INSERT INTO rh_pessoas
            ( nome, nomeSocial, cpf, telefone, email, ativo, sexo )
            VALUES (:nome, :nomeSocial, :cpf, :telefone, :email, 1, :sexo);";

try{
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( ':nome',       $dados['nome'],       PDO::PARAM_STR );
    $stmt->bindParam( ':nomeSocial', $dados['nomeSocial'], PDO::PARAM_STR );
    $stmt->bindParam( 'cpf',         $dados['cpf'],        PDO::PARAM_STR );
    $stmt->bindParam( 'telefone',    $dados['telefone'],   PDO::PARAM_STR );
    $stmt->bindParam( 'email',       $dados['email'],      PDO::PARAM_STR );
    $stmt->bindParam( 'sexo',        $dados['sexo'],       PDO::PARAM_STR );
    $result = $stmt->execute();
    $idPessoa = $conn->lastInsertId();
    //
    f_log("INC", "Incluiu pessoa ID: " . $idPessoa . $dados['nome'] . " | " . $dados['cpf'] . " | " . $dados['nomeSocial'], "pessoas", $idModulo, $idPessoa);
    echo json_encode(["status" => true, "msg" => "Inclusão bem sucedida!", "idPessoa" => $idPessoa, "nome" => $dados['nome']]);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    $errorInfo = $stmt->errorInfo(); // Obtém informações do erro
    $retorno = "Sinto muito, deu erro ao atualizar! Detalhes: " . $errorInfo[2];
    echo json_encode(["status" => false, "msg" => "$retorno"]);
}

$conn = null;


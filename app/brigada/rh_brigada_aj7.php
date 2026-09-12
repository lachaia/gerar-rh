<?PHP
//
//- rh_brigada_aj7.php | Inclui novo Tipo de Ocorrência
//(C)haia, 2025-06-20

session_start();
if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcBrigada']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}


$resposta = [
    "status" => true,
    "msg" => "<div class='alert alert-success'>TESTE REALIZADO COM SUCESSO!</div>"
];

$idModulo = 10; // Brigada

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) {
    extract($dados);
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    //
    $criado_por = $_SESSION['nmLogin'];
    $idLogin = $_SESSION['idLogin'];
    //    
} else {
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

//
//- EVITAR DUPLICAÇÃO
//
    // 1. Remove espaços extras antes e depois da string digitada
    $descricao_limpa = trim($descricao);

    // 2. Compara ambos os lados em minúsculo (LOWER)
    $sql = "SELECT id FROM rh_brigada_tipo_ocorrencia WHERE LOWER(descricao) = LOWER(:descricao)";
    $stmt = $conn->prepare($sql);

    // Passa a variável já limpa
    $stmt->bindParam(':descricao', $descricao_limpa, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Dica: use o charset UTF-8 no json_encode em vez de entidades HTML como &aacute;
        echo json_encode(["status" => false, "msg" => "Tipo de Ocorrência já cadastrado!"], JSON_UNESCAPED_UNICODE);
        exit;
    }

$sql = "INSERT INTO rh_brigada_tipo_ocorrencia (descricao, criado_por, idLogin) 
        VALUES (:descricao, :criado_por, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
$stmt->bindParam(':criado_por', $criado_por, PDO::PARAM_STR);
$stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);

if ( $stmt->execute()) {
    $idTipoOcorrencia = $conn->lastInsertId();
    
    // Log de criação
    f_log("INC", "Criou novo Tipo de Ocorrência: $descricao", "rh_brigada_tipo_ocorrencia", $idModulo, $idTipoOcorrencia);
    $retorno = [
        "status" => true,
        "msg" => "Tipo de Ocorrência criado com sucesso!",
        "idTipo" => $idTipoOcorrencia,
        'dsTipo' => $descricao
    ];
    
    echo json_encode( $retorno);
} else {
    echo json_encode(["status" => false, "msg" => "Erro ao criar Tipo de Ocorrência!"]);
}
$conn = null; // Fecha a conexão
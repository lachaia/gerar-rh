<?PHP
//
//- rh_cipa_aj7.php | Inclui novo Tipo de Ocorrência
//(C)haia, 2025-06-20

session_start();

$resposta = [
    "status" => true,
    "msg" => "<div class='alert alert-success'>TESTE REALIZADO COM SUCESSO!</div>"
];

$idModulo = 11; // CIPA

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

$sql = "INSERT INTO rh_cipa_tipo_ocorrencia (descricao, criado_por, idLogin) 
        VALUES (:descricao, :criado_por, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
$stmt->bindParam(':criado_por', $criado_por, PDO::PARAM_STR);
$stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);

if ( $stmt->execute()) {
    $idTipoOcorrencia = $conn->lastInsertId();
    
    // Log de criação
    f_log("INC", "Criou novo Tipo de Ocorrência: $descricao", "rh_cipa_tipo_ocorrencia", $idModulo, $idTipoOcorrencia);
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
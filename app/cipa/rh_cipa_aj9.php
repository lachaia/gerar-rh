<?PHP
//
//- rh_cipa_aj9.php | Salva Inclusão de Atendimento
// (C)haia, 24/06/2025

$idModulo = 11; // CIPA

session_start();

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
    $stringDados = implode(", ", $dados);
} else {
    die( json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]) );
}


//include_once "../includes/debug.php";
//debug( json_encode($dados, JSON_PRETTY_PRINT) );
/*
 rh_cipa_aj9.php | 2025-07-11 08:42:36 
{
    "idMembro": "2",
    "data_ocorrencia": "2025-07-01T10:10",
    "nmPessoaAtendida": "Maria José",
    "idTipoOco": "1",
    "local_ocorrencia": "Rua Frederico Westfallen, 354",
    "descricao": "tropeçou e caiu, quebrando o braço",
    "acao_realizada": "ABERTO CAT",
    "encaminhamento": "Pronto socorro"
}
*/
/*
$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-primary'>TESTE REALIZADO COM SUCESSO!</div>"
];
die( json_encode($retorno) );
*/
if( isset($_SESSION['idLogin']) && (!empty($_SESSION['dcCIPA']) || (int) ($_SESSION['idGrupo'] ?? 0) === 9) ){
    $idLogin = $_SESSION['idLogin'];
    $idSubSede = $_SESSION['idSubSede'];
    $criado_por = $_SESSION['nmLogin'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";  
} else {
    die( json_encode(["status" => false, "msg" => "Usuário não autenticado!"]) );
}

$sql = "INSERT INTO rh_cipa_atendimentos 
        (idSubSede, data_ocorrencia, idCipeiro, nome_paciente, tipo_ocorrencia, local_ocorrencia, descricao, acao_realizada, encaminhamento, criado_em, criado_por, idLogin)
        VALUES 
        (:idSubSede, :data_ocorrencia, :idCipeiro, :nome_paciente, :tipo_ocorrencia, :local_ocorrencia, :descricao, :acao_realizada, :encaminhamento, NOW(), :criado_por, :idLogin)";

$stmt = $conn->prepare($sql);

$stmt->bindParam(':idSubSede',       $idSubSede,        PDO::PARAM_INT);
$stmt->bindParam(':data_ocorrencia', $data_ocorrencia,   PDO::PARAM_STR);
$stmt->bindParam(':idCipeiro',       $idMembro,         PDO::PARAM_INT);
$stmt->bindParam(':nome_paciente',   $nmPessoaAtendida, PDO::PARAM_STR);
$stmt->bindParam(':tipo_ocorrencia', $idTipoOco,        PDO::PARAM_INT);
$stmt->bindParam(':local_ocorrencia',$local_ocorrencia, PDO::PARAM_STR);
$stmt->bindParam(':descricao',       $descricao,        PDO::PARAM_STR);
$stmt->bindParam(':acao_realizada',  $acao_realizada,   PDO::PARAM_STR);
$stmt->bindParam(':encaminhamento',   $encaminhamento,   PDO::PARAM_STR);
$stmt->bindParam(':criado_por',      $criado_por,       PDO::PARAM_STR);
$stmt->bindParam(':idLogin',         $idLogin,          PDO::PARAM_INT);

if ( $stmt->execute()) {
    $idAtendimento = $conn->lastInsertId();
    
    // Log de criação
    f_log("INC", "Criou novo atendimento de CIPA: ($stringDados)", "rh_atendimento", $idModulo, $idAtendimento);
    $retorno = [
        "status" => true,
        "msg" => "Atendimento realizado com sucesso!"
    ];
    
    echo json_encode( $retorno);
} else {
    echo json_encode(["status" => false, "msg" => "Erro ao criar Tipo de Ocorrência!"]);
}
$conn = null; // Fecha a conexão
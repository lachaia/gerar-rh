<?PHP
//
//- rh_brigada_aj11.php | Salva ALTERAÇÃO de Atendimento
// (C)haia, 24/06/2025

$idModulo = 10; // Brigada

session_start();

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
    $stringDados = implode(", ", $dados);
    //
} else {
    die( json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]) );
}

/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
/*
 rh_brigada_aj11.php | 2025-06-24 15:52:30 
{
    "idMembro": "9",
    "data_ocorrencia": "2025-06-24T11:31",
    "nmPessoaAtendida": "Maria das Dores",
    "idTipoOco": "3",
    "local_ocorrencia": "sala de aula",
    "descricao": "jovem apresentou sintomas de crise de ansiedade. ",
    "acao_realizada": "levado \u00e0 sala dos professores, ficou em observa\u00e7\u00e3o. bebeu \u00e1gua. se acalmou",
    "encaminhamento": "encaminhado \u00e0 sala de aula",
    "idAtendimento": "1"
}
*/

/*
$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-primary'>TESTE REALIZADO COM SUCESSO!</div>"
];
die( json_encode($retorno) );
*/
if( isset($_SESSION['idLogin']) ){
    $idLogin = $_SESSION['idLogin'];
    $idSubSede = $_SESSION['idSubSede'];
    $criado_por = $_SESSION['nmLogin'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";  
} else {
    die( json_encode(["status" => false, "msg" => "Usuário não autenticado!"]) );
}

// - RECUPERA dados antes da alteração
$sql = "SELECT * FROM rh_atendimentos WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $idAtendimento, PDO::PARAM_INT);
if ( !$stmt->execute() ) {
    die( json_encode(["status" => false, "msg" => "Erro ao recuperar dados do atendimento!"]) );
}
$atendimento = $stmt->fetch(PDO::FETCH_ASSOC);
$dadosAntes = implode(", ", array_map('strval', $atendimento));


//
//-- Atualiza novos dados do atendimento
//

$sql = "UPDATE rh_atendimentos SET 
            idSubSede        = :idSubSede,
            data_ocorrencia  = :data_ocorrencia,
            idBrigadista     = :idBrigadista,
            nome_paciente    = :nome_paciente,
            tipo_ocorrencia  = :tipo_ocorrencia,
            local_ocorrencia = :local_ocorrencia,
            descricao        = :descricao,
            acao_realizada   = :acao_realizada,
            encaminhamento   = :encaminhamento
        WHERE id = :id"; // <== ajuste aqui com a PK

$stmt = $conn->prepare($sql);

$stmt->bindParam(':id',              $idAtendimento,    PDO::PARAM_INT);
$stmt->bindParam(':idSubSede',       $idSubSede,        PDO::PARAM_INT);
$stmt->bindParam(':data_ocorrencia', $data_ocorrencia,  PDO::PARAM_STR);
$stmt->bindParam(':idBrigadista',    $idMembro,         PDO::PARAM_INT);
$stmt->bindParam(':nome_paciente',   $nmPessoaAtendida, PDO::PARAM_STR);
$stmt->bindParam(':tipo_ocorrencia', $idTipoOco,        PDO::PARAM_INT);
$stmt->bindParam(':local_ocorrencia',$local_ocorrencia, PDO::PARAM_STR);
$stmt->bindParam(':descricao',       $descricao,        PDO::PARAM_STR);
$stmt->bindParam(':acao_realizada',  $acao_realizada,   PDO::PARAM_STR);
$stmt->bindParam(':encaminhamento',  $encaminhamento,   PDO::PARAM_STR);

if ( $stmt->execute()) {
    // Log de criação
    f_log("ALT", "Alterou atendimento de Brigada | Antes:($dadosAntes) | Novos:($stringDados)", "rh_atendimento", $idModulo, $idAtendimento);
    $retorno = [
        "status" => true,
        "msg" => "Atendimento atualizado com sucesso!"
    ];
    
    echo json_encode( $retorno);
} else {
    echo json_encode(["status" => false, "msg" => "Erro ao criar Tipo de Ocorrência!"]);
}
$conn = null; // Fecha a conexão
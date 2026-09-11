<?PHP
//
//- rh_brigada_aj9.php | Salva Inclusão de Atendimento
// (C)haia, 24/06/2025

$idModulo = 10; // Brigada

session_start();

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
    $stringDados = json_encode($dados, JSON_UNESCAPED_UNICODE);
    //$stringDados = implode(", ", $dados);
} else {
    die( json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]) );
}

/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
/*
 rh_brigada_aj9.php | 2025-11-24 11:04:05 
{
    "brigadistas": [
        "8",
        "11"
    ],
    "data_ocorrencia": "2025-11-24T10:59",
    "nmPessoaAtendida": "CHAIA",
    "idTipoOco": "12",
    "local_ocorrencia": "TESTE",
    "descricao": "TESTE",
    "acao_realizada": "TESTE",
    "encaminhamento": "TESTE"
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
    include_once "../includes/f_linha_do_tempo.php"; 
} else {
    die( json_encode(["status" => false, "msg" => "Usuário não autenticado!"]) );
}

$ids = $_POST['brigadistas'] ?? []; // Garante que seja array

$sql = "INSERT INTO rh_atendimentos 
        (idSubSede, data_ocorrencia, nome_paciente, tipo_ocorrencia, local_ocorrencia, descricao, acao_realizada, encaminhamento, criado_em, criado_por, idLogin)
        VALUES 
        (:idSubSede, :data_ocorrencia, :nome_paciente, :tipo_ocorrencia, :local_ocorrencia, :descricao, :acao_realizada, :encaminhamento, NOW(), :criado_por, :idLogin)";

$stmt = $conn->prepare($sql);

$stmt->bindParam(':idSubSede',       $idSubSede,        PDO::PARAM_INT);
$stmt->bindParam(':data_ocorrencia', $data_ocorrencia,   PDO::PARAM_STR);
//$stmt->bindParam(':idBrigadista',    $idMembro,         PDO::PARAM_INT);
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
    f_log("INC", "Criou novo atendimento de Brigada: ($stringDados)", "rh_atendimento", $idModulo, $idAtendimento);
    $retorno = [
        "status" => true,
        "msg" => "Atendimento realizado com sucesso!"
    ];
    
    //
    //- INSERIR PARTICIPANETES DO ATENDIMENTO
    //
        foreach ($ids as $idBrigadista) {
            // Verifica se o idBrigadista é válido
            if (is_numeric($idBrigadista) && $idBrigadista > 0) {
                // Prepara a inserção para cada brigadista
                $sql = "INSERT INTO rh_brigada_atend_membros (idAtendimento, idBrigadista) 
                        VALUES (:idAtendimento, :idBrigadista)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':idAtendimento', $idAtendimento, PDO::PARAM_INT);
                $stmt->bindParam(':idBrigadista', $idBrigadista, PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    // Se falhar, registra o erro e continua com os outros
                    f_log("ERR", "Erro ao inserir brigadista no atendimento: " . implode(", ", $stmt->errorInfo()), "rh_brigada_atend_membros", $idModulo, $idAtendimento);
                }
            }
        }  

    //
    //-- INSERIR AÇÃO NA LINHA DO TEMPO DO BRIGADISTA
    //
        foreach ($ids as $idBrigadista) {
            // Verifica se o idBrigadista é válido
            if (is_numeric($idBrigadista) && $idBrigadista > 0) {
                //
                //- recupera idPessoa do brigadista
                //
                $sqlPessoa = "SELECT idPessoa FROM rh_brigadistas WHERE id = :idBrigadista";
                $stmtPessoa = $conn->prepare($sqlPessoa);
                $stmtPessoa->bindParam(':idBrigadista', $idBrigadista, PDO::PARAM_INT);
                $stmtPessoa->execute();
                $dados = $stmtPessoa->fetch(PDO::FETCH_ASSOC);
                if (!$dados) {
                    // Se não encontrar o brigadista, continua para o próximo
                    continue;
                }

                $idPessoa = $dados['idPessoa'];
                $tipo = 33; // Evento de Brigada
                $descricao = "Fez Atendimento de ocorrência em $data_ocorrencia";
                f_ldt( $tipo, $idPessoa, $descricao, $idAtendimento);
            }
        }

    echo json_encode( $retorno);
} else {
    echo json_encode(["status" => false, "msg" => "Erro ao criar Tipo de Ocorrência!"]);
}
$conn = null; // Fecha a conexão
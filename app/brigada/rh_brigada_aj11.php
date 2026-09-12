<?PHP
//
//- rh_brigada_aj11.php | Salva ALTERAÇÃO de Atendimento
// (C)haia, 24/06/2025

$idModulo = 10; // Brigada

session_start();

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
    $stringDados = json_encode($dados, JSON_UNESCAPED_UNICODE);
    //
} else {
    die( json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]) );
}

/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
/*
 rh_brigada_aj11.php | 2025-11-25 08:48:35 
{
    "brigadistas": [
        "1",
        "2"
    ],
    "data_ocorrencia": "2025-11-24T16:29",
    "nmPessoaAtendida": "MARIA EDUARDO GOMES",
    "idTipoOco": "7",
    "local_ocorrencia": "SALA DE AULA",
    "descricao": "CRISE DE RINITE",
    "acao_realizada": "LEVADO PARA TOMAR AR PURO NO LADO DE FORA DO P\u00c1TIO\nMELHOROU. ",
    "encaminhamento": "DEVOLVIDA \u00c0 SALA DE AULA",
    "idAtendimento": "41"
}
/*
$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-primary'>TESTE REALIZADO COM SUCESSO!</div>"
];
die( json_encode($retorno) );
*/
if( isset($_SESSION['idLogin']) && (!empty($_SESSION['dcBrigada']) || (int) ($_SESSION['idGrupo'] ?? 0) === 9) ){
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
    $sql = "SELECT
                S.identificador as dsSubSede,
                A.*,
                O.descricao AS dsOcorrencia,

                -- Lista de nomes
                GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ') AS membros,

                -- Lista de IDs dos brigadistas participantes
                GROUP_CONCAT(B2.id ORDER BY B2.id SEPARATOR ',') AS idMembros

            FROM rh_atendimentos A
            INNER JOIN rh_subsedes S ON S.subsede_id = A.idSubSede
            INNER JOIN rh_brigada_tipo_ocorrencia O ON O.id = A.tipo_ocorrencia
            LEFT JOIN rh_brigada_atend_membros M ON M.idAtendimento = A.id 
            LEFT JOIN rh_brigadistas B2 ON B2.id = M.idBrigadista
            LEFT JOIN rh_pessoas P ON P.idPessoa = B2.idPessoa
            WHERE A.id = :idAtendimento
            GROUP BY A.id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idAtendimento', $idAtendimento, PDO::PARAM_INT);
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
    //
    //- ATUALIZA LISTA DE BRIGADISTAS QUE PARTICIPARAM DO ATENDIMENTO
    //
        $sql = "DELETE FROM rh_brigada_atend_membros WHERE idAtendimento = :idAtendimento";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idAtendimento', $idAtendimento, PDO::PARAM_INT);
        $stmt->execute();
        //
        $sql = "INSERT INTO rh_brigada_atend_membros (idAtendimento, idBrigadista) VALUES (:idAtendimento, :idBrigadista)";
        $stmt = $conn->prepare($sql);
        foreach ($brigadistas as $idBrigadista) {
            $stmt->bindParam(':idAtendimento', $idAtendimento, PDO::PARAM_INT);
            $stmt->bindParam(':idBrigadista',  $idBrigadista,  PDO::PARAM_INT);
            $stmt->execute();
        }

    echo json_encode( $retorno);
} else {
    echo json_encode(["status" => false, "msg" => "Erro ao criar Tipo de Ocorrência!"]);
}
$conn = null; // Fecha a conexão
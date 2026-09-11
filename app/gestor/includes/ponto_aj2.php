<?PHP 
//
//- ponto_aj2.php | Salva Decisão sobre a Solicitação de Ajustes de Ponto
// (C)haia, 14/11/2025
//

header('Content-Type: application/json');

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../../includes/conexao_gerar.php";
    //include_once "../../includes/debug.php";
    include_once "../../ponto/api/inc_funcoes.php";
    //
    $nmLogin = $_SESSION['nmLogin'];
} else {
    header("location: ../logout.php");
}

$paramtros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($paramtros)) extract($paramtros);
/*
debug( json_encode($paramtros, JSON_PRETTY_PRINT) );
$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-primary">
        <strong>OK!</strong> Sucesso no Teste de Sistema!
        </div>'
];
die(  json_encode($retorno) );
/*
"solicitacao_id": "12",
"decisao": "Aceitar"
*/

//
//- RECUPERA DADOS DA SOLICITAÇÃO
//
    $sql = "SELECT * FROM rh_ponto_solicitacoes WHERE id = :solicitacao_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':solicitacao_id', $solicitacao_id);
    $stmt->execute();
    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);
    $colaborador_id = $solicitacao['colaborador_id'];

//
//- RECUPERA DADOS DO COLABORADOR
//
    $sql = "SELECT 
            P.nome as nmColaborador,
            C.idColab, 
            C.horario_ini, 
            C.horario_fim, 
            S.cidade AS cidade_id,
            S.estado AS colaborador_uf,
            CONCAT(X.nome,'-',X.uf) AS dsCidade
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
        LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
        LEFT OUTER JOIN rh_cidades X ON X.idCidade = S.cidade 
        WHERE C.idColab = :colaborador_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':colaborador_id', $colaborador_id);
    $stmt->execute();
    $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);
    //
    $cidade_id = $colaborador['cidade_id']; 
    $uf = $colaborador['colaborador_uf'];
    $horario_ini = $colaborador['horario_ini'];
    $horario_fim = $colaborador['horario_fim'];

//
//- ATUALIZA O STATUS DA SOLICITAÇÃO
//
    if( $decisao == 'Aceitar' )  $decisao = "APROVADO";
    if( $decisao == 'Rejeitar' ) $decisao = "REJEITADO";

    $sql = "UPDATE rh_ponto_solicitacoes
            SET  
                aprovado_em = NOW(), 
                aprovado_por = :nmLogin, 
                decisao_obs = :decisao_obs,
                status = :status            
            WHERE id = :solicitacao_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':solicitacao_id', $solicitacao_id);
    $stmt->bindParam(':status', $decisao);
    $stmt->bindParam(':nmLogin', $nmLogin);
    $stmt->bindParam(':decisao_obs', $observacao);
    $res = $stmt->execute();

//
//- APLICA DECISÃO NO PONTO
//
    if ( $res ){
        //
        if( $decisao == 'APROVADO' ){
            $url = "https://rh.gerar.org.br/ponto/api/cron_banco_horas.php?colaborador_id={$colaborador_id}";
            $processa = file_get_contents($url);            
        }
        //
        $retorno = [
            'status' => true,
            'msg' => '<div class="alert alert-primary">
                <strong>OK!</strong> Sucesso ao decidir Solicitação!
                </div>'
        ];
        //
    } else {
        $retorno = [
            'status' => false,
            'msg' => '<div class="alert alert-danger">
                <strong>Erro!</strong> Erro ao Aprovar Solicitação!
                </div>'
        ];
    }

die( json_encode($retorno) );


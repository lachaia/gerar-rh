<?php 
//
//- rh_ponto_aj5.php | (C)haia, 19/12/2025 | SALVA ALTERAÇÃO de dados do Evento de Calendário
//

header('Content-Type: application/json');

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

$idUsuario = $_SESSION['idUsuario'];

include_once "conexao_gerar.php";
include_once "f_logs.php";
//include_once "debug.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);

$retorno_ok = [
    "status" => false,
    "msg" => '<div class="alert alert-primary">
        <strong>Youhuu!</strong> O Testes foi ok!!
        </div>'
];

//debug( "DADOS POST: " . json_encode($dados, JSON_PRETTY_PRINT) );
/*
    rh_ponto_aj5.php | 2025-12-19 13:53:52 
    {
        "id_calendario_evento": "4",
        "data_calendario": "2025-03-04",
        "tipo": "REDUZIDO",
        "descricao": "Quarta-feira de Carnaval",
        "cidade": "",
        "cidade_id": "",
        "hora_ini": "13:13",
        "hora_fim": "17:17"
    }
*/
if( empty($id_calendario_evento)){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die( json_encode( $retorno ) );
}

//
//- RECUPERA DADOS ANTIGOS
//
    $sql = "SELECT E.*, C.nome as nmCidade 
            FROM rh_ponto_calendario E
            LEFT OUTER JOIN rh_cidades C ON C.idCidade = E.cidade_id 
            WHERE id = :id";
    $params = [':id' => $id_calendario_evento];
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $dados_old = $stmt->fetch();
    
//debug( "DADOS OLD: " . json_encode($dados_old, JSON_PRETTY_PRINT) );
    /*
     rh_ponto_aj5.php | 2025-12-19 14:07:48 
        {
            "id": 4,
            "data": "2025-03-04",
            "tipo": "REDUZIDO",
            "descricao": "Quarta-feira de Carnaval",
            "hora_ini": "12:00:00",
            "hora_fim": "18:05:00",
            "abrangencia": 1,
            "estado": "BR",
            "cidade_id": null,
            "ativo": 1,
            "nmCidade": null
        }
    */

//
//- ATUALIZA DADOS
//
    if( empty($cidade_id) ) $cidade_id = null;
    if( empty($hora_ini) ) $hora_ini = null;
    if( empty($hora_fim) ) $hora_fim = null;
    
    $sql = "UPDATE rh_ponto_calendario SET 
                tipo = :tipo,
                descricao = :descricao,
                cidade_id = :cidade_id,
                hora_ini = :hora_ini,
                hora_fim = :hora_fim
            WHERE id = :id";

    $params = [
        ':tipo' => $tipo,
        ':descricao' => $descricao,
        ':cidade_id' => $cidade_id,
        ':hora_ini' => $hora_ini,
        ':hora_fim' => $hora_fim,
        ':id' => $id_calendario_evento
    ];
    $stmt = $conn->prepare($sql);
    $res = $stmt->execute($params);
    //
    if( !$res ) {
        //        
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Erro Ao Salvar Registro!
                </div>'
        ];
        die( json_encode( $retorno ) );
    }
    //
    //- REGISTRA LOG DE SISTEMA
    //
        $historico = "ALTERADO Registro de Evento de Calendário | Dados Antigos: " .
             json_encode($dados_old, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $tabela = "rh_ponto_calendario";
        $idOperacao = $id_calendario_evento;
        f_log( "ALT", $historico, $tabela, $idModulo, $idOperacao, "rh_ponto_aj5.php" );


$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-success">
        <strong>Sucesso!</strong> Registro atualizado!
        </div>'
];
die( json_encode( $retorno ) );

//die( json_encode($retorno_ok, JSON_PRETTY_PRINT) );


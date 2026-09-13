<?php 
//
//- rh_ponto_aj2.php | (C)haia, 08/12/2025 | Salva Novo Registro de Calendário
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

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idUsuario = $_SESSION['idUsuario'];

//
//- VALIDAÇÕES
//
    if( empty($data_calendario) ) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Faltou Informar Data!
                </div>'
        ];
        die( json_encode($retorno, JSON_PRETTY_PRINT) );
    }
    if( empty($tipo) ){
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Faltou Informar Tipo!
                </div>'
        ];
        die( json_encode($retorno, JSON_PRETTY_PRINT) );
    }
    if( $tipo == 'REDUZIDO' && (empty($hora_ini) || empty($hora_fim)) ){
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Faltou Informar Horário!
                </div>'
        ];
        die( json_encode($retorno, JSON_PRETTY_PRINT) );
    }
    
//
//- VERIFICA SE JÁ EXISTE?
//
    if (empty($uf) && empty($cidade_id)) {
        // Nacional
        $sql = "SELECT 1 FROM rh_ponto_calendario
                WHERE data = :data
                AND (estado IS NULL or estado = 'BR')
                AND cidade_id IS NULL
                LIMIT 1";
        $params = [':data' => $data_calendario];

    } elseif (!empty($uf) && empty($cidade_id)) {
        // Estadual
        $sql = "SELECT 1 FROM rh_ponto_calendario
                WHERE data = :data
                AND (estado = :uf or estado = 'BR')
                AND cidade_id IS NULL
                LIMIT 1";
        $params = [
            ':data' => $data_calendario,
            ':uf'   => $uf
        ];

    } else {
        // Municipal
        $sql = "SELECT 1 FROM rh_ponto_calendario
                WHERE data = :data
                AND cidade_id = :cidade
                LIMIT 1";
        $params = [
            ':data'   => $data_calendario,
            ':cidade' => $cidade_id
        ];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->fetch()) {
        // Já existe
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Já existia!
                </div>'
        ];
        die( json_encode($retorno, JSON_PRETTY_PRINT) );
    }


//
//- INSERE REGISTRO NA BASE
//

    if( empty($uf) && empty($cidade_id) ) {
        //- abrangência nacional
        $cidade_id = NULL;
        $uf = 'BR';
    }

    $sql = "INSERT INTO rh_ponto_calendario 
                (data, tipo, descricao, hora_ini, hora_fim, cidade_id, estado, ativo)
            VALUES
                (:data, :tipo, :descricao, :hora_ini, :hora_fim, :cidade_id, :estado, 1)";
    $params = [
        ':data'         => $data_calendario,
        ':tipo'         => $tipo,
        ':descricao'    => $descricao,
        ':hora_ini'     => $hora_ini,
        ':hora_fim'     => $hora_fim,
        ':cidade_id'    => $cidade_id,
        ':estado'       => $uf
    ];
    $stmt = $conn->prepare($sql);
    $res = $stmt->execute($params);

    if (!$res) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Erro Ao Salvar Registro!
                </div>'
        ];
        die( json_encode($retorno, JSON_PRETTY_PRINT) );
    }
    $idOperacao = $conn->lastInsertId();

//
//- REGISTRA LOG DE SISTEMA
//

    $historico = "INCLUIDO Registro de Evento de Calendário | Dados: " .
             json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tabela = "rh_ponto_calendario";
    f_log( "INC", $historico, $tabela, $idModulo, $idOperacao,'rh_ponto_aj2.php' );
// 

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-success">
        <strong>OK!</strong> Salvo Com Sucesso!
        </div>'
];

die( json_encode($retorno, JSON_PRETTY_PRINT) );
    


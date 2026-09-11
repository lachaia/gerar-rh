<?php
//
//- rh_colab_aj1.php | Salva INCLUSÃO de COLABORADOR
//- (C)haia, 07/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

// TESTE DE RECEBIMENTO DE DADOS
//
/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
/*
 rh_colab_aj1.php | 2026-07-27 17:17:29 
{
    "idColab": "0",
    "nmPessoa": "PEDRO MANSUR BORIN",
    "idPessoa": "44",
    "matricula": "",
    "idOrgao": "4",
    "idSubSede": "101",
    "idPolo": "3",
    "idCentroCusto": "3",
    "idCargo": "171",
    "idFuncao": "7",
    "admissao": "2026-07-27",
    "idContratoTipo": "1",
    "horario_ini": "08:20",
    "horario_fim": "18:05",
    "carga_h": "44",
    "salario": "15000",
    "tipo_prazo": "I",
    "tipo_forma": "M",
    "jornada": "S",
    "cbo": "4110-10",
    "email_corp": "pedro.borin@gerar.org.br",
    "celular_corporativo": "41 9 9878 9878",
    "chave_pix": "57160600991",
    "idBanco": "98",
    "bco_agencia": "3215",
    "bco_cc": "32165465",
    "esocial": "415252"
}
    
*/

if ( empty($idPessoa) || empty($idSubSede) || empty($idPolo) || empty($idOrgao) || empty($idCargo) || empty($idFuncao) || empty($admissao) || empty($idContratoTipo) || empty($horario_ini) || empty($horario_fim) || empty($carga_h) || empty($salario)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
} else {
    header("location: logout.php");
}

//
//- VERIFICA SE JÁ EXISTE
//
    $sql = "";
    $sql = "SELECT idColab FROM rh_colaboradores WHERE matricula = ? and idPessoa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$matricula, $idPessoa]);
    if ($stmt->rowCount() > 0) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                <strong>Erro!</strong> Pessoa+Matricula já cadastrada!
                </div>'
        ];
        die(json_encode($retorno));
    }

//
//- INSERE NOVO COLABORADOR
//

if( ! isset($bate_ponto)) $bate_ponto = 0;
$salario = normalizarSalario($salario);

$sql = "INSERT INTO rh_colaboradores ( idEmpresa, idPessoa, matricula, idOrgao, idCargo, idFuncao, idContratoTipo, 
            data_admissao, salario_base, carga_horaria, idStatus, horario_ini, horario_fim,  
            idBanco, bco_agencia, bco_cc, idSubSede, polo_id, idJornada, idTipoForma, 
            idTipoPrazo, idLogin, cbo, idCentroCusto, chave_pix, bate_ponto, esocial_id )
          VALUES( :idEmpresa, :idPessoa, :matricula, :idOrgao, :idCargo, :idFuncao, :idContratoTipo, :admissao,  
            :salario, :carga_h, 1, :horario_ini, :horario_fim, :idBanco, :bco_agencia, 
            :bco_cc, :idSubSede, :idPolo, :idJornada, :idTipoForma, :idTipoPrazo, :idLogin, :cbo, :idCentroCusto, 
            :chave_pix, :bate_ponto, :esocial_id )";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
$stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->bindValue(':matricula', $matricula, PDO::PARAM_STR);
$stmt->bindValue(':idOrgao', $idOrgao, PDO::PARAM_INT);
$stmt->bindValue(':idCargo', $idCargo, PDO::PARAM_INT);
$stmt->bindValue(':idFuncao', $idFuncao, PDO::PARAM_INT);
$stmt->bindValue(':idContratoTipo', $idContratoTipo, PDO::PARAM_INT);
$stmt->bindValue(':admissao', $admissao, PDO::PARAM_STR);
$stmt->bindValue(':salario', $salario, PDO::PARAM_STR);
$stmt->bindValue(':carga_h', $carga_h, PDO::PARAM_STR);
$stmt->bindValue(':horario_ini', $horario_ini, PDO::PARAM_STR);
$stmt->bindValue(':horario_fim', $horario_fim, PDO::PARAM_STR);
$stmt->bindValue(':idBanco', $idBanco, PDO::PARAM_INT);
$stmt->bindValue(':bco_agencia', $bco_agencia, PDO::PARAM_STR);
$stmt->bindValue(':bco_cc', $bco_cc, PDO::PARAM_STR);
$stmt->bindValue(':idJornada', $jornada, PDO::PARAM_STR);
$stmt->bindValue(':idSubSede', $idSubSede, PDO::PARAM_INT);
$stmt->bindValue(':idPolo', $idPolo, PDO::PARAM_INT);
$stmt->bindValue(':idTipoForma', $tipo_forma, PDO::PARAM_STR);
$stmt->bindValue(':idTipoPrazo', $tipo_prazo, PDO::PARAM_STR);
$stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
$stmt->bindValue(':cbo', $cbo, PDO::PARAM_STR);
$stmt->bindValue(':idCentroCusto', $idCentroCusto, PDO::PARAM_INT);
$stmt->bindValue(':chave_pix', $chave_pix, PDO::PARAM_STR);
$stmt->bindValue(':bate_ponto', $bate_ponto, PDO::PARAM_INT);
$stmt->bindValue(':esocial_id', $esocial, PDO::PARAM_STR);
//
$stmt->execute();
$idColab = $conn->lastInsertId();
if ($idColab == 0) {
    $msg = "<div class='alert alert-danger'><strong>Erro!</strong> ao inserir registro.</div>";
    $response = ["status" => false, "msg" => $msg, "idColab" => 0 ];
    die(json_encode($response));
}

//
//- INSERE HISTÓRICOS - SALÁRIO + CARGOS + FUNÇÕES + ORGANOGRAMA
//
    $dsMotivo = "Início de Contrato";
    $sql = "INSERT INTO rh_historico_sal ( idColab, data, valor, motivo, idLogin ) 
            VALUES ( :idColab, :data, :salario, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $admissao, PDO::PARAM_STR);
    $stmt->bindValue(':salario', $salario, PDO::PARAM_STR);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();
    //
    $sql = "INSERT INTO rh_historico_funcao ( idColab, data, idFuncao, motivo, idLogin ) 
            VALUES ( :idColab, :data, :idFuncao, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $admissao, PDO::PARAM_STR);
    $stmt->bindValue(':idFuncao', $idFuncao, PDO::PARAM_INT);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();
    //
    $sql = "INSERT INTO rh_historico_cargo ( idColab, data, idCargo, motivo, idLogin ) 
            VALUES ( :idColab, :data, :idCargo, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $admissao, PDO::PARAM_STR);
    $stmt->bindValue(':idCargo', $idCargo, PDO::PARAM_INT);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();
    //
    $sql = "INSERT INTO rh_historico_orgao ( idColab, data, idOrgao, motivo, idLogin ) 
            VALUES ( :idColab, :data, :idOrgao, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $admissao, PDO::PARAM_STR);
    $stmt->bindValue(':idOrgao', $idOrgao, PDO::PARAM_INT);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();
//
//- ATUALIZA PESSOAS
//
if(empty($cnh_vcto)) $cnh_vcto = null;
$sql = "UPDATE rh_pessoas SET email_corporativo=:email_corporativo, celular_corporativo=:celular_corporativo 
                WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':email_corporativo', $email_corp, PDO::PARAM_STR);
$stmt->bindValue(':celular_corporativo', $celular_corporativo, PDO::PARAM_STR);
$stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
//
$msg = "<div class='alert alert-success'><strong>Successo!</strong> ao inserir registro. ID: $idColab</div>";
f_log("INC", "INCLUSÃO de COLABORADOR - Dados Profissionais: ($dados_novos)", "rh_colaboradores", $idModulo, $idColab);


$response = ["status" => true, "msg" => $msg, "idColab" => $idColab ];

//
//- LINHA DO TEMPO
//
$tipo = 1; // 1 = cadastro criado
$descricao = "Incluido novo colaborador: $nmPessoa - ID: $idColab";
f_ldt( $tipo, $idPessoa, $descricao);
$conn = null;
die(json_encode($response));

function normalizarSalario($valor) {
    // Se tiver vírgula, assumimos que é formato brasileiro
    if (strpos($valor, ',') !== false) {
        // Remove milhar e troca vírgula decimal por ponto
        return floatval(str_replace(['.', ','], ['', '.'], $valor));
    } else {
        // Já está em formato compatível
        return floatval($valor);
    }
}

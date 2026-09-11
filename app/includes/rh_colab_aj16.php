<?php
//
//- rh_colab_aj1.php | Salva ALTERAÇÃO de COLABORADOR
//- (C)haia, 15/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

// TESTE DE RECEBIMENTO DE DADOS
/*
include "debug.php";
debug(json_encode($parametros, JSON_PRETTY_PRINT));
$retorno = [
    'status' => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die(json_encode($retorno, JSON_PRETTY_PRINT));
/*
 rh_colab_aj16.php | 2025-10-09 11:20:21 
{
    "idColab": "1",
    "nmPessoa": "LUIZ AUGUSTO CHAIA",
    "idPessoa": "1",
    "matricula": "4984",
    "idOrgaoOld": "52",
    "motivoOrgao": "Promo\u00e7\u00e3o",
    "idOrgao": "150",
    "idCargoOld": "30",
    "motivoCargo": "",
    "idCargo": "30",
    "idFuncaoOld": "1",
    "motivoFuncao": "",
    "idFuncao": "1",
    "admissao": "2023-04-05",
    "idContratoTipo": "1",
    "horario_ini": "08:20",
    "horario_fim": "18:05",
    "carga_h": "44",
    "salario": "9849.84",
    "salarioOld": "9849.84",
    "motivoSalario": "",
    "tipo_prazo": "I",
    "tipo_forma": "M",
    "jornada": "S",
    "idSubSede": "101",
    "email_corporativo": "luiz.chaia@gerar.org.br",
    "celular_corporativo": "41 9 9700 4083",
    "idBanco": "98",
    "bco_agencia": "3600",
    "bco_cc": "000710340077"
    "esocial": "1234",
    "bate_ponto": "1"
}
*/

if (
    empty($idPessoa) || empty($matricula) || empty($idOrgao) || empty($idCargo) || empty($idFuncao) ||
    empty($admissao) || empty($idContratoTipo) || empty($horario_ini) || empty($horario_fim) || empty($carga_h) ||
    empty($salario)
) {
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
// CARREGA DADOS ANTIGOS
//
$sql = "SELECT * FROM rh_colaboradores WHERE idColab = :idColab";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
$antigos = implode(", ", $dados);

//
//- ATUALIZA DADOS DO COLABORADOR
//
if (! isset($lider)) $lider = 0;
if ( empty($cnh_vcto) ) $cnh_vcto = NULL;

$sql = "UPDATE rh_colaboradores SET
    idEmpresa         = :idEmpresa,
    idPessoa          = :idPessoa,
    matricula         = :matricula,
    idOrgao           = :idOrgao,
    idCargo           = :idCargo,
    idFuncao          = :idFuncao,
    idContratoTipo    = :idContratoTipo,
    data_admissao     = :admissao,
    data_rescisao     = NULL,
    idRescisaoTipo    = NULL,
    salario_base      = :salario,
    carga_horaria     = :carga_h,
    horario_ini       = :horario_ini,
    horario_fim       = :horario_fim,
    idBanco           = :idBanco,
    bco_agencia       = :bco_agencia,
    bco_cc            = :bco_cc,
    idSubSede         = :idSubSede,
    idJornada         = :idJornada,
    idTipoForma       = :idTipoForma,
    idTipoPrazo       = :idTipoPrazo,
    dcLider           = :lider, 
    cbo               = :cbo,
    idCentroCusto     = :idCentroCusto,
    chave_pix         = :chave_pix,
    esocial_id        = :esocial_id,
    bate_ponto        = :bate_ponto
WHERE idColab = :idColab";
//
if(empty($idBanco)) $idBanco = NULL;
if(empty($bco_agencia)) $bco_agencia = NULL;
if(empty($bco_cc)) $bco_cc = NULL;
if( ! isset($bate_ponto) ) $bate_ponto = 0;
if( empty($esocial)) $esocial = NULL;
//
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
$stmt->bindValue(':idSubSede', $idSubSede, PDO::PARAM_STR);
$stmt->bindValue(':idTipoForma', $tipo_forma, PDO::PARAM_STR);
$stmt->bindValue(':idTipoPrazo', $tipo_prazo, PDO::PARAM_STR);
$stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
$stmt->bindValue(':lider', $lider, PDO::PARAM_INT);
//
$stmt->bindValue(':cbo', $cbo, PDO::PARAM_STR);
$stmt->bindValue(':idCentroCusto', $idCentroCusto, PDO::PARAM_INT);
$stmt->bindValue(':chave_pix', $chave_pix, PDO::PARAM_STR);

$stmt->bindValue(':esocial_id', $esocial, PDO::PARAM_STR);
$stmt->bindValue(':bate_ponto', $bate_ponto, PDO::PARAM_INT);
//
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao atualizar registro. ID: $idColab</div>";
    f_log("ALT", "ALTERAÇÃO de dados do COLABORADOR $nmPessoa: Dados Profissionais: ($dados_novos) | Dados anteriores: ($antigos)", "rh_colaboradores", $idModulo, $idColab);
    //
    $tipo = 3; // 3 = dados atualizados
    $descricao = "Dados de Colaborador atualizado ID: $idColab";
    f_ldt($tipo, $idPessoa, $descricao);
    //
    //
    $sql = "UPDATE rh_pessoas SET email_corporativo=:email_corporativo, celular_corporativo=:celular_corporativo 
                WHERE idPessoa = :idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':email_corporativo', $email_corporativo, PDO::PARAM_STR);
    $stmt->bindValue(':celular_corporativo', $celular_corporativo, PDO::PARAM_STR);
    $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->execute();

    //
    //- HISTÓRICO DE CARGOS E SALÁRIOS
    //
        $hoje = date('Y-m-d');
        if( ($idCargoOld != $idCargo) &&  ! empty($motivoCargo) ){
            $sql = "INSERT INTO rh_historico_cargo ( idColab, data, idCargo, motivo, idLogin ) 
                    VALUES ( :idColab, :data, :idCargo, :motivo, :idLogin )";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
            $stmt->bindValue(':data', $hoje, PDO::PARAM_STR);
            $stmt->bindValue(':idCargo', $idCargo, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', $motivoCargo, PDO::PARAM_STR);
            $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
            $stmt->execute();
        }
        //
        if( ($idFuncaoOld != $idFuncao) &&  ! empty($motivoFuncao) ){
            $sql = "INSERT INTO rh_historico_funcao ( idColab, data, idFuncao, motivo, idLogin ) 
                    VALUES ( :idColab, :data, :idFuncao, :motivo, :idLogin )";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
            $stmt->bindValue(':data', $admissao, PDO::PARAM_STR);
            $stmt->bindValue(':idFuncao', $idFuncao, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', $motivoFuncao, PDO::PARAM_STR);
            $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
            $stmt->execute();
        }
        //
        if( ($salarioOld != $salario) &&  ! empty($motivoSalario) ){
            $sql = "INSERT INTO rh_historico_sal ( idColab, data, valor, motivo, idLogin ) 
                    VALUES ( :idColab, :data, :salario, :motivo, :idLogin )";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
            $stmt->bindValue(':data', $admissao, PDO::PARAM_STR);
            $stmt->bindValue(':salario', $salario, PDO::PARAM_STR);
            $stmt->bindValue(':motivo', $motivoSalario, PDO::PARAM_STR);
            $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
            $stmt->execute();
        }
        //
        if( ($idOrgaoOld != $idOrgao) &&  ! empty($motivoOrgao) ){
            $sql = "INSERT INTO rh_historico_orgao ( idColab, data, idOrgao, motivo, idLogin ) 
                    VALUES ( :idColab, :data, :idOrgao, :motivo, :idLogin )";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
            $stmt->bindValue(':data', $admissao, PDO::PARAM_STR);
            $stmt->bindValue(':idOrgao', $idOrgao, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', $motivoOrgao, PDO::PARAM_STR);
            $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
            $stmt->execute();
        }
        //
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro!</strong> ao inserir registro.</div>";
    $response = ["status" => false, "msg" => $msg];
    $conn = null;
    die(json_encode($response));
}
//
$response = ["status" => false, "msg" => $msg, "idColab" => $idColab];
//
//- INSERE HISTÓRICO DE COLABORADOR
//

$sql = "INSERT INTO rh_colaboradores_hist (
    idColab, idEmpresa, idPessoa, matricula, idOrgao, dcLider, idCargo, idFuncao, idContratoTipo, data_admissao,
    idRescisao, data_rescisao, idRescisaoTipo, salario_base, carga_horaria, idStatus, horario_ini, horario_fim,
    idBanco, bco_agencia, bco_cc, arquivo_ctps, arquivo_ddir, idEnderecoTrab, vale_transporte, vale_refeicao,
    idPlanoSaude, idPlanoOdonto, idSubSede, idJornada, idTipoForma, idTipoPrazo, idStatusOld, idLogin,
    chave_pix, idCentroCusto, cbo, salario_old
)
SELECT 
    idColab, idEmpresa, idPessoa, matricula, idOrgao, dcLider, idCargo, idFuncao, idContratoTipo, data_admissao,
    idRescisao, data_rescisao, idRescisaoTipo, salario_base, carga_horaria, idStatus, horario_ini, horario_fim,
     idBanco, bco_agencia, bco_cc, arquivo_ctps, arquivo_ddir, idEnderecoTrab, vale_transporte, vale_refeicao, 
     idPlanoSaude, idPlanoOdonto, idSubSede, idJornada, idTipoForma, idTipoPrazo, idStatusOld, idLogin,
     chave_pix, idCentroCusto, cbo, salario_old
FROM rh_colaboradores WHERE idColab = $idColab";
$stmt = $conn->prepare($sql);
$stmt->execute();

$conn = null;
die(json_encode($response));

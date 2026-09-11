<?PHP
//
//- ponto_sol_aj1.php | Detalhe da SOLICITAÇÃO - Edita o cartão
// (C)haia, 09/12/2025
//

session_start();

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

//include $_SERVER['DOCUMENT_ROOT'] . "/rh/includes/conexao_gerar.php";
//include $_SERVER['DOCUMENT_ROOT'] . "/rh/includes/debug.php";
include "../../includes/conexao_gerar.php";
include "../../includes/debug.php";

$id = $_POST['id'];

if (empty($id)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

//
//- RECUPERA DADOS DO PONTO-BANCO-HORAS ID
//
    $sql = "SELECT S.*,
                        DATE_FORMAT(S.data_hora, '%Y-%m-%d') AS data,
                        C.idColab, 
                        C.horario_ini, 
                        C.horario_fim,
                        SS.cidade AS cidade_id, CD.nome as nmCidade,
                        SS.estado AS colaborador_uf,
                        P.nome AS nmSupervisor,
                        ( select DATE_FORMAT(R.data_hora, '%H:%i') AS hora 
                            from rh_ponto_registros R 
                            WHERE R.id = S.batida_id
                        ) as hora_antes
                FROM rh_ponto_solicitacoes S
                INNER JOIN rh_colaboradores C on C.idColab = S.colaborador_id
                INNER JOIN rh_subsedes SS on SS.idSubSede = C.idSubSede
                INNER JOIN rh_cidades CD on CD.idCidade = SS.cidade
                INNER JOIN rh_colaboradores CS ON CS.idColab = S.supervisor_id
                INNER JOIN rh_pessoas P on P.idPessoa = CS.idPessoa
                WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC); // <<< troca aqui
    //debug( json_encode($dados, JSON_PRETTY_PRINT) );
    extract($dados);

//
//- VERIFICA SE A DATA É DIA UTIL OU NÃO
//
    $dia_util = verifica_data($data_hora, $conn, $cidade_id, $colaborador_uf);

    $horaFormatada = strftime('%H:%M', strtotime($data_hora));
    $dsTipo = "ND";
    if( $tipo == 'INC') $dsTipo = "Incluir";
    if( $tipo == 'DEL') $dsTipo = "Excluir";
    if( $tipo == 'ALT') $dsTipo = "Alterar de $hora_antes para";
    if( $tipo == 'ABO') $dsTipo = "Abonar o dia";

    $dsStatus = $status;
    if ($status == "AGUARDANDO") $dsStatus = "<spam class='badge bg-warning w-100 text-dark status'>Aguardando</spam>";
    if ($status == "APROVADO")   $dsStatus = "<spam class='badge bg-success w-100 status'>Aprovado</spam>";
    if ($status == "REJEITADO")  $dsStatus = "<spam class='badge bg-danger w-100 status'>Rejeitado</spam>";
?>
<div class='row' id='divPrincipal'>
    <style>

        .status{
            width: 100%; 
            height: 40px;
            font-size: 1.25rem;
        }

    </style>    
    <div class='col-sm-12'>

        <div class='row'>
            <div class='col-sm-4 mt-1'>
                <label for="data">Data</label>
                <input type='text' id='data' readonly class='form-control text-center' value="<?= strftime('%d/%m/%Y', strtotime($data_hora)); ?>">
            </div>
            <div class='col-sm-4 mt-1'>
                <label for="dia">Dia</label>
                <input type='text' id='dia' readonly class='form-control text-center' value="<?= nomeDiaSemana($data) ?>">
            </div>
            <div class='col-sm-4 mt-1'>
                <label for="tipo">Tipo-dia</label>
                <input type='text' id='tipo' readonly class='form-control text-center' value="<?= $dia_util ?>">
            </div>
            <div class='col-sm-6 mt-1'>
                <label for="tipo">Local-padrão</label>
                <input type='text' id='tipo' readonly class='form-control text-center' value="<?= "$nmCidade - $colaborador_uf" ?>">
            </div>
            <div class='col-sm-6 mt-1'>
                <label for="tipo">Horário-padrão</label>
                <input type='text' id='tipo' readonly class='form-control text-center' value="<?= $dia_util ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-12">

                <!-- BLOCO: Soliciação -->
                <label for="divSolicitacao" class="mt-2">Solicitacao</label>
                <div id='divSolicitacao' 
                    class="card d-flex align-items-center justify-content-center h6 text-center" 
                    style="width: 100%; height: 40px;">
                    <?= "$dsTipo $horaFormatada" ?>
                </div>
                <!-- BLOCO: Motivo -->
                <label for="divMotivo" class="mt-2">Motivo</label>
                <div id='divMotivo' 
                    class="card d-flex align-items-center justify-content-center h6 text-center" 
                    style="width: 100%; height: 40px;">
                    <?= $motivo ?>
                </div>
            </div>
            <div class='col-sm-6 mt-1'>
                <label for="divStatus">Status</label>
                <div id='divStatus' 
                    class="card d-flex align-items-center justify-content-center h6 text-center" 
                    style="width: 100%; height: 40px;">
                    <?= $dsStatus ?>
                </div>
            </div>
            <div class='col-sm-6 mt-1'>
                <label for="tipo">Solicitado em</label>
                <input type='text' readonly class='form-control text-center' value="<?= $solicitado_em ?>">
            </div>
        </div>
    </div>
    <hr>
    <!-- Footer -->
    <div id='botoesModalSolicitacao' class="d-flex justify-content-center">
        <button 
            type="button" 
            class="btn btn-secondary w-100 m-1" 
            data-bs-dismiss="modal">
            Fechar
        </button>
        <button 
            type="button" 
            class="btn btn-danger w-100 m-1"
            <?php if($status != "AGUARDANDO") echo "disabled"; ?> 
            onclick="f_excluir_solicitacao(<?= $id ?>)">
            Excluir
        </button>
    </div>
</div>

<div class='row d-none' id='divFormulario'></div>

<?php
function verifica_data($data, $conn, $cidade_id, $estado)
{
    $tipoHoje = 'UTIL';
    //
    $sql = "SELECT * 
            FROM rh_ponto_calendario 
            WHERE ativo = 1 AND
                (data = :data AND estado = 'BR') or 
                (data = :data AND estado = :estado ) or
                (data = :data AND cidade_id = :cidade_id) or
                (data = :data AND tipo <> 'FERIADO')
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':data', $data);
    $stmt->bindValue(':estado', $estado);
    $stmt->bindValue(':cidade_id', $cidade_id);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($linha) {
        $tipoHoje = $linha['tipo'];
        //
        if ($tipoHoje == 'REDUZIDO') {
            $_SESSION['horario_ini'] = $linha['hora_ini'];
            $_SESSION['horario_fim'] = $linha['hora_fim'];
        }
    }
    $_SESSION['dsTipoDia'] = $tipoHoje == 'UTIL' ? 'Dia Util' : $tipoHoje;
    return $tipoHoje;
}

function nomeDiaSemana($data) {
    // $data no formato "Y-m-d"
    $timestamp = strtotime($data);

    // 0 = Domingo, 1 = Segunda, ..., 6 = Sábado
    $diaNumero = date('w', $timestamp);

    $diasSemana = [
        "Domingo",
        "Segunda-feira",
        "Terça-feira",
        "Quarta-feira",
        "Quinta-feira",
        "Sexta-feira",
        "Sábado"
    ];

    return $diasSemana[$diaNumero];
}

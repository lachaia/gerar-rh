<?PHP
//
//- edita_cartao.php | Detalhe da Jornada - Edita o cartão
// (C)haia, 12/11/2025
//

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

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
$sql = "SELECT D.*, C.bate_ponto, P.nome, CD.nome as nmCidade, C.horario_ini, C.horario_fim
            FROM rh_ponto_banco_horas D
            INNER JOIN rh_colaboradores C on C.idColab = D.colaborador_id
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT JOIN rh_cidades CD on CD.idCidade = D.cidade_id
            where D.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // <<< troca aqui
//debug( json_encode($dados, JSON_PRETTY_PRINT) );
extract($dados);

/*
 ponto_aj1.php | 2025-12-08 14:14:32 
[
    {
        "id": 15443,
        "colaborador_id": 1,
        "cidade_id": 3281,
        "colaborador_uf": "PR",
        "data_ref": "2025-11-24",
        "tipo_dia": "UTIL",
        "qtd_batidas": 1,
        "horas_previstas": 31500,
        "horas_trabalhadas": 0,
        "intervalo_almoco": 0,
        "he_50": 0,
        "he_100": 0,
        "adicional_noturno": 0,
        "saldo_dia": 0,
        "credito": 0,
        "debito": 0,
        "bh": -23675,
        "alerta": 1,
        "observacao": "\u26a0\ufe0f N\u00famero de batidas incorreto",
        "criado_em": "2025-12-04 10:01:15",
        "bate_ponto": 1,
        "nome": "LUIZ AUGUSTO CHAIA",
        "nmCidade": "Curitiba",
        "horario_ini": "08:20",
        "horario_fim": "18:05"
    }
]
*/
//
// Busca dados básicos do colaborador
//
$sql = "SELECT 
                    C.idColab, 
                    C.horario_ini, 
                    C.horario_fim, 
                    S.cidade AS cidade_id,
                    S.estado AS colaborador_uf
                FROM rh_colaboradores C
                LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
                WHERE C.data_rescisao IS NULL AND C.idColab = :idColab";
$stmt = $conn->prepare($sql);
$stmt->execute([':idColab' => $colaborador_id]);
$linha = $stmt->fetch(PDO::FETCH_ASSOC);

$horario_ini = $linha['horario_ini'];
$horario_fim = $linha['horario_fim'];

$dataFormatada = strftime('%A, %d/%m/%Y', strtotime($data_ref));
$dataFormatada = ucfirst(utf8_encode($dataFormatada));

//
//- VERIFICA SE A DATA É DIA UTIL OU NÃO
//
$dia_util = verifica_data($data_ref, $conn, $cidade_id, $colaborador_uf);

//
//- VERIFICA SE TEM BATIDAS NO DIA SELECIONADO
//
$sql = "SELECT *
                FROM rh_ponto_registros
                WHERE colaborador_id = :idColab
                AND DATE(data_hora) = :dia
                ORDER BY data_hora
            ";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':idColab' => $colaborador_id,
    ':dia' => $data_ref
]);
$temBatidas = ($stmt->rowCount() > 0);
?>
<div class='row' id='divPrincipal'>
    <div class='col-sm-12'>

        <div class='row'>
            <div class='col-sm-4 mt-1'>
                <label for="data">Data</label>
                <input type='text' id='data' readonly class='form-control text-center' value="<?= strftime('%d/%m/%Y', strtotime($data_ref)); ?>">
            </div>
            <div class='col-sm-4 mt-1'>
                <label for="dia">Dia</label>
                <input type='text' id='dia' readonly class='form-control text-center' value="<?= strftime('%A', strtotime($data_ref)); ?>">
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

                <!-- BLOCO: REGISTROS DO DIA -->
                <div class="text-center text-dark mt-4 mb-3 h4 w-100">
                    Registros do dia
                </div>
                <?PHP
                //
                if ($temBatidas) {
                    echo "<table class='table table-striped table-sm table-hover table-responsive w-100 nowrap'>";
                    echo "<tbody class='text-center'>";
                    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $horaFormatada = strftime('%H:%M', strtotime($linha['data_hora']));
                        $tipo = $linha['tipo'];
                        $botoes = '
                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                onclick="abrirModalEditar(' . $linha['id'] . ')">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="abrirModalExcluir(' . $linha['id'] . ')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        ';
                        echo "
                            <tr class='hover:bg-gray-50'>
                                <td class='px-4 py-2'>$tipo</td>
                                <td class='px-4 py-2'>$horaFormatada</td>
                                <td class='px-4 py-2'>$botoes</td>
                            </tr>";
                    }
                    echo "
                        </tbody>
                        </table>";
                    } else {
                        echo "
                            <div class='max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mt-2'>
                                <div class='text-center'>
                                    <div class='flex justify-center items-center text-bold text-gray-700 text-base'>
                                        Nenhuma registro de ponto para hoje.
                                    </div>
                                </div>
                            </div>
                        ";
                }

                ?>
                <button
                    type="button"
                    class="btn btn-primary mt-3 w-100"
                    onclick="abrirModalNovo('<?= $data_ref ?>')">
                    Incluir acima novo registro
                </button>
            </div>
        </div>

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

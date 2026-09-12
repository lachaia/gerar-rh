<?php 
//
//- espelho_assina.php | Assinatura do Cartão Ponto
// (C)haia, 26/11/2025
//

session_start();

if (empty($_SESSION['idLogin']) || empty($_SESSION['idColab'])) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Sessão inválida."]));
}

$nmLogin = $_SESSION['nmLogin'];

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

$id = $_POST['id'];

if( isset($_POST['origem']) ) $origem = $_POST['origem']; else $origem = null;

//
//- LÊ DADOS DO ESPELHO
//
    $sql = "SELECT * FROM rh_ponto_espelhos WHERE id = :id";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':id', $id);
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$linha || (int) $linha['colaborador_id'] !== (int) $_SESSION['idColab']) {
        http_response_code(403);
        die(json_encode(["status" => false, "msg" => "Acesso negado."]));
    }

    extract($linha);
    /* campos lidos: 
        id, colaborador_id, ano, mes, dsMes, periodo_ini, periodo_fim, horas_normal, faltas, extras, 
        status, arquivo, gerado_em, assinado_em, assinado_por, hash_integridade
    */

//
//- CALCULO DO HASH
//
    $agora = date('Y-m-d H:i:s');
    $dados = $id . '|' .
         $colaborador_id . '|' .
         $ano . '|' . 
         $mes . '|' .
         $dsMes . '|' .
         $periodo_ini . '|' .
         $periodo_fim . '|' .
         $horas_normal . '|' .
         $faltas . '|' .
         $extras . '|' .
         $arquivo . '|' .
         $nmLogin . '|' .       // quem assinou
         $agora;   // timestamp exato

    $hash = hash('sha256', $dados);

$sql = "UPDATE rh_ponto_espelhos 
        SET status = 'ASSINADO', assinado_em = :agora, assinado_por = :user, hash_integridade = :hash
        WHERE id = :id";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $id);
$consulta->bindParam(':user', $nmLogin);
$consulta->bindParam(':hash', $hash);
$consulta->bindParam(':agora', $agora);
$res = $consulta->execute();

if ($res) {
    if( $origem == 'colaborador'){
            $retorno = [
                "status" => true,
                "msg" => '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Sucesso!</strong>
                            <span class="d-block">Assinatura realizada com sucesso!</span>
                        </div>'
            ];
    } else{
        $retorno = [
            "status" => true,
            "msg" => '<div class="mb-4 rounded border border-green-300 bg-green-100 px-4 py-3 text-green-800">
                        <strong class="font-bold">Sucesso!</strong>
                        <span class="block">Assinatura realizada com sucesso!</span>
                    </div>'
        ];
    }
    //
} else {
    if( $origem == 'colaborador'){
            $retorno = [
                "status" => true,
                "msg" => '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erro!</strong>
                            <span class="d-block">Falha ao assinar Cartão Ponto!</span>
                        </div>'
            ];
    } else{
        $retorno = [
            "status" => false,
            "msg" => '<div class="mb-4 rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                        <strong class="font-bold">Erro!</strong>
                        <span class="block">Falha ao assinar Cartão Ponto.</span>
                    </div>'
        ];
    }

}

die(json_encode($retorno));
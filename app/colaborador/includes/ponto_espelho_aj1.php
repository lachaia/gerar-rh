<?php
//
//- ponto_espelho_aj1.php | Mostra o Espelho de Ponto - Versão Colaborador (WEB)
// (C)haia, 10/12/2025
//

session_start();

include '../../includes/conexao_gerar.php';

$espelho_id = $_POST['espelho_id'] ?? null;
$status = $_POST['status'] ?? null;

if( empty($espelho_id) || empty($status)){
    $retorno = [
        "status" => false,
        "msg" => "Faltou Parâmetros"
    ];
    die(json_encode($retorno, JSON_PRETTY_PRINT));
}

//
//- RECUPERA DADOS do ESPELHO ID
//
    $sql = "SELECT * 
            FROM rh_ponto_espelhos 
            WHERE id = :espelho_id;";
    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':espelho_id', $espelho_id);
    $consulta->execute();
    $linha = $consulta->fetch(PDO::FETCH_ASSOC);
    extract($linha);
    /* campos lidos: 
        id, colaborador_id, ano, mes, dsMes, periodo_ini, periodo_fim, horas_normal, 
        faltas, extras, status, arquivo, gerado_em, assinado_em, assinado_por, hash_integridade  
    */

    $url = "../ponto/docs/$colaborador_id/$arquivo";
    if( $status == 'GERADO' ){?>
        <div class="d-flex justify-content-center position-relative mb-3">
            <button type="button" class="btn btn-outline-primary" onclick="f_assinar('<?php echo $espelho_id; ?>')"> A S S I N A R </button>
        </div>
        <div class='text-center h5' id='msgAssinatura'></div>
    <?php
    }

    echo "<iframe src='$url' style='width:100%; height:600px; border:none;'></iframe>";
    
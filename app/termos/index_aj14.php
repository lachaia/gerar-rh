<?php
//
//- rh_index_aj14.php | Salva BAIXA Termo de Responsabilidade (ELETRÔNICO)
//- (C)haia, 17/09/2025
//

session_start();

require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include "../includes/conexao_gerar.php";

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);
if (empty($idTermo) || empty($usuario) || empty($senha)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

$idTermo = (int) $idTermo; // usado também para montar caminho de arquivo — precisa ser sempre numérico

/*
include_once "../includes/debug.php";
debug(json_encode($parametros, JSON_PRETTY_PRINT));

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-primary">
        <strong>OK!</strong> Sucesso no Teste de Sistema!
        </div>'
];

die( json_encode( $retorno ) );

 index_aj14.php | 2025-09-17 15:35:24 
{
    "idTermo": "10",
    "usuario": "luiz.chaia",
    "senha": "Joshua2025**",
    "data_dev": "2025-09-17T15:35",
    "vistoriador": "luiz.chaia",
    "observacoes_dev": "<p>os equipamentos foram devolvidos em perfeito estado.<\/p>",
    "origem": "e"
}
*/

// Configurações do Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true); // permite carregar imagens remotas
$dompdf = new Dompdf($options);

$idModulo = 19; // Equipamentos

//
//- CONFERE USUÁRIO + SENHA
//
    $sql = "SELECT senha FROM rh_usuarios WHERE login = :login LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':login', $usuario, PDO::PARAM_STR);
    $stmt->execute(); 
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //
    if( ! password_verify( $senha, $row['senha'] ) ){
        $conn = null; 
        die('{"status":"0", "msg":"Usuário ou Senha errada!"}');            
    }

//
//- RECUPERA DADOS DO TERMO
//
    $sql = "SELECT * FROM rh_equip_termos WHERE id = :idTermo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idTermo', $idTermo);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //
    $termo = $row['termo_html'];
    $idPessoa = $row['idPessoa'];

//
//- GERA O PDF ASSINADO
//

$usuario = htmlspecialchars($usuario);
$dtAssinatura = date("Y-m-d H:i:s");
$status = "Baixado";

$userIP = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

//
$termo .= "<div style='text-align: center; margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; font-size: 12px;'>
                Observação da Vistoria de Devolução: $observacoes_dev</div>";

$termo .= "<div style='text-align: center; margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; font-size: 12px;'>
                Assinado eletronicamente por: $usuario<br>IP: $userIP<br>Data/Hora: $dtAssinatura</div>";
//
$dompdf->loadHtml($termo);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Gera o PDF em memória
$pdfOutput = $dompdf->output();

// Salva o PDF em disco
$arquivo = "../docs/pessoa_$idPessoa/responsa_$idTermo.pdf";
file_put_contents($arquivo, $pdfOutput);

$hashPDF = hash('sha256', $pdfOutput); // SHA-256 garante integridade

//
//- SALVA DADOS NA TABELA
//

$sql = "UPDATE rh_equip_termos 
        SET user_devolucao = :user_devolucao,
            userIPDev = :userIPDev,
            userAgentDev = :userAgentDev,
            vistoria_devolucao = :vistoria_devolucao,
            data_devolucao = :data_devolucao,
            dtAssinaturaDev = :dtAssinaturaDev,
            status = :status, 
            termo_html = :termo_html, 
            hashPDF = :hashPDF
            WHERE id = :id";

$stmt = $conn->prepare($sql); // <-- PREPARA ANTES

$stmt->bindParam(':id', $idTermo);

$stmt->bindParam(':user_devolucao', $usuario);
$stmt->bindParam(':userIPDev', $userIP);
$stmt->bindParam(':userAgentDev', $userAgent);
$stmt->bindParam(':vistoria_devolucao', $observacoes_dev);
$stmt->bindParam(':data_devolucao', $data_dev);
$stmt->bindParam(':termo_html', $termo);
$stmt->bindParam(':hashPDF', $hashPDF);
$stmt->bindParam(':status', $status);
$stmt->bindParam(':dtAssinaturaDev', $dtAssinatura);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'>Assinado!</div>"
];
if (! $stmt->execute()) {
    //
    $retorno = [
        "status"=> false,
        "msg"=> "<div class='alert alert-danger'>Erro ao assinar!</div>"
    ];
}
die( json_encode($retorno) );
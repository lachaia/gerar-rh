<?php
//
//- index_aj12.php | Assina documento: Termo de Responsabilidade DEVOLUÇÃO
// (C)haia, 01/09/2025

/*
$retorno = [
    "status" => false,
    "msg" => "<div class='alert alert-danger'>Senha inválida!</div>"
];
die( json_encode($retorno) );
*/

session_start();

// Nenhuma página atual do sistema chama este arquivo (foi substituído por
// index_aj14.php), mas ele continua acessível diretamente por URL — por
// segurança, exige a mesma sessão/grupo que a tela de Termos já exige.
if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [4, 7, 9], true)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include_once "../includes/conexao_gerar.php";

// Configurações do Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true); // permite carregar imagens remotas
$dompdf = new Dompdf($options);

$idModulo = 19; // Equipamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

// "usuario" nunca vem do cliente para fins de autenticação — ver
// index_aj14.php para a explicação completa (mesmo padrão aplicado aqui).
$usuario = $_SESSION['nmLogin'];

if (empty($idTermo) || empty($usuario) || empty($senha || empty($vistoria))) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

$idTermo = (int) $idTermo; // usado também para montar caminho de arquivo — precisa ser sempre numérico

//
//- CONFERE USUÁRIO + SENHA
//
    $sql = "SELECT senha FROM rh_usuarios WHERE login = :login LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':login', $usuario, PDO::PARAM_STR);
    $stmt->execute(); // <- precisa executar a query
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($senha, $row['senha'])) {
        $conn = null;
        die('{"status":"0", "msg":"Usuário ou Senha errada!"}');
    }


//
//- GERA O PDF ASSINADO
//
$sql = "SELECT * FROM rh_equip_termos WHERE id = :idTermo";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idTermo', $idTermo);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
//
$termo = $row['termo_html'];
$idPessoa = $row['idPessoa'];
$dtAssinatura = date("Y-m-d H:i:s");

$status = "Baixado";
$userIP = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$usuario = htmlspecialchars($usuario);
//
$termo .= "<h5 style='text-align: center; margin-top: 50px; border-top: 1px solid #000; padding-top: 5px'>
            Devolução dos Equipamentos - Baixa do Termo
            <p>$vistoria</p></h5><br>";
//
$termo .= "<div style='text-align: center; margin-top: 30px; border-top: 1px solid #000; padding-top: 5px; font-size: 12px;'>
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

include_once "../includes/conexao_gerar.php";

$sql = "UPDATE rh_equip_termos 
            SET data_devolucao = :data_devolucao, 
                status = :status, 
                user_devolucao = :usuario, 
                userIPDev = :userIP, 
                userAgentDev = :userAgent,
                termo_html = :termo, 
                hashPDF = :hashPDF,
                vistoria_devolucao = :vistoria
            WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $idTermo);
$stmt->bindParam(':data_devolucao', $dtAssinatura);
$stmt->bindParam(':status', $status);
$stmt->bindParam(':usuario', $usuario);
$stmt->bindParam(':userIP', $userIP);
$stmt->bindParam(':userAgent', $userAgent);
$stmt->bindParam(':termo', $termo);
$stmt->bindParam(':hashPDF', $hashPDF);
$stmt->bindParam(':vistoria', $vistoria);

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



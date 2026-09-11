<?php
//
//- new_aj15_conq.php | Recupera dados CURRÍCULO: CERTIFICADO/CONQUISTA para Edição/Visualização
//- (C)haia, 31/03/2025 | (U) 14/05/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

if (empty($id)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
if (isset($_SESSION['idEmpresa'])) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;

include_once "../app/includes/conexao_gerar.php";
include_once "../app/includes/f_logs.php";

//- idPessoa vem da sessão - nunca de um campo do cliente, senão dá pra ler
//- conquista/certificado do currículo de qualquer pessoa só sabendo o id do registro.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoaSessao = (int) $_SESSION['candidato_idPessoa'];

//- Recupera dados do certificado/conquista
//
$sql = "SELECT C.*, P.nome, T.nome as dsTipo, DATE_FORMAT(C.criado_em, '%d/%m/%Y %H:%i') AS criado_em,
                ifnull(D.arquivo,'') as arquivo
                FROM rh_cv_conq C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_conqTipos T on T.idConqTipo = C.idConqTipo
                LEFT OUTER JOIN rh_documentos D on D.idDoc = C.idDoc
                WHERE C.id = :id AND C.idPessoa = :idPessoa";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $id);
$consulta->bindParam(':idPessoa', $idPessoaSessao, PDO::PARAM_INT);
$consulta->execute();
$linha = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$linha) {
    die(json_encode(["status" => false, "msg" => "Registro não encontrado."]));
}

$nome = $linha['nome'];

//- monta a URL para mostrar o Certificado
//
    $idPessoa = $linha['idPessoa'];
    $arquivo = $linha['arquivo'];
    if( ! empty($arquivo) ) $url = "/rh/app/docs/pessoa_$idPessoa/$arquivo"; else $url = "";

    $linha['url'] = $url;

if (isset($origem) && $origem == "visualizar") {
    $dados = implode( ", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Certificado/Conquista de $nome: Dados:( $dados )", "rh_cv_conq", $idModulo, $id);
}

$conn = null;

$retorno = [
        "status" => true,
        "d" => $linha // Aqui colocamos os dados dentro de 'd'
    ];

die(json_encode($retorno, JSON_PRETTY_PRINT));

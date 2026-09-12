<?php
//
//- rh_colhimento_aj3.php | Salva INCLUSÃO de NOVA de Denúncia/acolhimento
//- (C)haia, 18/08/2025
//

session_start();
include_once "conexao_gerar.php";
include_once "f_logs.php";

$idModulo = 15; // Acolhimento do RH

$agora = date('Y-m-d H:i:s');

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( $dados ) extract($dados);

/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO"];
die(json_encode($response));
/*
 rh_acolhimento_aj3.php | 2025-08-18 15:54:51 
{
    "identificacao": "anonimo",
    "nome": "",
    "email": "",
    "telefone": "",
    "tipoAssedio": "acolhimento",
    "tipoOutro": "",
    "relato": "<p><span style=\"font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify;\">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?<\/span><\/p><p><span style=\"font-family: &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14px; text-align: justify; color: white;\">Sed ut perspicia<\/span><span style=\"color: white;\">n&nbsp;<\/span><\/p>",
    "envolvidos": "tiago, lucas e matheus",
    "testemunhas": "sim",
    "nomesTestemunhas": "pedro e joão",
    "comunicado": "sim",
    "respostaComunicado": "bartolomeu e simão barjonas",
    "acompanhamento": "sim",
    "nomeContato": "Elizete da Silva",
    "emailContato": "elizete@email.com.br",
    "telefoneContato": "41 9 9999 9999"
}
*/

include_once "f_ouvidoria_cripto.php";

$sql = "INSERT INTO rh_denuncias (
    identificacao, nome, email, telefone, tipo_assedio, tipo_outro,
    relato, envolvidos, testemunhas, nomes_testemunhas, comunicado,
    resposta_comunicado, acompanhamento, nome_contato, email_contato,
    telefone_contato
) VALUES (
    :identificacao, :nome, :email, :telefone, :tipo_assedio, :tipo_outro,
    :relato, :envolvidos, :testemunhas, :nomes_testemunhas, :comunicado,
    :resposta_comunicado, :acompanhamento, :nome_contato, :email_contato,
    :telefone_contato
)";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ':identificacao'       => $identificacao,
    ':nome'                => criptografar($nome),
    ':email'               => criptografar($email),
    ':telefone'            => criptografar($telefone),
    ':tipo_assedio'        => $tipoAssedio,
    ':tipo_outro'          => $tipoOutro,
    ':relato'              => criptografar($relato),
    ':envolvidos'          => criptografar($envolvidos),
    ':testemunhas'         => $testemunhas,
    ':nomes_testemunhas'   => criptografar($nomesTestemunhas),
    ':comunicado'          => $comunicado,
    ':resposta_comunicado' => criptografar($respostaComunicado),
    ':acompanhamento'      => $acompanhamento,
    ':nome_contato'        => criptografar($nomeContato),
    ':email_contato'       => criptografar($emailContato),
    ':telefone_contato'    => criptografar($telefoneContato)
]);

$ultimoId = $conn->lastInsertId();

//
//- Cria linha do tempo do processo
//
// idAcaoTipo = 1 (Abertura de Denúncia)
//
$sql = "INSERT INTO rh_denuncias_ldt 
    (idAcaoTipo, idDenuncia, idEmpresa, idUsuario, data, descricao, idLogin) 
    VALUES (1, :idDenuncia, :idEmpresa, :idUsuario, :data, :descricao, :idLogin)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':idDenuncia' => $ultimoId,
    ':idEmpresa'  => 1,
    ':idUsuario'  => 0,
    ':data'       => $agora, // certifique-se de que $agora está no formato 'Y-m-d H:i:s'
    ':descricao'  => 'Denúncia Recebida',
    ':idLogin'    => 0
]);

$ultimoId = $conn->lastInsertId(); // pega o último ID inserido nessa conexão

f_log("INC", "Inclusão de Denúncia/Acolhimento", "rh_denuncias", $idModulo, $ultimoId);

$response = [
    "status" => true,
    "msg" => "Denúncia/Acolhimento registrada com sucesso!",
    "id" => $ultimoId
];

die( json_encode($response) );
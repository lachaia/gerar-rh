<?PHP
//
//- rh_ouvir_aj.php | RECEBE+SALVA FORMULÁRIO AVULSO DE OUVIDORIA
// (C)haia, 23/07/2025

$idModulo = 15; // Denúncias ao RH

session_start();
include_once "includes/conexao_gerar.php";
include_once "includes/f_logs.php";

$agora = date('Y-m-d H:i:s');

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( $dados ) extract($dados);
/*
include_once "includes/debug.php";
debug( json_encode( $dados, JSON_PRETTY_PRINT) );
 rh_denuncia_aj.php | 2025-07-23 17:13:59 
{
    "identificacao": "identificado",
    "nome": "Luiz Augusto Chaia",
    "email": "lachaia@gmail.com",
    "telefone": "42 9 654",
    "tipoAssedio": "moral",
    "tipoOutro": "",
    "relato": "<p>meu chefe me chamou de burro. fiquei muito infeliz com isso. nem consigo mais dormir \u00e0 noite. tenho pesadelos com ele.<\/p>",
    "files": "",
    "envolvidos": "pedro paulo",
    "testemunhas": "sim",
    "nomesTestemunhas": "Danilo",
    "comunicado": "sim",
    "respostaComunicado": "ao Sr. Francicso",
    "acompanhamento": "sim",
    "nomeContato": "Luiz Augusto Chaia",
    "emailContato": "lachaia@gmail.com",
    "telefoneContato": "42 9 654"
}
*/

include_once "includes/f_ouvidoria_cripto.php";

$sql = "INSERT INTO rh_ouvidoria (
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
$sql = "INSERT INTO rh_ouvidoria_ldt 
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

?>
<div id="mensagemSucesso" class="mensagem-sucesso">
  <div class="conteudo">
    <i class="fas fa-check-circle"></i><br>
    Sua denúncia foi enviada com sucesso!
  </div>
</div>

<style>
.mensagem-sucesso {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(240, 255, 240, 0.95); /* leve tom verde claro */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  font-family: Arial, sans-serif;
}

.mensagem-sucesso .conteudo {
  text-align: center;
  background-color: #d4edda;
  border: 2px solid #c3e6cb;
  color: #155724;
  padding: 30px 40px;
  border-radius: 12px;
  font-size: 20px;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
}

.mensagem-sucesso .conteudo i {
  font-size: 36px;
  color: #28a745;
  margin-bottom: 10px;
}
</style>

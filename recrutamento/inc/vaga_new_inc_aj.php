<?php
//
//- vaga_new_inc_aj.php | INCLUI NOVA VAGA (AJAX)
//- (C)haia,  11/08/2026
//

session_start();
ob_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../app/logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../../app/includes/email_config.php';

//- Inclui o arquivo de conexão com o banco de dados
include "../../app/includes/conexao_gerar.php";
include "../../app/includes/debug.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) extract($dados);

$arquivo_aut_gestor = salvar_arquivo_autorizacao_gestor();

// ============================================================
// COMPETÊNCIAS COMPORTAMENTAIS
// ============================================================

$html_c_comportamentais = gerar_html_competencias_comportamentais(
    $conn,
    $comp_comportamentais ?? []
);


// ============================================================
// COMPETÊNCIAS TÉCNICAS
// ============================================================

$html_c_tecnicas = gerar_html_competencias_tecnicas(
    $conn,
    $comp_tecnicas ?? []
);


// ============================================================
// EQUIPAMENTOS
// ============================================================

$html_equipamentos = gerar_html_equipamentos(
    $conn,
    $equipamentos ?? [],
    $equipamentos_outro ?? ''
);

/*
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão foi um Sucesso"]);
$conn = null;
die;
/*
vaga_new_inc_aj.php | 2026-08-12 10:58:35 
{
    "motivacao_id": "1",
    "sigilosa": "SIM",
    "formato": "Externo",
    "subsede_id": "105",
    "polo_id": "96",
    "idOrgao": "133",
    "salario": "1800",
    "tipo_vaga": "Est\u00e1gio",
    "idCargo": "2",
    "horario_trabalho": "08:00 as 18:00",
    "pref_genero": "Masculino",
    "pref_faixa_etaria": "Sim - entre 26 e 35",
    "regime_trabalho": "100% Presencial",
    "cnh": "Sim e ser\u00e1 um diferencial",
    "cep": "81315420",
    "endereco": "Rua Hamilton Taborda Ribas",
    "numero": "84",
    "bairro": "Cidade Industrial",
    "complemento": "casa 2",
    "cidade": "Curitiba",
    "uf": "PR",
    "formacao_necessaria": "Ensino Superior Cursando",
    "curso_superior_nome": "MATEM\u00c1TICA APLICADA",
    "estagio_curso_1": "FISICA EXPERIMENTAL",
    "estagio_curso_2": "F\u00cdSICA Q\u00dc\u00c2NTICA",
    "incluir_pergunta": "2",
    "pergunta_chave_texto": "VOC\u00ca GOSTA DE MANGA?",
    "experiencia": "3 ANOS DE SUPORTE \u00c0 USU\u00c1RIOS",
    "atividades_vaga": "FAZER A\r\nFAZER B\r\nSABER C\r\nINCLUIR D\r\nTAMB\u00c9M O E",
    "comp_comportamentais": [ "10", "5", "3" ],
    "comp_tecnicas": [ "7", "10", "11" ],
    "equipamentos": [ "2", "3", "4" ],
    "equipamentos_outro": "IMPRESSORA COLORIDA A LASER",
    "observacoes": "AQUI EU ESCREVO O QUE EU QUISER!"
}
*/

//
//- SALVA A SOLICITAÇÃO
//

$status_id = 1; // => "SOLICITADA" --> Aguardando Aprovação;
$criado_em = date('Y-m-d H:i:s');
$criado_por = $_SESSION['nmLogin'];
$email_resposta = $_SESSION['email'];

if (! isset($qtd) || empty($qtd)) $qtd = 1;
if (! isset($pcd) || empty($pcd)) $pcd = 0;

if ($sigilosa == 'SIM') $confidencial = 1;
else $confidencial = 0;

$salario = arruma_salario($salario);

$token = gerarTokenSeguro(64);

$sql = "INSERT INTO rs_vagas (
    subsede_id, polo_id, orgao_id, modalidade, cargo_id, horario, genero,
    etaria, cnh, trab_cep, trab_endereco, trab_numero, trab_complemento,
    trab_bairro, trab_cidade, trab_uf, trab_pais, formacao, curso_superior,
    curso_estagio_1, curso_estagio_2, pergunta_chave, experiencia,
    c_comportamentais, c_tecnicas, equipamentos, obs, login_id, solicitante_id, status_id,
    pcd, qtd, confidencial, criado_em, criado_por, atividades, salario,
    motivo_id, forma_recrutamento, tipo_contrato, token, email_resposta,
    arquivo_aut_gestor
) VALUES (
    :subsede_id, :polo_id, :orgao_id, :modalidade, :cargo_id, :horario, :genero,
    :etaria, :cnh, :trab_cep, :trab_endereco, :trab_numero, :trab_complemento,
    :trab_bairro, :trab_cidade, :trab_uf, :trab_pais, :formacao, :curso_superior,
    :curso_estagio_1, :curso_estagio_2, :pergunta_chave, :experiencia,
    :c_comportamentais, :c_tecnicas, :equipamentos, :obs, :login_id, :solicitante_id, :status_id,
    :pcd, :qtd, :confidencial, :criado_em, :criado_por, :atividades, :salario,
    :motivo_id, :forma_recrutamento, :tipo_contrato, :token, :email_resposta,
    :arquivo_aut_gestor
)";
$stmt = $conn->prepare($sql);

$stmt->bindParam(':login_id', $_SESSION['idLogin'], PDO::PARAM_INT);
//- solicitante_id = idPessoa (rh_pessoas) de quem preencheu a solicitação - mesma
//- referência de pessoa usada em todo o resto do sistema (rh_colaboradores,
//- rh_organograma etc.), não idUsuario. Dá pra usar como gestor padrão do parecer
//- de screening quando a vaga não tiver um definido explicitamente (ver
//- candidato_parecer.php).
$stmt->bindValue(':solicitante_id', $_SESSION['idPessoa'] ?? null, isset($_SESSION['idPessoa']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
$stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT);
$stmt->bindParam(':criado_em', $criado_em, PDO::PARAM_STR);
$stmt->bindParam(':criado_por', $criado_por, PDO::PARAM_STR);
$stmt->bindParam(':confidencial', $confidencial, PDO::PARAM_STR);
$stmt->bindParam(':subsede_id', $subsede_id, PDO::PARAM_INT);
$stmt->bindParam(':polo_id', $polo_id, PDO::PARAM_INT);
$stmt->bindParam(':orgao_id', $idOrgao, PDO::PARAM_INT);
$stmt->bindParam(':modalidade', $regime_trabalho, PDO::PARAM_STR);
$stmt->bindParam(':cargo_id', $idCargo, PDO::PARAM_INT);
$stmt->bindParam(':horario', $horario_trabalho, PDO::PARAM_STR);
$stmt->bindParam(':genero', $pref_genero, PDO::PARAM_STR);
$stmt->bindParam(':etaria', $pref_faixa_etaria, PDO::PARAM_STR);
$stmt->bindParam(':cnh', $cnh, PDO::PARAM_STR);
$stmt->bindParam(':trab_cep', $cep, PDO::PARAM_STR);
$stmt->bindParam(':trab_endereco', $endereco, PDO::PARAM_STR);
$stmt->bindParam(':trab_numero', $numero, PDO::PARAM_STR);
$stmt->bindParam(':trab_complemento', $complemento, PDO::PARAM_STR);
$stmt->bindParam(':trab_bairro', $bairro, PDO::PARAM_STR);
$stmt->bindParam(':trab_cidade', $cidade, PDO::PARAM_STR);
$stmt->bindParam(':trab_uf', $uf, PDO::PARAM_STR);
$stmt->bindValue(':trab_pais', 'Brasil', PDO::PARAM_STR);
$stmt->bindParam(':formacao', $formacao_necessaria, PDO::PARAM_STR);
$stmt->bindParam(':curso_superior', $curso_superior_nome, PDO::PARAM_STR);
$stmt->bindParam(':curso_estagio_1', $estagio_curso_1, PDO::PARAM_STR);
$stmt->bindParam(':curso_estagio_2', $estagio_curso_2, PDO::PARAM_STR);
$stmt->bindParam(':pergunta_chave', $pergunta_chave_texto, PDO::PARAM_STR);
$stmt->bindParam(':experiencia', $experiencia, PDO::PARAM_STR);
$stmt->bindParam(':c_comportamentais', $html_c_comportamentais, PDO::PARAM_STR);
$stmt->bindParam(':c_tecnicas', $html_c_tecnicas, PDO::PARAM_STR);
$stmt->bindParam(':equipamentos', $html_equipamentos, PDO::PARAM_STR);
$stmt->bindParam(':obs', $observacoes, PDO::PARAM_STR);
$stmt->bindParam(':salario', $salario, PDO::PARAM_STR);
$stmt->bindParam(':motivo_id', $motivacao_id, PDO::PARAM_INT);
$stmt->bindParam(':forma_recrutamento', $formato, PDO::PARAM_STR);
$stmt->bindParam(':tipo_contrato', $tipo_vaga, PDO::PARAM_STR);
$stmt->bindParam(':atividades', $atividades_vaga, PDO::PARAM_STR);
$stmt->bindParam(':token', $token, PDO::PARAM_STR);
$stmt->bindParam(':email_resposta', $email_resposta, PDO::PARAM_STR);
$stmt->bindParam(':arquivo_aut_gestor', $arquivo_aut_gestor, PDO::PARAM_STR);

$stmt->bindParam(':pcd', $pcd, PDO::PARAM_STR);
$stmt->bindParam(':qtd', $qtd, PDO::PARAM_INT);
//
if ($stmt->execute()) {
    $id = $conn->lastInsertId();
    //
    //- LINHA DO TEMPO DA VAGA (TIMELINE)
    //
        $agora = date('Y-m-d H:i:s');
        $oque = "Vaga Solicitada";
        $quem = $criado_por;
        //
        vaga_timeline($id, $agora, $oque, $quem, $conn);    
    //
    envia_emails($conn, $id);
    //
    resposta_json(["status" => true, "msg" => "Inclusão foi um Sucesso", "mensagem" => "Inclusão foi um Sucesso"]);
} else {
    resposta_json(["status" => false, "msg" => "Inclusão falhou", "mensagem" => "Inclusão falhou"]);
}

/**
 * Gera HTML com as descrições das competências comportamentais.
 */
function gerar_html_competencias_comportamentais(PDO $conn, array $ids): string
{
    if (empty($ids)) {
        return '';
    }

    // Garante que sejam apenas inteiros
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids);

    if (empty($ids)) {
        return '';
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "
        SELECT id, descricao
        FROM rs_competencias_comportamentais
        WHERE ativo = 1
          AND id IN ($placeholders)
        ORDER BY descricao
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array_values($ids));

    $html = '<ul>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $descricao = htmlspecialchars(
            $row['descricao'],
            ENT_QUOTES,
            'UTF-8'
        );

        $html .= "<li>{$descricao}</li>";
    }

    $html .= '</ul>';

    return $html;
}


/**
 * Gera HTML com as descrições das competências técnicas.
 */
function gerar_html_competencias_tecnicas(PDO $conn, array $ids): string
{
    if (empty($ids)) {
        return '';
    }

    $ids = array_map('intval', $ids);
    $ids = array_filter($ids);

    if (empty($ids)) {
        return '';
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "
        SELECT id, descricao
        FROM rs_competencias_tecnicas
        WHERE ativo = 1
          AND id IN ($placeholders)
        ORDER BY descricao
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array_values($ids));

    $html = '<ul>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $descricao = htmlspecialchars(
            $row['descricao'],
            ENT_QUOTES,
            'UTF-8'
        );

        $html .= "<li>{$descricao}</li>";
    }

    $html .= '</ul>';

    return $html;
}


/**
 * Gera HTML com os equipamentos selecionados.
 *
 * IDs:
 * 1 = Nenhum
 * 2 = Notebook
 * 3 = Smartphone
 * 4 = Monitor
 *
 * Também acrescenta equipamentos_outro, quando informado.
 */
function gerar_html_equipamentos(
    PDO $conn,
    array $ids,
    string $outro = ''
): string {

    $nomes = [
        1 => 'Nenhum',
        2 => 'Notebook (c/ carregador)',
        3 => 'Smartphone Corporativo (Carregador, chip)',
        4 => 'Monitor para Computador'
    ];

    $html = '';

    $ids = array_map('intval', $ids);
    $ids = array_filter($ids);

    /*
     * Se "Nenhum" estiver selecionado,
     * não faz sentido manter os demais equipamentos.
     */
    if (in_array(1, $ids, true)) {
        $ids = [1];
    }

    if (!empty($ids)) {

        $html .= '<ul>';

        foreach ($ids as $id) {

            if (isset($nomes[$id])) {

                $descricao = htmlspecialchars(
                    $nomes[$id],
                    ENT_QUOTES,
                    'UTF-8'
                );

                $html .= "<li>{$descricao}</li>";
            }
        }

        // Outro equipamento
        $outro = trim($outro);

        if ($outro !== '') {

            $outro = htmlspecialchars(
                $outro,
                ENT_QUOTES,
                'UTF-8'
            );

            $html .= "<li>{$outro}</li>";
        }

        $html .= '</ul>';
    }

    /*
     * Caso não tenha selecionado nenhum equipamento,
     * mas tenha preenchido "Outro".
     */ elseif (trim($outro) !== '') {

        $outro = htmlspecialchars(
            trim($outro),
            ENT_QUOTES,
            'UTF-8'
        );

        $html = "<ul><li>{$outro}</li></ul>";
    }

    return $html;
}


function arruma_salario($valor)
{
    $valor = trim((string) $valor);

    if ($valor === '') {
        return '0.00';
    }

    // Se houver vírgula, considera o padrão brasileiro:
    // 1.800,50 -> 1800.50
    if (strpos($valor, ',') !== false) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    }
    // Se não houver vírgula, remove pontos de milhar:
    // 1.800 -> 1800
    else {
        $valor = str_replace('.', '', $valor);
    }

    return $valor;
}

function envia_emails($conn, $vaga_id)
{
    //
    //- RECUPERA DADOS DA VAGA
    //
        $sql = "SELECT V.*, 
                    S.identificador as subsede_ds, 
                    O.descricao as orgao_ds, 
                    date(V.criado_em) as _criado_em,
                    P.identificador as polo_ds, 
                    DATEDIFF(CURRENT_DATE(), DATE(V.criado_em)) AS dias,
                    ST.*, C.nome as cargo_ds
                FROM rs_vagas V
                LEFT JOIN rh_subsedes S on S.subsede_id = V.subsede_id 
                LEFT JOIN rh_polos P on P.polo_id = V.polo_id
                LEFT JOIN rh_organograma O on O.idOrgao = V.orgao_id
                LEFT JOIN rs_vagas_status ST ON ST.id = V.status_id
                LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
                WHERE V.id = :vaga_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
        $stmt->execute();
        $dados_vaga = $stmt->fetch(PDO::FETCH_ASSOC);

    //
    //- Quantos Superintendentes irão aprovar a vaga?
    //

    $sql = "SELECT S.* 
            FROM rs_superintendentes S 
            WHERE S.ativo = 1";
    $stmt_super = $conn->prepare($sql);
    $stmt_super->execute();

    while ($row = $stmt_super->fetch(PDO::FETCH_ASSOC)) {
        //
        //- Envia e-mail para Superintendente
        //
        $mail = criarMailer();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        //
        $mail->setFrom("rh@gerar.org.br", "Equipe RH");  // Nome e e-Mail do Remetente
        $mail->Subject = "GERAR|R&S - Solicitação de abertura de Vaga";

        $mail->Body = '
            <div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa; color: #333333; border-radius: 8px;">
                
                <div style="text-align: center; padding-bottom: 20px; border-bottom: 2px solid #0056b3;">
                    <h2 style="color: #0056b3; margin: 0; font-size: 22px;">Solicitação de Aprovação de Vaga</h2>
                </div>

                <div style="padding: 20px 0;">
                    <p style="font-size: 15px; line-height: 1.6; margin-bottom: 20px;">
                        Prezado(a) Superintendente ' . $row['identificador'] . ',
                    </p>
                    <p style="font-size: 15px; line-height: 1.6; margin-bottom: 20px;">
                        Uma nova abertura de vaga foi cadastrada e requer a sua avaliação e aprovação para prosseguimento no processo seletivo.
                    </p>

                    <!-- Bloco de detalhes da vaga -->
                    <div style="background-color: #ffffff; padding: 18px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                        <h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">
                            Detalhes da Vaga
                        </h3>
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <td style="padding: 6px 0; color: #718096; width: 35%;"><strong>Código/ID:</strong></td>
                                <td style="padding: 6px 0; color: #1a202c;">#' . $vaga_id . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 0; color: #718096;"><strong>Cargo/Função:</strong></td>
                                <td style="padding: 6px 0; color: #1a202c;">' . htmlspecialchars($dados_vaga['cargo_ds'] ?? 'Não informado') . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 0; color: #718096;"><strong>Lotação:</strong></td>
                                <td style="padding: 6px 0; color: #1a202c;">' . 
                                "SubSede " . htmlspecialchars($dados_vaga['subsede_ds'] ?? 'Não informado') . "<br>" .
                                htmlspecialchars($dados_vaga['polo_ds'] ?? 'Não informado') . "<br>" .
                                "Lotação: " . htmlspecialchars($dados_vaga['orgao_ds'] ?? 'Não informado') . 
                                '</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 0; color: #718096;"><strong>Solicitante:</strong></td>
                                <td style="padding: 6px 0; color: #1a202c;">' . htmlspecialchars($dados_vaga['criado_por'] ?? 'Não informado') . '</td>
                            </tr>
                        </table>
                    </div>

                    <p style="font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                        Por favor, clique no botão abaixo para acessar a página de decisão e realizar a aprovação ou reprovação desta vaga:
                    </p>

                    <!-- Botão de Ação -->
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="https://rh.gerar.org.br/recrutamento/aprova.php?id=' . $vaga_id . '&super_id=' . $row['id'] . '&token=' . $dados_vaga['token'] . '" 
                        style="background-color: #0056b3; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;">
                            Analisar e Aprovar Vaga
                        </a>
                    </div>

                    <p style="font-size: 12px; color: #a0aec0; text-align: center; margin-top: 30px;">
                        Caso o botão não funcione, copie e cole o link a seguir no seu navegador:<br>
                        <span style="color: #4a5568; word-break: break-all;">https://rh.gerar.org.br/recrutamento/aprova.php?id=' . $vaga_id . '&super_id=' . $row['id'] . '&token=' . $token . '</span>
                    </p>
                </div>

                <div style="border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center; font-size: 12px; color: #718096;">
                    <p style="margin: 0;">Este é um e-mail automático gerado pelo Sistema de RH - GERAR. Por favor, não responda diretamente a esta mensagem.</p>
                </div>
            </div>
            ';

        $mail->addAddress($row['email'], $row['identificador']);

        //if (!empty($cc)) $mail->addCC($cc, '');       //- Copia para...
        //if (!empty($cco)) $mail->addBCC($cco, '');    //- Copia Oculta
        //$mail->addAttachment( $documento, $arquivo);  //- Anexo ao email

        //
        //-- ENVIA A MENSAGEM
        //
        if ($mail->send()) {
            //
            //- LINHA DO TEMPO DA VAGA (TIMELINE)
            //
                $agora = date('Y-m-d H:i:s');
                $super_id = $row['id'] ?? null;
                $oque = "Enviado e-mail para aprovação da vaga à " . $row['identificador'];
                $quem = $dados_vaga['criado_por'];
                //
                registra_aprovacao_vaga($vaga_id, $super_id, $agora, $conn);
                vaga_timeline($vaga_id, $agora, $oque, $quem, $conn);
                //
        } else {
            $retorna = ["status" => false, "msg" => '<div class="alert alert-danger" role="alert">ERRO: e-Mail NÃO enviado! ' . $mail->ErrorInfo . '</div>'];
        }
    }
}

exit();

/**
 * Gera um token seguro e amigável para URLs
 * 
 * @param int $tamanho Comprimento do token (padrão 64 caracteres)
 * @return string Token em formato hexadecimal seguro
 */
function gerarTokenSeguro($tamanho = 64) {
    // Generates cryptographically secure random bytes
    $bytes = random_bytes((int) ceil($tamanho / 2));
    
    // Convert to hex string and trim to exact size
    return substr(bin2hex($bytes), 0, $tamanho);
}

function resposta_json(array $dados): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados);
    exit();
}

function vaga_timeline($vaga_id, $agora, $oque, $quem, $conn){
    //
    $sql = "INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) 
    VALUES (:vaga_id, :quando, :oque, :quem)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindParam(':quando', $agora, PDO::PARAM_STR);
    $stmt->bindParam(':oque', $oque, PDO::PARAM_STR);
    $stmt->bindParam(':quem', $quem, PDO::PARAM_STR);
    $stmt->execute();
    $id_timeline = $conn->lastInsertId();
    return $id_timeline;
}

function registra_aprovacao_vaga($vaga_id, $super_id, $enviado_em, $conn): int
{
    $super_id = (int) $super_id;

    if ($super_id <= 0) {
        throw new \Exception('ID do superintendente aprovador nao encontrado.');
    }

    $sql = "INSERT INTO rs_vagas_aprova (vaga_id, super_id, enviado_em)
            VALUES (:vaga_id, :super_id, :enviado_em)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindParam(':super_id', $super_id, PDO::PARAM_INT);
    $stmt->bindParam(':enviado_em', $enviado_em, PDO::PARAM_STR);
    $stmt->execute();

    return $conn->lastInsertId();
}

function salvar_arquivo_autorizacao_gestor(): string
{
    if (
        !isset($_FILES['email_gestor_file']) ||
        !is_array($_FILES['email_gestor_file']) ||
        $_FILES['email_gestor_file']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        resposta_json([
            "status" => false,
            "msg" => "O anexo do e-mail do gestor e obrigatorio."
        ]);
    }

    $arquivo = $_FILES['email_gestor_file'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        resposta_json([
            "status" => false,
            "msg" => "Falha ao receber o anexo do e-mail do gestor."
        ]);
    }

    if (!is_uploaded_file($arquivo['tmp_name'])) {
        resposta_json([
            "status" => false,
            "msg" => "Upload do anexo invalido."
        ]);
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $extensoes_permitidas = ['pdf', 'msg', 'eml', 'jpg', 'jpeg', 'png'];

    if (!in_array($extensao, $extensoes_permitidas, true)) {
        resposta_json([
            "status" => false,
            "msg" => "Tipo de arquivo nao permitido para o anexo do gestor."
        ]);
    }

    $diretorio_destino = __DIR__ . '/../docs/';

    if (!is_dir($diretorio_destino) || !is_writable($diretorio_destino)) {
        resposta_json([
            "status" => false,
            "msg" => "Diretorio de documentos indisponivel para gravacao."
        ]);
    }

    do {
        $nome_arquivo = 'aut_gestor_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        $destino = $diretorio_destino . $nome_arquivo;
    } while (file_exists($destino));

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        resposta_json([
            "status" => false,
            "msg" => "Nao foi possivel salvar o anexo do e-mail do gestor."
        ]);
    }

    return $nome_arquivo;
}

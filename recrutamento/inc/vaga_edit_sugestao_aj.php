<?php
//
//- vaga_edit_sugestao_aj.php | Sugere o preenchimento dos campos de publicação da vaga
//- com base no que o solicitante já informou em solicitacao.php (rs_vagas)
//- (C)haia, 24/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$vaga_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$vaga_id) {
    echo json_encode(["status" => false, "msg" => "ID da vaga inválido."]);
    exit;
}

$sql = "SELECT V.*, S.identificador AS subsede_ds, C.nome AS cargo_ds
        FROM rs_vagas V
        LEFT JOIN rh_subsedes S ON S.subsede_id = V.subsede_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        WHERE V.id = :vaga_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vaga_id', $vaga_id, PDO::PARAM_INT);
$stmt->execute();
$vaga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vaga) {
    echo json_encode(["status" => false, "msg" => "Vaga não encontrada."]);
    exit;
}

$cargo = $vaga['cargo_ds'] ?: 'Colaborador(a)';

[$setor, $area] = sugerir_setor_area($conn, (int) $vaga['orgao_id']);

$sugestao = [
    "identificador"      => sugerir_identificador($cargo, $vaga['subsede_ds'] ?? ''),
    "codigo_vaga"        => sugerir_codigo_vaga($vaga_id),
    "setor_ds"           => $setor,
    "area"               => $area,
    "nivel_experiencia"  => sugerir_nivel_experiencia($vaga['tipo_contrato'] ?? '', $vaga['formacao'] ?? '', $vaga['experiencia'] ?? ''),
    "descricao"          => sugerir_descricao($vaga, $cargo, $setor, $area),
    "resumo"             => sugerir_resumo($cargo, $setor, $vaga['modalidade'] ?? '', $vaga['tipo_contrato'] ?? ''),
    "diferenciais"       => sugerir_diferenciais($vaga),
    "beneficios"         => sugerir_beneficios($vaga['tipo_contrato'] ?? ''),
];

echo json_encode(["status" => true, "dados" => $sugestao]);
exit;

//
//- FUNÇÕES DE SUGESTÃO (heurísticas simples sobre o que já foi preenchido na solicitação)
//

function sugerir_identificador(string $cargo, string $subsede): string
{
    $texto = $subsede !== '' ? "{$cargo} - {$subsede}" : $cargo;
    return mb_substr($texto, 0, 150);
}

function sugerir_codigo_vaga(int $vaga_id): string
{
    return "RS" . date('y') . '-' . str_pad((string) $vaga_id, 4, '0', STR_PAD_LEFT);
}

//
//- Sobe a árvore de rh_organograma (via idSupervisor) a partir do órgão da vaga para
//- achar o nível "Gerência/Assessoria" (nivel = 3) como sugestão de Área. O Setor
//- sugerido é o próprio órgão informado na solicitação, com o prefixo do cargo do
//- nível (Coordenação de / Supervisão de / Liderança de) removido.
//
function sugerir_setor_area(PDO $conn, int $orgao_id): array
{
    if (!$orgao_id) {
        return ['', ''];
    }

    $stmt = $conn->prepare("SELECT idOrgao, descricao, nivel, idSupervisor FROM rh_organograma WHERE idOrgao = :id");
    $stmt->bindParam(':id', $orgao_id, PDO::PARAM_INT);
    $stmt->execute();
    $orgao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orgao) {
        return ['', ''];
    }

    $ancestral_gerencia = buscar_ancestral_por_nivel($conn, $orgao_id, 3);

    $setor = limpar_prefixo_orgao($orgao['descricao']);
    $area  = $ancestral_gerencia ? limpar_prefixo_orgao($ancestral_gerencia['descricao']) : $setor;

    return [mb_substr($setor, 0, 45), mb_substr($area, 0, 45)];
}

function buscar_ancestral_por_nivel(PDO $conn, int $orgao_id, int $nivel_alvo, int $max_saltos = 10): ?array
{
    $atual = $orgao_id;

    for ($i = 0; $i < $max_saltos; $i++) {
        $stmt = $conn->prepare("SELECT idOrgao, descricao, nivel, idSupervisor FROM rh_organograma WHERE idOrgao = :id");
        $stmt->bindParam(':id', $atual, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }
        if ((int) $row['nivel'] === $nivel_alvo) {
            return $row;
        }
        if ((int) $row['idSupervisor'] === (int) $row['idOrgao']) {
            return null; // chegou na raiz (Presidência aponta pra si mesma)
        }

        $atual = (int) $row['idSupervisor'];
    }

    return null;
}

function limpar_prefixo_orgao(string $descricao): string
{
    return trim(preg_replace('/^(Coordenação|Supervisão|Liderança|Gerência|Assessoria|Diretoria)\s+de\s+/ui', '', $descricao));
}

function sugerir_nivel_experiencia(string $tipo_contrato, string $formacao, string $experiencia): string
{
    if (stripos($tipo_contrato, 'Estágio') !== false) {
        return 'Estagiário';
    }

    if (preg_match('/(\d+)\s*ano/ui', $experiencia, $m)) {
        $anos = (int) $m[1];
        if ($anos <= 0) return 'Sem experiência anterior';
        if ($anos <= 2) return 'Júnior';
        if ($anos <= 5) return 'Pleno';
        return 'Sênior';
    }

    if (stripos($formacao, 'Pós-Graduação') !== false) return 'Sênior';
    if (stripos($formacao, 'Ensino Superior Completo') !== false) return 'Pleno';
    if (stripos($formacao, 'Ensino Superior Cursando') !== false) return 'Júnior';

    return 'A definir';
}

//
//- "descricao", "diferenciais" e "beneficios" viram editores Summernote no
//- formulário (ver js/vaga_edit.js) — as sugestões abaixo já saem em HTML.
//- "resumo" continua texto puro de propósito (varchar(255), aparece na
//- listagem de vagas, não deve virar HTML).
//

//
//- "atividades" e "experiencia" já vêm em HTML do Summernote de solicitacao.php.
//- Se algum dado legado estiver em texto puro (sem tags), envolve em <p> preservando
//- as quebras de linha, em vez de jogar tudo achatado num parágrafo só.
//
function garantir_html(string $conteudo): string
{
    if ($conteudo !== strip_tags($conteudo)) {
        return $conteudo; // já é HTML
    }

    return '<p>' . nl2br(htmlspecialchars($conteudo)) . '</p>';
}

function sugerir_descricao(array $vaga, string $cargo, string $setor, string $area): string
{
    $qtd = (int) ($vaga['qtd'] ?? 1);
    $plural = $qtd > 1 ? "{$qtd} profissionais" : "1 profissional";
    $lotacao = trim("{$area} / {$setor}", ' /');

    $html = "<p>A GERAR está em busca de {$plural} para atuar como <strong>" . htmlspecialchars($cargo) . "</strong>"
          . ($lotacao !== '' ? ", na área de " . htmlspecialchars($lotacao) : "") . ".</p>";

    $info = [];
    if (!empty($vaga['modalidade']))      $info[] = "Regime de trabalho: " . htmlspecialchars($vaga['modalidade']) . ".";
    if (!empty($vaga['tipo_contrato']))   $info[] = "Tipo de contratação: " . htmlspecialchars($vaga['tipo_contrato']) . ".";
    if (!empty($vaga['horario']))         $info[] = "Horário de trabalho: " . htmlspecialchars($vaga['horario']) . ".";
    if (!empty($info)) {
        $html .= "<p>" . implode('<br>', $info) . "</p>";
    }

    if (!empty($vaga['atividades'])) {
        $html .= "<p><strong>Principais atividades:</strong></p>" . garantir_html($vaga['atividades']);
    }
    if (!empty($vaga['experiencia'])) {
        $html .= "<p><strong>Experiência desejada:</strong></p>" . garantir_html($vaga['experiencia']);
    }

    return $html;
}

function sugerir_resumo(string $cargo, string $setor, string $modalidade, string $tipo_contrato): string
{
    $texto = "{$cargo}" . ($setor !== '' ? " — {$setor}" : "");
    if ($modalidade !== '') $texto .= " | {$modalidade}";
    if ($tipo_contrato !== '') $texto .= " | {$tipo_contrato}";

    return mb_substr($texto, 0, 255);
}

//
//- Não inclui preferência de gênero/faixa etária aqui de propósito: são dados de uso
//- interno da solicitação e não devem virar texto de divulgação pública da vaga.
//
function sugerir_diferenciais(array $vaga): string
{
    $itens = [];

    if (!empty($vaga['curso_superior'])) {
        $itens[] = "Formação ou cursando " . trim($vaga['curso_superior']);
    }
    if (!empty($vaga['curso_estagio_1'])) {
        $itens[] = "Cursando " . trim($vaga['curso_estagio_1']);
    }
    if (!empty($vaga['curso_estagio_2'])) {
        $itens[] = "Cursando " . trim($vaga['curso_estagio_2']);
    }
    if (stripos((string) $vaga['cnh'], 'diferencial') !== false) {
        $itens[] = "Possuir CNH";
    }

    if (empty($itens)) {
        return "<p>A definir pelo recrutador, conforme perfil dos candidatos disponíveis.</p>";
    }

    return lista_html($itens);
}

function sugerir_beneficios(string $tipo_contrato): string
{
    if (stripos($tipo_contrato, 'Estágio') !== false) {
        $itens = ['Bolsa-auxílio', 'Vale-transporte', 'Seguro de vida (conforme convênio de estágio)'];
        $obs   = 'Sugestão padrão — ajustar conforme convênio vigente.';
    } else {
        $itens = ['Vale-transporte', 'Vale-alimentação/refeição', 'Plano de saúde', 'Seguro de vida'];
        $obs   = 'Sugestão padrão — ajustar conforme política de benefícios vigente.';
    }

    return lista_html($itens) . "<p><em>{$obs}</em></p>";
}

function lista_html(array $itens): string
{
    $html = '<ul>';
    foreach ($itens as $item) {
        $html .= '<li>' . htmlspecialchars($item) . '</li>';
    }
    return $html . '</ul>';
}

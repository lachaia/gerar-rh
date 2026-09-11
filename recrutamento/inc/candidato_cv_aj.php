<?php
//
//- candidato_cv_aj.php | Devolve o currículo completo do candidato de uma
//- candidatura (dados pessoais, diversidade, experiências, formação, idiomas,
//- conquistas, habilidades) - somente leitura, para o modal em vaga_candidatos.php
//- (C)haia, 27/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";

$candidatura_id = filter_input(INPUT_GET, 'candidatura_id', FILTER_VALIDATE_INT);
if (!$candidatura_id) {
    echo json_encode(["status" => false, "msg" => "Dados inválidos."]);
    exit;
}

$sql = "SELECT CA.pessoa_id, O.descricao AS origem_ds
        FROM rs_vagas_candidaturas CA
        LEFT JOIN rs_candidatos_origem O ON O.id = CA.origem_id
        WHERE CA.id = :candidatura_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();
$candidatura = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidatura) {
    echo json_encode(["status" => false, "msg" => "Candidatura não encontrada."]);
    exit;
}
$idPessoa = (int) $candidatura['pessoa_id'];
$origem_ds = $candidatura['origem_ds'];

//- Dados pessoais
$sql = "SELECT P.nome, P.nomeSocial, P.telefone, P.email, P.sexo, P.nacionalidade,
               G.descricao AS escolaridade
        FROM rh_pessoas P
        LEFT OUTER JOIN rh_graus_instrucao G ON G.id = P.idGrauEscola
        WHERE P.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$dados_pessoais = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

//- Diversidade + arquivo do CV (rh_cv)
$sql = "SELECT C.arquivo, C.genero, C.linkedin, C.deficiente, C.def_fisica, C.def_visual,
               C.def_auditiva, C.def_mental, C.def_intelectual, C.def_autista, C.cid,
               C.cor, C.pronome, C.orientacao, C.idGenero,
               ifnull(B.nome, '') AS nmCidade, ifnull(B.uf, '') AS uf
        FROM rh_cv C
        LEFT OUTER JOIN rh_cidades B ON B.idCidade = C.idCidade
        WHERE C.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$diversidade = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$arquivo_url = !empty($diversidade['arquivo']) ? "../app/docs_view.php?pessoa={$idPessoa}&arquivo=" . rawurlencode($diversidade['arquivo']) : null;

//- Converte o HTML rico do Summernote (preenchido pelo próprio candidato, em new.php)
//- em texto simples antes de mandar pro navegador do recrutador - o candidato controla
//- esse conteúdo, então renderizar o HTML bruto como confiável abriria XSS armazenado
//- no painel interno (diferente dos campos de vaga, preenchidos só por staff).
function texto_simples(?string $html): string
{
    $html = trim((string) $html);
    if ($html === '') {
        return '';
    }
    //- Preserva quebras de linha visuais antes de remover as tags.
    $html = preg_replace('/<\/(p|div|li)>|<br\s*\/?>/i', "\n", $html);
    $texto = trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
    return preg_replace("/\n{3,}/", "\n\n", $texto);
}

//- Experiências profissionais
$sql = "SELECT id, empresa, cargo, descricao, ano_ini, ano_fim, ativo
        FROM rh_cv_exp WHERE idPessoa = :idPessoa ORDER BY ano_ini DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($experiencias as &$exp) {
    $exp['descricao'] = texto_simples($exp['descricao']);
}
unset($exp);

//- Formação acadêmica
$sql = "SELECT F.id, F.curso, F.ano_conclusao, N.nivel, I.sigla
        FROM rh_cv_fa F
        INNER JOIN rh_fa_niveis N ON N.idNivel = F.idNivel
        INNER JOIN rh_fa_instituicoes I ON I.idInstituicao = F.idInstituicao
        WHERE F.idPessoa = :idPessoa ORDER BY F.ano_conclusao DESC, F.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$formacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

//- Idiomas
$sql = "SELECT C.id, I.nome AS idioma, F.nome AS fluencia
        FROM rh_cv_idiomas C
        INNER JOIN rh_idiomas I ON I.idIdioma = C.idIdioma
        INNER JOIN rh_fluencias F ON F.idFluencia = C.idFluencia
        WHERE C.idPessoa = :idPessoa ORDER BY C.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$idiomas = $stmt->fetchAll(PDO::FETCH_ASSOC);

//- Conquistas e certificados
$sql = "SELECT C.id, C.ano, C.titulo, T.nome AS dsTipo
        FROM rh_cv_conq C
        INNER JOIN rh_conqTipos T ON T.idConqTipo = C.idConqTipo
        WHERE C.idPessoa = :idPessoa ORDER BY C.ano DESC, C.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$conquistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

//- Habilidades
$sql = "SELECT habilidade FROM rh_cv_habilidades WHERE idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$habilidades = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    "status" => true,
    "dadosPessoais" => $dados_pessoais,
    "origem" => $origem_ds,
    "diversidade" => $diversidade,
    "arquivoUrl" => $arquivo_url,
    "experiencias" => $experiencias,
    "formacoes" => $formacoes,
    "idiomas" => $idiomas,
    "conquistas" => $conquistas,
    "habilidades" => $habilidades,
]);
exit;

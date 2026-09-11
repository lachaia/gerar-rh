<?php
//
//- new_aj18_carrega_tudo.php | Devolve tudo que já foi salvo do CV (diversidade,
//- experiências, formação, idiomas, conquistas, habilidades) pra repopular o
//- assistente quando a pessoa volta com o mesmo CPF (ex.: cancelou no meio e
//- voltou depois) - sem isso, só o bloco 1 aparecia preenchido e o resto do
//- que já tinha sido salvo ficava invisível (mas continuava no banco).
//- (C)haia, 2026-08-27
//

session_start();

include "../app/includes/conexao_gerar.php";

if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

//- Diversidade + upload de CV (rh_cv)
$sql = "SELECT C.arquivo, C.deficiente, C.def_fisica, C.def_visual, C.def_auditiva,
               C.def_mental, C.def_intelectual, C.def_autista, C.cid, C.idCidade,
               C.cor, C.pronome, C.orientacao, C.idGenero,
               ifnull(B.nome, '') AS nmCidade, ifnull(B.uf, '') AS uf
        FROM rh_cv C
        LEFT OUTER JOIN rh_cidades B ON B.idCidade = C.idCidade
        WHERE C.idPessoa = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$diversidade = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

//- Experiências profissionais
$sql = "SELECT id, empresa, cargo, ano_ini, ano_fim, ativo
        FROM rh_cv_exp WHERE idPessoa = :idPessoa ORDER BY ano_ini DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
$sql = "SELECT C.id, I.nome AS idioma, F.nome AS fluencia, DATE_FORMAT(C.criado_em, '%d/%m/%Y') AS criado_em
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
    "diversidade" => $diversidade,
    "experiencias" => $experiencias,
    "formacoes" => $formacoes,
    "idiomas" => $idiomas,
    "conquistas" => $conquistas,
    "habilidades" => $habilidades,
]);

<?php
//
//- candidato_manual_inc_aj.php | Inclui manualmente um candidato no funil de uma vaga
//- (indicação, e-mail, LinkedIn etc. - fora do portal público) | vaga_candidatos.php
//- (C)haia, 27/08/2026 | (U) 2026-08-27
//
//- Faz o mesmo dedupe por CPF que talentos/new_aj1.php + new_aj2.php: se a pessoa já
//- existe em rh_pessoas (já tem currículo na base ou já foi colaborador), reaproveita
//- o cadastro; senão cria um registro mínimo (nome, telefone, e-mail) na hora. Também
//- garante o registro em rh_cv (currículo) e, se informados, grava o LinkedIn e o
//- arquivo do currículo anexado - mesmo formato/local que talentos/new_aj3.php usa,
//- pra aparecer certinho no modal de currículo (candidato_cv_aj.php).
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header("location: ../logout.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

include "../../app/includes/conexao_gerar.php";
include "../../app/includes/f_ia_curriculo.php";

const FLUXO_APLICADO = 1;
const EXTENSOES_PERMITIDAS = ['pdf', 'doc', 'docx'];

//- filter_input(INPUT_POST, ...) não é confiável aqui: o formulário manda
//- multipart/form-data (por causa do upload de arquivo), e o buffer interno do
//- filter_input não acompanha $_POST direito nesse cenário - lê $_POST direto.
$vaga_id = isset($_POST['vaga_id']) ? (int) $_POST['vaga_id'] : 0;
$cpf = trim((string) ($_POST['cpf'] ?? ''));
$nome = trim((string) ($_POST['nome'] ?? ''));
$telefone = trim((string) ($_POST['telefone'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$linkedin = trim((string) ($_POST['linkedin'] ?? ''));
$origem_id = isset($_POST['origem_id']) ? (int) $_POST['origem_id'] : 0;

$cpf_limpo = preg_replace('/\D+/', '', $cpf);

if (!$vaga_id || $cpf_limpo === '' || strlen($cpf_limpo) !== 11 || $nome === '' || !$origem_id) {
    echo json_encode(["status" => false, "msg" => "Preencha CPF (válido), nome e origem do candidato."]);
    exit;
}

$stmt = $conn->prepare("SELECT descricao FROM rs_candidatos_origem WHERE id = :id AND ativo = 1");
$stmt->bindValue(':id', $origem_id, PDO::PARAM_INT);
$stmt->execute();
$origem_ds = $stmt->fetchColumn();
if (!$origem_ds) {
    echo json_encode(["status" => false, "msg" => "Origem inválida."]);
    exit;
}

//- Valida o arquivo (se enviado) antes de mexer no banco.
$tem_arquivo = isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] !== UPLOAD_ERR_NO_FILE;
if ($tem_arquivo) {
    if ($_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => false, "msg" => "Erro no upload do arquivo."]);
        exit;
    }
    $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, EXTENSOES_PERMITIDAS, true)) {
        echo json_encode(["status" => false, "msg" => "Formato de arquivo não permitido (use PDF, DOC ou DOCX)."]);
        exit;
    }
}

$stmt = $conn->prepare("SELECT id FROM rs_vagas WHERE id = :id");
$stmt->bindValue(':id', $vaga_id, PDO::PARAM_INT);
$stmt->execute();
if (!$stmt->fetch()) {
    echo json_encode(["status" => false, "msg" => "Vaga não encontrada."]);
    exit;
}

try {
    $conn->beginTransaction();

    //- Dedupe tolerante a formatação (registros antigos podem ter CPF com pontuação).
    $sql = "SELECT idPessoa, nome FROM rh_pessoas
            WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = :cpf
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':cpf', $cpf_limpo, PDO::PARAM_STR);
    $stmt->execute();
    $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pessoa) {
        $idPessoa = (int) $pessoa['idPessoa'];
        $nome_final = $pessoa['nome'];
    } else {
        $sql = "INSERT INTO rh_pessoas (cpf, nome, telefone, email) VALUES (:cpf, :nome, :telefone, :email)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':cpf', $cpf_limpo, PDO::PARAM_STR);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null, $telefone !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':email', $email !== '' ? $email : null, $email !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
        $idPessoa = (int) $conn->lastInsertId();
        $nome_final = $nome;
    }

    $stmt = $conn->prepare("SELECT id FROM rs_vagas_candidaturas WHERE vaga_id = :vaga_id AND pessoa_id = :pessoa_id");
    $stmt->bindValue(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindValue(':pessoa_id', $idPessoa, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->fetch()) {
        $conn->rollBack();
        echo json_encode(["status" => false, "msg" => "{$nome_final} já está candidatado(a) a esta vaga."]);
        exit;
    }

    //- Garante o currículo (rh_cv) - é dele que candidato_cv_aj.php lê tudo.
    $stmt = $conn->prepare("SELECT idCV, linkedin FROM rh_cv WHERE idPessoa = :idPessoa");
    $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->execute();
    $cv_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cv_existente) {
        $stmt = $conn->prepare("INSERT INTO rh_cv (idPessoa) VALUES (:idPessoa)");
        $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();
    }

    //- Só grava o LinkedIn se ainda não houver um salvo - não sobrescreve o que já
    //- está no currículo (o campo vem travado na tela quando já existe, mas protege
    //- aqui também caso a requisição não venha da tela).
    if ($linkedin !== '' && empty($cv_existente['linkedin'])) {
        $stmt = $conn->prepare("UPDATE rh_cv SET linkedin = :linkedin WHERE idPessoa = :idPessoa");
        $stmt->bindValue(':linkedin', $linkedin, PDO::PARAM_STR);
        $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();
    }

    if ($tem_arquivo) {
        $nomeArquivo = "CV_{$idPessoa}_" . time() . ".{$extensao}";
        $uploadDir = "../../app/docs/pessoa_{$idPessoa}/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $uploadDir . $nomeArquivo)) {
            $conn->rollBack();
            echo json_encode(["status" => false, "msg" => "Falha ao salvar o arquivo do currículo."]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE rh_cv SET arquivo = :arquivo WHERE idPessoa = :idPessoa");
        $stmt->bindValue(':arquivo', $nomeArquivo, PDO::PARAM_STR);
        $stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();

        //- Currículo novo (pessoa ainda não tinha rh_cv) + arquivo em PDF - tenta
        //- pré-preencher experiências/formação/idiomas/conquistas/habilidades via IA.
        //- Se a extração falhar por qualquer motivo, não interrompe a inclusão do
        //- candidato - só fica sem o pré-preenchimento automático.
        if (!$cv_existente && $extensao === 'pdf') {
            $ia_resultado = ia_extrair_curriculo_e_preencher($conn, $idPessoa, $uploadDir . $nomeArquivo, $_SESSION['idLogin']);
        }
    }

    $sql = "INSERT INTO rs_vagas_candidaturas (vaga_id, pessoa_id, fluxo_id, fluxo_alterado_em, origem_id, criado_em)
            VALUES (:vaga_id, :pessoa_id, :fluxo, NOW(), :origem_id, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindValue(':pessoa_id', $idPessoa, PDO::PARAM_INT);
    $stmt->bindValue(':fluxo', FLUXO_APLICADO, PDO::PARAM_INT);
    $stmt->bindValue(':origem_id', $origem_id, PDO::PARAM_INT);
    $stmt->execute();

    $quem = $_SESSION['nmLogin'] ?? '';
    $stmt = $conn->prepare("INSERT INTO rs_vagas_timeline (vaga_id, quando, oque, quem) VALUES (:vaga_id, NOW(), :oque, :quem)");
    $stmt->bindValue(':vaga_id', $vaga_id, PDO::PARAM_INT);
    $stmt->bindValue(':oque', "Candidato adicionado manualmente por {$quem}: {$nome_final} (origem: {$origem_ds})");
    $stmt->bindValue(':quem', mb_substr($quem, 0, 45));
    $stmt->execute();

    $conn->commit();

    $msg = "{$nome_final} adicionado(a) ao funil desta vaga.";
    if (!empty($ia_resultado['ok'])) {
        $msg .= " Currículo pré-preenchido por IA a partir do arquivo anexado.";
    } elseif (!empty($ia_resultado['erro'])) {
        $msg .= " (Não foi possível pré-preencher o currículo automaticamente: {$ia_resultado['erro']})";
    }
    echo json_encode(["status" => true, "msg" => $msg]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("candidato_manual_inc_aj.php | erro: " . $e->getMessage());
    echo json_encode(["status" => false, "msg" => "Falha ao adicionar candidato."]);
}
exit;

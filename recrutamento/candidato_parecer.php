<?php
//
//- candidato_parecer.php | R&S | Parecer de Screening (tela cheia, envio ao gestor)
//- (C)haia, 28/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
  exit();
}

include "../app/includes/conexao_gerar.php";

$candidatura_id = filter_input(INPUT_GET, 'candidatura_id', FILTER_VALIDATE_INT);
if (!$candidatura_id) {
    header("location: fluxo.php");
    exit();
}

//- Se a vaga não tem gestor_nome/gestor_email definidos explicitamente, usa como
//- sugestão o solicitante da vaga (rs_vagas.solicitante_id = idPessoa -> rh_pessoas,
//- com o login em rh_usuarios buscado a partir do idPessoa) - na prática, quem pediu
//- a vaga costuma ser o próprio gestor. O e-mail vem do login corporativo
//- (login + @gerar.org.br), não de rh_pessoas.email (que pode ser pessoal) - mesma
//- lição aprendida no agendamento do Google Calendar.
$sql = "SELECT CA.id, CA.vaga_id, CA.screening_data, CA.screening_meet_link,
               P.nome, P.telefone, P.dtNascimento,
               V.gestor_nome, V.gestor_email, V.identificador,
               C.nome AS cargo_ds,
               G.descricao AS escolaridade,
               SP.nome AS solicitante_nome,
               SU.login AS solicitante_login
        FROM rs_vagas_candidaturas CA
        INNER JOIN rh_pessoas P ON P.idPessoa = CA.pessoa_id
        INNER JOIN rs_vagas V ON V.id = CA.vaga_id
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        LEFT JOIN rh_graus_instrucao G ON G.id = P.idGrauEscola
        LEFT JOIN rh_pessoas SP ON SP.idPessoa = V.solicitante_id
        LEFT JOIN rh_usuarios SU ON SU.idPessoa = SP.idPessoa
        WHERE CA.id = :candidatura_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();
$ctx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ctx) {
    header("location: fluxo.php");
    exit();
}

//- Só sugere o solicitante quando a vaga ainda não tem gestor próprio definido -
//- uma vez que o campo é preenchido (por essa tela ou por vaga_edit.php), prevalece.
$gestor_sugerido = empty($ctx['gestor_nome']) && empty($ctx['gestor_email']);
if (empty($ctx['gestor_nome']) && !empty($ctx['solicitante_nome'])) {
    $ctx['gestor_nome'] = $ctx['solicitante_nome'];
}
if (empty($ctx['gestor_email']) && !empty($ctx['solicitante_login'])) {
    $ctx['gestor_email'] = $ctx['solicitante_login'] . '@gerar.org.br';
}

$stmt = $conn->prepare("SELECT * FROM rs_candidatos_parecer WHERE candidatura_id = :candidatura_id");
$stmt->bindValue(':candidatura_id', $candidatura_id, PDO::PARAM_INT);
$stmt->execute();
$parecer = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$titulo_vaga = $ctx['identificador'] ?: ($ctx['cargo_ds'] ?? 'Vaga');

function pv(array $p, string $campo): string
{
    return htmlspecialchars((string) ($p[$campo] ?? ''));
}

$titulo_pagina = "PARECER DE SCREENING";
include 'inc/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<style>
  .icon-box {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }

  .parecer-secao {
    margin-bottom: 1.75rem;
  }

  .parecer-secao-titulo {
    color: #0dcaf0;
    font-weight: 700;
    font-size: .95rem;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-bottom: 1px solid #333;
    padding-bottom: .5rem;
    margin-bottom: 1.25rem;
  }

  .note-editor.note-frame {
    background-color: #2c3034;
    border-color: #495057;
  }

  .note-editing-area .note-editable {
    background-color: #2c3034 !important;
    color: #e9ecef !important;
  }

  .note-toolbar {
    background-color: #212529 !important;
    border-bottom: 1px solid #495057 !important;
  }
</style>

<main class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
        <i class="fa-solid fa-file-signature fa-lg"></i>
      </div>
      <div>
        <h4 class="mb-0 text-white fw-bold">Parecer de Screening</h4>
        <p class="text-white-50 small mb-0"><?= htmlspecialchars($ctx['nome']) ?> — <?= htmlspecialchars($titulo_vaga) ?></p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <?php if (!empty($ctx['screening_meet_link'])): ?>
        <button type="button" class="btn btn-outline-warning btn-sm d-flex align-items-center gap-2" onclick="abrirJanelaMeet('<?= htmlspecialchars($ctx['screening_meet_link'], ENT_QUOTES) ?>')">
          <i class="fa-solid fa-video"></i> Entrar na Reunião de Screening
        </button>
      <?php endif; ?>
      <a href="vaga_candidatos.php?id=<?= (int) $ctx['vaga_id'] ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Funil
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-4">
      <div id="msgAlertaParecer"></div>

      <input type="hidden" id="parecer_candidatura_id" value="<?= (int) $candidatura_id ?>">

      <div class="parecer-secao">
        <div class="parecer-secao-titulo">Dados do Candidato</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Data de Nascimento</label>
            <input type="date" id="parecer_data_nascimento" class="form-control" value="<?= pv($parecer, 'data_nascimento') ?: htmlspecialchars($ctx['dtNascimento'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Endereço</label>
            <input type="text" id="parecer_endereco" class="form-control" value="<?= pv($parecer, 'endereco') ?>">
          </div>
        </div>
      </div>

      <div class="parecer-secao">
        <div class="parecer-secao-titulo">Gestor da Vaga</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Nome do Gestor</label>
            <input type="text" id="parecer_gestor_nome" class="form-control" value="<?= htmlspecialchars($ctx['gestor_nome'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">E-mail do Gestor *</label>
            <input type="email" id="parecer_gestor_email" class="form-control" value="<?= htmlspecialchars($ctx['gestor_email'] ?? '') ?>" placeholder="Informe aqui se a vaga ainda não tiver um definido">
            <small class="text-white-50">
              <?php if ($gestor_sugerido && !empty($ctx['gestor_email'])): ?>
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Sugestão a partir de quem solicitou a vaga - confira antes de enviar.
              <?php else: ?>
                Salvo aqui, fica reaproveitado para os próximos pareceres desta vaga.
              <?php endif; ?>
            </small>
          </div>
        </div>
      </div>

      <div class="parecer-secao">
        <div class="parecer-secao-titulo">Informações do Formulário</div>

        <div class="mb-3">
          <label class="form-label text-white-50 small fw-bold">O que chamou atenção na vaga?</label>
          <textarea id="parecer_chamou_atencao" class="summernote-edit"><?= $parecer['chamou_atencao'] ?? '' ?></textarea>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label text-white-50 small fw-bold">Tem CNH B?</label>
            <select id="parecer_tem_cnh" class="form-select">
              <option value="">Selecione...</option>
              <option value="Sim" <?= ($parecer['tem_cnh'] ?? '') === 'Sim' ? 'selected' : '' ?>>Sim</option>
              <option value="Não" <?= ($parecer['tem_cnh'] ?? '') === 'Não' ? 'selected' : '' ?>>Não</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label text-white-50 small fw-bold">Nível no pacote Office</label>
            <select id="parecer_nivel_office" class="form-select">
              <option value="">Selecione...</option>
              <?php foreach (['Básico', 'Intermediário', 'Avançado'] as $n): ?>
                <option value="<?= $n ?>" <?= ($parecer['nivel_office'] ?? '') === $n ? 'selected' : '' ?>><?= $n ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label text-white-50 small fw-bold">Pretensão Salarial (R$)</label>
            <input type="number" step="0.01" id="parecer_pretensao_salarial" class="form-control" value="<?= pv($parecer, 'pretensao_salarial') ?>">
          </div>
        </div>

        <div class="mb-0">
          <label class="form-label text-white-50 small fw-bold">Tem experiência para a vaga? Quais?</label>
          <textarea id="parecer_tem_experiencia" class="summernote-edit"><?= $parecer['tem_experiencia'] ?? '' ?></textarea>
        </div>
      </div>

      <div class="parecer-secao">
        <div class="parecer-secao-titulo">Análise da Entrevista</div>
        <textarea id="parecer_analise_entrevista" class="summernote-edit-grande"><?= $parecer['analise_entrevista'] ?? '' ?></textarea>
      </div>

      <div class="parecer-secao">
        <div class="parecer-secao-titulo">Conclusão</div>
        <select id="parecer_conclusao" class="form-select" style="max-width:400px;">
          <option value="">Selecione...</option>
          <option value="Apto" <?= ($parecer['conclusao'] ?? '') === 'Apto' ? 'selected' : '' ?>>Apto - segue para a próxima etapa</option>
          <option value="Não apto" <?= ($parecer['conclusao'] ?? '') === 'Não apto' ? 'selected' : '' ?>>Não apto</option>
        </select>
        <small class="text-white-50 d-block mt-2">Marcar "Não apto" move o candidato automaticamente para Rejeitado no funil.</small>
      </div>

      <?php if (!empty($parecer['enviado_em'])): ?>
        <div class="alert alert-success small">
          <i class="fa-solid fa-circle-check me-1"></i>Enviado ao gestor (<?= htmlspecialchars($parecer['enviado_para']) ?>)
          em <?= date('d/m/Y \à\s H:i', strtotime($parecer['enviado_em'])) ?>
        </div>
      <?php endif; ?>

      <div class="d-flex gap-2 justify-content-end pt-3 border-top border-secondary">
        <button type="button" class="btn btn-outline-light" onclick="salvarParecer(false)">
          <i class="fa-solid fa-floppy-disk me-2"></i>Salvar Rascunho
        </button>
        <button type="button" class="btn btn-warning" onclick="salvarParecer(true)">
          <i class="fa-solid fa-paper-plane me-2"></i>Salvar e Enviar ao Gestor
        </button>
      </div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>var VAGA_ID_PARECER = <?= (int) $ctx['vaga_id'] ?>;</script>
<script src="js/candidato_parecer.js"></script>
<?php include 'inc/footer.php'; ?>

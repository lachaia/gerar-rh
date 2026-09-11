<?php
// ponto_auditoria.php
/*
Como funciona / o que procurar
Linhas em amarelo (suspect): remoções ou operações que merecem atenção.
Linhas em vermelho (alterado): indicam que os valores novos logados no evento de UPDATE não correspondem ao hash do registro atual — isso aponta que depois do UPDATE houve uma nova alteração (possivelmente manual) não coberta pela auditoria (ou a chave secreta local está diferente).
Botão “Ver” abre modal com JSON antigo × novo para inspeção.
A coluna “Quem / IP / data” vem da própria tabela de auditoria.
*/
session_start();
if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php'); exit;
}

header('Content-Type: text/html; charset=utf-8');
include dirname(__DIR__) . '/../includes/conexao_gerar.php';

// ************ CONFIG ************
$CHAVE_SECRETA = 'CHAVE-SECRETA-INTERNA'; // <<< ALTERE para a chave real usada nos triggers
$porPagina = 25;
// *******************************

// filtros
$colab = $_GET['colab'] ?? '';
$acao  = $_GET['acao'] ?? '';
$data_ini = $_GET['data_ini'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

// paginação
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $porPagina;

// lista de colaboradores para o select
$colabsStmt = $conn->query("SELECT idColab, rh_pessoas.nome as nmColab
    FROM rh_colaboradores 
    INNER JOIN rh_pessoas ON rh_pessoas.idPessoa = rh_colaboradores.idPessoa
    ORDER BY rh_pessoas.nome");
$colabs = $colabsStmt->fetchAll(PDO::FETCH_ASSOC);

// constrói where
$where = "WHERE 1=1";
$params = [];
if ($colab !== '') {
    $where .= " AND JSON_EXTRACT(valores_novos, '$.colaborador_id') = :colab";
    $params[':colab'] = $colab;
}
if ($acao !== '') {
    $where .= " AND acao = :acao";
    $params[':acao'] = $acao;
}
if ($data_ini) {
    $where .= " AND data_hora >= :dini";
    $params[':dini'] = $data_ini . " 00:00:00";
}
if ($data_fim) {
    $where .= " AND data_hora <= :dfim";
    $params[':dfim'] = $data_fim . " 23:59:59";
}

// total
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM rh_ponto_auditoria $where");
$stmtTotal->execute($params);
$total = $stmtTotal->fetchColumn();
$totalPages = ceil($total / $porPagina);

// busca registros
$sql = "SELECT * 
            FROM rh_ponto_auditoria $where ORDER BY data_hora DESC LIMIT :lim OFFSET :off";
$stmt = $conn->prepare($sql);
foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', (int)$porPagina, PDO::PARAM_INT);
$stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// helper: formata json bonito
function pretty_json($json) {
    if (empty($json)) return '';
    $decoded = json_decode($json, true);
    if ($decoded === null) return htmlspecialchars($json);
    return '<pre style="text-align:left;font-size:13px;">' . htmlspecialchars(json_encode($decoded, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) . '</pre>';
}

// helper: recalcula hash para um registro atual em rh_ponto_registros
function recalcula_hash_registro($conn, $id_ponto, $chave) {
    $s = $conn->prepare("SELECT * FROM rh_ponto_registros WHERE id = :id LIMIT 1");
    $s->execute([':id'=>$id_ponto]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    if (!$r) return null;
    // atenção: garanta a ordem dos campos igual ao trigger
    $concat = 
        ($r['colaborador_id'] ?? '') .
        ($r['data_hora'] ?? '') .
        ($r['ip'] ?? '') .
        ($r['lat'] ?? '') .
        ($r['lon'] ?? '') .
        ($r['endereco_id'] ?? '') .
        ($r['endereco_texto'] ?? '') .
        ($r['ticket'] ?? '') .
        ($r['tipoBatida'] ?? '') .
        $chave;
    return hash('sha256', $concat);
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Auditoria - Ponto</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .suspect { background: #fff3cd; } /* amarelo */
    .alterado { background: #f8d7da; } /* vermelho */
    pre { margin:0; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, "Roboto Mono", monospace; font-size:13px; }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <h3>Auditoria — Batidas (rh_ponto_registros)</h3>

    <!-- filtros -->
    <form class="row g-2 align-items-center mb-3" method="get">
      <div class="col-auto">
        <label class="form-label small">Colaborador</label>
        <select name="colab" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach($colabs as $c): ?>
            <option value="<?= $c['idColab'] ?>" <?= ($colab == $c['idColab'])?'selected':'' ?>>
              <?= htmlspecialchars($c['nmColab']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-auto">
        <label class="form-label small">Ação</label>
        <select name="acao" class="form-select form-select-sm">
          <option value="">Todas</option>
          <option value="INSERT" <?= $acao=='INSERT'?'selected':'' ?>>INSERT</option>
          <option value="UPDATE" <?= $acao=='UPDATE'?'selected':'' ?>>UPDATE</option>
          <option value="DELETE" <?= $acao=='DELETE'?'selected':'' ?>>DELETE</option>
        </select>
      </div>

      <div class="col-auto">
        <label class="form-label small">De</label>
        <input type="date" name="data_ini" value="<?= htmlspecialchars($data_ini) ?>" class="form-control form-control-sm">
      </div>

      <div class="col-auto">
        <label class="form-label small">Até</label>
        <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" class="form-control form-control-sm">
      </div>

      <div class="col-auto align-self-end">
        <button class="btn btn-primary btn-sm">Filtrar</button>
        <a href="ponto_auditoria.php" class="btn btn-outline-secondary btn-sm">Limpar</a>
      </div>
    </form>

    <!-- resultados -->
    <div class="card">
      <div class="card-body p-2">
        <div class="table-responsive" style="max-height:60vh;overflow:auto;">
          <table class="table table-sm mb-0 align-middle">
            <thead class="table-light sticky-top">
              <tr>
                <th>#</th>
                <th>Data / Hora</th>
                <th>Ação</th>
                <th>Id Ponto</th>
                <th>Quem</th>
                <th>IP</th>
                <th>Resumo</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
<?php
$idx = $offset + 1;
foreach ($rows as $r):
    // decide classe: tenta detectar se alteração foi posteriormente adulterada
    $classe = '';
    $badge = '';
    // se UPDATE ou DELETE — podemos comparar o registro atual com valores_antigos/novos
    if (in_array($r['acao'], ['UPDATE','DELETE'])) {
        // recomputa hash do registro atual (se existir)
        $hashAtual = recalcula_hash_registro($conn, $r['id_ponto'], $CHAVE_SECRETA);
        // se não existe mais o ponto (DELETE), marcamos
        if ($r['acao'] === 'DELETE') {
            $classe = 'suspect';
            $badge = '<span class="badge bg-warning text-dark">REMOVIDO</span>';
        } else {
            // para UPDATE: pega valores_novos->data_hora etc
            $valn = json_decode($r['valores_novos'] ?? 'null', true);
            $expectedHash = null;
            if ($valn) {
                $concat = 
                    ($valn['colaborador_id'] ?? '') .
                    ($valn['data_hora'] ?? '') .
                    ($valn['ip'] ?? '') .
                    ($valn['lat'] ?? '') .
                    ($valn['lon'] ?? '') .
                    ($valn['endereco_id'] ?? '') .
                    ($valn['endereco_texto'] ?? '') .
                    ($valn['ticket'] ?? '') .
                    ($valn['tipoBatida'] ?? '') .
                    $CHAVE_SECRETA;
                $expectedHash = hash('sha256', $concat);
            }
            // compara com hash atual: se diferente -> possivelmente adulterado depois da auditoria
            if ($hashAtual !== null && $expectedHash !== null && $hashAtual !== $expectedHash) {
                $classe = 'alterado';
                $badge = '<span class="badge bg-danger">HASH DIVERGENTE</span>';
            }
        }
    } elseif ($r['acao'] === 'INSERT') {
        $badge = '<span class="badge bg-success">INSERT</span>';
    }
?>
              <tr class="<?= $classe ?>">
                <td class="mono"><?= $idx++ ?></td>
                <td style="min-width:160px;"><?= htmlspecialchars($r['data_hora']) ?></td>
                <td><?= htmlspecialchars($r['acao']) ?></td>
                <td><?= htmlspecialchars($r['id_ponto']) ?></td>
                <td><?= htmlspecialchars($r['alterado_por']) ?></td>
                <td><?= htmlspecialchars($r['ip_origem']) ?></td>
                <td>
                  <?= $badge ?>
                  <?php
                    // resumo: mostra mudança de data_hora ou tipoBatida
                    $ant = json_decode($r['valores_antigos'] ?? 'null', true);
                    $nov = json_decode($r['valores_novos'] ?? 'null', true);
                    $resumes = [];
                    if ($ant && $nov) {
                        foreach (['data_hora','tipoBatida','ip','lat','lon'] as $k) {
                            if (($ant[$k] ?? null) != ($nov[$k] ?? null)) {
                                $resumes[] = "$k";
                            }
                        }
                    }
                    echo !empty($resumes) ? '<small class="text-muted">'.implode(', ',$resumes).'</small>' : '';
                  ?>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detModal"
                    data-ant='<?= htmlspecialchars($r['valores_antigos'] ?? '') ?>'
                    data-nov='<?= htmlspecialchars($r['valores_novos'] ?? '') ?>'
                    data-info='<?= htmlspecialchars(json_encode([
                        'id_log'=>$r['id_log'],
                        'acao'=>$r['acao'],
                        'id_ponto'=>$r['id_ponto'],
                        'alterado_por'=>$r['alterado_por'],
                        'ip'=>$r['ip_origem'],
                        'data_hora'=>$r['data_hora'],
                    ], JSON_UNESCAPED_UNICODE))  ?>'
                  >
                    Ver
                  </button>
                </td>
              </tr>
<?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- paginação simples -->
        <div class="d-flex justify-content-between align-items-center mt-2">
          <small class="text-muted">Exibindo <?= count($rows) ?> de <?= $total ?> registros</small>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <?php for($p=1;$p<=$totalPages;$p++): ?>
                <li class="page-item <?= $p==$page ? 'active' : '' ?>">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"><?= $p ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        </div>

      </div>
    </div>
  </div>

  <!-- Modal de detalhes -->
  <div class="modal fade" id="detModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalhes da Auditoria</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6>Valores antigos</h6>
              <div id="valAnt" class="mono p-2 bg-light border rounded"></div>
            </div>
            <div class="col-md-6">
              <h6>Valores novos</h6>
              <div id="valNov" class="mono p-2 bg-light border rounded"></div>
            </div>
          </div>

          <hr>
          <div id="metaInfo" class="small text-muted"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
var detModal = document.getElementById('detModal');
detModal.addEventListener('show.bs.modal', function (event) {
  var btn = event.relatedTarget;
  var ant = btn.getAttribute('data-ant') || '';
  var nov = btn.getAttribute('data-nov') || '';
  var info = btn.getAttribute('data-info') || '{}';
  try {
    var objAnt = JSON.parse(ant || 'null');
    var objNov = JSON.parse(nov || 'null');
  } catch(e) {
    var objAnt = null;
    var objNov = null;
  }
  document.getElementById('valAnt').innerHTML = objAnt ? '<pre>'+JSON.stringify(objAnt, null, 2)+'</pre>' : '<em>— vazio —</em>';
  document.getElementById('valNov').innerHTML = objNov ? '<pre>'+JSON.stringify(objNov, null, 2)+'</pre>' : '<em>— vazio —</em>';
  var meta = JSON.parse(info);
  document.getElementById('metaInfo').innerHTML = 'Log #'+meta.id_log+' — Ação: '+meta.acao+' — Id: '+meta.id_ponto+' — Por: '+meta.alterado_por+' — '+meta.data_hora;
});
</script>
</body>
</html>

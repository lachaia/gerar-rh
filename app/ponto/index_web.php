<?php
//
// index_web_desktop.php | Versão Desktop em Bootstrap 5
// (C)haia - conversão por ChatGPT
//
session_start();

// Ajusta fuso horário
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

$modulo = 22;

include "../includes/conexao_gerar.php";
include "api/inc_verifica_data.php";

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    $idColab = $_SESSION['idColab'];
    $hoje = date('Y-m-d');
}

// estado inicial do botão
if (isset($_POST['ponto'])) $batido = "sim";
else $batido = "não";
$batido = "não";

// --- Busca horário do colaborador ---
$sql = "SELECT 
            C.horario_ini, 
            C.horario_fim, 
            S.cidade AS cidade_id, CD.nome as cidade_ds,
            S.estado AS colaborador_uf,
            P.nome, U.foto
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
        LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
        LEFT JOIN rh_usuarios U ON U.idColab = C.idColab
        LEFT JOIN rh_cidades CD ON CD.idCidade = S.cidade
        WHERE C.idColab = :idColab
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':idColab' => $idColab]);
$linha = $stmt->fetch(PDO::FETCH_ASSOC);

$horario_ini = $linha['horario_ini'] ?? '08:20';
$horario_fim = $linha['horario_fim'] ?? '18:05';
$cidade_id = $linha['cidade_id'] ?? 3281;
$cidade_ds = $linha['cidade_ds'] ?? 'N/D';
$colaborador_uf = $linha['colaborador_uf'] ?? 'PR';
$nomeColab = $linha['nome'] ?? ($_SESSION['nmLogin'] ?? 'Colaborador');
$foto = $linha['foto'] ?? '';

if (empty($foto) || $foto == 'perfil.png') {
    $url_foto = "https://ui-avatars.com/api/?name=" . urlencode($nomeColab) . "&background=0D6EFD&color=fff&size=256";
} else {
    $url_foto = "../fotos/$foto";
}

// registra na sessão
$_SESSION['horario_ini'] = $horario_ini;
$_SESSION['horario_fim'] = $horario_fim;
$_SESSION['cidade_id'] = $cidade_id;
$_SESSION['colaborador_uf'] = $colaborador_uf;

// verifica tipo de dia
$tipoHoje = verifica_data($hoje, $cidade_id, $colaborador_uf, $conn);
$dsTipoDia = $_SESSION['dsTipoDia'] ?? 'UTIL';

// Busca batidas do dia
$stmt = $conn->prepare("
    SELECT data_hora
    FROM rh_ponto_registros
    WHERE colaborador_id = :id
      AND data_hora BETWEEN CURDATE() AND (CURDATE() + INTERVAL 1 DAY)
    ORDER BY data_hora
");
$stmt->execute([':id' => $idColab]);
$batidas = $stmt->fetchAll(PDO::FETCH_COLUMN);

$totalTrabalhado = tempoTotalTrabalhado($batidas);

// Jornada total do colaborador (em segundos), já descontando 1h de almoço
if ($tipoHoje == 'FERIADO' || $tipoHoje == 'FACULTATIVO') {
    $jornada = 0;
    $dsJornada = $tipoHoje;
} else {
    $jornada = (strtotime($horario_fim) - strtotime($horario_ini)) - 3600;
    $dsJornada = gmdate('H:i', max(0, $jornada));
}

// cálculo faltam com proteções
if ($jornada <= 0) {
    $faltam = 0;
} else {
    $faltam = $jornada - $totalTrabalhado;
    if ($faltam < 0) $faltam = 0;
    if ($faltam > 12 * 3600) $faltam = 0;
}

$horas = floor($faltam / 3600);
$minutos = floor(($faltam % 3600) / 60);

/* busca foto do usuário (se tiver)
$stmt = $conn->prepare("SELECT U.foto FROM rh_usuarios U WHERE U.idColab = :idColab LIMIT 1");
$stmt->execute([':idColab' => $idColab]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
$foto = $u['foto'] ?? null;
if (empty($foto) || $foto == 'perfil.png') {
    $url_foto = "https://ui-avatars.com/api/?name=" . urlencode($nomeColab) . "&background=0D6EFD&color=fff&size=256";
} else {
    $url_foto = "../fotos/$foto";
}
*/
//
// busca Endereço Padrão de Trabalho do colaborador
//
$sql = "SELECT 
                    E.logradouro AS enderecoTrab,
                    E.numero AS numeroTrab,
                    E.bairro AS bairroTrab,
                    E.cidade AS cidadeTrab,
                    E.uf AS estadoTrab,
                    S.endereco AS enderecoSub,
                    S.numero AS numeroSub,
                    S.bairro AS bairroSub,
                    rh_cidades.nome AS cidadeSub,
                    S.estado AS estadoSub
                FROM rh_colaboradores C 
                LEFT JOIN rh_enderecos E ON E.idEndereco = C.idEnderecoTrab
                LEFT JOIN rh_subsedes S ON S.idSubSede = C.idSubSede
                LEFT JOIN rh_cidades ON rh_cidades.idCidade = S.cidade
                WHERE C.idColab = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idColab]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Define o endereço de trabalho de referência
    if (!empty($row['enderecoTrab'])) {
        $enderecoReferencia = "{$row['enderecoTrab']}, {$row['numeroTrab']} - {$row['bairroTrab']} - {$row['cidadeTrab']}/{$row['estadoTrab']}";
    } else {
        $enderecoReferencia = "{$row['enderecoSub']}, {$row['numeroSub']} - {$row['bairroSub']} - {$row['cidadeSub']}/{$row['estadoSub']}";
    }
} else {
    $enderecoReferencia = null;
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Ponto Eletrônico — Web</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background: #f4f6f9;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }

        .card-quiet {
            box-shadow: 0 6px 18px rgba(30, 41, 59, 0.06);
            border: none;
        }

        .big-clock {
            font-weight: 700;
            letter-spacing: -1px;
        }

        .small-clock {
            opacity: .8;
        }

        .avatar-sm {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 50%;
        }

        .btn-primary-strong {
            background: #0d6efd;
            border-color: #0d6efd;
        }

        .address-box {
            min-height: 72px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="../imagens/logo.png" alt="Logo" style="height:36px">
                <span class="fw-bold text-dark">Gerar RH</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <!-- nome do colaborador (opcional) -->
                <div class="me-3 text-end d-none d-md-block">
                    <div class="small text-muted">Olá,</div>
                    <div class="fw-semibold"><?= htmlspecialchars($nomeColab) ?></div>
                </div>

                <!-- avatar com dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $url_foto ?>" alt="avatar" class="avatar-sm border">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalResetSenha">
                                <i class="fa-solid fa-key me-2"></i> Resetar senha
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- MENSAGENS -->
    <div class="text-center m-2 h4" id='mensagem'></div>

    <!-- CONTENT -->
    <div class="container-fluid mt-4">
        <div class="row g-4">
            <!-- left: painel principal (clock, jornada, registro) -->
            <div class="col-lg-6 col-xl-6">
                <div class="card card-quiet p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">Meu Ponto</h5>
                            <small class="text-muted"><?= $dsTipoDia ?></small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Horário</small>
                            <div class="fw-bold"><?= htmlspecialchars($horario_ini) ?> — <?= htmlspecialchars($horario_fim) ?></div>
                        </div>
                    </div>

                    <hr>

                    <!-- Relógio -->
                    <div class="text-center my-3">
                        <div class="big-clock display-4" id="hora">--:--</div>
                        <div class="small-clock text-muted" id="segundos">--</div>
                        <div class="mt-2" id="dia-semana">...</div>
                        <div class="text-muted" id="data-completa">...</div>
                        <div class="mt-3">
                            <span class="badge bg-secondary"><?= htmlspecialchars($dsTipoDia) ?></span>
                        </div>
                    </div>

                    <!-- Jornada / contador -->
                    <div class="mt-3 p-3 bg-light rounded address-box">
                        <h6 class="mb-2">Jornada restante</h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="h4 mb-0" id="contador"><?= sprintf('%02d HORAS : %02d MINUTOS', $horas, $minutos) ?></div>
                                <small class="text-muted">Total trabalhado: <span id="totalTrabalhadoText"><?= gmdate('H:i', max(0, $totalTrabalhado)) ?></span></small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Previsto</small>
                                <div class="fw-semibold"><?= $dsJornada ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Botão Registrar -->
                    <div class="mt-4 d-grid">
                        <button id="btn-ponto" class="btn btn-primary btn-lg btn-primary-strong" onclick="registrarPonto()">
                            <i class="fa-solid fa-fw fa-clock-rotate-left me-2"></i> Registrar Ponto
                        </button>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted">Última ação:</small>
                        <div id="status-ponto" class="fw-semibold text-success">Nenhum registro ainda.</div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <a href="api/equipe_web.php" class="btn btn-outline-secondary flex-fill"><i class="fa-solid fa-users me-1"></i> Equipe</a>
                        <button class="btn btn-outline-secondary flex-fill" onclick="meu_rh()"><i class="fa-solid fa-clock me-1"></i> Meu RH</button>
                    </div>
                </div>
            </div>

            <!-- right: endereço + últimas batidas -->
            <div class="col-lg-6 col-xl-6">
                <div class="row g-4">
                    <!-- endereço atual -->
                    <div class="col-12">
                        <div class="card card-quiet p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Endereço atual</h5>
                                    <small class="text-muted">Leitura automática via GPS (pode editar manualmente)</small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="pegarEndereco();">
                                        <i class="fa-solid fa-location-crosshairs me-1"></i> Atualizar
                                    </button>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEndereco">
                                        <i class="fa-solid fa-pen me-1"></i> Editar
                                    </button>
                                </div>
                            </div>

                            <hr>

                            <div id="enderecoBox" class="mb-2">
                                <div class="h6" id="enderecoTexto">Detectando localização...</div>
                            </div>

                            <div class='row'>
                                <div class="col-4">
                                    <small class="text-muted">Cidade/UF Padrão:</small>
                                    <div class="fw-semibold"><?= htmlspecialchars($cidade_ds) ?> - <?= htmlspecialchars($colaborador_uf) ?></div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Coordenadas:</small>
                                    <div class="text-muted small" id="enderecoCoords"></div>
                                </div>
                                <div class="col-4">
                                    <button
                                        onclick="usar_endereco_padrao()"
                                        type='button'
                                        class="btn btn-outline-primary text-muted small w-100">
                                        <i class="fa-solid fa-location-dot me-1"></i> Usar Endereço de<br>trabalho Padrão
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- histórico resumido / indicadores -->
                    <div class="col-12">
                        <div class="card card-quiet p-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="small text-muted">Horas trabalhadas (hoje)</div>
                                    <div class="fw-bold"><?= gmdate('H:i', max(0, $totalTrabalhado)) ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Jornada prevista</div>
                                    <div class="fw-bold"><?= $dsJornada ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Saldo faltante</div>
                                    <div class="fw-bold"><?= sprintf('%02d:%02d', $horas, $minutos) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- últimas batidas -->
                    <div class="col-12">
                        <div class="card card-quiet p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Últimos registros</h6>
                                <small class="text-muted">Hoje</small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Hora</th>
                                            <th></th>
                                            <th>Tipo</th>
                                            <th>Local</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblUltimas" class='small'>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Carregando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">Observação: o horário exibido é a hora do registro.</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal: editar endereço -->
    <div class="modal fade" id="modalEndereco" tabindex="-1" aria-labelledby="modalEnderecoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEndereco" class="modal-content" onsubmit="event.preventDefault(); salvarEnderecoManual();">
                <input type="hidden" id='endereco_referencia' value='<?= $enderecoReferencia ?>'>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnderecoLabel">Editar endereço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" id="enderecoManual" class="form-control" placeholder="Rua, número, bairro, cidade">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Coordenadas (lat,lon)</label>
                        <input type="text" id="coordsManual" class="form-control" placeholder="-25.4,-49.3">
                    </div>
                    <div class="form-text">Você pode editar o endereço caso o GPS esteja incorreto. Salvará apenas para o registro deste batimento.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: RESETAR SENHA -->
    <div class="modal fade" id="modalResetSenha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-key me-2"></i> Redefinir Senha
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label fw-bold mt-2">Nova Senha</label>
                    <div class="input-group mb-3">
                        <input type="password" id="novaSenha" class="form-control" placeholder="Digite a nova senha">
                        <span class="input-group-text" role="button" onclick="toggleSenha('novaSenha', this)">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>

                    <label class="form-label fw-bold">Confirmar Senha</label>
                    <div class="input-group mb-3">
                        <input type="password" id="confirmaSenha" class="form-control" placeholder="Confirme a senha">
                        <span class="input-group-text" role="button" onclick="toggleSenha('confirmaSenha', this)">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>

                    <div id="resetFeedback" class="alert d-none"></div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" onclick="confirmarResetSenha()">Salvar Nova Senha</button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Formulário invisível para envio -->
    <form id="formConfirma" method="POST" action="api/confirma.php" class="d-none">
        <input type="hidden" name="idColab" value="<?= htmlspecialchars($_SESSION['idColab']) ?>">
        <input type="hidden" name="hora" id="horaHidden">
        <input type="hidden" name="lat" id="latHidden">
        <input type="hidden" name="lon" id="lonHidden">
        <input type="hidden" name="endereco" id="enderecoHidden">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Variáveis iniciais vindas do PHP
        const idColab = <?= json_encode($idColab) ?>;
        let faltam = <?= (int)$faltam ?> * 1000;
        let totalTrabalhado = <?= (int)$totalTrabalhado ?>; // em segundos

        // Atualiza o relógio
        function atualizarRelogio() {
            const agora = new Date();
            const hora = agora.toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            const segundos = agora.getSeconds().toString().padStart(2, '0');
            document.getElementById('hora').textContent = hora;
            document.getElementById('segundos').textContent = segundos;
            document.getElementById('dia-semana').textContent = agora.toLocaleDateString('pt-BR', {
                weekday: 'long'
            });
            document.getElementById('data-completa').textContent = agora.toLocaleDateString('pt-BR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        setInterval(atualizarRelogio, 1000);
        atualizarRelogio();

        // Contador da jornada (se houver trabalho)
        function atualizarContador() {
            if (faltam <= 0) {
                document.getElementById('contador').textContent = "Jornada concluída!";
                return;
            }
            faltam -= 1000;
            const horas = Math.floor(faltam / (1000 * 60 * 60));
            const minutos = Math.floor((faltam % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((faltam % (1000 * 60)) / 1000);
            document.getElementById('contador').textContent =
                `${String(horas).padStart(2,'0')}h ${String(minutos).padStart(2,'0')}m:${String(segundos).padStart(2,'0')}s`;
        }

        if (totalTrabalhado > 0) {
            setInterval(atualizarContador, 1000);
            atualizarContador();
        }

        // FUNÇÕES AJAX
        function ultimos_registros() {
            $.post("api/ultimas_batidas_web.php", {
                idColab: idColab
            }, function(data) {
                // espera HTML com <tr> contendo as linhas ou um texto; adaptável
                const tbody = document.getElementById('tblUltimas');
                tbody.innerHTML = data;

                // também atualiza o status resumido
                const status = document.getElementById('status-ponto');
                // tenta extrair texto de primeiro TR
                const firstRow = tbody.querySelector('tr');
                if (firstRow) status.textContent = firstRow.innerText.trim();
            }).fail(function() {
                document.getElementById('tblUltimas').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Erro ao carregar</td></tr>';
            });
        }

        //- QUANDO TUDO PRONTO?
        $(document).ready(function() {
            ultimos_registros();
        });

        // pegar endereço via geolocation + reverse geocoding (simples com Nominatim)
        function pegarEndereco() {
            const enderecoTexto = document.getElementById('enderecoTexto');
            const enderecoCoords = document.getElementById('enderecoCoords');

            enderecoTexto.textContent = 'Obtendo localização...';
            enderecoCoords.textContent = '';

            if (!('geolocation' in navigator)) {
                enderecoTexto.textContent = 'Geolocalização não suportada.';
                return;
            }

            navigator.geolocation.getCurrentPosition(async (pos) => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;

                enderecoCoords.textContent = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;

                try {
                    //const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}&accept-language=pt-BR`;
                    const url = `api/reverse.php?lat=${lat}&lon=${lon}`;
                    const res = await fetch(url);
                    if (!res.ok) throw new Error('Erro no reverse geocode');
                    const j = await res.json();

                    const a = j.address || {};

                    // monta o endereço formatado
                    const rua = a.road || "";
                    const num = a.house_number || "";
                    const bairro = a.suburb || a.neighbourhood || "";
                    const cidade = a.city || a.town || a.village || "";
                    const uf = a.state || "";
                    const pais = (a.country_code || "").toUpperCase() === "BR" ? "Brasil" : (a.country || "");

                    let enderecoFormatado = `${rua}`;
                    if (num) enderecoFormatado += `, ${num}`;
                    if (bairro) enderecoFormatado += `, ${bairro}`;
                    if (cidade) enderecoFormatado += `, ${cidade}`;
                    if (uf) enderecoFormatado += `, ${uf}`;
                    if (pais) enderecoFormatado += `, ${pais}`;

                    enderecoTexto.textContent = enderecoFormatado;

                    // Preenche campos ocultos / modal
                    document.getElementById('enderecoManual').value = enderecoFormatado;
                    document.getElementById('coordsManual').value = `${lat},${lon}`;

                    document.getElementById('enderecoHidden').value = enderecoFormatado;
                    document.getElementById('latHidden').value = lat;
                    document.getElementById('lonHidden').value = lon;

                } catch (err) {
                    enderecoTexto.textContent = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                }

            }, (err) => {
                enderecoTexto.textContent = 'Falha ao obter localização. Permissões?';
            }, {
                enableHighAccuracy: true,
                timeout: 10000
            });
        }

        // salva endereço manual do modal
        function salvarEnderecoManual() {
            const display = document.getElementById('enderecoManual').value.trim();
            const coords = document.getElementById('coordsManual').value.trim();
            document.getElementById('enderecoTexto').textContent = display || 'Não informado';
            document.getElementById('enderecoCoords').textContent = coords || '';
            document.getElementById('enderecoHidden').value = display;
            if (coords) {
                const parts = coords.split(',');
                if (parts.length >= 2) {
                    document.getElementById('latHidden').value = parts[0].trim();
                    document.getElementById('lonHidden').value = parts[1].trim();
                }
            }
            // fecha modal
            const modalEl = document.getElementById('modalEndereco');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
        }

        function usar_endereco_padrao() {
            const ref = document.getElementById('endereco_referencia').value.trim();

            if (!ref) {
                alert("Endereço padrão não cadastrado.");
                return;
            }

            // Exibe o endereço formatado na tela
            const enderecoTexto = document.getElementById('enderecoTexto');
            if (enderecoTexto) enderecoTexto.textContent = ref;

            // Preenche campo manual (se existir)
            const enderecoManual = document.getElementById('enderecoManual');
            if (enderecoManual) enderecoManual.value = ref;

            // Preenche hidden usado no envio
            const enderecoHidden = document.getElementById('enderecoHidden');
            if (enderecoHidden) enderecoHidden.value = ref;

            // Se usar coordenadas, zeramos (já que o endereço é fixo)
            const coordsManual = document.getElementById('coordsManual');
            if (coordsManual) coordsManual.value = "";

            const latHidden = document.getElementById('latHidden');
            const lonHidden = document.getElementById('lonHidden');

            //if (latHidden) latHidden.value = "";
            //if (lonHidden) lonHidden.value = "";

            // Feedback opcional
            console.log("Endereço padrão aplicado:", ref);
        }

        // Registrar Ponto 
        async function registrarPonto() {
            const btn = document.getElementById('btn-ponto');
            const mensagem = document.getElementById('mensagem');
            //
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Enviando ...';

            const form = document.getElementById('formConfirma');
            document.getElementById('horaHidden').value = new Date().toISOString();

            // prioriza valores já preenchidos no hidden (se o usuário editou manualmente)
            let lat = document.getElementById('latHidden').value;
            let lon = document.getElementById('lonHidden').value;
            let endereco = document.getElementById('enderecoHidden').value;

            $.post("api/registrar_ponto.php", {
                lat: lat,
                lon: lon,
                agora: document.getElementById('horaHidden').value,
                dsEndereco: endereco,
                colaborador_id: idColab,
                origem: 'web'
            }, function(retorno) {
                mensagem.innerHTML = retorno.mensagem;
                setTimeout(function() {
                    document.location.reload();
                }, 3000);
            }, 'JSON').fail(function() {
                alert('Erro ao comunicar com o servidor.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-fw fa-clock-rotate-left me-2"></i>' + ' Registrar Ponto';
            });
        }

        function meu_rh() {
            document.location.href = "../colaborador/index.php";
        }

        function confirmarResetSenha() {
            const senha = document.getElementById("novaSenha").value.trim();
            const conf = document.getElementById("confirmaSenha").value.trim();
            const feedback = document.getElementById("resetFeedback");

            // validações
            if (senha.length < 6) {
                feedback.className = "alert alert-warning";
                feedback.textContent = "A senha deve ter ao menos 6 caracteres.";
                feedback.classList.remove("d-none");
                return;
            }

            if (senha !== conf) {
                feedback.className = "alert alert-danger";
                feedback.textContent = "As senhas não coincidem.";
                feedback.classList.remove("d-none");
                return;
            }

            feedback.className = "alert alert-info";
            feedback.textContent = "Salvando nova senha...";
            feedback.classList.remove("d-none");

            $.post("api/reset_senha.php", {
                    novaSenha: senha
                },
                function(resp) {

                    if (resp.status === true) {
                        feedback.className = "alert alert-success";
                        feedback.textContent = resp.msg;

                        // opcional: fechar modal depois de 2s
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById("modalResetSenha"));
                            modal.hide();
                        }, 2000);
                    } else {
                        feedback.className = "alert alert-danger";
                        feedback.textContent = resp.msg;
                    }

                },
                "json"
            );
        }

        function toggleSenha(idCampo, spanIcone) {
            const campo = document.getElementById(idCampo);
            const icone = spanIcone.querySelector("i");

            if (campo.type === "password") {
                campo.type = "text";
                icone.classList.remove("fa-eye");
                icone.classList.add("fa-eye-slash");
            } else {
                campo.type = "password";
                icone.classList.remove("fa-eye-slash");
                icone.classList.add("fa-eye");
            }
        }

        // inicializa endereço e últimas batidas
        pegarEndereco();
        ultimos_registros();
    </script>

</body>

</html>

<?php
// funções auxiliares que você já tinha (mantidas)
function tempoTotalTrabalhado($batidas)
{
    $total = 0;
    $agora = time();
    $qtd = count($batidas);

    for ($i = 0; $i < $qtd; $i += 2) {
        $t1 = strtotime($batidas[$i]);
        if (isset($batidas[$i + 1])) {
            $t2 = strtotime($batidas[$i + 1]);
        } else {
            $t2 = $agora;
        }
        $total += ($t2 - $t1);
    }

    return $total; // em segundos
}

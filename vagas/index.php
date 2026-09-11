<?php
//
//- vagas_todas.php | RH | Portal Principal - TODAS AS VAGAS
// (C)haia, 2026-07-23
//

include "../app/includes/conexao_gerar.php";

//- Mesma regra de elegibilidade usada em vaga_perfil.php: aprovada, publicada,
//- ainda aberta e dentro da validade do anúncio. (status_id=2 é o portão de
//- aprovação em rs_vagas_status - não existe coluna "status" em rs_vagas.)
$sql = "SELECT V.*, C.nome AS cargo_ds
        FROM rs_vagas V
        LEFT JOIN rh_cargos C ON C.idCargo = V.cargo_id
        WHERE V.status_id = 2
          AND V.fechada_em IS NULL
          AND V.publicada_em IS NOT NULL
          AND (V.expira_em IS NULL OR V.expira_em >= CURDATE())
        ORDER BY V.publicada_em DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabalhe Conosco - Vagas Abertas</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .vaga-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .vaga-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        .badge-vaga {
            font-weight: 500;
            font-size: 0.8rem;
        }
        .logo-banner {
            max-height: 80px;
            max-width: 220px;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-body-tertiary d-flex flex-column min-vh-100">

    <!-- Header / Banner com Logo e Botão Voltar -->
    <header class="bg-primary bg-gradient text-white py-4 mb-4 shadow-sm">
        <div class="container">
            <div class="row align-items-center gy-3">
                <!-- Logo na lateral esquerda -->
                <div class="col-12 col-md-auto text-center text-md-start">
                    <img src="../app/imagens/logo.png" alt="Logo GERAR" class="img-fluid logo-banner">
                </div>
                
                <!-- Título e Subtítulo -->
                <div class="col-12 col-md text-center text-md-start border-start-md ps-md-4">
                    <h1 class="display-6 fw-bold mb-1">Oportunidades em Aberto</h1>
                    <p class="lead mb-0 text-white-50 fs-6">Faça parte da nossa equipe. Encontre a vaga ideal para o seu perfil.</p>
                </div>

                <!-- Botão Voltar (Lado Direito) -->
                <div class="col-12 col-md-auto text-center text-md-end">
                    <a href="../index.php" class="btn btn-light text-primary fw-semibold rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container mb-5 flex-grow-1">
        
        <?php if (empty($vagas)): ?>
            <div class="alert alert-info text-center py-4 rounded-3 shadow-sm" role="alert">
                <i class="fa-solid fa-briefcase fa-2x mb-3 d-block"></i>
                <h5 class="alert-heading">Nenhuma vaga aberta no momento</h5>
                <p class="mb-0">Acompanhe nossas redes sociais para futuras oportunidades!</p>
            </div>
        <?php else: ?>

            <!-- Barra de Pesquisa -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8 col-lg-6">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-body border-end-0 text-body-secondary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" 
                               id="inputPesquisa" 
                               class="form-control bg-body border-start-0 ps-0" 
                               placeholder="Pesquisar por cargo, setor, cidade ou área..." 
                               aria-label="Pesquisar vagas">
                        <button class="btn btn-outline-secondary" type="button" id="btnLimpar" style="display: none;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mensagem quando a busca não encontra nada -->
            <div id="msgNenhumResultado" class="alert alert-warning text-center py-4 rounded-3 shadow-sm d-none" role="alert">
                <i class="fa-solid fa-filter-circle-xmark fa-2x mb-3 d-block"></i>
                <h5 class="alert-heading">Nenhuma vaga encontrada</h5>
                <p class="mb-0">Tente buscar por outros termos ou categorias.</p>
            </div>

            <!-- Grid de Vagas -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="containerVagas">
                <?php foreach ($vagas as $vaga): ?>
                    <?php
                        $localizacao = $vaga['modalidade'] === 'Remoto'
                            ? $vaga['modalidade']
                            : trim(($vaga['trab_cidade'] ?? '') . ' - ' . ($vaga['trab_uf'] ?? '') . (($vaga['trab_bairro'] ?? '') !== '' ? ' | ' . $vaga['trab_bairro'] : ''), ' -|');

                        // String compilada para facilitar a busca case-insensitive no JS
                        $termoBusca = mb_strtolower(
                            ($vaga['identificador'] ?? '') . ' ' .
                            ($vaga['setor_ds'] ?? '') . ' ' .
                            ($vaga['modalidade'] ?? '') . ' ' .
                            $localizacao . ' ' .
                            ($vaga['area'] ?? '')
                        );
                    ?>
                    <div class="col vaga-item" data-search="<?= htmlspecialchars($termoBusca) ?>">
                        <div class="card h-100 vaga-card shadow-sm rounded-3">
                            <div class="card-body d-flex flex-column">
                                
                                <!-- Tags / Badges superiores -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle badge-vaga">
                                        <?= htmlspecialchars($vaga['setor_ds'] ?? 'Geral') ?>
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary badge-vaga">
                                        <?= htmlspecialchars($vaga['modalidade'] ?? 'Presencial') ?>
                                    </span>
                                </div>

                                <!-- Título / Identificador -->
                                <h5 class="card-title text-body-emphasis mb-2 fw-semibold">
                                    <?= htmlspecialchars($vaga['identificador'] ?: ($vaga['cargo_ds'] ?? 'Vaga')) ?>
                                </h5>

                                <!-- Localização -->
                                <p class="text-body-secondary small mb-3">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                    <?= htmlspecialchars($localizacao ?: '-') ?>
                                </p>

                                <!-- Área / Descrição -->
                                <p class="card-text text-body-secondary flex-grow-1 small">
                                    <?= htmlspecialchars($vaga['area'] ?? 'Confira os detalhes da oportunidade e envie seu currículo.') ?>
                                </p>

                                <!-- Ação -->
                                <div class="pt-3 border-top mt-auto">
                                    <a href="vaga_perfil.php?id=<?= urlencode($vaga['id']) ?>" 
                                       class="btn btn-outline-primary w-100 rounded-pill fw-medium">
                                        Ver Detalhes da Vaga <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>

                            </div>
                            
                            <!-- Rodapé do Card com a data -->
                            <div class="card-footer bg-transparent border-0 text-end pt-0">
                                <small class="text-body-tertiary" style="font-size: 0.75rem;">
                                    Publicada em <?= date('d/m/Y', strtotime($vaga['publicada_em'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

    <!-- Rodapé -->
    <footer class="py-4 text-center text-body-secondary border-top mt-auto">
        <div class="container">
            <small>&copy; <?= date('Y') ?> GERAR - Todos os direitos reservados.</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script de Pesquisa em Tempo Real -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputPesquisa = document.getElementById('inputPesquisa');
            const btnLimpar = document.getElementById('btnLimpar');
            const vagaItems = document.querySelectorAll('.vaga-item');
            const msgNenhumResultado = document.getElementById('msgNenhumResultado');

            if (!inputPesquisa) return;

            inputPesquisa.addEventListener('input', function() {
                const termo = this.value.trim().toLowerCase();

                // Exibe/oculta botão de limpar
                btnLimpar.style.display = termo.length > 0 ? 'block' : 'none';

                let visiveis = 0;

                vagaItems.forEach(item => {
                    const dadosBusca = item.getAttribute('data-search');
                    
                    if (dadosBusca.includes(termo)) {
                        item.classList.remove('d-none');
                        visiveis++;
                    } else {
                        item.classList.add('d-none');
                    }
                });

                // Controla a exibição da mensagem de "nada encontrado"
                if (visiveis === 0) {
                    msgNenhumResultado.classList.remove('d-none');
                } else {
                    msgNenhumResultado.classList.add('d-none');
                }
            });

            // Ação do botão limpar (X)
            btnLimpar.addEventListener('click', () => {
                inputPesquisa.value = '';
                inputPesquisa.dispatchEvent(new Event('input'));
                inputPesquisa.focus();
            });
        });
    </script>
</body>
</html>
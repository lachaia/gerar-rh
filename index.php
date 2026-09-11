<?php
//
//- index.php | RH | Portal Princial - Talentos x Candidatura às vagas - Tela principal
// (C)haia, 2026-07-23
//

include "app/includes/conexao_gerar.php";

//- Vagas visíveis publicamente: aprovadas, publicadas (fluxo de Divulgação em diante,
//- ver fluxo.php), ainda não encerradas e dentro do prazo de validade do anúncio.
$sql = "SELECT * FROM rs_vagas
        WHERE status_id = 2
          AND fechada_em IS NULL
          AND publicada_em IS NOT NULL
          AND (expira_em IS NULL OR expira_em >= CURDATE())
        ORDER BY publicada_em DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$vagas_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔵 GERAR | RH | Talentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); }
        
        /* Cabeçalho compacto */
        .hero-careers {
            background: linear-gradient(135deg, #002b5b 0%, #0056b3 100%);
            padding: 60px 0 100px 0;
            color: white;
            border-radius: 0 0 40px 40px;
        }

        /* Cartões Principais (Metades) */
        .main-card {
            border: none;
            border-radius: 24px;
            background: #ffffff;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            height: 100%;
        }
        
        .card-action-box {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
        }
        .card-action-box:hover {
            background: #ffffff;
            border-color: #0056b3;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .card-vaga-item {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }
        .card-vaga-item:hover {
            border-color: #0056b3;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .badge-vaga { 
            background: #e7f1ff; 
            color: #0056b3; 
            font-weight: 600; 
            border-radius: 50px; 
            padding: 4px 12px; 
            font-size: 0.85rem;
        }

        .culture-section { padding: 80px 0; }
        
        .step-number {
            width: 45px; height: 45px; background: #002b5b; color: white;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-weight: bold; margin-bottom: 20px;
        }

        #listaVagas{
            height: 300px;
            overflow-y: scroll;
        }
    </style>
</head>
<body>

    <!-- NAVBAR COM ACESSO AO PORTAL DO COLABORADOR -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand mb-0" href="#">
                <span class="fw-800 fs-4 text-primary">GERAR</span> | <span class="fw-300 fs-4">RH</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="app/index.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-user-tie me-2"></i>Área do Colaborador
                </a>
            </div>
        </div>
    </nav>

    <!-- BANNER HERO -->
    <header class="hero-careers text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="fw-800 mb-2 display-5">Portal de Oportunidades & Talentos</h1>
                    <p class="lead opacity-75 mb-0 fs-6">Conectando profissionais ao futuro através do trabalho e da educação.</p>
                </div>
            </div>
        </div>
    </header>

    <!-- SEÇÃO PRINCIPAL DIVIDIDA EM 2 METADES -->
    <section class="container position-relative" style="margin-top: -60px; z-index: 5;">
        <div class="row g-4 align-items-stretch">
            
            <!-- METADE 1: CURRÍCULOS (CADASTRO E GERENCIAMENTO) -->
            <div class="col-lg-6">
                <div class="main-card p-4 p-md-5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="bg-primary text-white p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-address-card fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-800 mb-0">Currículos</h3>
                                <p class="text-muted small mb-0">Gerencie suas informações ou cadastre-se</p>
                            </div>
                        </div>

                        <!-- Opção 1: Novo Cadastro -->
                        <div class="card-action-box mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa-solid fa-file-circle-plus text-primary fs-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Novo por aqui?</h6>
                                        <p class="text-muted small mb-0">Cadastre seu currículo no nosso banco de talentos.</p>
                                    </div>
                                </div>
                            </div>
                            <a href="talentos/new.php" class="btn btn-primary rounded-pill w-100 mt-3 fw-bold">
                                Cadastrar Currículo
                            </a>
                        </div>

                        <!-- Opção 2: Já Cadastrado -->
                        <div class="card-action-box">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa-solid fa-user-gear text-secondary fs-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Já tem cadastro?</h6>
                                        <p class="text-muted small mb-0">Atualize dados, histórico e acompanhe seleções.</p>
                                    </div>
                                </div>
                            </div>
                            <a href="talentos/index.php" class="btn btn-outline-dark rounded-pill w-100 mt-3 fw-bold">
                                Gerenciar Meu Perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- METADE 2: VAGAS ABERTAS E AUTOCANDIDATURA -->
            <div class="col-lg-6">
                <div class="main-card p-4 p-md-5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success text-white p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-briefcase fs-4"></i>
                                </div>
                                <div>
                                    <h3 class="fw-800 mb-0">Últimas Vagas Abertas</h3>
                                    <p class="text-muted small mb-0">Encontre oportunidades e candidate-se</p>
                                </div>
                            </div>
                            <a href="vagas/index.php" class="btn btn-link text-decoration-none fw-bold p-0">Ver todas <i class="fa-solid fa-arrow-right"></i></a>
                        </div>

                        <!-- Lista de Vagas Rápidas -->
                        <div class="d-flex flex-column gap-3" id='listaVagas'>
                        <?php if (empty($vagas_recentes)): ?>
                            <p class="text-muted small text-center py-4 mb-0">Nenhuma vaga publicada no momento.</p>
                        <?php else: ?>
                        <?php
                            foreach ($vagas_recentes as $linha) {
                                //- Localização
                                if ( $linha['modalidade'] == "Remoto" ){
                                    $localizacao = $linha['modalidade'];
                                } else {
                                    $localizacao = $linha['trab_cidade'] . "-" . $linha['trab_uf'] . " (" . $linha['modalidade'] . ")";
                                }
                                ?>
                                <div class="card-vaga-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge-vaga mb-1 d-inline-block"><?= htmlspecialchars($linha['area'] ?? '') ?></span>
                                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($linha['identificador'] ?? '') ?></h6>
                                        </div>
                                        <a href="vagas/vaga_perfil.php?id=<?= (int) $linha['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">ver</a>
                                    </div>
                                    <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($localizacao) ?></p>
                                </div>
                                <?php
                            }
                            ?>
                        <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-center">
                        <a href="vagas.php" class="btn btn-light rounded-pill w-100 fw-bold text-muted">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>Buscar mais oportunidades
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- PARTE INSTITUCIONAL (ABAIXO DO QUADRO PRINCIPAL) -->
    
    <!-- Cultura e Propósito -->
    <section class="culture-section mt-4">
        <div class="container">
            <div class="row align-items-center py-4">
                <div class="col-md-6">
                    <h2 class="fw-800 display-6">Trabalhar na GERAR é ser <span class="text-primary">protagonista</span>.</h2>
                    <p class="text-muted fs-5 mt-3">
                        Não somos apenas uma organização social. Somos um ambiente de inovação onde o seu talento ajuda a construir uma sociedade mais justa e sustentável.
                    </p>
                </div>
                <div class="col-md-6 mt-4 mt-md-0">
                    <div class="row g-3">
                        <div class="col-6"><div class="p-4 text-center border rounded-4 bg-white shadow-sm"><i class="fa-solid fa-heart text-danger fs-3 mb-2"></i><br><strong>Impacto</strong></div></div>
                        <div class="col-6"><div class="p-4 text-center border rounded-4 bg-white shadow-sm"><i class="fa-solid fa-rocket text-warning fs-3 mb-2"></i><br><strong>Inovação</strong></div></div>
                        <div class="col-6"><div class="p-4 text-center border rounded-4 bg-white shadow-sm"><i class="fa-solid fa-users text-primary fs-3 mb-2"></i><br><strong>Diversidade</strong></div></div>
                        <div class="col-6"><div class="p-4 text-center border rounded-4 bg-white shadow-sm"><i class="fa-solid fa-chart-line text-success fs-3 mb-2"></i><br><strong>Carreira</strong></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Passos do Processo Seletivo -->
    <section class="bg-white py-5 border-top">
        <div class="container text-center py-3">
            <h3 class="fw-800 mb-5">Como ingressar no nosso time?</h3>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="step-number mx-auto">1</div>
                    <h6 class="fw-bold">Cadastro</h6>
                    <p class="small text-muted">Preencha seu perfil e anexe seu melhor currículo.</p>
                </div>
                <div class="col-md-3">
                    <div class="step-number mx-auto">2</div>
                    <h6 class="fw-bold">Análise</h6>
                    <p class="small text-muted">Nossa equipe de Atração de Talentos avalia sua jornada.</p>
                </div>
                <div class="col-md-3">
                    <div class="step-number mx-auto">3</div>
                    <h6 class="fw-bold">Conexão</h6>
                    <p class="small text-muted">Entrevistas técnicas e comportamentais com os gestores.</p>
                </div>
                <div class="col-md-3">
                    <div class="step-number mx-auto">4</div>
                    <h6 class="fw-bold">Onboarding</h6>
                    <p class="small text-muted">Você inicia sua jornada como um protagonista na GERAR.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER INSTITUCIONAL -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <h5 class="fw-800 mb-1">GERAR RH</h5>
                    <p class="small opacity-50 mb-0">Geração de Emprego, Renda e Apoio ao Desenvolvimento Regional.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-4 mt-md-0">
                    <p class="small mb-0 opacity-50">&copy; 2026 GERAR - Curitiba/PR</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
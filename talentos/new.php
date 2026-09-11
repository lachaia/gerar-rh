<?php
//
//- new.php | Cadastro de Novo Curriculo (fase 1)
//- (C)haia, 07/04/2026
//

session_start();

$idModulo = 7; // Curriculo Vitae

include "../app/includes/conexao_gerar.php";

//- Se veio de "Candidatar-se" numa vaga (vaga_perfil.php), carrega o vaga_id pelo
//- cadastro pra candidatar automaticamente assim que o currículo for concluído.
$vaga_id = filter_input(INPUT_GET, 'vaga_id', FILTER_VALIDATE_INT);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talentos GERAR | Novo Curriculo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="./new.css">

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI (CSS para o estilo e JS para o autocomplete) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>`r`n
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

</head>

<body>

    <header class="topbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
            <div class="brand">
                <i class="fa-solid fa-seedling"></i>
                <span>Talentos GERAR</span>
            </div>
            <span class="text-light">Novo Curriculo</span>
        </div>
    </header>

    <main class="container py-4 py-lg-5" id="Principal">

        <section class="hero mb-4">
            <h1 class="mb-2">Cadastro Inicial de Curriculo</h1>
            <p class="mb-0">Nesta primeira fase, preencha seus dados pessoais e monte a base do seu perfil profissional.</p>
        </section>

        <!-- SESSÃO: DADOS PESSOAIS -->
        <section class="card shadow-sm border-0" id="bloco1">
            <div class="card-header section-header d-flex justify-content-between align-items-center"
                role="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseDados"
                aria-expanded="true"
                style="cursor: pointer;">

                <span><i class="fa-regular fa-address-card me-2"></i> Dados Pessoais</span>

                <i class="fa-solid fa-chevron-down btn-toggle-icon"></i>
            </div>

            <!-- Bloco Dados Pessoais  -->
            <div class="collapse show" id="collapseDados">
                <div class="card-body card-cor">
                    <form id="form_dados" class="vstack gap-4" novalidate>
                        <input type="hidden" id='idCV' name='idCV' value='0'>
                        <input type="hidden" id='idPessoa' name='idPessoa' value='0'>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">CPF *</label>
                                <input type="text" id="cpf" name="cpf" class='form-control' required
                                    oninput="this.value = maskCPF(this.value)"
                                    onblur="testar_cpf(this)"
                                    maxlength="14" placeholder="000.000.000-00">
                            </div>

                            <div class="col-md-9">
                                <label class="form-label">Nome completo *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required onblur='testa_bloco1()'>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Nome social</label>
                                <input type="text" class="form-control" id="nomeSocial" name="nomeSocial" onblur='testa_bloco1()'>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sexo Biológico</label>
                                <select class="form-select" id="sexo" name="sexo" onblur='testa_bloco1()'>
                                    <option value="">Selecione</option>
                                    <option value="F">Feminino</option>
                                    <option value="M">Masculino</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Telefone *</label>
                                <input type="text" id="telefone" name="telefone" required class="form-control"
                                    oninput="this.value = maskTelefone(this.value)"
                                    maxlength="15" placeholder="(00) 00000-0000" onblur='testa_bloco1()'>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" required onblur='testa_bloco1()'>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nacionalidade*</label>
                                <input type="text" class="form-control" id="nacionalidade" name="nacionalidade" onblur='testa_bloco1()'>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Grau Escolar *</label>
                                <?= f_escolaridade(0, $conn); ?>
                            </div>
                        </div>

                        <!-- Bloco de Gênero / Deficiência -->
                        <div class="card shadow-sm p-3 rounded-3 border-0 bloco-cor">
                            <!-- Gênero x Deficiência -->
                            <div class="row align-items-center">
                                <div class="col-sm-2">
                                    <label class="form-label fw-bold text-primary"><i class="fa-solid fa-venus-mars"></i> Gênero</label>
                                </div>
                                <!-- Gênero -->
                                <div class="col-sm-8">
                                    <div class="d-flex justify-content-between">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="F">
                                            <label class="form-check-label" for="feminino">Feminino</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="M">
                                            <label class="form-check-label" for="masculino">Masculino</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="N">
                                            <label class="form-check-label" for="nao-binario">Não-Binário</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="O">
                                            <label class="form-check-label" for="outros">Outros</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="genero" id="genero" value="0">
                                            <label class="form-check-label" for="prefiro-nao-responder">Prefiro não responder</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2 border-start border-3 border-dark ps-3">
                                    <input class="form-check-input me-2" type="checkbox" id="deficiente" name="deficiente" value="0"
                                        onclick="toggleDeficiencia(this)">
                                    <label class="form-check-label" for="deficiente">Deficiente?</label>
                                </div>
                            </div>
                        </div>

                        <!-- Bloco de Deficiência (inicia oculto) -->
                        <div id="deficiencia-bloco" class="card shadow-sm p-3 rounded-3 border-0 collapse bloco-cor">
                            <div class="row align-items-center">
                                <div class="col-sm-2">
                                    <label class="form-label fw-bold text-primary"><i class="fa-brands fa-accessible-icon"></i> Deficiência</label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="d-flex justify-content-between">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="fisica" id="fisica" value='0'
                                                onchange='toggleDefValue(this)'>
                                            <label class="form-check-label" for="fisica">Física</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="visual" id="visual" value='0'
                                                onchange='toggleDefValue(this)'>
                                            <label class="form-check-label" for="visual">Visual</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="auditiva" id="auditiva" value='0'
                                                onchange='toggleDefValue(this)'>
                                            <label class="form-check-label" for="auditiva">Auditiva</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="mental" id="mental" value='0'
                                                onchange='toggleDefValue(this)'>
                                            <label class="form-check-label" for="mental">Mental</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="intelectual" id="intelectual" value='0'
                                                onchange='toggleDefValue(this)'>
                                            <label class="form-check-label" for="intelectual">Intelectual</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="autista" id="autista" value='0'
                                                onchange='toggleDefValue(this)'>
                                            <label class="form-check-label" for="autista">Autista</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2 border-start border-3 border-dark ps-3">
                                    <label class="form-label" for="cid">CID</label>
                                    <input class="form-control" type="text" id="cid" name="cid" placeholder="Código CID" value="">
                                </div>
                            </div>
                        </div>

                        <!-- Bloco LinkedIn -->
                        <div class="card shadow-sm p-2 rounded-3 border-0 bloco-cor">
                            <label class="form-label fw-bold" for="linkedin">Perfil do LinkedIn</label>
                            <input class="form-control" type="text" id="linkedin" name="linkedin" placeholder="Insira o link do seu perfil" value=''>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bloco UPLOAD  -->
            <div class="card shadow-sm p-3 mt-3 rounded-3 border-1 collapse" style='background-color: #c5c6d0e1;' id="blocoUpload">
                <label class="form-label fw-bold" for="arquivo"><b>Opcional</b>: envie o arquivo (pdf) do seu currículo personalizado</label>
                <div class="input-group">
                    <input class="form-control" type="file" id="arquivo" name="arquivo" accept=".pdf">
                    <button type=' button' class="btn btn-primary" onclick="envia_arquivoCV()">Enviar</button>
                </div>
            </div>

            <div id='mensagemUpload' class="h5 text-center"></div>

            <!-- Botões para salvar o bloco 1 -->
            <div class="row g-1 mt-1 collapse" id="btnDados">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 botaozao" onclick="reset_bloco1()"><i class="fa-solid fa-recycle"></i> Reset</button>
                    <button type="button" class="btn btn-outline-primary btn-sm m-1 botaozao" onclick="salvar_bloco1()"><i class="fa-solid fa-download"></i> Salvar este bloco</button>
                </div>
            </div>
            <div class="h5 text-center" id="msgDados"></div>
        </section>

        <!-- Bloco Experiencia Profissional -->
        <section class="card shadow-sm border-0 mb-3">
            <div class="card-header section-header d-flex justify-content-between align-items-center" style="cursor: default;">
                <span><i class="fa-solid fa-briefcase me-2"></i> Experiencia Profissional</span>

                <div class="d-flex align-items-center">
                    <button type='button' class='btn btn-outline-success btn-sm me-3 botaozao' id="btnNovoExp" onClick='exp_incluir()'>
                        <i class="fa-solid fa-plus"></i> Novo
                    </button>

                    <a role="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseExp"
                        aria-expanded="true"
                        class="text-dark"
                        style="cursor: pointer;">
                        <i class="fa-solid fa-chevron-down btn-toggle-icon"></i>
                    </a>
                </div>
            </div>
            <div class="collapse" id="collapseExp">
                <div class="card-body">
                    <table class='table table-striped table-hover table-sm'>
                        <thead>
                            <tr>
                                <th class="text-center">Início</th>
                                <th class="text-center">Fim</th>
                                <th class="text-center">Atual</th>
                                <th>Empresa</th>
                                <th>Cargo</th>
                                <th class="text-center" style='width: 100px'>
                                    <i class="fa-solid fa-circle-down"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id='tbExp'></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Bloco Formacao Academica -->
        <section class="card shadow-sm border-0 mb-3">
            <div class="card-header section-header d-flex justify-content-between align-items-center" style="cursor: default;">

                <span><i class="fa-solid fa-graduation-cap"></i> Formacao Academica</span>
                <div class="d-flex align-items-center">
                    <button type='button' class='btn btn-outline-success btn-sm me-3 botaozao'
                        id="btnNovoFA" onClick='fa_incluir()'>
                        <i class="fa-solid fa-plus"></i> Novo
                    </button>

                    <a role="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseFA"
                        aria-expanded="true"
                        class="text-dark"
                        style="cursor: pointer;">
                        <i class="fa-solid fa-chevron-down btn-toggle-icon"></i>
                    </a>
                </div>

            </div>
            <div class="collapse" id="collapseFA">
                <div class="card-body">
                    <table class='table table-striped table-hover table-sm'>
                        <thead>
                            <tr>
                                <th>Nível</th>
                                <th>Curso</th>
                                <th><i class="fa-solid fa-graduation-cap"></i></th>
                                <th class="text-center">Conclusão</th>
                                <th style='width: 50px' class="text-center"><i
                                        class="fa-solid fa-circle-down"></i></th>
                            </tr>
                        </thead>
                        <tbody id='tbFormacao'></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Bloco: IDIOMAS -->
        <section class="card shadow-sm border-0 mb-3">

            <div class="card-header section-header d-flex justify-content-between align-items-center" style="cursor: default;">

                <span><i class="fa-solid fa-language"></i> Idiomas</span>

                <div class="d-flex align-items-center">
                    <button type='button' class='btn btn-outline-success btn-sm me-3 botaozao'
                        id="btnNovoFA" onClick='idioma_incluir()'>
                        <i class="fa-solid fa-plus"></i> Novo
                    </button>

                    <a role="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseIdiomas"
                        aria-expanded="true"
                        class="text-dark"
                        style="cursor: pointer;">
                        <i class="fa-solid fa-chevron-down btn-toggle-icon"></i>
                    </a>
                </div>

            </div>

            <div class="collapse" id="collapseIdiomas">
                <div class="card-body">
                    <table class='table table-striped table-hover table-sm'>
                        <thead>
                            <tr>
                                <th>Idioma</th>
                                <th>Fluência</th>
                                <th>Incluído em</th>
                                <th style='width: 50px' class="text-center"><i class="fa-solid fa-circle-down"></i></th>
                            </tr>
                        </thead>
                        <tbody id='tbIdiomas'></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Bloco: Conquistas -->
        <section class="card shadow-sm border-0 mb-3">
            <div class="card-header section-header d-flex justify-content-between align-items-center" style="cursor: default;">
                <span><i class="fa-solid fa-certificate"></i> Conquistas/Certificados</span>
                <div class="d-flex align-items-center">
                    <button type='button' class='btn btn-outline-success btn-sm me-3 botaozao'
                        id="btnNovoConq" onClick='con_incluir()'>
                        <i class="fa-solid fa-plus"></i> Novo
                    </button>
                    <a role="button" data-bs-toggle="collapse" data-bs-target="#collapseConquista"
                        aria-expanded="true" class="text-dark" style="cursor: pointer;">
                        <i class="fa-solid fa-chevron-down btn-toggle-icon"></i>
                    </a>
                </div>
            </div>
            <div class="collapse" id="collapseConquista">
                <div class="card-body">
                    <!-- Alerta para mensagens de erro/sucesso da seção -->
                    <div id="msgAlertaConquista" class="mb-2"></div>

                    <table class='table table-striped table-hover table-sm'>
                        <thead>
                            <tr>
                                <th class="text-center">Ano</th>
                                <th>Tipo</th>
                                <th>Título</th>
                                <th style='width: 80px' class="text-center">
                                    <i class="fa-solid fa-gear"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id='tbConquistas'>
                            <!-- As linhas devem vir do banco de dados inicialmente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Bloco: HABILIDADES -->
        <section class="card shadow-sm border-0 mb-3">
            <div class="card-header section-header d-flex justify-content-between align-items-center" style="cursor: default;">
                <span><i class="fa-solid fa-lightbulb"></i> Habilidades</span>
                <div class="d-flex align-items-center">
                    <a role="button" data-bs-toggle="collapse" data-bs-target="#collapseHabilidades"
                        aria-expanded="true" class="text-dark" style="cursor: pointer;">
                        <i class="fa-solid fa-chevron-down btn-toggle-icon"></i>
                    </a>
                </div>
            </div>
            <div class="collapse show" id="collapseHabilidades">
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" id="inputSkill" class="form-control" placeholder="Digite uma habilidade..." maxlength="20">
                        <button class="btn btn-primary" onclick="addSkill()">Adicionar</button>
                    </div>
                    <div id="skillsContainer" class="d-flex flex-wrap gap-2"></div>
                    <div class="text-end collapse" id='btnHabilidades'><button class="btn btn-outline-primary mt-3 botaozao" onclick="saveSkills()">Salvar Habilidades</button></div>

                </div>
                <div id='msgHabilidades' class="text-center h5"></div>
            </div>
        </section>


        <!-- Bloco: DIVERSIDADE -->
        <section class="card shadow-sm border-0 mb-3">
            <div class="card-header section-header d-flex justify-content-between align-items-center" style="cursor: default;">
                <span><i class="fa-solid fa-hands-holding-circle"></i> Diversidade</span>
                <div class="d-flex align-items-center">

                    <a role="button" data-bs-toggle="collapse" data-bs-target="#collapseDiversidade"
                        aria-expanded="true" class="text-dark" style="cursor: pointer;">
                        <i class="fa-solid fa-chevron-down btn-toggle-icon"></i>
                    </a>
                </div>
            </div>
            <div class="collapse" id="collapseDiversidade">
                <div class="card-body">

                    <form name='formDiversidade' id='formDiversidade'>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <i class="fa-solid fa-circle-info" style='font-size: 18px'></i> O preenchimento desta seção é opcional. As informações
                            serão usadas em todos os processos que utilizem a solução de Diversidade. Nenhum dado será utilizado como critério de eliminação.
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label for="cidade">Local de Origem</label>
                                <div class="input-group">
                                    <input type="text" id="cidade" name="cidade" class='form-control' placeholder="Digite o nome da cidade" onchange='troggle_on_btn_diversidade()'>
                                    <span class="input-group-text"><a href="#" data-bs-toggle="modal" data-bs-target="#modalOrigem"><i class="fa-regular fa-circle-question"></i></a></span>
                                </div>
                                <input type="hidden" id="cidade_id" name="cidade_id">
                            </div>
                            <div class="col-sm-4">
                                <label for="cor">Qual sua cor ou raça</label>
                                <div class="input-group">
                                    <select name="cor" id="cor" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                        <option value="">Escolha</option>
                                        <option value="Amarela">Amarela</option>
                                        <option value="Branca">Branca</option>
                                        <option value="Indigena">Indígena</option>
                                        <option value="Parda">Parda</option>
                                        <option value="Preta">Preta</option>
                                        <option value="não">Prefiro não responder</option>
                                    </select>
                                    <span class="input-group-text">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalCorRaca">
                                            <i class="fa-regular fa-circle-question"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="pronome">Pronome adequado para você</label>
                                <select name="pronome" id="pronome" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                    <option value="">Escolha</option>
                                    <option value="ela">Ela / Dela</option>
                                    <option value="ele">Ele / Dele</option>
                                    <option value="não">Prefiro não responder</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-4">
                                <label for="orientacao">Sua orientação sexual</label>
                                <div class="input-group">
                                    <select name="orientacao" id="orientacao" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                        <option value="">Escolha</option>
                                        <option value="Assexual">Assexual</option>
                                        <option value="Bissexual">Bissexual</option>
                                        <option value="Heterossexual">Heterossexual</option>
                                        <option value="Homossexual">Homossexual</option>
                                        <option value="Pansexual">Pansexual</option>
                                        <option value="não">Prefiro não responder</option>
                                    </select>
                                    <span class="input-group-text">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalOrientacao">
                                            <i class="fa-regular fa-circle-question"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="pronome">Sua identidade de gênero</label>
                                <div class="input-group">
                                    <select name="identgenero" id="identgenero" class="form-select" onchange='troggle_on_btn_diversidade()'>
                                        <option value="">Escolha</option>
                                        <option value="Cisgênero">Cisgênero</option>
                                        <option value="Transgênero">Transgênero</option>
                                        <option value="não">Prefiro não responder</option>
                                    </select>
                                    <span class="input-group-text">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalIdentGenero">
                                            <i class="fa-regular fa-circle-question"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <!--
                            <div class="col-sm-4">
                                <div class="col text-end mt-3 collapse" id='btnDiversidade'>
                                    <button type="button" class="btn btn-outline-secondary px-4" onclick='btnResetBloco2()'><i class="fa-solid fa-recycle"></i> Reset</button>
                                    <button type="button" class="btn btn-outline-primary px-4" onclick='salvar_cv2()'><i class="fa-solid fa-download"></i> Salvar</button>
                                </div>
                            </div>
                            -->
                        </div>
                    </form>

                </div>
                <div id='msgformDiversidade' class="text-center"></div>
            </div>
        </section>

        <div class="d-flex gap-2 justify-content-end pb-4" id="botoes_final">
            <a href="index.php" class="btn btn-outline-secondary botaozao">Cancelar</a>
            <button type="button" class="btn btn-success botaozao" id="btnSalvarCV" onclick='salvar_curriculo()'>Salvar e Enviar</button>
        </div>

        <div id="msgNovoCV" class="alert d-none"></div>
    </main>

    <!-- MODAL - expModalVer | VISUALIZAR | EXPERIÊNCIA PROFISSIONAL -->
    <div class="modal fade" id="expModalVer" tabindex="-1" aria-labelledby="visualizarExpLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header modal-view-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fa-solid fa-briefcase me-2"></i> Experiência Profissional
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-sm-9">
                            <label class="label-custom">Empresa</label>
                            <p id="v_empresa" class="visCampo" style="font-size: 1.1rem; color: #2c3e50;"></p>
                        </div>
                        <div class="col-sm-3 text-sm-end">
                            <label class="label-custom">Status</label>
                            <div id="v_atual"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <label class="label-custom">Cargo</label>
                            <p id="v_cargo" class="visCampo fw-bold text-primary"></p>
                        </div>
                        <div class="col-sm-3">
                            <label class="label-custom">Início</label>
                            <p id="v_ano_ini" class="visCampo text-muted"></p>
                        </div>
                        <div class="col-sm-3">
                            <label class="label-custom">Fim</label>
                            <p id="v_ano_fim" class="visCampo text-muted"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="label-custom">Atividades e Descrição</label>
                            <div class="p-3 bg-light rounded border-start border-4 border-secondary">
                                <p id="v_descricao" class="mb-0 text-dark" style="white-space: pre-line; line-height: 1.5;"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <span id='divQuandoExp' class="text-muted flex-grow-1" style='font-size: 11px;'></span>
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - faModalVer | Formação Acadêmica - VISUALIZAR -->
    <div class="modal fade" id="faModalVer" tabindex="-1" aria-labelledby="visualizarfaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header modal-view-header" style="background-color: #34495e;">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fa-solid fa-graduation-cap me-2"></i> Formação Acadêmica
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-12">
                            <label class="label-custom">Curso / Qualificação</label>
                            <p id="v_curso" class="visCampo fw-bold" style="font-size: 1.25rem; color: #2c3e50; border-bottom-color: #3498db;"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <label class="label-custom">Instituição de Ensino</label>
                            <p id="v_instituicao" class="visCampo"></p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-sm-4">
                            <label class="label-custom">Sigla</label>
                            <p id="v_sigla" class="visCampo text-muted"></p>
                        </div>
                        <div class="col-sm-4">
                            <label class="label-custom">Nível</label>
                            <p id="v_nivel" class="visCampo text-primary"></p>
                        </div>
                        <div class="col-sm-4">
                            <label class="label-custom">Ano de Conclusão</label>
                            <p id="v_ano_conclusao" class="visCampo fw-bold"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="file-box">
                                <label class="label-custom">Comprovante / Diploma</label>
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-file-pdf text-danger me-2 fa-lg"></i>
                                    <span id="fa_arquivo" class="text-truncate"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <span id='divQuando' class="text-muted flex-grow-1" style='font-size: 11px;'></span>
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>>

    <!-- MODAL - expModalInc | INCLUIR | EXPERIÊNCIA PROFISSIONAL -->
    <div class="modal fade" id="expModalInc" tabindex="-1" aria-labelledby="incluirExpLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-briefcase"></i> Incluir Experiência Profissional</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <form action="#" id='formIncExp'>
                        <div class="row mb-3">
                            <div class="col-sm-10">
                                <label class="col-form-label">Empresa</label>
                                <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Nome da Empresa">
                            </div>
                            <div class="col-sm-2 mt-4">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="0" onchange='exp_atual(this)'>
                                    <label class="form-check-label" for="ativo">Atual</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-8">
                                <label class="col-form-label">Cargo</label>
                                <input type="text" id="cargo" name="cargo" class="form-control" placeholder="Informe o cargo ocupado">
                            </div>
                            <div class="col-sm-2">
                                <label class="col-form-label">Início</label>
                                <input type="number" id="ano_ini" name="ano_ini" class="form-control" placeholder="ano">
                            </div>
                            <div class="col-sm-2">
                                <label class="col-form-label">Fim</label>
                                <input type="number" id="ano_fim" name="ano_fim" class="form-control" placeholder="ano">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label class="col-form-label">Descrição</label>
                                <textarea name="descricao" id="descricao" class="form-control" placeholder="Descreva aqui sua experiência"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="btn-group" id='divBotoesExp'>
                                <!-- Botão Fechar (vermelho) -->
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                    data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Fechar
                                </button>
                                <!-- Botão Reset (azul) -->
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                    id='btnResetExp'>
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                                <!-- Botão Salvar (verde) -->
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                    onclick='exp_incluir_salva()'>
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                            </div>
                        </div>
                        <div id='msgformIncExp' class='text-center'></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - faModalInc | Formação Acadêmica - INCLUIR -->
    <div class="modal fade" id="faModalInc" tabindex="-1" aria-labelledby="incluirfaLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-eye"></i> Incluir Formação Acadêmica</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <form action="#" id='formIncFA'>
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label class="col-form-label">Curso</label>
                                <input type="text" id="curso" name="curso" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label class="col-form-label">Instituição</label>
                                <div id='seletorInstituicao'><?= seletorInstituicoes() ?></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-8">
                                <label class="col-form-label">Nível</label>
                                <div id='seletorTipoCurso'><?= seletorTipoCurso() ?></div>
                            </div>
                            <div class="col-sm-4">
                                <label class="col-form-label">Conclusão</label>
                                <input type="number" id='ano' name='ano' class='form-control'>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label class="col-form-label">Arquivo para upload (opcional)</label>
                                <input type="file" id='arquivo' name='arquivo' class='form-control'>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="btn-group" id='divBotoesFA'>
                                <!-- Botão Fechar (vermelho) -->
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                    data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Fechar
                                </button>
                                <!-- Botão Reset (azul) -->
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                    id='btnResetFA'>
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                                <!-- Botão Salvar (verde) -->
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                    onclick='fa_incluir_salva()'>
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                            </div>
                        </div>
                        <div id='msgformIncFA' class='text-center'></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - faMovalIncIE | Formação Acadêmica - INCLUIR IE-->
    <div class="modal fade" id="faModalIncIE" tabindex="-1" aria-labelledby="incluirfaLabelIE"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: #C8C8C8 ">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-eye"></i> Incluir Instituição de Ensino</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <form action="#" id='formIncFAIE' class='mt-4'>
                        <div class="row mb-3">
                            <div class="col-sm-9">
                                <label class="col-form-label">Nome da Instituição</label>
                                <input type="text" id="_nomeInstituicao" name="_nomeInstituicao"
                                    class="form-control">
                            </div>
                            <div class="col-sm-3">
                                <label class="col-form-label">Sigla</label>
                                <input type="text" id="_sigla" name="_sigla" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <label class="col-form-label">Cidade</label>
                                <input type="text" id="_cidade" name="_cidade" class="form-control">
                                <input type="hidden" id='cidade_id' name='cidade_id' value='0'>
                            </div>
                            <div class="col-sm-3">
                                <label class="col-form-label">UF</label>
                                <div id='seletorTipoUF'><?= seletorUF() ?></div>
                            </div>
                            <div class="col-sm-3">
                                <label class="col-form-label">País</label>
                                <input type="text" id='_pais' name='_pais' class='form-control'
                                    value='Brasil'>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="btn-group" id="botoes_fa_id">
                                <!-- Botão Fechar (vermelho) -->
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                    data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Fechar
                                </button>
                                <!-- Botão Reset (azul) -->
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1">
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                                <!-- Botão Salvar (verde) -->
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                    onclick='f_inclui_ie_salva()'>
                                    <i class="fa fa-save"></i> Salvar
                                </button>

                            </div>
                        </div>
                        <div id='msgformIncFAIE' class="text-center h4"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - idiModalInc | INCLUIR | IDIOMAS -->
    <div class="modal fade" id="idiModalInc" tabindex="-1" aria-labelledby="incluirIdiLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-globe"></i> Incluir IDIOMA</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <form action="#" id='formIncIdi'>
                        <input type="hidden" id='idPessoa' name='idPessoa' value='<?= $idPessoa ?>'>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <label class="col-form-label">Idioma</label>
                                <?= seletor_idiomas() ?>
                            </div>
                            <div class="col-sm-6">
                                <label class="col-form-label">Idioma</label>
                                <?= seletor_fluencia() ?>
                            </div>

                        </div>

                        <div class="row mb-3">
                            <div class="btn-group" id='divBotoesIdi'>
                                <!-- Botão Fechar (vermelho) -->
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                    data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Fechar
                                </button>
                                <!-- Botão Reset (azul) -->
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                    id='btnResetIdi'>
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                                <!-- Botão Salvar (verde) -->
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                    onclick='idioma_incluir_salva()'>
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                            </div>
                        </div>
                        <div id='msgformIncIdi' class='text-center'></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - conModalInc | INCLUIR | CONQUISTAS & CERTIFICADOS -->
    <div class="modal fade" id="conModalInc" tabindex="-1" aria-labelledby="incluirConLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: gainsboro">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">
                        <h5><i class="fa-solid fa-briefcase"></i> Incluir Certificado / Conquista</h5>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <form id="formIncCon" enctype="multipart/form-data" method="post">
                        <div class="row mb-3">
                            <div class="col-sm-10">
                                <label class="col-form-label">Tipo</label>
                                <?= seletorConquistas() ?>
                            </div>
                            <div class="col-sm-2">
                                <label class="col-form-label">Ano</label>
                                <input type="number" id="ano" name="ano" class="form-control" placeholder="Ano">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label class="col-form-label">Título do Certificado/Conquista</label>
                                <input type='text' name="titulo" id="titulo" class="form-control" placeholder="Título do Certificado/Conquista">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label class="col-form-label">Descrição</label>
                                <textarea name="descricao" id="descricao" class="form-control" placeholder="Descreva aqui"></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <label class="col-form-label">Arquivo para upload</label>
                                <input type="file" id='arquivoCert' name='arquivoCert' class='form-control'>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="btn-group" id='divBotoesCon'>
                                <!-- Botão Fechar (vermelho) -->
                                <button type="button" class="btn btn-outline-danger btn-sm rounded m-1"
                                    data-bs-dismiss="modal">
                                    <i class="fa fa-close"></i> Fechar
                                </button>
                                <!-- Botão Reset (cinza escuro) -->
                                <button type="reset" class="btn btn-outline-secondary btn-sm rounded m-1"
                                    id='btnResetCon' onClick='btn_reset_con()'>
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                                <!-- Botão Salvar (verde) -->
                                <button type="button" class="btn btn-outline-success btn-sm rounded m-1"
                                    onclick='con_incluir_salva()'>
                                    <i class="fa fa-save"></i> Salvar
                                </button>
                            </div>
                        </div>
                        <div id='msgformIncCon' class='text-center'></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - conModalVer | VISUALIZAR | CERTIFICADO OU CONQUISTA -->
    <div class="modal fade" id="conModalVer" tabindex="-1" aria-labelledby="visualizarConLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header modal-view-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fa-solid fa-eye me-2"></i> Visualizar Certificado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-sm-9">
                            <label class="label-custom">Tipo de Conquista</label>
                            <p id="v_tipo" class="visCampo"></p>
                        </div>
                        <div class="col-sm-3">
                            <label class="label-custom">Ano</label>
                            <p id="v_ano" class="visCampo text-primary"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="label-custom">Título</label>
                            <p id="v_titulo" class="visCampo" style="font-size: 1.2rem; color: #2c3e50;"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="label-custom">Descrição Detalhada</label>
                            <p id="v_descricao" class="visCampo text-muted" style="border-bottom: none;"></p>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="file-box">
                                <label class="label-custom">Documento Anexo</label>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span id="v_arquivo" class="text-truncate me-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <span id='divQuandoExp' class="text-muted flex-grow-1" style='font-size: 11px;'></span>
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - modalOrigem | TEXTO EXPLICATIVO | Importância da Naturalidade -->
    <div class="modal fade" id="modalOrigem">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Qual a importância de preencher sua naturalidade?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>O local de origem de uma pessoa, além de influenciar sua cultura e costumes, também pode impactar suas oportunidades de emprego.</p>
                    <p>No Brasil, muitas cidades concentram grande parte das vagas de trabalho, o que leva candidatos de outras regiões a enfrentarem desafios extras para conquistar uma posição no mercado. Além do esforço necessário para se inserir profissionalmente, essas pessoas podem enfrentar preconceitos relacionados ao seu local de origem.</p>
                    <p>Nosso compromisso é transformar essa realidade por meio de dados, promovendo ações afirmativas e aprimorando as estratégias de Recrutamento & Seleção.</p>
                    <p>É importante ressaltar que suas informações nunca serão utilizadas para excluir candidatos em processos seletivos. Pelo contrário, elas contribuem para análises de diversidade e ajudam a minimizar vieses na tomada de decisão.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - modalCorRaca | TEXTO EXPLICATIVO | Importância da Cor / Raça -->
    <div class="modal fade" id="modalCorRaca">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Qual a importância de preencher sua cor/raça?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>A identidade racial de uma pessoa pode impactar diversos aspectos da sua vida, incluindo acesso à renda, serviços, oportunidades profissionais e muito mais. No Brasil, ainda há desigualdades estruturais que afetam diferentes grupos de maneira distinta.</p>
                    <p>No ambiente de trabalho, essa realidade se reflete em diferenças significativas na distribuição de oportunidades. Alguns grupos enfrentam mais barreiras para inserção e crescimento profissional, o que se torna ainda mais evidente em cargos de liderança.</p>
                    <p>O objetivo é transformar esse cenário por meio da coleta e análise de dados, promovendo estratégias afirmativas dentro dos processos de Recrutamento & Seleção.</p>
                    <p>É importante reforçar que essas informações jamais serão utilizadas como critério de exclusão em seleções. Pelo contrário, elas permitem que as empresas ampliem suas iniciativas de diversidade e reduzam vieses nas contratações.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - modalOrientacao | TEXTO EXPLICATIVO | Importância da Orientação Sexual -->
    <div class="modal fade" id="modalOrientacao">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Qual a importância de preencher sua orientação sexual?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>A orientação sexual ainda influencia a forma como o mérito e a capacidade profissional são percebidos. Em algumas organizações, visões conservadoras podem impactar as decisões de contratação, limitando oportunidades para pessoas não heterossexuais.</p>
                    <p>O propósito deste trabalho é transformar essa realidade por meio de dados e apoiar iniciativas afirmativas dentro das estratégias de Recrutamento & Seleção.</p>
                    <p>É importante ressaltar que essas informações jamais serão utilizadas como critério de exclusão em processos seletivos. Pelo contrário, elas permitem análises mais abrangentes sobre diversidade e auxiliam na redução de vieses durante a seleção.</p>
                    <p><strong>Assexual</strong> é a pessoa que experimenta pouco ou nenhuma atração sexual.</p>
                    <p><strong>Bissexual</strong> é a pessoa que sente atração por dois ou mais gêneros.</p>
                    <p><strong>Heterossexual</strong> é a pessoa que sente atração por pessoas de gênero oposto ao seu.</p>
                    <p><strong>Homossexual</strong> é a pessoa que sente atração por pessoas do mesmo gênero.</p>
                    <p><strong>Pansexual</strong> é a pessoa que sente atração por pessoas de todos os gêneros.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - modalIdentGenero | TEXTO EXPLICATIVO | Importância da Identidade de Gênero -->
    <div class="modal fade" id="modalIdentGenero">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Qual a importância de preencher sua identidade de gênero?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>A sociedade, em grande parte, encara o gênero como algo fixo e binário. No entanto, a identidade de gênero abrange um espectro diverso, que nem sempre corresponde ao gênero atribuído no nascimento.</p>
                    <p>Pessoas trans enfrentam desafios adicionais no mercado de trabalho, muitas vezes tendo suas oportunidades de emprego reduzidas devido a preconceitos estruturais.</p>
                    <p>Nosso objetivo é transformar essa realidade por meio da coleta e análise de dados, promovendo ações afirmativas dentro das estratégias de Recrutamento & Seleção.</p>
                    <p>É fundamental destacar que essas informações nunca serão utilizadas para excluir candidatos nos processos seletivos. Pelo contrário, elas possibilitam uma visão mais ampla da diversidade e ajudam a reduzir vieses na seleção.</p>
                    <p><strong>Cisgênero</strong> ou (Cis) é a pessoa que se identifica com o gênero que lhe foi atribuído ao nascer.</p>
                    <p><strong>Transgênero</strong> ou (trans) é a pessoa cuja identidade não corresponde ao gênero que lhe foi atribuído ao nascer.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Candidatura pendente (veio de "Candidatar-se" numa vaga) - ver salvar_curriculo() em new.js
        var VAGA_ID_PENDENTE = <?= $vaga_id ? (int) $vaga_id : 'null' ?>;
    </script>
    <script src="./new.js"></script>
    <script src="../app/js/cpf.js"></script>
</body>

</html>
<?php

function f_escolaridade($_id = 0, $conn)
{
    $sql = "SELECT * FROM rh_graus_instrucao;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = '<label for="idGrauInstrucao" class="form-label">Escolaridade</label>';
    $select .= "<select class='form-select fs-13' id='idGrauInstrucao' name='idGrauInstrucao' required>";
    if ($_id == 0) $select .= "<option value='0' selected>Selecione</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $id) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$id' $selected>$descricao</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

function f_etnias($_id = 0, $conn)
{
    $sql = "SELECT * FROM RH.rh_etnias;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    //- ASSUNTOS
    //
    $select = '<label for="idEtnia" class="form-label">Etnia</label>';
    $select .= "<select class='form-select fs-13' id='idEtnia' name='idEtnia' required>";
    if ($_id == 0) $select .= "<option value='0' selected>Selecione...</option>";

    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //
        extract($linha);
        if ($_id == $idEtnia) $selected = "selected";
        else $selected = "";
        $select .= "<option value='$idEtnia' $selected>$categoria</option>";
        //
    }

    $select .= "</select>";

    echo $select;
}

function seletorUF($_uf = '')
{
    global $conn;
    $sql = "SELECT * FROM rh_uf";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='uf' name='uf' required>";
    if ($_uf == '') $select .= "<option value='0' selected>UF</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = ($_uf == $uf) ? "selected" : "";
        $select .= "<option value='$uf' $selected>$nome</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletorTipoCurso($id = 0)
{
    global $conn;
    $sql = "SELECT * FROM RH.rh_fa_niveis;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idNivel' name='idNivel' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione um Nivel</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = ($id == $idNivel) ? "selected" : "";
        $select .= "<option value='$idNivel' $selected>$nivel</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletorInstituicoes($id = 0)
{
    global $conn;
    $sql = "SELECT * FROM RH.rh_fa_instituicoes order by nome;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<div class='input-group'>";
    $select .= "<select class='form-select fs-13' id='idInstituicao' name='idInstituicao' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione uma IE</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = ($id == $idInstituicao) ? "selected" : "";
        $select .= "<option value='$idInstituicao' $selected>$nome ($cidade/$uf-$pais)</option>";
    }
    $select .= "</select>";
    $select .= "<button type='button' class='btn btn-outline-secondary' onclick='f_inclui_ie()'><i class='fa fa-plus'></i></button>";
    $select .= "</div>";
    echo $select;
}

function seletor_idiomas($id = 0)
{
    global $conn;
    $sql = "SELECT * FROM rh_idiomas;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idIdioma' name='idIdioma' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione um Idioma</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = ($id == $idIdioma) ? "selected" : "";
        $select .= "<option value='$idIdioma' $selected>$nome</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletor_fluencia($id = 0)
{
    global $conn;
    $sql = "SELECT * FROM rh_fluencias;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idFluencia' name='idFluencia' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione uma Fluencia</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = ($id == $idFluencia) ? "selected" : "";
        $select .= "<option value='$idFluencia' $selected>$nome</option>";
    }
    $select .= "</select>";
    echo $select;
}

function seletorConquistas($id = 0)
{
    global $conn;
    $sql = "SELECT * FROM rh_conqTipos;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = "<select class='form-select fs-13' id='idConqTipo' name='idConqTipo' required>";
    if ($id == 0) $select .= "<option value='0' selected>Selecione uma Conquista</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $selected = ($id == $idConqTipo) ? "selected" : "";
        $select .= "<option value='$idConqTipo' $selected>$nome</option>";
    }
    $select .= "</select>";
    echo $select;
}

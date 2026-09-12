<?php
//
//- ficha_colab.php | exibe Ficha Pessoa
//- (C)haia, 07/03/2025 | 20/10/2025
//

session_start();

$idModulo = 16; // Portal do Gestor

$modulo = "Colaboradores";
include 'includes/header.php';

$parametros = filter_input_array(INPUT_GET, FILTER_DEFAULT);

if ($parametros) extract($parametros);
if (empty($id)) {
    die("FALTOU PARÂMETROS ");
}

if (!isset($_SESSION['idLogin'])) {
    header("location: logout.php");
    exit();
} else {
    include_once "../includes/conexao_gerar.php";
    $_idUsuario = $_SESSION['idUsuario'];
}

//
//- SÓ PODE VER A FICHA DE QUEM É SUBORDINADO (direto ou indireto) DO GESTOR LOGADO
//
$listaColabs = getSubordinados($_SESSION['idOrgao'] ?? 0, $conn);
if (!in_array((int) $id, $listaColabs, true)) {
    http_response_code(403);
    die("<h1>ACESSO NEGADO</h1>");
}

//
//- SELECIONA DADOS DA PESSOA
//

$sql = "SELECT C.*, P.* , ifnull(EC.categoria,'ND') AS dsEstadoCivil, ifnull( ET.categoria, 'ND') as dsEtnia,
                O.descricao as dsOrgao, CG.nome as dsCargo, F.nome as dsFuncao, CT.descricao as dsTipoContrato,
                S.descricao as dsStatus, GI.descricao as dsGrauInstrucao, B.nome as nmBanco, 
                PS.nomePlano as dsPlanoSaude, PO.nomePlano as dsPlanoOdonto, SS.dsSubSede,
                CONCAT_WS(', ', E.logradouro, CONCAT('nº ', E.numero), E.complemento,
                    E.bairro, CONCAT(E.cidade, ' - ', E.UF), CONCAT('CEP: ', E.cep)
                ) AS dsEnderecoTrab, U1.login as pessoa_login, L1.dtLogin as pessoa_dtLogin,
                U2.login as colab_login, L2.dtLogin as colab_dtLogin,
                (SELECT descricao FROM rh_organograma where idOrgao = O.idSupervisor) as orgaoSupervisor,
                (SELECT P2.nome FROM rh_colaboradores C2 INNER JOIN rh_pessoas P2 on P2.idPessoa = C2.idPessoa 
				    where C2.idOrgao = O.idSupervisor) as nmSupervisor,
                (SELECT P2.email_corporativo FROM rh_colaboradores C2 INNER JOIN rh_pessoas P2 on P2.idPessoa = C2.idPessoa 
				    where C2.idOrgao = O.idSupervisor) as emailSupervisor,
                                (SELECT 1
                    FROM rh_afastamentos
                    WHERE NOW() BETWEEN data_inicio AND data_retorno AND idColab = C.idColab and status ='Aprovado'
                ) as afastado,
                (
                SELECT 1
                    FROM rh_ferias
                    WHERE (
                            (NOW() BETWEEN data_parte1 AND DATE_ADD(data_parte1, INTERVAL dias_parte1 - 1 DAY)
                            AND aprova_rh_1_em IS NOT NULL)
                        OR (NOW() BETWEEN data_parte2 AND DATE_ADD(data_parte2, INTERVAL dias_parte2 - 1 DAY)
                            AND aprova_rh_2_em IS NOT NULL)
                        OR (NOW() BETWEEN data_parte3 AND DATE_ADD(data_parte3, INTERVAL dias_parte3 - 1 DAY)
                            AND aprova_rh_3_em IS NOT NULL)
                        ) AND idColab = C.idColab
                ) as ferias
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT OUTER JOIN rh_estadoCivil EC ON EC.idEstadoCivil = P.idEstadoCivil
            LEFT OUTER JOIN rh_etnias ET ON ET.idEtnia = P.idEtnia
            INNER JOIN rh_organograma O ON O.idOrgao = C.idOrgao
            INNER JOIN rh_cargos CG on CG.idCargo = C.idCargo
            INNER JOIN rh_funcoes F ON F.idFuncao = C.idFuncao
            INNER JOIN rh_contratos_tipo CT ON CT.idContratoTipo = C.idContratoTipo
            INNER JOIN rh_colaboradores_status S ON S.idStatus = C.idStatus
            LEFT OUTER JOIN rh_graus_instrucao GI ON GI.id = P.idGrauEscola
            LEFT OUTER JOIN rh_bancos B on B.id = C.idBanco
            LEFT OUTER JOIN rh_planos_saude PS on PS.idPlano = C.idPlanoSaude and PS.tipoPlano = 1
            LEFT OUTER JOIN rh_planos_saude PO on PO.idPlano = C.idPlanoOdonto and PO.tipoPlano = 2
            LEFT OUTER JOIN rh_subsedes SS on SS.idSubSede = C.idSubSede
            LEFT OUTER JOIN rh_enderecos E on E.idEndereco = C.idEnderecoTrab
            LEFT OUTER JOIN rh_logins L1 ON L1.idLogin = P.idLogin
            LEFT OUTER JOIN rh_usuarios U1 ON U1.idUsuario = L1.idUsuario
            LEFT OUTER JOIN rh_logins L2 ON L2.idLogin = C.idLogin
            LEFT OUTER JOIN rh_usuarios U2 ON U2.idUsuario = L2.idUsuario
            WHERE C.idColab = :idColab";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idColab', $id, PDO::PARAM_STR);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract($dados);

$cpf_formatado = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpf);

if (in_array($idStatus, [1, 2, 3])) {
    $dsStatus = "<span class='badge bg-success w-100'>$dsStatus</span>";
} elseif (in_array($idStatus, [4, 5, 6, 12])) {
    $dsStatus = "<span class='badge bg-warning text-dark w-100'>$dsStatus</span>";
} elseif (in_array($idStatus, [7, 8, 9])) {
    $dsStatus = "<span class='badge bg-danger w-100'>$dsStatus</span>";
} elseif (in_array($idStatus, [10, 11])) {
    $dsStatus = "<span class='badge bg-primary w-100'>$dsStatus</span>";
} elseif ($idStatus == 13) {
    $dsStatus = "<span class='badge bg-purple w-100'>$dsStatus</span>"; // Você pode definir a classe `bg-purple` no CSS
}

?>
<link rel="stylesheet" href="css/ficha_colab.css">
<div>
    <main class="main">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="mb-0">
                    <i class="fa-solid fa-address-card"></i>
                    Dados do Colaborador
                </h2>
                <small style="color:var(--muted)">Informações sobre o Colaborador registradas no RH</small>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href='config.php'><i class="fa-solid fa-gear"></i></a>
                <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>

        <div class="big-card">
            <div class="text-end">
                <button class="btn" style="background:var(--accent);border:0;color:#fff;" onclick='go("equipe.php")'>Voltar</button>
            </div>
        </div>
        <div class='row'>
            <!-- PRIMEIRA COLUNA -->
            <div class='col-sm-7'>

                <!-- DADOS PESSOAIS -->
                <div class="card shadow-lg border-0 mt-2">
                    <div class="card-header text-white cartao clearfix">
                        <div class="float-start">
                            <h5 class="mb-0"><i class="fas fa-id-card-alt"></i> Dados Pessoais</h5>
                        </div>
                        <div class="float-end"><?= "<h5>ID (Pessoa): $idPessoa</h5>" ?></div>
                    </div>
                    <div class="card-body bg-light p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-user"></i> Nome completo:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($nome) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-user-tag"></i> Nome social:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($nomeSocial) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fas fa-calendar-alt"></i> Data Nascimento:</strong>
                                <p class="campo-destaque"><?= date('d/m/Y', strtotime($dtNascimento)) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fas fa-venus-mars"></i> Sexo:</strong>
                                <p class="campo-destaque"><?= $sexo == 'M' ? 'Masculino' : ($sexo == 'F' ? 'Feminino' : 'Não informado') ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fas fa-ring"></i> Estado Civil:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($dsEstadoCivil) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fas fa-globe"></i> Nacionalidade:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($nacionalidade) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fas fa-id-card"></i> CPF:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($cpf_formatado) ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fas fa-id-badge"></i> RG (e órgão emissor):</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($rg) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fas fa-vote-yea"></i> Título de Eleitor:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($titulo_eleitor) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fas fa-vote-yea"></i> CNH:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($cnh) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fas fa-vote-yea"></i> CNH Cat:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($cnh_categoria) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fas fa-vote-yea"></i> CNH Vencimento:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($cnh_vencimento) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-phone"></i> Telefone:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($telefone) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-6 mb-1">
                                <strong><i class="fas fa-envelope"></i> E-mail pessoal:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($email) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-3 mb-1">
                                <strong><i class="fas fa-tshirt"></i> Camiseta:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($camiseta) ?: 'Não informado' ?></p>
                            </div>
                            <div class="col-md-3 mb-1">
                                <strong><i class="fas fa-users"></i> Etnia:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($dsEtnia) ?></p>
                            </div>
                            <div class="col-md-6 mb-1 text-center">
                                <strong><i class="fa-regular fa-font-awesome"></i> Cadastrado em:</strong>
                                <p class="campo-destaque "><?= "$pessoa_dtLogin por $pessoa_login " ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DADOS PROFISSIONAIS -->
                <div class="card shadow-lg border-0 mt-2">
                    <div class="card-header text-white cartao clearfix">
                        <div class="float-start">
                            <h5 class="mb-0"><i class="fas fa-id-card-alt"></i> Dados Profissionais</h5>
                        </div>
                        <div class="float-end"><?= "<h5>ID (Colab): $idColab</h5>" ?></div>
                    </div>
                    <div class="card-body bg-light p-4">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <strong><i class="fa-regular fa-id-card"></i> Matrícula:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($matricula) ?></p>
                            </div>
                            <div class="col-md-2 mb-3">
                                <strong><i class="fa-solid fa-sitemap"></i> ID Orgão:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($idOrgao) ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fa-solid fa-sitemap"></i> Orgão:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($dsOrgao) ?></p>
                            </div>
                            <div class="col-md-2 mb-3">
                                <strong><i class="fa-solid fa-star"></i> Lider?</strong>
                                <p class="campo-destaque"><?= $dcLider == 1 ? "Sim" : "Não" ?></p>
                            </div>
                            <div class="col-md-2 mb-3">
                                <strong><i class="fa-solid fa-power-off"></i> Status?</strong>
                                <p class="campo-destaque text-center"><?= ($dsStatus) ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fa-solid fa-sitemap"></i> Cargo:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($dsCargo) ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fa-solid fa-sitemap"></i> Função:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($dsFuncao) ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fa-solid fa-file-contract"></i> Tipo de Contrato:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($dsTipoContrato) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-regular fa-calendar-check"></i> Admissão:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($data_admissao) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-sack-dollar"></i> Salário Base:</strong>
                                <p class="campo-destaque"><?= number_format($salario_base, 2, ',', '.') ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-hourglass-start"></i> Carga Horária:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($carga_horaria) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-regular fa-clock"></i> Horário:</strong>
                                <p class="campo-destaque"><?= $horario_ini . " - " . $horario_fim ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-regular fa-id-badge"></i> PIS:</strong>
                                <p class="campo-destaque"><?= $pis ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-regular fa-id-badge"></i> CTPS:</strong>
                                <p class="campo-destaque">
                                    <?= $ctps ?>
                                    <?php if (! empty($arquivo_ctps))
                                        echo "<a href='#!' onclick='f_mostra(1, $idPessoa, `$arquivo_ctps` )'><i class='fa-regular fa-file-pdf'></i></a>" ?>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fa-solid fa-person-dress"></i> Nome da Mãe:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($nome_mae) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-graduation-cap"></i> Escolaridade:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($dsGrauInstrucao) ?></p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong><i class="fa-solid fa-building-columns"></i> Banco:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($nmBanco) ?></p>
                            </div>
                            <div class="col-md-2 mb-3">
                                <strong><i class="fa-solid fa-building-columns"></i> Agência:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($bco_agencia) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-building-columns"></i> Conta Corrente:</strong>
                                <p class="campo-destaque"><?= htmlspecialchars($bco_cc) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-credit-card"></i> Vl Transporte :</strong>
                                <p class="campo-destaque"><?= empty($vale_transporte) ? "Nenhum" : $vale_transporte ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-credit-card"></i> Refeição :</strong>
                                <p class="campo-destaque"><?= empty($vale_refeicao) ? "Nenhum" : $vale_refeicao ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-suitcase-medical"></i> Plano Saúde :</strong>
                                <p class="campo-destaque"><?= empty($dsPlanoSaude) ? "Nenhum" : $dsPlanoSaude ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-tooth"></i> Plano Odonto :</strong>
                                <p class="campo-destaque"><?= empty($dsPlanoOdonto) ? "Nenhum" : $dsPlanoOdonto ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-building"></i> SubSede :</strong>
                                <p class="campo-destaque"><?= empty($dsPlanoOdonto) ? "Nenhum" : $dsSubSede ?></p>
                            </div>
                            <div class="col-md-9 mb-3">
                                <strong><i class="fa-solid fa-building"></i> Endereço de Trabalho :</strong>
                                <p class="campo-destaque"><?= empty($idEnderecoTrab) ? "O mesmo da SubSede" : $dsEnderecoTrab ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-regular fa-hourglass"></i> Jornada :</strong>
                                <p class="campo-destaque"><?= jornada($idJornada) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-regular fa-hourglass"></i> Forma Pagto :</strong>
                                <p class="campo-destaque"><?= forma_pagamento($idTipoForma) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-regular fa-hourglass"></i> Prazo Contrato :</strong>
                                <p class="campo-destaque"><?= tipo_prazo($idTipoPrazo) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <strong><i class="fa-solid fa-mobile-screen-button"></i> Celular Corporativo :</strong>
                                <p class="campo-destaque"><?= empty($celular_corporativo) ? "ND" : htmlspecialchars($celular_corporativo) ?></p>
                            </div>

                            <div class="col-md-4 mb-1">
                                <strong><i class="fa-solid fa-sitemap"></i> Orgão Supervisor:</strong>
                                <p class="campo-destaque "><?= "$orgaoSupervisor" ?></p>
                            </div>
                            <div class="col-md-4 mb-1">
                                <strong><i class="fa-regular fa-circle-user"></i> Supervisor:</strong>
                                <p class="campo-destaque "><?= "$nmSupervisor" ?></p>
                            </div>
                            <div class="col-md-4 mb-1">
                                <strong><i class="fa-solid fa-envelope"></i> e-mail do Supervisor:</strong>
                                <p class="campo-destaque "><?= "$emailSupervisor" ?></p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong><i class="fa-solid fa-envelope"></i> e-Mail Corporativo :</strong>
                                <p class="campo-destaque"><?= empty($email_corporativo) ? "ND" : htmlspecialchars($email_corporativo) ?></p>
                            </div>
                            <div class="col-md-6 mb-1 text-center">
                                <strong><i class="fa-regular fa-font-awesome"></i> Cadastrado em:</strong>
                                <p class="campo-destaque "><?= "$colab_dtLogin por $colab_login " ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DADOS DEPENDENTES -->
                <div class="card shadow-lg border-0 mt-2">
                    <div class="card-header text-white cartao clearfix">
                        <div class="float-start">
                            <h5 class="mb-0"><i class="fa-solid fa-users"></i> Dependentes</h5>
                        </div>
                        <div class="float-end"></div>
                    </div>
                    <div class="card-body bg-light p-4">
                        <table class='table table-striped tabke-hover w-100' id='tabela_dependentes'>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Parentesco</th>
                                    <th>Idade</th>
                                    <th>IR</th>
                                    <th>P.Saúde</th>
                                    <th>P.Odonto</th>
                                    <th>Creche</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?PHP
                                $sql = "SELECT D.*, P.nome as nmDependente, F.dsParentesco,
                                                TIMESTAMPDIFF(YEAR, dataNascimento, CURDATE()) AS idade
                                                FROM rh_dependentes D
                                                INNER JOIN rh_pessoas P ON P.idPessoa = D.idPessoaDep
                                                INNER JOIN rh_parentescos F on F.idParentesco = D.idParentesco
                                                WHERE D.idColab = :idColab";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':idColab', $idColab, PDO::PARAM_STR);
                                $stmt->execute();
                                //
                                while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    extract($linha);
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($nmDependente) ?></td>
                                        <td><?= htmlspecialchars($dsParentesco) ?></td>
                                        <td><?= htmlspecialchars($idade) ?></td>
                                        <td><?= $ir == 1 ? "Sim" : "Não" ?></td>
                                        <td><?= $usaPlanoSaude == 1 ? "Sim" : "Não" ?></td>
                                        <td><?= $usaPlanoOdonto == 1 ? "Sim" : "Não" ?></td>
                                        <td><?= $usaCreche == 1 ? "Sim" : "Não" ?></td>
                                    <?php
                                }
                                    ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ENDEREÇOS -->
                <?php
                $sql = "SELECT E.*, T.dsTipoEndereco, L.dtLogin, U.login
                                FROM RH.rh_enderecos E
                                INNER JOIN rh_enderecos_tipo T on T.idTipoEndereco = E.idTipoEndereco
                                INNER JOIN rh_logins L on L.idLogin = E.idLogin
                                INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                                WHERE E.idPessoa = :idPessoa";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':idPessoa', $id, PDO::PARAM_STR);
                $stmt->execute();
                ?>
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white cartao">
                        <h5 class="mb-0"><i class="fa-solid fa-map"></i> ENDEREÇOS</h5>
                    </div>
                    <div class="card-body bg-light p-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="fas fa-map-marker-alt"></i> Classe</th>
                                    <th><i class="fas fa-road"></i> Logradouro</th>
                                    <th><i class="fas fa-envelope"></i> CEP</th>
                                    <th><i class="fas fa-city"></i> Cidade/UF</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    extract($linha);
                                    $endereco = trim("$logradouro, $numero, $complemento, $bairro", ", ");
                                    $cidadeuf = "$cidade/$uf";
                                    if (! empty($cep)) $cep = preg_replace("/^(\d{5})(\d{3})$/", "$1-$2", $cep);
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($dsTipoEndereco) ?></td>
                                        <td><?= htmlspecialchars($endereco) ?: 'Não informado' ?></td>
                                        <td><?= htmlspecialchars($cep) ?: 'Não informado' ?></td>
                                        <td><?= htmlspecialchars($cidadeuf) ?: 'Não informado' ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CONTATOS DE EMERGÊNCIA -->
                <?php
                $sql = "SELECT * FROM rh_pessoas_emg
                                WHERE idPessoa = :idPessoa";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':idPessoa', $id, PDO::PARAM_STR);
                $stmt->execute();
                ?>
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white cartao">
                        <h5 class="mb-0"><i class="fa-solid fa-truck-medical"></i> Contatos de Emergência</h5>
                    </div>
                    <div class="card-body bg-light p-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="fas fa-user"></i> Pessoa</th>
                                    <th><i class="fas fa-road"></i> Logradouro</th>
                                    <th><i class="fas fa-phone"></i> Telefone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    extract($linha);
                                    $pessoa = "$grau <br> $nome";
                                    $telefone = !empty($telefone) ? $telefone : "Não Informado";
                                    $celular = !empty($celular) ? $celular : "Não Informado";
                                    $telefones = $telefone . "<br>" . $celular;
                                ?>
                                    <tr>
                                        <td><?= $pessoa ?></td>
                                        <td><?= htmlspecialchars($endereco) ?: 'Não informado' ?></td>
                                        <td><?= $telefones ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ANEXOS  -->
                <?php
                $sql = "SELECT D.* , T.nome as dsTipoDoc
                                FROM rh_documentos D
                                INNER JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
                                WHERE idPessoa = :idPessoa";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':idPessoa', $id, PDO::PARAM_STR);
                $stmt->execute();
                ?>
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white cartao">
                        <h5 class="mb-0"><i class="fa-regular fa-folder-open"></i> Anexos</h5>
                    </div>
                    <div class="card-body bg-light p-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="fa-regular fa-font-awesome"></i> Tipo</th>
                                    <th><i class="fas fa-file"></i> Arquivo</th>
                                    <th><i class="fas fa-calendar"></i>Validade</th>
                                    <th style="text-align: center;"><i class="fa-solid fa-circle-down"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    extract($linha);
                                    $acao = "<button class='btn btn-sm btn-outline-primary' onclick=\"f_mostra($idDoc, $idPessoa, '$arquivo')\"><i class='fa-solid fa-magnifying-glass'></i></button>";
                                    $acao .= "<button class='btn btn-sm btn-outline-secondary' onclick=\"f_down($idDoc, $idPessoa, '$arquivo')\"><i class='fa-solid fa-download'></i></button>";
                                    $acao .= "<button class='btn btn-sm btn-outline-success' onclick=\"f_email($idDoc, $idPessoa, '$arquivo')\"><i class='fa-regular fa-envelope'></i></button>";
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($dsTipoDoc) ?></td>
                                        <td><?= htmlspecialchars($arquivo) ?></td>
                                        <td style='text-align: center;' class='nowrap'><?= htmlspecialchars($data_validade) ?></td>
                                        <td style='text-align: center;' class='nowrap '><?= $acao ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- SEGUNDA COLUNA -->
            <div class='col-sm-5'>
                <div class="card mt-2">
                    <div class="card-header text-white cartao clear-fix">
                        <div class="float-start">
                            <h5 class='text-center'><i class="fa-regular fa-hourglass"></i>&nbsp;LINHA DO TEMPO da Pessoa</h5>
                        </div>
                        <div class="float-end">
                            <button class='btn btn-sm btn-outline-dark' onclick='inclui_ldt()'>Incluir na linha do tempo</button>
                        </div>

                    </div>
                    <div class="card-body container" style="font-size: 13px;">
                        <?php
                        $sql = "SELECT A.*, T.*, U.login 
                                        FROM RH.rh_pessoas_ldt A 
                                        INNER JOIN rh_ldt_tipos T on T.idAcaoTipo = A.idAcaoTipo
                                        INNER JOIN rh_usuarios U on U.idUsuario = A.idUsuario
                                        WHERE A.idPessoa = :idPessoa";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_STR);
                        $stmt->execute();
                        ?>
                        <table class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th><i class="fa-solid fa-circle-down"></i></th>
                                    <th style="width: 150px;">Em</th>
                                    <th>Descrição</th>
                                    <th class='text-end'><i class="fa-solid fa-bolt"></i> Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?PHP
                                while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    extract($linha);
                                    $descricao = strip_tags($descricao);
                                    //
                                    //- e-Mail Enviado
                                    if ($idAcaoTipo == 5 && $idEmail > 0) {
                                        $link = "<a href='#!' class='btn btn-sm btn-outline-success' onClick='f_mostra_email($idEmail)'><i class='fa-solid fa-magnifying-glass'></i></a>";
                                    } else {
                                        $link = "<a href='#!' class='btn btn-sm btn-outline-success' onClick='f_mostra_cartao($idAcao)'><i class='fa-solid fa-magnifying-glass'></i></a>";;
                                    }
                                    if ($idUsuario == $_idUsuario) {
                                        $link_del = "<a href='#!' class='btn btn-sm btn-outline-danger' onClick='f_del($idAcao)'><i class='fa-solid fa-trash'></i></a>";
                                    } else {
                                        $link_del = "";
                                    }

                                    //
                                    echo "<tr>
                                            <td><i class='fa $icone icon' style='color: $cor'></i></td>
                                            <td>$data<br>Por: $login</td>
                                            <td>$descricao</td>
                                            <td class='text-end' style=' white-space: nowrap;'>$link_del $link</td>
                                        </tr>";
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- FIM DA ROW -->
    </main>

    <!-- Modal Visualizar Documento -->
    <div class="modal fade" id="modalArquivo" tabindex="-1" aria-labelledby="modalArquivoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalArquivoLabel">Visualização de Arquivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <iframe id="iframeArquivo" src="" width="100%" height="600px" style="border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Envio de E-mail -->
    <div class="modal fade" id="modalEmail" tabindex="-1" aria-labelledby="modalEmailLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEmailLabel">
                        <i class="fa-solid fa-envelope"></i> Enviar Documento por E-mail
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm">
                        <input type="hidden" id="idPessoa" name="idPessoa" value='<?= $idPessoa ?>'>
                        <input type="hidden" id="idDoc" name="idDoc">
                        <input type="hidden" id="arquivo" name="arquivo">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="emailDestinatario" class="form-label"><strong>Destinatário</strong></label>
                                <input type="email" class="form-control" id="emailDestinatario" name='emailDestinatario' required>
                            </div>
                            <div class="col-md-4">
                                <label for="emailCC" class="form-label"><strong>CC</strong></label>
                                <input type="email" class="form-control" id="emailCC" name='emailCC'>
                            </div>
                            <div class="col-md-4">
                                <label for="emailCCO" class="form-label"><strong>CCO</strong></label>
                                <input type="email" class="form-control" id="emailCCO" name='emailCCO'>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label for="emailTitulo" class="form-label"><strong>Título</strong></label>
                                <input type="text" class="form-control" id="emailTitulo" value="GERAR (RH) - Envio de documento" name='emailTitulo'>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label for="emailCorpo" class="form-label"><strong>Corpo do E-mail</strong></label>
                                <textarea class="form-control" id="emailCorpo" name='emailCorpo' rows="5"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class='col-sm-6'></div>
                            <div class="col-sm-6 d-flex">
                                <button type='button' class='btn btn-sm btn-outline-danger w-100 m-2' data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Fechar</button>
                                <button type='reset' class='btn btn-sm btn-outline-secondary w-100 m-2'><i class="fa-solid fa-recycle"></i> Reset</button>
                                <button type='button' class='btn btn-sm btn-outline-success w-100 m-2' onclick="f_email_commit()"><i class="fa-solid fa-share"></i> Enviar</button>
                            </div>
                        </div>
                        <div id='msgEmail'></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Visualizar E-mail -->
    <div class="modal fade" id="modalVisualizarEmail" tabindex="-1" aria-labelledby="modalVisualizarEmailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalVisualizarEmailLabel">
                        <i class="fa-solid fa-envelope"></i> Visualizar E-mail Enviado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Destinatário -->
                        <div class="mb-3">
                            <label class="form-label"><strong>📩 Destinatário:</strong></label>
                            <div class="form-control" id="v_emailDestinatario"></div>
                        </div>

                    </div>
                    <div class="row">
                        <!-- CC (Com Cópia) -->
                        <div class="col-sm-6 mb-3">
                            <label class="form-label"><strong>📑 CC:</strong></label>
                            <div class="form-control" id="v_emailCC"></div>
                        </div>

                        <!-- CCO (Com Cópia Oculta) -->
                        <div class="col-sm-6 mb-3">
                            <label class="form-label"><strong>🔒 CCO:</strong></label>
                            <div class="form-control" id="v_emailCCO"></div>
                        </div>
                    </div>

                    <!-- Assunto -->
                    <div class="mb-3">
                        <label class="form-label"><strong>📌 Assunto:</strong></label>
                        <div class="form-control" id="v_emailAssunto"></div>
                    </div>

                    <!-- Corpo do E-mail -->
                    <div class="mb-3">
                        <label class="form-label"><strong>📝 Corpo do E-mail:</strong></label>
                        <div class="form-control bg-light p-2" id="v_emailCorpo"
                            style="height: 120px; overflow-y: auto; white-space: pre-wrap;"></div>
                    </div>

                    <!-- Anexo -->
                    <div class="mb-3">
                        <label class="form-label"><strong>📎 Anexo:</strong></label>
                        <div class="d-flex align-items-center border p-2 rounded">
                            <span id="v_emailAnexo" class="flex-grow-1">Nenhum anexo</span>
                            <span id='v_botao'>
                                <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="btnVisualizarAnexo">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                                </spam>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal INCLUIR LINHA DO TEMPO -->
    <div class="modal fade" id="modalLinhaTempo" tabindex="-1" aria-labelledby="modalLinhaTempoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalLinhaTempoLabel"><i class="fa-solid fa-clock"></i> Registrar Linha do Tempo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formLinhaTempo">
                        <div class="row">
                            <!-- Tipo da Ação -->
                            <div class="col-md-7 mb-3" id="seletorAcao">
                                <label for="idAcaoTipo" class="form-label text-dark"><i class="fa-solid fa-list"></i> Tipo de Ação</label>
                                <?php echo seletor_tipo($conn); ?>
                            </div>

                            <!-- Data -->
                            <?php $agora = date("Y-m-d\TH:i"); ?>
                            <div class="col-md-5 mb-3">
                                <label for="data" class="form-label text-dark"><i class="fa-solid fa-calendar-day"></i> Data</label>
                                <input type="datetime-local" class="form-control form-sm text-center" id="data" name="data" value='<?= $agora ?>' required>
                            </div>

                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label for="descricao" class="form-label text-dark"><i class="fa-solid fa-align-left"></i> Descreva o evento que deseja registrar</label>
                            <textarea class="form-control text-dark" id="descricao" name="descricao"></textarea>
                        </div>
                        <input type="hidden" name='idEmpresa' value="<?= $idEmpresa ?>">
                    </form>
                </div>
                <div id="msgLinhaDoTempo" class='h5 text-center'></div>
                <div class="modal-footer" id="botoesLDT">
                    <button type="reset" class="btn btn-outline-secondary" onclick="$('#descricao').summernote('reset');" id='btnReset'><i class="fa-solid fa-eraser"></i> Reset</button>
                    <button type="button" class="btn btn-outline-success" onclick="salvarLinhaTempo()"><i class="fa-solid fa-save"></i> Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal VISUALIZAR LINHA DO TEMPO -->
    <div class="modal fade" id="modalLinhaTempoVer" tabindex="-1" aria-labelledby="modalLinhaTempoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" style='background-color:darkslategray'>
                    <h5 class="modal-title" id="modalLinhaTempoLabel"><i class="fa-solid fa-clock"></i> Registrar Linha do Tempo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="xformLinhaTempo">
                        <div class="row">
                            <!-- Tipo da Ação -->
                            <div class="col-md-7 mb-3" id="seletorAcao">
                                <label for="xidAcaoTipo" class="form-label"><i class="fa-solid fa-list"></i> Tipo de Ação</label>
                                <?php echo seletor_tipo($conn); ?>
                            </div>

                            <!-- Data -->
                            <?php $agora = date("Y-m-d\TH:i"); ?>
                            <div class="col-md-5 mb-3">
                                <label for="xdata" class="form-label"><i class="fa-solid fa-calendar-day"></i> Data</label>
                                <input type="datetime-local" class="form-control form-sm text-center" id="xdata" name="xdata" value='' required>
                            </div>

                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label for="xdescricao" class="form-label"><i class="fa-solid fa-align-left"></i> Descreva o evento que deseja registrar</label>
                            <textarea class="form-control" id="xdescricao" name="xdescricao"></textarea>
                        </div>
                        <input type="hidden" name='idAcao' id='idAcao'>
                    </form>
                </div>
                <div id="xmsgLinhaDoTempo" class='h5 text-center'></div>
                <div class="modal-footer" id="xbotoesLDT">
                    <button type="reset" class="btn btn-outline-warning" onclick="$('#xdescricao').summernote('reset');" id='xbtnReset'>
                        <i class="fa-solid fa-eraser"></i> Reset
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times"></i> Fechar
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="xsalvarLinhaTempo()" id='xbtnSalvar'>
                        <i class="fa-solid fa-save"></i> Salvar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Aqui Termina o conteúdo da página -->
</div>

<!--<script src="js/scripts.js"></script> -->
<script src="../js/cpf.js"></script>
<script src="js/ficha_colab.js"></script>
</body>

</html>
<?PHP

function seletor_tipo($conn)
{
    global $conn;
    $sql = "SELECT * FROM rh_ldt_tipos ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $retorno = "<select class='form-select form-select-sm' id='idTipoAcao' name='idTipoAcao'>";
    if (empty($id)) $retorno .= "<option value='0' selected>Tipo de Atividade</option>";
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($linha);
        $retorno .= "<option value='$idAcaoTipo' style='color: $cor'>$nome</option>";
    }
    $retorno .= "</select>";
    return $retorno;
}

function jornada($id)
{
    if ($id == "0") return "Nenhum";
    if ($id == "D") return "Diária";
    if ($id == "S") return "Semanal";
    if ($id == "M") return "Mensal";
}

function forma_pagamento($id)
{
    if ($id == "0") return "Nenhum";
    if ($id == "H") return "Hora";
    if ($id == "S") return "Semanal";
    if ($id == "M") return "Mensal";
}

function tipo_prazo($id)
{
    if ($id == "0") return "Nenhum";
    if ($id == "I") return "Indeterminado";
    if ($id == "D") return "Determinado";
}

function getSubordinados($idOrgao, $pdo) {
    // 1. Buscar a linha do organograma do gestor
    $sql = "SELECT * FROM rh_organograma WHERE idOrgao = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idOrgao]);
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gestor) {
        return [];
    }

    // 2. Descobrir em qual nível o gestor está (último nível > 0)
    $nivelGestor = 0;
    for ($i = 1; $i <= 7; $i++) {
        if (!empty($gestor["nivel_$i"]) && $gestor["nivel_$i"] > 0) {
            $nivelGestor = $i;
        }
    }

    // 3. Montar condição dinâmica para os níveis anteriores
    $conds = [];
    $params = [];

    for ($i = 1; $i <= $nivelGestor; $i++) {
        $conds[] = "O.nivel_$i = :n$i";
        $params[":n$i"] = $gestor["nivel_$i"];
    }

    // 4. O próximo nível precisa ser > 0
    $proximoNivel = $nivelGestor + 1;
    if ($proximoNivel <= 7) {
        $conds[] = "O.nivel_$proximoNivel > 0";
    }

    // 5. Montar SQL final
    $where = implode(" AND ", $conds);

    $sql = "
        SELECT C.idColab
        FROM rh_colaboradores C
        INNER JOIN rh_organograma O ON O.idOrgao = C.idOrgao
        WHERE $where
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 6. Extrair apenas os IDs em um vetor simples
    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row["idColab"];
    }

    return $ids;
}

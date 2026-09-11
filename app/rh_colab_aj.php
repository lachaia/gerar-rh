<?php
//
// rh_colab_aj.php | GRID dos COLABORADORES
// by (C)haia, 20/03/2025
//
session_start();

$idUsuario = $_SESSION['idUsuario'];
$idModulo = 4; // COLABORADORES

include_once __DIR__ . "/includes/conexao_gerar.php";

//- Obter dados a serem apresentados

$pesquisa = "SELECT C.idColab, C.matricula, P.nome, O.idOrgao, O.nivel, O.staff, O.descricao as dsOrgao, T.descricao as dsContratoTipo, 
                    data_admissao, P.idPessoa, S.descricao as dsStatus, S.cor_status, C.dcLider, F.nome as dsFuncao,
                    (select count(*) from rh_dependentes where idColab = C.idColab) as qtdDependentes,
                    E.idColab as ctrExp,
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
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                INNER JOIN rh_contratos_tipo T on T.idContratoTipo = C.idContratoTipo
                INNER JOIN rh_colaboradores_status S on S.idStatus = C.idStatus
                LEFT OUTER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
                LEFT OUTER JOIN rh_ctr_exp E on E.idColab = C.idColab AND now() <= E.data_fim";
                //
$stmt = $conn->prepare($pesquisa);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();


//- EXCUTAR A QUERY

try {
    $stmt = $conn->prepare($pesquisa);
    $stmt->execute();
    // Aqui você pode continuar com o processamento dos resultados
} catch (PDOException $e) {
    // Caso ocorra algum erro na execução da consulta
    echo "Erro: " . $e->getMessage();
}

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    //

    $qtdEmails = 0;
    if (! $qtdEmails > 0) {
        $qtdEmails = "";
    } else {
        $qtdEmails = "<span style='font-size: 7px; position: absolute; bottom: 0; right: 1; color: blue'><b>$qtdEmails</b></span>";
    }

    $acoes = '<div class="btn-group">';
    $acoes .= '<button type="button" class="btn btn-outline-primary btn-sm" onClick="f_ver('     . $idColab . ')">' . '<i class="fas fa-search" data-bs-toggle="tooltip" title="Visualizar!"></i> ' . "</button>";
    $acoes .= '<button type="button" class="btn btn-outline-warning btn-sm" onClick="f_editar('  . $idColab . ')">' . '<i class="fas fa-edit" data-bs-toggle="tooltip" title="Editar!"></i> ' . "</button>";
    $acoes .= '<button type="button" class="btn btn-outline-danger  btn-sm" onClick="f_excluir(' . $idColab . ')">' . '<i class="far fa-trash-alt" data-bs-toggle="tooltip" title="Excluir!"></i> ' . "</button>";

    if (empty($email)) {
        $acoes .= '<button type="button" class="btn btn-outline-secondary btn-sm"><i class="fa-regular fa-envelope"></i></button>';
    } else {
        $acoes .= "<button type='button' class='btn btn-outline-success btn-sm' onClick='f_email($idColab, \"$email\")'><i class='fa-regular fa-envelope'></i>$qtdEmails</button>";
    }
    $acoes .= '</div>';
    //
    if( $dcLider==1) $dsLider = "<i class='fa-solid fa-star text-primary' data-bs-toggle='tooltip' title='Líder!'></i>";
    else $dsLider = "<i class='fa-solid fa-user' data-bs-toggle='tooltip' title='Colaborador!'></i>";
    //
    if( $staff==0 && $nivel<6) {
        $dsOrgao = "<i class='fa-solid fa-user-tie text-danger' data-bs-toggle='tooltip' title='Gestor!'></i> $dsOrgao";
        $dsLider = "<i class='fa-solid fa-star text-success' data-bs-toggle='tooltip' title='Líder!'></i>";
    } else {
        $dsOrgao = "<i class='fa-solid fa-users text-primary' data-bs-toggle='tooltip' title='Colaborador!'></i> $dsOrgao";
    }
    //
    if ($dsStatus == 'Ativo' && $ctrExp > 0){
        $dsStatus = "<span class='badge bg-warning text-dark w-100 status'>Experiência</span>";
    }else{
        $dsStatus = "<span class='badge $cor_status w-100 status'>$dsStatus</span>";
    }
    if( $ferias == 1) $dsStatus = "<span class='badge bg-dark text-light w-100 status'><i class='fa-solid fa-plane'></i> Férias</span>";
    if( $afastado == 1) $dsStatus = "<span class='badge bg-warning text-dark w-100 status'>Afastado</span>";
    //
    $dado = array();
    $dado[] = $idColab;
    $dado[] = $nome;
    $dado[] = $matricula;
    $dado[] = str_pad($idOrgao, 3, '0', STR_PAD_LEFT) . "-" . $dsOrgao;
    $dado[] = $dsFuncao;
    $dado[] = $dsContratoTipo!='CLT' ? "<span class='badge bg-warning text-dark w-100 status'>$dsContratoTipo</span>" : $dsContratoTipo;
    $dado[] = $data_admissao;
    $dado[] = $dsStatus;
    $dado[] = $dsLider;
    $dado[] = ($qtdDependentes>0) ? "<kbd>$qtdDependentes</kbd>" : "-";
    $dado[] = $acoes;
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript
//

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);

$conteudo = json_encode($output);
echo $conteudo;

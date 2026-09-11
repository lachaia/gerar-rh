<?php
//
//- index_aj1.php | Dashboard - Calcula valores para os Cards do index.php
//- (C)haia, 27/03/24

session_start();

if (isset($_POST['idSubSede'])) {
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT); //- dados do FORMULÁRIO.
    extract($dados);
} else {
    $idSubSede = 0;
}

include "includes/conexao_gerar.php";

$retorno = [];

//$idSubSede = 101;

//
//- Quantidade de Colaboradores Ativos
//

    $sql = "select count(idColab) as qtd from rh_colaboradores where data_rescisao is null";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados =  $stmt->fetch(PDO::FETCH_ASSOC);
    //
    $retorno['qtdAtivos'] = number_format($dados['qtd'], 0, ',', ".");

//
//- Quantidade de Desligados
//
    $sql = "select count(idColab) as qtd from rh_colaboradores where data_rescisao is not null";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados =  $stmt->fetch(PDO::FETCH_ASSOC);
    //
    $retorno['qtdDesligados'] = number_format($dados['qtd'], 0, ',', ".");

//
//- Quantidade de Currículos
//
    $sql = "select count(idPessoa) as qtd from rh_cv ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados =  $stmt->fetch(PDO::FETCH_ASSOC);
    $retorno['qtdCurriculos'] = number_format($dados['qtd'], 0, ',', ".");

// 
//- Quantidade de Afastados
//

    $sql = "SELECT COUNT(*) AS qtd
                    FROM rh_afastamentos
                    WHERE CURDATE() BETWEEN data_inicio AND data_retorno";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados =  $stmt->fetch(PDO::FETCH_ASSOC);
    $retorno['qtdAfastados'] = number_format($dados['qtd'], 0, ',', ".");

    //$retorno['qtdAfastados'] = 0;

// 
//- Quantidade em Férias HOJE
//
    $sql = "SELECT COUNT(DISTINCT idColab) AS qtd
            FROM rh_ferias
            WHERE
                (
                    data_parte1 IS NOT NULL AND
                    CURDATE() BETWEEN data_parte1 AND DATE_ADD(data_parte1, INTERVAL dias_parte1 - 1 DAY)
                )
                OR
                (
                    data_parte2 IS NOT NULL AND
                    CURDATE() BETWEEN data_parte2 AND DATE_ADD(data_parte2, INTERVAL dias_parte2 - 1 DAY)
                )
                OR
                (
                    data_parte3 IS NOT NULL AND
                    CURDATE() BETWEEN data_parte3 AND DATE_ADD(data_parte3, INTERVAL dias_parte3 - 1 DAY)
                );
            ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados =  $stmt->fetch(PDO::FETCH_ASSOC);
    $retorno['qtdFerias'] = number_format($dados['qtd'], 0, ',', ".");

//
//- POR GÊNERO
//
    $sql = "SELECT 
                CASE P.sexo
                    WHEN 'M' THEN 'Masculino'
                    WHEN 'F' THEN 'Feminino'
                    ELSE 'Não informado'
                END AS sexo,
                COUNT(*) AS total,
                ROUND(
                    COUNT(*) * 100.0 / (SELECT COUNT(*) 
                                        FROM rh_colaboradores C2
                                        INNER JOIN rh_pessoas P2 ON P2.idPessoa = C2.idPessoa
                                        WHERE C2.data_rescisao IS NULL),
                    1
                ) AS percentual
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
            WHERE C.data_rescisao IS NULL
            GROUP BY P.sexo;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vetorGenero = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $retorno['vetorGenero'] = $vetorGenero;

    // Monta o HTML do card
    $htmlGenero = "<div class='row text-center'>";

    foreach ($vetorGenero as $g) {
        $icone = ($g['sexo'] == 'Masculino') ? "<i class='fa-solid fa-person me-2 text-primary'></i>" : (($g['sexo'] == 'Feminino') ? "<i class='fa-solid fa-person-dress me-2 text-danger'></i>" :
                "<i class='fa-solid fa-question me-2 text-secondary'></i>");

        $corTexto = ($g['sexo'] == 'Masculino') ? "text-info" : (($g['sexo'] == 'Feminino') ? "text-pink" : "text-secondary");

        $htmlGenero .= "
            <div class='col-6 border-end border-secondary'>
                <div class='fw-bold fs-5 $corTexto'>$icone {$g['sexo']}</div>
                <div class='display-6 fw-bold text-light'>{$g['total']}</div>
                <div class='small text-muted'>{$g['percentual']}%</div>
            </div>
        ";
    }
    $htmlGenero .= "</div>";
    $retorno['divGenero'] = $htmlGenero;

//
//- CÁLCULO DO TURNOVER
//
    // Últimos 12 meses
    $data_fim = new DateTime(); // hoje
    $data_ini = (clone $data_fim)->modify('-12 months');

    // Formata para YYYY-MM-DD (MySQL)
    $data_ini_str = $data_ini->format('Y-m-d');
    $data_fim_str = $data_fim->format('Y-m-d');

    $sql = "SELECT 
    ROUND(
        (
            (
                (SELECT COUNT(*) 
                 FROM rh_colaboradores 
                 WHERE data_admissao BETWEEN '$data_ini_str' AND '$data_fim_str')
                +
                (SELECT COUNT(*) 
                 FROM rh_colaboradores 
                 WHERE data_rescisao BETWEEN '$data_ini_str' AND '$data_fim_str')
            ) / 2
        )
        /
        (SELECT COUNT(*) 
         FROM rh_colaboradores 
         WHERE data_rescisao IS NULL)
        * 100,
    1) AS turnover_percentual;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados =  $stmt->fetch(PDO::FETCH_ASSOC);
    $retorno['turnover_percentual'] = $dados['turnover_percentual'];

//
//- CÁLCULO DA MÉDIA DE IDADE
//
    $sql = "SELECT 
        ROUND(AVG(TIMESTAMPDIFF(YEAR, P.dtNascimento, CURDATE())), 1) AS idade_media_geral,
        ROUND(AVG(CASE WHEN P.sexo = 'M' THEN TIMESTAMPDIFF(YEAR, P.dtNascimento, CURDATE()) END), 1) AS idade_media_masculina,
        ROUND(AVG(CASE WHEN P.sexo = 'F' THEN TIMESTAMPDIFF(YEAR, P.dtNascimento, CURDATE()) END), 1) AS idade_media_feminina
    FROM rh_colaboradores C
    INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
    WHERE C.data_rescisao IS NULL AND P.dtNascimento is not null;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vetorIdade = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $retorno['vetorIdade'] = $vetorIdade;

    // Monta o HTML do card de média de idade
    $dadosIdade = $vetorIdade[0]; // só vem uma linha

    $htmlIdade = "<div class='row text-center'>";

    $htmlIdade .= "
        <div class='col-4 border-end border-secondary'>
            <div class='fw-bold text-light'>Geral</div>
            <div class='display-6 fw-bold text-white'>{$dadosIdade['idade_media_geral']}</div>
        </div>
        <div class='col-4 border-end border-secondary'>
            <div class='fw-bold text-primary'>Masculino</div>
            <div class='display-6 fw-bold text-primary'>{$dadosIdade['idade_media_masculina']}</div>
        </div>
        <div class='col-4'>
            <div class='fw-bold text-pink'>Feminino</div>
            <div class='display-6 fw-bold text-pink'>{$dadosIdade['idade_media_feminina']}</div>
        </div>
    ";

    $htmlIdade .= "</div>";

    // Retorna via AJAX
    $retorno['divMediaIdade'] = $htmlIdade;

//
//- CALCULA O TEMPO MÉDIO DE CASA
//
    $sql = "SELECT 
            ROUND(AVG(TIMESTAMPDIFF(YEAR, C.data_admissao, CURDATE())), 1) AS tempo_casa_geral,
            ROUND(AVG(CASE WHEN P.sexo = 'M' THEN TIMESTAMPDIFF(YEAR, C.data_admissao, CURDATE()) END), 1) AS tempo_casa_masculino,
            ROUND(AVG(CASE WHEN P.sexo = 'F' THEN TIMESTAMPDIFF(YEAR, C.data_admissao, CURDATE()) END), 1) AS tempo_casa_feminino
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
        WHERE C.data_rescisao IS NULL AND C.data_admissao IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vetorTempo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dadosTempo = $vetorTempo[0]; // apenas uma linha

    // Monta HTML do card, mesmo padrão da média de idade
    $htmlTempo = "<div class='row text-center'>";

    $htmlTempo .= "
        <div class='col-4 border-end border-secondary'>
            <div class='fw-bold text-light'>Geral</div>
            <div class='display-6 fw-bold text-white'>{$dadosTempo['tempo_casa_geral']}</div>
        </div>
        <div class='col-4 border-end border-secondary'>
            <div class='fw-bold text-primary'>Masculino</div>
            <div class='display-6 fw-bold text-primary'>{$dadosTempo['tempo_casa_masculino']}</div>
        </div>
        <div class='col-4'>
            <div class='fw-bold text-pink'>Feminino</div>
            <div class='display-6 fw-bold text-pink'>{$dadosTempo['tempo_casa_feminino']}</div>
        </div>
    ";

    $htmlTempo .= "</div>";

    // Retorno via AJAX
    $retorno['divIdadeCasa'] = $htmlTempo;


//
//- CALCULA O SALÁRIO MÉDIO dos ativos
//
    $sql = "SELECT
                MIN(salario_base) AS menor_salario,
                MAX(salario_base) AS maior_salario,
                ROUND(AVG(salario_base), 2) AS salario_medio
            FROM rh_colaboradores
            WHERE data_rescisao IS NULL AND data_admissao IS NOT NULL
            AND salario_base IS NOT NULL
            AND salario_base > 1;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vetorSalarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dadosSalarios = $vetorSalarios[0]; // apenas uma linha

    // Monta HTML do card, mesmo padrão da média de idade
    $htmlSalarios = "<div class='row text-center'>";

    $menor = number_format($dadosSalarios['menor_salario'], 2, ',', '.');
    $maior = number_format($dadosSalarios['maior_salario'], 2, ',', '.');
    $medio = number_format($dadosSalarios['salario_medio'], 2, ',', '.');

    $htmlSalarios .= "
        <div class='col-4 border-end border-secondary'>
            <div class='fw-bold text-light'>Menor</div>
            <div class='display-7 fw-bold text-white'>{$menor}</div>
        </div>
        <div class='col-4 border-end border-secondary'>
            <div class='fw-bold text-primary'>Médio</div>
            <div class='display-7 fw-bold text-primary'>{$medio}</div>
        </div>
        <div class='col-4'>
            <div class='fw-bold text-pink'>Maior</div>
            <div class='display-7 fw-bold text-pink'>{$maior}</div>
        </div>
    ";

    $htmlSalarios .= "</div>";

    // Retorno via AJAX
    $retorno['divSalMedio'] = $htmlSalarios;

//
//- GRUPOS DE ETNIAS (Cor/Raça)
//
    $sql = "SELECT 
                    grupo_etnia,
                    quantidade,
                    ROUND((quantidade * 100.0) / total.total_geral, 2) AS percentual
                FROM (
                    SELECT 
                        CASE
                            WHEN E.categoria = 'Branca' THEN 'Branca'
                            WHEN E.categoria IN ('Parda', 'Preta', 'Negra') THEN 'Negra'
                            ELSE 'Outros'
                        END AS grupo_etnia,
                        COUNT(*) AS quantidade
                    FROM rh_colaboradores C
                    INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                    LEFT OUTER JOIN rh_etnias E ON E.idEtnia = P.idEtnia
                    WHERE C.data_rescisao IS NULL
                    AND C.data_admissao IS NOT NULL
                    GROUP BY grupo_etnia
                ) dados
                CROSS JOIN (
                    SELECT COUNT(*) AS total_geral
                    FROM rh_colaboradores C
                    INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                    WHERE C.data_rescisao IS NULL
                    AND C.data_admissao IS NOT NULL
                    AND P.idEtnia > 0
                ) total
                ORDER BY quantidade DESC;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vetorEtnias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $retorno['vetorGenero'] = $vetorEtnias;

    // Monta o HTML do card
    $htmlEtnias = "<div class='row text-center'>";

    foreach ($vetorEtnias as $g) {

        if($g['grupo_etnia'] == 'Branca'){
            $icone = "<i class='fa-solid fa-person me-2 text-primary'></i>";
        } else{
            $icone = (($g['grupo_etnia'] == 'Negra') ? "<i class='fa-solid fa-person me-2 text-warning'></i>" :
                "<i class='fa-solid fa-person me-2 text-secondary'></i>");            
        }

        if($g['grupo_etnia'] == 'Branca'){
            $corTexto = "text-info";
        } else{
            $corTexto = ($g['grupo_etnia'] == 'Negra') ? "text-warning" : "text-secondary";
        }            

        $htmlEtnias .= "
            <div class='col-4 border-end border-secondary'>
                <div class='fw-bold fs-5 $corTexto'>$icone {$g['grupo_etnia']}</div>
                <div class='display-6 fw-bold text-light'>{$g['quantidade']}</div>
                <div class='small text-muted'>{$g['percentual']}%</div>
            </div>
        ";
    }
    $htmlEtnias .= "</div>";
    $retorno['divEtnias'] = $htmlEtnias;

//
//-- ENVIA O VETOR DOS DADOS
//
echo json_encode($retorno);
$conn = null;

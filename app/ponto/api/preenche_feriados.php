<?php
// inserir_feriados_2025.php
// (C)haia, 2025-11-07

include dirname(__DIR__) . '/../includes/conexao_gerar.php';
date_default_timezone_set('America/Sao_Paulo');

$feriados = [

    // ----- FERIADOS NACIONAIS -----
    ['2025-01-01', 'FERIADO', 'Confraternização Universal', 'BR', null, null, null],
    ['2025-03-03', 'FACULTATIVO', 'Ponto facultativo - Segunda-feira de Carnaval', 'BR', null, null, null],
    ['2025-03-04', 'FERIADO', 'Carnaval', 'BR', null, null, null],
    ['2025-03-04', 'REDUZIDO', 'Quarta-feira de Carnaval', 'BR', "12:00", "18:05", null],
    ['2025-04-18', 'FERIADO', 'Sexta-Feira Santa (Paixão de Cristo)', 'BR', null, null, null],
    ['2025-04-21', 'FERIADO', 'Tiradentes', 'BR', null, null, null],
    ['2025-05-01', 'FERIADO', 'Dia do Trabalho', 'BR', null, null, null],
    ['2025-06-19', 'FERIADO', 'Corpus Christi', 'BR', null, null, null],
    ['2025-09-07', 'FERIADO', 'Independência do Brasil', 'BR', null, null, null],
    ['2025-10-12', 'FERIADO', 'Nossa Senhora Aparecida - Padroeira do Brasil', 'BR', null, null, null],
    ['2025-11-02', 'FERIADO', 'Finados', 'BR', null, null, null],
    ['2025-11-15', 'FERIADO', 'Proclamação da República', 'BR', null, null, null],
    ['2025-11-20', 'FERIADO', 'Dia Nacional de Zumbi e da Consciência Negra', 'BR', null, null, null],
    ['2025-12-25', 'FERIADO', 'Natal', 'BR', null, null, null],

    // ----- FERIADOS ESTADUAIS - PARANÁ -----
    ['2025-12-19', 'FERIADO', 'Emancipação Política do Estado do Paraná', 'PR', null, null, null],

    // ----- FERIADOS ESTADUAIS - SANTA CATARINA -----
    ['2025-08-11', 'FERIADO', 'Criação da Capitania, Estado e Aniversário de Santa Catarina', 'SC', null, null, null],

    // ----- FERIADOS MUNICIPAIS - CURITIBA -----
    ['2025-03-29', 'FERIADO', 'Aniversário de Curitiba', 'PR', null, null, 3281],
    ['2025-09-08', 'FERIADO', 'Nossa Senhora da Luz dos Pinhais (Padroeira de Curitiba)', 'PR', null, null, 3281],
];

$sql = "
    INSERT INTO rh_ponto_calendario
        (data, tipo, descricao, hora_ini, hora_fim, cidade_id, estado, ativo)
    VALUES
        (:data, :tipo, :descricao, :hora_ini, :hora_fim, :cidade_id, :estado, 1)
";

$stmt = $conn->prepare($sql);

foreach ($feriados as $f) {
    [$data, $tipo, $descricao, $estado, $hora_ini, $hora_fim, $cidade_id] = $f;

    // valores padrão caso sejam nulos
    $hora_ini = $hora_ini ?? '00:00:00';
    $hora_fim = $hora_fim ?? '23:59:59';

    $stmt->execute([
        ':data' => $data,
        ':tipo' => $tipo,
        ':descricao' => $descricao,
        ':hora_ini' => $hora_ini,
        ':hora_fim' => $hora_fim,
        ':cidade_id' => $cidade_id,
        ':estado' => $estado
    ]);

    $abrangencia = $cidade_id ? "Cidade ID $cidade_id" : ($estado === 'BR' ? 'Nacional' : "Estadual $estado");
    echo "✔️ Inserido: $descricao ($data) - $abrangencia<br>";
}

echo "<br><b>Concluído com sucesso!</b>";

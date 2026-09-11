<?php

function isDSR(DateTime $data, bool $sabadoDSR = true): bool
{
    $diaSemana = (int)$data->format('w');
    return $diaSemana === 0 || ($sabadoDSR && $diaSemana === 6);
}

function verifica_data(string $data, ?int $cidade_id, ?string $estado, PDO $conn): array
{
    $dt = new DateTime($data);

    // padrão
    $vetor = [
        'tipo'   => 'UTIL',
        'motivo' => null
    ];

    $sql = "SELECT *
            FROM rh_ponto_calendario
            WHERE ativo = 1
              AND data = :data
              AND (
                    estado = 'BR'
                    OR estado = :estado
                    OR cidade_id = :cidade_id
                    OR tipo IN ('FACULTATIVO','REDUZIDO','COMPENSADO')
                  )
            ORDER BY
                CASE
                    WHEN estado = 'BR' THEN 1
                    WHEN estado = :estado THEN 2
                    WHEN cidade_id = :cidade_id THEN 3
                    WHEN tipo = 'COMPENSADO' THEN 4
                    WHEN tipo = 'REDUZIDO' THEN 5
                    WHEN tipo = 'FACULTATIVO' THEN 6
                END
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':data'      => $data,
        ':estado'    => $estado,
        ':cidade_id' => $cidade_id
    ]);

    if ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $vetor['tipo']   = $linha['tipo'];
        $vetor['motivo'] = $linha['descricao'];

        if ($linha['tipo'] === 'REDUZIDO') {
            $vetor['hora_ini'] = $linha['hora_ini'];
            $vetor['hora_fim'] = $linha['hora_fim'];
        }

        return $vetor; // exceção sempre ganha
    }

    // só cai aqui se não houve exceção
    if (isDSR($dt)) {
        $vetor['tipo'] = 'DSR';
    }

    return $vetor;
}

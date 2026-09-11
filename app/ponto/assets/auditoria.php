<?PHP
/*
⭐ O que este sistema entrega
Risco	Solução
Alguém muda uma batida no banco	Trigger UPDATE registra tudo
Alguém apaga uma batida	Trigger DELETE registra tudo
Alguém insere manualmente	Trigger INSERT gera hash e loga
Alguém altera direto no MySQL	Auditoria pega o usuário e o IP
Alguém tenta adulterar escondido	Hash acusa imediatamente

Esse é o nível de auditoria usado em ponto eletrônico homologado.
*/
function validarIntegridade($row) {
    $chave = 'CHAVE-SECRETA-INTERNA';

    $hashCheck = hash('sha256',
        $row['colaborador_id'] .
        $row['data_hora'] .
        $row['ip'] .
        $row['lat'] .
        $row['lon'] .
        $row['endereco_id'] .
        $row['endereco_texto'] .
        $row['ticket'] .
        $row['tipoBatida'] .
        $chave
    );

    return $hashCheck === $row['hash_integridade'];
}

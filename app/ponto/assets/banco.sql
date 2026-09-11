
CREATE TABLE IF NOT EXISTS rh_ponto_auditoria (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    acao ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    id_ponto INT NOT NULL,
    valores_antigos JSON NULL,
    valores_novos JSON NULL,
    alterado_por VARCHAR(100),
    ip_origem VARCHAR(50),
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (id_ponto),
    INDEX (acao),
    INDEX (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TRIGGER tri_ponto_before_update
BEFORE UPDATE ON rh_ponto_registros
FOR EACH ROW
INSERT INTO rh_ponto_auditoria
(acao, id_ponto, valores_antigos, valores_novos, alterado_por, ip_origem)
VALUES
(
    'UPDATE',
    OLD.id,
    JSON_OBJECT(
        'colaborador_id', OLD.colaborador_id,
        'data_hora', OLD.data_hora,
        'ip', OLD.ip,
        'lat', OLD.lat,
        'lon', OLD.lon,
        'endereco_id', OLD.endereco_id,
        'endereco_texto', OLD.endereco_texto,
        'ticket', OLD.ticket,
        'origem', OLD.origem
    ),
    JSON_OBJECT(
        'colaborador_id', NEW.colaborador_id,
        'data_hora', NEW.data_hora,
        'ip', NEW.ip,
        'lat', NEW.lat,
        'lon', NEW.lon,
        'endereco_id', NEW.endereco_id,
        'endereco_texto', NEW.endereco_texto,
        'ticket', NEW.ticket,
        'origem', NEW.origem
    ),
    CURRENT_USER(),
    SUBSTRING_INDEX(USER(),'@',-1)
);


CREATE TRIGGER tri_ponto_before_delete
BEFORE DELETE ON rh_ponto_registros
FOR EACH ROW
INSERT INTO rh_ponto_auditoria
(acao, id_ponto, valores_antigos, alterado_por, ip_origem)
VALUES
(
    'DELETE',
    OLD.id,
    JSON_OBJECT(
        'colaborador_id', OLD.colaborador_id,
        'data_hora', OLD.data_hora,
        'ip', OLD.ip,
        'lat', OLD.lat,
        'lon', OLD.lon,
        'endereco_id', OLD.endereco_id,
        'endereco_texto', OLD.endereco_texto,
        'ticket', OLD.ticket,
        'origem', OLD.origem
    ),
    CURRENT_USER(),
    SUBSTRING_INDEX(USER(),'@',-1)
);


CREATE TRIGGER tri_ponto_before_insert
BEFORE INSERT ON rh_ponto_registros
FOR EACH ROW
SET NEW.hash_integridade = SHA2(
    CONCAT(
        NEW.colaborador_id,
        NEW.data_hora,
        NEW.ip,
        NEW.lat,
        NEW.lon,
        NEW.endereco_id,
        NEW.endereco_texto,
        NEW.ticket,
        NEW.origem,
        'CHAVE-SECRETA-INTERNA'  -- troque por algo forte
    ),
    256
);


CREATE TRIGGER tri_ponto_hash_update
BEFORE UPDATE ON rh_ponto_registros
FOR EACH ROW
SET NEW.hash_integridade = SHA2(
    CONCAT(
        NEW.colaborador_id,
        NEW.data_hora,
        NEW.ip,
        NEW.lat,
        NEW.lon,
        NEW.endereco_id,
        NEW.endereco_texto,
        NEW.ticket,
        NEW.origem,
        'CHAVE-SECRETA-INTERNA'
    ),
    256
);

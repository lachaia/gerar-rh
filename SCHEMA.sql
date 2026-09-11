-- ============================================================
-- SCHEMA.sql — Estrutura do banco de dados `RH` (Sistema RH)
-- Gerado automaticamente via SHOW CREATE TABLE em 2026-09-11 10:10:32
-- Contém apenas ESTRUTURA (DDL) — nenhum dado (rows) foi exportado.
-- Total de tabelas: 124 | Views: 5
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Tabela: imp_pessoas
-- ----------------------------
DROP TABLE IF EXISTS `imp_pessoas`;
CREATE TABLE `imp_pessoas` (
  `nome` varchar(200) NOT NULL,
  `fonte` varchar(200) DEFAULT NULL,
  `cargo` varchar(200) DEFAULT NULL,
  `cpf` varchar(11) DEFAULT NULL,
  `equipe` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: importacao
-- ----------------------------
DROP TABLE IF EXISTS `importacao`;
CREATE TABLE `importacao` (
  `esocial` int DEFAULT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome_social` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `sexo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raca_cor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grau_instrucao` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admissao` date DEFAULT NULL,
  `categoria` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao_cargo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cbo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_dpto` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao_dpto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_gestor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idGrauInstrucao` int DEFAULT NULL,
  `idEstadoCivil` int DEFAULT NULL,
  `idEtnia` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Tabela: rh_afastamento_tipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_afastamento_tipos`;
CREATE TABLE `rh_afastamento_tipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COMMENT='TIPOS DE AFASTAMENTO';

-- ----------------------------
-- Tabela: rh_afastamentos
-- ----------------------------
DROP TABLE IF EXISTS `rh_afastamentos`;
CREATE TABLE `rh_afastamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `idTipo` int DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `dias_afastado` int DEFAULT NULL,
  `data_retorno` date DEFAULT NULL,
  `cid` varchar(45) DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  `emitido_por` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `idLogin` int DEFAULT NULL,
  `idUsuario` int DEFAULT NULL,
  `status` varchar(45) DEFAULT 'Gestor' COMMENT '1: PENDENTE\\\\\\\\n2: REPROVADO\\\\\\\\n3: APROVADO',
  `gestor_por` varchar(45) DEFAULT NULL,
  `gestor_em` datetime DEFAULT NULL,
  `rh_por` varchar(45) DEFAULT NULL,
  `rh_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COMMENT='	';

-- ----------------------------
-- Tabela: rh_atendimentos
-- ----------------------------
DROP TABLE IF EXISTS `rh_atendimentos`;
CREATE TABLE `rh_atendimentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSubSede` int DEFAULT NULL,
  `data_ocorrencia` datetime DEFAULT NULL,
  `idBrigadista` int DEFAULT NULL,
  `nome_paciente` varchar(100) DEFAULT NULL,
  `tipo_ocorrencia` int DEFAULT NULL COMMENT 'desmaio, crise de ansiedade, etc...',
  `local_ocorrencia` varchar(100) DEFAULT NULL,
  `descricao` text,
  `acao_realizada` text,
  `encaminhamento` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(50) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_autocadastro
-- ----------------------------
DROP TABLE IF EXISTS `rh_autocadastro`;
CREATE TABLE `rh_autocadastro` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `nomeSocial` varchar(100) DEFAULT NULL,
  `nome_mae` varchar(100) DEFAULT NULL,
  `telefone` varchar(45) DEFAULT NULL,
  `email` varchar(220) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `dtNascimento` date DEFAULT NULL,
  `idEstadoCivil` int DEFAULT NULL,
  `nacionalidade` varchar(100) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(45) DEFAULT NULL,
  `titulo_eleitor` varchar(45) DEFAULT NULL,
  `pis` varchar(45) DEFAULT NULL,
  `ctps` varchar(45) DEFAULT NULL,
  `camiseta` varchar(10) DEFAULT NULL,
  `idEtnia` int DEFAULT NULL,
  `idGrauEscola` int DEFAULT NULL,
  `cnh` varchar(45) DEFAULT NULL,
  `cnh_categoria` varchar(10) DEFAULT NULL,
  `cnh_vencimento` date DEFAULT NULL,
  `idLogin` int DEFAULT '1',
  `arquivo_foto` varchar(45) DEFAULT NULL,
  `arquivo_endereco` varchar(45) DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  `atualizado_em` datetime DEFAULT NULL,
  `cep` varchar(8) DEFAULT NULL,
  `logradouro` varchar(100) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(45) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `uf` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_autocadastro_ctr
-- ----------------------------
DROP TABLE IF EXISTS `rh_autocadastro_ctr`;
CREATE TABLE `rh_autocadastro_ctr` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT NULL,
  `expira` datetime DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `data_resposta` datetime DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_avaliacao_tipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_avaliacao_tipos`;
CREATE TABLE `rh_avaliacao_tipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_avaliacoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_avaliacoes`;
CREATE TABLE `rh_avaliacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `avaliado_em` datetime DEFAULT NULL,
  `idTipo` int DEFAULT NULL,
  `score` int DEFAULT NULL,
  `avaliado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `idColabSuper` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_bancos
-- ----------------------------
DROP TABLE IF EXISTS `rh_bancos`;
CREATE TABLE `rh_bancos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `ispb` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_brigada_acoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigada_acoes`;
CREATE TABLE `rh_brigada_acoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSubSede` int DEFAULT NULL,
  `data_acao` date NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `acao_arquivo` varchar(255) DEFAULT NULL,
  `observacoes` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_brigada_acoes_membros
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigada_acoes_membros`;
CREATE TABLE `rh_brigada_acoes_membros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idAcao` int NOT NULL,
  `idBrigadista` int NOT NULL,
  `presente` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idAcao` (`idAcao`),
  KEY `idBrigadista` (`idBrigadista`),
  CONSTRAINT `rh_brigada_acoes_membros_ibfk_1` FOREIGN KEY (`idAcao`) REFERENCES `rh_brigada_acoes` (`id`),
  CONSTRAINT `rh_brigada_acoes_membros_ibfk_2` FOREIGN KEY (`idBrigadista`) REFERENCES `rh_brigadistas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_brigada_atend_membros
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigada_atend_membros`;
CREATE TABLE `rh_brigada_atend_membros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idAtendimento` int NOT NULL,
  `idBrigadista` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=290 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_brigada_cargos
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigada_cargos`;
CREATE TABLE `rh_brigada_cargos` (
  `idCargoBrigada` int NOT NULL AUTO_INCREMENT,
  `dsCargo` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idCargoBrigada`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_brigada_reuniao_membros
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigada_reuniao_membros`;
CREATE TABLE `rh_brigada_reuniao_membros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idReuniao` int NOT NULL,
  `idBrigadista` int NOT NULL,
  `presente` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idReuniao` (`idReuniao`),
  KEY `idBrigadista` (`idBrigadista`),
  CONSTRAINT `rh_brigada_reuniao_membros_ibfk_1` FOREIGN KEY (`idReuniao`) REFERENCES `rh_brigada_reunioes` (`id`),
  CONSTRAINT `rh_brigada_reuniao_membros_ibfk_2` FOREIGN KEY (`idBrigadista`) REFERENCES `rh_brigadistas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_brigada_reunioes
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigada_reunioes`;
CREATE TABLE `rh_brigada_reunioes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSubSede` int DEFAULT NULL,
  `data_reuniao` date NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `ata_arquivo` varchar(255) DEFAULT NULL,
  `observacoes` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_brigada_tipo_ocorrencia
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigada_tipo_ocorrencia`;
CREATE TABLE `rh_brigada_tipo_ocorrencia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_brigadistas
-- ----------------------------
DROP TABLE IF EXISTS `rh_brigadistas`;
CREATE TABLE `rh_brigadistas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPessoa` int DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_final` date DEFAULT NULL,
  `idCargoBrigada` int DEFAULT NULL,
  `idSubSede` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_cargos
-- ----------------------------
DROP TABLE IF EXISTS `rh_cargos`;
CREATE TABLE `rh_cargos` (
  `idCargo` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `nome` varchar(250) DEFAULT NULL,
  `descricao` text,
  `nivel` int DEFAULT NULL,
  `ativo` tinyint DEFAULT '1',
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idCargo`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8mb3 COMMENT='Tabela de Cargos e Salários';

-- ----------------------------
-- Tabela: rh_cargos_historico
-- ----------------------------
DROP TABLE IF EXISTS `rh_cargos_historico`;
CREATE TABLE `rh_cargos_historico` (
  `idCargoHistorico` int NOT NULL AUTO_INCREMENT,
  `idColaborador` int DEFAULT NULL,
  `idCargo_anterior` int DEFAULT NULL,
  `idCargo_atual` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `salario` decimal(12,2) DEFAULT NULL,
  `idTipoAlteracao` smallint DEFAULT NULL,
  PRIMARY KEY (`idCargoHistorico`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COMMENT='Registra o histórico (evulação) dos cargos do colaborador		';

-- ----------------------------
-- Tabela: rh_cargos_tipo_alt
-- ----------------------------
DROP TABLE IF EXISTS `rh_cargos_tipo_alt`;
CREATE TABLE `rh_cargos_tipo_alt` (
  `idTipoAlteracao` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idTipoAlteracao`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COMMENT='Tipos de alteração dos Cargos (promoção, transferência, etc)';

-- ----------------------------
-- Tabela: rh_centrocusto
-- ----------------------------
DROP TABLE IF EXISTS `rh_centrocusto`;
CREATE TABLE `rh_centrocusto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_cidades
-- ----------------------------
DROP TABLE IF EXISTS `rh_cidades`;
CREATE TABLE `rh_cidades` (
  `idCidade` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `ibge` int DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Brasil',
  PRIMARY KEY (`idCidade`)
) ENGINE=InnoDB AUTO_INCREMENT=5562 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_cipa_acoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipa_acoes`;
CREATE TABLE `rh_cipa_acoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSubSede` int DEFAULT NULL,
  `data_acao` date NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `acao_arquivo` varchar(255) DEFAULT NULL,
  `observacoes` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_cipa_acoes_membros
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipa_acoes_membros`;
CREATE TABLE `rh_cipa_acoes_membros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idAcao` int NOT NULL,
  `idCipeiro` int NOT NULL,
  `presente` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idAcao` (`idAcao`),
  KEY `idCipeiro` (`idCipeiro`),
  CONSTRAINT `rh_cipa_acoes_membros_ibfk_1` FOREIGN KEY (`idAcao`) REFERENCES `rh_cipa_acoes` (`id`),
  CONSTRAINT `rh_cipa_acoes_membros_ibfk_2` FOREIGN KEY (`idCipeiro`) REFERENCES `rh_cipeiros` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_cipa_atendimentos
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipa_atendimentos`;
CREATE TABLE `rh_cipa_atendimentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSubSede` int DEFAULT NULL,
  `data_ocorrencia` datetime DEFAULT NULL,
  `idCipeiro` int DEFAULT NULL,
  `nome_paciente` varchar(100) DEFAULT NULL,
  `tipo_ocorrencia` int DEFAULT NULL COMMENT 'desmaio, crise de ansiedade, etc...',
  `local_ocorrencia` varchar(100) DEFAULT NULL,
  `descricao` text,
  `acao_realizada` text,
  `encaminhamento` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(50) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_cipa_cargos
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipa_cargos`;
CREATE TABLE `rh_cipa_cargos` (
  `idCargo` int NOT NULL AUTO_INCREMENT,
  `dsCargo` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idCargo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_cipa_reuniao_membros
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipa_reuniao_membros`;
CREATE TABLE `rh_cipa_reuniao_membros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idReuniao` int NOT NULL,
  `idCipeiro` int NOT NULL,
  `presente` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idReuniao` (`idReuniao`),
  KEY `idCipeiro` (`idCipeiro`),
  CONSTRAINT `rh_cipa_reuniao_membros_ibfk_1` FOREIGN KEY (`idReuniao`) REFERENCES `rh_cipa_reunioes` (`id`),
  CONSTRAINT `rh_cipa_reuniao_membros_ibfk_2` FOREIGN KEY (`idCipeiro`) REFERENCES `rh_cipeiros` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_cipa_reunioes
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipa_reunioes`;
CREATE TABLE `rh_cipa_reunioes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSubSede` int DEFAULT NULL,
  `data_reuniao` date NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `ata_arquivo` varchar(255) DEFAULT NULL,
  `observacoes` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_cipa_tipo_ocorrencia
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipa_tipo_ocorrencia`;
CREATE TABLE `rh_cipa_tipo_ocorrencia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_cipeiros
-- ----------------------------
DROP TABLE IF EXISTS `rh_cipeiros`;
CREATE TABLE `rh_cipeiros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPessoa` int DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_final` date DEFAULT NULL,
  `idCargo` int DEFAULT NULL,
  `idSubSede` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_colaboradores
-- ----------------------------
DROP TABLE IF EXISTS `rh_colaboradores`;
CREATE TABLE `rh_colaboradores` (
  `idColab` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT '1',
  `idPessoa` int DEFAULT NULL,
  `matricula` varchar(30) DEFAULT NULL,
  `idOrgao` int DEFAULT NULL,
  `dcLider` smallint DEFAULT '0',
  `idCargo` int DEFAULT NULL,
  `idFuncao` int DEFAULT NULL,
  `idContratoTipo` int DEFAULT NULL,
  `data_admissao` date DEFAULT NULL,
  `idRescisao` int DEFAULT NULL,
  `data_rescisao` date DEFAULT NULL,
  `idRescisaoTipo` int DEFAULT NULL,
  `salario_base` decimal(12,2) DEFAULT NULL,
  `carga_horaria` int DEFAULT NULL,
  `idStatus` int DEFAULT NULL,
  `horario_ini` char(5) DEFAULT NULL,
  `horario_fim` char(5) DEFAULT NULL,
  `idBanco` int DEFAULT NULL,
  `bco_agencia` varchar(10) DEFAULT NULL,
  `bco_cc` varchar(30) DEFAULT NULL,
  `arquivo_ctps` varchar(250) DEFAULT NULL,
  `arquivo_ddir` varchar(250) DEFAULT NULL,
  `idEnderecoTrab` int DEFAULT NULL,
  `vale_transporte` varchar(45) DEFAULT NULL,
  `vale_refeicao` varchar(45) DEFAULT NULL,
  `idPlanoSaude` int DEFAULT NULL,
  `idPlanoOdonto` int DEFAULT NULL,
  `planoFarmacia` varchar(45) DEFAULT NULL,
  `idSubSede` int DEFAULT '0',
  `polo_id` int DEFAULT NULL,
  `idJornada` char(1) DEFAULT '0',
  `idTipoForma` char(1) DEFAULT '0',
  `idTipoPrazo` char(1) DEFAULT '0',
  `idStatusOld` int DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `salario_old` decimal(12,2) DEFAULT NULL,
  `cbo` varchar(10) DEFAULT NULL,
  `idCentroCusto` int DEFAULT NULL,
  `chave_pix` varchar(100) DEFAULT NULL,
  `bate_ponto` tinyint DEFAULT '1',
  `esocial_id` int DEFAULT NULL,
  PRIMARY KEY (`idColab`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COMMENT='Guardas as informações dos colaboradores';

-- ----------------------------
-- Tabela: rh_colaboradores_hist
-- ----------------------------
DROP TABLE IF EXISTS `rh_colaboradores_hist`;
CREATE TABLE `rh_colaboradores_hist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int NOT NULL,
  `idEmpresa` int DEFAULT '1',
  `idPessoa` int DEFAULT NULL,
  `matricula` varchar(30) DEFAULT NULL,
  `idOrgao` int DEFAULT NULL,
  `dcLider` smallint DEFAULT '0',
  `idCargo` int DEFAULT NULL,
  `idFuncao` int DEFAULT NULL,
  `idContratoTipo` int DEFAULT NULL,
  `data_admissao` date DEFAULT NULL,
  `idRescisao` int DEFAULT NULL,
  `data_rescisao` date DEFAULT NULL,
  `idRescisaoTipo` int DEFAULT NULL,
  `salario_base` decimal(12,2) DEFAULT NULL,
  `carga_horaria` int DEFAULT NULL,
  `idStatus` int DEFAULT NULL,
  `horario_ini` char(5) DEFAULT NULL,
  `horario_fim` char(5) DEFAULT NULL,
  `pis` varchar(45) DEFAULT NULL,
  `ctps` varchar(45) DEFAULT NULL,
  `nome_mae` varchar(100) DEFAULT NULL,
  `idGrauEscolaridade` int DEFAULT NULL,
  `idBanco` int DEFAULT NULL,
  `bco_agencia` varchar(10) DEFAULT NULL,
  `bco_cc` varchar(30) DEFAULT NULL,
  `arquivo_ctps` varchar(250) DEFAULT NULL,
  `arquivo_ddir` varchar(250) DEFAULT NULL,
  `idEnderecoTrab` int DEFAULT NULL,
  `vale_transporte` varchar(45) DEFAULT NULL,
  `vale_refeicao` varchar(45) DEFAULT NULL,
  `idPlanoSaude` int DEFAULT NULL,
  `idPlanoOdonto` int DEFAULT NULL,
  `planoFarmacia` varchar(45) DEFAULT '"Nenhum"',
  `idSubSede` int DEFAULT '0',
  `idJornada` char(1) DEFAULT '0',
  `idTipoForma` char(1) DEFAULT '0',
  `idTipoPrazo` char(1) DEFAULT '0',
  `idStatusOld` int DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `salario_old` decimal(12,2) DEFAULT NULL,
  `cbo` varchar(10) DEFAULT NULL,
  `idCentroCusto` int DEFAULT NULL,
  `chave_pix` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3 COMMENT='Guardas as informações dos colaboradores';

-- ----------------------------
-- Tabela: rh_colaboradores_status
-- ----------------------------
DROP TABLE IF EXISTS `rh_colaboradores_status`;
CREATE TABLE `rh_colaboradores_status` (
  `idStatus` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(250) DEFAULT NULL,
  `cor_status` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COMMENT='	';

-- ----------------------------
-- Tabela: rh_conqTipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_conqTipos`;
CREATE TABLE `rh_conqTipos` (
  `idConqTipo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idConqTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_contratos_tipo
-- ----------------------------
DROP TABLE IF EXISTS `rh_contratos_tipo`;
CREATE TABLE `rh_contratos_tipo` (
  `idContratoTipo` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(250) DEFAULT NULL,
  `tem_seguro` tinyint DEFAULT NULL,
  `tem_ferias` tinyint DEFAULT NULL,
  `tem_rescisao` tinyint DEFAULT NULL,
  `tem_fgts` tinyint DEFAULT NULL,
  `tem_aivso_previo` tinyint DEFAULT NULL,
  `tem_13_proporcional` tinyint DEFAULT NULL,
  `multa_fgts` float DEFAULT NULL,
  `ativo` tinyint DEFAULT '1',
  PRIMARY KEY (`idContratoTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COMMENT='Tipos de Contrato do Colaborador';

-- ----------------------------
-- Tabela: rh_ctr_exp
-- ----------------------------
DROP TABLE IF EXISTS `rh_ctr_exp`;
CREATE TABLE `rh_ctr_exp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `duracao` varchar(5) DEFAULT NULL,
  `prorrogado` tinyint DEFAULT '0',
  `status` enum('Ativo','Vincendo','Efetivado','Rescindido','Encerrado') DEFAULT NULL,
  `observacoes` text,
  `criado_por` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `atualizado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `data_prorrogacao` date DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_cv
-- ----------------------------
DROP TABLE IF EXISTS `rh_cv`;
CREATE TABLE `rh_cv` (
  `idCV` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT '1',
  `idPessoa` int DEFAULT NULL,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  `ultimaAtualizacao` datetime DEFAULT NULL,
  `genero` char(1) DEFAULT NULL COMMENT 'F: Feminino\nM: Masculino\nN: Não Binário\nO: outros\n0: Prefiro não responder',
  `deficiente` smallint DEFAULT NULL,
  `def_fisica` smallint DEFAULT NULL,
  `def_visual` smallint DEFAULT NULL,
  `def_auditiva` smallint DEFAULT NULL,
  `def_mental` smallint DEFAULT NULL,
  `def_intelectual` smallint DEFAULT NULL,
  `def_autista` smallint DEFAULT NULL,
  `cid` varchar(45) DEFAULT NULL,
  `linkedin` varchar(200) DEFAULT NULL,
  `idCidade` int DEFAULT NULL COMMENT 'CIDADE DE ORIGEM (DIVERSIDADE)',
  `cor` varchar(30) DEFAULT NULL,
  `pronome` varchar(30) DEFAULT NULL,
  `orientacao` varchar(30) DEFAULT NULL,
  `idGenero` varchar(30) DEFAULT NULL,
  `arquivo` varchar(250) DEFAULT NULL,
  `status` varchar(45) DEFAULT 'Preenchendo',
  PRIMARY KEY (`idCV`),
  UNIQUE KEY `idPessoa_UNIQUE` (`idPessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COMMENT='Currículo Vitae';

-- ----------------------------
-- Tabela: rh_cv_conq
-- ----------------------------
DROP TABLE IF EXISTS `rh_cv_conq`;
CREATE TABLE `rh_cv_conq` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPessoa` int DEFAULT NULL,
  `idEmpresa` int DEFAULT '1',
  `idConqTipo` int DEFAULT NULL,
  `titulo` varchar(250) DEFAULT NULL,
  `ano` int DEFAULT NULL,
  `descricao` text,
  `idLogin` varchar(45) DEFAULT NULL,
  `idDoc` int DEFAULT NULL COMMENT 'ID do Documento na tabela rr_documentos',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_cv_exp
-- ----------------------------
DROP TABLE IF EXISTS `rh_cv_exp`;
CREATE TABLE `rh_cv_exp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `cargo` varchar(250) DEFAULT NULL,
  `descricao` text,
  `ano_ini` int DEFAULT NULL,
  `ano_fim` int DEFAULT NULL,
  `ativo` smallint DEFAULT '0' COMMENT '1: sim, 0: não',
  `idLogin` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb3 COMMENT='CURRICULUM VITAE - EXPERIÊNCIA PROFISSIONAL';

-- ----------------------------
-- Tabela: rh_cv_fa
-- ----------------------------
DROP TABLE IF EXISTS `rh_cv_fa`;
CREATE TABLE `rh_cv_fa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT '1',
  `idPessoa` int DEFAULT NULL,
  `idInstituicao` int DEFAULT NULL,
  `idNivel` int DEFAULT NULL,
  `curso` varchar(255) DEFAULT NULL,
  `ano_conclusao` int DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `idDoc` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_cv_habilidades
-- ----------------------------
DROP TABLE IF EXISTS `rh_cv_habilidades`;
CREATE TABLE `rh_cv_habilidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPessoa` int NOT NULL,
  `habilidade` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idPessoa` (`idPessoa`),
  CONSTRAINT `rh_cv_habilidades_ibfk_1` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_cv_idiomas
-- ----------------------------
DROP TABLE IF EXISTS `rh_cv_idiomas`;
CREATE TABLE `rh_cv_idiomas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `idIdioma` int DEFAULT NULL,
  `idFluencia` int DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_dependentes
-- ----------------------------
DROP TABLE IF EXISTS `rh_dependentes`;
CREATE TABLE `rh_dependentes` (
  `idDependente` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `idPessoaDep` int DEFAULT NULL,
  `idParentesco` int DEFAULT NULL,
  `dataNascimento` date DEFAULT NULL,
  `usaPlanoSaude` tinyint DEFAULT '0',
  `usaPlanoOdonto` tinyint DEFAULT '0',
  `usaCreche` tinyint DEFAULT '0',
  `ir` tinyint DEFAULT '0',
  `ativo` smallint DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idDependente`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3 COMMENT='	';

-- ----------------------------
-- Tabela: rh_docs_tipo
-- ----------------------------
DROP TABLE IF EXISTS `rh_docs_tipo`;
CREATE TABLE `rh_docs_tipo` (
  `idTipoDoc` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `validade` int DEFAULT NULL,
  `ativo` smallint DEFAULT NULL,
  PRIMARY KEY (`idTipoDoc`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3 COMMENT='Tipos de dodumentos';

-- ----------------------------
-- Tabela: rh_documentos
-- ----------------------------
DROP TABLE IF EXISTS `rh_documentos`;
CREATE TABLE `rh_documentos` (
  `idDoc` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `idTipoDoc` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `descricao` varchar(200) DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `arquivo` varchar(45) DEFAULT NULL,
  `nome_original` varchar(250) DEFAULT NULL,
  `ocr` text,
  `tags` text,
  `extensao` varchar(5) DEFAULT NULL,
  `tamanho` int DEFAULT NULL,
  `status` int DEFAULT '0',
  `idLoginAprova` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `origem` char(3) DEFAULT NULL,
  PRIMARY KEY (`idDoc`)
) ENGINE=InnoDB AUTO_INCREMENT=272 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_emails
-- ----------------------------
DROP TABLE IF EXISTS `rh_emails`;
CREATE TABLE `rh_emails` (
  `idEmail` int NOT NULL AUTO_INCREMENT,
  `idPessoa` int DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `destinatario` varchar(220) DEFAULT NULL,
  `cc` varchar(220) DEFAULT NULL,
  `cco` varchar(220) DEFAULT NULL,
  `titulo` varchar(220) DEFAULT NULL,
  `mensagem` text,
  `status` smallint DEFAULT NULL,
  `idDoc` int DEFAULT NULL COMMENT 'id documento enviado por e-mail (somente os de origem DOC)',
  `retorno_id` smallint DEFAULT '0',
  `idEmpresa` int DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idEmail`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_empresas
-- ----------------------------
DROP TABLE IF EXISTS `rh_empresas`;
CREATE TABLE `rh_empresas` (
  `idEmpresa` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `razao_social` varchar(250) DEFAULT NULL,
  `cnpj` varchar(14) DEFAULT NULL,
  `telefone` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `idEndereco` int DEFAULT NULL,
  `dcAtivo` smallint DEFAULT NULL,
  PRIMARY KEY (`idEmpresa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_enderecos
-- ----------------------------
DROP TABLE IF EXISTS `rh_enderecos`;
CREATE TABLE `rh_enderecos` (
  `idEndereco` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `idTipoEndereco` int DEFAULT NULL,
  `logradouro` varchar(250) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(45) DEFAULT NULL,
  `cep` varchar(8) DEFAULT NULL,
  `bairro` varchar(250) DEFAULT NULL,
  `cidade` varchar(250) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idEndereco`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb3 COMMENT='Endereços das Pessoas';

-- ----------------------------
-- Tabela: rh_enderecos_tipo
-- ----------------------------
DROP TABLE IF EXISTS `rh_enderecos_tipo`;
CREATE TABLE `rh_enderecos_tipo` (
  `idTipoEndereco` int NOT NULL AUTO_INCREMENT,
  `dsTipoEndereco` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idTipoEndereco`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_equip_modelos
-- ----------------------------
DROP TABLE IF EXISTS `rh_equip_modelos`;
CREATE TABLE `rh_equip_modelos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `texto` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `alterado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `alterado_por` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COMMENT='Modelos de Termo de Responsabilidade & Compromisso';

-- ----------------------------
-- Tabela: rh_equip_solic
-- ----------------------------
DROP TABLE IF EXISTS `rh_equip_solic`;
CREATE TABLE `rh_equip_solic` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPessoa` int DEFAULT NULL,
  `usuario_final` varchar(100) DEFAULT NULL,
  `equipamentos` varchar(250) DEFAULT NULL,
  `observacao` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `glpi_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COMMENT='Solicitações do RH para Reserva de Equipamentos';

-- ----------------------------
-- Tabela: rh_equip_termos
-- ----------------------------
DROP TABLE IF EXISTS `rh_equip_termos`;
CREATE TABLE `rh_equip_termos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSolic` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `idModelo` int DEFAULT '1',
  `data_entrega` datetime DEFAULT NULL,
  `vistoria_entrega` text,
  `termo_nome` varchar(100) DEFAULT NULL,
  `termo_endereco` varchar(100) DEFAULT NULL,
  `termo_end_numero` varchar(10) DEFAULT NULL,
  `termo_end_cpl` varchar(45) DEFAULT NULL,
  `termo_bairro` varchar(100) DEFAULT NULL,
  `termo_cidade` varchar(100) DEFAULT NULL,
  `termo_uf` char(2) DEFAULT NULL,
  `termo_cep` char(8) DEFAULT NULL,
  `termo_email` varchar(100) DEFAULT NULL,
  `termo_celular` varchar(45) DEFAULT NULL,
  `termo_html` text,
  `arquivo` varchar(100) DEFAULT NULL,
  `token` char(32) DEFAULT NULL,
  `expira` datetime DEFAULT NULL,
  `hashPDF` varchar(64) DEFAULT NULL,
  `userAgent` varchar(255) DEFAULT NULL,
  `userIP` varchar(45) DEFAULT NULL,
  `userAssinatura` varchar(45) DEFAULT NULL,
  `dtAssinatura` datetime DEFAULT NULL,
  `user_devolucao` varchar(45) DEFAULT NULL,
  `userAgentDev` varchar(255) DEFAULT NULL,
  `userIPDev` varchar(45) DEFAULT NULL,
  `vistoria_devolucao` text,
  `data_devolucao` datetime DEFAULT NULL,
  `origem` char(1) DEFAULT NULL,
  `dtAssinaturaDev` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8mb3 COMMENT='Termos de Responsabilidade de Equipamentos';

-- ----------------------------
-- Tabela: rh_equip_termos_ld
-- ----------------------------
DROP TABLE IF EXISTS `rh_equip_termos_ld`;
CREATE TABLE `rh_equip_termos_ld` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEquipTermo` int DEFAULT NULL,
  `idTipo` int DEFAULT NULL,
  `modelo` varchar(45) DEFAULT NULL,
  `patrimonio` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=660 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_equip_tipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_equip_tipos`;
CREATE TABLE `rh_equip_tipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  `criado_por` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_estadoCivil
-- ----------------------------
DROP TABLE IF EXISTS `rh_estadoCivil`;
CREATE TABLE `rh_estadoCivil` (
  `idEstadoCivil` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(50) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  PRIMARY KEY (`idEstadoCivil`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COMMENT='Tabela de estados civis conforme IBGE';

-- ----------------------------
-- Tabela: rh_etnias
-- ----------------------------
DROP TABLE IF EXISTS `rh_etnias`;
CREATE TABLE `rh_etnias` (
  `idEtnia` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(45) DEFAULT NULL,
  `descricao` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`idEtnia`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COMMENT='Lista de Etnias conforme IBGE';

-- ----------------------------
-- Tabela: rh_exames
-- ----------------------------
DROP TABLE IF EXISTS `rh_exames`;
CREATE TABLE `rh_exames` (
  `idExame` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `idExameTipo` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `dtValidade` date DEFAULT NULL,
  `nmExame` varchar(200) DEFAULT NULL,
  `nmClinica` varchar(100) DEFAULT NULL,
  `nmMedico` varchar(100) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `observacoes` text,
  `idDoc` int DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idExame`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COMMENT='Exames obrigatórios de Saúde Ocupacional';

-- ----------------------------
-- Tabela: rh_exames_tipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_exames_tipos`;
CREATE TABLE `rh_exames_tipos` (
  `idExameTipo` int NOT NULL AUTO_INCREMENT,
  `nmExame` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idExameTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COMMENT='Tipos de Exames	';

-- ----------------------------
-- Tabela: rh_fa_instituicoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_fa_instituicoes`;
CREATE TABLE `rh_fa_instituicoes` (
  `idInstituicao` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL COMMENT 'Nome da Instituição',
  `sigla` varchar(45) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `pais` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idInstituicao`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_fa_niveis
-- ----------------------------
DROP TABLE IF EXISTS `rh_fa_niveis`;
CREATE TABLE `rh_fa_niveis` (
  `idNivel` int NOT NULL AUTO_INCREMENT,
  `nivel` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idNivel`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ferias
-- ----------------------------
DROP TABLE IF EXISTS `rh_ferias`;
CREATE TABLE `rh_ferias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int NOT NULL,
  `admissao` date DEFAULT NULL,
  `inicio_aquisitivo` date DEFAULT NULL,
  `fim_aquisitivo` date DEFAULT NULL,
  `inicio_concessivo` date DEFAULT NULL,
  `fim_concessivo` date DEFAULT NULL,
  `start` date DEFAULT NULL,
  `observacao` text,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `agenda_parte1` date DEFAULT NULL,
  `data_parte1` date DEFAULT NULL,
  `dias_parte1` int DEFAULT NULL,
  `agenda_parte2` date DEFAULT NULL,
  `data_parte2` date DEFAULT NULL,
  `dias_parte2` int DEFAULT NULL,
  `agenda_parte3` date DEFAULT NULL,
  `data_parte3` date DEFAULT NULL,
  `dias_parte3` int DEFAULT NULL,
  `aprova_1_em` datetime DEFAULT NULL,
  `aprova_1_por` varchar(45) DEFAULT NULL,
  `aprova_2_em` datetime DEFAULT NULL,
  `aprova_2_por` varchar(45) DEFAULT NULL,
  `aprova_3_em` datetime DEFAULT NULL,
  `aprova_3_por` varchar(45) DEFAULT NULL,
  `agendado_em1` datetime DEFAULT NULL,
  `agendado_em2` datetime DEFAULT NULL,
  `agendado_em3` datetime DEFAULT NULL,
  `agendado_por1` varchar(45) DEFAULT NULL,
  `agendado_por2` varchar(45) DEFAULT NULL,
  `agendado_por3` varchar(45) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `idColabSupervisor` int DEFAULT NULL,
  `aprova_rh_1_em` datetime DEFAULT NULL,
  `aprova_rh_1_por` varchar(45) DEFAULT NULL,
  `aprova_rh_2_em` datetime DEFAULT NULL,
  `aprova_rh_2_por` varchar(45) DEFAULT NULL,
  `aprova_rh_3_em` datetime DEFAULT NULL,
  `aprova_rh_3_por` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ferias_repro
-- ----------------------------
DROP TABLE IF EXISTS `rh_ferias_repro`;
CREATE TABLE `rh_ferias_repro` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idFerias` int DEFAULT NULL,
  `motivo` text,
  `idUsuario` int DEFAULT NULL,
  `criado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COMMENT='Registra os motivos das Reprovações de solicitações de férias';

-- ----------------------------
-- Tabela: rh_fluencias
-- ----------------------------
DROP TABLE IF EXISTS `rh_fluencias`;
CREATE TABLE `rh_fluencias` (
  `idFluencia` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idFluencia`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_funcoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_funcoes`;
CREATE TABLE `rh_funcoes` (
  `idFuncao` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `nome` varchar(250) DEFAULT NULL,
  `descricao` text,
  `ativo` tinyint DEFAULT '1',
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idFuncao`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COMMENT='Tabela de Funções';

-- ----------------------------
-- Tabela: rh_graus_instrucao
-- ----------------------------
DROP TABLE IF EXISTS `rh_graus_instrucao`;
CREATE TABLE `rh_graus_instrucao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_historico_cargo
-- ----------------------------
DROP TABLE IF EXISTS `rh_historico_cargo`;
CREATE TABLE `rh_historico_cargo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `idCargo` int DEFAULT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_historico_funcao
-- ----------------------------
DROP TABLE IF EXISTS `rh_historico_funcao`;
CREATE TABLE `rh_historico_funcao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `idFuncao` int DEFAULT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_historico_orgao
-- ----------------------------
DROP TABLE IF EXISTS `rh_historico_orgao`;
CREATE TABLE `rh_historico_orgao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `idOrgao` int DEFAULT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_historico_sal
-- ----------------------------
DROP TABLE IF EXISTS `rh_historico_sal`;
CREATE TABLE `rh_historico_sal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `valor` decimal(12,2) DEFAULT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `indice` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3 COMMENT='HISTÓRICO DE SALARIOS';

-- ----------------------------
-- Tabela: rh_idiomas
-- ----------------------------
DROP TABLE IF EXISTS `rh_idiomas`;
CREATE TABLE `rh_idiomas` (
  `idIdioma` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idIdioma`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ldt_tipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_ldt_tipos`;
CREATE TABLE `rh_ldt_tipos` (
  `idAcaoTipo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(50) NOT NULL,
  `cor` varchar(20) NOT NULL,
  PRIMARY KEY (`idAcaoTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_logins
-- ----------------------------
DROP TABLE IF EXISTS `rh_logins`;
CREATE TABLE `rh_logins` (
  `idLogin` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT '1',
  `idUsuario` int DEFAULT NULL,
  `dtLogin` datetime DEFAULT NULL,
  `dtLogout` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `sisoper` varchar(45) DEFAULT NULL,
  `browser` varchar(45) DEFAULT NULL,
  `hardware` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idLogin`),
  KEY `Usuario_idx` (`idUsuario`),
  CONSTRAINT `Usuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=1316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_logs
-- ----------------------------
DROP TABLE IF EXISTS `rh_logs`;
CREATE TABLE `rh_logs` (
  `idLog` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT '1',
  `idLogin` int DEFAULT NULL,
  `dtOper` datetime DEFAULT NULL,
  `oper` char(3) DEFAULT NULL,
  `historico` text,
  `tabela` varchar(45) DEFAULT NULL,
  `idModulo` int DEFAULT NULL,
  `idOperacao` int DEFAULT NULL,
  `programa` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idLog`)
) ENGINE=InnoDB AUTO_INCREMENT=14065 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_notificacoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_notificacoes`;
CREATE TABLE `rh_notificacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int DEFAULT NULL,
  `colaborador_id` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `mensagem` varchar(255) DEFAULT NULL,
  `lido_em` datetime DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `idTipo` int DEFAULT NULL,
  `idEvento` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=705 DEFAULT CHARSET=utf8mb3 COMMENT='Notificações do Sistema';

-- ----------------------------
-- Tabela: rh_notificacoes_eventos
-- ----------------------------
DROP TABLE IF EXISTS `rh_notificacoes_eventos`;
CREATE TABLE `rh_notificacoes_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_notificacoes_tipo
-- ----------------------------
DROP TABLE IF EXISTS `rh_notificacoes_tipo`;
CREATE TABLE `rh_notificacoes_tipo` (
  `idTipo` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(50) DEFAULT NULL,
  `cor` varchar(45) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COMMENT='Tipos de Notificações';

-- ----------------------------
-- Tabela: rh_notificacoes_usuarios
-- ----------------------------
DROP TABLE IF EXISTS `rh_notificacoes_usuarios`;
CREATE TABLE `rh_notificacoes_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEvento` int DEFAULT NULL,
  `idUsuario` int DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_organograma
-- ----------------------------
DROP TABLE IF EXISTS `rh_organograma`;
CREATE TABLE `rh_organograma` (
  `idOrgao` int NOT NULL AUTO_INCREMENT COMMENT 'ID do órgão no Organograma Emmpresarial',
  `descricao` varchar(250) DEFAULT NULL,
  `nivel_1` tinyint DEFAULT NULL,
  `nivel_2` tinyint DEFAULT NULL,
  `nivel_3` tinyint DEFAULT NULL,
  `nivel_4` tinyint DEFAULT NULL,
  `nivel_5` tinyint DEFAULT NULL,
  `nivel_6` tinyint DEFAULT NULL,
  `nivel_7` tinyint DEFAULT '0',
  `nivel` tinyint DEFAULT NULL,
  `idSupervisor` int DEFAULT NULL,
  `staff` tinyint DEFAULT NULL,
  `estrategico` tinyint DEFAULT NULL,
  `ativo` tinyint DEFAULT '1',
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idOrgao`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb3 COMMENT='Define a estrutura formal da empresa em 6 níveis		';

-- ----------------------------
-- Tabela: rh_ouvidoria
-- ----------------------------
DROP TABLE IF EXISTS `rh_ouvidoria`;
CREATE TABLE `rh_ouvidoria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `identificacao` enum('anonimo','identificado') NOT NULL,
  `nome` varbinary(512) DEFAULT NULL,
  `email` varbinary(512) DEFAULT NULL,
  `telefone` varbinary(512) DEFAULT NULL,
  `tipo_assedio` varchar(50) NOT NULL,
  `tipo_outro` varchar(255) DEFAULT NULL,
  `relato` mediumblob,
  `envolvidos` varbinary(1024) DEFAULT NULL,
  `testemunhas` enum('sim','nao') DEFAULT NULL,
  `nomes_testemunhas` varbinary(1024) DEFAULT NULL,
  `comunicado` enum('sim','nao') DEFAULT NULL,
  `resposta_comunicado` varbinary(1024) DEFAULT NULL,
  `acompanhamento` enum('sim','nao') DEFAULT NULL,
  `nome_contato` varbinary(512) DEFAULT NULL,
  `email_contato` varbinary(512) DEFAULT NULL,
  `telefone_contato` varbinary(512) DEFAULT NULL,
  `data_ocorrido` date DEFAULT NULL,
  `encerrado_em` datetime DEFAULT NULL,
  `encerrado_por` varchar(45) DEFAULT NULL,
  `status` smallint DEFAULT '1' COMMENT '0 = Excluído\n1 = Novo\n2 = Em Processo\n9 = Baixado',
  `status_em` datetime DEFAULT NULL,
  `status_por` varchar(45) DEFAULT NULL,
  `status_motivo` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ouvidoria_ldt
-- ----------------------------
DROP TABLE IF EXISTS `rh_ouvidoria_ldt`;
CREATE TABLE `rh_ouvidoria_ldt` (
  `idAcao` int NOT NULL AUTO_INCREMENT,
  `idAcaoTipo` int DEFAULT NULL,
  `idDenuncia` int DEFAULT NULL,
  `idEmpresa` int DEFAULT NULL,
  `idUsuario` int DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `descricao` text,
  PRIMARY KEY (`idAcao`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='LINHA DO TEMPO - DENÚNCIAS';

-- ----------------------------
-- Tabela: rh_ouvidoria_tldt
-- ----------------------------
DROP TABLE IF EXISTS `rh_ouvidoria_tldt`;
CREATE TABLE `rh_ouvidoria_tldt` (
  `idAcaoTipo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(50) NOT NULL,
  `cor` varchar(20) NOT NULL,
  PRIMARY KEY (`idAcaoTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_parentescos
-- ----------------------------
DROP TABLE IF EXISTS `rh_parentescos`;
CREATE TABLE `rh_parentescos` (
  `idParentesco` int NOT NULL AUTO_INCREMENT,
  `dsParentesco` varchar(50) NOT NULL,
  PRIMARY KEY (`idParentesco`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_perfis
-- ----------------------------
DROP TABLE IF EXISTS `rh_perfis`;
CREATE TABLE `rh_perfis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPerfil` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_pessoas
-- ----------------------------
DROP TABLE IF EXISTS `rh_pessoas`;
CREATE TABLE `rh_pessoas` (
  `idPessoa` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT '1',
  `nome` varchar(100) DEFAULT NULL,
  `nomeSocial` varchar(100) DEFAULT NULL,
  `nome_mae` varchar(100) DEFAULT NULL,
  `telefone` varchar(45) DEFAULT NULL,
  `email` varchar(220) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `dtNascimento` date DEFAULT NULL,
  `idEstadoCivil` int DEFAULT NULL,
  `nacionalidade` varchar(100) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(45) DEFAULT NULL,
  `titulo_eleitor` varchar(45) DEFAULT NULL,
  `pis` varchar(45) DEFAULT NULL,
  `ctps` varchar(45) DEFAULT NULL,
  `camiseta` varchar(10) DEFAULT NULL,
  `idEtnia` int DEFAULT NULL,
  `idGrauEscola` int DEFAULT NULL,
  `cnh` varchar(45) DEFAULT NULL,
  `cnh_categoria` varchar(10) DEFAULT NULL,
  `cnh_vencimento` date DEFAULT NULL,
  `email_corporativo` varchar(100) DEFAULT NULL,
  `celular_corporativo` varchar(45) DEFAULT NULL,
  `obs` text,
  `ativo` smallint DEFAULT '1' COMMENT '0 - Inativo\\\\n1 - Ativo\\\\n2 - Excluído',
  `idLogin` int DEFAULT '1',
  PRIMARY KEY (`idPessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=637 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_pessoas_emg
-- ----------------------------
DROP TABLE IF EXISTS `rh_pessoas_emg`;
CREATE TABLE `rh_pessoas_emg` (
  `idContato` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `nome` varchar(250) DEFAULT NULL,
  `grau` varchar(45) DEFAULT NULL,
  `telefone` varchar(45) DEFAULT NULL,
  `celular` varchar(45) DEFAULT NULL,
  `endereco` text,
  `ativo` smallint DEFAULT '1',
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idContato`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COMMENT='Cadastro de Contatos de Emergência';

-- ----------------------------
-- Tabela: rh_pessoas_ldt
-- ----------------------------
DROP TABLE IF EXISTS `rh_pessoas_ldt`;
CREATE TABLE `rh_pessoas_ldt` (
  `idAcao` int NOT NULL AUTO_INCREMENT,
  `idAcaoTipo` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `idEmpresa` int DEFAULT NULL,
  `idUsuario` int DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `descricao` varchar(250) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `idEmail` int DEFAULT NULL,
  `idDoc` int DEFAULT NULL,
  `idOrigem` int DEFAULT NULL,
  `origem` char(3) DEFAULT NULL,
  PRIMARY KEY (`idAcao`)
) ENGINE=InnoDB AUTO_INCREMENT=1149 DEFAULT CHARSET=utf8mb3 COMMENT='LINHA DO TEMPO - PESSOAS';

-- ----------------------------
-- Tabela: rh_planos_saude
-- ----------------------------
DROP TABLE IF EXISTS `rh_planos_saude`;
CREATE TABLE `rh_planos_saude` (
  `idPlano` int NOT NULL AUTO_INCREMENT,
  `nomePlano` varchar(100) DEFAULT NULL,
  `operadora` varchar(100) DEFAULT NULL,
  `valorTitular` decimal(10,2) DEFAULT NULL,
  `valorDependente` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `tipoPlano` smallint DEFAULT NULL COMMENT '1. Saúde\n2. Odontológico',
  PRIMARY KEY (`idPlano`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_polos
-- ----------------------------
DROP TABLE IF EXISTS `rh_polos`;
CREATE TABLE `rh_polos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identificador` varchar(100) DEFAULT NULL,
  `subsede_id` int DEFAULT NULL,
  `polo_id` int DEFAULT NULL,
  `responsavel` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(45) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `numero` varchar(45) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `cidade_id` int DEFAULT NULL,
  `cidade_ds` varchar(100) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `login_id` int DEFAULT NULL,
  `ativo` smallint DEFAULT '1',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `obs` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='POLOS';

-- ----------------------------
-- Tabela: rh_ponto_auditoria
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_auditoria`;
CREATE TABLE `rh_ponto_auditoria` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `acao` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `id_ponto` int NOT NULL,
  `valores_antigos` json DEFAULT NULL,
  `valores_novos` json DEFAULT NULL,
  `alterado_por` varchar(100) DEFAULT NULL,
  `ip_origem` varchar(50) DEFAULT NULL,
  `data_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=45429 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ponto_banco
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_banco`;
CREATE TABLE `rh_ponto_banco` (
  `id` int NOT NULL AUTO_INCREMENT,
  `periodo_inicial` date DEFAULT NULL,
  `periodo_final` date DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `criado_por` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ponto_banco_horas
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_banco_horas`;
CREATE TABLE `rh_ponto_banco_horas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `colaborador_id` int NOT NULL,
  `cidade_id` int DEFAULT NULL,
  `colaborador_uf` char(2) DEFAULT NULL,
  `data_ref` date NOT NULL,
  `tipo_dia` varchar(45) DEFAULT NULL,
  `qtd_batidas` int DEFAULT NULL,
  `horas_previstas` int NOT NULL,
  `horas_trabalhadas` int NOT NULL,
  `intervalo_almoco` int DEFAULT NULL,
  `he_50` int DEFAULT NULL,
  `he_100` int DEFAULT NULL,
  `adicional_noturno` int DEFAULT NULL,
  `saldo_dia` int NOT NULL,
  `credito` int DEFAULT NULL,
  `debito` int DEFAULT NULL,
  `bh` int DEFAULT NULL,
  `alerta` tinyint DEFAULT '0',
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `colaborador_id` (`colaborador_id`,`data_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=23183 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ponto_banco_saldo
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_banco_saldo`;
CREATE TABLE `rh_ponto_banco_saldo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `banco_id` int DEFAULT NULL,
  `colaborador_id` int NOT NULL,
  `saldo_total` int NOT NULL,
  `status` enum('ABERTO','FECHADO') DEFAULT 'ABERTO',
  `pago_folha` tinyint(1) DEFAULT '0',
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2898 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ponto_calendario
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_calendario`;
CREATE TABLE `rh_ponto_calendario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `tipo` enum('FERIADO','FACULTATIVO','REDUZIDO','DSR','COMPENSADO') NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `hora_ini` time DEFAULT NULL,
  `hora_fim` time DEFAULT NULL,
  `abrangencia` smallint DEFAULT '1' COMMENT '1 - Nacional  |  2 - Estadual  | 3 - Municipal',
  `estado` char(2) DEFAULT NULL,
  `cidade_id` int DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ponto_enderecos
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_enderecos`;
CREATE TABLE `rh_ponto_enderecos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `colaborador_id` int DEFAULT NULL,
  `nome_local` varchar(45) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `criado_em` datetime DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ponto_espelhos
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_espelhos`;
CREATE TABLE `rh_ponto_espelhos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `colaborador_id` int DEFAULT NULL,
  `ano` int DEFAULT NULL,
  `mes` int DEFAULT NULL,
  `dsMes` varchar(45) DEFAULT NULL,
  `periodo_ini` date DEFAULT NULL,
  `periodo_fim` date DEFAULT NULL,
  `horas_normal` int DEFAULT NULL,
  `faltas` int DEFAULT NULL,
  `extras` int DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `arquivo` varchar(45) DEFAULT NULL,
  `gerado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `assinado_em` datetime DEFAULT NULL,
  `assinado_por` varchar(45) DEFAULT NULL,
  `hash_integridade` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb3 COMMENT='ESPELHO DO CARTAO PONTO';

-- ----------------------------
-- Tabela: rh_ponto_registros
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_registros`;
CREATE TABLE `rh_ponto_registros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `colaborador_id` int DEFAULT NULL,
  `data_hora` datetime DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL,
  `endereco_id` int DEFAULT NULL,
  `endereco_texto` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ticket` varchar(30) DEFAULT NULL,
  `hash_integridade` char(64) DEFAULT NULL,
  `origem` enum('app','web','manual','ajuste') DEFAULT 'app',
  `solicitacao_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_UNIQUE` (`ticket`)
) ENGINE=InnoDB AUTO_INCREMENT=9498 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_ponto_solicitacoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_ponto_solicitacoes`;
CREATE TABLE `rh_ponto_solicitacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `colaborador_id` int DEFAULT NULL,
  `batida_id` int DEFAULT NULL,
  `supervisor_id` int DEFAULT NULL,
  `solicitado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_hora` datetime DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `aprovado_em` datetime DEFAULT NULL,
  `aprovado_por` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `tipo` varchar(3) DEFAULT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `decisao_obs` varchar(255) DEFAULT NULL,
  `aplicado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3 COMMENT='Solicitações de Ajuste de Ponto';

-- ----------------------------
-- Tabela: rh_rescisao_tipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_rescisao_tipos`;
CREATE TABLE `rh_rescisao_tipos` (
  `idTipoRescisao` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idTipoRescisao`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_rescisoes
-- ----------------------------
DROP TABLE IF EXISTS `rh_rescisoes`;
CREATE TABLE `rh_rescisoes` (
  `idRescisao` int NOT NULL AUTO_INCREMENT,
  `idColab` int NOT NULL,
  `idTipoRescisao` int DEFAULT NULL,
  `motivoRescisao` text,
  `dtAviso` date DEFAULT NULL,
  `tipoAviso` varchar(20) DEFAULT NULL,
  `dtDesligamento` date NOT NULL,
  `saldoSalario` decimal(10,2) DEFAULT NULL,
  `feriasVencidas` decimal(10,2) DEFAULT NULL,
  `feriasProporcionais` decimal(10,2) DEFAULT NULL,
  `decimoTerceiro` decimal(10,2) DEFAULT NULL,
  `multaFgts` decimal(10,2) DEFAULT NULL,
  `descontos` decimal(10,2) DEFAULT NULL,
  `totalLiquido` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  `dtRegistro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idRescisao`),
  KEY `idColab` (`idColab`),
  CONSTRAINT `rh_rescisoes_ibfk_1` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_salarios
-- ----------------------------
DROP TABLE IF EXISTS `rh_salarios`;
CREATE TABLE `rh_salarios` (
  `idSalario` int NOT NULL AUTO_INCREMENT,
  `idCargo` int DEFAULT NULL,
  `nivel` int DEFAULT '1',
  `faixa_minima` decimal(12,2) DEFAULT NULL,
  `faixa_maxima` decimal(12,2) DEFAULT NULL,
  `data_vigencia` date DEFAULT NULL,
  PRIMARY KEY (`idSalario`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COMMENT='Tabela de Cargos x Salários';

-- ----------------------------
-- Tabela: rh_subsedes
-- ----------------------------
DROP TABLE IF EXISTS `rh_subsedes`;
CREATE TABLE `rh_subsedes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subsede_id` int DEFAULT NULL,
  `identificador` varchar(45) DEFAULT NULL,
  `responsavel` varchar(100) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` int DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `cidade_id` int DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Brasil',
  `telefone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `login_id` int DEFAULT NULL,
  `ativo` smallint DEFAULT '1',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `obs` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_termos
-- ----------------------------
DROP TABLE IF EXISTS `rh_termos`;
CREATE TABLE `rh_termos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idColab` int DEFAULT NULL,
  `idTipoTermo` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COMMENT='Registro dos Termos Gerais aceitos pelo colaborador';

-- ----------------------------
-- Tabela: rh_termos_tipos
-- ----------------------------
DROP TABLE IF EXISTS `rh_termos_tipos`;
CREATE TABLE `rh_termos_tipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COMMENT='Tipos de Termos';

-- ----------------------------
-- Tabela: rh_token
-- ----------------------------
DROP TABLE IF EXISTS `rh_token`;
CREATE TABLE `rh_token` (
  `idToken` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `idUsuario` int DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `login` varchar(250) DEFAULT NULL,
  `data_solicitacao` datetime DEFAULT NULL,
  `data_reset` datetime DEFAULT NULL,
  `tipo` smallint DEFAULT '1',
  PRIMARY KEY (`idToken`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb3 COMMENT='Cria os tokens para reset de senha';

-- ----------------------------
-- Tabela: rh_uf
-- ----------------------------
DROP TABLE IF EXISTS `rh_uf`;
CREATE TABLE `rh_uf` (
  `uf` char(2) NOT NULL,
  `nome` varchar(45) NOT NULL,
  PRIMARY KEY (`uf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rh_user_candidatos
-- ----------------------------
DROP TABLE IF EXISTS `rh_user_candidatos`;
CREATE TABLE `rh_user_candidatos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pessoa_id` int DEFAULT NULL,
  `senha` varchar(220) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pessoa` (`pessoa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COMMENT='Usuários do módulo de talentos';

-- ----------------------------
-- Tabela: rh_usuarios
-- ----------------------------
DROP TABLE IF EXISTS `rh_usuarios`;
CREATE TABLE `rh_usuarios` (
  `idUsuario` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `idUsuarioGrupo` int DEFAULT NULL,
  `idPessoa` int DEFAULT NULL,
  `idColab` int DEFAULT NULL,
  `login` varchar(220) DEFAULT NULL,
  `senha` varchar(220) DEFAULT NULL,
  `ativo` smallint DEFAULT '1',
  `foto` varchar(255) DEFAULT NULL,
  `chaveApp` varchar(45) DEFAULT NULL,
  `idSubSede` int DEFAULT NULL,
  `assinatura` text,
  `idLogin` int DEFAULT NULL,
  `dcCIPA` smallint DEFAULT '0',
  `dcBrigada` smallint DEFAULT '0',
  `criado_por` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUsuario`),
  KEY `Grupo_idx` (`idUsuarioGrupo`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rh_usuariosgrupo
-- ----------------------------
DROP TABLE IF EXISTS `rh_usuariosgrupo`;
CREATE TABLE `rh_usuariosgrupo` (
  `idUsuarioGrupo` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int DEFAULT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  `ativo` smallint DEFAULT NULL,
  `sigla` char(3) DEFAULT NULL,
  `idLogin` int DEFAULT NULL,
  PRIMARY KEY (`idUsuarioGrupo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rs_candidatos_fluxo
-- ----------------------------
DROP TABLE IF EXISTS `rs_candidatos_fluxo`;
CREATE TABLE `rs_candidatos_fluxo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(45) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint DEFAULT '1',
  `cor_frente` varchar(45) DEFAULT NULL,
  `cor_fundo` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_candidatos_origem
-- ----------------------------
DROP TABLE IF EXISTS `rs_candidatos_origem`;
CREATE TABLE `rs_candidatos_origem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(200) DEFAULT NULL,
  `ativo` smallint DEFAULT '1',
  `login_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_candidatos_parecer
-- ----------------------------
DROP TABLE IF EXISTS `rs_candidatos_parecer`;
CREATE TABLE `rs_candidatos_parecer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `candidatura_id` int NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `chamou_atencao` text,
  `tem_cnh` varchar(10) DEFAULT NULL,
  `nivel_office` varchar(30) DEFAULT NULL,
  `tem_experiencia` text,
  `pretensao_salarial` decimal(10,2) DEFAULT NULL,
  `analise_entrevista` text,
  `conclusao` varchar(20) DEFAULT NULL,
  `criado_por` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL,
  `enviado_em` datetime DEFAULT NULL,
  `enviado_para` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_candidatura` (`candidatura_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_competencias_comportamentais
-- ----------------------------
DROP TABLE IF EXISTS `rs_competencias_comportamentais`;
CREATE TABLE `rs_competencias_comportamentais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(150) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `login_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rs_competencias_tecnicas
-- ----------------------------
DROP TABLE IF EXISTS `rs_competencias_tecnicas`;
CREATE TABLE `rs_competencias_tecnicas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(150) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `login_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Tabela: rs_motivos_vaga
-- ----------------------------
DROP TABLE IF EXISTS `rs_motivos_vaga`;
CREATE TABLE `rs_motivos_vaga` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  `login_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_superintendentes
-- ----------------------------
DROP TABLE IF EXISTS `rs_superintendentes`;
CREATE TABLE `rs_superintendentes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identificador` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ativo` int DEFAULT NULL,
  `login_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_vagas
-- ----------------------------
DROP TABLE IF EXISTS `rs_vagas`;
CREATE TABLE `rs_vagas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identificador` varchar(150) DEFAULT NULL,
  `codigo_vaga` varchar(20) DEFAULT NULL,
  `subsede_id` int DEFAULT NULL,
  `polo_id` int DEFAULT NULL,
  `orgao_id` int DEFAULT NULL,
  `modalidade` varchar(100) DEFAULT NULL,
  `cargo_id` int DEFAULT NULL,
  `horario` varchar(45) DEFAULT NULL,
  `genero` varchar(45) DEFAULT NULL,
  `etaria` varchar(45) DEFAULT NULL,
  `cnh` varchar(45) DEFAULT NULL,
  `trab_cep` varchar(8) DEFAULT NULL,
  `trab_endereco` varchar(100) DEFAULT NULL,
  `trab_numero` varchar(45) DEFAULT NULL,
  `trab_complemento` varchar(100) DEFAULT NULL,
  `trab_bairro` varchar(100) DEFAULT NULL,
  `trab_cidade` varchar(100) DEFAULT NULL,
  `trab_uf` char(2) DEFAULT NULL,
  `trab_pais` varchar(100) DEFAULT NULL,
  `formacao` varchar(255) DEFAULT NULL,
  `curso_superior` varchar(255) DEFAULT NULL,
  `curso_estagio_1` varchar(255) DEFAULT NULL,
  `curso_estagio_2` varchar(255) DEFAULT NULL,
  `pergunta_chave` varchar(255) DEFAULT NULL,
  `experiencia` text,
  `c_comportamentais` text,
  `c_tecnicas` text,
  `equipamentos` text,
  `obs` text,
  `login_id` int DEFAULT NULL,
  `solicitante_id` int DEFAULT NULL,
  `setor_ds` varchar(45) DEFAULT NULL,
  `area` varchar(45) DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `fluxo_id` int DEFAULT NULL,
  `fluxo_alterado_em` datetime DEFAULT NULL,
  `pcd` tinyint DEFAULT NULL,
  `qtd` int DEFAULT NULL,
  `confidencial` tinyint DEFAULT '0',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `criado_por` varchar(45) DEFAULT NULL,
  `fechada_em` datetime DEFAULT NULL,
  `fechada_por` varchar(45) DEFAULT NULL,
  `slug` varchar(180) DEFAULT NULL COMMENT 'Para URLs amigáveis na landing page',
  `nivel_experiencia` varchar(45) DEFAULT NULL COMMENT 'Estágio, Júnior, Pleno, Sênior, Especialista, Liderença',
  `descricao` text COMMENT 'Texto detalhado da posição (visão geral da oportunidade).',
  `resumo` varchar(255) DEFAULT NULL COMMENT 'Resumo que aparece no card de listagem',
  `requisitos` text COMMENT 'Competências técnicas, qualificações obrigatórias e formação',
  `diferenciais` text COMMENT 'Qualificações desejáveis (não eliminatórias)',
  `atividades` text,
  `exibir_salario` tinyint DEFAULT '0',
  `salario` decimal(10,2) DEFAULT NULL COMMENT 'Faixa Salarial Inicial',
  `beneficios` text,
  `publicada_em` datetime DEFAULT NULL,
  `publicada_por` varchar(45) DEFAULT NULL,
  `expira_em` date DEFAULT NULL,
  `motivo_id` int DEFAULT NULL,
  `recrutador_id` int DEFAULT NULL,
  `gestor_nome` varchar(150) DEFAULT NULL,
  `gestor_email` varchar(200) DEFAULT NULL,
  `forma_recrutamento` varchar(45) DEFAULT NULL COMMENT 'Formato do recrutamento: ex. Externo, Misto',
  `tipo_contrato` varchar(45) DEFAULT NULL COMMENT 'Estágio, CLT, Instrutor, GIS, etc...',
  `arquivo_aut_gestor` varchar(100) DEFAULT NULL,
  `arquivo_indicacao` varchar(100) DEFAULT NULL,
  `alinha_gestor_em` datetime DEFAULT NULL,
  `alinha_gestor_com` varchar(45) DEFAULT NULL,
  `alinha_gestor_por` varchar(45) DEFAULT NULL,
  `aprovada` tinyint DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL COMMENT 'token para autenticação de vaga junto ao superintendente',
  `email_resposta` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COMMENT='VAGAS GERAR';

-- ----------------------------
-- Tabela: rs_vagas_aprova
-- ----------------------------
DROP TABLE IF EXISTS `rs_vagas_aprova`;
CREATE TABLE `rs_vagas_aprova` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vaga_id` int DEFAULT NULL,
  `super_id` int DEFAULT NULL,
  `enviado_em` datetime DEFAULT NULL,
  `aprovado_em` datetime DEFAULT NULL,
  `reprovado_em` datetime DEFAULT NULL,
  `obs` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_vagas_candidaturas
-- ----------------------------
DROP TABLE IF EXISTS `rs_vagas_candidaturas`;
CREATE TABLE `rs_vagas_candidaturas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vaga_id` int NOT NULL,
  `pessoa_id` int NOT NULL,
  `fluxo_id` int DEFAULT NULL,
  `fluxo_alterado_em` datetime DEFAULT NULL,
  `motivo_rejeicao` varchar(500) DEFAULT NULL,
  `screening_data` datetime DEFAULT NULL,
  `screening_meet_link` varchar(300) DEFAULT NULL,
  `screening_evento_id` varchar(200) DEFAULT NULL,
  `origem_id` int DEFAULT NULL,
  `criado_em` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_vaga_pessoa` (`vaga_id`,`pessoa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_vagas_fluxo
-- ----------------------------
DROP TABLE IF EXISTS `rs_vagas_fluxo`;
CREATE TABLE `rs_vagas_fluxo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(45) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint DEFAULT '1',
  `cor_frente` varchar(45) DEFAULT NULL,
  `cor_fundo` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_vagas_mot
-- ----------------------------
DROP TABLE IF EXISTS `rs_vagas_mot`;
CREATE TABLE `rs_vagas_mot` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(200) DEFAULT NULL,
  `ativo` smallint DEFAULT NULL,
  `login_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COMMENT='Motivação para criação da vaga';

-- ----------------------------
-- Tabela: rs_vagas_status
-- ----------------------------
DROP TABLE IF EXISTS `rs_vagas_status`;
CREATE TABLE `rs_vagas_status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(45) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint DEFAULT NULL,
  `cor_frente` varchar(45) DEFAULT NULL,
  `cor_fundo` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: rs_vagas_timeline
-- ----------------------------
DROP TABLE IF EXISTS `rs_vagas_timeline`;
CREATE TABLE `rs_vagas_timeline` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vaga_id` int DEFAULT NULL,
  `quando` datetime DEFAULT NULL,
  `oque` varchar(255) DEFAULT NULL,
  `quem` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Tabela: sys_modulos
-- ----------------------------
DROP TABLE IF EXISTS `sys_modulos`;
CREATE TABLE `sys_modulos` (
  `idModulo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `descricao` varchar(220) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ativo` smallint NOT NULL DEFAULT '1',
  PRIMARY KEY (`idModulo`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='Tabela dos Módulos (programas) do sistema';

-- ----------------------------
-- View: VW_Usuarios_logins
-- ----------------------------
DROP VIEW IF EXISTS `VW_Usuarios_logins`;
CREATE ALGORITHM=UNDEFINED DEFINER=`user_sistema`@`189.112.64.46` SQL SECURITY INVOKER VIEW `VW_Usuarios_logins` AS select `L`.`idEmpresa` AS `idEmpresa`,`L`.`idUsuario` AS `idUsuario`,count(`L`.`idLogin`) AS `qtd` from `rh_logins` `L` group by `L`.`idEmpresa`,`L`.`idUsuario`;

-- ----------------------------
-- View: VW_Usuarios_logs
-- ----------------------------
DROP VIEW IF EXISTS `VW_Usuarios_logs`;
CREATE ALGORITHM=UNDEFINED DEFINER=`user_sistema`@`189.112.64.46` SQL SECURITY INVOKER VIEW `VW_Usuarios_logs` AS select `U`.`idEmpresa` AS `idEmpresa`,`U`.`idUsuario` AS `idUsuario`,count(`L1`.`idLog`) AS `qtd` from ((`rh_logs` `L1` join `rh_logins` `L2` on((`L2`.`idLogin` = `L1`.`idLogin`))) join `rh_usuarios` `U` on((`U`.`idUsuario` = `L2`.`idUsuario`))) group by `U`.`idEmpresa`,`U`.`idUsuario`;

-- ----------------------------
-- View: VW_Usuarios_ultima
-- ----------------------------
DROP VIEW IF EXISTS `VW_Usuarios_ultima`;
CREATE ALGORITHM=UNDEFINED DEFINER=`user_sistema`@`189.112.64.46` SQL SECURITY INVOKER VIEW `VW_Usuarios_ultima` AS select distinct `L`.`idEmpresa` AS `idEmpresa`,`L`.`idUsuario` AS `idUsuario`,(select max(`X`.`dtLogin`) from `rh_logins` `X` where (`X`.`idUsuario` = `L`.`idUsuario`)) AS `ultima` from `rh_logins` `L`;

-- ----------------------------
-- View: VW_colabxsuper
-- ----------------------------
DROP VIEW IF EXISTS `VW_colabxsuper`;
CREATE ALGORITHM=UNDEFINED DEFINER=`user_sistema`@`189.112.64.46` SQL SECURITY INVOKER VIEW `VW_colabxsuper` AS select `C`.`idColab` AS `idColab`,(select `C2`.`idColab` from `rh_colaboradores` `C2` where (`C2`.`idOrgao` = `O`.`idSupervisor`)) AS `idColabSuper` from (`rh_colaboradores` `C` left join `rh_organograma` `O` on((`O`.`idOrgao` = `C`.`idOrgao`)));

-- ----------------------------
-- View: VW_emails
-- ----------------------------
DROP VIEW IF EXISTS `VW_emails`;
CREATE ALGORITHM=UNDEFINED DEFINER=`user_sistema`@`189.112.64.46` SQL SECURITY INVOKER VIEW `VW_emails` AS select distinct `rh_pessoas`.`nome` AS `nome`,`rh_pessoas`.`email` AS `email`,'Pessoal' AS `tipo` from `rh_pessoas` where ((`rh_pessoas`.`email` is not null) and (char_length(trim(`rh_pessoas`.`email`)) > 5)) union select distinct `rh_pessoas`.`nome` AS `nome`,`rh_pessoas`.`email_corporativo` AS `email`,'Corporativo' AS `tipo` from `rh_pessoas` where ((`rh_pessoas`.`email_corporativo` is not null) and (char_length(trim(`rh_pessoas`.`email_corporativo`)) > 5)) order by `nome`;

SET FOREIGN_KEY_CHECKS=1;

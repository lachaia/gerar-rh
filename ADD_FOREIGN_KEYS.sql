-- ============================================================================
-- ADD_FOREIGN_KEYS.sql
-- Script para CRIAR chaves estrangeiras ausentes no banco `RH` (Sistema RH)
--
-- ✅ STATUS: EXECUTADO COM SUCESSO EM PRODUÇÃO. Confirmado via
-- information_schema: 236 FKs no banco agora (11 pré-existentes + 225 deste
-- script, incluindo as 24 da Categoria A liberadas depois da limpeza de
-- dados em DIAGNOSTICO_ORFAOS.md). Zero pendências das 225 relações que
-- este script continha. Mantido como referência histórica — ver a seção
-- final para o que ainda ficou de fora (Categoria B/C, dados órfãos de
-- verdade, e colunas ambíguas).
--
-- CONTEXTO: o banco tem 124 tabelas e, na data de geração deste script,
-- apenas 11 FKs estavam de fato declaradas (ver ANALISE_SISTEMA.md e
-- SCHEMA.sql). Este script foi construído cruzando o nome de cada coluna
-- "id*"/"*_id" com a chave primária correspondente (com atenção a colisões
-- de nome, ex.: idCargo existe em rh_cargos E em rh_cipa_cargos), e depois
-- CADA relação abaixo foi checada contra os dados reais de produção via
-- LEFT JOIN (linhas onde a coluna aponta para um registro que não existe
-- no "pai"). Só entraram aqui as relações com ZERO linhas órfãs.
--
-- Durante a execução, 2 obstáculos precisaram de correção (ambos aplicados,
-- ver histórico do git):
--   - rh_subsedes.subsede_id e rh_polos.polo_id precisaram de UNIQUE KEY
--     (usados como alvo de FK, mas não eram a chave primária da tabela).
--   - rh_cargos_historico.idTipoAlteracao era SMALLINT, mas o PK referenciado
--     (rh_cargos_tipo_alt.idTipoAlteracao) é INT — alargado para INT.
--
-- ON DELETE escolhido por categoria:
--   - RESTRICT   : dados "mestre"/centrais — nunca apaga em cascata
--   - CASCADE    : tabelas de detalhe que só existem em função do pai
--                  (ex.: currículo, documentos, e-mails de uma pessoa;
--                  linhas de um termo de equipamento; itens de vaga)
--   - SET NULL   : campos de auditoria (idLogin/login_id = "criado por"),
--                  auto-referências hierárquicas, e tabelas de HISTÓRICO
--                  onde o registro deve sobreviver mesmo que o "pai" seja
--                  excluído
--
-- IMPORTANTE: colunas idSubSede/subsede_id em quase todo o banco NÃO
-- referenciam rh_subsedes.id — referenciam rh_subsedes.subsede_id, um
-- código legado do sistema anterior à migração (ex.: id=1 tem
-- subsede_id=101). O mesmo vale para rs_vagas.polo_id, que referencia
-- rh_polos.polo_id (não rh_polos.id) — enquanto rh_colaboradores.polo_id já
-- usa o id novo. Ou seja, "polo_id" tem DOIS significados diferentes
-- dependendo da tabela. Ver DIAGNOSTICO_ORFAOS.md para detalhes.
-- ============================================================================

-- ============================================================================
-- RELAÇÕES NÃO INCLUÍDAS — ainda existem linhas órfãs de verdade em
-- produção (Categoria B/C do DIAGNOSTICO_ORFAOS.md; a Categoria A, sentinela
-- "0", já foi limpa e essas relações já foram aplicadas com sucesso).
-- Rode a query de diagnóstico (comentada) para ver os registros afetados,
-- decida se corrige o dado ou aponta para NULL, e só então adicione a FK.
-- ============================================================================

-- rh_enderecos.idPessoa -> rh_pessoas.idPessoa  (7 linha(s) orfa(s) — Categoria B, cluster de pessoas excluídas)
-- SELECT c.* FROM `rh_enderecos` c LEFT JOIN `rh_pessoas` p ON c.`idPessoa` = p.`idPessoa`
--   WHERE c.`idPessoa` IS NOT NULL AND p.`idPessoa` IS NULL;
-- ALTER TABLE `rh_enderecos` ADD CONSTRAINT `fk_rh_enderecos_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- rh_usuarios.idUsuarioGrupo -> rh_usuariosgrupo.idUsuarioGrupo  (1 linha(s) orfa(s) — Categoria B, falta a linha do grupo 8/Candidatos)
-- SELECT c.* FROM `rh_usuarios` c LEFT JOIN `rh_usuariosgrupo` p ON c.`idUsuarioGrupo` = p.`idUsuarioGrupo`
--   WHERE c.`idUsuarioGrupo` IS NOT NULL AND p.`idUsuarioGrupo` IS NULL;
-- ALTER TABLE `rh_usuarios` ADD CONSTRAINT `fk_rh_usuarios_idUsuarioGrupo` FOREIGN KEY (`idUsuarioGrupo`) REFERENCES `rh_usuariosgrupo` (`idUsuarioGrupo`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- rh_usuarios.idPessoa -> rh_pessoas.idPessoa  (1 linha(s) orfa(s) — Categoria B, jose.toledo)
-- SELECT c.* FROM `rh_usuarios` c LEFT JOIN `rh_pessoas` p ON c.`idPessoa` = p.`idPessoa`
--   WHERE c.`idPessoa` IS NOT NULL AND p.`idPessoa` IS NULL;
-- ALTER TABLE `rh_usuarios` ADD CONSTRAINT `fk_rh_usuarios_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- rh_cv.idPessoa -> rh_pessoas.idPessoa  (1 linha(s) orfa(s) — Categoria B, pessoa 13)
-- SELECT c.* FROM `rh_cv` c LEFT JOIN `rh_pessoas` p ON c.`idPessoa` = p.`idPessoa`
--   WHERE c.`idPessoa` IS NOT NULL AND p.`idPessoa` IS NULL;
-- ALTER TABLE `rh_cv` ADD CONSTRAINT `fk_rh_cv_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- rh_cv_fa.idPessoa -> rh_pessoas.idPessoa  (1 linha(s) orfa(s) — Categoria B, cluster de pessoas excluídas)
-- SELECT c.* FROM `rh_cv_fa` c LEFT JOIN `rh_pessoas` p ON c.`idPessoa` = p.`idPessoa`
--   WHERE c.`idPessoa` IS NOT NULL AND p.`idPessoa` IS NULL;
-- ALTER TABLE `rh_cv_fa` ADD CONSTRAINT `fk_rh_cv_fa_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- rh_cv_idiomas.idPessoa -> rh_pessoas.idPessoa  (1 linha(s) orfa(s) — Categoria B, cluster de pessoas excluídas)
-- SELECT c.* FROM `rh_cv_idiomas` c LEFT JOIN `rh_pessoas` p ON c.`idPessoa` = p.`idPessoa`
--   WHERE c.`idPessoa` IS NOT NULL AND p.`idPessoa` IS NULL;
-- ALTER TABLE `rh_cv_idiomas` ADD CONSTRAINT `fk_rh_cv_idiomas_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- rh_cv_conq.idPessoa -> rh_pessoas.idPessoa  (1 linha(s) orfa(s) — Categoria B, cluster de pessoas excluídas)
-- SELECT c.* FROM `rh_cv_conq` c LEFT JOIN `rh_pessoas` p ON c.`idPessoa` = p.`idPessoa`
--   WHERE c.`idPessoa` IS NOT NULL AND p.`idPessoa` IS NULL;
-- ALTER TABLE `rh_cv_conq` ADD CONSTRAINT `fk_rh_cv_conq_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- rh_emails.idDoc -> rh_documentos.idDoc  (1 linha(s) orfa(s) — Categoria B, documento 14 não existe mais)
-- SELECT c.* FROM `rh_emails` c LEFT JOIN `rh_documentos` p ON c.`idDoc` = p.`idDoc`
--   WHERE c.`idDoc` IS NOT NULL AND p.`idDoc` IS NULL;
-- ALTER TABLE `rh_emails` ADD CONSTRAINT `fk_rh_emails_idDoc` FOREIGN KEY (`idDoc`) REFERENCES `rh_documentos` (`idDoc`) ON DELETE SET NULL ON UPDATE CASCADE;

-- rh_pessoas_emg.idPessoa -> rh_pessoas.idPessoa  (3 linha(s) orfa(s) — Categoria B, cluster de pessoas excluídas)
-- SELECT c.* FROM `rh_pessoas_emg` c LEFT JOIN `rh_pessoas` p ON c.`idPessoa` = p.`idPessoa`
--   WHERE c.`idPessoa` IS NOT NULL AND p.`idPessoa` IS NULL;
-- ALTER TABLE `rh_pessoas_emg` ADD CONSTRAINT `fk_rh_pessoas_emg_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- rh_logs.idModulo -> sys_modulos.idModulo  (12 linha(s) orfa(s) — Categoria B, módulo 32 removido/renumerado)
-- SELECT c.* FROM `rh_logs` c LEFT JOIN `sys_modulos` p ON c.`idModulo` = p.`idModulo`
--   WHERE c.`idModulo` IS NOT NULL AND p.`idModulo` IS NULL;
-- ALTER TABLE `rh_logs` ADD CONSTRAINT `fk_rh_logs_idModulo` FOREIGN KEY (`idModulo`) REFERENCES `sys_modulos` (`idModulo`) ON DELETE SET NULL ON UPDATE CASCADE;

-- rh_ponto_solicitacoes.batida_id -> rh_ponto_registros.id  (3 linha(s) orfa(s) — Categoria B, registros de ponto inexistentes; sentinela 0 já foi limpo)
-- SELECT c.* FROM `rh_ponto_solicitacoes` c LEFT JOIN `rh_ponto_registros` p ON c.`batida_id` = p.`id`
--   WHERE c.`batida_id` IS NOT NULL AND p.`id` IS NULL;
-- ALTER TABLE `rh_ponto_solicitacoes` ADD CONSTRAINT `fk_rh_ponto_solicitacoes_batida_id` FOREIGN KEY (`batida_id`) REFERENCES `rh_ponto_registros` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- rh_ponto_auditoria.id_ponto -> rh_ponto_registros.id  (8919 linha(s) orfa(s) — Categoria C, NÃO recomendado, ver DIAGNOSTICO_ORFAOS.md)
-- SELECT c.* FROM `rh_ponto_auditoria` c LEFT JOIN `rh_ponto_registros` p ON c.`id_ponto` = p.`id`
--   WHERE c.`id_ponto` IS NOT NULL AND p.`id` IS NULL;
-- ALTER TABLE `rh_ponto_auditoria` ADD CONSTRAINT `fk_rh_ponto_auditoria_id_ponto` FOREIGN KEY (`id_ponto`) REFERENCES `rh_ponto_registros` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================================================
-- COLUNAS AMBÍGUAS / NÃO RESOLVIDAS — não incluídas por falta de certeza:
-- ============================================================================
-- rh_ponto_solicitacoes.supervisor_id  -> poderia ser rh_colaboradores.idColab ou rh_usuarios.idUsuario
-- rs_vagas.solicitante_id              -> tabela de origem não identificada com certeza
-- rs_vagas.recrutador_id               -> idem
-- rh_perfis.idPerfil                   -> não existe tabela de "perfis"/permissões no schema
-- rh_logs.idOperacao                   -> não existe tabela de "operações" no schema
-- rh_equip_solic.glpi_id               -> referência a sistema externo (GLPI), fora deste banco
-- rh_colaboradores.esocial_id          -> referência a sistema externo (eSocial), fora deste banco
-- rh_colaboradores.idStatusOld         -> campo legado, sem tabela de referência clara
-- rh_pessoas.idGrauEscola / rh_autocadastro.idGrauEscola / rh_colaboradores_hist.idGrauEscolaridade
--                                       -> possivelmente rh_graus_instrucao.id, mas nome não bate exatamente; confirmar com a aplicação
-- rh_pessoas_ldt.idOrigem + origem     -> referência polimórfica (tabela alvo depende do valor de "origem"); não modelável como FK simples
-- rh_equip_termos.origem               -> mesmo padrão acima
-- rs_motivos_vaga                      -> nenhuma coluna encontrada que referencie esta tabela (possível tabela legada/duplicada de rs_vagas_mot)

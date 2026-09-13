-- ============================================================================
-- ADD_FOREIGN_KEYS.sql
-- Script para CRIAR chaves estrangeiras ausentes no banco `RH` (Sistema RH)
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
-- IMPORTANTE — LEIA ANTES DE RODAR EM PRODUÇÃO:
--   1) Faça backup completo do banco antes de aplicar (mysqldump).
--   2) Rode primeiro em uma cópia/homologação, nunca direto em produção.
--   3) Este script assume InnoDB em todas as tabelas (confirmado: 124/124).
--   4) Algumas ALTERs podem demorar em tabelas grandes (ex. rh_logs,
--      rh_ponto_registros) — considere rodar fora do horário de pico.
--   5) ON DELETE escolhido por categoria:
--        - RESTRICT   : dados "mestre"/centrais — nunca apaga em cascata
--        - CASCADE    : tabelas de detalhe que só existem em função do pai
--                       (ex.: currículo, documentos, e-mails de uma pessoa;
--                       linhas de um termo de equipamento; itens de vaga)
--        - SET NULL   : campos de auditoria (idLogin/login_id = "criado
--                       por"), auto-referências hierárquicas, e tabelas de
--                       HISTÓRICO onde o registro deve sobreviver mesmo que
--                       o "pai" seja excluído
--      Ajuste conforme a política de negócio antes de aplicar.
--   6) Ficaram DE FORA deste script (ver seção final) as relações onde
--      foram encontradas linhas órfãs em produção — precisam de limpeza
--      de dados (ou definição de regra de negócio) antes de virar FK.
--      Diagnóstico completo dessas linhas em DIAGNOSTICO_ORFAOS.md.
--   6b) IMPORTANTE: colunas idSubSede/subsede_id em quase todo o banco NÃO
--      referenciam rh_subsedes.id — referenciam rh_subsedes.subsede_id, um
--      código legado do sistema anterior à migração (ex.: id=1 tem
--      subsede_id=101). O mesmo vale para rs_vagas.polo_id, que referencia
--      rh_polos.polo_id (não rh_polos.id) — enquanto rh_colaboradores.polo_id
--      já usa o id novo. Ou seja, "polo_id" tem DOIS significados diferentes
--      dependendo da tabela. Ver DIAGNOSTICO_ORFAOS.md para detalhes.
--   7) Também ficaram de fora ~18 colunas ambíguas (nome genérico demais,
--      tipo incompatível, ou referência polimórfica) — não incluídas por
--      falta de certeza, não por erro. Ver lista ao final.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- PRÉ-REQUISITO: duas colunas usadas como alvo de FK abaixo NÃO são a chave
-- primária das tabelas de origem — são um "código legado" do sistema anterior
-- à migração (ex.: rh_subsedes.id=1 tem subsede_id=101). Confirmado contra os
-- dados reais: são únicos em 100% das linhas hoje, mas não têm um índice
-- UNIQUE declarado. Isso é pré-requisito do MySQL para virar alvo de FK.
-- ----------------------------------------------------------------------------
ALTER TABLE `rh_subsedes` ADD UNIQUE KEY `uk_rh_subsedes_subsede_id` (`subsede_id`);
ALTER TABLE `rh_polos`    ADD UNIQUE KEY `uk_rh_polos_polo_id` (`polo_id`);

-- ----------------------------------------------------------------------------
-- PRÉ-REQUISITO 2: rh_cargos_historico.idTipoAlteracao estava como SMALLINT,
-- mas rh_cargos_tipo_alt.idTipoAlteracao (o PK referenciado) é INT — o
-- InnoDB recusa FK entre tipos incompatíveis (erro 3780). Alargar de
-- SMALLINT para INT é seguro (tabela tem 1 linha só, valor 1).
-- ----------------------------------------------------------------------------
ALTER TABLE `rh_cargos_historico` MODIFY `idTipoAlteracao` INT NULL;

-- ----------------------------------------------------------------
-- Tabela: rh_afastamento_tipos
-- ----------------------------------------------------------------
ALTER TABLE `rh_afastamento_tipos`
  ADD CONSTRAINT `fk_rh_afastamento_tipos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_afastamentos
-- ----------------------------------------------------------------
ALTER TABLE `rh_afastamentos`
  ADD CONSTRAINT `fk_rh_afastamentos_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_afastamentos_idTipo` FOREIGN KEY (`idTipo`) REFERENCES `rh_afastamento_tipos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_afastamentos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_afastamentos_idUsuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_atendimentos
-- ----------------------------------------------------------------
ALTER TABLE `rh_atendimentos`
  ADD CONSTRAINT `fk_rh_atendimentos_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_atendimentos_idBrigadista` FOREIGN KEY (`idBrigadista`) REFERENCES `rh_brigadistas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_atendimentos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_avaliacoes
-- ----------------------------------------------------------------
ALTER TABLE `rh_avaliacoes`
  ADD CONSTRAINT `fk_rh_avaliacoes_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_avaliacoes_idTipo` FOREIGN KEY (`idTipo`) REFERENCES `rh_avaliacao_tipos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_avaliacoes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_avaliacoes_idColabSuper` FOREIGN KEY (`idColabSuper`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_brigada_acoes
-- ----------------------------------------------------------------
ALTER TABLE `rh_brigada_acoes`
  ADD CONSTRAINT `fk_rh_brigada_acoes_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_brigada_acoes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_brigada_atend_membros
-- ----------------------------------------------------------------
ALTER TABLE `rh_brigada_atend_membros`
  ADD CONSTRAINT `fk_rh_brigada_atend_membros_idAtendimento` FOREIGN KEY (`idAtendimento`) REFERENCES `rh_atendimentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_brigada_atend_membros_idBrigadista` FOREIGN KEY (`idBrigadista`) REFERENCES `rh_brigadistas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_brigada_reunioes
-- ----------------------------------------------------------------
ALTER TABLE `rh_brigada_reunioes`
  ADD CONSTRAINT `fk_rh_brigada_reunioes_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_brigada_reunioes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_brigada_tipo_ocorrencia
-- ----------------------------------------------------------------
ALTER TABLE `rh_brigada_tipo_ocorrencia`
  ADD CONSTRAINT `fk_rh_brigada_tipo_ocorrencia_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_brigadistas
-- ----------------------------------------------------------------
ALTER TABLE `rh_brigadistas`
  ADD CONSTRAINT `fk_rh_brigadistas_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_brigadistas_idCargoBrigada` FOREIGN KEY (`idCargoBrigada`) REFERENCES `rh_brigada_cargos` (`idCargoBrigada`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_brigadistas_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_brigadistas_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cargos
-- ----------------------------------------------------------------
ALTER TABLE `rh_cargos`
  ADD CONSTRAINT `fk_rh_cargos_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cargos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cargos_historico
-- ----------------------------------------------------------------
ALTER TABLE `rh_cargos_historico`
  ADD CONSTRAINT `fk_rh_cargos_historico_idColaborador` FOREIGN KEY (`idColaborador`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cargos_historico_idCargo_anterior` FOREIGN KEY (`idCargo_anterior`) REFERENCES `rh_cargos` (`idCargo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cargos_historico_idCargo_atual` FOREIGN KEY (`idCargo_atual`) REFERENCES `rh_cargos` (`idCargo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cargos_historico_idTipoAlteracao` FOREIGN KEY (`idTipoAlteracao`) REFERENCES `rh_cargos_tipo_alt` (`idTipoAlteracao`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cipa_acoes
-- ----------------------------------------------------------------
ALTER TABLE `rh_cipa_acoes`
  ADD CONSTRAINT `fk_rh_cipa_acoes_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cipa_acoes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cipa_atendimentos
-- ----------------------------------------------------------------
ALTER TABLE `rh_cipa_atendimentos`
  ADD CONSTRAINT `fk_rh_cipa_atendimentos_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cipa_atendimentos_idCipeiro` FOREIGN KEY (`idCipeiro`) REFERENCES `rh_cipeiros` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cipa_atendimentos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cipa_reunioes
-- ----------------------------------------------------------------
ALTER TABLE `rh_cipa_reunioes`
  ADD CONSTRAINT `fk_rh_cipa_reunioes_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cipa_reunioes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cipa_tipo_ocorrencia
-- ----------------------------------------------------------------
ALTER TABLE `rh_cipa_tipo_ocorrencia`
  ADD CONSTRAINT `fk_rh_cipa_tipo_ocorrencia_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cipeiros
-- ----------------------------------------------------------------
ALTER TABLE `rh_cipeiros`
  ADD CONSTRAINT `fk_rh_cipeiros_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cipeiros_idCargo` FOREIGN KEY (`idCargo`) REFERENCES `rh_cipa_cargos` (`idCargo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cipeiros_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cipeiros_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_colaboradores
-- ----------------------------------------------------------------
ALTER TABLE `rh_colaboradores`
  ADD CONSTRAINT `fk_rh_colaboradores_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idOrgao` FOREIGN KEY (`idOrgao`) REFERENCES `rh_organograma` (`idOrgao`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idCargo` FOREIGN KEY (`idCargo`) REFERENCES `rh_cargos` (`idCargo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idFuncao` FOREIGN KEY (`idFuncao`) REFERENCES `rh_funcoes` (`idFuncao`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idContratoTipo` FOREIGN KEY (`idContratoTipo`) REFERENCES `rh_contratos_tipo` (`idContratoTipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idRescisao` FOREIGN KEY (`idRescisao`) REFERENCES `rh_rescisoes` (`idRescisao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idRescisaoTipo` FOREIGN KEY (`idRescisaoTipo`) REFERENCES `rh_rescisao_tipos` (`idTipoRescisao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idStatus` FOREIGN KEY (`idStatus`) REFERENCES `rh_colaboradores_status` (`idStatus`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idEnderecoTrab` FOREIGN KEY (`idEnderecoTrab`) REFERENCES `rh_enderecos` (`idEndereco`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_polo_id` FOREIGN KEY (`polo_id`) REFERENCES `rh_polos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_colaboradores_hist
-- ----------------------------------------------------------------
ALTER TABLE `rh_colaboradores_hist`
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idOrgao` FOREIGN KEY (`idOrgao`) REFERENCES `rh_organograma` (`idOrgao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idCargo` FOREIGN KEY (`idCargo`) REFERENCES `rh_cargos` (`idCargo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idFuncao` FOREIGN KEY (`idFuncao`) REFERENCES `rh_funcoes` (`idFuncao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idContratoTipo` FOREIGN KEY (`idContratoTipo`) REFERENCES `rh_contratos_tipo` (`idContratoTipo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idRescisao` FOREIGN KEY (`idRescisao`) REFERENCES `rh_rescisoes` (`idRescisao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idRescisaoTipo` FOREIGN KEY (`idRescisaoTipo`) REFERENCES `rh_rescisao_tipos` (`idTipoRescisao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idStatus` FOREIGN KEY (`idStatus`) REFERENCES `rh_colaboradores_status` (`idStatus`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idBanco` FOREIGN KEY (`idBanco`) REFERENCES `rh_bancos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idEnderecoTrab` FOREIGN KEY (`idEnderecoTrab`) REFERENCES `rh_enderecos` (`idEndereco`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ctr_exp
-- ----------------------------------------------------------------
ALTER TABLE `rh_ctr_exp`
  ADD CONSTRAINT `fk_rh_ctr_exp_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ctr_exp_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cv
-- ----------------------------------------------------------------
ALTER TABLE `rh_cv`
  ADD CONSTRAINT `fk_rh_cv_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_idCidade` FOREIGN KEY (`idCidade`) REFERENCES `rh_cidades` (`idCidade`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cv_conq
-- ----------------------------------------------------------------
ALTER TABLE `rh_cv_conq`
  ADD CONSTRAINT `fk_rh_cv_conq_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_conq_idConqTipo` FOREIGN KEY (`idConqTipo`) REFERENCES `rh_conqTipos` (`idConqTipo`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cv_exp
-- ----------------------------------------------------------------
ALTER TABLE `rh_cv_exp`
  ADD CONSTRAINT `fk_rh_cv_exp_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cv_fa
-- ----------------------------------------------------------------
ALTER TABLE `rh_cv_fa`
  ADD CONSTRAINT `fk_rh_cv_fa_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_fa_idInstituicao` FOREIGN KEY (`idInstituicao`) REFERENCES `rh_fa_instituicoes` (`idInstituicao`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_fa_idNivel` FOREIGN KEY (`idNivel`) REFERENCES `rh_fa_niveis` (`idNivel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_fa_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_fa_idDoc` FOREIGN KEY (`idDoc`) REFERENCES `rh_documentos` (`idDoc`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_cv_idiomas
-- ----------------------------------------------------------------
ALTER TABLE `rh_cv_idiomas`
  ADD CONSTRAINT `fk_rh_cv_idiomas_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_idiomas_idIdioma` FOREIGN KEY (`idIdioma`) REFERENCES `rh_idiomas` (`idIdioma`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_idiomas_idFluencia` FOREIGN KEY (`idFluencia`) REFERENCES `rh_fluencias` (`idFluencia`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_dependentes
-- ----------------------------------------------------------------
ALTER TABLE `rh_dependentes`
  ADD CONSTRAINT `fk_rh_dependentes_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_dependentes_idPessoaDep` FOREIGN KEY (`idPessoaDep`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_dependentes_idParentesco` FOREIGN KEY (`idParentesco`) REFERENCES `rh_parentescos` (`idParentesco`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_dependentes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_documentos
-- ----------------------------------------------------------------
ALTER TABLE `rh_documentos`
  ADD CONSTRAINT `fk_rh_documentos_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_documentos_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_documentos_idTipoDoc` FOREIGN KEY (`idTipoDoc`) REFERENCES `rh_docs_tipo` (`idTipoDoc`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_emails
-- ----------------------------------------------------------------
ALTER TABLE `rh_emails`
  ADD CONSTRAINT `fk_rh_emails_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_emails_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_emails_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_empresas
-- ----------------------------------------------------------------
ALTER TABLE `rh_empresas`
  ADD CONSTRAINT `fk_rh_empresas_idEndereco` FOREIGN KEY (`idEndereco`) REFERENCES `rh_enderecos` (`idEndereco`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_enderecos
-- ----------------------------------------------------------------
ALTER TABLE `rh_enderecos`
  ADD CONSTRAINT `fk_rh_enderecos_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_enderecos_idTipoEndereco` FOREIGN KEY (`idTipoEndereco`) REFERENCES `rh_enderecos_tipo` (`idTipoEndereco`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_enderecos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_equip_solic
-- ----------------------------------------------------------------
ALTER TABLE `rh_equip_solic`
  ADD CONSTRAINT `fk_rh_equip_solic_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_equip_termos
-- ----------------------------------------------------------------
ALTER TABLE `rh_equip_termos`
  ADD CONSTRAINT `fk_rh_equip_termos_idSolic` FOREIGN KEY (`idSolic`) REFERENCES `rh_equip_solic` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_equip_termos_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_equip_termos_idModelo` FOREIGN KEY (`idModelo`) REFERENCES `rh_equip_modelos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_equip_termos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_equip_termos_ld
-- ----------------------------------------------------------------
ALTER TABLE `rh_equip_termos_ld`
  ADD CONSTRAINT `fk_rh_equip_termos_ld_idEquipTermo` FOREIGN KEY (`idEquipTermo`) REFERENCES `rh_equip_termos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_equip_termos_ld_idTipo` FOREIGN KEY (`idTipo`) REFERENCES `rh_equip_tipos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_equip_tipos
-- ----------------------------------------------------------------
ALTER TABLE `rh_equip_tipos`
  ADD CONSTRAINT `fk_rh_equip_tipos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_exames
-- ----------------------------------------------------------------
ALTER TABLE `rh_exames`
  ADD CONSTRAINT `fk_rh_exames_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_exames_idExameTipo` FOREIGN KEY (`idExameTipo`) REFERENCES `rh_exames_tipos` (`idExameTipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_exames_idDoc` FOREIGN KEY (`idDoc`) REFERENCES `rh_documentos` (`idDoc`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_exames_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_fa_niveis
-- ----------------------------------------------------------------
ALTER TABLE `rh_fa_niveis`
  ADD CONSTRAINT `fk_rh_fa_niveis_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ferias
-- ----------------------------------------------------------------
ALTER TABLE `rh_ferias`
  ADD CONSTRAINT `fk_rh_ferias_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ferias_idColabSupervisor` FOREIGN KEY (`idColabSupervisor`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ferias_repro
-- ----------------------------------------------------------------
ALTER TABLE `rh_ferias_repro`
  ADD CONSTRAINT `fk_rh_ferias_repro_idFerias` FOREIGN KEY (`idFerias`) REFERENCES `rh_ferias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ferias_repro_idUsuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_funcoes
-- ----------------------------------------------------------------
ALTER TABLE `rh_funcoes`
  ADD CONSTRAINT `fk_rh_funcoes_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_funcoes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_historico_cargo
-- ----------------------------------------------------------------
ALTER TABLE `rh_historico_cargo`
  ADD CONSTRAINT `fk_rh_historico_cargo_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_historico_cargo_idCargo` FOREIGN KEY (`idCargo`) REFERENCES `rh_cargos` (`idCargo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_historico_cargo_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_historico_funcao
-- ----------------------------------------------------------------
ALTER TABLE `rh_historico_funcao`
  ADD CONSTRAINT `fk_rh_historico_funcao_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_historico_funcao_idFuncao` FOREIGN KEY (`idFuncao`) REFERENCES `rh_funcoes` (`idFuncao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_historico_funcao_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_historico_orgao
-- ----------------------------------------------------------------
ALTER TABLE `rh_historico_orgao`
  ADD CONSTRAINT `fk_rh_historico_orgao_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_historico_orgao_idOrgao` FOREIGN KEY (`idOrgao`) REFERENCES `rh_organograma` (`idOrgao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_historico_orgao_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_historico_sal
-- ----------------------------------------------------------------
ALTER TABLE `rh_historico_sal`
  ADD CONSTRAINT `fk_rh_historico_sal_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_historico_sal_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_logins
-- ----------------------------------------------------------------
ALTER TABLE `rh_logins`
  ADD CONSTRAINT `fk_rh_logins_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_logs
-- ----------------------------------------------------------------
ALTER TABLE `rh_logs`
  ADD CONSTRAINT `fk_rh_logs_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_notificacoes
-- ----------------------------------------------------------------
ALTER TABLE `rh_notificacoes`
  ADD CONSTRAINT `fk_rh_notificacoes_idUsuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_notificacoes_colaborador_id` FOREIGN KEY (`colaborador_id`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_notificacoes_idTipo` FOREIGN KEY (`idTipo`) REFERENCES `rh_notificacoes_tipo` (`idTipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_notificacoes_idEvento` FOREIGN KEY (`idEvento`) REFERENCES `rh_notificacoes_eventos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_notificacoes_usuarios
-- ----------------------------------------------------------------
ALTER TABLE `rh_notificacoes_usuarios`
  ADD CONSTRAINT `fk_rh_notificacoes_usuarios_idEvento` FOREIGN KEY (`idEvento`) REFERENCES `rh_notificacoes_eventos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_notificacoes_usuarios_idUsuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_notificacoes_usuarios_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_organograma
-- ----------------------------------------------------------------
ALTER TABLE `rh_organograma`
  ADD CONSTRAINT `fk_rh_organograma_idSupervisor` FOREIGN KEY (`idSupervisor`) REFERENCES `rh_organograma` (`idOrgao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_organograma_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ouvidoria_ldt
-- ----------------------------------------------------------------
ALTER TABLE `rh_ouvidoria_ldt`
  ADD CONSTRAINT `fk_rh_ouvidoria_ldt_idAcaoTipo` FOREIGN KEY (`idAcaoTipo`) REFERENCES `rh_ouvidoria_tldt` (`idAcaoTipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ouvidoria_ldt_idDenuncia` FOREIGN KEY (`idDenuncia`) REFERENCES `rh_ouvidoria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ouvidoria_ldt_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_perfis
-- ----------------------------------------------------------------
ALTER TABLE `rh_perfis`
  ADD CONSTRAINT `fk_rh_perfis_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_pessoas
-- ----------------------------------------------------------------
ALTER TABLE `rh_pessoas`
  ADD CONSTRAINT `fk_rh_pessoas_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_pessoas_emg
-- ----------------------------------------------------------------
ALTER TABLE `rh_pessoas_emg`
  ADD CONSTRAINT `fk_rh_pessoas_emg_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_emg_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_pessoas_ldt
-- ----------------------------------------------------------------
ALTER TABLE `rh_pessoas_ldt`
  ADD CONSTRAINT `fk_rh_pessoas_ldt_idAcaoTipo` FOREIGN KEY (`idAcaoTipo`) REFERENCES `rh_ldt_tipos` (`idAcaoTipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_ldt_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_ldt_idEmail` FOREIGN KEY (`idEmail`) REFERENCES `rh_emails` (`idEmail`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_ldt_idDoc` FOREIGN KEY (`idDoc`) REFERENCES `rh_documentos` (`idDoc`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_polos
-- ----------------------------------------------------------------
ALTER TABLE `rh_polos`
  ADD CONSTRAINT `fk_rh_polos_subsede_id` FOREIGN KEY (`subsede_id`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_polos_cidade_id` FOREIGN KEY (`cidade_id`) REFERENCES `rh_cidades` (`idCidade`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_polos_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ponto_banco_horas
-- ----------------------------------------------------------------
ALTER TABLE `rh_ponto_banco_horas`
  ADD CONSTRAINT `fk_rh_ponto_banco_horas_colaborador_id` FOREIGN KEY (`colaborador_id`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ponto_banco_horas_cidade_id` FOREIGN KEY (`cidade_id`) REFERENCES `rh_cidades` (`idCidade`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ponto_banco_saldo
-- ----------------------------------------------------------------
ALTER TABLE `rh_ponto_banco_saldo`
  ADD CONSTRAINT `fk_rh_ponto_banco_saldo_banco_id` FOREIGN KEY (`banco_id`) REFERENCES `rh_ponto_banco` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ponto_banco_saldo_colaborador_id` FOREIGN KEY (`colaborador_id`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ponto_banco_saldo_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ponto_calendario
-- ----------------------------------------------------------------
ALTER TABLE `rh_ponto_calendario`
  ADD CONSTRAINT `fk_rh_ponto_calendario_cidade_id` FOREIGN KEY (`cidade_id`) REFERENCES `rh_cidades` (`idCidade`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ponto_enderecos
-- ----------------------------------------------------------------
ALTER TABLE `rh_ponto_enderecos`
  ADD CONSTRAINT `fk_rh_ponto_enderecos_colaborador_id` FOREIGN KEY (`colaborador_id`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ponto_espelhos
-- ----------------------------------------------------------------
ALTER TABLE `rh_ponto_espelhos`
  ADD CONSTRAINT `fk_rh_ponto_espelhos_colaborador_id` FOREIGN KEY (`colaborador_id`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ponto_registros
-- ----------------------------------------------------------------
ALTER TABLE `rh_ponto_registros`
  ADD CONSTRAINT `fk_rh_ponto_registros_colaborador_id` FOREIGN KEY (`colaborador_id`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ponto_registros_endereco_id` FOREIGN KEY (`endereco_id`) REFERENCES `rh_ponto_enderecos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ponto_registros_solicitacao_id` FOREIGN KEY (`solicitacao_id`) REFERENCES `rh_ponto_solicitacoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_ponto_solicitacoes
-- ----------------------------------------------------------------
ALTER TABLE `rh_ponto_solicitacoes`
  ADD CONSTRAINT `fk_rh_ponto_solicitacoes_colaborador_id` FOREIGN KEY (`colaborador_id`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ponto_solicitacoes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_rescisoes
-- ----------------------------------------------------------------
ALTER TABLE `rh_rescisoes`
  ADD CONSTRAINT `fk_rh_rescisoes_idTipoRescisao` FOREIGN KEY (`idTipoRescisao`) REFERENCES `rh_rescisao_tipos` (`idTipoRescisao`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_rescisoes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_salarios
-- ----------------------------------------------------------------
ALTER TABLE `rh_salarios`
  ADD CONSTRAINT `fk_rh_salarios_idCargo` FOREIGN KEY (`idCargo`) REFERENCES `rh_cargos` (`idCargo`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_subsedes
-- ----------------------------------------------------------------
ALTER TABLE `rh_subsedes`
  ADD CONSTRAINT `fk_rh_subsedes_cidade_id` FOREIGN KEY (`cidade_id`) REFERENCES `rh_cidades` (`idCidade`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_subsedes_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_termos
-- ----------------------------------------------------------------
ALTER TABLE `rh_termos`
  ADD CONSTRAINT `fk_rh_termos_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_termos_idTipoTermo` FOREIGN KEY (`idTipoTermo`) REFERENCES `rh_termos_tipos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_termos_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_token
-- ----------------------------------------------------------------
ALTER TABLE `rh_token`
  ADD CONSTRAINT `fk_rh_token_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_token_idUsuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_user_candidatos
-- ----------------------------------------------------------------
ALTER TABLE `rh_user_candidatos`
  ADD CONSTRAINT `fk_rh_user_candidatos_pessoa_id` FOREIGN KEY (`pessoa_id`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_usuarios
-- ----------------------------------------------------------------
ALTER TABLE `rh_usuarios`
  ADD CONSTRAINT `fk_rh_usuarios_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_usuarios_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rh_usuariosgrupo
-- ----------------------------------------------------------------
ALTER TABLE `rh_usuariosgrupo`
  ADD CONSTRAINT `fk_rh_usuariosgrupo_idEmpresa` FOREIGN KEY (`idEmpresa`) REFERENCES `rh_empresas` (`idEmpresa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_usuariosgrupo_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_candidatos_origem
-- ----------------------------------------------------------------
ALTER TABLE `rs_candidatos_origem`
  ADD CONSTRAINT `fk_rs_candidatos_origem_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_candidatos_parecer
-- ----------------------------------------------------------------
ALTER TABLE `rs_candidatos_parecer`
  ADD CONSTRAINT `fk_rs_candidatos_parecer_candidatura_id` FOREIGN KEY (`candidatura_id`) REFERENCES `rs_vagas_candidaturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_competencias_comportamentais
-- ----------------------------------------------------------------
ALTER TABLE `rs_competencias_comportamentais`
  ADD CONSTRAINT `fk_rs_competencias_comportamentais_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_competencias_tecnicas
-- ----------------------------------------------------------------
ALTER TABLE `rs_competencias_tecnicas`
  ADD CONSTRAINT `fk_rs_competencias_tecnicas_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_superintendentes
-- ----------------------------------------------------------------
ALTER TABLE `rs_superintendentes`
  ADD CONSTRAINT `fk_rs_superintendentes_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_vagas
-- ----------------------------------------------------------------
ALTER TABLE `rs_vagas`
  ADD CONSTRAINT `fk_rs_vagas_subsede_id` FOREIGN KEY (`subsede_id`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_polo_id` FOREIGN KEY (`polo_id`) REFERENCES `rh_polos` (`polo_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_orgao_id` FOREIGN KEY (`orgao_id`) REFERENCES `rh_organograma` (`idOrgao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_cargo_id` FOREIGN KEY (`cargo_id`) REFERENCES `rh_cargos` (`idCargo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_status_id` FOREIGN KEY (`status_id`) REFERENCES `rs_vagas_status` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_fluxo_id` FOREIGN KEY (`fluxo_id`) REFERENCES `rs_vagas_fluxo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_motivo_id` FOREIGN KEY (`motivo_id`) REFERENCES `rs_vagas_mot` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_vagas_aprova
-- ----------------------------------------------------------------
ALTER TABLE `rs_vagas_aprova`
  ADD CONSTRAINT `fk_rs_vagas_aprova_vaga_id` FOREIGN KEY (`vaga_id`) REFERENCES `rs_vagas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_aprova_super_id` FOREIGN KEY (`super_id`) REFERENCES `rs_superintendentes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_vagas_candidaturas
-- ----------------------------------------------------------------
ALTER TABLE `rs_vagas_candidaturas`
  ADD CONSTRAINT `fk_rs_vagas_candidaturas_vaga_id` FOREIGN KEY (`vaga_id`) REFERENCES `rs_vagas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_candidaturas_pessoa_id` FOREIGN KEY (`pessoa_id`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_candidaturas_fluxo_id` FOREIGN KEY (`fluxo_id`) REFERENCES `rs_vagas_fluxo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rs_vagas_candidaturas_origem_id` FOREIGN KEY (`origem_id`) REFERENCES `rs_candidatos_origem` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_vagas_mot
-- ----------------------------------------------------------------
ALTER TABLE `rs_vagas_mot`
  ADD CONSTRAINT `fk_rs_vagas_mot_login_id` FOREIGN KEY (`login_id`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------
-- Tabela: rs_vagas_timeline
-- ----------------------------------------------------------------
ALTER TABLE `rs_vagas_timeline`
  ADD CONSTRAINT `fk_rs_vagas_timeline_vaga_id` FOREIGN KEY (`vaga_id`) REFERENCES `rs_vagas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ============================================================================
-- RELAÇÕES DA CATEGORIA A — tinham linhas órfãs (sentinela "0" em vez de
-- NULL), a limpeza de dados já foi aplicada em produção (ver
-- DIAGNOSTICO_ORFAOS.md) e reconfirmado por reconsulta (LEFT JOIN) que
-- ficaram com ZERO linhas órfãs. Prontas para rodar.
-- ============================================================================

ALTER TABLE `rh_pessoas`
  ADD CONSTRAINT `fk_rh_pessoas_idEstadoCivil` FOREIGN KEY (`idEstadoCivil`) REFERENCES `rh_estadoCivil` (`idEstadoCivil`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_idEtnia` FOREIGN KEY (`idEtnia`) REFERENCES `rh_etnias` (`idEtnia`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `rh_usuarios`
  ADD CONSTRAINT `fk_rh_usuarios_idColab` FOREIGN KEY (`idColab`) REFERENCES `rh_colaboradores` (`idColab`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_usuarios_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_colaboradores`
  ADD CONSTRAINT `fk_rh_colaboradores_idBanco` FOREIGN KEY (`idBanco`) REFERENCES `rh_bancos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idPlanoSaude` FOREIGN KEY (`idPlanoSaude`) REFERENCES `rh_planos_saude` (`idPlano`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idPlanoOdonto` FOREIGN KEY (`idPlanoOdonto`) REFERENCES `rh_planos_saude` (`idPlano`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_idCentroCusto` FOREIGN KEY (`idCentroCusto`) REFERENCES `rh_centrocusto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_cv_exp`
  ADD CONSTRAINT `fk_rh_cv_exp_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_cv_exp_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_cv_idiomas`
  ADD CONSTRAINT `fk_rh_cv_idiomas_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_cv_conq`
  ADD CONSTRAINT `fk_rh_cv_conq_idDoc` FOREIGN KEY (`idDoc`) REFERENCES `rh_documentos` (`idDoc`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_fa_instituicoes`
  ADD CONSTRAINT `fk_rh_fa_instituicoes_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_documentos`
  ADD CONSTRAINT `fk_rh_documentos_idLoginAprova` FOREIGN KEY (`idLoginAprova`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_pessoas_ldt`
  ADD CONSTRAINT `fk_rh_pessoas_ldt_idPessoa` FOREIGN KEY (`idPessoa`) REFERENCES `rh_pessoas` (`idPessoa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_ldt_idUsuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_pessoas_ldt_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_ouvidoria_ldt`
  ADD CONSTRAINT `fk_rh_ouvidoria_ldt_idUsuario` FOREIGN KEY (`idUsuario`) REFERENCES `rh_usuarios` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_ouvidoria_ldt_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_colaboradores_hist`
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idPlanoSaude` FOREIGN KEY (`idPlanoSaude`) REFERENCES `rh_planos_saude` (`idPlano`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idPlanoOdonto` FOREIGN KEY (`idPlanoOdonto`) REFERENCES `rh_planos_saude` (`idPlano`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idSubSede` FOREIGN KEY (`idSubSede`) REFERENCES `rh_subsedes` (`subsede_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rh_colaboradores_hist_idCentroCusto` FOREIGN KEY (`idCentroCusto`) REFERENCES `rh_centrocusto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rh_logs`
  ADD CONSTRAINT `fk_rh_logs_idLogin` FOREIGN KEY (`idLogin`) REFERENCES `rh_logins` (`idLogin`) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- RELAÇÕES NÃO INCLUÍDAS ACIMA — ainda existem linhas órfãs de verdade em
-- produção (Categoria B/C do DIAGNOSTICO_ORFAOS.md; a Categoria A, sentinela
-- "0", já foi limpa e essas relações já estão no bloco ativo acima).
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
-- rh_cv_conq.idLogin                   -> coluna está como varchar(45) (deveria ser int); corrigir o tipo antes de considerar FK
-- rs_motivos_vaga                      -> nenhuma coluna encontrada que referencie esta tabela (possível tabela legada/duplicada de rs_vagas_mot)

# Diagnóstico das linhas órfãs (para viabilizar as FKs pendentes)

> Investigação feita em cima do `ADD_FOREIGN_KEYS.sql` gerado anteriormente. Das 51 relações que ficaram de fora por terem linhas órfãs, **15 eram falso-positivo** (a FK apontava para a coluna errada — ver achado #1) e já foram corrigidas. Restam **36 relações genuinamente pendentes de decisão/limpeza**, catalogadas abaixo. Nenhuma alteração foi feita no banco — isto é só o diagnóstico.

## Achado principal: `idSubSede`/`subsede_id`/`polo_id` usam um código legado, não o `id`

Quase toda coluna `idSubSede`/`subsede_id` do banco (12 tabelas: `rh_colaboradores`, `rh_usuarios`, `rh_brigada_acoes`, `rh_brigada_reunioes`, `rh_brigadistas`, `rh_atendimentos`, `rh_cipa_acoes`, `rh_cipa_reunioes`, `rh_cipeiros`, `rh_cipa_atendimentos`, `rh_colaboradores_hist`, `rh_polos.subsede_id`, `rs_vagas.subsede_id`) **não referencia `rh_subsedes.id`** (1, 2, 3…) — referencia `rh_subsedes.subsede_id`, uma coluna de **código legado do sistema anterior à migração** (101, 102, 103…), confirmada única em produção (13 valores distintos em 13 linhas).

O mesmo padrão existe para polo, mas com uma armadilha adicional: **a coluna `polo_id` tem dois significados diferentes dependendo da tabela**:
- `rh_colaboradores.polo_id` já usa o `id` novo de `rh_polos` (0 órfãs contra `rh_polos.id`).
- `rs_vagas.polo_id` usa o código legado (0 órfãs contra `rh_polos.polo_id`, 4 órfãs contra `rh_polos.id`).

Isso já foi corrigido no `ADD_FOREIGN_KEYS.sql` (que agora referencia a coluna certa em cada caso, com `ALTER TABLE ... ADD UNIQUE KEY` como pré-requisito) e fez o total de relações limpas subir de 188 para **201**. Vale um alerta separado à equipe de desenvolvimento: qualquer código PHP que compare `polo_id` entre `rs_vagas` e outras tabelas sem saber dessa diferença pode estar produzindo joins errados hoje.

## Categoria A — Sentinela "0" usado no lugar de `NULL` (baixo risco, fácil de corrigir)

Padrão recorrente: campos opcionais foram gravados com `0` em vez de `NULL` (provavelmente por causa de `<select>` HTML sem opção vazia, ou de um `intval()` aplicado a string vazia no PHP). Uma vez zerados para `NULL`, a FK pode ser criada normalmente.

| Tabela.coluna | Linhas com valor 0 |
|---|---|
| `rh_pessoas.idEstadoCivil` | 1 |
| `rh_pessoas.idEtnia` | 3 |
| `rh_enderecos.idPessoa` | 1 (das 8 órfãs — as outras 7 são Categoria B) |
| `rh_usuarios.idColab` | 39 |
| `rh_usuarios.idLogin` | 1 |
| `rh_colaboradores.idBanco` | 2 |
| `rh_colaboradores.idPlanoSaude` | 6 |
| `rh_colaboradores.idPlanoOdonto` | 4 |
| `rh_colaboradores.idCentroCusto` | 2 |
| `rh_cv_exp.idPessoa` | 1 |
| `rh_cv_exp.idLogin` | 7 |
| `rh_cv_idiomas.idLogin` | 5 |
| `rh_cv_conq.idDoc` | 2 |
| `rh_fa_instituicoes.idLogin` | 2 |
| `rh_documentos.idLoginAprova` | 9 |
| `rh_pessoas_ldt.idPessoa` | 10 |
| `rh_pessoas_ldt.idUsuario` | 8 |
| `rh_pessoas_ldt.idLogin` | 8 |
| `rh_ouvidoria_ldt.idUsuario` | 2 |
| `rh_ouvidoria_ldt.idLogin` | 2 |
| `rh_colaboradores_hist.idPlanoSaude` | 24 |
| `rh_colaboradores_hist.idPlanoOdonto` | 5 |
| `rh_colaboradores_hist.idSubSede` | 2 |
| `rh_colaboradores_hist.idCentroCusto` | 10 |
| `rh_logs.idLogin` | 102 |
| `rh_logs.idModulo` (parte: valor 0) | 75 |
| `rh_ponto_solicitacoes.batida_id` (parte: valor 0) | 10 |

**Correção sugerida** (revisar e rodar manualmente, uma tabela por vez — não executado por mim):
```sql
UPDATE rh_pessoas          SET idEstadoCivil = NULL WHERE idEstadoCivil = 0;
UPDATE rh_pessoas          SET idEtnia = NULL WHERE idEtnia = 0;
UPDATE rh_enderecos        SET idPessoa = NULL WHERE idPessoa = 0;   -- CUIDADO: idPessoa é usado no join; confirmar se pode ser NULL na regra de negócio
UPDATE rh_usuarios         SET idColab = NULL WHERE idColab = 0;
UPDATE rh_usuarios         SET idLogin = NULL WHERE idLogin = 0;
UPDATE rh_colaboradores    SET idBanco = NULL WHERE idBanco = 0;
UPDATE rh_colaboradores    SET idPlanoSaude = NULL WHERE idPlanoSaude = 0;
UPDATE rh_colaboradores    SET idPlanoOdonto = NULL WHERE idPlanoOdonto = 0;
UPDATE rh_colaboradores    SET idCentroCusto = NULL WHERE idCentroCusto = 0;
UPDATE rh_cv_exp           SET idPessoa = NULL WHERE idPessoa = 0;
UPDATE rh_cv_exp           SET idLogin = NULL WHERE idLogin = 0;
UPDATE rh_cv_idiomas       SET idLogin = NULL WHERE idLogin = 0;
UPDATE rh_cv_conq          SET idDoc = NULL WHERE idDoc = 0;
UPDATE rh_fa_instituicoes  SET idLogin = NULL WHERE idLogin = 0;
UPDATE rh_documentos       SET idLoginAprova = NULL WHERE idLoginAprova = 0;
UPDATE rh_pessoas_ldt      SET idPessoa = NULL WHERE idPessoa = 0;
UPDATE rh_pessoas_ldt      SET idUsuario = NULL WHERE idUsuario = 0;
UPDATE rh_pessoas_ldt      SET idLogin = NULL WHERE idLogin = 0;
UPDATE rh_ouvidoria_ldt    SET idUsuario = NULL WHERE idUsuario = 0;
UPDATE rh_ouvidoria_ldt    SET idLogin = NULL WHERE idLogin = 0;
UPDATE rh_colaboradores_hist SET idPlanoSaude = NULL WHERE idPlanoSaude = 0;
UPDATE rh_colaboradores_hist SET idPlanoOdonto = NULL WHERE idPlanoOdonto = 0;
UPDATE rh_colaboradores_hist SET idSubSede = NULL WHERE idSubSede = 0;
UPDATE rh_colaboradores_hist SET idCentroCusto = NULL WHERE idCentroCusto = 0;
UPDATE rh_logs             SET idLogin = NULL WHERE idLogin = 0;
UPDATE rh_logs             SET idModulo = NULL WHERE idModulo = 0;
UPDATE rh_ponto_solicitacoes SET batida_id = NULL WHERE batida_id = 0;
```
Depois disso, essas FKs entram no `ADD_FOREIGN_KEYS.sql` sem problema.

**Nota sobre `rh_cv_conq.idLogin`**: essa coluna está como `varchar(45)` em vez de `int` — grava `"0"` e `"66"` como texto. Se for corrigida (tipo + valores), também vira candidata a FK para `rh_logins.idLogin`.

## Categoria B — Registros "pai" genuinamente ausentes (precisa de decisão do time)

Estes não são sentinela — apontam para um ID que nunca existiu ou foi apagado sem cascata:

| Relação | Valores órfãos | Observação |
|---|---|---|
| `rh_enderecos.idPessoa` | 74, 75, 78, 80, 81, 83, 37 | Mesmo cluster de "pessoas" aparece em várias tabelas abaixo — ver nota. |
| `rh_pessoas_emg.idPessoa` | 78, 81, 83 | Mesmo cluster. |
| `rh_cv_fa.idPessoa`, `rh_cv_idiomas.idPessoa`, `rh_cv_conq.idPessoa` | 83 | Mesmo cluster. |
| `rh_cv.idPessoa` | 13 | Pessoa isolada, fora do cluster acima. |
| `rh_usuarios.idPessoa` | 37 | Usuário `jose.toledo` (idColab 10) aponta pra uma pessoa que não existe mais. |
| `rh_usuarios.idUsuarioGrupo` | 8 | `rh_usuariosgrupo` **não tem** o grupo 8 (o código trata grupo 8 como "Candidatos" em `login_aj2.php`, mas a linha não existe na tabela de lookup). Usuária afetada: `flavia.queiroz` (idUsuario 40). |
| `rh_logs.idModulo` | 32 | `sys_modulos` não tem o módulo 32 — 12 linhas de log referenciam um módulo removido/renumerado. |
| `rh_emails.idDoc` | 14 | Documento 14 não existe mais em `rh_documentos`. |
| `rh_ponto_solicitacoes.batida_id` | 5, 90, 9446 | Registros de ponto que não existem (além dos 10 com sentinela `0`). |

**Sobre o cluster de pessoas 37/74/75/78/80/81/83/13**: são IDs baixos (a base de pessoas hoje tem centenas de registros), o que sugere que foram excluídas manualmente do `rh_pessoas` num momento inicial do sistema (teste, duplicidade, LGPD) sem que os registros dependentes (endereço, contato de emergência, formação acadêmica, idiomas, conquistas, usuário) fossem removidos junto. Decisão necessária: **restaurar** essas pessoas (se ainda existirem em backup) ou **excluir em cascata** os registros órfãos dependentes.

**Sobre `idUsuarioGrupo = 8`**: a correção mais simples é inserir a linha que falta:
```sql
-- revisar o nome antes de aplicar — é só um exemplo baseado no código (login_aj2.php trata grupo 8 como "Candidatos")
INSERT INTO rh_usuariosgrupo (idUsuarioGrupo, ...) VALUES (8, 'CANDIDATOS', ...);
```

## Categoria C — Tabelas de log/auditoria com numeração histórica quebrada (não recomendo FK)

- **`rh_ponto_auditoria.id_ponto` → `rh_ponto_registros.id`**: **8.919 linhas órfãs** (de um total de poucos milhares de linhas na tabela). Os valores órfãos são baixos e comuns (68, 69, 70, 71, 90, 97-104…), o que indica que `rh_ponto_registros` foi resetado/recarregado em algum momento (truncate + reimport, ou migração), invalidando a referência histórica do log de auditoria. **Não recomendo tentar "consertar" isso** — é volume grande demais para limpeza segura, e a tabela é um log de auditoria (`INSERT/UPDATE/DELETE` trigger-based, a julgar pela estrutura `valores_antigos`/`valores_novos` em JSON). Sugestão: manter sem FK, ou arquivar/truncar linhas anteriores à data do reset se não tiverem mais valor de auditoria.
- **`rh_logs`** (`idLogin`, `idModulo`): tabela de log de aplicação, mesma lógica — as 102+87 linhas afetadas já estão cobertas nas categorias A/B acima, mas vale considerar se realmente compensa colocar FK numa tabela de log que cresce indefinidamente (qualquer bug futuro de gravação passaria a *quebrar a operação do usuário* em vez de só gerar um log incompleto). Se decidirem manter a FK, `SET NULL` (como já está no script) é a escolha certa para não travar a aplicação.

## Resumo do que fazer

1. Rodar os `UPDATE` da Categoria A (baixo risco, revisão rápida) → libera 26 das 36 relações pendentes.
2. Decidir caso a caso os 9 itens da Categoria B (dados genuinamente inconsistentes) — não há uma correção "óbvia e segura" para aplicar sem intervenção humana.
3. Para a Categoria C (`rh_ponto_auditoria`), recomendo **não** adicionar a FK — o custo de limpar ~9 mil linhas de log histórico não compensa o benefício, e travar a auditoria não é desejável.
4. Depois disso, gerar a versão final do `ADD_FOREIGN_KEYS.sql` incluindo as relações que passarem a ficar limpas.

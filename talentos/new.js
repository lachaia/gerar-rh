function criarModalSeguro(id) {
  const el = document.getElementById(id);
  return el ? new bootstrap.Modal(el) : null;
}

const exp_Modal_Ver = criarModalSeguro("expModalVer");
const exp_Modal_Inc = criarModalSeguro("expModalInc");

const idi_Modal_Inc = criarModalSeguro("idiModalInc");

const fa_Modal_Ver = criarModalSeguro("faModalVer");
const fa_Modal_Inc = criarModalSeguro("faModalInc");
const fa_Modal_IncIE = criarModalSeguro("faModalIncIE");

const con_Modal_Ver = criarModalSeguro("conModalVer");
const con_Modal_Inc = criarModalSeguro("conModalInc");

$(document).ready(function () {
  // Inicialização do Summernote
  $('.summernote-edit').summernote({ // Dica: use uma classe comum em vez de IDs longos
    height: 150,
    lang: 'pt-BR',
    toolbar: [
      ['style', ['bold', 'italic', 'underline', 'clear']],
      ['font', ['strikethrough', 'superscript', 'subscript']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['insert', ['link']], // Removi video/picture para manter o banco leve, a menos que precise
      ['view', ['codeview']]
    ],
    callbacks: {
      onInit: function () {
        // Como você usa Dark Mode no sistema, isso garante visibilidade
        $('.note-editable').css('color', 'white');
        $('.note-editable').css('background-color', '#333');
      }
    }
  });

  // Controle do bloco de deficiência ao carregar
  toggleDeficiencia();

  // Evento para quando clicar no checkbox de deficiência
  $("#deficiente").on('change', function () {
    toggleDeficiencia();
  });

  //btnResetDiversidade();
});

// Função auxiliar para evitar repetição de código
function toggleDeficiencia() {
  if ($("#deficiente").prop("checked")) {
    $("#deficiencia-bloco").slideDown();
  } else {
    $("#deficiencia-bloco").slideUp();
    // Opcional: limpar os campos de deficiência ao esconder
  }
  let botoes = $("#bloco1 #btnDados");
  botoes.show();
}

function maskCPF(v) {
  v = v.replace(/\D/g, ""); // Remove tudo que não é dígito
  if (v.length > 11) v = v.slice(0, 11); // Limita a 11 dígitos

  // Aplica a formatação progressivamente
  v = v.replace(/(\d{3})(\d)/, "$1.$2");
  v = v.replace(/(\d{3})(\d)/, "$1.$2");
  v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  return v;
}

function maskTelefone(v) {
  v = v.replace(/\D/g, "");
  if (v.length > 11) v = v.slice(0, 11);

  if (v.length <= 10) {
    return v.replace(/(\d{2})(\d)/, "($1) $2").replace(/(\d{4})(\d)/, "$1-$2");
  } else {
    return v.replace(/(\d{2})(\d)/, "($1) $2").replace(/(\d{5})(\d)/, "$1-$2");
  }
}

// Alterei para focar APENAS na validação, sem mexer na máscara
function testar_cpf(o) {
  if (o.value === '') return;

  // Remove pontos e traços para validar o número puro
  var limpo = o.value.replace(/\D/g, '');

  if (validarCPF(limpo) == false) {
    alert('CPF inválido!');
    o.value = '';
    o.focus();
    return false;
  }
  ja_existe(limpo); //- testa se já existe o CV no banco
}

function ja_existe(cpf) {
  $.post("new_aj1.php", { cpf: cpf }, function (data) {
    //
    if (data.idColab > 0) {
      alert("CPF Encontrado no Sistema como Colaborador. O acesso ao currículo é pelo portal do Colaborador. Obrigado!");
      window.location.href = "../app/login.php";
    }
    if (data.idPessoa > 0) {
      $("#form_dados #idPessoa").val(data.idPessoa);
      $("#form_dados #idCV").val(data.idCV);
      $("#form_dados #nome").val(data.nome);
      // Demais campos do bloco 1 - já preenchidos antes (ex.: pessoa cancelou
      // o assistente no meio e voltou depois com o mesmo CPF).
      $("#form_dados #nomeSocial").val(data.nomeSocial);
      $("#form_dados #telefone").val(data.telefone);
      $("#form_dados #email").val(data.email);
      $("#form_dados #sexo").val(data.sexo);
      $("#form_dados #nacionalidade").val(data.nacionalidade);
      $("#form_dados #idGrauInstrucao").val(data.idGrauEscola);
      // Gênero é um grupo de rádio (todos com o mesmo id, então .val() não
      // funciona) - marca pelo value que bate com o já salvo em rh_cv.
      if (data.genero) {
        $('#form_dados input[name="genero"][value="' + data.genero + '"]').prop("checked", true);
      }
      $("#form_dados #linkedin").val(data.linkedin);
      testa_bloco1();

      // Demais blocos (experiência, formação, idiomas, conquistas, habilidades,
      // diversidade, currículo já enviado) - carregados à parte porque vêm de
      // várias tabelas diferentes.
      carregar_dados_existentes();
    }
    //
  }, "json");
}

//- Repopula os blocos que a pessoa já tinha preenchido numa visita anterior
//- (ex.: cancelou o assistente no meio e voltou depois com o mesmo CPF). Os
//- dados nunca foram perdidos (continuam salvos no banco) - só não apareciam
//- na tela, o que fazia parecer que tinham sumido.
function carregar_dados_existentes() {
  $.get("new_aj18_carrega_tudo.php", function (data) {
    if (!data.status) return;

    // --- DIVERSIDADE + CV JÁ ENVIADO ---
    const d = data.diversidade || {};
    if (d.arquivo) {
      $("#mensagemUpload").html("<div class='alert alert-info'>Você já enviou um currículo: <strong>" + d.arquivo + "</strong>. Envie outro arquivo aqui só se quiser substituí-lo.</div>");
      $("#blocoUpload").collapse("show");
    }
    if (d.deficiente == 1) {
      $("#deficiente").prop("checked", true);
      ["fisica", "visual", "auditiva", "mental", "intelectual", "autista"].forEach(function (campo) {
        $("#" + campo).prop("checked", d["def_" + campo] == 1);
      });
      $("#cid").val(d.cid && d.cid !== "0" ? d.cid : "");
      toggleDeficiencia();
    }
    const temDiversidade = d.deficiente == 1 || d.nmCidade || d.cor || d.pronome || d.orientacao || d.idGenero;
    if (d.nmCidade) {
      $("#formDiversidade #cidade").val(d.nmCidade + "/" + d.uf);
      $("#formDiversidade #cidade_id").val(d.idCidade);
    }
    if (d.cor) $("#formDiversidade #cor").val(d.cor);
    if (d.pronome) $("#formDiversidade #pronome").val(d.pronome);
    if (d.orientacao) $("#formDiversidade #orientacao").val(d.orientacao);
    if (d.idGenero) $("#formDiversidade #identgenero").val(d.idGenero);
    if (temDiversidade) $("#collapseDiversidade").collapse("show");

    // --- EXPERIÊNCIAS PROFISSIONAIS ---
    (data.experiencias || []).forEach(function (e) {
      const atual = e.ativo == 1 ? "Sim" : "Não";
      const linha = `
        <tr id="exp_${e.id}">
            <td class="text-center">${e.ano_ini}</td>
            <td class="text-center">${atual === "Sim" ? "---" : e.ano_fim}</td>
            <td class="text-center">${atual}</td>
            <td>${e.empresa}</td>
            <td>${e.cargo}</td>
            <td class="text-center align-middle" style="white-space: nowrap;">
                <button type="button" class="btn btn-outline-primary btn-sm botaozinho" onclick="exp_ver(${e.id})">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm botaozinho" onclick="exp_excluir(${e.id})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>`;
      $("#tbExp").append(linha);
    });
    if ((data.experiencias || []).length > 0) $("#collapseExp").collapse("show");

    // --- FORMAÇÃO ACADÊMICA ---
    (data.formacoes || []).forEach(function (f) {
      const linha = `
        <tr id="FA_${f.id}">
            <td class="text-center">${f.nivel}</td>
            <td class='text-center'>${f.curso}</td>
            <td class='text-center'>${f.sigla}</td>
            <td class='text-center'>${f.ano_conclusao}</td>
            <td class="text-center align-middle" style="white-space: nowrap;">
                <button type="button" class="btn btn-outline-primary btn-sm botaozinho" onclick="fa_ver(${f.id})">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm botaozinho" onclick="fa_excluir(${f.id})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>`;
      $("#tbFormacao").append(linha);
    });
    if ((data.formacoes || []).length > 0) $("#collapseFA").collapse("show");

    // --- IDIOMAS ---
    (data.idiomas || []).forEach(function (i) {
      const linha = `
        <tr id="idioma_${i.id}">
            <td>${i.idioma}</td>
            <td>${i.fluencia}</td>
            <td>${i.criado_em}</td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm botaozinho" onclick="idioma_excluir(${i.id})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>`;
      $("#tbIdiomas").append(linha);
    });
    if ((data.idiomas || []).length > 0) $("#collapseIdiomas").collapse("show");

    // --- CONQUISTAS E CERTIFICADOS ---
    (data.conquistas || []).forEach(function (c) {
      const linha = `
        <tr id="conq_${c.id}">
            <td class="text-center">${c.ano}</td>
            <td>${c.dsTipo}</td>
            <td>${c.titulo}</td>
            <td class="text-center align-middle" style="white-space: nowrap;">
                <button type="button" class="btn btn-outline-primary btn-sm botaozinho" onclick="con_ver(${c.id})">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm botaozinho" onclick="con_excluir(${c.id})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>`;
      $("#tbConquistas").append(linha);
    });
    if ((data.conquistas || []).length > 0) $("#collapseConquista").collapse("show");

    // --- HABILIDADES ---
    (data.habilidades || []).forEach(function (h) {
      addSkillToContainer(h);
    });
  }, "json");
}

//-- Salva Bloco 1 quando pronto
function testa_bloco1() {
  let cpf = $("#bloco1 #cpf").val().replace(/\D/g, '');
  let nome = $("#bloco1 #nome").val();
  let telefone = $("#bloco1 #telefone").val();
  let email = $("#bloco1 #email").val();
  let sexo = $("#bloco1 #sexo").val();
  let nacionalidade = $("#bloco1 #nacionalidade").val();
  let escolaridade = $("#bloco1 #idGrauInstrucao").val();
  let botoes = $("#bloco1 #btnDados");
  //
  if (cpf != '' && nome != '' && telefone != '' && email != '' && sexo != '' && nacionalidade != '' && escolaridade != '') {
    botoes.show();
  }
}
function reset_bloco1() {
  let cpf = $("#bloco1 #cpf");
  let nome = $("#bloco1 #nome");
  let nomeSocial = $("#bloco1 #nomeSocial");
  let telefone = $("#bloco1 #telefone");
  let email = $("#bloco1 #email");
  let sexo = $("#bloco1 #sexo");
  let nacionalidade = $("#bloco1 #nacionalidade");
  let escolaridade = $("#bloco1 #idGrauInstrucao");
  let botoes = $("#bloco1 #btnDados");
  //
  botoes.hide();
  cpf.val('');
  nome.val('');
  nomeSocial.val('');
  telefone.val('');
  email.val('');
  sexo.val('');
  nacionalidade.val('');
  escolaridade.val(0);
}

function salvar_bloco1() {
  let botoes = $("#bloco1 #btnDados");
  let formulario = $("#form_dados");
  let body_dados = $("#collapseDados");
  let bloco_upload = $("#blocoUpload");
  let msg = $("#msgDados");
  //
  botoes.hide();
  msg.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
  //
  $.ajax("new_aj2.php", {
    method: "POST",
    data: formulario.serialize()
  }).done(function (data) {
    //
    if (data.status == true) {
      msg.html("<div class='alert alert-success w-100'>Dados salvos com sucesso!</div>");
    }
    else {
      msg.html("<div class='alert alert-danger w-100'>" + data.msg + "</div>");
    }
    //
    setTimeout(() => {
      msg.html(""); // Limpa a mensagem depois de 3 segundos
      if (data.status == false) botoes.show();
      else {
        // Use a API nativa do Bootstrap para garantir controle consistente do collapse
        const uploadEl = document.getElementById('blocoUpload');
        const dadosEl = document.getElementById('collapseDados');
        if (uploadEl) bootstrap.Collapse.getOrCreateInstance(uploadEl, { toggle: false }).show();
        if (dadosEl) bootstrap.Collapse.getOrCreateInstance(dadosEl, { toggle: false }).hide();
      }
    }, 3000);
    //
  }).fail(function (jqXHR, textStatus) {
    alert("Request failed: " + textStatus);
    msg.html("");
    botoes.show();
  });
}

/*
function toggleDeficiencia(o) {
  var bloco = document.getElementById("deficiencia-bloco");

  if (o.checked) {
    bloco.classList.add("show"); // Mostra o bloco
    o.setAttribute("value", "1"); // Define o valor para 1
  } else {
    bloco.classList.remove("show"); // Oculta o bloco
    o.setAttribute("value", "0"); // Define o valor para 0
  }

  //showBtnCV();
}
*/

function toggleDefValue(o) {
  if (o.value == 0) {
    o.value = 1;
  } else {
    o.value = 0
  }
}

function validarDeficiencia() {
  let deficienteMarcado = $("#deficiente").prop("checked");

  if (deficienteMarcado) {
    // Verifica se pelo menos um dos checkboxes de deficiência está marcado
    let algumSelecionado = $("input[name='fisica']:checked, input[name='visual']:checked, input[name='auditiva']:checked, input[name='mental']:checked, input[name='intelectual']:checked, input[name='autista']:checked").length > 0;

    // Verifica se o campo CID está preenchido
    let cidPreenchido = $("#cid").val().trim() !== "";

    if (!algumSelecionado) {
      alert("Selecione pelo menos um tipo de deficiência.");
      return false;
    }

    if (!cidPreenchido) {
      alert("Preencha o campo CID.");
      return false;
    }
  }

  return true; // Permite continuar se tudo estiver correto
}

function envia_arquivoCV() {
  //
  let arquivo = $("#arquivo")[0].files[0]; // Obtém o arquivo selecionado
  if (!arquivo) {
    $("#mensagemUpload").html("<div class='alert alert-warning'>Por favor, selecione um arquivo.</div>");
    return;
  }

  let formData = new FormData();
  formData.append("arquivo", arquivo); // Adiciona o arquivo ao FormData
  formData.append("idPessoa", $("#idPessoa").val()); // ID da pessoa no formulário

  $.ajax({
    url: "new_aj3.php",
    type: "POST",
    data: formData,
    processData: false,  // Necessário para envio de arquivos
    contentType: false,  // Necessário para envio de arquivos
    beforeSend: function () {
      $("#mensagemUpload").html("<div class='alert alert-info'>Enviando arquivo...</div>");
    },
    success: function (dados) {
      let msg = "";
      try {
        if (dados.status) {
          msg = "<div class='alert alert-success'>Arquivo enviado com sucesso!</div>";
        } else {
          msg = "<div class='alert alert-danger'>Erro ao enviar o arquivo: " + dados.msg + "</div>";
        }
      } catch (e) {
        console.error("Erro ao processar JSON:", e, response); // Exibe detalhes do erro no console
        msg = "<div class='alert alert-danger'>Erro inesperado no envio: " + e.message + "</div>";
      }
      $("#mensagemUpload").html(msg);
      setTimeout(() => {
        $("#mensagemUpload").html(""); // Limpa a mensagem após 3 segundos
      }, 3000);
    },
    error: function () {
      $("#mensagemUpload").html("<div class='alert alert-danger'>Erro na requisição AJAX.</div>");
    }
  });
}

function exp_incluir() {
  exp_Modal_Inc.show();
}

function exp_atual(o) {
  if (o.checked) {
    o.value = "1";
  } else {
    o.value = "0";
  }
}

function exp_incluir_salva() {
  let mensagem = $("#msgformIncExp");
  let idPessoa = $("#idPessoa").val();
  let empresa = $("#formIncExp #empresa");
  let cargo = $("#formIncExp #cargo");
  let ano_ini = $("#formIncExp #ano_ini");
  let ano_fim = $("#formIncExp #ano_fim");
  let descricao = $("#formIncExp #descricao");
  let atual = $("#formIncExp #atual").is(':checked') ? 'Sim' : 'Não'; // Supondo que você tenha esse checkbox

  // Validações (mantidas conforme seu código)
  if (empresa.val() == '' || cargo.val() == '' || ano_ini.val() == 0) {
    alert("Preencha os campos obrigatórios.");
    return false;
  }

  let formData = new FormData(document.getElementById("formIncExp"));
  formData.append("idPessoa", idPessoa);

  $("#divBotoesExp").hide();
  mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");

  $.ajax({
    url: "new_aj4_exp.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      let dados = JSON.parse(response);
      mensagem.html(dados.msg);

      if (dados.status === 'success' || dados.status === true) {
        // Pegamos o ID retornado pelo PHP
        let novoId = dados.id;

        // --- INSERÇÃO DINÂMICA NA TABELA ---
        let novaLinha = `
        <tr id="exp_${novoId}"> 
            <td class="text-center">${ano_ini.val()}</td>
            <td class="text-center">${atual === 'Sim' ? '---' : ano_fim.val()}</td>
            <td class="text-center">${atual}</td>
            <td>${empresa.val()}</td>
            <td>${cargo.val()}</td>
            <td class="text-center align-middle" style="white-space: nowrap;">
                
                <button type="button" 
                        class="btn btn-outline-primary btn-sm botaozinho"
                        onclick="exp_ver(${novoId})">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <button type="button" 
                        class="btn btn-outline-danger btn-sm botaozinho"
                        onclick="exp_excluir(${novoId})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>

            </td>
        </tr>`;

        // Adiciona no topo da tabela
        $("#tbExp").prepend(novaLinha);
        // -----------------------------------
      }

      setTimeout(() => {
        $("#divBotoesExp").show();
        mensagem.html("");
        exp_Modal_Inc.hide();

        // Em vez de reload total, apenas limpa o form
        if (typeof btnResetExp !== 'undefined') btnResetExp.click();

        // Abre o collapseExp
        $("#collapseExp").collapse('show'); // Abre o collapseExp 
      }, 2000);
    },
    error: function (xhr) {
      mensagem.html("Erro: " + xhr.responseText);
      $("#divBotoesExp").show();
    }
  });
}

function exp_excluir(id) {

  if (!id) {
    alert("Sem Parâmetros para excluir o registro.");
    return;
  }

  if (confirm("Tem certeza que deseja excluir esta experiência profissional?")) {
    $("#msgAlertaPessoa").show();

    $.post("new_aj5_exp.php", { id: id }, function (response) {
      const dados = JSON.parse(response);
      $("#msgAlertaPessoa").html(dados.msg);

      // Se o status for sucesso, removemos a linha da tabela imediatamente
      if (dados.status === 'success' || dados.status === true) {
        // Efeito de fade para uma transição suave antes de remover
        $(`#exp_${id}`).fadeOut(500, function () {
          $(this).remove();
        });
      }

      setTimeout(function () {
        $("#msgAlertaPessoa").html("").hide();
        // document.location.reload(true); // Removido para evitar o refresh
      }, 3000);

    }).fail(function () {
      alert("Erro ao excluir o registro.");
    });
  }
}

function exp_ver(id) {
  exp_Modal_Ver.show();
  $.post("new_aj6_exp.php", { id: id }, function (retorno) {
    //
    //alert( retorno );
    let dados = JSON.parse(retorno);
    $("#v_empresa").html(dados.empresa)
    $("#v_cargo").html(dados.cargo)
    $("#v_ano_ini").html(dados.ano_ini)
    $("#v_ano_fim").html(dados.ano_fim)
    $("#v_descricao").html(dados.descricao)
    $("#v_atual").html(dados.ativo)
    $("#divQuandoExp").html(dados.dtLogin)
  });
}

function fa_incluir() {
  fa_Modal_Inc?.show();
}

function fa_incluir_salva() {
  let idPessoa = $("#idPessoa").val();
  let mensagem = $("#msgformIncFA");
  //
  let curso = $("#formIncFA #curso");
  let idInstituicao = $("#formIncFA #idInstituicao");
  let idNivel = $("#formIncFA #idNivel");
  let ano = $("#formIncFA #ano");
  //
  if (curso.val() == '') {
    alert("Informe o nome do curso");
    curso.focus();
    return false;
  }
  //
  if (idInstituicao.val() == 0) {
    alert("Informe a IE do curso");
    idInstituicao.focus();
    return false;
  }
  //
  if (idNivel.val() == 0) {
    alert("Informe o Nível do curso");
    idNivel.focus();
    return false;
  }
  //
  if (ano.val() == 0) {
    alert("Informe o ano de conclusão curso");
    ano.focus();
    return false;
  }
  //
  let formData = new FormData(document.getElementById("formIncFA"));
  formData.append("idPessoa", idPessoa);
  //
  $("#divBotoesFA").hide();
  mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
  //
  $.ajax({
    url: "new_aj8_fa.php",
    type: "POST",
    data: formData,
    processData: false,  // Evita que o jQuery tente converter o FormData em string
    contentType: false,  // Permite que arquivos sejam enviados corretamente
    success: function (response) {
      let dados = JSON.parse(response);
      mensagem.html(dados.msg);

      if (dados.status === 'success' || dados.status === true) {
        // Pegamos o ID retornado pelo PHP
        let novoId = dados.d.id;

        // --- INSERÇÃO DINÂMICA NA TABELA ---
        let novaLinha = `
        <tr id="FA_${novoId}"> 
            <td class="text-center">${dados.d.nivel}</td>
            <td class='text-center'>${dados.d.curso}</td>
            <td class='text-center'>${dados.d.sigla}</td>
            <td class='text-center'>${dados.d.ano_conclusao}</td>
            <td class="text-center align-middle" style="white-space: nowrap;">
                
                <button type="button" 
                        class="btn btn-outline-primary btn-sm botaozinho"
                        onclick="fa_ver(${novoId})">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <button type="button" 
                        class="btn btn-outline-danger btn-sm botaozinho"
                        onclick="fa_excluir(${novoId})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>

            </td>
        </tr>`;

        // Adiciona no topo da tabela
        $("#tbFormacao").prepend(novaLinha);
        // -----------------------------------
      }

      setTimeout(() => {
        $("#divBotoesExp").show();
        mensagem.html("");
        fa_Modal_Inc.hide();

        // Em vez de reload total, apenas limpa o form
        if (typeof btnResetFA !== 'undefined') btnResetFA.click();

        // Abre o collapseExp
        $("#collapseFA").collapse('show'); // Abre o collapseExp 
      }, 2000);

    },
    error: function (xhr) {
      mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
    },
    complete: function () {
      //
    }
  });
}

function fa_excluir(id) {
  if (confirm("Tem certeza que deseja excluir este registro de formação acadêmica?")) {
    $("#msgAlertaPessoa").show();

    // Caminho do seu arquivo PHP de exclusão
    $.post("new_aj9_fa.php", { id: id }, function (response) {
      const dados = JSON.parse(response);
      $("#msgAlertaPessoa").html(dados.msg);

      // Se a exclusão no banco foi bem sucedida
      if (dados.status === 'success' || dados.status === true) {
        // Remove a linha da tabela com um efeito suave
        $(`#FA_${id}`).fadeOut(500, function () {
          $(this).remove();
        });
      }

      setTimeout(function () {
        $("#msgAlertaPessoa").html("").hide();
        // Removido o reload para manter a fluidez da página
      }, 3000);

    }).fail(function () {
      alert("Erro ao excluir o registro.");
      $("#msgAlertaPessoa").hide();
    });
  }
}

function fa_ver(id) {
  fa_Modal_Ver.show();
  $.post("new_aj12_fa.php", { id: id }, function (retorno) {
    //
    let dados = JSON.parse(retorno);
    //
    let arquivo = "não informado";
    if (dados.arquivo.length > 0) {
      let url = dados.url.replace(/'/g, "\\'"); // evita quebra no JS
      let link = ` <a href="#" onclick="mostrar_certificado('${url}')"><i class="fa-solid fa-magnifying-glass"></i></a>`;
      arquivo = dados.arquivo + link;
    }
    //
    $("#v_curso").html(dados.curso)
    $("#v_instituicao").html(dados.nmInstituicao)
    $("#v_sigla").html(dados.sigla)
    $("#v_nivel").html(dados.nivel)
    $("#v_ano_conclusao").html(dados.ano_conclusao)
    $("#divQuando").html(dados.criado_em)
    $("#fa_arquivo").html(arquivo);
  });
}

//----------------------------------------------------
// INSTITUIÇÃO DE ENSINO
//
function f_inclui_ie() {
  // Exibe a modal
  fa_Modal_IncIE.show();

  // Configura o foco após 500ms (quando a modal estiver totalmente visível)
  $('#fa_Modal_IncIE').on('shown.bs.modal', function () {
    setTimeout(function () {
      $("#_nomeInstituicao").focus();
    }, 3000);
  });
}

function f_inclui_ie_salva() {
  //
  let mensagem = $("#msgformIncFAIE");
  //
  let nome = $("#formIncFAIE #_nomeInstituicao");
  let sigla = $("#formIncFAIE #_sigla");
  let idNivel = $("#formIncFAIE #idNivel");
  let cidade = $("#formIncFAIE #_cidade");
  let uf = $("#formIncFAIE #uf");
  let pais = $("#formIncFAIE #_pais");
  //
  if (nome.val() == '') {
    alert("Informe o nome da Instituição");
    nome.focus();
    return false;
  }
  //
  if (sigla.val() == '') {
    alert("Informe a SIGLA da Instituição");
    sigla.focus();
    return false;
  }
  //
  if (idNivel.val() == 0) {
    alert("Informe o nível de ensino");
    idNivel.focus();
    return false;
  }
  //
  if (cidade.val() == '') {
    alert("Informe a Cidade onde está a Instituição");
    cidade.focus();
    return false;
  }
  //
  if (uf.val() == 0) {
    alert("Informe o Estado onde está a Instituição");
    uf.focus();
    return false;
  }
  //
  if (pais.val() == '') {
    alert("Informe o País onde está a Instituição");
    pais.focus();
    return false;
  }
  //
  let formData = new FormData(document.getElementById("formIncFAIE"));
  $("#botoes_fa_id").hide();
  mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");
  //
  $.ajax({
    url: "new_aj7_faie.php",
    type: "POST",
    data: formData,
    processData: false,  // Evita que o jQuery tente converter o FormData em string
    contentType: false,  // Permite que arquivos sejam enviados corretamente
    success: function (response) {
      let dados = JSON.parse(response);
      mensagem.html(dados.msg);
      setTimeout(() => {
        $("#botoes_fa_id").show();
        mensagem.html("");
        fa_Modal_IncIE.hide();
        //
        let newOption = `<option value="${dados.dados.idInstituicao}" selected>${dados.dados.nome} (${dados.dados.cidade}/${dados.dados.uf}-${dados.dados.pais})</option>`;
        // Adicionar a nova opção ao select
        $("#idInstituicao").append(newOption);

        // Alternativamente, você pode forçar o "Selecione uma IE" a ser desmarcado se estiver presente
        $("#idInstituicao").val(dados.dados.idInstituicao);
        //
      }, 3000); // Ajuste o tempo conforme necessário
    },
    error: function (xhr) {
      mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
    },
    complete: function () {
      //
    }
  });
}

$("#formIncFAIE #_cidade").autocomplete({
  appendTo: "#faModalIncIE",
  source: function (request, response) {
    $.ajax({
      url: "../app/includes/buscar_cidades.php",
      type: "GET",
      dataType: "json",
      data: { term: request.term },
      success: function (data) {
        response($.map(data, function (item) {
          return {
            label: item.nome, // O que aparece para o usuário
            value: item.nome, // O que preenche o input
            id: item.id       // ID da cidade
          };
        }));
      }
    });
  },
  minLength: 2, // Busca após 2 caracteres
  select: function (event, ui) {
    $("#formIncFAIE #cidade_id").val(ui.item.id); // Salva o ID no campo oculto
  }
});

$("#formDiversidade #cidade").autocomplete({
  source: function (request, response) {
    $.ajax({
      url: "../app/includes/buscar_cidades.php",
      type: "GET",
      dataType: "json",
      data: { term: request.term },
      success: function (data) {
        response($.map(data, function (item) {
          return {
            label: item.nome, // O que aparece para o usuário
            value: item.nome, // O que preenche o input
            id: item.id       // ID da cidade
          };
        }));
      }
    });
  },
  minLength: 2, // Busca após 2 caracteres
  select: function (event, ui) {
    $("#formDiversidade #cidade_id").val(ui.item.id); // Salva o ID no campo oculto
  }
});

//----------------------------------------------------
// IDIOMAS
//

function idioma_incluir() {
  idi_Modal_Inc.show();
}

function idioma_incluir_salva() {
  let mensagem = $("#msgformIncIdi");
  let idIdioma = $("#formIncIdi #idIdioma");
  let idFluencia = $("#formIncIdi #idFluencia");
  let idPessoa = $("#idPessoa").val();

  // Validações
  if (idIdioma.val() == 0) {
    alert("Informe a Lingua");
    idIdioma.focus();
    return false;
  }
  if (idFluencia.val() == 0) {
    alert("Informe o grau de fluência nessa lingua");
    idFluencia.focus();
    return false;
  }

  let formData = new FormData(document.getElementById("formIncIdi"));
  formData.append("idPessoa", idPessoa);

  $("#divBotoesIdi").hide(); // Ajustado para o ID correto dos botões de idioma
  mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");

  $.ajax({
    url: "new_aj10_idi.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      let dados = JSON.parse(response);
      mensagem.html(dados.msg);

      if (dados.status === 'success' || dados.status === true) {

        let novoId = dados.d.id; // ID que vem do PHP

        // --- INSERÇÃO DINÂMICA NA GRID ---
        // Nota: dados.d deve conter os textos (Nome do Idioma e Fluência) vindos do PHP
        let novaLinha = `
                    <tr id="idioma_${novoId}"> 
                        <td>${dados.d.idioma}</td>
                        <td>${dados.d.fluencia}</td>
                        <td>${dados.d.criado_em}</td>
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm botaozinho"
                                    onclick="idioma_excluir(${novoId})">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>`;

        $("#tbIdiomas").prepend(novaLinha);
        // -----------------------------------
      }

      setTimeout(() => {
        $("#divBotoesIdi").show();
        mensagem.html("");
        if (typeof idi_Modal_Inc !== 'undefined') idi_Modal_Inc.hide();
        if (typeof btnResetIdi !== 'undefined') btnResetIdi.click();

        // Garante que o bloco de idiomas esteja aberto para ver a nova linha
        $("#collapseIdiomas").collapse('show');
      }, 2000);
    },
    error: function (xhr) {
      mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
      $("#divBotoesIdi").show();
    }
  });
}

function idioma_excluir(id) {
  if (confirm("Deseja realmente remover este idioma do seu currículo?")) {
    // Usando o mesmo container de alerta das outras funções
    $("#msgAlertaPessoa").show();

    $.post("new_aj11_idi.php", { id: id }, function (response) {
      const dados = JSON.parse(response);
      $("#msgAlertaPessoa").html(dados.msg);

      if (dados.status === 'success' || dados.status === true) {
        $(`#idioma_${id}`).fadeOut(500, function () {
          $(this).remove();
        });
      }

      setTimeout(function () {
        $("#msgAlertaPessoa").html("").hide();
      }, 3000);

    }).fail(function () {
      alert("Erro ao excluir o idioma.");
    });
  }
}

//----------------------------------------------------
// CONQUISTAS & CERTIFICADOS
//

function con_incluir() {
  con_Modal_Inc.show();
}

function btn_reset_con() {
  $("#formIncCon #descricao").summernote('code', '');
}

function con_incluir_salva() {
  let mensagem = $("#msgformIncCon");
  let idPessoa = $("#idPessoa").val();

  let idConqTipo = $("#formIncCon #idConqTipo");
  let titulo = $("#formIncCon #titulo");
  let ano = $("#formIncCon #ano");
  let descricao = $("#formIncCon #descricao");

  // Validações
  if (idConqTipo.val() == 0) { alert("Informe o tipo de conquista/certificado"); idConqTipo.focus(); return false; }
  if (titulo.val() == '') { alert("Informe o título do evento"); titulo.focus(); return false; }
  if (ano.val() == 0) { alert("Informe o ano do evento"); ano.focus(); return false; }
  if (descricao.val() == '') { alert("Descreva o evento"); descricao.focus(); return false; }

  let formData = new FormData(document.getElementById("formIncCon"));
  formData.append("idPessoa", idPessoa);

  $("#divBotoesCon").hide();
  mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");

  $.ajax({
    url: "new_aj13_conq.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      let dados = JSON.parse(response);
      mensagem.html(dados.msg);

      if (dados.status === 'success' || dados.status === true) {
        let novoId = dados.d.id;

        // --- INSERÇÃO DINÂMICA NA GRID ---
        let novaLinha = `
                    <tr id="conq_${novoId}"> 
                        <td class="text-center">${dados.d.ano}</td>
                        <td>${dados.d.dsTipo}</td>
                        <td>${dados.d.titulo}</td>
                        <td class="text-center align-middle" style="white-space: nowrap;">
                            <button type="button" class="btn btn-outline-primary btn-sm botaozinho" 
                                    onclick="con_ver(${novoId})">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm botaozinho" 
                                    onclick="con_excluir(${novoId})">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>`;

        $("#tbConquistas").prepend(novaLinha);
      }

      setTimeout(() => {
        $("#divBotoesCon").show();
        mensagem.html("");

        // Fechar modal e resetar form (ajuste os nomes das suas variáveis se necessário)
        if (typeof con_Modal_Inc !== 'undefined') con_Modal_Inc.hide();
        if (typeof btnResetCon !== 'undefined') btnResetCon.click();

        $("#collapseConquista").collapse('show');
      }, 2000);
    },
    error: function (xhr) {
      mensagem.html("Erro: " + xhr.responseText);
      $("#divBotoesCon").show();
    }
  });
}

function con_excluir(id) {
  if (confirm("Tem certeza que deseja excluir esta conquista/certificado?")) {
    // Usando o alerta interno do bloco para feedback focado
    $("#msgAlertaConquista").show();

    $.post("new_aj14_conq.php", { id: id }, function (response) {
      const dados = JSON.parse(response);
      $("#msgAlertaConquista").html(dados.msg);

      if (dados.status === 'success' || dados.status === true) {
        // Remove a linha específica da grid
        $(`#conq_${id}`).fadeOut(500, function () {
          $(this).remove();
        });
      }

      setTimeout(function () {
        $("#msgAlertaConquista").html("").hide();
      }, 3000);

    }).fail(function () {
      alert("Erro ao excluir o registro.");
    });
  }
}

function con_ver(id) {
  // Exibe a modal antes da requisição para dar feedback visual
  con_Modal_Ver.show();

  $.post("new_aj15_conq.php", { id: id, origem: 'visualizar' }, function (retorno) {
    let res = JSON.parse(retorno);

    if (res.status) {
      let dados = res.d; // Agora pegamos o 'd' do objeto
      let arquivoHtml = "Não informado";

      if (dados.arquivo && dados.arquivo.length > 0) {
        // Link direto para abrir em nova aba
        arquivoHtml = `${dados.arquivo} 
                    <a href="${dados.url}" target="_blank" class="ms-2 btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-up-right-from-square"></i> Abrir
                    </a>`;
      }

      // Preenche os campos
      $("#v_tipo").html(dados.dsTipo);
      $("#conModalVer #v_ano").html(dados.ano);
      $("#v_titulo").html(dados.titulo);
      $("#conModalVer #v_descricao").html(dados.descricao);
      $("#v_arquivo").html(arquivoHtml);

      // Rodapé com data de criação
      $("#divQuandoExp").html("Cadastrado em: " + dados.criado_em);
    } else {
      alert(res.msg);
      con_Modal_Ver.hide();
    }
  });
}

function mostrar_certificado(url) {
  window.open(url, "_blank");
}

//---------------------------------------------------
// BLOCO: HABILIDADES (SKILLS)
//

let skills = [];

function addSkill() {
  let skillInput = $("#inputSkill");
  let skill = skillInput.val().trim();

  if (skill === "") return; // Evita adicionar valores vazios

  // Verifica se a habilidade já existe
  let exists = false;
  $("#skillsContainer span").each(function () {
    if ($(this).text().trim() === skill) {
      exists = true;
    }
  });

  if (!exists) {
    addSkillToContainer(skill);
    $("#btnHabilidades").show();
  }

  skillInput.val(""); // Limpa o campo após adicionar
}

function removeSkill(skill) {
  $("#skillsContainer span").each(function () {
    let textoSkill = $(this).text().trim();
    if (textoSkill.startsWith(skill)) { // Verifica se o texto começa com a skill
      $(this).remove(); // Remove o elemento corretamente
    }
  });
  $("#btnHabilidades").show();
}

function updateSkills() {
  let container = document.getElementById("skillsContainer");
  container.innerHTML = "";
  $("#btnHabilidades").show();
  skills.forEach(skill => {
    let span = document.createElement("span");
    span.className = "badge bg-secondary p-2";
    span.innerHTML = `${skill} <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeSkill("${skill}")'></i>`;
    container.appendChild(span);
  });
}

function saveSkills() {
  let mensagem = $('#msgHabilidades');
  //
  let idPessoa = $("#idPessoa").val(); // ID do currículo
  let skills = [];

  // Verificar se o container de skills existe
  if ($("#skillsContainer").length === 0) {
    alert("Erro: O container de skills NÃO foi encontrado! Verifique o ID no HTML.");
    return;
  }

  // Coletar habilidades dentro do container
  $("#skillsContainer span").each(function () {
    let skillText = $(this).text().trim().replace(" x", "");
    if (skillText !== "") {
      skills.push(skillText);
    }
  });

  if (skills.length === 0) {
    mensagem.html("Nenhuma habilidade foi encontrada!");
    return;
  }

  // Enviar via AJAX
  $.post("new_aj16_hab.php", { idPessoa: idPessoa, habilidades: skills }, function (response) {
    mensagem.html(response.msg);
    setTimeout(() => {
      mensagem.html("");
      $("#btnHabilidades").hide();
      //
    }, 3000); // Ajuste o tempo conforme necessário
  });
}

/*
function loadSkills() {
    let mensagem = $('#msgHabilidades');
    let idPessoa = $("#formCV #idPessoa").val();
    if (!idPessoa) return;

    $.ajax({
        url: "includes/rh_cv_aj4.php",
        type: "POST",
        data: { idPessoa: idPessoa },
        dataType: "json",
        success: function (response) {
            if (response.status) {
                $("#skillsContainer").empty(); // Limpa o container antes de adicionar
                response.habilidades.forEach(function (skill) {
                    addSkillToContainer(skill);
                });
            } else {
                mensagem.html("Nenhuma habilidade encontrada.");
            }
        },
        error: function (xhr) {
            mensagem.html("Erro ao carregar habilidades:", xhr.responseText);
        }
    });
}
*/

// Adiciona a skill ao container com botão de remoção
function addSkillToContainer(skill) {
  let span = document.createElement("span");
  span.className = "badge bg-primary p-2";
  span.innerHTML = `${skill} <i class='fa-solid fa-xmark ms-2' style='cursor:pointer' onclick='removeSkill("${skill}")'></i>`;
  document.querySelector("#skillsContainer").appendChild(span);
  //
  //$("#btnHabilidades").show();
}

function troggle_on_btn_diversidade() {
  $("#btnDiversidade").show()
}

/*
function btnResetDiversidade() {
  let idPessoa = $("#idPessoa").val();
  $.post("new_aj17_div.php", { idPessoa: idPessoa }, function (retorno) {
    //alert( retorno )
    let d = JSON.parse(retorno);
    //
    if (d.dados.idCidade > 0) {
      $("#cidade_id").val(d.dados.idCidade);
      $("#cidade").val(d.dados.nmCidade + "/" + d.dados.uf);
    }
    //        
    $("#cor").val(d.dados.cor);
    $("#pronome").val(d.dados.pronome);
    $("#orientacao").val(d.dados.orientacao);
    $("#identgenero").val(d.dados.idGenero);
    //
    $("#btnDiversidade").hide();
  });
}
*/

//
//- FINAL
//

function salvar_curriculo() {
  let mensagem = $("#msgNovoCV");
  let btn = $("#btnSalvarCV");

  // Validações
  if (validarGenero() == false || validarDeficiencia() == false) {
    return false;
  }

  // Evita duplo clique / duplo envio
  if (btn.prop("disabled")) {
    return false;
  }
  let textoOriginalBtn = btn.html();
  btn.prop("disabled", true).html("<i class='fa-solid fa-spinner fa-spin'></i> Enviando...");

  function reabilitarBotao() {
    btn.prop("disabled", false).html(textoOriginalBtn);
  }

  // 1. Inicia o FormData com o formulário principal (Dados: form_data)
  let form_dados = document.getElementById("form_dados");
  let formData = new FormData(form_dados);

  // 2. Captura os dados do formDiversidade e anexa ao formData existente
  let formDiversidade = document.getElementById("formDiversidade");
  if (formDiversidade) {
    let extraData = new FormData(formDiversidade);
    for (let [key, value] of extraData.entries()) {
      // append adiciona os dados. Se houver chaves repetidas, o PHP recebe como array ou sobrescreve
      formData.append(key, value);
    }
  }

  mensagem.html("<h6 class='text-center'><i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...</h6>");

  $.ajax({
    url: "new_aj0_save.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      try {
        let dados = JSON.parse(response);
        mensagem.html(dados.msg);

        if (dados.status == 'success' || dados.status == true) {
          setTimeout(() => {
            // Se veio de "Candidatar-se" numa vaga, a sessão do candidato já foi
            // aberta (new_aj1.php) - registra a candidatura e volta pra vaga.
            if (typeof VAGA_ID_PENDENTE !== "undefined" && VAGA_ID_PENDENTE) {
              $.post("candidatura_aj.php", { vaga_id: VAGA_ID_PENDENTE }, function () {
                document.location.href = "../vagas/vaga_perfil.php?id=" + VAGA_ID_PENDENTE + "&auto=1";
              }, "json").fail(function () {
                document.location.href = "../vagas/vaga_perfil.php?id=" + VAGA_ID_PENDENTE + "&auto=1";
              });
            } else {
              alert("Curriculum enviado com sucesso! Obrigado");
              document.location.href = "../index.php";
            }
          }, 2000);
        } else {
          reabilitarBotao();
        }
      } catch (e) {
        mensagem.html("Erro na resposta do servidor.");
        reabilitarBotao();
      }
    },
    error: function (xhr) {
      reabilitarBotao();
      mensagem.html("Erro ao enviar o formulário: " + xhr.responseText);
    }
  });
}

function validarGenero() {
  // Verifica se algum radio button com name="genero" está marcado
  let selecionado = $("input[name='genero']:checked").length > 0;

  if (!selecionado) {
    alert("Por favor, selecione uma opção de gênero.");
    return false; // Impede a continuação da função de salvamento
  }
  return true; // Permite o salvamento
}

function validarDeficiencia() {
  let deficienteMarcado = $("#deficiente").prop("checked");

  if (deficienteMarcado) {
    // Verifica se pelo menos um dos checkboxes de deficiência está marcado
    let algumSelecionado = $("input[name='fisica']:checked, input[name='visual']:checked, input[name='auditiva']:checked, input[name='mental']:checked, input[name='intelectual']:checked, input[name='autista']:checked").length > 0;

    // Verifica se o campo CID está preenchido
    let cidPreenchido = $("#cid").val().trim() !== "";

    if (!algumSelecionado) {
      alert("Selecione pelo menos um tipo de deficiência.");
      return false;
    }

    if (!cidPreenchido) {
      alert("Preencha o campo CID.");
      return false;
    }
  }

  return true; // Permite continuar se tudo estiver correto
}


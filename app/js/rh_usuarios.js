//
// rh_usuarios.js
//

$(document).ready(function () {

    new DataTable('#example', {
        "processing": true,
        "serverSide": false,
        "order": [
            [2, "desc"]
        ],
        "ajax": {
            "url": "rh_usuarios_aj.php",
            "type": "POST"
        },
        "columnDefs": [
            { orderable: false, targets: [9, 13] },
            {
                "targets": 6, // Índice da coluna a ser ocultada
                "visible": false // Define a coluna como invisível
            },
            {
                "targets": [0, 1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 13], // Índices das colunas a serem centralizadas (3 e 4 neste caso)
                "className": "text-center" // Classe CSS para centralizar o conteúdo
            }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // $('.js-select2').select2();

});

const visModal = new bootstrap.Modal(document.getElementById("modalVisualisar"));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));
const incModalPessoa = new bootstrap.Modal(document.getElementById("modalIncluirPessoa"));
const incModalGrupo = new bootstrap.Modal(document.getElementById("modalIncluirGrupo"));
const altModal = new bootstrap.Modal(document.getElementById("modalAlterar"));

function f_incluir() {
    incModal.show();
    //- Preenche o Combo Pessoas
    $.post("includes/rh_usuario_inc_aj2.php", { "idPessoa": 0 },
        function (codigo, status) {
            $("#idSeletorPessoas").html(codigo);
        });
    //- Preenche o Combo Colaboradores
    $.post("includes/rh_usuario_inc_aj4.php", { "idColab": 0 },
        function (codigo, status) {
            $("#idSeletorColaborador").html(codigo);
        });
    //- Preenche o Combo Subsedes
    $.post("includes/rh_selectSubsede.php", { "idSubSede": 0 },
        function (codigo, status) {
            $("#idSeletorSubsedes").html(codigo);
        });
}

function f_incluiPessoa() {
    incModalPessoa.show();
    //- Limpa o Formulário
    $("#btnResetIncPessoa").click();
}

function f_incluiGrupo() {
    incModalGrupo.show();
    //- Limpa o Formulário
    $("#btnResetIncGrupo").click();
}

function btnIncSalvarUsuario() {
    //
    var idUsuarioGrupo = $("#inputIdUsuarioGrupo").val();
    var idEmpresa = $("#inputIdEmpresa").val();
    var idPessoa = $("#inputIDPessoa").val();
    var idColab = $("#inputIDColab").val();
    var login = $("#inputLogin").val();
    var senha = $("#inputSenha").val();
    var foto = $("#inputFoto")[0].files[0]; // Obtenha o arquivo de imagem selecionado
    var chave = $("#inputChave").val();
    var idSubSede = $("#form-cad-usuario #idSubSede").val();
    //
    var chkCipa = $("#form-cad-usuario #chkCipa").val();
    var chkBrigada = $("#form-cad-usuario #chkBrigada").val();
    //
    if (idUsuarioGrupo == 0) {
        alert("Por favor, Selecione um grupo...");
        $("#inputIdUsuarioGrupo").focus();
        return false;
    }
    if (idEmpresa == 0) {
        alert("Por favor, Selecione uma Organização...");
        $("#inputIdEmpresa").focus();
        return false;
    }
    if (!idPessoa || isNaN(idPessoa) || Number(idPessoa) <= 0) {
        alert(idPessoa);
        alert("Por favor, Selecione uma Pessoa...");
        $("#inputIDPessoa").focus();
        return false;
    }
    if (idSubSede == 0) {
        alert("Por favor, Selecione uma Subsede...");
        $("#form-cad-usuario #idSubSede").focus();
        return false;
    }
    if (login == "") {
        alert("Por favor, preencha o campo de login.");
        $("#inputLogin").focus();
        return false;
    }
    if (senha == "") {
        alert("Por favor, preencha a SENHA de login.");
        $("#inputSenha").focus();
        return false;
    }

    var formData = new FormData();
    formData.append("idUsuarioGrupo", idUsuarioGrupo);
    formData.append("idPessoa", idPessoa);
    formData.append("idColab", idColab);
    formData.append("idEmpresa", idEmpresa);
    formData.append("login", login);
    formData.append("senha", senha);
    formData.append("foto", foto);
    formData.append("chaveApp", chave);
    formData.append("idSubSede", idSubSede);
    formData.append("chkCipa", chkCipa);
    formData.append("chkBrigada", chkBrigada);

    $.ajax({
        type: 'POST',
        url: 'includes/rh_usuarios_inc_aj.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (dados) {
            //
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertErroUsuInc").html(mensagem);
            //
            setTimeout(function () {
                $("#msgAlertErroUsuInc").html("");
                var dataTable = $('#example').DataTable();
                dataTable.ajax.reload();
                $("#btnIncReset").click();
                incModal.hide();
            }, 3000);
        },
        error: function () {
            // Tratar erros de requisição aqui
        }
    });
}

function btnIncSalvarPessoa() {
    //
    // Salvar dados da Modal Inclusão de Pessoas
    //
    const _cpf = $("#_cpf").val().replace(/\D/g, ''); // Remove tudo que não for número
    //
    if ($("#_nomePessoa").val() === "") {
        alert("Por favor, informe o nome da pessoa.");
        $("#_nomePessoa").focus();
        return false;
    }
    if ($("#_nomeSocial").val() === "") {
        alert("Por favor, informe o nome social da pessoa.");
        $("#_nomeSocial").focus();
        return false;
    }
    if ($("#_cpf").val() === "") {
        alert("Por favor, informe o CPF da pessoa.");
        $("#_cpf").focus();
        return false;
    }
    if (!validarCPF(_cpf)) {
        alert("CPF INVÁLIDO, tente outro!");
        $("#_cpf").focus();
        return false;
    }
    //- Envia o formulário para salvar
    //
    $("#msgAlertIncPessoa").show();
    $.post("includes/rh_usuario_inc_aj3.php",
        {
            nome: $("#_nomePessoa").val(),
            nomeSocial: $("#_nomeSocial").val(),
            cpf: $("#_cpf").val(),
            email: $("#_email").val(),
            telefone: $("#_celular").val()
        },
        function (dados, status) {
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertIncPessoa").html(mensagem);
            //
            setTimeout(function () {
                $("#msgAlertIncPessoa").html("");
                incModalPessoa.hide();
                $.post("includes/rh_usuario_inc_aj2.php", { "idPessoa": response.idPessoa },
                    function (codigo, status) {
                        $("#idSeletorPessoas").html(codigo);
                    });
            }, 3000);
        });
}

function f_editar(id) {
    altModal.show();
    $.post("includes/rh_usuario_alt1_aj.php", { idUsuario: id },
        function (dados, status) {
            var response = JSON.parse(dados);
            var idUsuarioGrupo = response.dados.idUsuarioGrupo;
            var idPessoa = response.dados.idPessoa;
            var idColab = response.dados.idColab;
            var idSubSede = response.dados.idSubSede;
            //
            if (response.status) {
                //- preenche os campos para edição
                //
                $("#idAltUsuario").val(response.dados.idUsuario);
                $("#inputAltLogin").val(response.dados.login);
                $("#inputAltChave").val(response.dados.chaveApp);

                //- preenche o campo grupo de usuários
                $.post("includes/rh_usuario_alt2_aj.php", { id: idUsuarioGrupo },
                    function (dados2, status2) {
                        $("#idSeletorAltGrupo").html(dados2);
                    }
                );
                //- preenche o campo pessoa 
                $.post("includes/rh_usuario_alt3_aj.php", { idPessoa: idPessoa },
                    function (dados3, status2) {
                        $("#idAltSeletorPessoas").html(dados3);
                    }
                );
                //- Preenche o Combo Colaboradores
                $.post("includes/rh_usuario_alt_aj4.php", { "idColab": idColab },
                    function (codigo, status) {
                        $("#idAltSeletorColaborador").html(codigo);
                    });
                //- Preenche o Combo Subsedes
                $.post("includes/rh_selectSubsede.php", { idSubSede: idSubSede },
                    function (codigo, status) {
                        $("#idAltSeletorSubsedes").html(codigo);
                    });

                if (response.dados.ativo) {
                    $('#inputAltAtivo').prop('checked', true);
                    $('#inputAltAtivo').val("SIM");
                } else {
                    $('#inputAltAtivo').prop('checked', false);
                    $('#inputAltAtivo').val("NÃO");
                }
                if (response.dados.dcCIPA) {
                    $('#inputCheckCipa').prop('checked', true);
                    $('#inputCheckCipa').val("1");
                } else {
                    $('#inputCheckCipa').prop('checked', false);
                    $('#inputCheckCipa').val("0");
                }
                if (response.dados.dcBrigada) {
                    $('#inputChkBrigada').prop('checked', true);
                    $('#inputChkBrigada').val("1");
                } else {
                    $('#inputChkBrigada').prop('checked', false);
                    $('#inputChkBrigada').val("0");
                }
                //
                testeInputAtivo();
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
                $("#msgAlertErroUsuAlt").html(mensagem);
                return false;
            }

        });
    //
}

function btnAltSalvarUsuario() {
    //
    var idUsuarioGrupo = $("#inputAltIdUsuarioGrupo").val();
    var idUsuario = $("#idAltUsuario").val();
    var idPessoa = $("#inputAltIDPessoa").val();
    var idColab = $("#inputAltIDColab").val();
    var idSubSede = $("#form-alt-usuario #idSubSede").val();
    var login = $("#inputAltLogin").val();
    var senha = $("#inputAltSenha").val();
    var ativo = $("#inputAltAtivo").val();
    var cipa = $("#inputCheckCipa").val();
    var brigada = $("#inputChkBrigada").val();
    var chave = $("#inputAltChave").val();
    var foto = $("#inputAltFoto")[0].files[0]; // Obtenha o arquivo de imagem selecionado
    //

    if (idUsuarioGrupo == 0) {
        alert("Por favor, Selecione um grupo...");
        $("#inputAltIdUsuarioGrupo").focus();
        return false;
    }
    if (idPessoa == 0) {
        alert("Por favor, Selecione uma Pessoa...");
        $("#inputAltIDPessoa").focus();
        return false;
    }
    if (login == "") {
        alert("Por favor, preencha o campo de login.");
        $("#inputAltLogin").focus();
        return false;
    }
    if (idSubSede == 0) {
        alert("Por favor, selecione a subsede");
        $("#form-alt-usuario #idSubSede").focus();
        return false;
    }

    var formData = new FormData();
    formData.append("idUsuarioGrupo", idUsuarioGrupo);
    formData.append("idPessoa", idPessoa);
    formData.append("idColab", idColab);
    formData.append("idSubSede", idSubSede);
    formData.append("idUsuario", idUsuario);
    formData.append("login", login);
    formData.append("senha", senha);
    formData.append("foto", foto);
    formData.append("ativo", ativo);
    formData.append("checkCipa", cipa);
    formData.append("checkBrigada", brigada);
    formData.append("chaveApp", chave);

    $.ajax({
        type: 'POST',
        url: 'includes/rh_usuario_alt_aj.php',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (dados) {
            //
            var response = JSON.parse(dados);
            if (response.status) {
                var mensagem = '<div class="alert alert-success"><strong>Sucesso!</strong> ' + response.msg + '</div>';
            } else {
                var mensagem = '<div class="alert alert-danger"><strong>Erro!</strong> ' + response.msg + '</div>';
            }
            $("#msgAlertErroUsuAlt").html(mensagem);
            setTimeout(function () {
                $("#msgAlertErroUsuAlt").html("");
                var dataTable = $('#example').DataTable();
                dataTable.ajax.reload();
                $("#btnResetAltUsuario").click();
                altModal.hide();
            }, 3000);
        },
        error: function () {
            // Tratar erros de requisição aqui
        }
    });
}

function testeInputAtivo() {
    if (document.getElementById("inputAltAtivo").checked) {
        $("#textoAltAtivo").html("Ativo!");
        $("#inputAltAtivo").val("SIM");
    } else {
        $("#textoAltAtivo").html("Está Inativo!");
        $("#inputAltAtivo").val("NÃO");
    }

}

async function f_visualisar(id) {
    //
    $("#rodape").show();
    const dados = await fetch('includes/rh_usuarios_con_aj.php?id=' + id);
    const resposta = await dados.json();
    //console.log(resposta);            
    if (resposta['status']) {
        visModal.show();
        //
        document.getElementById("idUsuario" ).innerHTML = resposta["dados"].idUsuario;
        document.getElementById("idPessoa"  ).innerHTML = resposta["dados"].idPessoa;
        document.getElementById("idGrupo"   ).innerHTML = resposta["dados"].idUsuarioGrupo + " | " + resposta["dados"].dsGrupo;
        document.getElementById("login"     ).innerHTML = resposta["dados"].login;
        document.getElementById("nome"      ).innerHTML = resposta["dados"].nome;
        document.getElementById("nomeSocial").innerHTML = resposta["dados"].nomeSocial;
        document.getElementById("cpf"       ).innerHTML = resposta["dados"].cpf;
        document.getElementById("email"     ).innerHTML = resposta["dados"].email;
        document.getElementById("criado_em" ).innerHTML = resposta["dados"].criado_em + " (" + resposta["dados"].criado_por + ")";
        //
        let foto = "fotos/" + resposta["dados"].foto;
        $('#fichaFoto').attr('src', foto || 'fotos/perfil.png');

        $("#_chaveApp").html( resposta["dados"].chaveApp );
        //
        if (resposta["dados"].uAtivo) {
            document.getElementById("uAtivo").innerHTML = "Sim";
        } else {
            document.getElementById("uAtivo").innerHTML = "Inativo";
        }
        //
        if (resposta["dados"].pAtivo) {
            document.getElementById("pAtivo").innerHTML = "Sim";
        } else {
            document.getElementById("pAtivo").innerHTML = "Inativo";
        }
        //
        if (resposta["dados"].dcCIPA) {
            document.getElementById("pCipa").innerHTML = "Sim";
        } else {
            document.getElementById("pCipa").innerHTML = "Não";
        }
        //
        if (resposta["dados"].dcBrigada) {
            document.getElementById("pBrigada").innerHTML = "Sim";
        } else {
            document.getElementById("pBrigada").innerHTML = "Não";
        }
        //
        document.getElementById("msgAlert").innerHTML = "";
    } else {
        document.getElementById("msgAlert").innerHTML = resposta['msg'];
    }
}

function f_excluir(id) {
    $("#rodape").show();
    var botoes = '<button type="button" class="btn btn-danger btn-sm" onClick="f_excluirUsuario(' + id + ')"><i class="fas fa-trash" aria-hidden="true"></i> EXCLUIR !</button>';
    var botoes = botoes + '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-door-closed"></i> Fechar</button>';
    f_visualisar(id);
    $("#msgCabVisualisar").html("<h5>DADOS PARA EXCLUSÃO</h5>");
    $("#rodape").html(botoes);
    return true;
}

function f_excluirUsuario(id) {
    const rodape = $("#rodape");
    const msg = $("#msgAlertaVisual")
    rodape.hide();
    $.ajax({
        url: 'includes/rh_usuario_exc_aj.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
            if (response.status) {
                const mensagem = "<div class='alert alert-success text-center'>" + response.msg + "</div>";
                msg.html(mensagem);
            } else {
                const mensagem = "<div class='alert alert-danger text-center'>" + response.msg + "</div>";
                msg.html(mensagem);
            }
            setTimeout(function () {
                $("#msgAlertaVisual").html("");
                var dataTable = $('#example').DataTable();
                dataTable.ajax.reload();
                $("#btnResetIncUser").click();
                visModal.hide();
            }, 3000);
        },
        error: function (xhr, status, error) {
            console.error("Erro na requisição AJAX: " + error);
        }
    });
}

function f_chat(id) {
    alert("Conversando com ID " + id);
}

function f_incluiGrupo_commit() {
    var formulario = $("#form-inc_grupo");

    // Verifica se os campos obrigatórios estão preenchidos
    if (formulario.find("#g_descricao").val() == "" || formulario.find("#g_sigla").val() == "") {
        alert("Por favor, preencha os campos obrigatórios.");
        return;
    }

    // Coleta os dados do formulário
    var dadosFormulario = formulario.serialize();

    // Envia os dados via AJAX para o servidor
    $.post("includes/rh_usuario_aj1.php", dadosFormulario, function (resposta) {
        try {
            // Tenta converter a resposta para JSON
            let dados = JSON.parse(resposta);

            if (dados.status) {
                alert("Grupo adicionado com sucesso!");
                $.post("includes/rh_usuario_aj2.php", { idGrupo: dados.idGrupo }, function (codigo, status) {
                    $("#idSeletorGrupo").html(codigo);
                });
                incModalGrupo.hide();
            } else {
                alert("Erro ao adicionar grupo: " + dados.msg);
            }
        } catch (e) {
            alert("Erro inesperado: " + resposta);
        }
    }).fail(function () {
        alert("Erro na comunicação com o servidor.");
    });
}

function membros(o) {
    o.value = o.checked ? '1' : '0';
}

//
// login.js | Rotinas auxiliares do LOGIN
// (C)haia, 11/02/2025

$(document).ready(function () {
    
    $("#c_nome").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "includes/buscar_colab.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idColab: item.idColab  // <- chave personalizada
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $("#idColab").val(ui.item.idColab); // <- usa a mesma chave
        }
    });
});


$("#botaoLogin").click(function () {
    //- Testa se forneceu o Login ou cpf ou email
    if ($("#inputLogin").val() == "") {
        alert("Usuário ou CPF ou e-Mail deve ser informado");
        $("#inputCPF").focus();
        return false;
    }

    //- Testa se digitou a senha
    if ($("#inputPassword").val() == "") {
        alert("A senha de acesso deve ser informada");
        $("#inputPassword").focus();
        return false;
    }
    if ($("#idPerfil").val() == 0) {
        alert("Selecione o Perfil de Login");
        $("#idPerfil").focus();
        return false;
    }
    //- Faz a autenticação
    $.post("login_aut.php", {
        login: $("#inputLogin").val(),
        senha: $("#inputPassword").val(),
        idPerfil: $("#idPerfil").val(),
        idEmpresa: $("#idEmpresa").val()
    },
        function (dados, status) {
            var retorno = JSON.parse(dados);
            if (retorno.status == 1) {
                $("#formulario").hide();
                $("#aguarde").show();
                if( $("#idPerfil").val() == 1 ) window.location.href = "index.php";
                if( $("#idPerfil").val() == 2 ) window.location.href = "colaborador/index.php";
                if( $("#idPerfil").val() == 3 ) window.location.href = "gestor/index.php";
                if( $("#idPerfil").val() == 4 ) window.location.href = "cipa/index.php";
                if( $("#idPerfil").val() == 5 ) window.location.href = "brigada/index.php";
                if( $("#idPerfil").val() == 6 ) window.location.href = "../recrutamento/index.php";
                return true;
            } else {
                alert(retorno.mensagem);
                return false;
            }
        }
    );
});

function esqueci() {
    $("#formulario").hide();
    $("#form_esqueci").show();
}

function enviar_email() {
    const email = $("#x_email").val();
    const idEmpresa = $("#form_esqueci #x_idEmpresa").val();
    //
    $.post("includes/login_aj.php", { email: email, idEmpresa: idEmpresa },
        function (retorno) {
            const dados = JSON.parse(retorno);
            $("#aguarde").show();
            $("#aguarde").html(dados.msg);
            //alert(retorno);
            //
            // Aguarda 3 segundos (3000ms) e esconde a mensagem
            setTimeout(function () {
                //$("#aguarde").fadeOut();
                location.reload();
            }, 3000);
        });
}

function cadastro(){
    $("#cabecalho").html("RH: Novo Cadastro")
    $("#formulario").hide();
    $("#form_cadastro").show();
    $("#form_cadastro #c_nome").focus();
}

function enviar_cadastro(){
    const nome = $("#c_nome");
    const email = $("#c_email");
    const idEmpresa = $("#form_cadastro #c_idEmpresa").val();
    const idColab = $("#form_cadastro #idColab").val();
    //
    //- Testa se forneceu o Login ou cpf ou email
    if (nome.val() == "") {
        alert("Nome deve ser informado");
        nome.focus();
        return false;
    }
    if (email.val() == "") {
        alert("e-Mail Corporativo deve ser informado");
        email.focus();
        return false;
    }
    if (!email.val().endsWith("@gerar.org.br")) {
        alert("Informe um e-Mail corporativo (@gerar.org.br)");
        email.focus();
        return false;
    }    
    //
    $.post("includes/login_aj1.php", { idColab: idColab, nome: nome.val(), email: email.val(), idEmpresa: idEmpresa },
        function (retorno) {
            const dados = JSON.parse(retorno);
            $("#aguarde").show();
            $("#aguarde").html(dados.msg);
            //alert(retorno);
            //
            // Aguarda 3 segundos (3000ms) e esconde a mensagem
            setTimeout(function () {
                //$("#aguarde").fadeOut();
                location.reload();
            }, 3000);
        });
}

function verifica( o ){
    let login = o.value;
    $.post("includes/login_aj2.php", { login: login },
        function (retorno) {
            const dados = JSON.parse(retorno);
            if (dados.status == 1) {
                const select = document.getElementById('idPerfil');

                // Limpa antes, se necessário
                select.innerHTML = '<option value="0">Selecione</option>';
                
                if (dados.rh === true) {
                    const opt = document.createElement('option');
                    opt.value = '1';
                    opt.textContent = 'RH';
                    select.appendChild(opt);
                }
                if (dados.recrutamento === true) {
                    const opt = document.createElement('option');
                    opt.value = '6';
                    opt.textContent = 'R&S';
                    select.appendChild(opt);
                }
                if (dados.colab === true) {
                    const opt = document.createElement('option');
                    opt.value = '2';
                    opt.textContent = 'Colaborador';
                    select.appendChild(opt);
                }
                if (dados.gestor === true) {
                    const opt = document.createElement('option');
                    opt.value = '3';
                    opt.textContent = 'Gestor';
                    select.appendChild(opt);
                }
                if (dados.dcCIPA === 1) {
                    const opt = document.createElement('option');
                    opt.value = '4';
                    opt.textContent = 'CIPA';
                    select.appendChild(opt);
                }
                if (dados.dcBrigada === 1) {
                    const opt = document.createElement('option');
                    opt.value = '5';
                    opt.textContent = 'Brigadista';
                    select.appendChild(opt);
                }
                
                //
            } else {
                $("#aguarde").html(dados.msg);
            }
        });
}
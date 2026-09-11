

function envia(e) {

    if (e) e.preventDefault();

    const formLogin  = document.getElementById('formLogin');
    const alertArea  = document.getElementById('login-alert');
    const btnLogin   = document.getElementById('btnLogin');
    const btnSpinner = document.getElementById('btnSpinner');
    const btnEsqueciSenha = document.getElementById('btnEsqueciSenha');

        alertArea.innerHTML = ''; // Limpa alertas anteriores

        const usuario = document.getElementById('usuario').value.trim();
        const senha = document.getElementById('senha').value.trim();

        if (!usuario || !senha) {
            mostrarAlerta('Preencha todos os campos para continuar.', 'warning');
            return;
        }

        if( ! testar_senha( $("#senha") ) ) return;

        // Estado de Loading
        setLoading(true);

        const formData = new FormData(formLogin);
        formData.append('acao', 'autenticar');

        fetch('inc/login_aj.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Erro na requisição ao servidor.');
            return response.json();
        })
        .then(data => {
            setLoading(false);

            if (data.status === 'success') {
                mostrarAlerta(data.message || 'Login efetuado com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || 'index.php';
                    return;
                }, 1200);
            } else {
                mostrarAlerta(data.message || 'Usuário ou senha inválidos.', 'danger');
            }
        })
        .catch(error => {
            setLoading(false);
            mostrarAlerta('Erro ao processar solicitação. Tente novamente mais tarde.', 'danger');
            console.error('Erro AJAX:', error);
        });


    // Clique em Esqueci Minha Senha
    btnEsqueciSenha.addEventListener('click', function (e) {
        e.preventDefault();
        const usuario = document.getElementById('usuario').value.trim();
        
        if (!usuario) {
            mostrarAlerta('Informe seu usuário ou e-mail no campo acima para recuperar a senha.', 'info');
            document.getElementById('usuario').focus();
            return;
        }

        setLoading(true);
        const formData = new FormData();
        formData.append('acao', 'recuperar_senha');
        formData.append('usuario', usuario);

        fetch('inc/login_aj.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            setLoading(false);
            mostrarAlerta(data.message, data.status === 'success' ? 'success' : 'danger');
        })
        .catch(() => {
            setLoading(false);
            mostrarAlerta('Falha ao solicitar recuperação de senha.', 'danger');
        });
    });

    // Função auxiliar para exibir alertas Bootstrap
    function mostrarAlerta(mensagem, tipo) {
        alertArea.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show mb-3" role="alert">
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    // Função auxiliar para alternar o estado do botão
    function setLoading(isSpinner) {
        if (isSpinner) {
            btnSpinner.classList.remove('d-none');
            btnLogin.disabled = true;
        } else {
            btnSpinner.classList.add('d-none');
            btnLogin.disabled = false;
        }
    }
}

// Toggle para mostrar/ocultar senha
const toggleSenha = document.getElementById('toggleSenha');
const inputSenha = document.getElementById('senha');
const iconeOlho = document.getElementById('iconeOlho');

if (toggleSenha && inputSenha && iconeOlho) {
    toggleSenha.addEventListener('click', function () {
        const isPassword = inputSenha.getAttribute('type') === 'password';
        
        // Alterna o tipo do input
        inputSenha.setAttribute('type', isPassword ? 'text' : 'password');
        
        // Alterna as classes do ícone
        iconeOlho.classList.toggle('bi-eye', !isPassword);
        iconeOlho.classList.toggle('bi-eye-slash', isPassword);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const inputUsuario = document.getElementById('usuario');
    const inputSenha = document.getElementById('senha');
    const formLogin = document.getElementById('formLogin');

    // 1. Limpa o formulário nativamente
    if (formLogin) {
        formLogin.reset();
    }

    // 2. Função de limpeza de emergência
    function forcarCamposVazios() {
        if (inputUsuario) inputUsuario.value = '';
        if (inputSenha) inputSenha.value = '';
    }

    // Executa imediatamente e em intervalos curtos onde os navegadores injetam o autofill
    forcarCamposVazios();
    setTimeout(forcarCamposVazios, 50);
    setTimeout(forcarCamposVazios, 200);
    setTimeout(forcarCamposVazios, 500);

    // OPCIONAL: Se o usuário voltar na página usando o botão "Voltar" do navegador
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            forcarCamposVazios();
        }
    });
});

function testar_usuario(o) {
    const status = $("#status");
    const input = $(o);
    const usuario = input.val().trim();

    // Se o campo estiver vazio, limpa as validações
    if (!usuario) {
        input.removeClass('is-valid is-invalid');
        return;
    }

    $.post("inc/testar_usuario.php", { usuario: usuario }, function (res) {
        //console.log(res);

        // Supondo que 'res' seja um booleano (true/false) ou um objeto contendo { status: true }
        const isValid = (res === true || res.status === true || res.existe === true);

        if (isValid) {
            input.removeClass('is-invalid').addClass('is-valid');
            status.val(1);
        } else {
            input.removeClass('is-valid').addClass('is-invalid');
            status.val(0);
        }
    }, 'json')
    .fail(function () {
        // Trata eventual erro de conexao/PHP
        input.removeClass('is-valid').addClass('is-invalid');
    });
}

function testar_senha(o){
    const status = $("#status");
    const usuario = $("#usuario");
    const input = $(o);
    const senha = input.val().trim();
    //
    if( status.val() == 0 ){
        alert("Por favor, informe um Gestor Válido para continuar!");
        usuario.val("").focus();
        return false;
    }
    return true;
}
//***********************************************************************************************************//
//***********************************************************************************************************//
//*****************************                                         *************************************//
//*****************************        E Q U I P A M E N T O S          *************************************//
//*****************************                                         *************************************//
//***********************************************************************************************************//
//* Modulo de Termos de Responsabilidade

const verModalTermo = new bootstrap.Modal(document.getElementById("modalVerTermo"));
const modalAssinar = new bootstrap.Modal(document.getElementById("modalAssinar"));
const modalAssinatura = new bootstrap.Modal(document.getElementById("modalAssinatura"));

function f_equip_visualizar(id) {
    $.post("../includes/rh_equipamentos_aj5.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        $("#vw_termo_responsavel").html(resposta.nome);
        $("#vw_termo_endereco").html(resposta.endereco);
        $("#vw_termo_email").html(resposta.termo_email);
        $("#vw_termo_celular").html(resposta.termo_celular);
        $("#vw_termo_equipamentos").html(resposta.tabela);
        $("#vw_termo_criado_em").html(resposta.criado_em);
        $("#vw_termo_criado_por").html(resposta.criado_por);
        $("#vw_termo_status").html(resposta.dsStatus);
        //
        let filename = "../docs_view.php?pessoa=" + resposta.idPessoa + "&arquivo=" + encodeURIComponent(resposta.arquivo);
        let url = '<embed src="' + filename + '" type="application/pdf" width="100%" height="400px" />';
        $("#view_documento").html(url);
        $("#vw_termo_documento").val(filename);
    });
    verModalTermo.show();
}

function f_preview_documento() {
    let filename = $("#vw_termo_documento").val();
    if (filename) {
        window.open(filename, '_blank'); // abre em nova guia
    } else {
        alert("Nenhum documento disponível para visualização.");
    }
}

function f_termo_assinar(id) {
    $.post("../includes/rh_equipamentos_aj5.php", { id: id }, function (data) {
        let resposta = JSON.parse(data);
        $("#assinar_id").val(resposta.id);
        $("#termoConteudo").html(resposta.termo);
        modalAssinar.show();
    });
}

function marquei_lido() {
    const check = document.getElementById("concordo");
    if (check.checked) {
        //console.log("Está marcado ✅");
        // aqui você pode habilitar o botão, por exemplo:
        document.getElementById("btnAssinar").disabled = false;
    } else {
        //console.log("Não está marcado ❌");
        document.getElementById("btnAssinar").disabled = true;
    }
}

function clicou_assinar() {
    let botaoAssinar = $("#botaoAssinar");
    botaoAssinar.addClass("d-none");
    modalAssinatura.show();
}

document.getElementById("toggleSenha").addEventListener("click", function () {
    const senha = document.getElementById("senha");
    const icon = document.getElementById("iconSenha");

    if (senha.type === "password") {
        senha.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        senha.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
});

function confirma_assinatura() {
    let botoes = $("#botoes_assinar");
    let mensagem = $("#msgAlerta");
    let usuario = $("#usuario").val();
    let senha = $("#senha").val();
    let idTermo = $("#idTermo").val();
    let token = $("#token").val();
    //
    botoes.addClass("d-none");
    $.post("../termos/index_aj11.php", {
        idTermo: idTermo,
        usuario: usuario,
        senha: senha,
        token: token
    }, function (res) {
        let dados = JSON.parse(res);
        mensagem.html(dados.msg);
        setTimeout(() => {
            /*
            $("#modalAssinar").modal("hide");
            $("#divPrincipal").modal("hide");
            $("#divObrigado").modal("show");
            */
            window.location.reload();
        }, 3000);
    });

}
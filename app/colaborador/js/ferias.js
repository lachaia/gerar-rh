//
//- ferias.js | JavaScript da Seção de Ferias
//

const modalAgenda = new bootstrap.Modal(document.getElementById("modalAgenda"));

$(document).ready(function () {

    let idPessoa = $("#idPessoa").val();
    if (idPessoa > 0) {
        atualiza_pessoa(idPessoa);
    }

    $('#obs').summernote({
        height: 150,
        lang: 'pt-BR',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['codeview']]
        ],
        callbacks: {
            onInit: function () {
                // Garante que o texto fique branco ao inicializar
                $('.note-editable').css({
                    'color': 'black',
                    'background-color': 'white'
                });
            }
        }
    });

});

function agendar(id) {
    $.post("includes/ferias_aj1.php", { id: id }, function (retorno) {
        let d = JSON.parse(retorno);
        let per_aquisitivo = formatarDataBR(d.dados.inicio_aquisitivo) + " a " + formatarDataBR(d.dados.fim_aquisitivo);
        // Converte as datas para objetos Date
        let inicioConcessivo = new Date(d.dados.inicio_concessivo);
        let fimConcessivo = new Date(d.dados.fim_concessivo);

        // Reduz 60 dias do fim do período concessivo
        fimConcessivo.setDate(fimConcessivo.getDate());

        // Calcula 30 dias antes de fimConcessivo
        let dtLimite = new Date(fimConcessivo);
        dtLimite.setDate(fimConcessivo.getDate() - 45);

        // Formata como YYYY-MM-DD para preencher o input type="date"
        let dataFormatada = dtLimite.toISOString().split('T')[0];

        // Formata para dd/mm/aaaa
        let per_concessivo = formatarDataBR(inicioConcessivo.toISOString().slice(0, 10)) + " a " + formatarDataBR(dtLimite.toISOString().slice(0, 10));
        //let per_concessivo = formatarDataBR(d.dados.inicio_concessivo) + " a " + formatarDataBR(d.dados.fim_concessivo);
        $("#aquisitivo").html(per_aquisitivo);
        $("#concessivo").html(per_concessivo);
        $("#id").val(id);
        $("#fruido1").val(d.dados.data_parte1);
        $("#fruido2").val(d.dados.data_parte2);
        $("#fruido3").val(d.dados.data_parte3);
        //
        // Define no input
        $("#dtLimite").val(dataFormatada);
        //
        if (d.dados.agenda_parte1) {
            $("#e_agenda1").val(d.dados.agenda_parte1)
                .prop("readonly", true)
                .css("background-color", "#e9ecef"); // cinza claro usado no Bootstrap
            $("#e_dias1")
                .val(d.dados.dias_parte1)
                .prop("readonly", true)
                .css("background-color", "#e9ecef"); // cinza claro usado no Bootstrap
        }
        if (d.dados.agenda_parte2) {
            $("#e_agenda2").val(d.dados.agenda_parte2)
                .prop("readonly", true)
                .css("background-color", "#e9ecef"); // cinza claro usado no Bootstrap
            $("#e_dias2")
                .val(d.dados.dias_parte2)
                .prop("readonly", true)
                .css("background-color", "#e9ecef"); // cinza claro usado no Bootstrap
        }
    });
    modalAgenda.show();
}

function formatarDataBR(dataIso) {
    let [ano, mes, dia] = dataIso.split("-");
    return `${dia}/${mes}/${ano}`;
}

function f_reset() {
    let id = $("#id").val();
    $.post("includes/ferias_aj1.php", { id: id }, function (retorno) {
        const d = JSON.parse(retorno);
        $("#e_agenda1").val(d.dados.agenda_parte1);
        $("#e_dias1").val(d.dados.dias_parte1);
        $("#e_agenda2").val(d.dados.agenda_parte2);
        $("#e_dias2").val(d.dados.dias_parte2);
        $("#e_agenda3").val(d.dados.agenda_parte3);
        $("#e_dias3").val(d.dados.dias_parte3);
        //
        $("#fruido1").val(d.dados.data_parte1);
        $("#fruido2").val(d.dados.data_parte2);
        $("#fruido3").val(d.dados.data_parte3);
        //
        $("#obs").summernote('code', "");
    });
}

function f_agendar_commit() {
    //alert("f_agendar_commit");
    let id = $("#id").val();
    let fruido1 = $("#fruido1").val();
    let fruido2 = $("#fruido2").val();
    let fruido3 = $("#fruido3").val();
    let dtLimite = $("#dtLimite").val();
    let agenda1 = $("#e_agenda1").val();
    let agenda2 = $("#e_agenda2").val();
    let agenda3 = $("#e_agenda3").val();
    let dias1 = $("#e_dias1").val();
    let dias2 = $("#e_dias2").val();
    let dias3 = $("#e_dias3").val();
    let obs = $("#obs").val();
    let nmSupervisor = $("#nmSupervisor").val();
    let emailSupervisor = $("#emailSupervisor").val();
    let idCSuper = $("#idCSuper").val();
    let orgao = $("#orgao").val();
    //
    if ($("#e_agenda1").val() == "") {
        alert("Informe a data do 1º agendamento");
        $("#e_agenda1").focus();
        return false;
    }
    if ($("#e_dias1").val() == "") {
        alert("Informe a quantidades de dias para o 1º agendamento");
        $("#e_dias1").focus();
        return false;
    }
    //
    if (
        !validarAgenda('e_agenda1', '1ª parcela', dtLimite) ||
        !validarAgenda('e_agenda2', '2ª parcela', dtLimite) ||
        !validarAgenda('e_agenda3', '3ª parcela', dtLimite)
    ) {
        return false; // Se alguma falhar, cancela envio
    }

    //
    // Validação de 45 dias de antecedência para agendamentos (exceto fruídos)
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0); // zera hora

    function validarAntecedencia(dataStr, fruidoStr, parcela) {
        if (dataStr && !fruidoStr) {
            let dataAgendada = new Date(dataStr);
            let diffEmMs = dataAgendada - hoje;
            let diffEmDias = diffEmMs / (1000 * 60 * 60 * 24);

            if (diffEmDias < 45) {
                alert(`A data da parcela ${parcela} precisa ser agendada com no mínimo 45 dias de antecedência.`);
                return false;
            }
        }
        return true;
    }

    if (!validarAntecedencia(agenda1, fruido1, 1)) {
        $("#e_agenda1").focus();
        return false;
    }
    if (!validarAntecedencia(agenda2, fruido2, 2)) {
        $("#e_agenda2").focus();
        return false;
    }
    if (!validarAntecedencia(agenda3, fruido3, 3)) {
        $("#e_agenda3").focus();
        return false;
    }
    //
    $.post("includes/ferias_aj2.php", {
        id: id, agenda1: agenda1, dias1: dias1, agenda2: agenda2, dias2: dias2,
        agenda3: agenda3, dias3: dias3, obs: obs, emailSupervisor: emailSupervisor,
        nmSupervisor: nmSupervisor, orgao: orgao, idCSuper: idCSuper
    },
        function (retorno) {
            const dados = JSON.parse(retorno);
            $("#msgAlertaAgenda").html(dados.msg);
            //
            setTimeout(function () {
                $("#msgAlertaAgenda").html("");
                modalAgenda.hide();
                document.location.reload(true);
            }, 3000);
        });
}

function validarAgenda(idCampo, nomeParcela, dtLimite) {
    let valor = document.getElementById(idCampo).value;
    if (!valor) return true; // campo vazio, nada a validar

    let dataAgenda = new Date(valor);
    let dataLimite = new Date(dtLimite);

    // hoje + 30 dias
    let hoje = new Date();
    let dataMinima = new Date();
    dataMinima.setDate(hoje.getDate() + 30);

    // Sexta-feira = 5
    if (dataAgenda.getDay() === 5) {
        alert(`A data da ${nomeParcela} não pode cair em uma sexta-feira!`);
        document.getElementById(idCampo).value = '';
        document.getElementById(idCampo).focus();
        return false;
    }

    if (dataAgenda < dataMinima) {
        alert(`A data da ${nomeParcela} deve ser ao menos 30 dias após hoje!`);
        document.getElementById(idCampo).value = '';
        document.getElementById(idCampo).focus();
        return false;
    }

    if (dataAgenda > dataLimite) {
        alert(`A data da ${nomeParcela} ultrapassa o limite permitido!\nFavor dirigir-se ao RH para mais informações.`);
        document.getElementById(idCampo).value = '';
        document.getElementById(idCampo).focus();
        return false;
    }

    return true;
}

function dias(o) {
    let d1 = parseInt($("#e_dias1").val()) || 0;
    let d2 = parseInt($("#e_dias2").val()) || 0;
    let d3 = parseInt($("#e_dias3").val()) || 0;

    let total = d1 + d2 + d3;

    if (total > 30) {
        alert("ERRO: excede 30 dias");
        o.value = 0;
        o.focus();
        return;
    }

    if (d1 === 30) {
        $("#e_dias2, #e_dias3, #e_agenda2, #e_agenda3")
            .val("")
            .prop("disabled", true)
            .css("background-color", "#e9ecef"); // cinza claro (similar ao Bootstrap disabled)
    } else {
        $("#e_dias2, #e_dias3, #e_agenda2, #e_agenda3")
            .prop("disabled", false)
            .css("background-color", ""); // volta ao padrão
    }
}
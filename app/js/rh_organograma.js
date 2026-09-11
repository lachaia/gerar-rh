var dataTable;
var linhasPorPagina = 12;

const visModal = new bootstrap.Modal(document.getElementById("modalVisualizar" ));
const altModal = new bootstrap.Modal(document.getElementById("modalEditar" ));
const incModal = new bootstrap.Modal(document.getElementById("modalIncluir"));

$(document).ready(function () {
    $("#sidebarToggle").trigger("click"); // Dispara o clique
    //
    setTimeout(() => {
        constroiTabela();
    }, 300); // Ajuste o tempo conforme necessário
});

function constroiTabela() {
    const alturaTotal = window.screen.height;
    const alturaViewport = window.innerHeight;
    var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
    //
    // Definir quantidade de linhas por página dinamicamente
    if( alturaTotal == alturaViewport ) {
        linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
    }   

    dataTable = new DataTable('#example', {
        "processing": true,
        "serverSide": false,
        "order": [
            [7, "asc"],
            [8, "asc"],
            [9, "asc"],
            [10, "asc"],
            [11, "asc"],
            [12, "asc"]
        ],
        "scrollX": true,
        "pageLength": linhasPorPagina, // Define a quantidade de linhas
        "ajax": {
            "url": "rh_organograma_aj.php",
            "type": "POST"
        },
        "columnDefs": [{
            "targets": [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12,13,14],
            "className": "text-center"
        }],
        language: {
            url: 'includes/pt-BR.json',
        },
    });
}


async function selecionou() {
    // Destroy the existing DataTable
    //
    dataTable.clear().draw();
    dataTable.destroy();
    constroiTabela();
    //
}

window.onresize = selecionou; // Atualiza quando a tela for redimensionada

function f_incluir() {
    incModal.show();
    //
    $.post("includes/rh_organograma_aj1.php", { idOrgao: 0 }, function (retorno) {
        $("#seletor_supervisor").html(retorno);
    });
}

function f_incluir_commit() {
    //
    const nome = $("#formIncluir #_nome");
    const nivel = $("#formIncluir #_nivel");
    const idSupervisor = $("#formIncluir #idSupervisor");
    const nivel1 = $("#formIncluir #_nivel1");
    const nivel2 = $("#formIncluir #_nivel2");
    const nivel3 = $("#formIncluir #_nivel3");
    const nivel4 = $("#formIncluir #_nivel4");
    const nivel5 = $("#formIncluir #_nivel5");
    const nivel6 = $("#formIncluir #_nivel6");
    const nivel7 = $("#formIncluir #_nivel7");
    //
    if (nome.val() == "") {
        nome.focus();
        alert("Informe o nome do setor/órgão");
        return;
    }
    //
    if (nivel.val() == "") {
        nivel.focus();
        alert("Informe o nível na estrutura");
        return;
    }
    //
    if (idSupervisor.val() == "0") {
        idSupervisor.focus();
        alert("Selecione o Órgão Supervisor desta área");
        return;
    }
    //
    if (nivel1.val() == "") {
        nivel1.focus();
        alert("Informe o dígito nivel 1 do setor/órgão");
        return;
    }
    //
    if (nivel2.val() == "") {
        nivel2.focus();
        alert("Informe o dígito nivel 2 do setor/órgão");
        return;
    }
    //
    if (nivel3.val() == "") {
        nivel3.focus();
        alert("Informe o dígito nivel 3 do setor/órgão");
        return;
    }
    //
    if (nivel4.val() == "") {
        nivel4.focus();
        alert("Informe o dígito nivel 4 do setor/órgão");
        return;
    }
    //
    if (nivel5.val() == "") {
        nivel5.focus();
        alert("Informe o dígito nivel 5 do setor/órgão");
        return;
    }
    //
    if (nivel6.val() == "") {
        nivel6.focus();
        alert("Informe o dígito nivel 6 do setor/órgão");
        return;
    }
    if (nivel7.val() == "") {
        nivel7.focus();
        alert("Informe o dígito nivel 7 do setor/órgão");
        return;
    }
    //
    $("#botoes_incluir").hide();
    $("#msgAlertaIncluir").show();
    $("#msgAlertaIncluir").html("<h6 class='text-center'>Processando...</h6>");
    //
    var dados = $("#formIncluir").serialize();
    //
    // Envio via AJAX
    $.post("includes/rh_organograma_aj2.php", dados, function (resposta) {
        $("#msgAlertaIncluir").html(resposta);
        setTimeout(function() {
            document.location.reload(true);
        }, 3000);        
    });
}

function f_excluir(id) {
    if (confirm("Tem certeza que deseja excluir o registro de ID " + id + "?")) {
        $("#divAlertaOrganograma").show();
        $.post("includes/rh_organograma_aj3.php", { id: id }, function (response) {
            const dados = JSON.parse(response);
            $("#divAlertaOrganograma").html( dados.msg );
            //
            setTimeout(function() {
                document.location.reload(true);
                alert("oi");
            }, 3000);            
            
        }).fail(function () {
            alert("Erro ao excluir o registro.");
        });
        document.location.reload(true);
    }
}

function f_editar( id ) {
    //
    $.post("includes/rh_organograma_aj4.php", { id: id }, function (retorno) {
        const dados = JSON.parse(retorno);
        altModal.show();
        $("#formEditar #_nome"  ).val(dados.descricao);
        $("#formEditar #_id"    ).val(dados.idOrgao );
        $("#formEditar #_nivel" ).val(dados.nivel );
        $("#formEditar #_nivel1").val(dados.nivel_1 );
        $("#formEditar #_nivel2").val(dados.nivel_2 );
        $("#formEditar #_nivel3").val(dados.nivel_3 );
        $("#formEditar #_nivel4").val(dados.nivel_4 );
        $("#formEditar #_nivel5").val(dados.nivel_5 );
        $("#formEditar #_nivel6").val(dados.nivel_6 );
        $("#formEditar #_estrategico").val(dados.estrategico);
        $("#formEditar #_staff").val(dados.staff);
        //
        $.post("includes/rh_organograma_aj1.php", { idOrgao: dados.idSupervisor }, function (retorno) {
            $("#formEditar #seletor_supervisor").html(retorno);
        });
        //
    });
}

function f_editar_commit(){
    //
    const nome         = $("#formEditar #_nome");
    const nivel        = $("#formEditar #_nivel");
    const idSupervisor = $("#formEditar #idSupervisor");
    const nivel1       = $("#formEditar #_nivel1");
    const nivel2       = $("#formEditar #_nivel2");
    const nivel3       = $("#formEditar #_nivel3");
    const nivel4       = $("#formEditar #_nivel4");
    const nivel5       = $("#formEditar #_nivel5");
    const nivel6       = $("#formEditar #_nivel6");
    const nivel7       = $("#formEditar #_nivel7");
    //
    if (nome.val() == "") {
        nome.focus();
        alert("Informe o nome do setor/órgão");
        return;
    }
    //
    if (nivel.val() == "") {
        nivel.focus();
        alert("Informe o nível na estrutura");
        return;
    }
    //
    if (idSupervisor.val() == "0") {
        idSupervisor.focus();
        alert("Selecione o Órgão Supervisor desta área");
        return;
    }    
    //
    if (nivel1.val() == "") {
        nivel1.focus();
        alert("Informe o dígito nivel 1 do setor/órgão");
        return;
    }
    //
    if (nivel2.val() == "") {
        nivel2.focus();
        alert("Informe o dígito nivel 2 do setor/órgão");
        return;
    }
    //
    if (nivel3.val() == "") {
        nivel3.focus();
        alert("Informe o dígito nivel 3 do setor/órgão");
        return;
    }
    //
    if (nivel4.val() == "") {
        nivel4.focus();
        alert("Informe o dígito nivel 4 do setor/órgão");
        return;
    }
    //
    if (nivel5.val() == "") {
        nivel5.focus();
        alert("Informe o dígito nivel 5 do setor/órgão");
        return;
    }
    //
    if (nivel6.val() == "") {
        nivel6.focus();
        alert("Informe o dígito nivel 6 do setor/órgão");
        return;
    }
    //
    if (nivel7.val() == "") {
        nivel7.focus();
        alert("Informe o dígito nivel 7 do setor/órgão");
        return;
    }
    //
    $("#botoes_editar").hide();
    $("#msgAlertaEditar").show();
    $("#msgAlertaEditar").html("<h6 class='text-center'>Processando...</h6>");
    //
    var dados = $("#formEditar").serialize();
    //
    // Envio via AJAX
    $.post("includes/rh_organograma_aj5.php", dados, function (resposta) {
        $("#msgAlertaEditar").html(resposta);
        setTimeout(function() {
            document.location.reload(true);
        }, 3000);        
    });
}

function f_visualizar(id) {
    $.post("includes/rh_organograma_aj4.php", { id: id }, function(retorno) {
        const dados = JSON.parse(retorno);

        $("#v_nome").text(dados.descricao);
        $("#v_nivel").text(dados.nivel);
        $("#v_idSupervisor").text(dados.dsSupervisor);
        $("#v_staff").text(dados.staff == 1 ? "Sim" : "Não");
        $("#v_estrategico").text(dados.estrategico == 1 ? "Sim" : "Não");
        $("#v_nivel1").text(dados.nivel_1);
        $("#v_nivel2").text(dados.nivel_2);
        $("#v_nivel3").text(dados.nivel_3);
        $("#v_nivel4").text(dados.nivel_4);
        $("#v_nivel5").text(dados.nivel_5);
        $("#v_nivel6").text(dados.nivel_6);
        $("#v_nivel7").text(dados.nivel_7);
        $("#divQuando").html( dados.usuario + " em " + dados.data );
        // Abrir o modal de visualização
        $("#modalVisualizar").modal("show");
    });
}

function selecionou_orgao(o){
    let id = o.value;
    //
    $.post("includes/rh_organograma_aj6.php",{idOrgao: id}, function(res){
        let dados = JSON.parse( res );
        $("#_nivel1").val(dados.nivel_1);
        $("#_nivel2").val(dados.nivel_2);
        $("#_nivel3").val(dados.nivel_3);
        $("#_nivel4").val(dados.nivel_4);
        $("#_nivel5").val(dados.nivel_5);
        $("#_nivel6").val(dados.nivel_6);
        $("#_nivel7").val(dados.nivel_7);
    });
}
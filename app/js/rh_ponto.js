//
//- rh_ponto.js | Rotinas auxiliares do RH_PONTO
// (C)haia, 16/12/2025
//

var dataTable_calendario = "";
var dataTable_espelhos = "";
var dataTable_ajustes = "";

const incCalendar = new bootstrap.Modal(document.getElementById("modalCalendario"));
const verCalendar = new bootstrap.Modal(document.getElementById("modalCalendarioEvento"));

$(document).ready(function () {
    //
    constroi_calendario();
    constroi_espelhos();
    constroi_ajustes();
    //
    $("#cidade").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "includes/buscar_cidades.php",
                type: "GET",
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.nome,
                            value: item.nome,
                            idCidade: item.id,
                            uf: item.uf
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            $("#cidade").val(ui.item.value);
            $("#cidade_id").val(ui.item.idCidade); // hidden
            //$("#uf").val(ui.item.uf);              // se existir campo UF
            return false;
        }
    });    
});

//
//- C A L E N D A R I O
//

    function constroi_calendario(){
        // Definir quantidade de linhas por página dinamicamente
        const alturaTotal = window.screen.height;
        const alturaViewport = window.innerHeight;
        //
        var linhasPorPagina = 8;
        //if( alturaViewport > 800) linhasPorPagina = 13
        //if( alturaViewport > 900) linhasPorPagina = 14
        //if( alturaViewport > 1000) linhasPorPagina = 18
        //
        dataTable_calendario = new DataTable('#tabCalendario', {
            "dom": 'frtp',
            "processing": false,
            "serverSide": false,
            "scrollX": true,
            "pageLength": linhasPorPagina, // Define a quantidade de linhas        
            "ajax": {
                "url": "includes/rh_ponto_aj1.php",
                "type": "POST"
            },
            "columnDefs": [
                {
                    "targets": [0], // Índices das colunas a serem centralizadas (3 e 4 neste caso)
                    "className": "text-center" // Classe CSS para centralizar o conteúdo
                },
                {
                    "targets": [],
                    "className": "dt-body-right"
                },
                { 'orderable': false, 'targets': [] },
                { "targets": [5],
                    visible: false
                },
            ],
            language: {
                url: 'includes/pt-BR.json',
            },
        });
    }

    async function selecionou() {
        // Destroy the existing DataTable
        //
        dataTable_calendario.clear().draw();
        dataTable_calendario.destroy();
        constroi_calendario();
        //
    }

    function f_incluir_calendario(){
        incCalendar.show();
    }

    $("#formCalendario #tipo_calendario").on("change", function () {

        const tipo = $(this).val();

        // Esconde tudo primeiro
        $("#formCalendario #grupo_uf, #formCalendario #grupo_cidade, #formCalendario #grupo_hora_ini, #formCalendario #grupo_hora_fim").addClass("d-none");

        if (tipo === "FERIADO") {
            // pode ser estadual ou municipal
            $("#formCalendario #grupo_uf, #formCalendario #grupo_cidade").removeClass("d-none");
        }
        if (tipo === "REDUZIDO") {
            $("#formCalendario #grupo_hora_ini, #formCalendario #grupo_hora_fim").removeClass("d-none");
        }
    });

    $("#modalCalendarioEvento #tipo_calendario").on("change", function () {

        const tipo = $(this).val();

        // Esconde tudo primeiro
        $("#modalCalendarioEvento #grupo_uf, #modalCalendarioEvento #grupo_cidade, #modalCalendarioEvento #grupo_hora_ini, #modalCalendarioEvento #grupo_hora_fim").addClass("d-none");

        if (tipo === "FERIADO") {
            // pode ser estadual ou municipal
            $("#modalCalendarioEvento #grupo_uf, #modalCalendarioEvento #grupo_cidade").removeClass("d-none");
        }
        if (tipo === "REDUZIDO") {
            $("#modalCalendarioEvento #grupo_hora_ini, #modalCalendarioEvento #grupo_hora_fim").removeClass("d-none");
        }
    });

    function f_salvarCalendario() {
        const mensagem = document.getElementById("msgAlertaCalendario");
        const botoes = document.getElementById("botoes_calendario");
        //
        botoes.classList.add("d-none");
        mensagem.innerHTML = "<i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...";
        //
        $.post(
            "includes/rh_ponto_aj2.php",
            $("#formCalendario").serialize(),
            function (ret) {
                mensagem.innerHTML = ret.msg;
                setTimeout(function () {
                    if( ret.status == false ){
                        mensagem.innerHTML = "";
                        botoes.classList.remove("d-none");
                        return;
                    }                
                    location.reload();
                }, 3000);                
            },
            "json"
        );
    }

    function mudou_uf(){
        const uf = document.getElementById("uf");
        const cidade = document.getElementById("cidade");

        if (uf.value !== "") {
            cidade.value = "";
            cidade.disabled = true;   // 🔒 bloqueia cidade
        } else {
            cidade.disabled = false;  // 🔓 libera cidade
        }
    }

    function f_ver( id ){
        //- Modal: Visualiza / Edita / Exclui o evento 
        //
        const hora_ini = $("#formCalendarioEvento #hora_ini");
        const hora_fim = $("#formCalendarioEvento #hora_fim");
        //
        $.post("includes/rh_ponto_aj3.php", { id: id }, function (ret) {
            //
            $("#formCalendarioEvento #id_calendario_evento").val(ret.id);
            $("#formCalendarioEvento #data_calendario").val(ret.data);
            $("#formCalendarioEvento #tipo_calendario").val(ret.tipo);
            $("#formCalendarioEvento #descricao_calendario").val(ret.descricao);
            $("#formCalendarioEvento #uf").val(ret.estado);
            $("#formCalendarioEvento #cidade").val(ret.nmCidade);
            $("#formCalendarioEvento #cidade_id").val(ret.cidade_id);
            //
            if( ret.tipo == 'REDUZIDO' ) {
                $("#formCalendarioEvento #hora_ini").val(ret.hora_ini);
                $("#formCalendarioEvento #hora_fim").val(ret.hora_fim);
                $("#formCalendarioEvento #grupo_hora_ini, #formCalendarioEvento #grupo_hora_fim").removeClass("d-none");
            }
            verCalendar.show();
        }, "json");
    }

    function f_excluirCalendarioEvento(){
        console.log("excluindo registro");
        const botoes = $("#botoes_calendario_evento");
        const mensagem = $("#msgAlertaCalendarioEvento");
        const id = $("#formCalendarioEvento #id_calendario_evento").val();
        //
        const resposta = confirm("Confirma a exclusão deste evento?");
        if( !resposta ) return;
        //
        botoes.addClass("d-none");
        mensagem.html("<i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...");
        console.log("antes da função ajax");
        $.post("includes/rh_ponto_aj4.php", { id: id }, function (ret) {
            console.log("Resposta: ", ret);
            console.log("Mensagem: ", ret.msg);
        mensagem.html(ret.msg);
        setTimeout(function () {
            if( ret.status == false ){
                mensagem.html("");
                botoes.removeClass("d-none");
                return;
            } else {
                location.reload();
            }
        }, 3000);
        }, "json");
    }

    function f_salvarCalendarioEvento(){
        const botoes = $("#botoes_calendario_evento");
        const mensagem = $("#msgAlertaCalendarioEvento");
        //
        botoes.addClass("d-none");
        mensagem.html("<i class='fa-solid fa-spinner fa-spin mt-4'></i> Enviando...");
        //
        $.post(
            "includes/rh_ponto_aj5.php",
            $("#formCalendarioEvento").serialize(),
            function (ret) {
                mensagem.html( ret.msg );
                setTimeout(function () {
                    if( ret.status == false ){
                        mensagem.html(""); 
                        botoes.removeClass("d-none");
                        return;
                    }                
                    location.reload();
                }, 3000);                
            },
            "json"
        );   
    }

//
//-- E S P E L H O S  do Cartão Ponto
//

    function constroi_espelhos(){
        // Definir quantidade de linhas por página dinamicamente
        const alturaTotal = window.screen.height;
        const alturaViewport = window.innerHeight;
        //
        var linhasPorPagina = 8;
        //if( alturaViewport > 800) linhasPorPagina = 13
        //if( alturaViewport > 900) linhasPorPagina = 14
        //if( alturaViewport > 1000) linhasPorPagina = 18
        //
        dataTable_espelhos = new DataTable('#tabEspelhos', {
            dom: 'frtp',
            processing: false,
            serverSide: false,
            scrollX: true,
            pageLength: linhasPorPagina, // Define a quantidade de linhas        
            ajax: {
                url: "includes/rh_espelhos_aj.php",
                type: "POST",
                data: function (d) {
                d.status = $("#seletorStatus").val();
            }
            },
            order: [0, "desc"],
            columnDefs: [
                {
                    targets: [], // Índices das colunas a serem centralizadas (3 e 4 neste caso)
                    className: "text-center" // Classe CSS para centralizar o conteúdo
                },
                {
                    targets: [],
                    className: "dt-body-right"
                },
                { orderable: false, 'targets': [] },
                { targets: [0],
                    visible: false
                },
            ],
            language: {
                url: 'includes/pt-BR.json',
            },
        });
    }

    function f_espelhos_do_ponto(){
        const divCalendario = document.getElementById("divCalendario");
        const divEspelho = document.getElementById("divEspelho");
        const divAjustes = document.getElementById("divAjustes");
        //
        divCalendario.classList.add("d-none");
        divEspelho.classList.remove("d-none");
        divAjustes.classList.add("d-none");
    }

    function f_calendario(){
        const divCalendario = document.getElementById("divCalendario");
        const divEspelho = document.getElementById("divEspelho");
        const divAjustes = document.getElementById("divAjustes");
        //
        divCalendario.classList.remove("d-none");
        divEspelho.classList.add("d-none");
        divAjustes.classList.add("d-none");
        //
        f_limpa_janelas();
    }

    async function f_selecionouStatus() {
        // Destroy the existing DataTable
        //
        dataTable_espelhos.clear().draw();
        dataTable_espelhos.destroy();
        constroi_espelhos();
        //
    }

    function f_ver_espelho( id ){
        const divDashboard = document.getElementById("divDashboard");
        const divVerEspelho = document.getElementById("divVerEspelho");
        const divAssinado = document.getElementById("divAssinadoEm");
        //
        divDashboard.classList.add("d-none");
        divAjustes.classList.add("d-none");
        divVerEspelho.classList.remove("d-none");
        //
        $.post("includes/rh_espelhos_aj1.php", { id: id }, function (ret) {
            //
            $("#esp_nome"   ).html( ret.nome );
            $("#esp_mes_ref").html( ret.mes_ref );
            $("#esp_periodo_inicial").html( ret.periodo_ini  );
            $("#esp_periodo_final"  ).html( ret.periodo_fim  );
            $("#esp_horas_normais"  ).html( ret.horas_normal );
            $("#esp_horas_extras"   ).html( ret.horas_extras );
            $("#esp_horas_faltas"   ).html( ret.horas_faltas );
            $("#esp_status"         ).html( ret.dsStatus     );
            $("#esp_assinado_em"    ).html( ret.assinado_em  );
            //
            let filename = "ponto/docs/" + ret.colaborador_id + "/" + ret.arquivo;
            let url = '<embed src="' + filename + '" type="application/pdf" width="100%" height="400px" />'; 
            $("#view_documento").html(url);  
            $("#url_arquivo_espelho").val( filename );  
            //
            if( ret._status == 'ASSINADO'){
                divAssinado.classList.remove("d-none");   
            } else{
                divAssinado.classList.add("d-none");
            }
            //
        },'json');
        
    }

    function f_dashboard(){
        const divDashboard = document.getElementById("divDashboard");
        const divVerEspelho = document.getElementById("divVerEspelho");
        const divDetalhePonto = document.getElementById("divDetalhePonto");
        //
        divDashboard.classList.remove("d-none");
        divVerEspelho.classList.add("d-none");
        divDetalhePonto.classList.add("d-none");
    }

    function f_preview_documento() {
        //
        let url = $("#url_arquivo_espelho").val();
        if (!url) {
            alert("Arquivo não informado.");
            return;
        }
        window.open(url, '_blank');
    }

//
//-- A J U S T E S  do Cartão Ponto
//

    function f_ajustes(){
        const divCalendario = document.getElementById("divCalendario");
        const divEspelho = document.getElementById("divEspelho");
        const divAjustes = document.getElementById("divAjustes");
        //
        divCalendario.classList.add("d-none");
        divEspelho.classList.add("d-none");
        divAjustes.classList.remove("d-none");
        //
        f_limpa_janelas();
    }

    function constroi_ajustes(){
        // Definir quantidade de linhas por página dinamicamente
        const alturaTotal = window.screen.height;
        const alturaViewport = window.innerHeight;
        //
        var linhasPorPagina = 8;
        //if( alturaViewport > 800) linhasPorPagina = 13
        //if( alturaViewport > 900) linhasPorPagina = 14
        //if( alturaViewport > 1000) linhasPorPagina = 18
        //
        dataTable_ajustes = new DataTable('#tabAjustes', {
            dom: 'frtp',
            processing: false,
            serverSide: false,
            scrollX: true,
            pageLength: linhasPorPagina, // Define a quantidade de linhas        
            ajax: {
                url: "includes/rh_ajustes_aj.php",
                type: "POST",
                data: function (d) {
                d.status = $("#seletorStatus2").val();
            }
            },
            order: [0, "desc"],
            columnDefs: [
                {
                    targets: [], // Índices das colunas a serem centralizadas (3 e 4 neste caso)
                    className: "text-center" // Classe CSS para centralizar o conteúdo
                },
                {
                    targets: [],
                    className: "dt-body-right"
                },
                { orderable: false, 'targets': [] },
                { targets: [],
                    visible: false
                },
            ],
            language: {
                url: 'includes/pt-BR.json',
            },
        });
    }

    async function f_selecionouStatus2() {
        // Destroy the existing DataTable
        //
        dataTable_ajustes.clear().draw();
        dataTable_ajustes.destroy();
        constroi_ajustes();
        //
    }

    function f_limpa_janelas(){
        const divDashboard  = document.getElementById("divDashboard");
        const divVerEspelho = document.getElementById("divVerEspelho");
        //
        divDashboard.classList.remove("d-none");
        divVerEspelho.classList.add("d-none");
    }

    function f_ver_ajuste( id ){
        //alert( id );
        //- Detalhe do Ajuste do Cartão Ponto - Coluna 1
        const divDashboard  = document.getElementById("divDashboard");
        const divVerEspelho = document.getElementById("divVerEspelho");
        const divDetalhePonto = document.getElementById("divDetalhePonto");
        //
        divDashboard.classList.add("d-none");
        divVerEspelho.classList.add("d-none");
        divDetalhePonto.classList.remove("d-none");
        //
        $.post("includes/rh_ajustes_aj1.php",{id:id},function(ret){
            //
            $("#data_solicitacao" ).html( ret.dados.solicitado_em );
            $("#tipo_solicitacao" ).html( ret.dados.tipo );
            $("#ajuste_solicitado").html( ret.dados.data_hora );
            //
            $("#status_solicitacao"  ).html( ret.dados.status );
            $("#aplicado_solicitacao" ).html( ret.dados.aplicado_em );
            $("#motivo_solicitado"   ).html( ret.dados.motivo );
            //
            $("#gestor_solicitacao"  ).html( ret.dados.gestor );
            $("#decisao_solicitacao" ).html( ret.dados.decisao_obs );
            //$("#motivo_solicitado"   ).html( ret.dados.motivo );
            //
        },'json');
    }
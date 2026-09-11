
        let dataTable = "";

        $(document).ready(function() {
            constroiTabela();
        });

        function constroiTabela() {
            const alturaTotal = window.screen.height;
            const alturaViewport = window.innerHeight;
            var linhasPorPagina = alturaTotal > 900 ? 12 : 8;
            //
            // Definir quantidade de linhas por página dinamicamente
            if (alturaTotal == alturaViewport) {
                linhasPorPagina = (alturaTotal > 900) ? 16 : 12;
            }

            dataTable = new DataTable('#tblAtestados', {
                "processing": true,
                "serverSide": false,
                "order": [0, "asc"],
                "scrollX": true,
                "pageLength": linhasPorPagina, // Define a quantidade de linhas
                "ajax": {
                    "url": "includes/atestados_aj.php",
                    "type": "POST"
                },
                "columnDefs": [{
                    "targets": [0, 3, 4, 5, 6, 7],
                    "className": "text-center"
                }],
                language: {
                    url: '../includes/pt-BR.json',
                },
            });
        }

        function f_ver_colab(idColab) {
            location.href = "ficha_colab.php?id=" + idColab;
        }

        function f_visualizar(id) {

            $.post("../includes/rh_afastamento_aj2.php", {
                id: id
            }, function(retorno) {
                const dados = JSON.parse(retorno);

                $("#v_nome").html(dados.nome);
                $("#v_data").html(dados.data_inicio);
                $("#v_qtd").html(dados.dias_afastado);
                $("#v_retorno").html(dados.data_retorno);
                $("#v_tipo").html(dados.descricao);
                $("#v_emitido_por").html(dados.emitido_por);
                $("#v_cid").html(dados.cid);
                $("#v_quando").html(dados.criado_em + " por " + dados.login);
                $("#v_arquivo").html(dados.arquivo_link);
                // Abrir o modal de visualização
                visModal.show();
            });

        }

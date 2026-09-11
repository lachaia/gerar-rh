
        let dataTable = "";

        $(document).ready(function () {
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

            dataTable = new DataTable('#tblEquipe', {
                "processing": true,
                "serverSide": false,
                "order": [0, "asc"],
                "scrollX": true,
                "pageLength": linhasPorPagina, // Define a quantidade de linhas
                "ajax": {
                    "url": "includes/equipe_aj.php",
                    "type": "POST"
                },
                "columnDefs": [{
                    "targets": [4,5,6,7],
                    "className": "text-center"
                }],
                language: {
                    url: '../includes/pt-BR.json',
                },
            });
        }

        function f_visualizar(idColab) {
            location.href = "ficha_colab.php?id=" + idColab;
        }  

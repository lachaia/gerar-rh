<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Autocomplete na Modal</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- jQuery + jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- CSS para garantir visibilidade -->
    <style>
        .ui-autocomplete {
            z-index: 99999 !important;
            background: white;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="p-4">

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalExemplo">
        Abrir Modal
    </button>

    <!-- Modal -->
    <div class="modal fade" id="modalExemplo" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Autocomplete na Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="nmPessoa" class="form-label">Nome da Pessoa</label>
                    <input type="text" id="nmPessoa" class="form-control" placeholder="Digite...">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS do Autocomplete -->
    <script>
        $(function () {
            $("#nmPessoa").autocomplete({
                source: ["João", "Maria", "José", "Josiane", "Carlos"],
                minLength: 1,
                appendTo: "#modalExemplo" // garante que fique dentro da modal
            });
        });
    </script>
</body>
</html>

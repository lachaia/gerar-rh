<?PHP
//
//- rh_denuncia.php | FORMULÁRIO AVULSO | OUVIDORIA
// (C)haia, 23/07/2025

$idModulo = 15; // Acolhimento de Denúncias

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Ouvidoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgb(179, 179, 167);
        }

        .form-section {
            background-color: rgba(240, 240, 240, 0.6);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        #cardCab {
            background-color: rgba(117, 117, 117, 0.8);
            color: white;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="row">
        <div class="col-2"></div>
        <div class="col-8">
            <div class="container py-4">

                <div class="card shadow mb-2" id="cardCab">
                    <div class="d-flex align-items-center justify-content-center position-relative" style="min-height: 120px;">
                        <!-- Logo fixada à esquerda -->
                        <img src="./imagens/logo.png" alt="Logo" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); max-height: 80px;">

                        <!-- Título centralizado -->
                        <h2 class="m-4 text-center flex-grow-1">GERAR - OUVIDORIA</h2>
                    </div>
                </div>

                <form id="denunciaForm" method="post" action="rh_ouvir_aj.php">

                    <!-- Identificação -->
                    <div class="form-section">
                        <h5>1. Você deseja se identificar?</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="identificacao" id="anonimo" value="anonimo" checked onclick="mostrarIdentificacao(false)">
                            <label class="form-check-label" for="anonimo">Prefiro permanecer anônimo(a)</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="identificacao" id="identificado" value="identificado" onclick="mostrarIdentificacao(true)">
                            <label class="form-check-label" for="identificado">Desejo me identificar</label>
                        </div>
                        <div id="dadosIdentificacao" style="display: none;" class="mt-3">
                            <input type="text" class="form-control mb-2" name="nome" placeholder="Nome completo" onkeyup="atualizaContato()">
                            <input type="email" class="form-control mb-2" name="email" placeholder="E-mail (opcional)" onkeyup="atualizaContato()">
                            <input type="text" class="form-control mb-2" name="telefone" placeholder="Telefone (opcional)" onkeyup="atualizaContato()">
                        </div>
                    </div>

                    <!-- Tipo de assédio -->
                    <div class="form-section">
                        <h5>2. Tipo de assédio</h5>
                        <select class="form-select" name="tipoAssedio" required>
                            <option value="">Selecione...</option>
                            <option value="elogio">Elogio</option>
                            <option value="reclame">Reclamação</option>
                            <option value="denuncia">Denúncia</option>
                            <option value="moral">Assédio Moral</option>
                            <option value="sexual">Assédio Sexual</option>
                            <option value="outro">Outro</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="tipoOutro" placeholder="Especificar (se aplicável)">
                    </div>

                    <!-- Relato -->
                    <div class="form-section">
                        <h5>3. Descreva o ocorrido</h5>
                        <textarea id="relato" name="relato"></textarea>
                        <span class='ms-3' style="font-size: 12px;">informe o que aconteceu, onde e quando, com quem e como se sentiu? Informe apelidos, cargos ...</span>
                    </div>

                    <!-- Envolvidos -->
                    <div class="form-section">
                        <h5>4. Nome(s) do(s) envolvido(s)</h5>
                        <input type="text" class="form-control" name="envolvidos" placeholder="Informe nomes, apelidos ou cargos (opcional)">
                    </div>

                    <!-- Testemunhas -->
                    <div class="form-section">
                        <h5>5. Houve testemunhas?</h5>
                        <select class="form-select" name="testemunhas" onchange="mostrarTestemunhas(this.value)">
                            <option value="">Selecione...</option>
                            <option value="sim">Sim</option>
                            <option value="nao">Não</option>
                            <option value="nsei">Não sei</option>
                        </select>
                        <div id="campoTestemunhas" style="display: none;" class="mt-2">
                            <input type="text" class="form-control" name="nomesTestemunhas" placeholder="Nome(s) ou cargo(s) das testemunhas (opcional)">
                        </div>
                    </div>

                    <!-- Comunicação prévia -->
                    <div class="form-section">
                        <h5>6. Você já comunicou esse fato a alguém?</h5>
                        <select class="form-select" name="comunicado" onchange="mostrarComunicado(this.value)">
                            <option value="">Selecione...</option>
                            <option value="sim">Sim</option>
                            <option value="nao">Não</option>
                        </select>
                        <div id="campoComunicado" style="display: none;" class="mt-2">
                            <textarea class="form-control" name="respostaComunicado" rows="3" placeholder="Quem foi comunicado e qual foi a resposta..."></textarea>
                        </div>
                    </div>

                    <!-- Acompanhamento -->
                    <div class="form-section">
                        <h5>7. Deseja ser contatado(a) para acompanhamento?</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="acompanhamento" id="contatoSim" value="sim" onclick="requerContato(true)">
                            <label class="form-check-label" for="contatoSim">Sim, aceito contato para acompanhamento</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="acompanhamento" id="contatoNao" value="nao" checked onclick="requerContato(false)">
                            <label class="form-check-label" for="contatoNao">Não, prefiro apenas registrar a denúncia</label>
                        </div>
                        <div id="dadosContato" style="display: none;" class="mt-3">
                            <input type="text" class="form-control mb-2" name="nomeContato" id="nomeContato" placeholder="Nome (se ainda não preenchido)">
                            <input type="email" class="form-control mb-2" name="emailContato" id="emailContato" placeholder="E-mail">
                            <input type="text" class="form-control mb-2" name="telefoneContato" id="telefoneContato" placeholder="Telefone">
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-dark px-4 w-100" onClick='enviar()'>Enviar denúncia</button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    <!-- Dependências -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Lógica condicional JS -->
    <script>
        $(document).ready(function() {
            $('#relato').summernote({
                height: 250,
                placeholder: 'Descreva aqui o ocorrido com todos os detalhes relevantes...',
            });
        });

        function mostrarIdentificacao(show) {
            $('#dadosIdentificacao').toggle(show);
        }

        function mostrarTestemunhas(val) {
            $('#campoTestemunhas').toggle(val === 'sim');
        }

        function mostrarComunicado(val) {
            $('#campoComunicado').toggle(val === 'sim');
        }

        function requerContato(show) {
            $('#dadosContato').toggle(show);
        }

        function atualizaContato() {
            if ($('#identificado').is(':checked')) {
                $('#nomeContato').val($('input[name="nome"]').val());
                $('#emailContato').val($('input[name="email"]').val());
                $('#telefoneContato').val($('input[name="telefone"]').val());
            }
        }

        function enviar() {
            // Verifica tipo de assédio
            var tipo = $('select[name="tipoAssedio"]').val();
            if (!tipo) {
                alert("Por favor, selecione o tipo de assédio.");
                $('select[name="tipoAssedio"]').focus();
                return;
            }

            // Verifica o campo relato
            var relato = $('#relato').summernote('isEmpty') ? '' : $('#relato').summernote('code');
            if (relato.trim() === '' || $('<div>').html(relato).text().trim() === '') {
                alert("Por favor, descreva o ocorrido.");
                $('#relato').summernote('focus');
                return;
            }

            // Se for identificado, validar nome
            if ($('#identificado').is(':checked')) {
                var nome = $('input[name="nome"]').val().trim();
                if (!nome) {
                    alert("Por favor, preencha seu nome.");
                    $('input[name="nome"]').focus();
                    return;
                }
            }

            // Se acompanhamento = sim, validar email ou telefone
            if ($('#contatoSim').is(':checked')) {
                var email = $('#emailContato').val().trim();
                var telefone = $('#telefoneContato').val().trim();
                if (!email && !telefone) {
                    alert("Para acompanhamento, informe pelo menos um meio de contato (email ou telefone).");
                    $('#emailContato').focus();
                    return;
                }
            }

            // Tudo certo, envia
            $('#denunciaForm').submit();
        }
    </script>

</body>

</html>
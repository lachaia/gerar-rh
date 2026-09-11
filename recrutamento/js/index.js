//
// index.js | Rotinas do Dashboard | index.php
//  (C)haia, 2026-08-12
//


$(document).ready(function () {
    //
    atualiza_dash();
    $('#busca_vagas').on('input', filtrar_vagas);
    //
});

function filtrar_vagas() {
    const termo = $(this).val().trim().toLowerCase();

    document.querySelectorAll('.kb-card').forEach(function (card) {
        const busca = card.getAttribute('data-busca') || '';
        card.style.display = busca.includes(termo) ? '' : 'none';
    });

    document.querySelectorAll('.kb-coluna').forEach(function (coluna) {
        const contador = coluna.querySelector('.kb-coluna-contador');
        const visiveis = coluna.querySelectorAll('.kb-card:not([style*="display: none"])').length;
        if (contador) contador.textContent = visiveis;
    });
}

async function atualiza_dash(){
    const vagas_criticas = $('#vagas_criticas');
    const vagas_abertas = $('#vagas_abertas');
    const vagas_diretoria = $('#vagas_diretoria');
    const vagas_triagem = $('#vagas_triagem');
    //
    $.ajax({
        url: 'inc/vagas_dash_aj.php',
        type: 'POST',
        dataType: 'json',
        success: function (res) {
            vagas_criticas.html(res.vagas_criticas);
            vagas_abertas.html(res.vagas_abertas);
            vagas_diretoria.html(res.vagas_diretoria);
            vagas_triagem.html(res.vagas_triagem);
        }
    });
}


<?php

$modulo = "Desempenho";
include 'header.php';
?>
<main class="main">
    <!-- Desempenho -->
    <div class="big-card">
        <h4>Desempenho</h4>
        <p style="color:var(--muted)">Resumo das avaliações, metas e feedbacks. Integrável com módulo de avaliação da empresa.</p>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <div style="color:var(--muted)">Última avaliação</div>
                <div style="font-weight:800;font-size:1.25rem">Aprovado • 87%</div>
            </div>
            <div class="col-md-6 text-md-end">
                <button class="btn btn-outline-light">Ver histórico de avaliações</button>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
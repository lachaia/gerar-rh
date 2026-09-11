<?php
//
// - g_politica_priv.php  | POLÍTICA DE PRIVACIDADE DO SITE
// - (C) Chaia, 01/08/2023
//

session_start();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Termos de Uso" />
    <meta name="author" content="LAChaia" />
    <title>Gerar: Configurações</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <style>
        h1 {
            margin-top: 10px;
        }

        #corpo-do-texto {
            text-align: justify;
            padding: 50px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "includes/menu_superior.php"; ?>
    <div id="layoutSidenav">
        <?php include "includes/menu_lateral.html"; ?>
        <div id="layoutSidenav_content">
            <main class="container">
                <h1 class="text-center">TERMOS DE USO</h1>
                <!-- Aqui COMEÇA o conteúdo da página -->
                <div id="corpo-do-texto">
                    <h2>1. Termos</h2>
                    <p>Ao acessar ao site GERAR-TI, concorda em cumprir estes termos de serviço, todas as leis e regulamentos aplicáveis e concorda que é responsável pelo cumprimento de todas as leis locais aplicáveis.</p>
                    <p>Se você não concordar com algum desses termos, está proibido de usar ou acessar este site.
                        Os materiais contidos neste site são protegidos pelas leis de direitos autorais e marcas comerciais aplicáveis.</p>
                    <h2>2. Uso de Licença</h2>
                    <p>É concedida permissão para baixar temporariamente uma cópia dos materiais (informações ou software) no site GERAR-TI, apenas para visualização transitória pessoal e não comercial.
                        Esta é a concessão de uma licença, não uma transferência de título e, sob esta licença, você não pode:</p>
                    <ul>
                        <li>modificar ou copiar os materiais;</li>
                        <li>usar os materiais para qualquer finalidade comercial ou para exibição pública (comercial ou não comercial);</li>
                        <li>tentar descompilar ou fazer engenharia reversa de qualquer software contido no site GERAR-TI;</li>
                        <li>remover quaisquer direitos autorais ou outras notações de propriedade dos materiais; </li>
                        <li>ou transferir os materiais para outra pessoa ou 'espelhe' os materiais em qualquer outro servidor.</li>
                    </ul>
                    Esta licença será automaticamente rescindida se você violar alguma dessas restrições e poderá ser rescindida por GERAR-TI a qualquer momento. Ao encerrar a visualização desses materiais ou após o término desta licença, você deve apagar todos os materiais baixados em sua posse, seja em formato eletrônico ou impresso.
                    <h2>3. Isenção de responsabilidade</h2>
                    <p>Os materiais no site da GERAR-TI são fornecidos 'como estão'.
                        GERAR-TI não oferece garantias, expressas ou implícitas, e, por este meio, isenta e nega todas as outras garantias, incluindo, sem limitação, garantias implícitas ou condições de comercialização, adequação a um fim específico ou não violação de propriedade intelectual ou outra violação de direitos.</p>
                    <p>Além disso, o GERAR-TI não garante ou faz qualquer representação relativa à precisão, aos resultados prováveis ou à confiabilidade do uso dos materiais em seu site ou de outra forma relacionado a esses materiais ou em sites vinculados a este site.</p>
                    <h2>4. Limitações</h2>
                    <p>Em nenhum caso o GERAR-TI ou seus fornecedores serão responsáveis por quaisquer danos (incluindo, sem limitação, danos por perda de dados ou lucro ou devido a interrupção dos negócios) decorrentes do uso ou da incapacidade de usar os materiais em GERAR-TI, mesmo que GERAR-TI ou um representante autorizado da GERAR-TI tenha sido notificado oralmente ou por escrito da possibilidade de tais danos.</p>
                    <p>Como algumas jurisdições não permitem limitações em garantias implícitas, ou limitações de responsabilidade por danos conseqüentes ou incidentais, essas limitações podem não se aplicar a você.</p>
                    <h2>5. Precisão dos materiais</h2>
                    <p>Os materiais exibidos no site da GERAR-TI podem incluir erros técnicos, tipográficos ou fotográficos.</p>
                    <p>GERAR-TI não garante que qualquer material em seu site seja preciso, completo ou atual. </p>
                    <p>GERAR-TI pode fazer alterações nos materiais contidos em seu site a qualquer momento, sem aviso prévio.</p>
                    <p>No entanto, GERAR-TI não se compromete a atualizar os materiais.</p>
                    <h2>6. Links</h2>
                    <p>O GERAR-TI não analisou todos os sites vinculados ao seu site e não é responsável pelo conteúdo de nenhum site vinculado.</p>
                    <p>A inclusão de qualquer link não implica endosso por GERAR-TI do site. </p>
                    <p>O uso de qualquer site vinculado é por conta e risco do usuário.</p>
                    <h2>Modificações</h2>
                    <p>O GERAR-TI pode revisar estes termos de serviço do site a qualquer momento, sem aviso prévio.</p>
                    <p>Ao usar este site, você concorda em ficar vinculado à versão atual desses termos de serviço.</p>
                    <h2>Lei aplicável</h2>
                    <p>Estes termos e condições são regidos e interpretados de acordo com as leis do GERAR-TI e você se submete irrevogavelmente à jurisdição exclusiva dos tribunais naquele estado ou localidade.</p>
                </div>

                <!-- Aqui Termina o conteúdo da página -->
            </main>
            <?php include "includes/footer.html"; ?>
        </div>
    </div>
    <script src="js/scripts.js"></script>
</body>

</html>
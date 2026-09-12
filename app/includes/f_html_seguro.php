<?php
//
// f_html_seguro.php | Sanitiza HTML rico (vindo do editor Summernote) antes de
// exibir na tela. Usa o HTMLPurifier (já vendorizado via Composer, dependência
// transitiva de outro pacote) em vez de confiar no conteúdo salvo.
//
// Substitui a função conteudo_rico() que existia duplicada (e quebrada) em
// vagas/vaga_perfil.php, recrutamento/vaga_view.php e recrutamento/vaga_edit.php:
// a versão antiga fazia `strip_tags($valor) === $valor ? escapa : mostra cru`,
// ou seja, qualquer valor que tivesse UMA tag HTML (mesmo <script>) era
// impresso sem escapar nada.
//

require_once __DIR__ . '/../vendor/autoload.php';

function conteudo_rico(?string $valor): string
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        return '';
    }

    // Texto puro (sem nenhuma tag) — provavelmente dado legado de antes do
    // Summernote. Escapa e converte quebra de linha manualmente, como a
    // função antiga já fazia para esse caso.
    if (strip_tags($valor) === $valor) {
        return nl2br(htmlspecialchars($valor));
    }

    // Tem alguma tag HTML — sanitiza de verdade em vez de confiar no
    // conteúdo salvo (o candidato/solicitante controla esse texto).
    static $purifier = null;
    if ($purifier === null) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,b,i,u,s,strong,em,ul,ol,li,a[href|target|rel],span[style],div[style],h1,h2,h3,h4,h5,h6,blockquote,table,thead,tbody,tr,td,th');
        $config->set('CSS.AllowedProperties', 'color,background-color,text-align,font-weight,font-style,text-decoration');
        $config->set('HTML.TargetBlank', true);
        $config->set('Cache.SerializerPath', sys_get_temp_dir());
        $purifier = new HTMLPurifier($config);
    }

    return $purifier->purify($valor);
}

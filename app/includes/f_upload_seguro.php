<?php
//
// f_upload_seguro.php | Valida extensão + MIME real de um upload antes de
// aceitar o arquivo, para impedir upload de scripts (.php, .phtml, etc.)
// disfarçados de documento/imagem.
//

const UPLOAD_MIME_POR_EXTENSAO = [
    'pdf'  => ['application/pdf'],
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'gif'  => ['image/gif'],
    'bmp'  => ['image/bmp', 'image/x-ms-bmp'],
    'webp' => ['image/webp'],
    'doc'  => ['application/msword'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
    'xls'  => ['application/vnd.ms-excel'],
    'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
    'ppt'  => ['application/vnd.ms-powerpoint'],
    'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
    'msg'  => ['application/vnd.ms-outlook', 'application/CDFV2', 'application/octet-stream'],
    'eml'  => ['message/rfc822', 'text/plain'],
];

/**
 * Valida um arquivo de $_FILES contra uma whitelist de extensões, checando
 * também o MIME real do conteúdo (não confia na extensão nem no
 * Content-Type enviado pelo navegador).
 *
 * @param array $arquivoPost   Ex.: $_FILES['foto']
 * @param array $extensoesPermitidas Ex.: ['jpg','jpeg','png']
 * @return true|string true se válido, ou uma mensagem de erro (string) se inválido
 */
function upload_seguro_validar(array $arquivoPost, array $extensoesPermitidas)
{
    if (empty($arquivoPost['tmp_name']) || !is_uploaded_file($arquivoPost['tmp_name'])) {
        return "Upload inválido.";
    }

    $extensao = strtolower(pathinfo($arquivoPost['name'] ?? '', PATHINFO_EXTENSION));

    if ($extensao === '' || !in_array($extensao, $extensoesPermitidas, true)) {
        return "Tipo de arquivo não permitido" . ($extensao !== '' ? " (.$extensao)" : "") . ".";
    }

    $mimesEsperados = UPLOAD_MIME_POR_EXTENSAO[$extensao] ?? null;
    if ($mimesEsperados) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo ? finfo_file($finfo, $arquivoPost['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if ($mimeReal && !in_array($mimeReal, $mimesEsperados, true)) {
            return "O conteúdo do arquivo não corresponde à extensão informada (.$extensao).";
        }
    }

    return true;
}

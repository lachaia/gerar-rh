async function ocr(origem) {
    const campo_ocr = $("#inc_ocr");
    const url = 'https://ti.gerar.org.br/ocr_gerar.php'; // Ajuste o URL conforme necessário
    //const url = 'ocr_gerar.php'; // Ajuste o URL conforme necessário

    try {
        // Cria um objeto FormData para enviar o arquivo
        const formData = new FormData();
        formData.append('arquivo', origem);

        // Configura a requisição fetch com método POST
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });

        // Verifica se a requisição foi bem-sucedida
        if (!response.ok) {
            throw new Error('Erro na requisição fetch: ' + response.statusText);
        }

        // Retorna o texto extraído ou processa conforme necessário
        const textoExtraido = await response.text();
        campo_ocr.val(textoExtraido);
        return true;
    } catch (error) {
        console.error('Erro durante a requisição fetch:', error);
        alert('Ocorreu um erro ao realizar a requisição OCR: ' + error.message);
    }
}

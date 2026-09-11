function validarCNPJ(cnpj) {
    if (!cnpj) return false;

    // 1. Limpeza: Agora mantemos letras e números, removendo apenas pontuação
    cnpj = cnpj.toString().toUpperCase().replace(/[^A-Z0-9]/g, '');

    if (cnpj.length !== 14) return false;

    // 2. Elimina sequências repetidas conhecidas (apenas numéricas)
    if (/^(\d)\1{13}$/.test(cnpj)) return false;

    // Função interna para converter char alfanumérico em valor numérico (Regra Receita)
    const obterValor = (char) => {
        const codigo = char.charCodeAt(0);
        return codigo - 48; // Números continuam 0-9, Letras viram 17-42
    };

    // 3. Validação dos Dígitos Verificadores
    const validarDV = (tamanhoBase) => {
        let soma = 0;
        let pos = tamanhoBase - 7;
        for (let i = tamanhoBase; i >= 1; i--) {
            let valor = obterValor(cnpj.charAt(tamanhoBase - i));
            soma += valor * pos--;
            if (pos < 2) pos = 9;
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        return resultado == cnpj.charAt(tamanhoBase);
    };

    // Valida o primeiro e o segundo DV
    if (!validarDV(12)) return false;
    if (!validarDV(13)) return false;

    return true;
}
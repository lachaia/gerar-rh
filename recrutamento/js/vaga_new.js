$(document).ready(function () {

    controlarEnderecoTrabalho();

    $('#regime_trabalho').on('change', function () {
        controlarEnderecoTrabalho();
    });

    $('#experiencia').summernote({
      placeholder: 'Descreve a experiência mínima (se houver) necessária para essa vaga...',
      tabsize: 2,
      height: 200, // Altura do editor em pixels
      lang: 'pt-BR', // Configura o idioma para português (opcional)
      toolbar: [
        // [nome_do_grupo, [lista de botões]]
        ['style', ['bold', 'italic', 'underline', 'clear']], // Edição de fonte
        ['color', ['forecolor', 'backcolor']],
        ['para', ['ul', 'ol', 'paragraph']]                 // Listas e parágrafo
      ]      
    });
    $('#atividades_vaga').summernote({
      placeholder: 'Descreva pelo menos 5 atividades que o colaborador deverá realizar...',
      tabsize: 2,
      height: 200, // Altura do editor em pixels
      lang: 'pt-BR', // Configura o idioma para português (opcional)
      toolbar: [
        // [nome_do_grupo, [lista de botões]]
        ['style', ['bold', 'italic', 'underline', 'clear']], // Edição de fonte
        ['color', ['forecolor', 'backcolor']],
        ['para', ['ul', 'ol', 'paragraph']]                 // Listas e parágrafo
      ]      
    });
    $('#observacoes').summernote({
      placeholder: 'Observações para o Recrutador...',
      tabsize: 2,
      height: 140, // Altura do editor em pixels
      lang: 'pt-BR', // Configura o idioma para português (opcional)
      toolbar: [
        // [nome_do_grupo, [lista de botões]]
        ['style', ['bold', 'italic', 'underline', 'clear']], // Edição de fonte
        ['color', ['forecolor', 'backcolor']],
        ['para', ['ul', 'ol', 'paragraph']]                 // Listas e parágrafo
      ]
    });

});


function controlarEnderecoTrabalho() {

    const regime = $('#regime_trabalho').val();

    const mostrarEndereco =
        regime === '100% Presencial' ||
        regime === 'Híbrido';

    const bloco = $('#bloco_endereco_trabalho');

    if (mostrarEndereco) {

        bloco.removeClass('d-none');

        // Campos obrigatórios quando houver endereço
        $('#cep').prop('required', true);
        $('#endereco').prop('required', true);
        $('#numero').prop('required', true);
        $('#bairro').prop('required', true);
        $('#cidade').prop('required', true);
        $('#uf').prop('required', true);

    } else {

        bloco.addClass('d-none');

        // Remove obrigatoriedade quando for Home Office
        $('#cep').prop('required', false);
        $('#endereco').prop('required', false);
        $('#numero').prop('required', false);
        $('#bairro').prop('required', false);
        $('#cidade').prop('required', false);
        $('#uf').prop('required', false);

        // Limpa os campos
        $('#cep').val('');
        $('#endereco').val('');
        $('#numero').val('');
        $('#bairro').val('');
        $('#complemento').val('');
        $('#cidade').val('');
        $('#uf').val('');
    }
}

document.addEventListener('DOMContentLoaded', function () {

  // 2. Lógica do Tipo de Vaga (Estágio)
  const tipoVaga = document.getElementById('tipo_vaga');
  const blocoEstagio = document.getElementById('bloco_estagio');

  tipoVaga.addEventListener('change', function () {
    blocoEstagio.classList.toggle('d-none', this.value !== 'Estágio'); // 4 = Estagiário
  });

  // 3. Lógica do Formação Superior
  const formacao = document.getElementById('formacao_necessaria');
  const boxCursoSuperior = document.getElementById('box_curso_superior');

  formacao.addEventListener('change', function () {
    // Array com as opções que EXIGEM o preenchimento do curso
    const formacoesComCurso = [
      'Ensino Superior Cursando',
      'Ensino Superior Completo',
      'Pós-Graduação em Andamento',
      'Pós-Graduação Completa'
    ];

    if (formacoesComCurso.includes(this.value)) {
      boxCursoSuperior.classList.remove('d-none');
    } else {
      boxCursoSuperior.classList.add('d-none');
      // Limpa o campo caso o usuário mude para Ensino Médio
      const inputCurso = boxCursoSuperior.querySelector('input');
      if (inputCurso) inputCurso.value = '';
    }
  });

  // 4. Lógica da Pergunta Chave
  const incluirPergunta = document.getElementById('incluir_pergunta');
  const boxPerguntaChave = document.getElementById('box_pergunta_chave');

  incluirPergunta.addEventListener('change', function () {
    boxPerguntaChave.classList.toggle('d-none', this.value !== '2'); // 2 = Sim
  });

  // 5. Trava de Seleção Máxima de 3 Competências (Comportamentais e Técnicas)
  function limitarCheckboxes(className, maxAllowed) {
    const checkboxes = document.querySelectorAll('.' + className);
    checkboxes.forEach(cb => {
      cb.addEventListener('change', function () {
        const checkedCount = document.querySelectorAll('.' + className + ':checked').length;
        if (checkedCount > maxAllowed) {
          this.checked = false;
          alert(`Você só pode selecionar no máximo ${maxAllowed} opções.`);
        }
      });
    });
  }

  limitarCheckboxes('comp-comportamental-check', 3);
  limitarCheckboxes('comp-tecnica-check', 3);

});

function selecionou_subsede(o) {
  const subsede_id = o.value;
  $.post("inc/vaga_new_polos.php", { subsede_id: subsede_id }, function (res) {
    $("#seletor_polos").html(res);
  });
}

function enviar_solicitacao( origem = null ) {
  const form = document.getElementById('formSolicitacaoVaga');

  // 1. Validação dos Selects que possuem valor "0" como opção padrão/inválida
  const motivacao = form.querySelector('[name="motivacao_id"]');
  if (!motivacao || motivacao.value === '0') {
    alert('Por favor, selecione a Motivação da Solicitação.');
    motivacao.focus();
    return;
  }

  const subsede = form.querySelector('[name="subsede_id"]');
  if (!subsede || subsede.value === '0') {
    alert('Por favor, selecione a Subsede.');
    subsede.focus();
    return;
  }

  const polo = form.querySelector('[name="polo_id"]');
  if (polo && polo.value === '') {
    alert('Por favor, selecione o Polo.');
    polo.focus();
    return;
  }

  const orgao = document.getElementById('idOrgao');
  if (!orgao || orgao.value === '0') {
    alert('Por favor, selecione o Órgão de Lotação.');
    orgao.focus();
    return;
  }

  const cargo = document.getElementById('idCargo');
  if (!cargo || cargo.value === '0') {
    alert('Por favor, selecione o Cargo.');
    cargo.focus();
    return;
  }

  // 2. Validação nativa do HTML5 (campos com required)
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  // 3. Validação condicional: Curso de Estágio 1 (se Tipo de Vaga == 4)
  const tipoVaga = document.getElementById('tipo_vaga').value;
  if (tipoVaga === '4') {
    const estagioCurso1 = form.querySelector('[name="estagio_curso_1"]');
    if (!estagioCurso1.value.trim()) {
      alert('Por favor, informe o Curso Estágio 1.');
      estagioCurso1.focus();
      return;
    }
  }

  // 4. Validação condicional: Nome do Curso Superior/Pós (se Formação for 2, 3, 4 ou 5)
  // Validação condicional: Nome do Curso Superior/Pós
  const formacao = document.getElementById('formacao_necessaria').value;
  const formacoesComCurso = [
    'Ensino Superior Cursando',
    'Ensino Superior Completo',
    'Pós-Graduação em Andamento',
    'Pós-Graduação Completa'
  ];

  if (formacoesComCurso.includes(formacao)) {
    const cursoSuperior = form.querySelector('[name="curso_superior_nome"]');
    if (!cursoSuperior || !cursoSuperior.value.trim()) {
      alert('Por favor, informe o Nome do Curso Superior/Pós.');
      if (cursoSuperior) cursoSuperior.focus();
      return;
    }
  }

  // 5. Validação condicional: Pergunta Chave (se incluir_pergunta == 2)
  const incluirPergunta = document.getElementById('incluir_pergunta').value;
  if (incluirPergunta === '2') {
    const textoPergunta = form.querySelector('[name="pergunta_chave_texto"]');
    if (!textoPergunta.value.trim()) {
      alert('Por favor, digite a Pergunta Chave.');
      textoPergunta.focus();
      return;
    }
  }

  // 6. Preparação dos dados para envio via FormData (necessário para upload de arquivos)
  const formData = new FormData(form);

  // 7. Envio via AJAX utilizando jQuery $.ajax (já em uso no seu projeto)
  $.ajax({
    url: 'inc/vaga_new_inc_aj.php',
    type: 'POST',
    data: formData,
    processData: false, // Necessário para enviar FormData
    contentType: false, // Necessário para multipart/form-data
    dataType: 'json',
    beforeSend: function () {
      // Bloqueia botão ou exibe indicação de carregamento se desejar
      $('button[onclick="enviar_solicitacao()"]').prop('disabled', true);
    },
    success: function (res) {
      if (res.status === true || res.success) {
        const config = window.solicitacaoVagaConfig || {};
        alert(config.successMessage || res.mensagem || 'Solicitação de vaga criada com sucesso!');
        if( origem == 'solicitacao'){
            window.location.href = 'logout_solicitacao.php';
        } else{
            window.location.href = config.successRedirect || 'index.php';
        }
        
      } else {
        alert('Erro: ' + (res.mensagem || res.erro || 'Falha ao salvar a solicitação.'));
        $('button[onclick="enviar_solicitacao()"]').prop('disabled', false);
      }
    },
    error: function (xhr, status, error) {
      console.error(error);
      alert('Ocorreu um erro na comunicação com o servidor. Tente novamente.');
      $('button[onclick="enviar_solicitacao()"]').prop('disabled', false);
    }
  });
}

function busca_cep( o ) {
    var texto = o.value;
    texto = texto.replace(/[^\d]+/g, '');
    if (texto == '') return false;
    if (texto.length == 8) {
        //- busca o cep
        const url = "https://viacep.com.br/ws/" + texto + "/json/";
        $.get(url, function (data) {
            console.log(data);
            //
            document.getElementById(`cep`).value = texto;
            document.getElementById(`endereco`).value = data.logradouro;
            document.getElementById(`bairro`).value = data.bairro;
            document.getElementById(`cidade`).value = data.localidade;
            document.getElementById(`uf`).value = data.uf;
            
            document.getElementById('numero').focus();
            //
        });
        //
        o.value = texto.replace(/(\d{5})(\d{3})/, "$1-$2");
        return true;
    }
    alert("Número de dígitos inválido!");
    o.value = '';
    o.focus();
    return false;
}

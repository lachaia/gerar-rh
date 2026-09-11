<style>
  ._x42 {
    font-size: 12px;
    font-weight: 700;
  }

  #notificacoesOffcanvas {
    top: 56px !important;
  }

  .offcanvas-body {
    overflow-y: auto;
    max-height: calc(100vh - 56px);
  }

  .notas-slide {
    position: fixed;
    top: 56px;
    /* ou ajuste conforme altura do seu navbar */
    right: 0;
    width: 400px;
    height: calc(100% - 56px);
    background: #cbc7c7;
    background-image: url('imagens/fundo_cinza.jpg');
    z-index: 1050;
    transition: transform 0.3s ease-in-out;
    transform: translateX(100%);
  }

  .notas-slide.show {
    transform: translateX(0);
  }
</style>

<?php
if ($_SESSION['idGrupo'] != 1 && $_SESSION['idGrupo'] != 3 && $_SESSION['idGrupo'] != 9 && $_SESSION['idGrupo'] != 7) {
  // 1 - Usuários do RH
  // 3 - Psicólogos do RH
  // 9 - SuperUsuário
  header('Location: proibido.php');
  exit();
}
?>

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark" >
  <!-- Navbar Brand-->
  <a class="navbar-brand ps-3" href="index.php">
    <img src="imagens/logo_resized.png" alt="Logo_GERAR" style="width:50px;">
    RH
  </a>
  <!-- Sidebar Toggle-->
  <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
  <!-- Navbar Search-->

  <nav class="navbar-nav navbar-expand-sm">

    <div class="container-fluid">
      <!-- Links -->
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="rh_pessoas.php">Pessoas</a></li>
        <li class="nav-item"><a class="nav-link" href="rh_colab.php">Colaboradores</a></li>
        <li class="nav-item"><a class="nav-link" href="rh_rescisao.php">Rescisão</a></li>
        <li class="nav-item"><a class="nav-link" href="rh_ferias.php">Férias</a></li>
        <li class="nav-item"><a class="nav-link" href="rh_afastamentos.php">Afastamentos</a></li>
        <li class="nav-item"><a class="nav-link" href="rh_termos.php">Termos</a></li>
        <?php if ($_SESSION['idGrupo'] == 3 || $_SESSION['idGrupo'] == 9) { ?>
          <li class="nav-item"><a class="nav-link" href="rh_ouvidoria.php">Ouvidoria</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="rh_equipamentos.php">Equipamentos</a></li>
        <li class="nav-item"><a class="nav-link" href="rh_avaliacoes.php">Desempenho</a></li>
        <li class="nav-item"><a class="nav-link" href="rh_ctr_exp.php">Ctr.Experiência</a></li>
      </ul>
    </div>

  </nav>


  <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0" id="formBusca">
    <div class="d-flex align-items-center gap-2">

      <!-- Ícone de sininho com badge -->
      <div class="position-relative me-4" id='fx_notas' onclick="fx_mostra_notas()" style="cursor: pointer;">
        <i class="fas fa-bell fa-lg text-secondary"></i>
        <?= fx_notificacoes() ?>
      </div>

      <!-- Campo de busca -->
      <div class="input-group">
        <input class="form-control" type="text" placeholder="Procure por..." aria-label="Procure por..."
          aria-describedby="btnNavbarSearch" onkeyup="__carregar_nomes(this.value)" id="campoBusca" />
        <button class="btn btn-primary" id="btnNavbarSearch" type="button">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </div>

    <span id="resultado_pesquisa"></span>
  </form>


  <!-- Navbar-->
  <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
    <li class="nav-item dropdown">
      <a class="nav-link" id="navbarDropdown1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i
          class="fas fa-user fa-cog"></i></a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="rh_config.php">Configurações</a></li>
        <?php
        if ($_SESSION['idGrupo'] <> 4) {
          echo '<li><a class="dropdown-item" href="rh_logs.php">Logs</a></li>';
          echo '<li><a class="dropdown-item" href="rh_logins.php">Logins</a></li>';
          echo '<li><a class="dropdown-item" href="rh_phpinfo.php">phpInfo</a></li>';
        }
        if ($_SESSION['idGrupo'] == 1 || $_SESSION['idGrupo'] == 7 || $_SESSION['idGrupo'] == 9) {
          echo '<li><a class="dropdown-item" href="rh_usuarios.php">Usuários</a></li>';
          echo '<li><a class="dropdown-item" href="rh_cfg_notificacao.php">Notificações</a></li>';
        }
        ?>
      </ul>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
        aria-expanded="false">
        <span id="idFotoPerfil">
          <img src="<?php echo 'fotos/' . $_SESSION['perfil'] ?>" alt="Foto do Usuário"
            class="rounded-circle user-photo" width="30" height="30">
        </span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="#" onclick="modalPerfil.show()">Meu Perfil</a></li>
        <li><a class="dropdown-item" href="rh_logs.php?status=pessoal">Meus Logs</a></li>
        <li><a class="dropdown-item" href="rh_logins.php?status=pessoal">Meus Logins</a></li>
        <li><a class="dropdown-item" href="rh_altsenha.php">Alterar Senha</a></li>
        <li>
          <hr class="dropdown-divider" />
        </li>
        <li><a class="dropdown-item" href="logout.php">Sair</a></li>
      </ul>
    </li>
    <li class="nav-item"><a class="nav-link" href="logout.php" id="sidebarToggleTop" role="button"
        aria-expanded="false"><i class="fas fa-sign-out-alt"></i></a>
    </li>
  </ul>
</nav>


<!-- The Modal PERFIL -->
<div class="modal fade" id="modal-perfil">
  <div class="modal-dialog modal-dialog-scrollable"">
    <div class=" modal-content">
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Meu Perfil</h4>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <!-- Modal body -->
    <div class="modal-body container" id="_corpo_principal">
      <span id="msg"></span>
      <div class="row text-center">
        <span id="imagem-preview">
          <img src="<?php echo 'fotos/' . $_SESSION['perfil'] ?>" class="rounded-circle img-fluid" alt="Foto do Perfil"
            width="200" height="200">
        </span>
        <p>Escolha uma foto para o perfil, <br>preferencialmente quadrada e tipo PNG</p>
      </div>
      <div class="row">
        <form id="modalPerfilForm" name="modalPerfilForm" enctype="multipart/form-data>">
          <div class="row">
            <div class="col-sm-12">
              <input type="file" id="idFilePerfil" name="idFilePerfil" accept="image/*" class="form-control"
                onchange="validarPerfil()">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12 mt-2 mb-2">
              <label for="chaveApp" class="_x42">chaveApp</label>
              <input type="text" name="chaveApp" id="chaveApp" class="form-control text-center small-label"
                value="<?php echo $_SESSION['chaveApp']; ?>">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <button id="_assinatura_email" class="btn btn-outline-success btn-block w-100">Editar Assinatura de
                e-Mail</button>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <button id="upload-button-perfil" class="btn btn-outline-primary btn-block w-100">Enviar</button>
            </div>
          </div>

        </form>
      </div>
      <div class="row">
        <span id="msgModalPerfil" class="text-center p-1"></span>
      </div>
    </div>
  </div>
</div>
</div>

<script>
  const modalPerfil = new bootstrap.Modal(document.getElementById("modal-perfil"));
  const upload_button_perfil = document.getElementById("upload-button-perfil");
  const perfil_barra_progresso = document.getElementById("perfil_barra_progresso");
  const percentual_barra_progresso = document.getElementById("percentual_barra_progresso");
  const modalPerfilForm = document.getElementById("modalPerfilForm");
  const idFilePerfil = document.getElementById("idFilePerfil");

  _assinatura_email.addEventListener("click", function(e) {
    event.preventDefault();
    modalPerfil.hide();
    location.href = "rh_config.php";
  });

  function validarPerfil() {
    var arquivo = document.querySelector("#idFilePerfil");
    var valorArquivo = arquivo.value;
    const extensaoPermitida = /(\.jpg|\.jpeg|\.png)$/i;
    if (!extensaoPermitida.exec(valorArquivo)) {
      document.getElementById("msg").innerHTML = "<p style='color: red'>Erro: Nessário selecionar uma imagem JPG ou PNG</p>";
      arquivo.value = "";
      document.getElementById("imagem-preview").innerHTML = "<img src='<?php echo 'fotos/' . $_SESSION['perfil'] ?>' class='rounded-circle img-fluid' alt='Foto do Perfil' width='200' height='200'>";
      return;
    } else {
      document.getElementById("msg").innerHTML = "<p></p>";
      previewImagem(arquivo);
    }
  }

  function previewImagem(arquivo) {
    if ((arquivo.files) && (arquivo.files[0])) {
      // lê o arquivo
      var reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById("imagem-preview").innerHTML = "<img src='" + e.target.result + "' class='rounded-circle img-fluid' alt='Foto do Perfil' width='200' height='200'>";
      }
    }
    //- retorna os dados no formato blob como uma url de dados - blob representa um arquivo
    reader.readAsDataURL(arquivo.files[0]);
  }

  upload_button_perfil.addEventListener("click", function(event) {
    event.preventDefault();
    //- recebo os dados do formulário

    var chaveApp = $("#chaveApp").val();
    var foto = $("#idFilePerfil")[0].files[0]; // Obtenha o arquivo de imagem selecionado
    var dadosFormPerfil = new FormData();
    //
    dadosFormPerfil.append("foto", foto);
    dadosFormPerfil.append("chaveApp", chaveApp);

    $.ajax({
      url: "includes/rh_perfil_aj.php",
      type: "POST",
      data: dadosFormPerfil,
      processData: false, // Não processar os dados
      contentType: false, // Não configurar automaticamente o cabeçalho Content-Type
      dataType: "json", // Esperamos um JSON como resposta do servidor
      success: function(retorno) {
        // Verifica se o retorno contém a chave "htmlFoto"
        const fueto = "<img src='fotos/" + retorno.htmlFoto + "' class='rounded-circle img-fluid' alt='Foto do Perfil' width='30' height='0'>";
        $("#idFotoPerfil").html(fueto);
        $("#msgModalPerfil").html(retorno.msg);
        setTimeout(function() {
          $("#modalPerfilForm")[0].reset();
          $("#msgModalPerfil").html("");
          modalPerfil.hide();
        }, 2000);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("Erro na requisição AJAX:", textStatus, errorThrown);
      }
    });

  });


  async function __carregar_nomes(valor) {
    if (valor.length >= 3) {
      const dados = await fetch('./includes/pesquisa_jovens.php?texto=' + valor);
      const resposta = await dados.json();
      console.log(resposta);
      var resultado = "<ul class='list-group position-fixed'>";

      if (resposta['status']) {
        for (i = 0; i < resposta['dados'].length; i++) {
          if (resposta['dados'][i].tipo == 'jovem') {
            resultado += "<li class='list-group-item list-group-item-action'>" +
              "<a class='aLinkBusca' href='rh_ficha.php?id=" + resposta['dados'][i].id + "'>" + resposta['dados'][i].tipo + " | " + resposta['dados'][i].nome + " (" + resposta['dados'][i].doc + ")</a></li>";
          } else {
            resultado += "<li class='list-group-item list-group-item-action'>" +
              "<a class='aLinkBusca' href='rh_ficha_empresa.php?id=" + resposta['dados'][i].id + "'>" + resposta['dados'][i].tipo + " | " + resposta['dados'][i].nome + " (" + resposta['dados'][i].doc + ")</a></li>";
          }

        }
      } else {
        resultado += "<li class='list-group-item disabled'>" + resposta['msg'] + "</li>";
      }

      resultado += "</ul>";
      document.getElementById('resultado_pesquisa').innerHTML = resultado;
    }
  }

  const _fechar = document.getElementById("campoBusca");
  document.addEventListener('click', function(event) {
    const validar_clique = _fechar.contains(event.target);
    if (!validar_clique) {
      document.getElementById('resultado_pesquisa').innerHTML = "";
    }
  });


  function fx_mostra_notas() {
    let painel = $('#fx_notas_painel');

    if (painel.hasClass('show')) {
      painel.removeClass('show');
      setTimeout(() => painel.addClass('d-none'), 300);
    } else {
      painel.removeClass('d-none');
      setTimeout(() => painel.addClass('show'), 10);
    }

    $.get("includes/rh_notificacoes_aj.php", function(listaJson) {
      let lista = JSON.parse(listaJson);
      $('#fx_notas_conteudo').html(lista.join(''));

      // Adiciona o comportamento aos checkboxes
      $('.fx-checkbox-nota').on('change', function() {
        let $div = $(this).closest('.alert');
        let id = $div.data('id');

        if (this.checked) {
          $.post("includes/rh_notificacoes_aj1.php", {
            id
          }, function(res) {
            if (res.sucesso) {
              $div.fadeOut(300, () => $div.remove());
            } else {
              alert("Erro ao marcar como lida");
            }
          }, 'json');
        }
      });
    });
  }


  function fecharNotas() {
    $('#fx_notas_painel').addClass('d-none');
    //window.location.reload();
  }
</script>

<div id="fx_notas_painel" class="notas-slide shadow d-none">
  <div class="p-3 border-bottom fw-bold d-flex justify-content-between align-items-center">
    <span class="text-light">Notificações</span>
    <button type="button" class="btn-close" aria-label="Fechar" onclick="fecharNotas()"></button>
  </div>

  <div id="fx_notas_conteudo" class="p-3">
    <!-- Aqui entram as notificações via JS/PHP -->
  </div>
</div>

<?php


function fx_notificacoes()
{
  $idUsuario = $_SESSION['idUsuario'];
  //
  include "includes/conexao_gerar.php";
  $sql = "SELECT count(*) as qtd 
            FROM rh_notificacoes N
            INNER JOIN rh_notificacoes_tipo T on T.idTipo = N.idTipo
            WHERE idUsuario = $idUsuario AND lido_em is null";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
  $n = $dados['qtd'];
  //
  if ($n > 0) {
    echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
              onclick="fx_mostra_notas()">' .
      $n . '<span class="visually-hidden">notificações não lidas</span>
          </span>';
  } else {
    echo "";
  }
}

$(document).ready(function () {

    let idGestor = $("#idGestor").val();
    let ativos = $("#divQtdAtivos");
    let inativos = $("#divQtdDesligados");
    let afastados = $("#divQtdAfastados");
    let ferias = $("#total_colab_ferias");
    //
    $.post("includes/index_aj1.php", { idGestor: idGestor }, function ( res ){
        let dados = JSON.parse(res);
        ativos.html( dados.qtdAtivos )
        inativos.html( dados.qtdInativos )
        afastados.html( dados.qtdAfastados )
        ferias.html( dados.qtdFerias )
    });

});

    
    $(function(){

        function showSection(id){
        $('.section').removeClass('active');
        $('#' + id).addClass('active');
        // update URL hash without scrolling
        history.replaceState(null, '', '#'+id);
      }

      // open initial section from hash if exists
      const hash = window.location.hash.replace('#','');
      if(hash && $('#' + hash).length){
        $('.nav-btn').removeClass('active');
        $('.nav-btn[data-target="'+hash+'"]').addClass('active');
        showSection(hash);
      }

      // Example action hooks
      $('#btnOpenAfastamento').on('click', function(){
        $('.nav-btn').removeClass('active');
        $('.nav-btn[data-target="afastamentos"]').addClass('active');
        showSection('afastamentos');
      });

      // keyboard shortcut: press "f" to go to Férias
      $(document).on('keydown', function(e){
        if(e.key === 'f' || e.key === 'F'){
          $('.nav-btn[data-target="ferias"]').click();
        }
      });

      // small UI nicety: animate the cards on load
      $('.big-card').css({opacity:0, transform:'translateY(8px)'}).each(function(i){
        $(this).delay(80*i).animate({opacity:1, top:0}, 300).css('transform','none');
      });

    });


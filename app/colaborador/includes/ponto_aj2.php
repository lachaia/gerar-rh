<?PHP
//
//- ponto_aj2.php | (C)haia, 08/12/2025 | Formulário Nova Batida
//

session_start();

$data = $_POST['dia'];

?>

      <!-- Header -->
    <h5 class="text-center h5" id="">Nova Batida</h5>

      <!-- Body -->
      <div class="">
        <form id="formBatida">
          <input type="hidden" id="batidaId" name="batidaId" value='0'>
          <input type="hidden" id="tipoSolicitacao" name="tipoSolicitacao" value='INC'>

          <div class="mb-3">
            <label for="dataBatida" class="form-label">Data</label>
            <input 
                type="date" 
                id="dataBatida" name="dataBatida" 
                class="form-control text-center" 
                style='font-weight: bold; background-color: #e9ecef; color: #495057;'
                readonly 
                value='<?= $data ?>'>
          </div>

          <div class="mb-3">
            <label for="horaBatida" class="form-label">Hora</label>
            <input type="time" id="horaBatida" name="horaBatida" class="form-control text-center" required>
          </div>

          <div class="mb-3">
            <label for="motivo" class="form-label">Motivo</label>
            <textarea id="motivo" name="motivo" rows="2" class="form-control" placeholder="Descreva o motivo do ajuste..." required></textarea>
          </div>

        </form>
      </div>

      <!-- Footer -->
      <div class="d-flex justify-content-center" id="divBotoesFormulario">
        <button type="button" class="btn btn-secondary w-100 m-1" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary w-100 m-1" onclick="f_salvarBatida()">Salvar</button>
      </div>

      <div id="msgAlertaFormulario"></div>



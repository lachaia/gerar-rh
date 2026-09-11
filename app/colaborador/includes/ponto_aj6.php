<?PHP
//
//- ponto_aj6.php | (C)haia, 09/12/2025 | Formulário EXCLUIR uma Batida
//

session_start();

$id = $_POST['id'];

if( empty($id) ){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

//
//- LÊ DADOS DO REGISTRO
//
    include "../../includes/conexao_gerar.php";
    include "../../includes/debug.php";
    
    $sql = "SELECT 
                date(data_hora) as data, 
                DATE_FORMAT(data_hora, '%H:%i') AS hora 
            FROM rh_ponto_registros 
            WHERE id = :id";
    $stmt = $conn->prepare($sql);  
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($dados);
    ?>

      <!-- Header -->
    <h5 class="text-center h5" id="">EXCLUIR Registro-Ponto</h5>

      <!-- Body -->
      <div class="">
        <form id="formBatida">
          <input type="hidden" id="batidaId" name="batidaId" value='<?= $id ?>'>
          <input type="hidden" id="tipoSolicitacao" name="tipoSolicitacao" value='DEL'>

          <div class="mb-3">
            <label for="dataBatida" class="form-label">Data</label>
            <input 
                type="date" 
                id="dataBatida" name="dataBatida" 
                class="form-control text-center" 
                style='font-weight: bold; background-color: #e9ecef; color: #495057; font-size: 1.2em;'
                readonly 
                value='<?= $data ?>'>
          </div>

          <div class="mb-3">
            <label for="horaBatida" class="form-label">Hora</label>
            <input 
                type="time" 
                id="horaBatida" name="horaBatida" 
                class="form-control text-center" required
                style='font-weight: bold; background-color: #e9ecef; color: #495057; font-size: 1.2em;'
                readonly
                value = '<?= $hora ?>'>
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
        <button type="button" class="btn btn-danger w-100 m-1" onclick="f_salvar_exclusao()">Confirmar!</button>
      </div>

      <div id="msgAlertaFormulario"></div>



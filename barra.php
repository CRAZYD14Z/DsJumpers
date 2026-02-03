

<?php
    ob_start();
    session_start(); 
    $_SESSION['Idioma'] = 'es';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['Idioma'];?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración con Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
   

</head>
<body>
<?php
    include_once 'nav.php';
?>

<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRangoFechas">
  Seleccionar Rango
</button>

<div class="modal fade" id="modalRangoFechas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Configurar Rango de Tiempo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formRango">
          <div class="mb-3">
            <label for="fechaInicio" class="form-label">Fecha y Hora de Inicio</label>
            <input type="text" class="form-control" id="fechaInicio" placeholder="Seleccione inicio...">
            
          </div>
          <div class="mb-3">
            <label for="fechaFin" class="form-label">Fecha y Hora de Término</label>

            <input type="text" class="form-control" id="fechaFin" placeholder="Seleccione término...">            
            
          </div>
          <div id="errorMsg" class="text-danger small d-none">
            La fecha de término debe ser posterior a la de inicio.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardar">Guardar Rango</button>
      </div>
    </div>
  </div>
</div>

<div class="container mt-5">
  <h4 class="mb-3">Simulación de Ciclo Solar (24h)</h4>
  
  <div class="day-cycle-bar shadow-sm rounded">
    <div class="d-flex justify-content-between px-2 pt-1 text-white small fw-bold">
      <span>12:00 AM</span>
      <span>6:00 AM</span>
      <span>12:00 PM</span>
      <span>6:00 PM</span>
      <span>11:59 PM</span>
    </div>
    
    <div id="marcadorHora" class="time-marker"></div>
  </div>
  
  <p class="mt-2 text-muted small text-center">El degradado representa la intensidad de la luz desde la medianoche hasta el fin del día.</p>
</div>

<style>
/* El "corazón" del degradado */
.day-cycle-bar {
  height: 80px;
  position: relative;
  width: 100%;
  /* Gradiente: Noche -> Amanecer -> Mediodía -> Atardecer -> Noche */
  background: linear-gradient(to right, 
    #00111e 0%,    /* 12 AM - Noche profunda */
    #00111e 15%, 
    #ff7e5f 25%,   /* 6 AM - Amanecer */
    #feb47b 35%, 
    #87ceeb 50%,   /* 12 PM - Día claro */
    #87ceeb 65%, 
    #f093fb 75%,   /* 6 PM - Atardecer */
    #4facfe 85%, 
    #00111e 100%   /* 11:59 PM - Regreso a noche */
  );
  overflow: hidden;
  border: 1px solid #ddd;
}

/* Línea que marca la hora actual */
.time-marker {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 3px;
  background-color: rgba(255, 255, 255, 0.8);
  box-shadow: 0 0 10px rgba(0,0,0,0.5);
  transition: left 0.5s ease;
}
</style>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>   
<script>
$(document).ready(function() {
    // Configuración base de Flatpickr
    const config = {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        locale: "es",
        time_24hr: true,
        onChange: function() {
            validarRango(); // Llamamos a tu función de validación
        }
    };

    // Inicializar los selectores
    const fpInicio = $("#fechaInicio").flatpickr(config);
    const fpFin = $("#fechaFin").flatpickr(config);

    function validarRango() {
        // Obtenemos las fechas directamente de los objetos Flatpickr
        const inicio = fpInicio.selectedDates[0];
        const fin = fpFin.selectedDates[0];
        
        if (inicio && fin) {
            if (fin <= inicio) {
                $('#errorMsg').removeClass('d-none');
                $('#btnGuardar').prop('disabled', true);
            } else {
                $('#errorMsg').addClass('d-none');
                $('#btnGuardar').prop('disabled', false);
            }
        }
    }
});

$(document).ready(function() {
    function actualizarMarcador() {
        const ahora = new Date();
        const horas = ahora.getHours();
        const minutos = ahora.getMinutes();
        
        // Calculamos el porcentaje del día transcurrido (0 a 100)
        // Total minutos en un día = 1440
        const totalMinutosDia = (horas * 60) + minutos;
        const porcentaje = (totalMinutosDia / 1440) * 100;
        
        // Movemos el marcador visualmente
        $('#marcadorHora').css('left', porcentaje + '%');
    }

    // Ejecutar al cargar y cada minuto
    actualizarMarcador();
    setInterval(actualizarMarcador, 60000);
});

</script>
</body>
</html>




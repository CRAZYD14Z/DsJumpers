<!DOCTYPE html>
<html lang="<?php echo $_SESSION['Idioma'];?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración con Navbar</title>


</head>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .modal-content { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .flatpickr-calendar { box-shadow: none !important; border: none !important; }
    .hour-select { border-radius: 8px; border: 2px solid #dee2e6; }
    .hour-select:focus { border-color: #0d6efd; box-shadow: none; }
</style>

<div class="container mt-5">
    <div class="card shadow-sm mx-auto border-0" style="max-width: 650px; border-radius: 15px;">
        <div class="card-body p-4">
            <h5 class="text-center fw-bold mb-4">Registro de Horario Local</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Fecha/Hora Inicio</label>
                    <input type="datetime-local" id="fechahorainicio" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Fecha/Hora Término</label>
                    <input type="datetime-local" id="fechahorafin" class="form-control">
                </div>
            </div>
            <button type="button" class="btn btn-dark w-100 mt-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalReserva">
                Abrir Selector de Rango
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReserva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Configurar Periodo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-7 p-4 border-end d-flex justify-content-center bg-white">
                        <input type="text" id="calendarioRango" class="d-none">
                    </div>
                    
                    <div class="col-md-5 p-4 bg-light">
                        <div class="mb-4">
                            <label class="small fw-bold text-primary text-uppercase d-block mb-2">Hora Inicio</label>
                            <select id="hInicio" class="form-select hour-select"></select>
                        </div>
                        <div class="mb-4">
                            <label class="small fw-bold text-success text-uppercase d-block mb-2">Hora Término</label>
                            <select id="hFin" class="form-select hour-select"></select>
                        </div>
                        <div class="alert alert-info py-2 small border-0 shadow-sm">
                            Haga clic en el primer día y luego en el segundo para definir el rango.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmar" class="btn btn-primary px-4 fw-bold">Sincronizar Datos</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Calendario
    const fp = flatpickr("#calendarioRango", {
        mode: "range",
        inline: true,
        locale: "es",
        minDate: "today",
        dateFormat: "Y-m-d"
    });

    const hInicio = document.getElementById('hInicio');
    const hFin = document.getElementById('hFin');

    // 2. Llenar selectores con etiquetas AM/PM pero valores en 24h
    function llenarHoras() {
        for (let i = 0; i < 24; i++) {
            let valor24 = i.toString().padStart(2, '0') + ":00";
            let ampm = i >= 12 ? 'PM' : 'AM';
            let h12 = i % 12 || 12;
            let label = `${h12}:00 ${ampm}`;
            
            hInicio.innerHTML += `<option value="${valor24}">${label}</option>`;
            hFin.innerHTML += `<option value="${valor24}">${label}</option>`;
        }
    }
    llenarHoras();

    // 3. Sugerencia de +8 horas
    hInicio.addEventListener('change', function() {
        let h = parseInt(this.value.split(':')[0]);
        let nuevaH = (h + 8) % 24;
        hFin.value = nuevaH.toString().padStart(2, '0') + ":00";
    });

    // 4. Confirmar y formatear para datetime-local
    document.getElementById('btnConfirmar').addEventListener('click', function() {
        const fechas = fp.selectedDates;
        
        if (fechas.length < 2) {
            alert("Por favor selecciona dos fechas en el calendario.");
            return;
        }

        // Formato ISO local: YYYY-MM-DD
        const f1 = fechas[0].toLocaleDateString('sv-SE'); // sv-SE devuelve YYYY-MM-DD
        const f2 = fechas[1].toLocaleDateString('sv-SE');

        // Los inputs datetime-local requieren el formato: YYYY-MM-DDTHH:mm
        document.getElementById('fechahorainicio').value = `${f1}T${hInicio.value}`;
        document.getElementById('fechahorafin').value = `${f2}T${hFin.value}`;

        bootstrap.Modal.getInstance(document.getElementById('modalReserva')).hide();
    });

    // Valores iniciales
    hInicio.value = "08:00";
    hInicio.dispatchEvent(new Event('change'));
});
</script>
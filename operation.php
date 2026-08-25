<?php
ob_start();
session_start();
// Incluye la clase de conexión a la BD
include_once 'valid_login.php';
include_once 'config/config.php';
include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$Idioma = $_SESSION['Idioma'];
$query = "select Traduccion FROM  programas_traduccion where Programa = 'operation' AND Idioma = ? ORDER BY Id";
$stmt = $db->prepare($query);
$stmt->bindValue(1, $Idioma);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$Traducciones[] = '';
if ($resultados) {
    foreach ($resultados as $registro) {
        $Traducciones[] = $registro['Traduccion'];
    }
}
function Trd($Id)
{
    global $Traducciones;
    return $Traducciones[$Id];
}

$Operadores = '';
$query = "SELECT Id, Nombres, Apellidos  from operators WHERE Estatus = 'A' AND Tipo = 'DRIVER'";
$stmt = $db->prepare($query);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($resultados) {
    foreach ($resultados as $registro) {
        $Operadores .= '{ Id: ' . $registro['Id'] . ', Nombre: "' . $registro['Nombres'] . ' ' . $registro['Apellidos'] . '" },';
    }
}

$Vehiculos = '';
$queryV = "SELECT id_vehicle, description, plates from vehicles WHERE active = 1 ORDER BY description";
$stmtV = $db->prepare($queryV);
$stmtV->execute();
$resultadosV = $stmtV->fetchAll(PDO::FETCH_ASSOC);
if ($resultadosV) {
    foreach ($resultadosV as $registro) {
        $desc = addslashes($registro['description'] ?? '');
        $placas = addslashes($registro['plates'] ?? '');
        $Vehiculos .= '{ Id: ' . $registro['id_vehicle'] . ', Descripcion: "' . $desc . '", Placas: "' . $placas . '" },';
    }
}

$asignaciones = '';
$query = "SELECT id_vehicle, DATE_FORMAT(StartDateTime, '%Y-%m-%d') as StartDateTime, id_driver from v_operations WHERE id_driver > 0 ";
$stmt = $db->prepare($query);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($resultados) {
    foreach ($resultados as $registro) {
        $asignaciones .= '{ IdVehiculo: ' . $registro['id_vehicle'] . ', Fecha: "' . $registro['StartDateTime'] . '", IdOperador: ' . $registro['id_driver'] . ' },';
    }
}
include_once 'head.php';
?>
<style>
    .table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }

    /* Bordes laterales de colores según status */
    .status-parcial {
        background: #6c757d !important;
    }

    .status-cotizado {
        background: #17a2b8 !important;
    }

    .status-confirmado {
        background: #28a745 !important;
    }

    .status-pendiente {
        background: #ffc107 !important;
    }

    .status-completado {
        background: #007bff !important;
    }

    .status-cancelado {
        background: #dc3545 !important;
    }

    .clickable-row:hover {
        background-color: rgba(13, 110, 253, 0.05) !important;
        /* Un azul muy tenue */
        transition: background-color 0.2s ease;
    }

    /* Efecto de foco en el select */
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }

    /* Redondear bordes del modal */
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }

    /* Estilo para el encabezado */
    .modal-header {
        border-bottom: 1px solid #f0f0f0;
    }
</style>
</head>

<body>

    <?php
    include_once 'nav.php';
    ?>

    <br>
    <br>

    <div class="container my-5">
        <div class="card shadow">
            <div class="card-header text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-black"><?php echo Trd(37) ?></h4>
            </div>
        </div>

        <div class="container pb-5">
            <div class="table-container shadow-sm border">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4"><?php echo Trd(2) ?></th>
                                <th><?php echo Trd(3) ?></th>
                                <th><?php echo Trd(4) ?></th>
                                <th><?php echo Trd(5) ?></th>
                                <th class="text-end pe-4"><?php echo Trd(6) ?></th>
                            </tr>
                        </thead>
                        <tbody id="leadsData">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="loadingIndicator" class="text-center my-4" style="display:none;">
                <div class="spinner-grow text-primary" role="status"></div>
                <p class="text-muted small"><?php echo Trd(7) ?></p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalOperadores" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-person-badge me-2"></i><?= Trd(14) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="display-6 text-primary mb-2">
                            <i class="bi bi-truck"></i>
                        </div>
                        <p class="text-muted small"><?= Trd(15) ?></p>
                    </div>

                    <div class="form-floating">
                        <select class="form-select border-primary-subtle" id="selectOperador" aria-label="Selección de operador">
                        </select>
                        <label for="selectOperador"><?= Trd(16) ?></label>
                    </div>

                    <div id="infoRuta" class="mt-3 p-2 bg-light rounded-3 small text-center text-secondary d-none">
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"><?= Trd(17) ?></button>
                    <button type="button" id="btnGuardarAsignacion" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i><?= Trd(18) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRutas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-arrow-left-right me-2"></i><?= Trd(19) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="display-6 text-primary mb-2">
                            <i class="bi bi-truck"></i>
                        </div>
                        <p class="text-muted small mb-1"><?= Trd(20) ?></p>
                        <div id="infoRutaAsignar" class="badge bg-light text-dark border"></div>
                    </div>

                    <!-- Selector de Tipo de Reasignación -->
                    <div class="btn-group w-100 mb-3" role="group" id="tipoReasignacionGroup">
                        <input type="radio" class="btn-check" name="tipoReasignacion" id="optRutaExistente" value="existente" checked autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="optRutaExistente">
                            <i class="bi bi-signpost-split me-1"></i><?= ($_SESSION['Idioma'] == 'en') ? 'Existing Route' : 'Ruta Existente' ?>
                        </label>

                        <input type="radio" class="btn-check" name="tipoReasignacion" id="optNuevaRuta" value="nueva" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="optNuevaRuta">
                            <i class="bi bi-plus-circle me-1"></i><?= ($_SESSION['Idioma'] == 'en') ? 'New Route (Vehicle & Driver)' : 'Nueva Ruta (Vehículo y Chofer)' ?>
                        </label>
                    </div>

                    <!-- Sección: Ruta Existente -->
                    <div id="seccionRutaExistente">
                        <div class="form-floating mb-2">
                            <select class="form-select border-primary-subtle" id="selectRoute" aria-label="Selección de ruta">
                            </select>
                            <label for="selectRoute"><?= Trd(21) ?></label>
                        </div>
                        <div id="avisoSinRutas" class="alert alert-warning py-2 small d-none">
                            <i class="bi bi-info-circle me-1"></i><?= Trd(25) ?>
                        </div>
                    </div>

                    <!-- Sección: Nueva Ruta -->
                    <div id="seccionNuevaRuta" class="d-none">
                        <div class="form-floating mb-3">
                            <select class="form-select border-primary-subtle" id="selectNuevoVehiculo" aria-label="Selección de vehículo">
                            </select>
                            <label for="selectNuevoVehiculo"><?= ($_SESSION['Idioma'] == 'en') ? 'Vehicle' : 'Vehículo' ?></label>
                        </div>
                        <div class="form-floating mb-2">
                            <select class="form-select border-primary-subtle" id="selectNuevoChofer" aria-label="Selección de chofer">
                            </select>
                            <label for="selectNuevoChofer"><?= ($_SESSION['Idioma'] == 'en') ? 'Driver' : 'Chofer / Operador' ?></label>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"><?= Trd(22) ?></button>
                    <button type="button" id="btnGuardarAsignacionRuta" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i><?= Trd(23) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEventoExtra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill"></i><?= Trd(39) ?> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEventoExtra">
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label"><?= Trd(40) ?></label>
                            <input type="text" id="eventTitulo" name="eventTitulo" class="form-control" placeholder="" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= Trd(41) ?></label>
                            <textarea id="eventDesc" name="eventDesc" class="form-control" rows="3" placeholder="<?= Trd(42) ?>..." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?= Trd(43) ?></label>
                                <input type="number" id="eventGasto" name="eventGasto" class="form-control" step="0.01" value="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?= Trd(44) ?></label>
                                <input type="file" id="eventFoto" name="eventFoto" class="form-control" accept="image/*" capture="environment">
                            </div>
                        </div>

                        <!-- Vista previa de la foto -->
                        <div class="text-center">
                            <img id="previewFoto" src="#" alt="Previsualización" class="img-fluid rounded d-none" style="max-height: 200px;">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Trd(45) ?></button>
                        <button type="submit" class="btn btn-primary"><?= Trd(46) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar la imagen -->
    <div class="modal fade" id="modalImagen" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= Trd(47) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="imgModal" class="img-fluid" alt="<?= Trd(48) ?>...">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarBorradoRuta" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header ">
                    <h5 class="modal-title"><?= Trd(49) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p><?= Trd(50) ?></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Trd(51) ?></button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarBorradoRuta"><?= Trd(52) ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarBorrado" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header ">
                    <h5 class="modal-title"><?= Trd(49) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p><?= Trd(50) ?></p>
                    <input type="hidden" id="idParaBorrar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Trd(51) ?></button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarBorrado"><?= Trd(52) ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        <?php
        include_once 'js_scripts.php';
        ?>
        let vehiculo_ev = '';
        let date_ev = '';
        let grupos = {};

        $(document).on('click', '.btn-ejecutar-carga', function(e) {
            e.stopPropagation();
            const vehiculoId = $(this).data('vid');
            const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
            const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
            var parts = fechaInicial.split(" ");
            const url = `load_emulator.php?VehiculoId=${vehiculoId}&FechaInicial=${encodeURIComponent(parts[0])}`;
            window.location.href = url;
        });



        $(document).on('click', '.btn-chofer-ruta', function(e) {
            e.stopPropagation();
            const vehiculoId = $(this).data('vid');
            const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
            const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
            var parts = fechaInicial.split(" ");
            //const url = `load_emulator.php?VehiculoId=${vehiculoId}&FechaInicial=${encodeURIComponent(parts[0])}`;
            //window.location.href = url;
            //alert('Asignar Chofer')
            abrirModalAsignacion(vehiculoId, parts[0])
        });

        $(document).on('click', '.btn-evento', function(e) {
            e.stopPropagation();
            const vehiculoId = $(this).data('vid');
            const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
            const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
            var parts = fechaInicial.split(" ");
            vehiculo_ev = vehiculoId;
            date_ev = parts[0];

            $('#modalEventoExtra').modal('show');

            //abrirModalAsignacion(vehiculoId, parts[0])
        });

        $(document).on('click', '.btn-eliminar-ruta', function(e) {
            e.stopPropagation();

            const vehiculoId = $(this).data('vid');
            const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
            const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
            var parts = fechaInicial.split(" ");
            vehiculo_ev = vehiculoId;
            date_ev = parts[0];

            $('#modalConfirmarBorradoRuta').modal('show');
            /*
                const vehiculoId = $(this).data('vid');
                const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
                const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
                var parts = fechaInicial.split(" ");     
                let formData = new FormData();
                formData.append('vehiculoId', vehiculoId);
                formData.append('fecha', parts[0]);
                $.ajax({
                    url: API_BASE_URL + 'api/delete_route/',
                    method: 'POST',
                    data: formData,
                    headers: { 'Authorization': 'Bearer ' + TOKEN },            
                    processData: false, // Vital para FormData
                    contentType: false, // Vital para FormData
                    success: function(response) {
                        location.reload();
                    },
                    error: function() {
                    }
                });
            */
        });

        // Datos iniciales
        let operadores = [<?php echo $Operadores; ?>];
        let vehiculos = [<?php echo $Vehiculos; ?>];
        let asignaciones = [<?php echo $asignaciones; ?>];

        let rutas = [];

        // Control de cambio de pestaña/modo en el modal de reasignación
        $(document).on('change', 'input[name="tipoReasignacion"]', function() {
            const modo = $(this).val();
            if (modo === 'nueva') {
                $('#seccionRutaExistente').addClass('d-none');
                $('#seccionNuevaRuta').removeClass('d-none');
                validarBotonNuevaRuta();
            } else {
                $('#seccionRutaExistente').removeClass('d-none');
                $('#seccionNuevaRuta').addClass('d-none');
                validarBotonRutaExistente();
            }
        });

        function validarBotonRutaExistente() {
            const idRoute = $('#selectRoute').val();
            $('#btnGuardarAsignacionRuta').prop('disabled', !idRoute || idRoute === "");
        }

        function validarBotonNuevaRuta() {
            const idVehiculo = $('#selectNuevoVehiculo').val();
            const idChofer = $('#selectNuevoChofer').val();
            $('#btnGuardarAsignacionRuta').prop('disabled', !idVehiculo || !idChofer);
        }

        $(document).on('change', '#selectRoute', validarBotonRutaExistente);
        $(document).on('change', '#selectNuevoVehiculo, #selectNuevoChofer', validarBotonNuevaRuta);

        function cambiarOrden(event, grupoKey, indexActual, direccion) {
            event.stopPropagation(); // Evitar click en la fila

            const grupo = grupos[grupoKey];
            const indexDestino = (direccion === 'up') ? indexActual - 1 : indexActual + 1;

            // Validación de seguridad
            if (indexDestino < 0 || indexDestino >= grupo.items.length) return;

            const itemActual = grupo.items[indexActual];
            const itemDestino = grupo.items[indexDestino];

            // Mostramos un loader o bloqueamos la UI opcionalmente
            console.log(`Cambiando orden entre: ${itemActual.Id_operation} y ${itemDestino.Id_operation}`);

            $.ajax({
                url: API_BASE_URL + 'swap_order/',
                method: 'POST',
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + TOKEN
                },
                data: JSON.stringify({
                    id_1: itemActual.Id_operation,
                    orden_1: itemDestino.orden, // El actual toma el orden del destino
                    id_2: itemDestino.Id_operation,
                    orden_2: itemActual.orden // El destino toma el orden del actual
                }),
                success: function(response) {
                    $('#leadsData').empty();
                    renderTable(response)
                },
                error: function() {
                    alert("Error al cambiar el orden en la base de datos.");
                }
            });
        }


        function abrirAsignacion(event, date, route, Id_operation) {
            date = date.substring(0, 10);
            event.stopPropagation();

            $('#infoRutaAsignar').text(`Fecha: ${date} | Operación: #${Id_operation}`);

            // 1. Llenar select de Rutas Existentes
            const $select = $('#selectRoute');
            $select.empty().append('<option value="" selected disabled><?= Trd(24) ?></option>');

            let rutasDisponibles = 0;
            rutas.forEach(op => {
                let nombreChofer = op.NombreChofer ? op.NombreChofer : (op.NombresChofer ? (op.NombresChofer + ' ' + (op.ApellidosChofer || '')) : 'S/A');
                if (op.Fecha == date && op.Ruta != route) {
                    $select.append(`<option value="${op.Ruta}"> ${op.nombreVehiculo} [${op.placas || ''}] - ${nombreChofer} </option>`);
                    rutasDisponibles++;
                }
            });

            if (rutasDisponibles === 0) {
                $('#avisoSinRutas').removeClass('d-none');
                // Si no hay rutas existentes, cambiar automáticamente a "Nueva Ruta"
                $('#optNuevaRuta').prop('checked', true).trigger('change');
            } else {
                $('#avisoSinRutas').addClass('d-none');
                $('#optRutaExistente').prop('checked', true).trigger('change');
            }

            // 2. Llenar select de Vehículos
            const $selectVehiculo = $('#selectNuevoVehiculo');
            $selectVehiculo.empty().append('<option value="" selected disabled><?= ($_SESSION['Idioma'] == 'en') ? 'Select Vehicle...' : 'Seleccionar Vehículo...' ?></option>');
            const vehiculosConRuta = rutas.filter(r => r.Fecha === date).map(r => r.IdVehiculo);
            vehiculos.forEach(v => {
                const tieneRuta = vehiculosConRuta.includes(v.Id);
                const tag = tieneRuta ? ' (con ruta)' : ' (disponible)';
                $selectVehiculo.append(`<option value="${v.Id}">${v.Descripcion} [${v.Placas}] ${tag}</option>`);
            });

            // 3. Llenar select de Choferes
            const $selectChofer = $('#selectNuevoChofer');
            $selectChofer.empty().append('<option value="" selected disabled><?= ($_SESSION['Idioma'] == 'en') ? 'Select Driver...' : 'Seleccionar Chofer...' ?></option>');
            const choferesOcupados = asignaciones.filter(a => a.Fecha === date).map(a => a.IdOperador);
            operadores.forEach(op => {
                const ocupado = choferesOcupados.includes(op.Id);
                const tag = ocupado ? ' (asignado)' : '';
                $selectChofer.append(`<option value="${op.Id}">${op.Nombre}${tag}</option>`);
            });

            // 4. Configurar botón Guardar
            $('#btnGuardarAsignacionRuta').off('click').on('click', function() {
                const modo = $('input[name="tipoReasignacion"]:checked').val();

                if (modo === 'existente') {
                    const idRoute = parseInt($('#selectRoute').val());
                    if (idRoute) {
                        reasignarRuta(Id_operation, date, idRoute, null, null, false);
                        $(this).html('<span class="spinner-border spinner-border-sm"></span> <?= Trd(26) ?>');
                    }
                } else {
                    const idVehiculo = parseInt($('#selectNuevoVehiculo').val());
                    const idChofer = parseInt($('#selectNuevoChofer').val());
                    if (idVehiculo && idChofer) {
                        reasignarRuta(Id_operation, date, null, idVehiculo, idChofer, true);
                        $(this).html('<span class="spinner-border spinner-border-sm"></span> <?= Trd(26) ?>');
                    }
                }
            });
            $('#modalRutas').modal('show');
        }


        function reasignarRuta(Id_operation, date, idRoute, idVehicle, idDriver, isNewRoute) {
            let formData = new FormData();
            formData.append('idOperation', Id_operation);
            formData.append('date', date);

            if (isNewRoute) {
                formData.append('isNewRoute', 1);
                formData.append('idVehicle', idVehicle);
                formData.append('idDriver', idDriver);
            } else {
                formData.append('idRoute', idRoute);
            }

            $.ajax({
                url: API_BASE_URL + 'api/reassign_route/',
                method: 'POST',
                data: formData,
                headers: {
                    'Authorization': 'Bearer ' + TOKEN
                },
                processData: false, // Vital para FormData
                contentType: false, // Vital para FormData
                dataType: 'json',
                success: function(response) {
                    $('#modalRutas').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Error al reasignar.";
                    alert(msg);
                    $('#btnGuardarAsignacionRuta').html('<i class="bi bi-check2-circle me-1"></i><?= Trd(23) ?>');
                }
            });
        }


        function abrirModalAsignacion(IdVehiculo, fecha) {
            const ocupados = asignaciones.filter(a => a.Fecha === fecha).map(a => a.IdOperador);
            const disponibles = operadores.filter(op => !ocupados.includes(op.Id));

            const $select = $('#selectOperador');
            const $info = $('#infoRuta');

            // Mostrar info de contexto en el modal
            //$info.html(`IdVehiculo: <b>#${IdVehiculo}</b> | Fecha: <b>${fecha}</b>`).removeClass('d-none');
            $info.html(` Fecha: <b>${fecha}</b>`).removeClass('d-none');

            $select.empty().append('<option value="" selected disabled><?= Trd(28) ?></option>');

            if (disponibles.length === 0) {
                $select.append('<option value="">⚠️<?= Trd(29) ?> </option>');
                $('#btnGuardarAsignacion').prop('disabled', true);
            } else {
                $('#btnGuardarAsignacion').prop('disabled', false);
                disponibles.forEach(op => {
                    $select.append(`<option value="${op.Id}">👤 ${op.Nombre}</option>`);
                });
            }

            $('#btnGuardarAsignacion').off('click').on('click', function() {
                const idOp = parseInt($select.val());
                if (idOp) {
                    agregarAsignacion(IdVehiculo, fecha, idOp);
                    // Efecto visual antes de cerrar
                    $(this).html('<span class="spinner-border spinner-border-sm"></span> <?= Trd(30) ?>');
                    setTimeout(() => {
                        $('#modalOperadores').modal('hide');
                        $(this).html('<i class="bi bi-check2-circle me-1"></i><?= Trd(31) ?>');
                    }, 600);
                }
            });

            $('#modalOperadores').modal('show');
        }

        /**
         * Agrega el registro al arreglo de asignaciones
         */
        function agregarAsignacion(idVehiculo, fecha, idOperador) {
            asignaciones.push({
                IdVehiculo: idVehiculo,
                Fecha: fecha,
                IdOperador: idOperador
            });


            let formData = new FormData();
            formData.append('vehiculoId', idVehiculo);
            formData.append('fecha', fecha);
            formData.append('operadorId', idOperador);
            $.ajax({
                url: API_BASE_URL + 'api/assign_operator/',
                method: 'POST',
                data: formData,
                headers: {
                    'Authorization': 'Bearer ' + TOKEN
                },
                processData: false, // Vital para FormData
                contentType: false, // Vital para FormData
                success: function(response) {
                    location.reload();
                },
                error: function() {}
            });

            //console.log("Asignación exitosa:", asignaciones);
            // Aquí puedes llamar a una función para refrescar tu tabla o vista

        }

        // Variable global para almacenar la ubicación actual
        let posicionActual = null;

        // Intentar obtener la ubicación apenas cargue el script
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    posicionActual = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    console.log("Ubicación de origen lista.");
                },
                (error) => {
                    console.warn("No se pudo pre-cargar la ubicación:", error.message);
                }, {
                    enableHighAccuracy: true,
                    timeout: 5000
                }
            );
        }

        $(document).ready(function() {
            /*
                attemptLogin('admin', '1234'); 
                
                if (TOKEN) {
                    //getRecordData(1); 
                } else {
                    console.warn('No se encontró el token. Necesita iniciar sesión primero.');
                }        
            */
            let currentPage = 1;
            let isFetching = false;
            let noMoreData = false;
            let timer;

            // Función Principal con AJAX
            function fetchLeads(reset = false) {
                if (isFetching || (noMoreData && !reset)) return;

                if (reset) {
                    currentPage = 1;
                    noMoreData = false;
                    $('#leadsData').empty();
                }

                isFetching = true;
                $('#loadingIndicator').fadeIn();

                $.ajax({
                    url: API_BASE_URL + 'operation/',
                    type: 'GET',
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + TOKEN
                    },
                    data: {
                        page: currentPage,
                        search: $('#txtSearch').val(),
                        tipo: '<?= $_SESSION['role_id'] ?? 'Rol' ?>',
                        usuario: '<?= $_SESSION['user'] ?? 'User' ?>',
                        id: '<?= $_SESSION['usuario_id'] ?>'
                    },
                    success: function(response) {
                        if (response.length === 0) {
                            noMoreData = true;
                            if (currentPage === 1) {
                                $('#leadsData').html('<tr><td colspan="5" class="text-center py-5 text-muted"><?php echo Trd(8) ?></td></tr>');
                            }
                        } else {
                            renderTable(response);
                            currentPage++;
                        }
                    },
                    error: function(xhr, status, error) {
                        //console.error("Error en la petición:", error);
                        //alert("<?php echo Trd(9) ?>");
                    },
                    complete: function() {
                        isFetching = false;
                        $('#loadingIndicator').fadeOut();
                    }
                });
            }



            function abrirRutaEnMaps(event, destLat, destLng) {
                // Evita que al hacer clic en el botón se dispare el evento de la fila (tr)
                event.stopPropagation();

                // Verificamos si el navegador soporta geolocalización
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        // Éxito: Tenemos la ubicación del dispositivo
                        const originLat = position.coords.latitude;
                        const originLng = position.coords.longitude;

                        // Construimos la URL con origen y destino
                        const url = `https://www.google.com/maps/dir/?api=1&origin=${originLat},${originLng}&destination=${destLat},${destLng}&travelmode=driving`;

                        window.open(url, '_blank');
                    }, function(error) {
                        // Error o permiso denegado: Abrimos maps solo con el destino (Google usará ubicación aproximada)
                        console.warn("No se pudo obtener la ubicación exacta:", error.message);
                        const urlFallback = `https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`;
                        window.open(urlFallback, '_blank');
                    });
                } else {
                    // El navegador no soporta geolocalización
                    const urlFallback = `https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`;
                    window.open(urlFallback, '_blank');
                }
            }


            // Evento Scroll (Infinite Scroll)
            $(window).on('scroll', function() {
                //        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
                //            fetchLeads();
                //        }
            });

            // Evento de Búsqueda (Debounce para no saturar el servidor)
            $('#txtSearch').on('keyup', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    fetchLeads(true);
                }, 500);
            });

            // Delegación de eventos para filas dinámicas
            $('#leadsData').on('click', '.clickable-row', function() {
                const leadId = $(this).data('id'); // Obtenemos el ID del atributo data-id
                const status = $(this).data('status'); // Obtenemos el ID del atributo data-id

                if (status == "SURTIDO")
                    return;

                if (leadId) {
                    // Redirección a la página de detalles
                    window.location.href = `operations.php?IdOperation=${leadId}`;
                }
            });

            // Carga inicial
            fetchLeads();
        });

        function abrirRutaEnMaps_(event, destLat, destLng) {
            event.stopPropagation(); // Evitamos el conflicto con la fila

            let url;
            const destino = `${destLat},${destLng}`;

            if (posicionActual) {
                // Usamos la ubicación que ya teníamos guardada
                const origen = `${posicionActual.lat},${posicionActual.lng}`;
                url = `https://www.google.com/maps/dir/?api=1&origin=${origen}&destination=${destino}&travelmode=driving`;
            } else {
                // Si por alguna razón no se ha cargado (el usuario fue muy rápido o denegó permiso)
                // Google Maps tratará de determinar el origen por su cuenta
                url = `https://www.google.com/maps/dir/?api=1&destination=${destino}`;
            }

            window.open(url, '_blank');
        }

        function getBadgeColor(status) {
            const s = status.toLowerCase();
            if (s.includes('parcial') || s.includes('draft')) return 'status-parcial';
            if (s.includes('cotizado') || s.includes('quoted')) return 'status-cotizado';
            if (s.includes('confirmado') || s.includes('confirmed')) return 'status-confirmado';
            if (s.includes('pendiente') || s.includes('pending')) return 'status-pendiente';
            if (s.includes('completo') || s.includes('complete')) return 'status-completo';
            if (s.includes('cancelado') || s.includes('canceled')) return 'status-cancelado';
            return 'text-bg-secondary';
        }


        function renderTable(data) {
            data_org = data;
            data = data.operations;
            events = data.extra_events
            let rows = '';
            grupos = {};

            // 1. Agrupar los datos
            $.each(data, function(i, item) {
                //const grupoKey = `${item.id_vehicle}_${item.vehiculo}_${item.placas}_${item.StartDateTime}`;
                const fechaSoloDia = item.StartDateTime.substring(0, 10);

                // Usamos la nueva variable para armar la clave del grupo
                const grupoKey = `${item.id_vehicle}_${item.vehiculo}_${item.placas}_${fechaSoloDia}`;

                if (!grupos[grupoKey]) {
                    grupos[grupoKey] = {
                        id_vehicle: item.id_vehicle,
                        nombreVehiculo: item.vehiculo,
                        placas: item.placas,

                        id_driver: item.id_driver,
                        NombresChofer: item.NombresChofer,
                        ApellidosChofer: item.ApellidosChofer,
                        Date: item.StartDateTime,
                        items: []
                    };

                    var SDate = item.StartDateTime;
                    SDate = SDate.substring(0, 10);

                    rutas.push({
                        IdVehiculo: item.id_vehicle,
                        nombreVehiculo: item.vehiculo,
                        placas: item.placas,
                        NombresChofer: item.NombresChofer,
                        Fecha: SDate,
                        Ruta: item.id_route
                    });
                }
                grupos[grupoKey].items.push(item);
            });

            // 2. Renderizar los grupos
            $.each(grupos, function(key, grupo) {

                // VALIDACIÓN: ¿Todos los status son "CARGAR"?
                const todosParaCargar = grupo.items.every(item => item.Status.toUpperCase() === "SURTIDO");

                const fechasCarga = grupo.items.map(it => it.StartDateTime);

                // Creamos el botón solo si la condición se cumple
                const botonCargar = todosParaCargar ?
                    `<button class="btn btn-sm btn-success ms-3 btn-ejecutar-carga" 
                       data-vid="${grupo.id_vehicle}" 
                       data-fechas='${JSON.stringify(fechasCarga)}'>
                   <i class="fas fa-file-upload me-1"></i> <?= Trd(32) ?>
               </button>` :
                    '';

                let todosParaEliminar = grupo.items.every(item => item.Status.toUpperCase() === "BODEGA");
                if (!todosParaEliminar)
                    todosParaEliminar = grupo.items.every(item => item.Status.toUpperCase() === "SURTIDO");
                if (!todosParaEliminar)
                    todosParaEliminar = grupo.items.every(item => item.Status.toUpperCase() === "CARGA");

                let todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "BODEGA");
                if (!todosParaChofer)
                    todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "SURTIDO");
                if (!todosParaChofer)
                    todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "CARGA");

                const botonEliminar = todosParaEliminar ? `<button class="btn btn-sm btn-danger ms-3 btn-eliminar-ruta" 
                       data-vid="${grupo.id_vehicle}" 
                       data-fechas='${JSON.stringify(fechasCarga)}'>
                   <i class="fa-solid fa-trash-can"></i> <?= Trd(33) ?>
               </button>` : '';


                let botonChofer = '';

                if (grupo.id_driver == 0) {

                    botonChofer = todosParaChofer ? `<button class="btn btn-sm btn-primary ms-3 btn-chofer-ruta" 
                        data-vid="${grupo.id_vehicle}" 
                        data-fechas='${JSON.stringify(fechasCarga)}'>
                    <i class="fa-solid fa-user"></i> <?= Trd(34) ?> 
                </button>` : '';

                }

                let botonEvento = '';

                botonEvento = `<button class="btn btn-sm btn-warning ms-3 btn-evento " 
                        data-vid="${grupo.id_vehicle}" 
                        data-fechas='${JSON.stringify(fechasCarga)}'>
                    <i class="fa-solid fa-stopwatch"></i> <?= Trd(53) ?>
                </button>`;

                // Fila de encabezado de grupo
                rows += `
            <tr class="table-light">
                <td colspan="5" class="py-3 ps-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-truck me-2 text-primary"></i>
                        <span class="fw-bold text-uppercase">
                            ${grupo.nombreVehiculo} 
                            <small class="text-muted ms-2">[${grupo.placas}]</small></br>
                            <?php echo Trd(38) ?>
                            <small class="text-muted ms-2">[${grupo.NombresChofer != null ? grupo.NombresChofer+' '+grupo.ApellidosChofer   : ''}]</small>                            
                        </span>
                        <?php if ($_SESSION['role_id'] == 'ADMIN' or $_SESSION['role_id'] == 'LOGISTICS') {
                            echo '${botonCargar}';
                        } ?>  
                        <?php if ($_SESSION['role_id'] == 'ADMIN' or $_SESSION['role_id'] == 'LOGISTICS') {
                            echo '${botonEliminar}';
                        } ?>  
                        <?php if ($_SESSION['role_id'] == 'ADMIN' or $_SESSION['role_id'] == 'LOGISTICS') {
                            echo '${botonChofer}';
                        } ?>  
                        ${botonEvento}

                        <span class="badge bg-secondary ms-auto me-4">
                            ${grupo.organizacion > 0 ? 'Org: ' + grupo.organizacion : 'Particular'}
                        </span>
                    </div>
                </td>
            </tr>
        `;

                // Filas de items (Misma lógica anterior)
                $.each(grupo.items, function(j, item) {
                    const statusClass = "status-" + item.Status.toLowerCase().replace(/\s/g, '');
                    const badgeClass = getBadgeColor(item.Status);

                    // Validamos si botonChofer tiene contenido
                    const isDisabled = (botonChofer != "" && item.Status == 'CARGA') ? "disabled" : "";
                    const iddisplay = (botonChofer == "" && (item.Status == 'CARGA' || item.Status == 'ENTREGA')) ? "" : "d-none";
                    const clickClass = isDisabled ? "" : "clickable-row";
                    const cursorStyle = isDisabled ? "default" : "pointer";

                    const isFirst = (j === 0);
                    const isLast = (j === grupo.items.length - 1)
                    // Definir el botón de imagen fuera para mantener limpio el template
                    let bottonImagen = (item.Status === 'EVENTO' && item.imagen && item.imagen.trim() !== "") ?
                        `<button type="button" class="btn btn-primary btn-sm ms-2" onclick="verImagen('${item.imagen}')">
            <i class="fa fa-camera"></i>
       </button>` :
                        '';

                    if (item.Status === 'EVENTO') {
                        rows += `
    <tr class="${statusClass} ${clickClass}" style="cursor: ${cursorStyle};">
        <!-- Columna 1: Folio/ID y Fecha -->
        <td class="ps-5">
            <div class="small text-muted">Evento extra #${item.id_event}</div>
            <div class="fw-semibold">${item.fechahora}</div>
        </td>
        <!-- Columna 2: Título y Descripción -->
        <td>
            <div class="fw-bold text-dark">${item.titulo}</div>
            <div class="small text-secondary">${item.descripcion}</div>
        </td>
        <!-- Columna 3: Controles de Orden y Acción -->
        <td>
            <div class="d-flex align-items-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" ${isFirst ? 'disabled' : ''} onclick="cambiarOrden(event, '${key}', ${j}, 'up')">
                        <i class="fa-solid fa-chevron-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" ${isLast ? 'disabled' : ''} onclick="cambiarOrden(event, '${key}', ${j}, 'down')">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>
                <button class="btn btn-sm btn-danger ms-3" onclick="prepararBorrado(${item.id_event})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
                ${bottonImagen}
            </div>
        </td>
        <!-- Columna 4: Status -->
        <td><span class="badge rounded-pill bg-info text-dark">EVENTO</span></td>
        <!-- Columna 5: Monto -->
        <td class="text-end pe-4 fw-bold text-dark">$${parseFloat(item.gasto).toFixed(2)}</td>
    </tr>`;

                    } else {
                        rows += `
    <tr class="${statusClass} ${clickClass}" data-id="${item.Id_operation}" data-status="${item.Status}" style="cursor: ${cursorStyle};">
        <!-- Columna 1: Folio/ID y Fecha -->
        <td class="ps-5">
            <div class="small text-muted">#${item.Folio}</div>
            <div class="fw-semibold">${item.DeliveryDateTime}</div>
        </td>
        <!-- Columna 2: Título y Descripción -->
        <td>
            <div class="fw-bold text-dark">${item.NombreMostrar}</div>
            <div class="small text-muted italic"><i class="fas fa-phone"></i> ${item.CPhone}</div>
            <div class="small text-muted italic">${item.Organization > 0 ? '<?php echo Trd(10) ?>' : '<?php echo Trd(11) ?>'}</div>
            <div class="small text-secondary">${item.Lugar} - ${item.Ciudad}</div>

            <!-- Notas 1 y 2 (se muestran solo si contienen texto) -->
            ${(item.Notas1 || item.Notas2) ? `
                <div class="small text-muted mt-1 bg-light p-1 rounded">
                    ${item.Notas1 ? `<div><strong>Nota 1:</strong> ${item.Notas1}</div>` : ''}
                    ${item.Notas2 ? `<div><strong>Nota 2:</strong> ${item.Notas2}</div>` : ''}
                </div>
            ` : ''}            

        </td>
        <!-- Columna 3: Controles de Orden y Acción -->
        <td>
            <div class="d-flex align-items-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" ${isFirst ? 'disabled' : ''} onclick="cambiarOrden(event, '${key}', ${j}, 'up')">
                        <i class="fa-solid fa-chevron-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" ${isLast ? 'disabled' : ''} onclick="cambiarOrden(event, '${key}', ${j}, 'down')">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="abrirAsignacion(event,'${item.StartDateTime}','${item.id_route}','${item.Id_operation}')">
                    <i class="fa-solid fa-up-down"></i> <?= Trd(35) ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-info ms-2 ${iddisplay}" onclick="abrirRutaEnMaps_(event, '${item.Lat}', '${item.Lng}')">
                    <i class="fa-solid fa-route"></i> <?= Trd(36) ?>
                </button>
            </div>
        </td>
        <!-- Columna 4: Status -->
        <td><span class="badge rounded-pill ${badgeClass}">${item.Status}</span></td>
        <!-- Columna 5: Monto -->
        <td class="text-end pe-4 fw-bold text-dark">$${formatCurrency(parseFloat(item.Balance).toFixed(2))}</td>
    </tr>`;
                    }
                    /*
                                // Filtramos todos los eventos que pertenecen a la ruta actual
                                const eventosDeEstaRuta = data_org.extra_events.filter(event => event.id_route == item.id_route);

                                // Iteramos solo sobre los resultados filtrados
                                eventosDeEstaRuta.forEach(evento => {
                                    //console.log("Evento encontrado:", evento);
                                    //alert("ID de ruta: " + evento.id_route);
                                    botton = '';
                                    if (evento.imagen && evento.imagen.trim() !== "") {
                                        botton += `
                                            <button type="button" 
                                                    class="btn btn-primary btn-sm" 
                                                    onclick="verImagen('${evento.imagen}')">
                                                <i class="fa fa-camera"></i>
                                            </button>`;
                                    }                

                                    rows += `
                                    <tr class="${statusClass} ${clickClass}"  style="cursor: ${cursorStyle};">
                                        <td class="ps-5">
                                            <div class="small text-muted">Evento extra #${evento.id_event}</div>
                                            <div class="fw-semibold">${evento.fechahora}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">${evento.titulo}</div>
                                        </td>
                                        <td >
                                            <div class="small text-secondary">${evento.descripcion}</div>
                                        </td>
                                        <td>
                                        
                                    <button class="btn btn-sm btn-danger ms-3" onclick="prepararBorrado(${evento.id_event})">
                                    
                                       <i class="fa-solid fa-trash-can"></i>
                                    </button>                    

                                            ${botton}
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-dark">$${parseFloat(evento.gasto).toFixed(2)}</td>
                                    </tr>`;    
                                });        
                    */
                });



            });

            $('#leadsData').append(rows);
        }

        // Mostrar vista previa de la foto seleccionada
        document.getElementById('eventFoto').onchange = function(evt) {
            const [file] = this.files;
            if (file) {
                const preview = document.getElementById('previewFoto');
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
        };

        $('#formEventoExtra').on('submit', function(e) {
            e.preventDefault();
            // 1. Crear el objeto FormData con los datos del formulario
            var formData = new FormData(this);
            formData.append('vehiculo', vehiculo_ev);
            formData.append('fecha', date_ev);
            // Si los campos no tienen el atributo "name", puedes agregarlos manualmente:
            // formData.append('titulo', $('#eventTitulo').val());
            // ... pero es mejor ponerle 'name' a los inputs en el HTML.

            $.ajax({

                url: API_BASE_URL + 'api/extra_event',
                method: 'POST',
                data: formData,
                headers: {
                    'Authorization': 'Bearer ' + TOKEN
                },
                cache: false,
                contentType: false, // Obligatorio para enviar archivos
                processData: false, // Obligatorio para enviar archivos
                dataType: 'json',
                beforeSend: function() {
                    // Opcional: Bloquear el botón para evitar múltiples clics
                    $('button[type="submit"]').prop('disabled', true).text('Enviando...');
                },
                success: function(data) {
                    // Asumiendo que tu PHP devuelve un JSON
                    //var data = JSON.parse(data);

                    if (data.status === 'success') {
                        //$('#modalEventoExtra').modal('hide');
                        //alert('Evento guardado con éxito');
                        // Aquí podrías recargar la tabla de la ruta o limpiar el form
                        //$('#formEventoExtra')[0].reset();
                        //$('#previewFoto').addClass('d-none');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                },
                error: function() {
                    alert('Hubo un error crítico en el servidor.');
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false).text('Registrar Evento');
                }
            });
        });

        function verImagen(url) {
            // 1. Asignamos la URL al src de la imagen dentro del modal
            $('#imgModal').attr('src', url);

            // 2. Abrimos el modal manualmente con jQuery
            $('#modalImagen').modal('show');
        }

        function prepararBorrado(id) {
            $('#idParaBorrar').val(id); // Guardamos el ID en el input oculto
            $('#modalConfirmarBorrado').modal('show');
        }

        // 2. Al hacer clic en el botón de eliminar del modal
        $('#btnConfirmarBorrado').click(function() {
            const id = $('#idParaBorrar').val();
            // Aquí tu petición AJAX
            $.ajax({
                url: API_BASE_URL + 'api/extra_event_delete',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + TOKEN
                },
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    //$('#modalConfirmarBorrado').modal('hide');
                    // Aquí puedes recargar la tabla o mostrar un mensaje de éxito
                    //alert("Registro eliminado con éxito");
                    location.reload();
                },
                error: function() {
                    alert("Error al eliminar el registro");
                }
            });
        });

        $('#btnConfirmarBorradoRuta').click(function() {
            let formData = new FormData();
            formData.append('vehiculoId', vehiculo_ev);
            formData.append('fecha', date_ev);
            $.ajax({
                url: API_BASE_URL + 'api/delete_route',
                method: 'POST',
                data: formData,
                headers: {
                    'Authorization': 'Bearer ' + TOKEN
                },
                processData: false, // Vital para FormData
                contentType: false, // Vital para FormData
                success: function(response) {
                    location.reload();
                },
                error: function() {}
            });


        });
    </script>


</body>

</html>
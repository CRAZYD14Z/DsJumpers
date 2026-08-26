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
$query = "select Traduccion FROM  programas_traduccion where Programa = 'leads' AND Idioma = ? ORDER BY Id";            
$stmt = $db->prepare($query);
$stmt->bindValue(1, $Idioma);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$Traducciones[]='';
if ($resultados) {
    foreach ($resultados as $registro) {
        $Traducciones[]=$registro['Traduccion'];
    }
}    
function Trd($Id){
    global $Traducciones;
    return $Traducciones[$Id];
}


include_once 'head.php';
?>
<style>
        body { background-color: #f4f7f6; }
        .sticky-search { position: sticky; top: 0; z-index: 1020; background: rgba(244, 247, 246, 0.95); padding: 20px 0; }
        .table-container { background: white; border-radius: 12px; overflow: hidden; }
        /* Bordes laterales de colores según status */
        .status-parcial { background: #6c757d !important; }        
        .status-cotizado { background: #17a2b8 !important; }        
        .status-confirmado { background: #28a745 !important; }
        .status-pendiente { background: #ffc107 !important; }
        .status-completado { background: #007bff !important; }
        .status-cancelado { background: #dc3545 !important; }

.clickable-row:hover {
    background-color: rgba(13, 110, 253, 0.05) !important; /* Un azul muy tenue */
    transition: background-color 0.2s ease;
}

/* Estilos para la barra flotante de eliminación */
.delete-action-bar {
    position: fixed;
    bottom: -80px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 14px 28px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.1) inset;
    display: flex;
    align-items: center;
    gap: 16px;
    z-index: 1050;
    transition: bottom 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    backdrop-filter: blur(12px);
}
.delete-action-bar.show {
    bottom: 30px;
}
.delete-action-bar .btn-delete-leads {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border: none;
    color: white;
    padding: 8px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    transition: all 0.2s ease;
}
.delete-action-bar .btn-delete-leads:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}
.delete-action-bar .btn-cancel-selection {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}
.delete-action-bar .btn-cancel-selection:hover {
    background: rgba(255,255,255,0.25);
}
.delete-action-bar .selected-count {
    font-size: 0.92rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
.delete-action-bar .selected-count .count-badge {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
}

/* Checkbox styling */
.lead-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #3b82f6;
}
.lead-checkbox-cell {
    width: 40px;
    text-align: center;
}
.clickable-row.selected-row {
    background-color: rgba(59, 130, 246, 0.08) !important;
}

    </style>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Idioma Español para Flatpickr (opcional pero recomendado) -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

</head>
<body>

<?php
    include_once 'nav.php';
?>
<br>
<br>



<div class="container-fluid py-4 px-md-5">
    <!-- Botón para móviles (solo visible en pantallas pequeñas) -->
    <div class="d-lg-none mb-3">
        <button class="btn btn-primary w-100" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarFilters" aria-controls="sidebarFilters">
            <i class="bi bi-sliders me-2"></i> <?php echo Trd(17)?>
        </button>
    </div>

    <div class="row">
        <!-- SIDEBAR DE FILTROS (Comportamiento híbrido: Fijo en Desktop, Desplegable en Móvil) -->
        <div class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start border-end h-100" tabindex="-1" id="sidebarFilters" aria-labelledby="sidebarFiltersLabel">
                <div class="offcanvas-header bg-light d-lg-none border-bottom">
                    <h5 class="offcanvas-title fw-bold" id="sidebarFiltersLabel"><i class="bi bi-sliders me-2"></i> <?php echo Trd(18)?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarFilters" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-3 p-lg-0 pe-lg-4">
                    <div class="card shadow-sm border-0 w-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4 d-none d-lg-block"><i class="bi bi-sliders me-2 text-primary"></i> <?php echo Trd(19)?></h5>
                            
                            <!-- Filtro de Texto -->
<!-- Filtro de Texto y Sugerencias -->
<div class="mb-4 position-relative">
    <label for="txtSearch" class="form-label small fw-bold text-secondary"><?php echo Trd(20)?></label>
    <div class="input-group">
        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
        <!-- Agregamos autocomplete="off" para evitar que el navegador interfiera -->
        <input type="text" id="txtSearch" class="form-control border-start-0" placeholder="<?php echo Trd(30)?>" autocomplete="off">
    </div>

    <!-- Dropdown de Filtros Predeterminados (Aparece al hacer focus) -->
    <div id="suggestionsDropdown" class="dropdown-menu shadow w-100 mt-1" style="display: none; position: absolute; z-index: 1000; max-height: 250px; overflow-y: auto;">
        <h6 class="dropdown-header text-primary fw-bold"><?php echo Trd(21)?></h6>
        <!-- Puedes definir aquí los filtros rápidos que desees -->
        <a class="dropdown-item preset-filter-item" href="#" data-field="Status" data-value="confirmed" data-label="Estado: Confirmado">
            <i class="bi bi-check-circle-fill text-success me-2"></i> <?php echo Trd(22)?>
        </a>
        <a class="dropdown-item preset-filter-item" href="#" data-field="Status" data-value="draft" data-label="Estado: Pendiente">
            <i class="bi bi-exclamation-circle-fill text-warning me-2"></i> <?php echo Trd(23)?>
        </a>
        <a class="dropdown-item preset-filter-item" href="#" data-field="Status" data-value="canceled" data-label="Estado: Cancelado">
            <i class="bi bi-x-circle-fill text-danger me-2"></i> <?php echo Trd(24)?>
        </a>
        <div class="dropdown-divider"></div>
        <h6 class="dropdown-header text-primary fw-bold"><?php echo Trd(25)?></h6>
        <a class="dropdown-item preset-filter-item" href="#" data-field="Product" data-value="Mesa" data-label="Producto: Mesa">
            <i class="bi bi-box-seam me-2"></i> Buscar Mesas
        </a>
        <a class="dropdown-item preset-filter-item" href="#" data-field="Product" data-value="Silla" data-label="Producto: Silla">
            <i class="bi bi-box-seam me-2"></i> Buscar Sillas
        </a>
    </div>
</div>

<!-- Contenedor de Filtros Activos (Se llenará dinámicamente) -->
<div id="activeFiltersContainer" class="mb-4 d-flex flex-wrap gap-2"></div>

                            <!-- Filtro de Rango de Fechas (Unificado) -->
                            <div class="mb-4">
                                <label for="dateRange" class="form-label small fw-bold text-secondary"><?php echo Trd(26)?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-range text-muted"></i></span>
                                    <input type="text" id="dateRange" class="form-control border-start-0" placeholder="<?php echo Trd(31)?>">
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-grid gap-2">
                                <button type="button" id="btnApplyFilters" class="btn btn-primary d-lg-none" data-bs-dismiss="offcanvas">
                                    <?php echo Trd(27)?>
                                </button>
                                <button type="button" id="btnClearFilters" class="btn btn-outline-secondary">
                                    <i class="bi bi-eraser-fill me-2"></i> <?php echo Trd(28)?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE LA TABLA -->
        <div class="col-lg-9">
            <div class="table-container shadow-sm border">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th class="lead-checkbox-cell"><input type="checkbox" id="selectAllLeads" class="lead-checkbox" title="Seleccionar todos"></th>
                                <th class="ps-4"><?php echo Trd(2)?></th>
                                <th class="ps-4"><?php echo Trd(12)?></th>
                                <th><?php echo Trd(3)?></th>
                                <th><?php echo Trd(4)?></th>
                                <th><?php echo Trd(5)?></th>
                                <th class="text-end pe-4"><?php echo Trd(13)?></th>
                                <th class="text-end pe-4"><?php echo Trd(6)?></th>
                            </tr>
                        </thead>
                        <tbody id="leadsData">
                            <!-- Datos generados dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="loadingIndicator" class="text-center my-4" style="display:none;">
                <div class="spinner-grow text-primary" role="status"></div>
                <p class="text-muted small"><?php echo Trd(7)?></p>
            </div>
        </div>
    </div>
</div>

<!-- Barra flotante de eliminación -->
<div id="deleteActionBar" class="delete-action-bar">
    <span class="selected-count">
        <i class="bi bi-check2-square"></i>
        <span id="selectedCountText">0 seleccionados</span>
    </span>
    <button type="button" class="btn btn-delete-leads" id="btnDeleteLeads">
        <i class="bi bi-trash3 me-1"></i> Eliminar
    </button>
    <button type="button" class="btn btn-cancel-selection" id="btnCancelSelection">
        <i class="bi bi-x-lg me-1"></i> Cancelar
    </button>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body text-center p-4 pt-5">
                <div style="width: 72px; height: 72px; background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="bi bi-trash3" style="font-size: 1.8rem; color: #ef4444;"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Eliminar leads seleccionados?</h5>
                <p class="text-muted mb-0" id="deleteConfirmMessage">Se eliminarán 0 leads. Esta acción se puede revertir.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                <button type="button" class="btn btn-light px-4" style="border-radius: 10px; font-weight: 500;" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn px-4" id="btnConfirmDelete" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 10px; font-weight: 600;">
                    <i class="bi bi-trash3 me-1"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resultado -->
<div class="modal fade" id="deleteResultModal" tabindex="-1" aria-labelledby="deleteResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body text-center p-4 pt-5">
                <div id="resultIconContainer" style="width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;"></div>
                <h5 class="fw-bold mb-2" id="deleteResultTitle"></h5>
                <p class="text-muted mb-0" id="deleteResultMessage"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-primary px-4" style="border-radius: 10px; font-weight: 500;" data-bs-dismiss="modal">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>

    

    <div id="loadingIndicator" class="text-center my-4" style="display:none;">
        <div class="spinner-grow text-primary" role="status"></div>
        <p class="text-muted small"><?php echo Trd(7)?></p>
    </div>


<script>

    <?php
    include_once 'js_scripts.php';
    ?>
$(document).ready(function() {
    let currentPage = 1;
    let isFetching = false;
    let noMoreData = false;
    let timer;
    let flatpickrInstance;

    // Variables de filtros principales
    let dateFromVal = '';
    let dateToVal = '';
    
    // Array para almacenar los filtros predeterminados seleccionados
    // Estructura: { id: uniqueTimestamp, field: 'status', value: 'Confirmado', label: 'Estado: Confirmado' }
    let activePresetFilters = [];

    // 1. Inicializar Flatpickr en modo Rango
    flatpickrInstance = flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        locale: "es",
        onClose: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                dateFromVal = instance.formatDate(selectedDates[0], "Y-m-d");
                dateToVal = instance.formatDate(selectedDates[1], "Y-m-d");
                fetchLeads(true);
            } else if (selectedDates.length === 0) {
                dateFromVal = '';
                dateToVal = '';
                fetchLeads(true);
            }
        }
    });

    // --- LÓGICA DE FILTROS PREDETERMINADOS ---

    // Mostrar dropdown al hacer focus
    $('#txtSearch').on('focus', function() {
        $('#suggestionsDropdown').fadeIn(200);
    });

    // Ocultar dropdown si se hace click fuera del input o del dropdown
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#txtSearch, #suggestionsDropdown').length) {
            $('#suggestionsDropdown').fadeOut(200);
        }
    });

    // Al seleccionar un filtro predeterminado
    $(document).on('click', '.preset-filter-item', function(e) {
        e.preventDefault();
        
        const field = $(this).data('field');
        const value = $(this).data('value');
        const label = $(this).data('label');
        const uniqueId = Date.now(); // ID único para poder borrarlo individualmente

        // Evitar duplicados del mismo filtro exacto
        const duplicate = activePresetFilters.some(f => f.field === field && f.value === value);
        if (!duplicate) {
            activePresetFilters.push({ id: uniqueId, field: field, value: value, label: label });
            renderActiveFilters();
            fetchLeads(true); // Refrescar consulta
        }

        $('#txtSearch').val(''); // Limpiar el input para que puedan seguir buscando otra cosa
        $('#suggestionsDropdown').fadeOut(200);
    });

    // Renderizar los "Badges/Cards" de los filtros activos
    function renderActiveFilters() {
        const container = $('#activeFiltersContainer');
        container.empty();

        $.each(activePresetFilters, function(index, filter) {
            const badgeHtml = `
                <div class="badge bg-light text-dark border d-flex align-items-center gap-2 p-2 rounded shadow-sm animate__animated animate__fadeIn" style="font-size: 0.85rem;">
                    <span><strong>${filter.label}</strong></span>
                    <button type="button" class="btn-close btn-remove-preset-filter" data-id="${filter.id}" style="font-size: 0.65rem; padding: 0.25rem;" aria-label="Eliminar"></button>
                </div>
            `;
            container.append(badgeHtml);
        });
    }

    // Evento para borrar un filtro predeterminado individualmente
    $(document).on('click', '.btn-remove-preset-filter', function() {
        const idToRemove = $(this).data('id');
        // Filtrar el array para remover el seleccionado
        activePresetFilters = activePresetFilters.filter(f => f.id !== idToRemove);
        renderActiveFilters();
        fetchLeads(true); // Refrescar consulta
    });

    // --- FIN DE FILTROS PREDETERMINADOS ---


    // 2. Función Principal con AJAX adaptada para múltiples filtros
    function fetchLeads(reset = false) {
        if (isFetching || (noMoreData && !reset)) return;

        if (reset) {
            currentPage = 1;
            noMoreData = false;
            $('#leadsData').empty();
        }

        isFetching = true;
        $('#loadingIndicator').fadeIn();

        const searchVal = $('#txtSearch').val();

        $.ajax({
            url:  API_BASE_URL + 'leads/',
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            // Enviamos tanto el texto libre, como las fechas, como el listado serializado de filtros predeterminados
            data: {
                page: currentPage,
                search: searchVal,
                date_from: dateFromVal,
                date_to: dateToVal,
                presets: JSON.stringify(activePresetFilters) // Enviamos los presets como JSON al Backend
            },
            success: function(response) {
                if (response.length === 0) {
                    noMoreData = true;
                    if (currentPage === 1) {
                        $('#leadsData').html('<tr><td colspan="8" class="text-center py-5 text-muted"><?php echo Trd(8)?></td></tr>');
                    }
                } else {
                    renderTable(response);
                    currentPage++;
                }
            },
            error: function(xhr, status, error) {
                //console.error("Error en la petición:", error);
                //alert("<?php echo Trd(9)?>");
            },
            complete: function() {
                isFetching = false;
                $('#loadingIndicator').fadeOut();
            }
        });
    }

    // Renderizar filas
    function renderTable(data) {
        let rows = '';
        $.each(data, function(i, item) {
            const statusClass = "status-" + item.Status.toLowerCase().replace(/\s/g, '');
            const badgeClass = getBadgeColor(item.Status);
            
            const isCanceled = item.Status.toLowerCase().includes('cancelado') || item.Status.toLowerCase().includes('cancel');
            const cancellationHtml = (isCanceled && item.CancellationReason) 
                ? `<div class="small text-danger mt-1" style="font-size: 0.75rem; max-width: 180px; white-space: normal; line-height: 1.2;" title="Motivo de cancelación: ${item.CancellationReason}">
                    <strong><?php echo Trd(14)?>:</strong> ${item.CancellationReason}
                   </div>` 
                : '';
            
            rows += `
                <tr class="${statusClass} clickable-row" data-id="${item.Id}" style="cursor: pointer;">
                    <td class="lead-checkbox-cell" onclick="event.stopPropagation();">
                        <input type="checkbox" class="lead-checkbox lead-row-check" value="${item.Id}" onclick="event.stopPropagation();">
                    </td>
                    <td class="ps-4">
                        <div class="fw-semibold">#${item.Folio}</div>
                        <div class="small text-muted">${item.FechaCreacion}</div>
                    </td>
                    <td class="ps-4">
                        <div class="small text-muted italic">${item.StartDateTime}</div>
                        <div class="small text-muted italic">${item.EndDateTime}</div>
                    </td>                    
                    <td>
                        <div class="fw-bold text-dark">${item.NombreMostrar == 'Sin identificar' ? '<?php echo Trd(29)?>' : item.NombreMostrar}</div>
                        <div class="small text-muted italic">${item.Organization > 0 ? '<?php echo Trd(10)?>' : '<?php echo Trd(11)?>'}</div>
                    </td>
                    <td>
                        <div class="small text-secondary">${item.DeliveryDateTime}</div>
                        <div class="small text-secondary">${item.Lugar}</div>
                        <div class="small text-secondary">${item.Ciudad}, ${item.Estado}</div>
                    </td>
                    <td>
                        <span class="badge rounded-pill ${badgeClass}">${item.Status}</span>
                        ${cancellationHtml}
                    </td>
                    <td class="text-end pe-4 fw-bold text-dark">$${formatCurrency(parseFloat(item.Balance).toFixed(2))}</td>
                    <td class="text-end pe-4 fw-bold text-dark">$${formatCurrency(parseFloat(item.Total).toFixed(2))}</td>
                </tr>
            `;
        });
        $('#leadsData').append(rows);
    }

    function getBadgeColor(status) {
        const s = status.toLowerCase();
        if (s.includes('parcial') || s.includes('draft') ) return 'status-parcial';
        if (s.includes('cotizado') || s.includes('quoted')) return 'status-cotizado';
        if (s.includes('confirmado') || s.includes('confirmed')) return 'status-confirmado';
        if (s.includes('pendiente') || s.includes('pending')) return 'status-pendiente';
        if (s.includes('completo') || s.includes('complete')) return 'status-completo';
        if (s.includes('cancelado') || s.includes('canceled')) return 'status-cancelado';
        return 'text-bg-secondary';
    }

    // Evento Scroll
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
            fetchLeads();
        }
    });

    // Evento de Búsqueda por texto (Debounce)
    $('#txtSearch').on('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            fetchLeads(true);
        }, 500);
    });

    // Botón para limpiar TODOS los filtros
    $('#btnClearFilters').on('click', function() {
        $('#txtSearch').val('');
        flatpickrInstance.clear();
        dateFromVal = '';
        dateToVal = '';
        activePresetFilters = []; // Vaciar filtros predeterminados
        renderActiveFilters();
        fetchLeads(true);
    });

    // Redirección
    $('#leadsData').on('click', '.clickable-row', function() {
        const leadId = $(this).data('id');
        if (leadId) {
            window.location.href = `lead.php?IdLead=${leadId}`;
        }
    });    

    fetchLeads();

    // --- LÓGICA DE SELECCIÓN Y ELIMINACIÓN DE LEADS ---

    // Actualizar la barra flotante según checkboxes seleccionados
    function updateDeleteBar() {
        const checked = $('.lead-row-check:checked');
        const count = checked.length;
        if (count > 0) {
            $('#selectedCountText').text(count + ' seleccionado' + (count > 1 ? 's' : ''));
            $('#deleteActionBar').addClass('show');
        } else {
            $('#deleteActionBar').removeClass('show');
        }
        // Sincronizar el checkbox "select all"
        const total = $('.lead-row-check').length;
        $('#selectAllLeads').prop('checked', total > 0 && count === total);
        $('#selectAllLeads').prop('indeterminate', count > 0 && count < total);

        // Marcar/desmarcar estilo de fila
        $('.lead-row-check').each(function() {
            $(this).closest('tr').toggleClass('selected-row', $(this).is(':checked'));
        });
    }

    // Checkbox individual
    $(document).on('change', '.lead-row-check', function(e) {
        updateDeleteBar();
    });

    // Seleccionar todos
    $('#selectAllLeads').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.lead-row-check').prop('checked', isChecked);
        updateDeleteBar();
    });

    // Cancelar selección
    $('#btnCancelSelection').on('click', function() {
        $('.lead-row-check').prop('checked', false);
        $('#selectAllLeads').prop('checked', false).prop('indeterminate', false);
        updateDeleteBar();
    });

    // Eliminar leads seleccionados - Abrir modal de confirmación
    $('#btnDeleteLeads').on('click', function() {
        const count = $('.lead-row-check:checked').length;
        if (count === 0) return;
        $('#deleteConfirmMessage').text('Se eliminarán ' + count + ' lead' + (count > 1 ? 's' : '') + '. Esta acción se puede revertir.');
        new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
    });

    // Confirmar eliminación desde el modal
    $('#btnConfirmDelete').on('click', function() {
        const selectedIds = [];
        $('.lead-row-check:checked').each(function() {
            selectedIds.push(parseInt($(this).val()));
        });

        if (selectedIds.length === 0) return;

        // Cerrar modal de confirmación
        bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();

        $.ajax({
            url: API_BASE_URL + 'leads/',
            type: 'DELETE',
            dataType: 'json',
            contentType: 'application/json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: JSON.stringify({ ids: selectedIds }),
            success: function(response) {
                if (response.status === 'success') {
                    // Todos eliminados exitosamente
                    selectedIds.forEach(function(id) {
                        $('tr[data-id="' + id + '"]').fadeOut(300, function() {
                            $(this).remove();
                            updateDeleteBar();
                        });
                    });
                    showResultModal('success', 'Leads eliminados', response.message || 'Los leads seleccionados fueron eliminados correctamente.');
                } else if (response.status === 'partial') {
                    // Algunos eliminados, otros bloqueados por pagos
                    const blockedIds = response.blocked_ids || [];
                    // Remover solo los que SÍ se eliminaron
                    selectedIds.forEach(function(id) {
                        if (!blockedIds.includes(id)) {
                            $('tr[data-id="' + id + '"]').fadeOut(300, function() {
                                $(this).remove();
                                updateDeleteBar();
                            });
                        }
                    });
                    // Deseleccionar los checkboxes de los bloqueados
                    blockedIds.forEach(function(id) {
                        $('tr[data-id="' + id + '"] .lead-row-check').prop('checked', false);
                    });
                    updateDeleteBar();

                    let msg = response.message;
                    if (response.deleted > 0) {
                        msg = response.deleted + ' lead(s) eliminado(s). ' + msg;
                    }
                    showResultModal('warning', 'Eliminación parcial', msg);
                } else {
                    showResultModal('error', 'Error', response.message || 'Error al eliminar los leads.');
                }
            },
            error: function() {
                showResultModal('error', 'Error de conexión', 'No se pudo conectar con el servidor. Intenta de nuevo.');
            }
        });
    });

    // Función helper para mostrar el modal de resultado
    function showResultModal(type, title, message) {
        const iconContainer = $('#resultIconContainer');
        let iconHtml = '';
        if (type === 'success') {
            iconContainer.css('background', 'linear-gradient(135deg, #f0fdf4, #dcfce7)');
            iconHtml = '<i class="bi bi-check-circle" style="font-size: 1.8rem; color: #22c55e;"></i>';
        } else if (type === 'warning') {
            iconContainer.css('background', 'linear-gradient(135deg, #fffbeb, #fef3c7)');
            iconHtml = '<i class="bi bi-exclamation-triangle" style="font-size: 1.8rem; color: #f59e0b;"></i>';
        } else {
            iconContainer.css('background', 'linear-gradient(135deg, #fef2f2, #fee2e2)');
            iconHtml = '<i class="bi bi-x-circle" style="font-size: 1.8rem; color: #ef4444;"></i>';
        }
        iconContainer.html(iconHtml);
        $('#deleteResultTitle').text(title);
        $('#deleteResultMessage').text(message);
        new bootstrap.Modal(document.getElementById('deleteResultModal')).show();
    }

    // --- FIN DE LÓGICA DE ELIMINACIÓN ---
});


   



</script>


</body>
</html>
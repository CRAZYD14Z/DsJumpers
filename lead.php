<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
//$_SESSION['Idioma'];
$lang ='es';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración con Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>        

    <link rel="stylesheet" href="css/lead.css" />

<style>
        @media only screen and (max-width: 600px) {
            .responsive-table { width: 100% !important; }
            .stack-column { display: block !important; width: 100% !important; box-sizing: border-box; border: none !important; }
            .item-description { width: auto !important; }
            .policy-columns { column-count: 1 !important; }
            .signature-box { width: 100% !important; margin-bottom: 20px; }
            .total-table { width: 100% !important; }
        }
    </style>    

</head>
<body >
<?php
    include_once 'nav.php';
?>
<div class="container-fluid px-3">
    <?php
        if (isset($_GET['IdLead']) AND $_GET['IdLead'] > 0 ){
            $IdLead = $_GET['IdLead'];
            $query = "select * FROM lead WHERE Id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $IdLead);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($lead) {
                $query = "select * FROM lead_detail WHERE IdLead = $IdLead ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $lead_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($lead_details) {
                }        
                $query = "select * FROM lead_discounts WHERE IdLead = $IdLead ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $lead_discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($lead_discounts) {

                }                
            }
        }



        include_once 'lead_grid.php';
        include_once 'lead_customer.php';
        include_once 'lead_venues.php';
        include_once 'bottom.php';
    ?>



<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="modalContratoLabel" aria-hidden="true" style="z-index: 2000;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 0; border: none;">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalContratoLabel">Visualización de Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div id="Contract">

                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-dark btn-sm" onclick="generarPDFContrato()">
                    <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                </button>
            </div>
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


</div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>        
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>    


    <script>

    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
    const TOKEN = localStorage.getItem('apiToken'); 

    let Row = 0;
    let Rltpc = 0;
    let TrDsc = 0;

    //AUTO GUARDADO !!
    // Variable para controlar el tiempo de espera (debounce)
    let autoSaveTimer;
    let autoSaveTimerQuant;    


    class ProductCounter {
        constructor() {
            this.cart = {};
        }

        // Verificar si un producto ya está en la lista
        hasProduct(id) {
            return Object.prototype.hasOwnProperty.call(this.cart, id);
        }

        // Recuperar cantidad
        getQuantity(id) {
            return this.hasProduct(id) ? this.cart[id].quantity : 0;
        }
        getInventory(id) {
            return this.hasProduct(id) ? this.cart[id].inventory : 0;
        }

        // Eliminar el producto completamente
        remove(id) {
            if (this.hasProduct(id)) {
            const name = this.cart[id].name;
            delete this.cart[id];
            //console.log(`"${name}" ha sido eliminado totalmente.`);
            this.debugInventory();
            return true;
            }
            return false;
        }

        // Métodos anteriores mejorados con hasProduct
        add(id, name, amount = 1, invent = 0) {
            if (this.hasProduct(id)) {
                this.cart[id].quantity += amount;
            } else {
                this.cart[id] = { name, quantity: amount, inventory: invent };
            }
            this.debugInventory();
        }

        subtract(id, amount = 1) {
            if (!this.hasProduct(id)) return;

            this.cart[id].quantity -= amount;
            if (this.cart[id].quantity <= 0) {
            this.remove(id);
            }
            this.debugInventory();
        }

        showSummary() {
            return Object.entries(this.cart).map(([id, data]) => ({ id, ...data }));
        }

        // --- Método de Debugging ---
        debugInventory() {
            //console.log("%c--- ESTADO DEL INVENTARIO SELECCIONADO ---", "color: #007bff; font-weight: bold;");
            
            if (Object.keys(this.cart).length === 0) {
            //console.log("El carrito está vacío.");
            } else {
            // Formateamos los datos para que la tabla sea clara
            const dataToPrint = Object.entries(this.cart).map(([id, data]) => ({
                ID: id,
                Producto: data.name,
                Cantidad: data.quantity,
                Inventario: data.inventory
            }));
            
            //console.table(dataToPrint);
            
            const total = dataToPrint.reduce((acc, item) => acc + item.Cantidad, 0);
            //console.log(`Total de artículos: ${total}`);
            }
            //console.log("------------------------------------------");
        }        

    }



    //let Codes = [];
    const inventario = new ProductCounter();

        $(document).ready(function() {

            attemptLogin('admin', '1234'); 
            
            if (TOKEN) {
                //getRecordData(1); 
            } else {
                console.warn('No se encontró el token. Necesita iniciar sesión primero.');
            }        

            $('.select-auto').each(function() {
                    $(this).select2({
                        theme: "bootstrap-5",
                        width: '100%',
                        placeholder: $(this).data('placeholder') || "Selecciona una opción",
                        allowClear: true
                    });
            });



            $('#Referal').select2({
                theme: "bootstrap-5",
                width: '100%',
                allowClear: true,
                selectOnClose: true,
                ajax: {
                    url:  API_BASE_URL+"get_referals/", // URL de tu Web Service
                    dataType: 'json',
                    headers: {
                        // *** Aquí se adjunta el token en el encabezado Authorization ***
                        'Authorization': 'Bearer ' + TOKEN 
                    }, 
                    delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
                    data: function (params) {
                        return {
                            q: params.term // El texto que el usuario escribió
                        };
                    },
                    processResults: function (data) {
                        // 'data' es la respuesta de tu WS. 
                        // Debes retornar un objeto con la propiedad 'results'.
                        return {
                            results: data.items.map(function(item) {
                                return {
                                    id: item.Id,
                                    text: item.Nombre, // Primera fila (Nombre)
                                    direccion: item.Direccion // Segunda fila (Dirección)
                                };
                            })
                        };
                    },
                    cache: true
                },
                templateResult: formatResult,   // Cómo se ve en la lista desplegable
                templateSelection: formatRepo   // Cómo se ve cuando ya se seleccionó
            });                



    var $select = $('#Organization').select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        selectOnClose: true,
        placeholder: '',
        tags: true, // Permite crear nuevos
        tokenSeparators: [',', '\n'], // Ayuda a que detecte el "Enter" como selección
        ajax: {
            url: API_BASE_URL + "get_organization/",
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.items.map(function(item) {
                        return {
                            id: item.Id,
                            text: item.Nombre,
                            direccion: item.Direccion
                        };
                    })
                };
            }
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        templateResult: formatResult,
        templateSelection: formatRepo
    });

    // USA ESTE MÉTODO PARA CAPTURAR LA SELECCIÓN
    $select.on('select2:select', function (e) {
        var data = e.params.data;
        
        //console.log("Seleccionado:", data); // Mira la consola de F12
        
        if (data.newTag) {
            //alert("Detectado nuevo tag: " + data.text);
            registrarOrganizacion(data.text);
        } else {
            // Si no es nuevo, ejecuta tu función normal
            if (typeof load_organization === "function") {
                load_organization(data.id);
            }
        }
    });

    function registrarOrganizacion(nombreNuevo) {
        $.ajax({
            url: API_BASE_URL + "save_organization/",
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json' 
            },
            data: JSON.stringify({ nombre: nombreNuevo }),
            success: function (response) {
                // Importante: La API debe retornar el ID asignado
                // response = { id: 500, nombre: 'Empresa Nueva' }
                
                // Reemplazamos el tag de texto por la opción real con su ID numérico
                var newOption = new Option(response.nombre, response.id, true, true);
                $('#Organization').find('option[value="' + nombreNuevo + '"]').remove();
                $('#Organization').append(newOption).trigger('change');
                
                $('#IdOrganization').val(response.id)

                //console.log("Registrado con éxito!");
                lanzarMensaje("¡Organización registrada con éxito!", "exito", 5000);
            },
            error: function () {
                alert("No se pudo guardar la organización.");
                $('#Organization').val(null).trigger('change');
            }
        });
    }    


    function triggerAutoSave() {
        if ($('#IdOrganization').val() > 0 || $('#IdCustomer').val() > 0){
            // Obtenemos el formulario y lo convertimos a un objeto plano
            const formArray = $('#customers').serializeArray();
            const formData = {};
            
            $.map(formArray, function(n, i){
                formData[n['name']] = n['value'];
            });
            URL_DESTINO = '';
            // Añadimos metadatos si es necesario
            if ($('#IdOrganization').val() > 0 ){
                formData['action'] = 'autosave_organization';
                URL_DESTINO = 'save_organization/"';
            }
            else{
                formData['action'] = 'autosave_customer';
                URL_DESTINO = 'save_customer/"';
            }
            $.ajax({
                url: API_BASE_URL + URL_DESTINO,
                type: 'PUT',
                contentType: 'application/json',
                    headers: { 
                        'Authorization': 'Bearer ' + TOKEN,
                        'Content-Type': 'application/json' 
                    },        
                data: JSON.stringify(formData), // Enviamos como JSON para que el PHP lo lea fácil
                success: function(response) {
                    //const res = typeof response === 'string' ? JSON.parse(response) : response;
                    // Si el servidor nos devuelve un ID nuevo (porque el cliente no existía)
                    //if (res.newIdCustomer) {
                    //    $('#IdCustomer').val(res.newIdCustomer);
                    //}
                    lanzarMensaje("¡Registro actualizado con éxito!", "exito", 5000);
                },
                error: function(xhr, status, error) {
                    console.error('Error en el autoguardado:', error);
                }
            });
        }
    }    


    $('#customers select, #customers input[type="checkbox"]').on('change', function() {
        triggerAutoSave();
    });

    // 2. Para inputs de texto y textareas (con delay de 1.5 segundos para no agobiar)
    $('#customers input[type="text"], #customers input[type="email"], #CustomerNote').on('keyup', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(triggerAutoSave, 1500);
    });




    var $selectcustomer = $('#Customer').select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        selectOnClose: true,
        placeholder: '',
        tags: true, // Permite crear nuevos
        tokenSeparators: [',', '\n'], // Ayuda a que detecte el "Enter" como selección
        ajax: {
            url:  API_BASE_URL+"get_customers/", // URL de tu Web Service
            dataType: 'json',
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            }, 
            delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
            data: function (params) {
                return {
                    q: params.term // El texto que el usuario escribió
                };
            },
            processResults: function (data) {
                // 'data' es la respuesta de tu WS. 
                // Debes retornar un objeto con la propiedad 'results'.
                return {
                    results: data.items.map(function(item) {
                        return {
                            id: item.Id,
                            text: item.Nombre, // Primera fila (Nombre)
                            direccion: item.Direccion // Segunda fila (Dirección)
                        };
                    })
                };
            },
            cache: true
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        templateResult: formatResult,   // Cómo se ve en la lista desplegable
        templateSelection: formatRepo   // Cómo se ve cuando ya se seleccionó
    });

    $selectcustomer.on('select2:select', function (e) {
        var data = e.params.data;
        
        //console.log("Seleccionado:", data); // Mira la consola de F12
        
        if (data.newTag) {
            //alert("Detectado nuevo tag: " + data.text);
            registrarCustomer(data.text);
        } else {
            // Si no es nuevo, ejecuta tu función normal
            if (typeof load_customer === "function") {
                load_customer(data.id);
            }
        }
    });


    function registrarCustomer(nombreNuevo) {
        $.ajax({
            url: API_BASE_URL + "save_customer/",
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json' 
            },
            data: JSON.stringify({ nombre: nombreNuevo }),
            success: function (response) {
                // Importante: La API debe retornar el ID asignado
                // response = { id: 500, nombre: 'Empresa Nueva' }
                
                // Reemplazamos el tag de texto por la opción real con su ID numérico
                var newOption = new Option(response.nombre, response.id, true, true);
                $('#Customer').find('option[value="' + nombreNuevo + '"]').remove();
                $('#Customer').append(newOption).trigger('change');
                
                $('#IdCustomer').val(response.id)

                //console.log("Registrado con éxito!");
                lanzarMensaje("¡Cliente registradao con éxito!", "exito", 5000);
            },
            error: function () {
                //alert("No se pudo guardar el cliente.");
                lanzarMensaje("¡No se pudo guardar el cliente.!", "error", 5000);
                $('#Customer').val(null).trigger('change');
            }
        });
    }      
    


    


    var $selectvenue = $('#Venue').select2({
        theme: "bootstrap-5",
        width: '100%',
        allowClear: true,
        selectOnClose: true,
        placeholder: '',
        tags: true, // Permite crear nuevos
        tokenSeparators: [',', '\n'], // Ayuda a que detecte el "Enter" como selección          
        ajax: {
            url:  API_BASE_URL+"get_venues/", // URL de tu Web Service
            dataType: 'json',
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
            data: function (params) {
                return {
                    q: params.term // El texto que el usuario escribió
                };
            },
            processResults: function (data) {
                // 'data' es la respuesta de tu WS. 
                // Debes retornar un objeto con la propiedad 'results'.
                return {
                    results: data.items.map(function(item) {
                        return {
                            id: item.Id,
                            text: item.Nombre, // Primera fila (Nombre)
                            direccion: item.Direccion // Segunda fila (Dirección)
                        };
                    })
                };
            },
            cache: true
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                newTag: true
            };
        },        
        templateResult: formatResult,   // Cómo se ve en la lista desplegable
        templateSelection: formatRepo   // Cómo se ve cuando ya se seleccionó
    });              

    $selectvenue.on('select2:select', function (e) {
        var data = e.params.data;
        
        //console.log("Seleccionado:", data); // Mira la consola de F12
        
        if (data.newTag) {
            //alert("Detectado nuevo tag: " + data.text);
            registrarVenue(data.text);
        } else {
            // Si no es nuevo, ejecuta tu función normal
            if (typeof load_venue === "function") {
                load_venue(data.id);
            }
        }
    });


    function registrarVenue(nombreNuevo) {
        $.ajax({
            url: API_BASE_URL + "save_venue/",
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json' 
            },
            data: JSON.stringify({ nombre: nombreNuevo }),
            success: function (response) {
                // Importante: La API debe retornar el ID asignado
                // response = { id: 500, nombre: 'Empresa Nueva' }
                
                // Reemplazamos el tag de texto por la opción real con su ID numérico
                var newOption = new Option(response.nombre, response.id, true, true);
                $('#Venue').find('option[value="' + nombreNuevo + '"]').remove();
                $('#Venue').append(newOption).trigger('change');
                
                $('#IdVenue').val(response.id)

                //console.log("Registrado con éxito!");
                lanzarMensaje("¡Lugar de evento registrado con éxito!", "exito", 5000);
            },
            error: function () {
                //alert("No se pudo guardar el ligar del evento.");
                lanzarMensaje("¡No se pudo guardar el lugar del evento.!", "error", 5000);
                $('#Venue').val(null).trigger('change');
            }
        });
    }        



    function triggerAutoSaveVenue() {
        if ($('#IdVenue').val() > 0 ){
            // Obtenemos el formulario y lo convertimos a un objeto plano
            const formArray = $('#venues').serializeArray();
            const formData = {};
            
            $.map(formArray, function(n, i){
                formData[n['name']] = n['value'];
            });
            URL_DESTINO = '';
            // Añadimos metadatos si es necesario
            formData['action'] = 'autosave_venue';
            URL_DESTINO = 'save_venue/"';
            $.ajax({
                url: API_BASE_URL + URL_DESTINO,
                type: 'PUT',
                contentType: 'application/json',
                    headers: { 
                        'Authorization': 'Bearer ' + TOKEN,
                        'Content-Type': 'application/json' 
                    },        
                data: JSON.stringify(formData), // Enviamos como JSON para que el PHP lo lea fácil
                success: function(response) {
                    //const res = typeof response === 'string' ? JSON.parse(response) : response;
                    // Si el servidor nos devuelve un ID nuevo (porque el cliente no existía)
                    //if (res.newIdCustomer) {
                    //    $('#IdCustomer').val(res.newIdCustomer);
                    //}
                    //console.log('Autoguardado exitoso');
                    lanzarMensaje("¡Lugar de evento actualizado con éxito!", "exito", 5000);
                },
                error: function(xhr, status, error) {
                    console.error('Error en el autoguardado:', error);
                }
            });
        }
    }    


    $('#venues select, #venues input[type="checkbox"]').on('change', function() {
        triggerAutoSaveVenue();
    });    

    $('#venues input[type="text"], #venues input[type="email"], #CustomerNote').on('keyup', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(triggerAutoSaveVenue, 1500);
    });    



        //$("input").on("focus", function() {
        //    var $this = $(this);
        //    //setTimeout(function() {
        //        $this.select();
        //    //}, 10);
        //});        

        $(document).on("focus", "input", function() {
            var $this = $(this);
            // El setTimeout a veces es necesario en navegadores móviles o Chrome 
            // para ganarle al evento de click que deselecciona el texto
            setTimeout(function() {
                $this.select();
            }, 50); 
        });        

        
        <?php 
            if (isset($_GET['IdLead']) AND $_GET['IdLead'] > 0 AND $lead){ 
            

                if ($lead['Organization']>0){

                    $query = "select Nombre FROM organizations WHERE Id = ". $lead['Organization'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $organization = $stmt->fetch(PDO::FETCH_ASSOC);


                    echo "
                    load_organization(".$lead['Organization'].");
                        var dataorganization = {
                            id: ".$lead['Organization'].",
                            text: '".$organization['Nombre']."',
                            direccion: 'Av. Siempre Viva 123' // Datos extra que usas en tus templates
                        };
                        var newOption = new Option(dataorganization.text, dataorganization.id, true, true);
                        $(newOption).data('data', dataorganization); 
                        $('#Organization').append(newOption).trigger('change');                    
                    ";
                }            

                if ($lead['Customer']>0){


                    $query = "select Nombres, Apellidos FROM customers WHERE Id = ". $lead['Customer'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $customers = $stmt->fetch(PDO::FETCH_ASSOC);


                    echo "
                    load_customer(".$lead['Customer'].");
                        var datacustomer = {
                            id: ".$lead['Customer'].",
                            text: '".$customers['Nombres']." ".$customers['Apellidos']."',
                            direccion: 'Av. Siempre Viva 123' // Datos extra que usas en tus templates
                        };
                        var newOption = new Option(datacustomer.text, datacustomer.id, true, true);
                        $(newOption).data('data', datacustomer); 
                        $('#Customer').append(newOption).trigger('change');                    
                    ";
                }

                if ($lead['Venue']>0){

                    $query = "select Nombre FROM venues WHERE Id = ". $lead['Venue'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $venue = $stmt->fetch(PDO::FETCH_ASSOC);                

                    echo "
                    load_venue(".$lead['Venue'].");
                        var datavenue = {
                            id: ".$lead['Venue'].",
                            text: '".$venue['Nombre']."',
                            direccion: 'Av. Siempre Viva 123' // Datos extra que usas en tus templates
                        };
                        var newOption = new Option(datavenue.text, datavenue.id, true, true);
                        $(newOption).data('data', datavenue); 
                        $('#Venue').append(newOption).trigger('change');                    
                    ";
                }                

                //if ( $lead['Venue'] >0){
                    echo "$('#Surface').val('".$lead['Surface']."');
                          $('#DeliveryType').val('".$lead['Delivery']."');
                    ";

                //}

                //CARGAR DETALLES
                if ($lead_details) {
                    foreach ($lead_details as $lead_detail) {

                    $query = "select * FROM products WHERE Id = ". $lead_detail['IdProduct'];
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);  
                    
                    $query = "SELECT *  from products_images WHERE Product = ". $lead_detail['IdProduct']." ORDER BY Orden LIMIT 1";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $Images = $stmt->fetch(PDO::FETCH_ASSOC);                      
                    
                        //$IdProd = $lead_detail['IdProduct'];
                        //$IdProdRel = $lead_detail['IdProductRel'];
                        //if ($IdProdRel > 0){
                        //    $IdP =
                        //    $IdPR =
                        //}

                        echo "
                            Row+=1;
                            $('#IdProducto').val('".$lead_detail['IdProduct']."');
                            $('#NombreProducto').val('".$product['Name']."');
                            add_row(Row,".$lead_detail['IdProductRel'].",{
                                id: Row,
                                rel: '".$lead_detail['IdProductRel']."',
                                product: '".$lead_detail['IdProduct']."',
                                name:  '".$product['Name']."',
                                quantity:  '".$product['Quantity']."',
                                quantityS:  '".$lead_detail['Quantity']."',
                                unlimited:  '".$product['Unlimited']."',
                                price:  '".$lead_detail['Price']."',
                                priceS:  '".$lead_detail['Price']."',
                                taxable:  '".$lead_detail['Tax']."',
                                operationstaff:  '".$product['OperationStaff']."',
                                setupstaff:  '".$product['SetUpStaff']."',
                                volunteer:  '".$product['Volunteer']."',
                                electric:  '".$product['Electric']."',
                                discount:  '".$lead_detail['Discount']."',
                                image:'".$Images['Image']."'
                            },1
                            );
                            ";
                    
                    }
                }
                //CARGAR DESCUENTOS  
                if ($lead_discounts){
                    $DiscCnt=0;
                    foreach ($lead_discounts as $lead_discount) {
                        $DiscCnt+=1;
                        if ($lead_discount['IdDiscount'] == 0 ){
                            echo "//DESCUENTO APLICADO ".$lead_discount['IdDiscount']." - ".$lead_discount['AmountVal'];
                            //echo "$('#DiscountType').val('Fee');Add_Discount();";
                            //echo "$('#Discount_Desc_$DiscCnt').val('Fee');";
                            //echo "$('#Discount_Amount_$DiscCnt').val('".$lead_discount['AmountVal']."');";
                        }
                        else{
                            //echo "//DESCUENTO APLICADO ".$lead_discount['IdDiscount']." - ".$lead_discount['AmountVal'];
                            echo "$('#DiscountType').val('Cupon');Add_Discount();";
                            echo "$('#Discount_Desc_$DiscCnt').val('".$lead_discount['IdDiscount']."');";
                            echo "$('#Discount_Amount_$DiscCnt').val('".$lead_discount['AmountVal']."');";                            
                        }
                    }
                }              

            }
        ?>


        });

//END DOCUMENT READY


        //$(".decimals").keypress(function (e) {
        //    if(e.which == 46){
        //        if($(this).val().indexOf('.') != -1) {
        //            return false;
        //        }
        //    }    
        //    if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
        //        return false;
        //    }
        //});          

        //$(".numbers-only").keypress(function (e) {
        //    if (e.which != 8 && e.which != 0  && (e.which < 48 || e.which > 57)) {
        //        return false;
        //    }
        //});
        
$(document).on("keypress", ".decimals", function (e) {
    if(e.which == 46){
        if($(this).val().indexOf('.') != -1) {
            return false;
        }
    }    
    if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
        return false;
    }
});

$(document).on("keypress", ".numbers-only", function (e) {
            if (e.which != 8 && e.which != 0  && (e.which < 48 || e.which > 57)) {
                return false;
            }
});

        //RESET CATEGORIA
        function reset_cat(){
            $('#IdCategory').val('');
            $('#Category').val('');
            $('#Categories').show();            
            $('#Category_Products').hide();
            $('#IdProducto').val('');
            $('#NombreProducto').val('');
            $('#ProductSelect').hide();
            $('#Products_Elements').hide();
        }
        //RESET PRODUCTO
        function reset_prod(){
            $('#Category_Products').show();
            $('#ProductSelect').hide();
            $('#Products_Elements').hide();
            get_products($('#IdCategory').val());
        }        

        // Seleccion de Category_Products_List
        $('.table-custom-cat tbody tr').on('click', function() {
            $('#IdCategory').val($(this).find('td:nth-child(1)').text());
            $('#Category').val($(this).find('td:nth-child(2)').text());
            $('#Categories').hide();
            //alert('Recupera Productos')
            get_products($(this).find('td:nth-child(1)').text());
            $('#Category_Products').show();
        });

        // Seleccion de Category_Products_List
        $('.table-custom-prd tbody').on('click', 'tr', function() {
            const idProd = $(this).find('td:nth-child(1)').text();
            const nombreProd = $(this).find('td:nth-child(2)').text();
            $('#IdProducto').val(idProd);
            $('#NombreProducto').val(nombreProd);
            $('#Category_Products').hide();
            $('#ProductSelect').show();
            $('#Products_Elements').show();
            const $primerTd = $(this).find('td:nth-child(1)');
            const todosLosDatos = $primerTd.data();            
            Row+=1;
            add_row(Row,0,todosLosDatos);
            get_related_products($(this).find('td:nth-child(1)').text());            
            //alert($(this).find('td:nth-child(1)').data('name'))
            //console.log('Producto seleccionado:', nombreProd);
        });               

        // Seleccion de Category_Products_List
        $('.table-custom-sprd tbody ').on('click','tr', function() {
            const $primerTd = $(this).find('td:nth-child(1)');
            $(this).hide();
            const todosLosDatos = $primerTd.data();            
            Row+=1;
            add_row(Row,$('#IdProducto').val(),todosLosDatos);
        });          

        //RECUPERAR PRODUCTOS DE CATERGORIA
        function get_products(IdCat){
            const $tbody = $("#Category_Products_List");
                // 2. Insertamos el spinner de carga (Bootstrap)
                $tbody.html(`
                    <tr id="loading-row">
                        <td colspan="100%" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <div class="mt-2 text-muted fw-bold">Buscando productos...</div>
                        </td>
                    </tr>
                `);        

            const FHI = $('#fechahorainicio').val(); // "2026-02-04T18:30"
            const FHF = $('#fechahorafin').val(); // "2026-02-04T18:30"
            //alert(FHIp[0].replaceAll('-',''))
            $.ajax ({
                url: API_BASE_URL+"get_products_categories/?IdCat="+IdCat+"&DateS="+FHI+"&DateE="+FHF, // URL de tu Web Service
                type: 'GET',
                dataType: 'json', // Indica que esperamos JSON
                headers: {
                    // *** Aquí se adjunta el token en el encabezado Authorization ***
                    'Authorization': 'Bearer ' + TOKEN 
                },
                delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
                //data: JSON.stringify({
                //    Id: IdCat
                //}),
                success: function(response) {

                    $tbody.empty();
                    if (response && response.products.length >= 0) {
                        response.products.forEach(row => {
                            // VALIDAR SI EXISTE
                            
                            //if (Codes.indexOf(row.Producto) < 0 ){
                            //alert(inventario.hasProduct(row.Producto))
                            if ( ! inventario.hasProduct(row.Producto) && row.Quantity > 0){
                                $tbody.append(`<tr>
                                <td style='display: none' 
                                data-product='${row.Producto}' 
                                data-name='${row.ProductName}' 
                                data-quantity='${row.Quantity}' 
                                data-unlimited='${row.Unlimited}' 
                                data-price='${row.Price}' 
                                data-taxable='${row.Taxable}' 
                                data-operationstaff='${row.OperationStaff}' 
                                data-setupstaff='${row.SetUpStaff}' 
                                data-volunteer='${row.Volunteer}' 
                                data-electric='${row.Electric}'
                                data-image='${row.Image}' 
                                >${row.Producto}</td>
                                <td>${row.ProductName}</td>
                                <td>
                                    ${row.Unlimited == 1 ? '<i class="fa-solid fa-infinity"></i>' : row.Quantity }
                                </td>
                                <td style="text-align: right;">$ ${row.Price}</td>                            
                                </tr>`)
                            }
                        });
                        // Aquí renderizas tus filas con .append()
                        // Ejemplo: response.forEach(item => $tbody.append(...));
                    } else {
                        $tbody.html('<tr><td colspan="100%" class="text-center text-muted">No se encontraron productos</td></tr>');
                    }

                },
                error: function(xhr) {

                },
                cache: true
            });        

        }

        //RECUPERAR PRODUCTOS RELACIONADOS
        function get_related_products(IdCat){
            const $tbody = $("#Products_Elements_List");
                // 2. Insertamos el spinner de carga (Bootstrap)
                $tbody.html(`
                    <tr id="loading-row">
                        <td colspan="100%" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <div class="mt-2 text-muted fw-bold">Buscando productos...</div>
                        </td>
                    </tr>
                `);        

            const FHI = $('#fechahorainicio').val(); // "2026-02-04T18:30"
            const FHF = $('#fechahorafin').val(); // "2026-02-04T18:30"
            $.ajax ({
                url: API_BASE_URL+"get_related_products/?IdP="+IdCat+"&DateS="+FHI+"&DateE="+FHF, // URL de tu Web Service
                type: 'GET',
                dataType: 'json', // Indica que esperamos JSON
                headers: {
                    // *** Aquí se adjunta el token en el encabezado Authorization ***
                    'Authorization': 'Bearer ' + TOKEN 
                },
                delay: 300, // Espera 300ms antes de enviar la petición (evita spam al server)
                //data: JSON.stringify({
                //    Id: IdCat
                //}),
                success: function(response) {

                    $tbody.empty();

                    if (response && response.products.length > 0) {
                        response.products.forEach(row => {
                            //alert(inventario.showSummary())
                            row.Quantity = row.Quantity - inventario.getQuantity(row.Producto);
                            if (row.Quantity > 0 || row.Unlimited == 1){
                                if (row.Unlimited == 1)
                                    row.Quantity = 100
                                Rltpc+=1;
                                $tbody.append(`<tr>
                                <td style='display: none' 
                                data-product='${row.Producto}' 
                                data-name='${row.ProductName}' 
                                data-quantity='${row.Quantity}' 
                                data-unlimited='${row.Unlimited}' 
                                data-price='${row.Price}' 
                                data-taxable='${row.Taxable}' 
                                data-operationstaff='${row.OperationStaff}' 
                                data-setupstaff='${row.SetUpStaff}' 
                                data-volunteer='${row.Volunteer}' 
                                data-electric='${row.Electric}' 
                                data-image='${row.Image}' 
                                id='Rltpc${Rltpc}'>${row.Producto}</td>
                                <td>${row.ProductName}</td>
                                <td id='Rltpc${Rltpc}Cnt' >
                                    ${row.Unlimited == 1 ? '<i class="fa-solid fa-infinity"></i>' : row.Quantity }
                                </td>
                                <td style="text-align: right;">$ ${row.Price}</td>                            
                                </tr>`)
                            }
                        });
                        //alert(Rltpc)
                        // Aquí renderizas tus filas con .append()
                        // Ejemplo: response.forEach(item => $tbody.append(...));
                    } else {
                        $tbody.html('<tr><td colspan="100%" class="text-center text-muted">No se encontraron productos</td></tr>');
                    }

                },
                error: function(xhr) {

                },
                cache: true
            });        

        }        

        //REMOVER FILA
        function remove(id){
            const $el = $(`#row_${id}`);
            if ($el.length > 0) {
                product = $el.data('product')
                rel = $el.data('rel')

                if ($el.data('rel') > 0) {
                    cant_rmv = $(`#row_${id}_col_3`).val();
                    inventario.subtract(product, cant_rmv)
                    if (inventario.getQuantity(product) == 0)
                        inventario.remove(product)
                    // Si en 0 borrar de inventario
                    //$el.fadeOut(300, function() {
                    //    $(this).reomve();
                    //});
                    $el.remove();

                }else{
                    inventario.remove(product);
                    $el.remove();
                    //$el.fadeOut(300, function() {
                    //    $(this).remove();
                    //});
                    id+=1;
                    for(var i = id; i <=Row; i++){                
                        const $elr = $(`#row_${i}`);
                        if ($elr.length > 0) {
                            //alert(product + " - "+ $elr.data('rel'))
                            if (product ==  $elr.data('rel')){
                                cant_rmv = $(`#row_${id}_col_3`).val();
                                inventario.subtract($elr.data('product'),cant_rmv);
                                if (inventario.getQuantity(product) == 0)
                                    inventario.remove(product)
                                //$elr.remove();
                                //$elr.fadeOut(300, function() {
                                //    $(this).remove();
                                //});
                                $elr.remove();
                            }
                        }
                    }
                }
            }

            //Actualizar Disponibilidad

            for(var i = 1; i <=Row; i++){  
                const $ell = $(`#row_${i}`);
                if ($ell.length > 0) {
                    //alert(inventario.getInventory($ell.data('product')) - inventario.getQuantity($ell.data('product')) + " prd " + $ell.data('product') +" " +$(`#row_${i}_col_2`).val() )
                    $(`#row_${i}_col_2`).val(inventario.getInventory($ell.data('product')) - inventario.getQuantity($ell.data('product')));
                    $(`#row_${i}_col_2_`).val(inventario.getInventory($ell.data('product')) - inventario.getQuantity($ell.data('product')));
                }            
            }

        autosave_lead()

        }

    //FORMATO CUSTOMER DE SELECT2
    // Función para dibujar las 2 filas en el listado
    function formatResult(repo) {
        if (repo.loading) return "Buscando...";
        // Si es un tag nuevo, mostramos un diseño simple o un aviso
        if (repo.newTag) {
            return $("<span><strong>Agregar nuevo: </strong>" + repo.text + "</span>");
        }        
        // Estructura de dos filas con clases de Bootstrap 5
        var $container = $(
            "<div class='d-flex flex-column py-1'>" +
                "<div class='fw-bold text-dark'>" + repo.text + "</div>" +
                "<div class='text-muted small' style='font-size: 0.75rem;'>" + 
                    "<i class='fa-solid fa-location-dot me-1'></i>" + (repo.direccion || '') + 
                "</div>" +
            "</div>"
        );

        return $container;
    }

    // Cómo se muestra el ítem seleccionado en el cuadro de búsqueda
    function formatRepo(repo) {
        return repo.text ;
    }


    // LOGIN PARA TOKEN

    function attemptLogin(username, password) {
        $.ajax({
            url: LOGIN_URL,
            type: 'POST',
            contentType: 'application/json', // Indica que enviamos JSON
            data: JSON.stringify({
                username: username,
                password: password
            }),
            success: function(response) {
                // Éxito: Guardar el token para futuras llamadas
                const jwtToken = response.jwt;
                //console.log('Login exitoso. Token:', jwtToken);
                
                // *** Almacena el token de forma segura (ej: localStorage) ***
                localStorage.setItem('apiToken', jwtToken); 
                
            },
            error: function(xhr, status, error) {
                // Error: Credenciales inválidas (401) o error del servidor
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido.';
                //console.error('Error de login:', errorMessage);
                //alert('Fallo el inicio de sesión: ' + errorMessage);
            }
        });
    }    




document.addEventListener('DOMContentLoaded', function() {


    // SECCION DE CALENDARIO 
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


    
    //PARA SINCRONIZAR INPUTS DESKTOP Y MOVIL    
    // 1. Sincronizar campos de texto (Delegación de eventos)
    // Escuchamos el evento 'input' en todo el documento
    document.addEventListener('input', function(event) {
        // Verificamos si el elemento tiene el atributo data-sync
        const element = event.target;
        if (element.hasAttribute('data-sync')) {
            const syncKey = element.getAttribute('data-sync');
            const value = element.value;

            // Actualizar todos los elementos con el mismo data-sync
            document.querySelectorAll('[data-sync="' + syncKey + '"]').forEach(function(el) {
                if (el !== element) {
                    el.value = value;
                }
            });
        }
    });

    // 2. Sincronizar checkboxes (Tax) (Delegación de eventos)
    document.addEventListener('change', function(event) {
        const checkbox = event.target;
        // Verificamos si tiene la clase .sync-tax
        if (checkbox.classList.contains('sync-tax')) {
            const checked = checkbox.checked;

            // Actualizar todos los checkboxes de tax
            document.querySelectorAll('.sync-tax').forEach(function(cb) {
                if (cb !== checkbox) {
                    cb.checked = checked;
                }
            });
        }
    });

});


// AGREGAR PRODUCTO SELECCIONADO
function add_row(id,rel,data,clc=0){
    //data.quantity = data.quantity - 1;
    //if (rel == 0){
        //Codes.push(data.product);
        let Quan=0;
        let Disc=0;
        if (clc == 1){
            inventario.add(data.product, data.name, data.quantityS,data.quantity);
            Quan=data.quantityS;
            Disc=data.discount;
        }
        else{
            inventario.add(data.product, data.name, 1, data.quantity);
            Quan=1;
            Disc=0;
        }
            
    //}
    row = `
        <div class="row custom-row mx-0" id="row_${id}"
            data-id='${id}'
            data-rel='${rel}'
            data-product='${data.product}' 
            data-name='${data.name}' 
            data-quantity='${data.quantity}' 
            data-unlimited='${data.unlimited}' 
            data-price='${data.price}' 
            data-taxable='${data.taxable}' 
            data-operationstaff='${data.operationstaff}' 
            data-setupstaff='${data.setupstaff}' 
            data-volunteer='${data.volunteer}' 
            data-electric='${data.electric}',
            data-image='${data.image}'
        >
            <div class="col-sm-12 col-md-3">
                <div class="mobile-label">Producto</div>
                <div class="d-flex align-items-center">
                    ${rel > 0 ? '&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp;':'<i class="fa-solid fa-grip-vertical text-muted me-2 d-none d-md-block"></i>'}
                    <span class="fw-semibold" >${data.name}</span>                    
                    <input type="hidden" id="row_${id}_col_1">
                </div>
            </div>    


            <!-- Contenedor para ícono, cantidad y referencia en móvil -->
            <div class="d-md-none mobile-price-row">
                <div class="icon-col">
                    <i class="fa-solid fa-list"></i>
                </div>            
                <div class="">
                    <div class="mobile-label" >Disp.</div>
                    ${data.unlimited == 1 
                    ? `<i class="fa-solid fa-infinity"></i><input type="hidden" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="" id="row_${id}_col_2" value="${(data.quantity - Quan)}" readonly>`
                    :`<input type="number" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="" id="row_${id}_col_2" value="${(data.quantity - Quan)}" readonly>`}
                    
                </div>
                <div class="">
                    <div class="mobile-label" >Cant.</div>

                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary border-0 bg-light btn-minus" 
                                type="button" 
                                onclick="cambiarCantidad('${id}', -1)">-</button>                    

                                    <input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-cant_${id}" data-sync="cant_${id}" placeholder="" id="row_${id}_col_3" value='${Quan}'>

                        <button class="btn btn-outline-secondary border-0 bg-light btn-plus" 
                                type="button" 
                                onclick="cambiarCantidad('${id}', 1)">+</button>
                    </div>                    


                </div>
            </div>
            
            <!-- Versión desktop (se oculta en móvil) -->
            <div class="col-sm-2 col-md-1 d-none d-md-block">
                <i class="fa-solid fa-list"></i>
            </div>               
            <div class="col-sm-2 col-md-1 d-none d-md-block text-center">
                <div class="mobile-label ">Disp.</div>
                    ${data.unlimited == 1 
                    ? `<i class="fa-solid fa-infinity"></i><input type="hidden" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="0.00" id="row_${id}_col_2_" value="${(data.quantity - Quan)}" readonly>`
                    :`<input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-disp_${id}" data-sync="disp_${id}" placeholder="0.00" id="row_${id}_col_2_" value="${(data.quantity - Quan)}" readonly>`}
            </div>        
            <div class="col-sm-2 col-md-1 d-none d-md-block">
                <div class="mobile-label">Cant.</div>


                <div class="input-group input-group-sm">
                    <button class="btn btn-outline-secondary border-0 bg-light btn-minus" 
                            type="button" 
                            onclick="cambiarCantidad('${id}', -1)">-</button>
                    
                    <input type="text" 
                        class="form-control form-control-sm border-0 bg-light text-center p-0 sync-cant_${id}" 
                        data-sync="cant_${id}" 
                        id="row_${id}_col_3_" 
                        value="${Quan}" 
                        readonly>
                    
                    <button class="btn btn-outline-secondary border-0 bg-light btn-plus" 
                            type="button" 
                            onclick="cambiarCantidad('${id}', 1)">+</button>
                </div>

            </div>


            <!-- Contenedor para precio, tax y total en móvil -->
            <div class="d-md-none mobile-price-row">
                <div>
                    <div class="mobile-label">Desc.</div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                        <input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-price_${id} decimals" data-sync="price_${id}" placeholder="0.00" id="row_${id}_col_4" value='${Disc}' onfocus="this.select()">
                    </div>
                </div>
                <div class="tax-col">
                    <div class="mobile-label">Tax</div>
                    <div class="text-center">
                        <input class="form-check-input sync-tax_${id}" data-sync="tax_${id}" type="checkbox" id="row_${id}_col_5" ${data.taxable == 1 ? 'checked' : '' }>
                    </div>
                </div>
                <div>
                    <div class="mobile-label">Total</div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                        <input type="text" class="form-control form-control-sm border-0 bg-light fw-bold text-end sync-total_${id}" data-sync="total_${id}" readonly id="row_${id}_col_6" value='${data.price}'>
                    </div>
                </div>
            </div>
            
            <!-- Versión desktop precio, tax y total (se oculta en móvil) -->
            <div class="col-sm-5 col-md-2 d-none d-md-block">
                <div class="mobile-label">Desc.</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-light text-end sync-price_${id} decimals" data-sync="price_${id}" placeholder="0.00" id="row_${id}_col_4_" value='${Disc}' onfocus="this.select()">
                </div>
            </div>
            <div class="col-sm-2 col-md-1 text-center d-none d-md-block">
                <div class="mobile-label">Tax</div>
                <input class="form-check-input sync-tax_${id}" data-sync="tax_${id}" type="checkbox" id="row_${id}_col_5_" ${data.taxable == 1 ? 'checked' : '' }>
            </div>
            <div class="col-sm-5 col-md-2 d-none d-md-block">
                <div class="mobile-label">Total</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-0 text-muted">$</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-light fw-bold text-end sync-total_${id}" data-sync="total_${id}" readonly id="row_${id}_col_6_" value='${data.price}'>
                </div>
            </div>

            <div class="col-md-1 text-center">
                <button class="btn btn-sm text-danger border-0 hover-bg-light" onclick="remove(${id});recalculate()">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>`;
        $("#lead_detail").append(row);

        
            for (f=1; f<= Row;f++){
                const $el = $(`#row_${f}`);
                if ($el.length > 0 ) {
                    if ($el.data('product') == data.product && id != f ){
                        const ajusteDisponible = document.getElementById(`row_${f}_col_2_`);
                        const ajusteDisponible_ = document.getElementById(`row_${f}_col_2`);
                        //if (delta > 0) {
                            ajusteDisponible.value = parseInt(ajusteDisponible.value) - 1;
                            ajusteDisponible_.value = ajusteDisponible.value
                        //}
                        //else{
                        //    ajusteDisponible.value = parseInt(ajusteDisponible.value) + 1;
                        //}
                        
                    }
                }
            }
        if (clc==0){                    
            recalculate();
            autosave_lead();
        }
}

//CAMBIO DE CANTIDADES -/+
function cambiarCantidad(id, delta) {
    const fila_afectada = document.getElementById(`row_${id}`);

    const spanDisponible_ = document.getElementById(`row_${id}_col_2`);
    const inputSeleccionado_ = document.getElementById(`row_${id}_col_3`);    

    const spanDisponible = document.getElementById(`row_${id}_col_2_`);
    const inputSeleccionado = document.getElementById(`row_${id}_col_3_`);
    // Convertimos a números
    let cantSeleccionada = parseInt(inputSeleccionado.value) || 0;
    let stockDisponible = parseInt(spanDisponible.value) || 0;

    if ( cantSeleccionada == 1 && delta < 0)
        return

    if (delta > 0) {
        // Lógica para SUMAR
        if (stockDisponible > 0) {
            cantSeleccionada++;
            stockDisponible--;
            
            // Actualizar la clase (opcional: tu ProductCounter)
            inventario.add(fila_afectada.dataset.product, fila_afectada.dataset.name, 1);
        } else {
            //alert("¡No hay más stock disponible!");
            lanzarMensaje("¡No hay más stock disponible!", "alert", 5000);
        }
    } else if (delta < 0) {
        // Lógica para RESTAR
        if (cantSeleccionada > 0) {
            cantSeleccionada--;
            stockDisponible++;
            
            // Actualizar la clase
            inventario.subtract(fila_afectada.dataset.product, 1);
        }
    }


    for (f=1; f<= Row;f++){
        const $el = $(`#row_${f}`);
        if ($el.length > 0 ) {
            if ($el.data('product') == fila_afectada.dataset.product && id != f ){
                const ajusteDisponible = document.getElementById(`row_${f}_col_2_`);
                const ajusteDisponible_ = document.getElementById(`row_${f}_col_2`);
                if (delta > 0) {
                    if (parseInt(ajusteDisponible.value) - 1 < 0)
                        ajusteDisponible.value = 0
                    else
                        ajusteDisponible.value = parseInt(ajusteDisponible.value) - 1;
                }
                else{
                    ajusteDisponible.value = parseInt(ajusteDisponible.value) + 1;
                }
                ajusteDisponible_.value = ajusteDisponible.value
            }
        }
    }
    for (f=1; f<= Rltpc;f++){
        const $el = $(`#Rltpc${f}`);
        if (inventario.hasProduct($el.data('product'))){
            quantity = inventario.getQuantity($el.data('product'));
            inventory= inventario.getInventory($el.data('product'));
            if ($el.data('unlimited') == 0){
                $el.data('quantity',inventory- quantity)
                $(`#Rltpc${f}Cnt`).html(inventory- quantity)
            }
        }
    }

    // Actualizar el DOM
    inputSeleccionado.value = cantSeleccionada;
    spanDisponible.value = stockDisponible;

    inputSeleccionado_.value = cantSeleccionada;
    spanDisponible_.value = stockDisponible;    

    // Feedback visual si se agota
    //spanDisponible.className = stockDisponible === 0 ? "fw-bold text-danger" : "fw-bold text-success";
    recalculate();

    clearTimeout(autoSaveTimerQuant);

    autoSaveTimerQuant = setTimeout(function() {
        autosave_lead();
    }, 5000);    

    
}

//RECCALCULAR POSICIONES
function recalculate(){
    
    let total = 0;
    //alert(total)
    for (f=1; f<= Row;f++){
        const $el = $(`#row_${f}`);
        //alert(f)
        //alert($el.length)
        if ($el.length > 0 ) {
            const Cnt = document.getElementById(`row_${f}_col_3`);
            const Tot = document.getElementById(`row_${f}_col_6`);
            const Tot_ = document.getElementById(`row_${f}_col_6_`);
            Tot.value = Cnt.value *  parseFloat($el.data('price'));
            Tot_.value = Tot.value;
            total = parseFloat(total) + parseFloat(Tot.value);
        }
    }
    //alert(total)
    $('#Item_Totals').val(total.toFixed(2));

    recalculate_totals()    
    
}

//RECALCULAR TOTALES
function recalculate_totals(){
    let total = 0;
    let DCT = 0;
    let SCT = 0;
    let DsCT = 0;
    let ADsCT = 0;
    let SubT = 0;
    let Total = 0;
    let Balance = 0;
    total = $('#Item_Totals').val() * 1;

    if ($('#Distance_Charges_check').prop('checked'))
        DCT = $('#Distance_Charges_Total').val() * 1;
    if ($('#Staff_Charges_check').prop('checked'))
        SCT = $('#Staff_Charges_Total').val() * 1;
    if ($('#Discount_Charges_check').prop('checked'))
        DsCT = $('#Discount_Charges_Total').val() * 1;

    SubT= ((total + DCT + SCT) - DsCT);

    
    //APLICAR DESCUENTOS 
    for (let i=1; i<= TrDsc; i ++){

        const $el = $(`#Discount_Amount_${i}`);
        if ($el.length > 0 ) {    
            if ($(`#Discount_Charges_check_${i}`).prop('checked')){
                if ($(`#Discount_Amount_${i}`).data('type') == 'fee'){
                    SubT-= $(`#Discount_Amount_${i}`).val() * 1;
                }
                else{
                    if ($(`#Discount_Desc_${i} option:selected`).data('type') == 'amount'){
                        SubT-= $(`#Discount_Amount_${i}`).val() * 1;
                    }
                    else{
                        $(`#Discount_Amount_${i}`).val(SubT *  ( $(`#Discount_Desc_${i} option:selected`).data('amount') / 100) ) * 1;
                        SubT-= $(`#Discount_Amount_${i}`).val() * 1;
                    }
                }
            }
        }
        //Discount_Amount_

    }
    //APLICAR DESCUENTOS 

    $('#SubTotal').val(SubT.toFixed(2));

    //Tax
    if ($('#Tax').prop('checked')){
        $("#Excempt").show();

        $("#NoExcempt1").hide();
        $("#NoExcempt2").hide();
        
        let TaxPc = $('#TaxPc').val();
        let TaxAm = "0.00";
        $('#TaxAm').val(TaxAm.toFixed(2));
        SubT+= TaxAm;
    }
    else{
        $("#Excempt").hide();

        $("#NoExcempt1").show();
        $("#NoExcempt2").show();        
        let TaxPc = $('#TaxPc').val();
        let TaxAm = SubT * TaxPc;
        $('#TaxAm').val(TaxAm.toFixed(2));
        SubT+= TaxAm;        
    }

    $('#Total').val(SubT.toFixed(2));
    //$('#Depopsit').val('0.00');
    $('#Balance').val(SubT.toFixed(2));
}

//CARGA DATOS DE ORGANIZACION
function load_organization(Id){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };
        $.ajax({
        url: API_BASE_URL + 'organizations/'+Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        success: function(data) {
            $('#Country').val( data.Pais);
            $('#Country').trigger('change');
            $('#State').val( data.Estado);
            $('#Street').val( data.Direccion+' '+data.Direccion2);
            $('#Cell').val( data.TelefonoCelular);
            $('#City').val( data.Ciudad);
            $('#Zip').val( data.CP);
            $('#CustomerEmail').val( data.Correo);
            aplicar_autosave();
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                console.error('Acceso denegado. Token expirado o inválido.');
                // Aquí puedes redirigir al login o limpiar el token
            } else {
                console.error('Error al obtener registro:', error);
            }
        }
    });
}

//CARGA DATOS DE CLIENTE
function load_customer(Id){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
        url: API_BASE_URL + 'customers/'+Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        success: function(data) {
            $('#Country').val( data.Pais);
            $('#Country').trigger('change');
            $('#State').val( data.Estado);        
            $('#Street').val( data.Direccion+' '+data.Direccion2);
            $('#Cell').val( data.TelefonoCelular);
            $('#City').val( data.Ciudad);
            $('#Zip').val( data.CP);
            $('#CustomerEmail').val( data.Correo); 
            aplicar_autosave();           
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                console.error('Acceso denegado. Token expirado o inválido.');
                // Aquí puedes redirigir al login o limpiar el token
            } else {
                console.error('Error al obtener registro:', error);
            }
        }
    });
}

//CARGA DATOS DE LUGAR DE EVENTO
function load_venue(Id){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
        url: API_BASE_URL + 'venues/'+Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        success: function(data) {
            //alert(response.Nombre)
            $('#EventCountry').val( data.Pais);
            $('#EventCountry').trigger('change');
            $('#EventState').val( data.Estado);
            $('#EventStreet').val( data.Direccion+' '+data.Direccion2);
            $('#EventStreet').val( data.Direccion+' '+data.Direccion2);
            $('#EventCity').val( data.Ciudad);
            $('#EventZip').val( data.CP);
            aplicar_autosave();
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                console.error('Acceso denegado. Token expirado o inválido.');
                // Aquí puedes redirigir al login o limpiar el token
            } else {
                console.error('Error al obtener registro:', error);
            }
        }
    });
}

//COPIAR DIRECCION DE ORGANIZACION/CLIENTE
function copy_ad(){
            $('#EventStreet').val($('#Street').val());
            $('#EventCity').val( $('#City').val());
            $('#EventZip').val( $('#Zip').val());

            distance_charge($('#Zip').val())
}

//CARGO POR DISTANCIA
function distance_charge(zip){
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
        url: API_BASE_URL + 'distance_charge/'+zip,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,

        success: function(data) {
            let totaldist = data.cost.costo_total;
            $('#Distance_Charges_Total').val(totaldist.toFixed(2))
            $('#Distance_Charges_check').prop('checked', true);
            
            $('#Tax').prop('checked', true);
            $('#TaxPc').val(data.cost.taxrate);
            
            recalculate_totals()
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                console.error('Acceso denegado. Token expirado o inválido.');
                // Aquí puedes redirigir al login o limpiar el token
            } else {
                console.error('Error al obtener registro:', error);
            }
        }
    });
}

//ABRIR RUTA EN GOOGLE
function abrirRutaGoogleMaps() {
    // 1. Limpiar y codificar los textos para la URL
    const origenURL = encodeURIComponent('villa Fontana Poniente 1379, tlaquepaque, jalisco, 45615');
    const destinoURL = encodeURIComponent('ramon lopez velarde 1023, guadalajara, jalisco, 44840');

    // 2. Construir la URL usando template literals (backticks)
    const url = `https://www.google.com/maps/dir/?api=1&origin=${origenURL}&destination=${destinoURL}&travelmode=driving&layer=traffic`;

    window.open(url, '_blank');
}

//AGREGAR FILAS DE DESCUENTO ** AUN NO FUNCIONA
function Add_Discount(){
    if ($('#DiscountType').val()==''){
        //alert('No selecciono tipo descuento')
        lanzarMensaje("¡No selecciono tipo descuento!", "alert", 5000);
    }
    if ($('#DiscountType').val()=='Fee'){
        TrDsc+=1;
            let Discount = `
                <tr id="tr_discount_${TrDsc}">
                    <td>
                        <input type="text" class="form-control" id="Discount_Desc_${TrDsc}" name="Discount_Desc_${TrDsc}" value="Fee"  readonly >
                    </td>
                    <td class="text-center">
                        <input class="form-check-input" type="checkbox" id="Discount_Charges_check_${TrDsc}" name="Discount_Charges_check_${TrDsc}" checked onchange="recalculate_totals()">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">$-</span>
                            <input type="text" data-type="fee" class="form-control text-end decimals" placeholder="0.00" id="Discount_Amount_${TrDsc}" name="Discount_Amount_${TrDsc}" onchange="ApplyDsc(${TrDsc})">
                        </div>                            
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#tr_discount_${TrDsc}').remove();recalculate_totals()">
                            <i class="fa-solid fa-trash"></i>
                        </button>    
                    </td>
                </tr>`;

            $('#tr_discount').before(Discount);

    }
    else if ($('#DiscountType').val()=='Cupon'){
        TrDsc+=1;

        <?php 
                $disc='';
                $query = "select * FROM discounts WHERE DateExp > now() AND Active = 1 AND ( Quantity > 0 OR Unlimited = 1) ORDER BY Name";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($discounts) {
                    $disc.='<option value="">...</option>';
                    foreach ($discounts as $discount) {
                        $disc.='<option value="'.$discount['Id'].'" data-Id="'.$discount['Id'].'" data-type="'.$discount['Type'].'" data-amount="'.$discount['Amount'].'" >'.$discount['Name'].'</option>';
                    }
                    
                }   

        ?>

            let Discount = `
                <tr id="tr_discount_${TrDsc}">
                    <td>
                        <select class="form-select" id="Discount_Desc_${TrDsc}" name="Discount_Desc_${TrDsc}" onchange="ApplyDsc(${TrDsc})">
                            <?php echo $disc;?>
                        </select>
                    </td>
                    <td class="text-center">
                        <input class="form-check-input" type="checkbox" id="Discount_Charges_check_${TrDsc}" name="Discount_Charges_check_${TrDsc}" checked  onchange="recalculate_totals()">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">$-</span>
                            <input type="text" data-type="cupon" class="form-control text-end decimals" placeholder="0.00" id="Discount_Amount_${TrDsc}" name="Discount_Amount_${TrDsc}" readonly>
                        </div>                            
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#tr_discount_${TrDsc}').remove();recalculate_totals()">
                            <i class="fa-solid fa-trash"></i>
                        </button>    
                    </td>
                </tr>`;

            $('#tr_discount').before(Discount);    

    }
}

//MENSAJES EN LA BARRA INFERIOR
let msgTimer;

function lanzarMensaje(texto, tipo = 'normal', duracion = 4000) {
    const $barra = $('#barra-mensajes');
    const $texto = $('#mensaje-texto');
    const $icono = $('#mensaje-icono');

    // Resetear clases
    $barra.removeClass('msg-minimal-normal msg-minimal-exito msg-minimal-error msg-minimal-alerta d-none');
    
    // Configurar según tipo
    let clase, icono;
    switch(tipo) {
        case 'exito':  clase = 'msg-minimal-exito';  icono = '<i class="fas fa-check-circle"></i>'; break;
        case 'error':  clase = 'msg-minimal-error';  icono = '<i class="fas fa-times-circle"></i>'; break;
        case 'alerta': clase = 'msg-minimal-alerta'; icono = '<i class="fas fa-exclamation-triangle"></i>'; break;
        default:       clase = 'msg-minimal-normal'; icono = '<i class="fas fa-info-circle"></i>';
    }

    $barra.addClass(clase);
    $texto.text(texto);
    $icono.html(icono);

    // Animación de entrada
    $barra.hide().fadeIn(400);

    clearTimeout(msgTimer);
    if (duracion > 0) {
        msgTimer = setTimeout(cerrarBarra, duracion);
    }
}

function cerrarBarra() {
    $('#barra-mensajes').fadeOut(400);
}



//FUNCION PARA CARGAR CONTRATO 

function LoadDocument(DocumentType){

const FHI = $('#fechahorainicio').val(); // "2026-02-04T18:30"
const FHF = $('#fechahorafin').val(); // "2026-02-04T18:30"
const FHIp = FHI.split('T')
const FHFp = FHF.split('T')
TaxPc = 0
TaxAm = 0
    if ($('#Tax').prop('checked')){
        TaxPc = 0;
        TaxAm = 0;        
    }
    else{
        TaxPc = $('#TaxPc').val() * 1;
        TaxAm = $('#TaxAm').val() * 1;
    }
    <?php
        $query = "select NombreCompania, Direccion,Direccion2, Ciudad,CP,Estado,Pais,TelefonoCelular FROM account";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "select Template FROM document_center WHERE Tipo = 'contract' AND IdTemplate = 2 AND Activo = 1 AND Idioma ='$lang'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $Template = $stmt->fetch(PDO::FETCH_ASSOC);
        $Contract = $Template['Template'];

        $query = "select Template FROM document_center WHERE Tipo = 'quote' AND IdTemplate = 4 AND Activo = 1 AND Idioma ='$lang'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $Template = $stmt->fetch(PDO::FETCH_ASSOC);
        $Quote = $Template['Template'];

    ?>

    const datosGenerales = {
        leadid: "",
        contractsentdate: "",
        company_name: "<?php echo $account['NombreCompania']?>",
        company_address:"<?php echo $account['Direccion']." ".$account['Direccion2'];?>",
        company_city:"<?php echo $account['Ciudad']." ".$account['CP'];?>",
        company_state:"<?php echo $account['Estado'];?>",
        company_phone:"<?php echo $account['TelefonoCelular']?>",

        organization: $('#Organization option:selected').text(),
        ctfirstname:$('#Customer option:selected').text(),
        ctlastname:"",
        eventstreet:$('#EventStreet').val(),
        eventcity:$('#EventCity').val(),
        eventstate:$('#EventState option:selected').text(),
        eventzip:$('#EventZip').val(),
        phones:$('#Cell').val(),
        startdate: FHIp[0],
        starttime: FHIp[1],
        enddate: FHFp[0],
        endtime: FHFp[1],
        deliverytype:$('#DeliveryType option:selected').text(),

        itemtotals: $('#Item_Totals').val(),
        distanecharges: $('#Distance_Charges_Total').val() * 1,
        staffcosts: $('#Staff_Charges_Total').val() * 1,
        discount: $('#Discount_Charges_Total').val() * 1,

        subtotal: $('#SubTotal').val(),
        taxexcempt: $('#IDTAX').val(),
        taxrate: TaxPc.toFixed(2),
        salestax: TaxAm.toFixed(2),
        tip: 0,
        total: $('#Total').val(),
        ctr_balance_due: $('#Balance').val(),

        electric:"",
        signature:"",
        signeddate:""
    };

    const productos = [];
    const descuentos = [];

    for (let i = 1; i <= Row; i++) {
        const $el = $(`#row_${i}`);
        if ($el.length > 0 ) {
            // Creamos el objeto con las llaves que espera nuestro contrato
            let item = {
                rentalname_url_photo: 'ajax/tmp/'+$el.data('image'),
                rentalname: $el.data('name'),
                fullrentaltime: "",
                rentalqty: $(`#row_${i}_col_3`).val(),
                rentaltotalprice: $(`#row_${i}_col_6`).val()
            };
            productos.push(item);
        }
    }    


    for (let i=1; i<= TrDsc; i ++){
        const $el = $(`#Discount_Amount_${i}`);
        if ($el.length > 0 ) {    
            if ($(`#Discount_Charges_check_${i}`).prop('checked')){
                if ($(`#Discount_Amount_${i}`).data('type') == 'fee'){
                    descuentos.push({
                        concepto:$(`#Discount_Desc_${i}`).val(),
                        monto: $(`#Discount_Amount_${i}`).val()
                    });
                }
                else{
                    descuentos.push({
                        concepto:  $(`#Discount_Desc_${i} option:selected`).text(),
                        monto: $(`#Discount_Amount_${i}`).val()
                    });
                }
            }
        }
        //Discount_Amount_
    }            



    if (DocumentType =='Contract' ){

            const htmlRecibido = <?php echo json_encode($Contract); ?>;
            $('#Contract').html(htmlRecibido);
            const $contenedor = $('#contrato-dsj');
            const $cuerpoTabla = $('#lista-productos');
            const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
            ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos);
            
            lanzarMensaje("Contrato cargado correctamente", "exito");
    }
    else if (DocumentType =='Quote'){

            const htmlRecibido = <?php echo json_encode($Quote); ?>;
            $('#Contract').html(htmlRecibido);
            const $contenedor = $('#cotizacion-dsj');
            const $cuerpoTabla = $('#lista-productos');
            const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
            ejecutarRenderizadoQuote($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos);
            
            lanzarMensaje("Contrato cargado correctamente", "exito");

    }
}

function ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos) {
    // 1. Limpiar productos previos (excepto la plantilla)
    $cuerpoTabla.find('tr:not(.item-fila)').remove();

    // 2. Procesar y agregar cada producto
    productos.forEach(producto => {
        let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
        $.each(producto, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });
        
        let $nuevaFila = $(nuevaFilaHtml).removeClass('item-fila').css('display', ''); // Quitar display:none
        $cuerpoTabla.append($nuevaFila);
    });


    const $contenedorDescuentos = $contenedor.find('#extra_discounts');
    const $filaOriginal = $contenedorDescuentos.find('tr').first();

    if ($filaOriginal.length > 0) {
        const htmlPlantillaDesc = $filaOriginal[0].outerHTML;
        $contenedorDescuentos.empty(); // Limpiamos después de copiar la plantilla

        descuentos.forEach(desc => {
            let filaDescHtml = htmlPlantillaDesc
                .replace('*conceptdiscount*', desc.concepto)
                .replace('*discountconcept*', desc.monto);
            $contenedorDescuentos.append(filaDescHtml);
        });
    }    


    // 3. Lógica para ocultar IDs si el valor es 0
    $.each(datosGenerales, function(key, val) {
        // Buscamos el elemento que tenga el ID igual a la 'key'
        let $elemento = $contenedor.find('#' + key);
        
        if (val === 0 || val === "0") {
            $elemento.hide(); // Oculta el elemento si es cero
        } else {
            $elemento.show(); // Se asegura de mostrarlo si tiene valor
        }

        // 4. Reemplazar etiquetas en el HTML (Mantenemos tu lógica de reemplazo)
        let regex = new RegExp('\\*' + key + '\\*', 'g');
        let contenidoActual = $contenedor.html();
        $contenedor.html(contenidoActual.replace(regex, val ?? ''));
    });

    const miModal = new bootstrap.Modal(document.getElementById('modalContrato'));
    miModal.show();
}

function ejecutarRenderizadoQuote($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos) {
    // 1. Limpiar productos previos (excepto la plantilla)
    $cuerpoTabla.find('tr:not(.item-fila)').remove();

    // 2. Procesar y agregar cada producto
    productos.forEach(producto => {
        let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
        $.each(producto, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });
        
        let $nuevaFila = $(nuevaFilaHtml).removeClass('item-fila').css('display', ''); // Quitar display:none
        $cuerpoTabla.append($nuevaFila);
    });


    const $contenedorDescuentos = $contenedor.find('#extra_discounts');
    const $filaOriginal = $contenedorDescuentos.find('tr').first();

    if ($filaOriginal.length > 0) {
        const htmlPlantillaDesc = $filaOriginal[0].outerHTML;
        $contenedorDescuentos.empty(); // Limpiamos después de copiar la plantilla

        descuentos.forEach(desc => {
            let filaDescHtml = htmlPlantillaDesc
                .replace('*conceptdiscount*', desc.concepto)
                .replace('*discountconcept*', desc.monto);
            $contenedorDescuentos.append(filaDescHtml);
        });
    }    


    // 3. Lógica para ocultar IDs si el valor es 0
    $.each(datosGenerales, function(key, val) {
        // Buscamos el elemento que tenga el ID igual a la 'key'
        let $elemento = $contenedor.find('#' + key);
        
        if (val === 0 || val === "0") {
            $elemento.hide(); // Oculta el elemento si es cero
        } else {
            $elemento.show(); // Se asegura de mostrarlo si tiene valor
        }

        // 4. Reemplazar etiquetas en el HTML (Mantenemos tu lógica de reemplazo)
        let regex = new RegExp('\\*' + key + '\\*', 'g');
        let contenidoActual = $contenedor.html();
        $contenedor.html(contenidoActual.replace(regex, val ?? ''));
    });

    const miModal = new bootstrap.Modal(document.getElementById('modalContrato'));
    miModal.show();
}

// LLENADO DE ESTADOS POR PAIS

    $('#Country').on('change', function() {

        const selectedCountry = $(this).val();
        const $stateSelect = $('#State');

        // Limpiar selección actual
        $stateSelect.val('');

        // 2. IMPORTANTE: Ocultar y deshabilitar todos primero
        $stateSelect.find('option[data-country]')
            .prop('disabled', true)
            .attr('hidden', true) // Forzamos el atributo HTML
            .hide();

        if (selectedCountry) {
            // 3. Mostrar y habilitar los estados que coinciden
            // Removemos 'hidden' y usamos .show() para forzar la vista
            $stateSelect.find('option[data-country="' + selectedCountry + '"]')
                .prop('disabled', false)
                .removeAttr('hidden') 
                .show();
        }
    });

    $('#EventCountry').on('change', function() {

        const selectedCountry = $(this).val();
        const $stateSelect = $('#EventState');

        // Limpiar selección actual
        $stateSelect.val('');

        // 2. IMPORTANTE: Ocultar y deshabilitar todos primero
        $stateSelect.find('option[data-country]')
            .prop('disabled', true)
            .attr('hidden', true) // Forzamos el atributo HTML
            .hide();

        if (selectedCountry) {
            // 3. Mostrar y habilitar los estados que coinciden
            // Removemos 'hidden' y usamos .show() para forzar la vista
            $stateSelect.find('option[data-country="' + selectedCountry + '"]')
                .prop('disabled', false)
                .removeAttr('hidden') 
                .show();
        }
    });    


//AUTO GUARDADO GRAL

    function autosave_lead(){

        const headerData = {
            IdLead : $('#IdLead').val(),
            FHI: $('#fechahorainicio').val(),
            FHF: $('#fechahorafin').val(),

            Item_Totals: $('#Item_Totals').val(),

            ChkDstC:    $('#Distance_Charges_check').is(':checked') ? 1 : 0,
            DstC:       $('#Distance_Charges_Total').val(),
            ChkStCs:    $('#Staff_Charges_check').is(':checked') ? 1 : 0,
            StCs:       $('#Staff_Charges_Total').val(),
            ChkDsc:     $('#Discount_Charges_check').is(':checked') ? 1 : 0,
            Dsc:        $('#Discount_Charges_Total').val(),

            SubT:   $('#SubTotal').val(),
            TaxId:  $('#IDTAX').val(),
            TaxPc:  $('#TaxPc').val(),
            TaxAm:  $('#TaxAm').val(),
            Total:  $('#Total').val(),
            Depo:   $('#Deposit').val(),
            BalDue: $('#Balance').val(),

            Referal: $('#Referal').val(),
            Organization:   $('#IdOrganization').val() || $('#Organization').val(),
            Customer:       $('#IdCustomer').val() || $('#Customer').val(),
            OkT:    $('#OkText').is(':checked') ? 1 : 0,
            WA:     $('#WindAlert').is(':checked') ? 1 : 0,
            AE:     $('#AutoEmail').is(':checked') ? 1 : 0,
            ME:     $('#ManualEmail').is(':checked') ? 1 : 0,
            CusNt:  $('#CustomerNote').val(),

            Venue:   $('#IdVenue').val() || $('#Venue').val(),
            EventName:  $('#EventName').val(),
            Surface:    $('#Surface').val(),
            Delivety:   $('#DeliveryType').val(),
            Nt1:        $('#Note_1').val(),
            Nt2:        $('#Note_2').val()
        };

        let detalleProductos = [];
        for (f=1; f<= Row;f++){
            const $el = $(`#row_${f}`);
            if ($el.length > 0 ) {
                detalleProductos.push({
                    id_referencia: f,
                    id_prd:     $el.data('product'),
                    id_rel:     $el.data('rel'),
                    nombre_prd: $el.data('name'),
                    price:      $el.data('price'),
                    cant:       $(`#row_${f}_col_3`).val(),
                    descuento:  $(`#row_${f}_col_4`).val(),
                    imp:        $(`#row_${f}_col_5`).is(':checked') ? 1 : 0,
                    precio:     $(`#row_${f}_col_6`).val()
                });
            }
        }

        let discounts = [];

        for (let i=1; i<= TrDsc; i ++){
            const $el = $(`#Discount_Amount_${i}`);
            if ($el.length > 0 ) {    
                if ($(`#Discount_Charges_check_${i}`).prop('checked')){
                    if ($(`#Discount_Amount_${i}`).data('type') == 'fee'){
                        discounts.push({
                            IdDiscount:0 ,
                            Type:  'fee',
                            Amount: '0',
                            AmountVal: $(`#Discount_Amount_${i}`).val()
                        });
                    }
                    else{
                        discounts.push({
                            IdDiscount:$(`#Discount_Desc_${i} option:selected`).data('id') ,
                            Type:  $(`#Discount_Desc_${i} option:selected`).data('type'),
                            Amount:  $(`#Discount_Desc_${i} option:selected`).data('amount'),
                            AmountVal: $(`#Discount_Amount_${i}`).val()
                        });
                    }
                }
            }
            //Discount_Amount_
        }        


        const dataGlobal = {
            header: headerData,
            detalle: detalleProductos,
            descuentos: discounts,
        };

        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };

        $.ajax({
            url: API_BASE_URL + 'lead_auto_save/',
            type: 'PUT',
            contentType: 'application/json',
            headers: misHeaders,
            data: JSON.stringify(dataGlobal),
            success: function(response) {
                //console.log("¡Detalle guardado correctamente!");
                //alert(response.IdLead)
                $('#IdLead').val(response.IdLead);
                lanzarMensaje("¡Auto guadado correctamente!", tipo = 'exito');
                
            }
        });

    }

// VALIDACIONES DE APLICACION DE DESCUENTO
    $(document).on('input', 'input[id^="row_"]', function() {
        // Obtenemos el ID del input actual
        const currentId = $(this).attr('id');
        // Extraemos el número de fila (ej: de "row_25_col_4" sacamos "25")
        const rowId = currentId.split('_')[1];
        // Referenciamos los campos de esa fila específica
        const $inputDescuento = $(`#row_${rowId}_col_4_`);
        const $inputPrecio = $(`#row_${rowId}_col_6_`);
        // Convertimos a números para operar
        let descuento = parseFloat($inputDescuento.val()) || 0;
        let precio = parseFloat($inputPrecio.val()) || 0;
        // --- Lógica de Validación ---
        // 1. Evitar que sea menor a cero
        if (descuento < 0) {
            $inputDescuento.val(0);
            descuento = 0;
        }
        // 2. Evitar que el descuento sea mayor al precio
        if (descuento > precio) {
            // Si es mayor, lo igualamos al precio máximo permitido
            $inputDescuento.val(precio);
            descuento = precio;
            // Opcional: un toque visual para avisar del error
            $inputDescuento.css('border-color', 'red');
            setTimeout(() => $inputDescuento.css('border-color', ''), 1000);
        }
        // --- Disparar el timer de Autoguardado ---
        clearTimeout(autoSaveTimerQuant);
        autoSaveTimerQuant = setTimeout(function() {
            autosave_lead();
        }, 5000);
    });    

    $(document).on('input', 'input[id^="row_"]', function() {
        // Obtenemos el ID del input actual
        const currentId = $(this).attr('id');
        // Extraemos el número de fila (ej: de "row_25_col_4" sacamos "25")
        const rowId = currentId.split('_')[1];
        // Referenciamos los campos de esa fila específica
        const $inputDescuento = $(`#row_${rowId}_col_4`);
        const $inputPrecio = $(`#row_${rowId}_col_6`);
        // Convertimos a números para operar
        let descuento = parseFloat($inputDescuento.val()) || 0;
        let precio = parseFloat($inputPrecio.val()) || 0;
        // --- Lógica de Validación ---
        // 1. Evitar que sea menor a cero
        if (descuento < 0) {
            $inputDescuento.val(0);
            descuento = 0;
        }
        // 2. Evitar que el descuento sea mayor al precio
        if (descuento > precio) {
            // Si es mayor, lo igualamos al precio máximo permitido
            $inputDescuento.val(precio);
            descuento = precio;
            // Opcional: un toque visual para avisar del error
            $inputDescuento.css('border-color', 'red');
            setTimeout(() => $inputDescuento.css('border-color', ''), 1000);
        }
        // --- Disparar el timer de Autoguardado ---
        clearTimeout(autoSaveTimerQuant);
        autoSaveTimerQuant = setTimeout(function() {
            autosave_lead();
        }, 5000);
    });    


    function aplicar_autosave(){
        if ($('#IdLead').val() > 0){
                clearTimeout(autoSaveTimerQuant);
                autoSaveTimerQuant = setTimeout(function() {
                    autosave_lead();
                }, 5000);
        }        
    }

    function aplicar_autosave_10(){
        if ($('#IdLead').val() > 0){
                clearTimeout(autoSaveTimerQuant);
                autoSaveTimerQuant = setTimeout(function() {
                    autosave_lead();
                }, 10000);
        }        
    }    

    //APLICAR DECUENTOS
    function ApplyDsc(IdDsc){
        //alert(1)
        //alert($(`#Discount_Desc_${IdDsc} option:selected`).data('type'))
        //alert($(`#Discount_Desc_${IdDsc} option:selected`).data('amount'))
        if ($(`#Discount_Desc_${IdDsc} option:selected`).data('type') == 'amount'){
            $(`#Discount_Amount_${IdDsc}`).val($(`#Discount_Desc_${IdDsc} option:selected`).data('amount'))
            
        }
        
        recalculate_totals();
        
    }
    </script>


</body>
</html>
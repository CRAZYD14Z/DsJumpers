<?php 
    ob_start();
    session_start(); 
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    require 'idioma.php'; 
    $company = $_GET['company'] ;

    $database = new DatabaseLogin();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT Id, nombre_db, estatus, fecha_termino FROM data_bases WHERE company = ?");
    $stmt->execute([$company]);
    $SDB = $stmt->fetch(PDO::FETCH_ASSOC); 
    if ($SDB){

            $loginUrl = URL_BASE."/api/account_login";            

            $data = [
                'data_base'         => $SDB['nombre_db']
            ];

            $payload = json_encode($data);
            $ch = curl_init($loginUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
            curl_setopt($ch, CURLOPT_POST, true);           // Define el método POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Adjunta los datos JSON
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);            


            if (curl_errno($ch)) {
                echo 'Error en cURL: ' . curl_error($ch);
            } else {
                if ($httpCode === 200) {
//                    die ($response);
                    $result = json_decode($response, true);
                    $WebSite = $result['WebSite'];
                    $Logo = $result['Logo'];                    
                } else {
                    $errorResponse = json_decode($response, true);
                    $errorMessage = $errorResponse['message'] ?? 'Error desconocido.';
                    echo json_encode(['status' => 'error', 'message' => "Fallo el inicio de sesión: " . $errorMessage]);
                    die();
                }
            }

            curl_close($ch);             

    }

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $texts['titulo']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm" style="width: 24rem;">
    <div class="card-body">
        
        <div class="d-flex justify-content-end mb-3">
            <select id="select-lang" class="form-select form-select-sm" style="width: auto;">
                <option value="es" <?php echo $lang == 'es' ? 'selected' : ''; ?>>Español 🇪🇸</option>
                <option value="en" <?php echo $lang == 'en' ? 'selected' : ''; ?>>English 🇺🇸</option>
            </select>
        </div>

        <h3 class="card-title text-center mb-4"><?php echo $texts['titulo']; ?></h3>

        <!-- LOGO DE LA EMPRESA AÑADIDO AQUÍ -->
        <div class="text-center mb-4">
            <img src="<?= $Logo ?>" alt="Logo Empresa" class="img-fluid" style="max-height: 100px;">
        </div>

        <div id="alert-container"></div>

        <form id="login-form">
            <input type="hidden" name="company" id = "company"  value = "<?= $_GET['company'] ?>">
            <div class="mb-3">
                <label for="usuario" class="form-label"><?php echo $texts['usuario']; ?></label>
                <input type="text" id="usuario" name="usuario" class="form-control" placeholder="<?php echo $texts['placeholder_user']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><?php echo $texts['password']; ?></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="<?php echo $texts['placeholder_pass']; ?>" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary"><?php echo $texts['boton']; ?></button>
            </div>
        </form>
        
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {

    // 1. Manejar cambio de Idioma
    $('#select-lang').on('change', function() {
        const langSeleccionado = $(this).val();

        $.ajax({
            url: '../cambiar_idioma.php',
            type: 'POST',
            data: { lang: langSeleccionado },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Recargamos para que el servidor lea la nueva sesión de idioma
                    location.reload(); 
                }
            }
        });
    });

    // 2. Manejar envío del Login por jQuery AJAX
    $('#login-form').on('submit', function(e) {
        e.preventDefault(); // Evita recargar la página

        const formData = $(this).serialize();

        $.ajax({
            url: '../login.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    localStorage.setItem('apiToken', response.jwtToken);
                    window.location.href = '../home.php';
                } else {
                    $('#alert-container').html(`
                        <div class="alert alert-danger py-2">${response.message}</div>
                    `);
                }
            },
            error: function() {
                $('#alert-container').html(`
                    <div class="alert alert-danger py-2">Error en el servidor.</div>
                `);
            }
        });
    });
});
</script>

</body>
</html>
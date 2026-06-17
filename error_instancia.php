<?php
// Opcional pero recomendado: enviar un código de error HTTP en lugar de un "200 OK"
http_response_code(400); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instancia no definida</title>
    <style>
        /* Reseteo básico y tipografía del sistema */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6; /* Gris muy claro */
            color: #1f2937; /* Gris oscuro para el texto principal */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        
        /* Contenedor principal */
        .container {
            text-align: center;
            max-width: 420px;
            padding: 2.5rem 2rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin: 1rem;
        }

        /* Icono minimalista */
        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            line-height: 1;
        }

        /* Tipografía */
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }
        p {
            font-size: 0.95rem;
            color: #6b7280; /* Gris medio para texto secundario */
            line-height: 1.6;
            margin: 0 0 1.5rem 0;
        }

        /* Caja de ejemplo */
        .example-box {
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            padding: 0.75rem;
            border-radius: 6px;
            font-family: monospace;
            color: #4b5563;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="icon">🏢</div>
        <h1>Instancia no definida</h1>
        <p>Para acceder al sistema, necesitas ingresar a través de la URL asignada específicamente para tu empresa.</p>
        
        <div class="example-box">
            https://www.eventgo.solutions/<strong>tu-empresa</strong>/login
        </div>
    </div>

</body>
</html>
<?php
ob_start();
session_start(); 
header('Content-Type: application/json');

    include_once 'config/config.php';     
    include_once 'config/database.php';    
    $database = new Database();
    $pdo = $database->getConnection();

    // Capturar fechas del frontend (con valores por defecto para el año actual si no se envían)
    $fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] . ' 00:00:00' : '2026-01-01 00:00:00';
    $fecha_fin    = isset($_GET['fin']) ? $_GET['fin'] . ' 23:59:59' : '2026-12-31 23:59:59';
    $tipo         = isset($_GET['tipo']) ? $_GET['tipo']  : '';
    $id         = isset($_GET['id']) ? $_GET['id']  : '';

    $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 2026;    

    if ($tipo == 'rentas'){
        switch ($id) {
            case '1':

                $sql = "SELECT
                            SUM(lead_detail.Quantity) as unidades, 
                            SUM(lead_detail.Price) as total,
                            categories.Nombre
                        FROM
                            lead
                            INNER JOIN lead_detail ON lead.Id = lead_detail.IdLead
                            INNER JOIN products AS `products A` ON lead_detail.IdProduct = `products A`.Id
                            INNER JOIN products_categories ON `products A`.Id = products_categories.Product
                            INNER JOIN categories ON products_categories.Category = categories.Id
                        WHERE lead.StartDateTime BETWEEN :fecha_inicio AND :fecha_fin
                        GROUP BY categories.Nombre";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin'    => $fecha_fin
                    ]);
                    $resultados = $stmt->fetchAll();

                    // Preparar los datos en el formato que Chart.js necesita
                    $labels = [];
                    $totales = [];
                    $unidades = [];

                    foreach ($resultados as $row) {
                        $labels[] = $row['Nombre'];
                        $totales[] = (float)$row['total'];
                        $unidades[] = (int)$row['unidades'];
                    }

                    echo json_encode([
                        'labels' => $labels,
                        'totales' => $totales,
                        'unidades' => $unidades
                    ]);

                } catch (\PDOException $e) {
                    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
                }                

            break;
            case '2':

                // Calcular los años dinámicamente según la fecha del servidor
                $anio_actual = (int)date('Y'); // 2026
                $anio_menos1 = $anio_actual - 1; // 2025
                $anio_menos2 = $anio_actual - 2; // 2024

                // Construir las fechas límite para el WHERE
                $fecha_inicio = "$anio_menos2-01-01 00:00:00";
                $fecha_fin    = "$anio_actual-12-31 23:59:59";

                // La consulta ahora usa las variables dinámicas de los años
                $sql = "SELECT 
                            MONTH(lead.StartDateTime) AS numero_mes,
                            SUM(CASE WHEN YEAR(lead.StartDateTime) = :anio2 THEN lead_detail.Price ELSE 0 END) AS venta_anio2,
                            SUM(CASE WHEN YEAR(lead.StartDateTime) = :anio1 THEN lead_detail.Price ELSE 0 END) AS venta_anio1,
                            SUM(CASE WHEN YEAR(lead.StartDateTime) = :anio0 THEN lead_detail.Price ELSE 0 END) AS venta_anio0
                        FROM 
                            lead
                            INNER JOIN lead_detail ON lead.Id = lead_detail.IdLead
                        WHERE 
                            lead.StartDateTime BETWEEN :fecha_inicio AND :fecha_fin
                        GROUP BY 
                            MONTH(lead.StartDateTime)
                        ORDER BY 
                            numero_mes ASC";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'anio2' => $anio_menos2,
                        'anio1' => $anio_menos1,
                        'anio0' => $anio_actual,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin'    => $fecha_fin
                    ]);
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $meses_es = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];

                    // Estructura inicial usando los años calculados como llaves de datos
                    $tabla_final = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $tabla_final[$i] = [
                            'mes' => $meses_es[$i],
                            $anio_menos2 => 0.00,
                            $anio_menos1 => 0.00,
                            $anio_actual => 0.00
                        ];
                    }

                    foreach ($resultados as $row) {
                        $m = (int)$row['numero_mes'];
                        $tabla_final[$m][$anio_menos2] = (float)$row['venta_anio2'];
                        $tabla_final[$m][$anio_menos1] = (float)$row['venta_anio1'];
                        $tabla_final[$m][$anio_actual] = (float)$row['venta_anio0'];
                    }

                    // Enviamos también los años calculados en la cabecera del JSON para que JS sepa qué columnas pintar
                    echo json_encode([
                        'anios' => [
                            'menos2' => $anio_menos2,
                            'menos1' => $anio_menos1,
                            'actual' => $anio_actual
                        ],
                        'datos' => array_values($tabla_final)
                    ]);

                } catch (\PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }                

            break; 
            case '3':

                $sql = "SELECT
                            `products A`.`Name` AS producto,
                            SUM(lead_detail.Quantity) AS Qty,
                            (SUM(lead_detail.Quantity) / SUM(SUM(lead_detail.Quantity)) OVER()) * 100 AS PorcentajeQty,
                            SUM(lead_detail.Price) / SUM(lead_detail.Quantity) AS AvgPrice,
                            SUM(lead_detail.Price) AS Sales,
                            (SUM(lead_detail.Price) / SUM(SUM(lead_detail.Price)) OVER()) * 100 AS PorcentajeSales
                        FROM
                            lead
                            INNER JOIN lead_detail ON lead.Id = lead_detail.IdLead
                            INNER JOIN products AS `products A` ON lead_detail.IdProduct = `products A`.Id
                        WHERE 
                            lead.StartDateTime BETWEEN :fecha_inicio AND :fecha_fin
                        GROUP BY
                            `products A`.Id, `products A`.`Name`
                        ORDER BY 
                            Sales DESC";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin'    => $fecha_fin
                    ]);
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo json_encode($resultados);
                } catch (\PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }                

            break; 
            case '4':

                $sql = "SELECT 
                            MONTH(sub.StartDateTime) AS mes_numero,
                            SUM(sub.TotalPagado) AS total_pagado,
                            SUM(sub.TotalaPagar - sub.TotalPagado) AS total_pendiente
                        FROM (
                            SELECT 
                                lead.Id,
                                lead.StartDateTime,
                                lead.Total AS TotalaPagar,
                                SUM(payments.Amount) AS TotalPagado
                            FROM 
                                payments
                                INNER JOIN lead ON payments.IdLead = lead.Id
                            WHERE 
                                payments.Estatus = 'A' 
                                AND YEAR(lead.StartDateTime) = :anio
                            GROUP BY 
                                lead.Id
                        ) AS sub
                        GROUP BY 
                            MONTH(sub.StartDateTime)
                        ORDER BY 
                            mes_numero ASC";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['anio' => $anio]);
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    
                    // Inicializar los 12 meses en 0
                    $pagado = array_fill(0, 12, 0.00);
                    $pendiente = array_fill(0, 12, 0.00);

                    foreach ($resultados as $row) {
                        $index = (int)$row['mes_numero'] - 1;
                        $pagado[$index] = (float)$row['total_pagado'];
                        // Si el pendiente da negativo por algún error de captura, lo dejamos en 0
                        $pendiente[$index] = max(0, (float)$row['total_pendiente']);
                    }

                    echo json_encode([
                        'labels' => $meses,
                        'pagado' => $pagado,
                        'pendiente' => $pendiente
                    ]);
                } catch (\PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }                

            break;                      
            default: // Executed if no case matches
                echo "";
            break;
        }            

    }
    else{

        switch ($id) {
            case '1':

                $sql = "SELECT 
                            SUM(sale_items.Quantity) as unidades, 
                            SUM(sale_items.Price * sale_items.Quantity) as total,
                            categories.Nombre
                        FROM
                            sales
                            INNER JOIN sale_items ON sales.Id = sale_items.sale_id
                            INNER JOIN products AS `products A` ON sale_items.product_id = `products A`.Id
                            INNER JOIN products_categories ON `products A`.Id = products_categories.Product
                            INNER JOIN categories ON products_categories.Category = categories.Id
                        WHERE sales.created_at BETWEEN :fecha_inicio AND :fecha_fin
                        GROUP BY categories.Nombre";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin'    => $fecha_fin
                    ]);
                    $resultados = $stmt->fetchAll();

                    // Preparar los datos en el formato que Chart.js necesita
                    $labels = [];
                    $totales = [];
                    $unidades = [];

                    foreach ($resultados as $row) {
                        $labels[] = $row['Nombre'];
                        $totales[] = (float)$row['total'];
                        $unidades[] = (int)$row['unidades'];
                    }

                    echo json_encode([
                        'labels' => $labels,
                        'totales' => $totales,
                        'unidades' => $unidades
                    ]);

                } catch (\PDOException $e) {
                    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
                }                

            break;
            case '2':

                // Calcular los años dinámicamente según la fecha del servidor
                $anio_actual = (int)date('Y'); // 2026
                $anio_menos1 = $anio_actual - 1; // 2025
                $anio_menos2 = $anio_actual - 2; // 2024

                // Construir las fechas límite para el WHERE
                $fecha_inicio = "$anio_menos2-01-01 00:00:00";
                $fecha_fin    = "$anio_actual-12-31 23:59:59";

                // La consulta ahora usa las variables dinámicas de los años
                $sql = "SELECT 
                            MONTH(sales.created_at) AS numero_mes,
                            SUM(CASE WHEN YEAR(sales.created_at) = :anio2 THEN (sale_items.Price * sale_items.quantity) ELSE 0 END) AS venta_anio2,
                            SUM(CASE WHEN YEAR(sales.created_at) = :anio1 THEN (sale_items.Price * sale_items.quantity) ELSE 0 END) AS venta_anio1,
                            SUM(CASE WHEN YEAR(sales.created_at) = :anio0 THEN (sale_items.Price * sale_items.quantity) ELSE 0 END) AS venta_anio0
                        FROM 
                            sales
                            INNER JOIN sale_items ON sales.Id = sale_items.sale_id
                        WHERE 
                            sales.created_at BETWEEN :fecha_inicio AND :fecha_fin
                        GROUP BY 
                            MONTH(sales.created_at)
                        ORDER BY 
                            numero_mes ASC";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'anio2' => $anio_menos2,
                        'anio1' => $anio_menos1,
                        'anio0' => $anio_actual,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin'    => $fecha_fin
                    ]);
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $meses_es = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];

                    // Estructura inicial usando los años calculados como llaves de datos
                    $tabla_final = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $tabla_final[$i] = [
                            'mes' => $meses_es[$i],
                            $anio_menos2 => 0.00,
                            $anio_menos1 => 0.00,
                            $anio_actual => 0.00
                        ];
                    }

                    foreach ($resultados as $row) {
                        $m = (int)$row['numero_mes'];
                        $tabla_final[$m][$anio_menos2] = (float)$row['venta_anio2'];
                        $tabla_final[$m][$anio_menos1] = (float)$row['venta_anio1'];
                        $tabla_final[$m][$anio_actual] = (float)$row['venta_anio0'];
                    }

                    // Enviamos también los años calculados en la cabecera del JSON para que JS sepa qué columnas pintar
                    echo json_encode([
                        'anios' => [
                            'menos2' => $anio_menos2,
                            'menos1' => $anio_menos1,
                            'actual' => $anio_actual
                        ],
                        'datos' => array_values($tabla_final)
                    ]);

                } catch (\PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }                

            break; 
            case '3':

                $sql = "SELECT
                            `products A`.`Name` AS producto,
                            SUM(sale_items.Quantity) AS Qty,
                            (SUM(sale_items.Quantity) / SUM(SUM(sale_items.Quantity)) OVER()) * 100 AS PorcentajeQty,
                            SUM(sale_items.Price * sale_items.Quantity) / SUM(sale_items.Quantity) AS AvgPrice,
                            SUM(sale_items.Price * sale_items.Quantity) AS Sales,
                            (SUM(sale_items.Price * sale_items.Quantity) / SUM(SUM(sale_items.Price * sale_items.Quantity)) OVER()) * 100 AS PorcentajeSales
                        FROM
                            sales
                            INNER JOIN sale_items ON sales.Id = sale_items.sale_id
                            INNER JOIN products AS `products A` ON sale_items.product_id = `products A`.Id
                        WHERE 
                            sales.created_at BETWEEN :fecha_inicio AND :fecha_fin
                        GROUP BY
                            `products A`.Id, `products A`.`Name`
                        ORDER BY 
                            Sales DESC";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin'    => $fecha_fin
                    ]);
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo json_encode($resultados);
                } catch (\PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }                

            break; 
            case '4':

                $sql = "SELECT 
                            MONTH(sub.created_at) AS mes_numero,
                            SUM(sub.TotalPagado) AS total_pagado,
                            SUM(sub.TotalaPagar - sub.TotalPagado) AS total_pendiente
                        FROM (
                            SELECT 
                                sales.Id,
                                sales.created_at,
                                sales.total_amount AS TotalaPagar,
                                SUM(payments_sale.Amount) AS TotalPagado
                            FROM 
                                payments_sale
                                INNER JOIN sales ON payments_sale.IdSale= sales.id
                            WHERE 
                                payments_sale.Estatus = 'A' 
                                AND YEAR(sales.created_at) = :anio
                            GROUP BY 
                                sales.Id
                        ) AS sub
                        GROUP BY 
                            MONTH(sub.created_at)
                        ORDER BY 
                            mes_numero ASC";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['anio' => $anio]);
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    
                    // Inicializar los 12 meses en 0
                    $pagado = array_fill(0, 12, 0.00);
                    $pendiente = array_fill(0, 12, 0.00);

                    foreach ($resultados as $row) {
                        $index = (int)$row['mes_numero'] - 1;
                        $pagado[$index] = (float)$row['total_pagado'];
                        // Si el pendiente da negativo por algún error de captura, lo dejamos en 0
                        $pendiente[$index] = max(0, (float)$row['total_pendiente']);
                    }

                    echo json_encode([
                        'labels' => $meses,
                        'pagado' => $pagado,
                        'pendiente' => $pendiente
                    ]);
                } catch (\PDOException $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }                

            break;                      
            default: // Executed if no case matches
                echo "";
            break;
        }


    }
?>
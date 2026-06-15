<?php
ob_start();
session_start(); 
header('Content-Type: application/json');

    include_once 'config/config.php';     
    include_once 'config/database.php';    
    $database = new Database();
    $pdo = $database->getConnection();
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
?>
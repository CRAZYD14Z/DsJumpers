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

    // Tu consulta SQL adaptada con placeholders para que sea dinámica
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
?>
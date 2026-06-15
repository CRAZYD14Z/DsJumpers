<?php
ob_start();
session_start(); 
header('Content-Type: application/json');

    include_once 'config/config.php';     
    include_once 'config/database.php';    
    $database = new Database();
    $pdo = $database->getConnection();


    $fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] . ' 00:00:00' : '2026-01-01 00:00:00';
    $fecha_fin    = isset($_GET['fin']) ? $_GET['fin'] . ' 23:59:59' : '2026-12-31 23:59:59';

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
?>
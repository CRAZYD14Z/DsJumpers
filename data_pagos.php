<?php
ob_start();
session_start(); 
header('Content-Type: application/json');

    include_once 'config/config.php';     
    include_once 'config/database.php';    
    $database = new Database();
    $pdo = $database->getConnection();
// Capturar el año del filtro (por defecto el año actual 2026)
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 2026;

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
?>
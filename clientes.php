<?php
header('Content-Type: application/json; charset=utf-8');

// Tu lógica de base de datos aquí...
$data = [
    "items" => [
        ["id" => 1, "nombre" => "Juan", "direccion" => "Calle 1"],
        ["id" => 2, "nombre" => "Maria", "direccion" => "Calle 2"]
    ]
];

echo json_encode($data);
exit;
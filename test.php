<?php
/**
 * Calcula el costo de envío acumulativo por tramos.
 */
function calcularCostoAcumulativo(array $millas_costos, float $costo_extra, float $total_millas): float {
    $costo_total = 0;
    $millas_recorridas = 0;

    // Ordenamos los rangos de menor a mayor
    ksort($millas_costos);

    foreach ($millas_costos as $limite => $precio_milla) {
        if ($total_millas > $millas_recorridas) {
            // Calculamos cuántas millas hay en este tramo actual
            $millas_en_este_tramo = min($total_millas, $limite) - $millas_recorridas;
            
            $costo_total += ($millas_en_este_tramo * $precio_milla);
            $millas_recorridas += $millas_en_este_tramo;
        }
    }

    // Si aún quedan millas después de pasar por todos los rangos, aplicamos el costo extra
    if ($total_millas > $millas_recorridas) {
        $millas_restantes = $total_millas - $millas_recorridas;
        $costo_total += ($millas_restantes * $costo_extra);
    }

    return $costo_total;
}

// --- Ejemplo de uso con tus datos ---
$tarifas = [
    10 => 0,  // 0-10 millas: $0
    20 => 2, // 11-20 millas: $18 c/u
    30 => 3  // 21-25 millas: $15 c/u
];

$millaExtra = 5; // Costo después de la milla 25
$distancia = 35;

$total = calcularCostoAcumulativo($tarifas, $millaExtra, $distancia);

echo "Total para $distancia millas: $" . $total;
?>
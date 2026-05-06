<?php
    ob_start();
    session_start(); 
    include_once 'config/config.php';     
    include_once 'config/database.php';    
    $database = new Database();
    $db = $database->getConnection();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mes  = isset($_GET['mes'])  ? (int)$_GET['mes']  : (int)date('m');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

// ---------------------------------------------------------------------------
// Catálogo de estatus
// ---------------------------------------------------------------------------
$estatus = [
    'quoted'  => ['label' => 'Confirmado',   'color' => '#10b981'],
    'pending'   => ['label' => 'Pendiente',    'color' => '#f59e0b'],
    'cancelado'   => ['label' => 'Cancelado',    'color' => '#ef4444'],
    'en_curso'    => ['label' => 'En curso',     'color' => '#3b82f6'],
    'completado'  => ['label' => 'Completado',   'color' => '#8b5cf6'],
    'reprogramado'=> ['label' => 'Reprogramado', 'color' => '#ec4899'],
];

            $sql = "
            
                SELECT 
                id as idev,
                date(StartDateTime) as fecha,
                CASE 
                        WHEN Organization > 0 THEN NombreOrganizacion 
                        WHEN Customer > 0 THEN CONCAT(NombreCliente, ' ', ApellidosCliente)
                        ELSE 'Sin identificar'
                END AS titulo,
                DATE_FORMAT(StartDateTime, '%H:%i') as hora,
                TIMESTAMPDIFF(HOUR, StartDateTime, EndDateTime) AS duracion,
                `Status` AS estatus,
                lugar as lugar,
                Note1 as `desc`
                FROM v_leads  where MONTH(StartDateTime) = :mes AND YEAR(StartDateTime) = :anio

            ";

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            $banco = $stmt->fetchAll(); 

// ---------------------------------------------------------------------------
// Banco de eventos simulados — se filtra por mes/año
// ---------------------------------------------------------------------------
$banco1 = [
    ['titulo'=>'Reunión de equipo',          'hora'=>'09:00', 'duracion'=>60,  'estatus'=>'confirmado',   'lugar'=>'Sala A',       'desc'=>'Revisión semanal de avances'],
    ['titulo'=>'Llamada con cliente',         'hora'=>'11:30', 'duracion'=>45,  'estatus'=>'pendiente',    'lugar'=>'Zoom',         'desc'=>'Demo del nuevo módulo'],
    ['titulo'=>'Capacitación interna',        'hora'=>'10:00', 'duracion'=>120, 'estatus'=>'en_curso',     'lugar'=>'Sala B',       'desc'=>'Taller de metodologías ágiles'],
    ['titulo'=>'Entrega de informe',          'hora'=>'17:00', 'duracion'=>30,  'estatus'=>'completado',   'lugar'=>'Drive',        'desc'=>'Informe Q2 enviado a dirección'],
    ['titulo'=>'Cita médica',                 'hora'=>'08:30', 'duracion'=>60,  'estatus'=>'confirmado',   'lugar'=>'Clínica Norte', 'desc'=>'Revisión anual'],
    ['titulo'=>'Presentación de producto',    'hora'=>'14:00', 'duracion'=>90,  'estatus'=>'cancelado',    'lugar'=>'Auditorio',    'desc'=>'Cancelada por falta de quórum'],
    ['titulo'=>'Sprint planning',             'hora'=>'09:00', 'duracion'=>120, 'estatus'=>'confirmado',   'lugar'=>'Teams',        'desc'=>'Planificación del sprint 14'],
    ['titulo'=>'Revisión de contratos',       'hora'=>'16:00', 'duracion'=>60,  'estatus'=>'pendiente',    'lugar'=>'Jurídico',     'desc'=>'Pendiente firma de partes'],
    ['titulo'=>'Almuerzo con proveedor',      'hora'=>'13:30', 'duracion'=>90,  'estatus'=>'confirmado',   'lugar'=>'Restaurante El Centro','desc'=>'Negociación de condiciones'],
    ['titulo'=>'Webinar de seguridad',        'hora'=>'11:00', 'duracion'=>60,  'estatus'=>'reprogramado', 'lugar'=>'Online',       'desc'=>'Reprogramado al siguiente viernes'],
    ['titulo'=>'Demo interna',                'hora'=>'15:00', 'duracion'=>45,  'estatus'=>'en_curso',     'lugar'=>'Sala C',       'desc'=>'Muestra de nuevo dashboard'],
    ['titulo'=>'Revisión de KPIs',            'hora'=>'10:30', 'duracion'=>30,  'estatus'=>'completado',   'lugar'=>'Dirección',    'desc'=>'KPIs del mes revisados'],
    ['titulo'=>'Taller de liderazgo',         'hora'=>'08:00', 'duracion'=>180, 'estatus'=>'confirmado',   'lugar'=>'RRHH',         'desc'=>'Formación ejecutiva'],
    ['titulo'=>'Actualización de sistemas',   'hora'=>'22:00', 'duracion'=>120, 'estatus'=>'programado',   'lugar'=>'Servidores',   'desc'=>'Mantenimiento nocturno'],
    ['titulo'=>'Comité directivo',            'hora'=>'09:00', 'duracion'=>120, 'estatus'=>'confirmado',   'lugar'=>'Sala Ejecutiva','desc'=>'Revisión de estrategia anual'],
    ['titulo'=>'Entrevista candidato',        'hora'=>'12:00', 'duracion'=>60,  'estatus'=>'pendiente',    'lugar'=>'RRHH',         'desc'=>'Senior Developer'],
    ['titulo'=>'Seguimiento de proyecto',     'hora'=>'14:30', 'duracion'=>45,  'estatus'=>'en_curso',     'lugar'=>'Teams',        'desc'=>'Avance fase 3'],
    ['titulo'=>'Firma de contrato',           'hora'=>'11:00', 'duracion'=>30,  'estatus'=>'confirmado',   'lugar'=>'Notaría',      'desc'=>'Contrato con nuevo cliente'],
    ['titulo'=>'Retrospectiva sprint',        'hora'=>'17:00', 'duracion'=>60,  'estatus'=>'completado',   'lugar'=>'Sala B',       'desc'=>'Lecciones aprendidas'],
    ['titulo'=>'Revisión presupuestal',       'hora'=>'10:00', 'duracion'=>90,  'estatus'=>'cancelado',    'lugar'=>'Finanzas',     'desc'=>'Cancelado — pendiente de datos'],
];

// ---------------------------------------------------------------------------
// Generar fechas aleatorias pero deterministas por mes/año
// ---------------------------------------------------------------------------
$diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
$eventos   = [];
$seed      = $mes * 100 + ($anio % 100);   // semilla fija por mes+año

foreach ($banco as $i => $e) {
    // día determinista
    $dia  = (($seed + $i * 7 + $i * $i) % $diasEnMes) + 1;
    $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

    // Asignar ID único
    $e['id']    = $anio * 10000 + $mes * 100 + $i;
    //$e['fecha'] = $fecha;

    // Info de estatus
    $st = $e['estatus'];
    $e['estatus_info'] = isset($estatus[$st])
        ? $estatus[$st]
        : ['label' => ucfirst($st), 'color' => '#6b7280'];

    $eventos[] = $e;
}

// Ordenar por fecha y hora
usort($eventos, fn($a,$b) => strcmp($a['fecha'].$a['hora'], $b['fecha'].$b['hora']));

echo json_encode([
    'ok'      => true,
    'mes'     => $mes,
    'anio'    => $anio,
    'total'   => count($eventos),
    'eventos' => $eventos,
    'estatus_catalogo' => $estatus,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
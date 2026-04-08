<?php
    ob_start();
    session_start(); 
    include_once '../config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();


    $Tipo = $_POST['tipo'] ?? null;

    if (!$Tipo) {
        echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos.']);
        exit;
    }    

    $fecha = $_POST['fecha'];

    $stmtRuta = $db->prepare("INSERT INTO daily_route (date,id_vehicle,id_driver,polyline,status) 
                                VALUES (?, ?, ?, ?, ?)");

    $stmtDetalle = $db->prepare("INSERT INTO route_stops (id_route, id_operation, visit_order) 
                                    VALUES (?, ?, ?)");    

    $updtOP = $db->prepare("UPDATE operation_master SET id_vehicle = ?, orden = ? WHERE Id_operation = ? ");

    if ($Tipo == 'optima'){

        $json_data = $_POST['todas_las_rutas'] ?? null;
        $rutas = json_decode($json_data, true);

        foreach ($rutas as $item) {
            $v = $item['vehiculo'];
            $dr = $item['datosRuta'];
            $envios = $item['envios'];
            //echo "Vehiculo: ".$v['id']." Ruta:".$dr['polyline'];
            $V = str_replace("V", "", $v['id']);
            $stmtRuta->execute([
                $fecha, 
                $V,
                0, 
                $dr['polyline'],
                1
            ]);
            
            $idRutaInsertada = $db->lastInsertId();

            foreach ($envios as $index => $envio) {
                //echo "Envio ".$envio['id']."</br>"; 
                $E = str_replace("E", "", $envio['id']);
                $orden = $index + 1;
                $stmtDetalle->execute([
                    $idRutaInsertada,
                    $E,
                    $orden
                ]);                

                $updtOP->execute([$V,$orden,$E]);
            }
        }
    }
    else{

        //echo "Vehiculo: ".$_POST['id_vehiculo']." Ruta:".$_POST['polyline'];
        $puntos_envio = $_POST['puntos_envio'] ?? null;
        $envios = json_decode($puntos_envio, true);        
        $V = str_replace("V", "", $_POST['id_vehiculo']);
        $stmtRuta->execute([
            $fecha, 
            $V,
            0, 
            $_POST['polyline'],
            1
        ]);
        
        $idRutaInsertada = $db->lastInsertId();        
        
        foreach ($envios as $index => $envio) {
            //echo "Envio ".$envio['id']."</br>"; 
            $E = str_replace("E", "", $envio['id']);
            $orden = $index + 1;
            $stmtDetalle->execute([
                $idRutaInsertada,
                $E,
                $orden
            ]);

            $updtOP->execute([$V,$orden,$E]);
        }

    }
?>
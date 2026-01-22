<?php
    ob_start();
    session_start(); 
    include_once '../config/database.php'; 
    $database = new Database();
    $conexion = $database->getConnection();

    $action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
    $Respuesta = '¡No existe coincidencia!';
    if($action == 'ajax'){
        $Idioma = $_SESSION['Idioma'];
        $valor    = strip_tags($_REQUEST['valor']);
        $campo    = strip_tags($_REQUEST['campo']);
        $receptor = strip_tags($_REQUEST['receptor']);
        $tabla    = strip_tags($_REQUEST['tabla']);
        
        $sql = "SELECT TablaDts2, CampoFiltro2, CampoValor2, CampoDescripcion2, Filtro2 
                FROM modal_add 
                WHERE Tabla = :tabla AND Campo = :campo";
        //echo $sql;
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':tabla' => $tabla,
            ':campo' => $campo
        ]);

        $myArray = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $TablaDts         = $row['TablaDts2'];
            $CampoFiltro      = $row['CampoFiltro2'];
            $CampoValor       = $row['CampoValor2'];
            $CampoDescripcion = $row['CampoDescripcion2'];
            $Filtro           = $row['Filtro2'];

            $sql2 = "SELECT $CampoValor AS Id, $CampoDescripcion AS Descripcion 
                    FROM $TablaDts 
                    WHERE $Filtro $CampoFiltro = :valor AND (Idioma ='$Idioma' OR Idioma ='')
                    ORDER BY $CampoDescripcion";
            //echo $sql2;
            $stmt2 = $conexion->prepare($sql2);
            $stmt2->execute([':valor' => $valor]);

            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $myArray[] = [
                    'Id'          => $row2['Id'],
                    'Descripcion' => $row2['Descripcion']
                ];
            }
        }

    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($myArray);
    exit();    
    //echo $Respuesta;
?>
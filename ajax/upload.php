<?php
//require_once ("../sesion.php");
//require_once ("../../db.php");
if (isset($_FILES['upload_file'])) {    


    $archivo = $_FILES["upload_file"]["tmp_name"]; 
    $tamanio = $_FILES["upload_file"]["size"];
    $tipo    = $_FILES["upload_file"]["type"];
    $nombre  = $_FILES["upload_file"]["name"];
    //$titulo  = $_POST["titulo"];    

    $fileName = "file_".uniqid();
    $partes_ruta = pathinfo("tmp/" . $_FILES['upload_file']['name']);
    if(move_uploaded_file($_FILES['upload_file']['tmp_name'], "tmp/" .$fileName.".".$partes_ruta['extension'])){

        $archivo = "tmp/" .$fileName.".".$partes_ruta['extension'];
        //$fp = fopen($archivo, "rb");
        //$contenido = fread($fp, $tamanio);
        //$contenido = addslashes($contenido);
        //fclose($fp);
        //$contenido = '';         
        //$archivo = $fileName.".".$partes_ruta['extension'];
        //$qry = "INSERT INTO archivos VALUES (0,'$nombre','$archivo','$contenido','$tipo')";
        //$query = mysqli_query($conexion,$qry);

        echo $fileName.".".$partes_ruta['extension'];
    //} else {
        //echo $_FILES['upload_file']['name']. " KO";
    }
    exit;
} else {
    echo "No files uploaded ...";
}   

?>
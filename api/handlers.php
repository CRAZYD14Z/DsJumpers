<?php
// api/Handlers.php
// Incluye las librerías de JWT
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// ----------------------------------------------------
// A. LÓGICA DE AUTENTICACIÓN
// ----------------------------------------------------
function handle_login_request($db, $data) {
    
    // --- Esta es la lógica copiada del antiguo login.php ---
    // Simulación de verificación de credenciales
    if (isset($data->username) && $data->username == "admin" && $data->password == "1234") {
        
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60); // 1 hora
        $issuer = URL_BASE & "/api/"; 

        $token_payload = array(
            "iss" => $issuer,
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "data" => array(
                "id" => 1,
                "nombre_usuario" => "AdminAPI"
            )
        );
        
        try {
            // Usamos la constante SECRET_KEY definida en config/config.php
            $jwt = JWT::encode($token_payload, SECRET_KEY, 'HS256');
            
            http_response_code(200);
            echo json_encode(array(
                "message" => "Inicio de sesión exitoso.",
                "jwt" => $jwt
            ));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => "No se pudo generar el token."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Credenciales inválidas."));
    }
}
// ----------------------------------------------------
// B. LÓGICA CRUD
// ----------------------------------------------------
function handle_generic_crud($table_name,$db, $method, $id, $data) {    
    global $IDS;
    $allowed_tables = [
        'clientes',
        'customers',
        'categories',
        'customer_type',
        'wharehouses',
        'gifcard',
        'products',
        'products_categories',
        'products_images',
        'products_files',
        'packing_list',
        'related_products',
        'distance_charges',
        'distance_charges_zip_code',
        'distance_charges_distance',
        'distance_charges_states',
        'distance_charges_totals',
        'account',
        'venues',
        'organizations',
        'surfaces',
        'upselling_products',
        'relationship_products',
        'cost_products',
        'item_prices',
        'products_item_price',
        'customer_addresses'
    ];
    if (!in_array($table_name, $allowed_tables)) {
        http_response_code(400);
        echo json_encode(array("message" => "Tabla $table_name no permitida"));
        return;
    }    
    switch ($method) {   
        // ------------------------------------------------------------------
        case 'GET': 
        // ------------------------------------------------------------------
            if (!isset($_GET['page'])) {

                $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Data' ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $Campos = '';
                    unset($Campos);
                    foreach ($resultados as $registro) {
                        $Campos[]=$registro['Campo'];
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Data no creada."));
                }

                $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id' ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $Where = '';
                    unset($Where);
                    foreach ($resultados as $registro) {
                        $Where[]=$registro['Campo'];
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Id no creada."));
                }
                $Campos = implode(', ', $Campos);
                $Where = implode(' = ? AND ', $Where);
                // READ ONE
                $query = "SELECT $Campos FROM  $table_name  WHERE $Where = ? LIMIT 0,1";
                if ($table_name == 'products_item_price'){
                    $query = "
                        SELECT
                            products_item_price.Producto, 
                            products_item_price.ItemPrice, 
                            item_prices.JsonPrice, 
                            products_item_price.Taxable
                        FROM
                            products_item_price
                            INNER JOIN
                            item_prices
                            ON 
                                products_item_price.ItemPrice = item_prices.Id
                                WHERE products_item_price.Producto = ?
                    ";
                }
                    
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    http_response_code(200);
                    echo json_encode($row);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Registro no encontrado."));
                }
            } else {
                // READ ALL CON PAGINACIÓN                 
                // 1. Obtener y validar parámetros de paginación
                // Usamos 10 registros por defecto si no se especifica el límite
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                // Usamos la página 1 por defecto
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $like = isset($_GET['like']) ? $_GET['like'] : '';
                $lang = isset($_GET['lang']) ? $_GET['lang'] : 'es';
                // Asegurar que limit y page sean positivos
                $limit = max(1, $limit); 
                $page = max(1, $page);                
                // Calcular el OFFSET (el punto de partida)
                $offset = ($page - 1) * $limit;



                if ($like!=""){
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Where' ORDER BY Id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados) {
                        $Where = '';
                        unset($Where);
                        foreach ($resultados as $registro) {
                            $Where[]=$registro['Campo'];
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Estructura Where no creada."));
                    }                
                    $Where = " WHERE ".implode(" LIKE ? OR ", $Where)." LIKE ? ";
                }
                else{
                    $Where ='';
                }  
                
                // PARA CONSULTA DE REGISTROS RELACIONADOS!
                    $Where2 = '';
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id2'";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados2) {
                        
                        unset($Where2);
                        foreach ($resultados2 as $registro) {
                            $Where2[]=$registro['Campo'];
                        }
                        $Where2 = implode(" = ? AND ", $Where2).' = ? AND';
                    }    

                    if ($Where2 !="")
                        $Where2 = substr($Where2, 0, -3);

                    if ($Where != "" AND $Where2 != ""){
                        $Where.=" AND ".$Where2;
                    }
                    elseif ($Where == "" AND $Where2 != ""){
                        $Where = ' WHERE '.$Where2;
                    }
                // PARA CONSULTA DE REGISTROS RELACIONADOS!

                //if ($table_name == 'products_categories'){
                //    echo $Where;
                //    die();                    
                //}

                // 2. Consulta para obtener el CONTEO TOTAL de registros
                if ($table_name == 'gifcard')
                    $v_table_name = 'v_gifcard';
                elseif ($table_name == 'products_categories')
                    $v_table_name = 'v_products_categories'; 
                elseif ($table_name == 'distance_charges')
                    $v_table_name = 'v_distance_charges';
                elseif ($table_name == 'packing_list')
                    $v_table_name = 'v_packing_list';
                elseif ($table_name == 'upselling_products')
                    $v_table_name = 'v_upselling_products';                
                elseif ($table_name == 'distance_charges_distance'){
                    $v_table_name = 'v_distance_charges_distance';
                    //if ($Where =="")
                    //    $Where.= ' WHERE Idioma = ? ';
                    //else
                    $Where.= ' AND Idioma = ? ';
                }
                elseif ($table_name == 'distance_charges_states'){
                    $v_table_name = 'v_distance_charges_states';
                    //if ($Where =="")
                    //    $Where.= ' WHERE Idioma = ? ';
                    //else
                    $Where.= ' AND Idioma = ? ';
                }
                elseif ($table_name == 'relationship_products'){
                    $v_table_name = 'v_relationship_products';
                    //if ($Where =="")
                    //    $Where.= ' WHERE Idioma = ? ';
                    //else
                    $Where.= ' AND Idioma = ? ';
                }                  

                else
                    $v_table_name = $table_name;
                
                $count_query = "SELECT COUNT(*) as total FROM $v_table_name $Where";
                //echo $count_query;
                $count_stmt = $db->prepare($count_query);
                $p=0;
                if ($like!=""){
                    foreach ($resultados as $registro) {
                        $p++;
                        $search_pattern = "%" . $like . "%";
                        $count_stmt->bindValue($p, $search_pattern);
                    }
                }
                $idx = 0;
                foreach ($resultados2 as $registro) {
                    $p++;                    
                    $search_pattern = $IDS[$idx];
                    $count_stmt->bindValue($p, $search_pattern);
                    $idx++;
                }                

                if ($table_name == 'distance_charges_distance'){
                    //echo $count_query;
                    $p++;  
                    $count_stmt->bindValue($p, $lang);
                }
                if ($table_name == 'distance_charges_states'){
                    //echo $count_query;
                    $p++;  
                    $count_stmt->bindValue($p, $lang);
                }
                if ($table_name == 'relationship_products'){
                    //echo $count_query;
                    $p++;  
                    $count_stmt->bindValue($p, $lang);
                }                 

                $count_stmt->execute();
                $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                // Calcular el total de páginas
                $total_pages = ceil($total_rows / $limit);

                //$query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Lst' ORDER BY Id";

                $query = "
                    SELECT
                        la.Campo, 
                        modal_add.TipoCampo
                    FROM
                        listado_ajax AS la
                        INNER JOIN
                        modal_add
                        ON 
                            la.Tabla = modal_add.Tabla AND
                            la.Campo = modal_add.Campo
                    WHERE
                        la.Tabla = ? AND
                        la.Tipo = 'Lst'
                    ORDER BY
                        la.Id ASC                
                ";                

                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $LCampos = '';
                    $LLCampos = '';
                    $TCampos = '';
                    unset($LCampos);
                    unset($LLCampos);
                    unset($TCampos);
                    foreach ($resultados as $registro) {
                        $LCampos[]=$registro['Campo'];
                        if ($registro['TipoCampo'] == 'img'){
                            $LLCampos[]=$registro['Campo'];
                            $TCampos[]=$registro['TipoCampo'];
                        }
                            
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Lst no creada."));
                }                
                $Campos = implode(', ', $LCampos);

                $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Order' ORDER BY Id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $table_name);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($resultados) {
                    $Order = '';
                    unset($Order);
                    foreach ($resultados as $registro) {
                        $Order[]=$registro['Campo'];
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Estructura Order no creada."));
                }                
                $Order = implode(',', $Order); 

                $where_clauses = [];
                $param_index = 1;
                $Where = '';

                if ($like!=""){
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Where' ORDER BY Id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados) {
                        //$Where = '';
                        //unset($Where);
                        
                        foreach ($resultados as $registro) {
                            //$Where[]=$registro['Campo'];
                        $param_name = ":like_" . $param_index;
                        $where_clauses[] = $registro['Campo'] . " LIKE " . $param_name;
                        $param_index++;                            
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Estructura Where no creada."));
                    }                
                    //$Where = " WHERE ".implode(" LIKE ? OR ", $Where)." LIKE ? ";
                    $Where = " WHERE " . implode(' OR ', $where_clauses);
                }
                else{
                    $Where ='';
                }   
                
                // PARA CONSULTA DE REGISTROS RELACIONADOS!
                    $Where2 = '';
                    $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id2'";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $table_name);
                    $stmt->execute();
                    $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultados2) {
                        
                        unset($Where2);
                        foreach ($resultados2 as $registro) {
                            $param_name = ":id_" . $param_index;
                            $Where2[]=$registro['Campo'] . " = " . $param_name;;
                        }
                        $Where2 = implode(" AND ", $Where2)." AND";
                    }    

                    if ($Where2 !="")
                        $Where2 = substr($Where2, 0, -3);

                    if ($Where != "" AND $Where2 != ""){
                        $Where.=" AND ".$Where2;
                    }
                    elseif ($Where == "" AND $Where2 != ""){
                        $Where = ' WHERE '.$Where2;
                    }
                // PARA CONSULTA DE REGISTROS RELACIONADOS!                
                
                if ($table_name == 'gifcard')
                    $v_table_name = 'v_gifcard';
                elseif ($table_name == 'products_categories')
                    $v_table_name = 'v_products_categories';  
                elseif ($table_name == 'distance_charges')
                    $v_table_name = 'v_distance_charges';
                elseif ($table_name == 'packing_list')
                    $v_table_name = 'v_packing_list';
                elseif ($table_name == 'upselling_products')
                    $v_table_name = 'v_upselling_products';
                elseif ($table_name == 'distance_charges_distance'){
                    $v_table_name = 'v_distance_charges_distance';
                    $Where.= ' AND Idioma = :lang ';
                }
                elseif ($table_name == 'distance_charges_states'){
                    $v_table_name = 'v_distance_charges_states';
                    $Where.= ' AND Idioma = :lang ';
                }
                elseif ($table_name == 'relationship_products'){
                    $v_table_name = 'v_relationship_products';
                    $Where.= ' AND Idioma = :lang ';
                }                                
                else
                    $v_table_name = $table_name;
                // 3. Consulta para obtener los DATOS PAGINADOS
                $data_query = "SELECT $Campos FROM $v_table_name $Where ORDER BY $Order DESC LIMIT :limit OFFSET :offset";                
                //echo $data_query;
                $data_stmt = $db->prepare($data_query);
                $param_index=1;
                if ($like!=""){
                    foreach ($resultados as $registro) {
                        $search_pattern = "%" . $like . "%";
                        $data_stmt->bindValue(":like_" . $param_index, $search_pattern, PDO::PARAM_STR);
                        $param_index++;
                    }
                }
                $idx=0;
                foreach ($resultados2 as $registro) {
                    $search_pattern = $IDS[$idx];
                    $data_stmt->bindValue(":id_" . $param_index, $search_pattern, PDO::PARAM_STR);
                    $param_index++;
                    $idx++;
                }                

                if ($table_name == 'distance_charges_distance'){
                    $data_stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                    //echo $data_query;
                }
                if ($table_name == 'distance_charges_states'){
                    $data_stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                    //echo $data_query;
                }
                if ($table_name == 'relationship_products'){
                    $data_stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
                    //echo $data_query;
                }                   

                $data_stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $data_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $data_stmt->execute();

                $registros = array();
                while ($row = $data_stmt->fetch(PDO::FETCH_ASSOC)) {

                foreach ($row as $columna => $valor) {
                    if (isset($LLCampos)){
                        // Buscamos si el nombre de la columna existe en el array de nombres de campos
                        $indice = array_search($columna, $LLCampos);
                        
                        // Si existe y el tipo asociado es 'img'
                        if ($indice !== false && $TCampos[$indice] === 'img') {
                            // Reemplazamos el valor por el tag HTML (ajusta la ruta según necesites)
                            $row[$columna] = '<img src="ajax/tmp/' . htmlspecialchars($valor) . '" alt="imagen" style="width:50px;">';
                        }
                    }
                }                    

                    $registros[] = $row;
                }
                // 4. Reguperar Titulos
                
                $query = "
                    SELECT
                        listado_ajax.Campo,
                        titulos_campos_tablas.Titulo,
                        listado_ajax.Alineacion
                    FROM
                        listado_ajax
                        INNER JOIN
                        titulos_campos_tablas
                        ON 
                            listado_ajax.Tabla = titulos_campos_tablas.Tabla AND
                            listado_ajax.Campo = titulos_campos_tablas.Campo		
                        WHERE 
                            listado_ajax.Tabla = ? AND
                            listado_ajax.Tipo = 'Lst' AND
                            titulos_campos_tablas.Idioma = ?
                        ORDER BY
                            listado_ajax.id            
                ";                            
                $stmt = $db->prepare($query);
                $stmt->bindValue(1, $table_name);
                $stmt->bindValue(2, $lang);
                $stmt->execute();
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $query = "
                    SELECT
                        listado_ajax.Campo,
                        modal_add.TipoCampo
                    FROM
                        listado_ajax
                        INNER JOIN
                        modal_add
                        ON 
                            listado_ajax.Tabla = modal_add.Tabla AND
                            listado_ajax.Campo = modal_add.Campo
                    WHERE 
                        listado_ajax.Tabla = ? AND
                        listado_ajax.Tipo = 'Lst'
                    ORDER BY
                            listado_ajax.id                                        
                ";                            
                $stmt = $db->prepare($query);
                $stmt->bindValue(1, $table_name);
                $stmt->execute();
                $resultados_t = $stmt->fetchAll(PDO::FETCH_ASSOC);                

                // 5. Construir la Respuesta
                http_response_code(200);
                echo json_encode(array(
                    "metadata" => array(
                        "total_registros" => (int)$total_rows,
                        "total_paginas" => (int)$total_pages,
                        "pagina_actual" => (int)$page,
                        "registros_por_pagina" => (int)$limit
                    ),
                    "data" => $registros,
                    "titulos" => $resultados,
                    "tipos" => $resultados_t
                ));
            }
            break;

        // ------------------------------------------------------------------
        case 'POST': 
        // ------------------------------------------------------------------
        // INSERTA UN NUEVO REGISTRO
            $query = "SELECT Campo, Requerido,TipoCampo FROM modal_add WHERE Tabla = ? AND TipoCampo <> 'auto' AND TipoCampo <> 'insert' AND TipoCampo <> 'Lst' AND TipoCampo <> 'Lst' AND TipoCampo <> 'option' AND TipoCampo <> 'titulo' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $Campos = '';
                $Valores = '';
                unset($Campos);
                unset($Valores);
                foreach ($resultados as $registro) {
                    if ($registro['Requerido'] == 'X' AND empty($data->{$registro['Campo']}) ){
                        http_response_code(404);
                        echo json_encode(array("message" => $registro['Campo']." Requerido."));
                        die();
                    }
                    $Campos[]=$registro['Campo'];
                    $Valores[]=":". strtolower($registro['Campo']);
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Add no creada."));
            }

            $CamposInsert= '';
            $CamposInsertValues = '';
            $query = "SELECT Campo, CampoValor   FROM modal_add WHERE Tabla = ? AND TipoCampo = 'insert' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados2) {
                foreach ($resultados2 as $registro2) {
                    $CamposInsert.= ','.$registro2['Campo'];
                    $CamposInsertValues .= ','.$registro2['CampoValor'];
                }
            }

            $Campos = implode(', ', $Campos);
            $Valores = implode(', ', $Valores);

            $query = "INSERT INTO  $table_name ($Campos $CamposInsert) VALUES($Valores $CamposInsertValues) ";

            $stmt = $db->prepare($query);

            foreach ($resultados as $registro) {
                $campo = strtolower($registro['Campo']);
                $valor = isset($data->{$registro['Campo']}) 
                    ? htmlspecialchars(strip_tags($data->{$registro['Campo']}))
                    : null;
                $tipocampo = strtolower($registro['TipoCampo']);
                if ($tipocampo =='checkbox'){
                    if ($valor == 'on'){
                        $valor = 1;
                    }
                    else{
                        $valor = 0;
                    }
                    $stmt->bindValue(":" . $campo, $valor);
                }
                else{
                    $stmt->bindValue(":" . $campo, $valor);
                }
            }

            
            
            if ($stmt->execute()) {
                $lastInsertId = $db->lastInsertId();
                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','$table_name',$lastInsertId,0,'I','Registro Insertado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();
                http_response_code(201); // Created
                echo json_encode(array("message" => "Registro creado exitosamente."));
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(array("message" => "No se pudo crear el registro."));
            }
            break;

        // ------------------------------------------------------------------
        case 'PUT':
        // ------------------------------------------------------------------
            // UPDATE (Actualizar un registro existente)
            $query = "SELECT Campo, TipoCampo, Requerido,TipoCampo FROM modal_edit WHERE Tabla = ?  ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $Campos = '';
                unset($Campos);
                foreach ($resultados as $registro) {
                    if ($registro['Requerido'] == 'X' AND empty($data->{$registro['Campo']}) ){
                        http_response_code(404);
                        echo json_encode(array("message" => $registro['Campo']." Requerido."));
                    }
                    if ($registro['TipoCampo']!= 'auto' AND $registro['TipoCampo']!= 'hidden' AND $registro['TipoCampo']!= 'option' AND $registro['TipoCampo']!= 'titulo')
                        $Campos[]=$registro['Campo']." = :". strtolower($registro['Campo']);
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Edit no creada."));
            }        

            $Campos = implode(', ', $Campos);



            $CamposUpdate= '';
            
            $query = "SELECT Campo,CampoValor FROM modal_edit WHERE Tabla = ? AND TipoCampo = 'update' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados2) {
                foreach ($resultados2 as $registro2) {
                    $CamposUpdate.= ','.$registro2['Campo'].'='.$registro2['CampoValor'];
                }
            }            

            $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $Llaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($Llaves) {
                $Where = '';
                foreach ($Llaves as $llave) {
                    $Where.= $llave['Campo'] ." = :".strtolower($llave['Campo'])." AND";
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Id no creada."));
            }
            $Where=substr($Where, 0, -3);
            $query = "UPDATE $table_name SET $Campos $CamposUpdate WHERE $Where";
            $stmt = $db->prepare($query);            
            // Sanitizar y enlazar parámetros
            foreach ($resultados as $registro) {
                if ($registro['TipoCampo']!= 'auto' AND $registro['TipoCampo']!= 'hidden' AND $registro['TipoCampo']!= 'option' AND $registro['TipoCampo']!= 'titulo')
                {
                    $campo = strtolower($registro['Campo']);
                    $valor = isset($data->{$registro['Campo']}) 
                        ? htmlspecialchars(strip_tags($data->{$registro['Campo']}))
                        : null;
                    $tipocampo = strtolower($registro['TipoCampo']);
                    if ($tipocampo =='checkbox'){
                        if ($valor == 'on'){
                            $valor = 1;
                        }
                        else{
                            $valor = 0;
                        }
                        $stmt->bindValue(":" . $campo, $valor);
                    }
                    elseif ($tipocampo =='img'){
                        if ($data->{"file_".$registro['Campo']}!=""){
                            $stmt->bindValue(":" . $campo, $valor);
                        }else{
                            $valor = isset($data->{"file_".$registro['Campo']."_1"}) 
                                ? htmlspecialchars(strip_tags($data->{"file_".$registro['Campo']."_1"}))
                                : null;                            
                            $stmt->bindValue(":" . $campo, $valor);
                        }
                    }
                    elseif ($tipocampo =='html'){
                        $valor = isset($data->{$registro['Campo']}) 
                            ? (($data->{$registro['Campo']}))
                            : null;                            
                        $stmt->bindValue(":" . $campo, $valor);

                    }
                    else{
                        $stmt->bindValue(":" . $campo, $valor);
                    }                        


                    //$stmt->bindValue(":" . $campo, $valor);                    
                }
            }
            $Id1 = 0;
            $Id2 = 0;
            $IdC = 0;
            foreach ($Llaves as $llave) {
                $IdC++;
                $campo = strtolower($llave['Campo']);
                $valor = isset($data->{$llave['Campo']}) 
                    ? htmlspecialchars(strip_tags($data->{$llave['Campo']}))
                    : null;
                $stmt->bindValue(":" . $campo, $valor);
                if ($IdC == 1)
                    $Id1 = $valor;
                else
                    $Id2 = $valor;
            }                 

            if ($stmt->execute()) {

                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','$table_name','$Id1','$Id2','U','Registro Actualizado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();                

                http_response_code(200);
                echo json_encode(array("message" => "Registro actualizado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el registro."));
            }

            break;

        // ------------------------------------------------------------------
        case 'DELETE':
        // ------------------------------------------------------------------
            // DELETE (Eliminar un registro)
            $query = "SELECT Campo FROM listado_ajax WHERE Tabla = ? AND Tipo = 'Id' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $Llaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($Llaves) {
                $Where = '';
                foreach ($Llaves as $llave) {
                    $Where.= $llave['Campo'] ." = :".strtolower($llave['Campo'])." AND";
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Id no creada."));
            }
            $Where=substr($Where, 0, -3);


            $query = "SELECT Campo FROM modal_delete WHERE Tabla = ?  ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $table_name);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {

            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Estructura Delete no creada."));
            }               

            $query = "DELETE FROM $table_name WHERE $Where";
            $stmt = $db->prepare($query);
            $Id1 = 0;
            $Id2 = 0;
            $IdC = 0;
            foreach ($Llaves as $llave) {
                $IdC++;
                $campo = strtolower($llave['Campo']);
                $valor = isset($data->{$llave['Campo']}) 
                    ? htmlspecialchars(strip_tags($data->{$llave['Campo']}))
                    : null;
                $stmt->bindValue(":" . $campo, $valor);
                if ($IdC == 1)
                    $Id1 = $valor;
                else
                    $Id2 = $valor;                
            }               
            if ($stmt->execute()) {

                $InsertLog ="INSERT INTO log (FechaHora,Usuario,Tabla,Id,Id2,Tipo,Log) VALUES(now(),'','$table_name','$Id1','$Id2','D','Registro Borrado')";
                $stmt = $db->prepare($InsertLog);
                $stmt->execute();                

                http_response_code(200);
                echo json_encode(array("message" => "Registro eliminado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el Registro."));
            }
            break;

        // ------------------------------------------------------------------
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
            break;
    }
}
function get_json_price($table_name,$db, $method, $id, $data) {
    global $IDS;
    switch ($method) {
        case 'GET': 
            $query = "            
            SELECT
                item_prices.Id, 
                item_prices.JsonPrice, 
                item_prices.Taxable
            FROM
                item_prices
            WHERE
            item_prices.Id = ?
            ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }
}
function get_price($table_name,$db, $method, $id, $data) {

    global $IDS;
    switch ($method) {
        case 'GET': 
            $query = "
            SELECT
                products_item_price.ItemPrice, 
                item_prices.JsonPrice, 
                item_prices.Taxable
            FROM
                products_item_price
                INNER JOIN
                item_prices
                ON 
                    products_item_price.ItemPrice = item_prices.Id
            WHERE 
            products_item_price.Producto = ?            

            ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Registro no encontrado."));
            }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }

}   



?>
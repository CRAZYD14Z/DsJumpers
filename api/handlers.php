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
        'customer_addresses',
        'document_center',
        'clone_record',
        'copy_records',
        'price_lists',
        'detail_price_lists',
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
                if ($table_name == 'related_products')
                    $query = "SELECT $Campos FROM  v_related_products  WHERE $Where = ? LIMIT 0,1";
                    
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
                elseif ($table_name == 'related_products')
                    $v_table_name = 'v_related_products';
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
                elseif ($table_name == 'detail_price_lists')
                    $v_table_name = 'v_detail_price_lists';
                else
                    $v_table_name = $table_name;
                
                $count_query = "SELECT COUNT(*) as total FROM $v_table_name $Where";
                //echo $count_query;
                //if ($page == 2)
                //    echo $count_query ." -- "; 
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
                elseif ($table_name == 'related_products')
                    $v_table_name = 'v_related_products';                
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
                elseif ($table_name == 'detail_price_lists')
                    $v_table_name = 'v_detail_price_lists';                                                
                else
                    $v_table_name = $table_name;
                // 3. Consulta para obtener los DATOS PAGINADOS
                $data_query = "SELECT $Campos FROM $v_table_name $Where ORDER BY $Order ASC LIMIT :limit OFFSET :offset";                
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
                //if ($page == 2)
                //echo $data_query ." $limit $offset " ;                
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
            $query = "SELECT Campo, Requerido,TipoCampo FROM modal_add WHERE Tabla = ? AND TipoCampo <> 'auto' AND TipoCampo <> 'insert' AND TipoCampo <> 'Lst' AND TipoCampo <> 'button' AND TipoCampo <> 'option' AND TipoCampo <> 'titulo' ORDER BY Id";
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
                    if ($registro['TipoCampo']!= 'auto' AND $registro['TipoCampo']!= 'hidden' AND $registro['TipoCampo']!= 'option' AND $registro['TipoCampo']!= 'titulo' AND $registro['TipoCampo']!= 'button')
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
                if ($registro['TipoCampo']!= 'auto' AND $registro['TipoCampo']!= 'hidden' AND $registro['TipoCampo']!= 'option' AND $registro['TipoCampo']!= 'titulo' AND $registro['TipoCampo']!= 'button')
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
    function get_template($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $query = "
            SELECT
                Template
            FROM
                templates
            WHERE 
            Id = ?

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

function clone_record($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            if($IDS[0]== 'products'){
                $nuevoId = clonarRegistro($db, $IDS[0], $id, $IDS[1]);
                //clonarRegistrosRelacionados($db, 'products_item_price', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'products_categories', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'products_images', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'packing_list', 'Producto_pl', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'related_products', 'Producto_rp', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'upselling_products', 'Producto_up', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'relationship_products', 'Producto_sp', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'cost_products', 'Product', $id, $nuevoId);
                clonarRegistrosRelacionados($db, 'products_files', 'Product', $id, $nuevoId);


                $sql = "UPDATE products SET Name = :name_
                        WHERE Id = :id";

                $stmt = $db->prepare($sql);
                $stmt->execute(['name_' => $IDS[2],'id' => $nuevoId]);

                if ( is_numeric($nuevoId)){
                    http_response_code(200);
                    echo json_encode(array("Id" => $nuevoId));
                }
                else{
                    http_response_code(404);
                    echo json_encode(array("message" => $nuevoId));
                }
            }
            else{
                http_response_code(405);
                echo json_encode(array("message" => "Tabla no permitida"));
            }


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }    
}

function copy_records($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
        $nuevoId = $IDS[2];
        switch ($IDS[0]) {
            case 'packing_list':
                clonarRegistrosRelacionados($db, 'packing_list', 'Producto_pl', $nuevoId, $id);
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));
            break;
            case 'related_products':
                clonarRegistrosRelacionados($db, 'related_products', 'Producto_rp', $nuevoId, $id);
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'upselling_products':
                clonarRegistrosRelacionados($db, 'upselling_products', 'Producto_up', $nuevoId, $id);
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'relationship_products':
                clonarRegistrosRelacionados($db, 'relationship_products', 'Producto_sp', $nuevoId, $id);            
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;                                    
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Tabla *".$IDS[0]."* no permitida"));
            break;
        }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}


function clonarRegistro($pdo, $tabla, $id_registro, $columna_id = 'id') {
    try {
        $query_columnas = $pdo->prepare("DESCRIBE $tabla");
        $query_columnas->execute();
        $columnas = $query_columnas->fetchAll(PDO::FETCH_COLUMN);

        $columnas_filtradas = array_diff($columnas, [$columna_id]);
        
        $columnas_select = [];
        foreach ($columnas_filtradas as $col) {
            // Si la columna es de fecha, usamos la función NOW() de MySQL
            if (in_array(strtolower($col), ['fechacreacion', 'fechacambio'])) {
                $columnas_select[] = "NOW()";
            } else {
                $columnas_select[] = $col;
            }
        }

        $lista_columnas_insert = implode(', ', $columnas_filtradas);
        $lista_columnas_select = implode(', ', $columnas_select);

        $sql = "INSERT INTO $tabla ($lista_columnas_insert) 
                SELECT $lista_columnas_select 
                FROM $tabla 
                WHERE $columna_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id_registro]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

function clonarRegistrosRelacionados($pdo, $tabla, $columna_relacional, $id_antiguo, $id_nuevo) {
    try {
        // 1. Obtener información detallada de las columnas
        $stmt = $pdo->prepare("DESCRIBE $tabla");
        $stmt->execute();
        $detalles_columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $columnas_finales = [];
        $columnas_select = [];

        foreach ($detalles_columnas as $col) {
            $nombre_col = $col['Field'];
            $es_auto_increment = (strpos($col['Extra'], 'auto_increment') !== false);
            $es_primary = ($col['Key'] === 'PRI');

            // EXCLUIR si es autoincrementable o llave primaria (para que MySQL genere el nuevo ID)
            if ($es_auto_increment || $es_primary) {
                continue; 
            }

            $columnas_finales[] = $nombre_col;

            // 2. Lógica de valores para el SELECT
            if ($nombre_col === $columna_relacional) {
                // Reemplazamos el ID padre viejo por el nuevo
                $columnas_select[] = ":id_nuevo";
            } elseif (in_array(strtolower($nombre_col), ['fechacreacion', 'fechacambio'])) {
                // Seteamos timestamp actual
                $columnas_select[] = "NOW()";
            } else {
                // El resto de columnas se copian tal cual
                $columnas_select[] = $nombre_col;
            }
        }

        $lista_insert = implode(', ', $columnas_finales);
        $lista_select = implode(', ', $columnas_select);

        // 3. Ejecutar la inserción masiva
        $sql = "INSERT IGNORE INTO $tabla ($lista_insert) 
                SELECT $lista_select 
                FROM $tabla 
                WHERE $columna_relacional = :id_antiguo";
        //die($sql . "  $id_nuevo  $id_antiguo");
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_nuevo'   => $id_nuevo,
            'id_antiguo' => $id_antiguo
        ]);

        return $stmt->rowCount();

    } catch (PDOException $e) {
        return "Error en clonarRegistrosRelacionados: " . $e->getMessage();
    }
}

function orden($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'PUT': 
        $nuevoId = 'Orden Actualizado';
        //echo $data->{'Idp'};
        //echo $data->{'Id'};  
                $productId = $data->{'Idp'};
                $imageId = $data->{'Id'};

        switch ($data->{'orden'}) {
            case 'I':

                $stmt = $db->prepare("CALL sp_image_move_to_start(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();

                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));
            break;
            case 'A':
                
                $stmt = $db->prepare("CALL sp_image_move_up(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();                

                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'S':
                $stmt = $db->prepare("CALL sp_image_move_down(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();                   
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));                
            break;
            case 'U':
                $stmt = $db->prepare("CALL sp_image_move_to_end(:product_id, :image_id)");
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
                $stmt->execute();                   
                http_response_code(200);
                echo json_encode(array("Id" => $nuevoId));
            break;                                    
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Acción no permitida"));
            break;
        }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}

function ajustar_precio($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'PUT': 
            $lista = $data->{'lista'};
            $tipo_opcion = $data->{'tipo_opcion'};
            $lista_categoria = $data->{'lista_categoria'};
            $tipo_m_p  = $data->{'tipo_m_p'};
            $montoA  = $data->{'montoA'};
            $montoE  = $data->{'montoE'};
            $montoA = str_replace("$", "", $montoA);
            $montoA = str_replace(",", "", $montoA);
            $montoE = str_replace("$", "", $montoE);
            $montoE = str_replace(",", "", $montoE);            
            if ($tipo_opcion=='C'){

                $query = "INSERT INTO price_lists( Nombre, FechaHoraInicio,FechaHoraFin,Estatus,FechaCreacion,FechaCambio) 
                        SELECT :Nombre, FechaHoraInicio,FechaHoraFin,Estatus,now(),now()
                        FROM price_lists
                        WHERE 
                        Id = :Id";

                $stmt = $db->prepare($query);
                $stmt->bindValue(":Nombre", $lista_categoria);
                $stmt->bindValue(":Id", $lista);
                if ($stmt->execute()) {
                    $lastInsertId = $db->lastInsertId();
                    if ($montoA * 1 == 0 AND $montoE * 1 ==0){
                        $query = "INSERT INTO detail_price_lists( IdLista,IdItem,JsonPrice,Estatus_price,FechaCreacion,FechaCambio) 
                                SELECT :IdLista,IdItem,JsonPrice,Estatus_price,now(),now()
                                FROM detail_price_lists
                                WHERE 
                                IdLista = :Id";
                        $stmt = $db->prepare($query);
                        $stmt->bindValue(":IdLista", $lastInsertId);
                        $stmt->bindValue(":Id", $lista);                    
                        $stmt->execute();
                    }
                    else{
                    
                        $query = "SELECT IdItem, JsonPrice,Estatus_price FROM detail_price_lists WHERE IdLista = ? ";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(1, $lista);
                        $stmt->execute();
                        $Precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($Precios) {
                            foreach ($Precios as $Precio) {
                                $IdItem = $Precio['IdItem'] ;
                                $JsonPrice = $Precio['JsonPrice'];
                                $Estatus_price = $Precio['Estatus_price'];

                                $json_limpio = html_entity_decode($JsonPrice);
                                $datos = json_decode($json_limpio, true);
                                $id_item =0;
                                foreach ($datos as &$item) {
                                    if ($tipo_m_p == '$'){
                                        if ($id_item == 0)
                                            $item['precio'] = (string)($item['precio'] + $montoA);
                                        else
                                            $item['precio'] = (string)($item['precio'] + $montoE);
                                    }
                                    else{
                                        if ($id_item == 0)
                                            $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoA /100 )));
                                        else
                                            $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoE /100 )));
                                    }
                                    $id_item+1;
                                }
                                unset($item); 
                                $nuevo_json = json_encode($datos);
                                $variable_json = htmlentities($nuevo_json);
                                // INSERT DE PRECIOS CON AJUSTE!!
                                $queryI = "INSERT INTO detail_price_lists( IdLista,IdItem,JsonPrice,Estatus_price,FechaCreacion,FechaCambio)
                                                        values(:idLista,:idItem,:jsonPrice,:estatus_price,now(),now()) ";
                                $stmtI = $db->prepare($queryI);
                                $stmtI->bindValue(":idLista", $lastInsertId);
                                $stmtI->bindValue(":idItem", $IdItem);
                                $stmtI->bindValue(":jsonPrice", $variable_json);
                                $stmtI->bindValue(":estatus_price", $Estatus_price);
                                $stmtI->execute();
                                // INSERT DE PRECIOS CON AJUSTE!!
                            }
                        }                     

                    }
                    http_response_code(201); // Created
                    echo json_encode(array("message" => "Nueva lista registrada."));
                } else {
                    http_response_code(503); // Service Unavailable
                    echo json_encode(array("message" => "No se encotraron registros."));
                }
            }
            else{
                //Ajustar precio
                //$tipo_m_p -> $ %
                if ($lista_categoria  == 0){
                
                    $query = "SELECT IId, JsonPrice FROM detail_price_lists WHERE IdLista = ? ";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $lista);
                }
                else{
                    $query = "
                        SELECT
                            detail_price_lists.IId, 
                            detail_price_lists.JsonPrice
                        FROM
                            detail_price_lists
                            INNER JOIN
                            products_item_price
                            ON 
                                detail_price_lists.IdItem = products_item_price.ItemPrice
                            INNER JOIN
                            products_categories
                            ON 
                                products_item_price.Producto = products_categories.Product
                            WHERE 
                            detail_price_lists.IdLista = ? AND
                            products_categories.Category =?                    
                    ";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $lista);
                    $stmt->bindParam(2, $lista_categoria);
                }                    
                    $stmt->execute();
                    $Precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($Precios) {
                        foreach ($Precios as $Precio) {
                            $IId = $Precio['IId'] ;
                            $JsonPrice = $Precio['JsonPrice'];

                            $json_limpio = html_entity_decode($JsonPrice);
                            $datos = json_decode($json_limpio, true);
                            $id_item =0;
                            foreach ($datos as &$item) {
                                if ($tipo_m_p == '$'){
                                    if ($id_item == 0)
                                        $item['precio'] = (string)($item['precio'] + $montoA);
                                    else
                                        $item['precio'] = (string)($item['precio'] + $montoE);
                                }
                                else{
                                    if ($id_item == 0)
                                        $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoA /100 )));
                                    else
                                        $item['precio'] = (string)($item['precio'] * ( 1 + ( $montoE /100 )));
                                }
                                $id_item+1;
                            }
                            unset($item); 
                            $nuevo_json = json_encode($datos);
                            $variable_json = htmlentities($nuevo_json);
                            // UPDATE DE PRECIOS CON AJUSTE!!
                            $queryI = "UPDATE detail_price_lists SET JsonPrice = :jsonPrice, FechaCambio = now()
                                        WHERE IId = :iId";
                            $stmtI = $db->prepare($queryI);
                            $stmtI->bindValue(":iId", $IId);
                            $stmtI->bindValue(":jsonPrice", $variable_json);
                            $stmtI->execute();
                            // UPDATE DE PRECIOS CON AJUSTE!!
                        }
                        http_response_code(201); // Created
                        echo json_encode(array("message" => "Registros actualizados."));
                    }
                    else {
                        http_response_code(503); // Service Unavailable
                        echo json_encode(array("message" => "No se encotraron registros."));
                    }
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}
function get_products_categories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $IdCat = isset($_GET['IdCat']) ? (int)$_GET['IdCat'] : 0;
            $Date = isset($_GET['Date']) ? (int)$_GET['Date'] : date('Ymd');
            $Date = DateTime::createFromFormat('Ymd', $Date);
            $Date = $Date->format('Y-m-d');
                $query = "
                    SELECT * FROM v_items_prices_lists
                    WHERE Category = :idCat  AND 
                                Estatus_price_list = 1 AND
                                Estatus_price = 1 AND 
                    :date BETWEEN  FechaHoraInicio AND FechaHoraFin                                      
                ";                            
                $stmt = $db->prepare($query);
                $stmt->bindParam(':idCat', $IdCat, PDO::PARAM_INT);
                $stmt->bindParam(':date', $Date, PDO::PARAM_STR);
                //$stmt->bindValue(1, $IdCat);
                //$stmt->bindValue(2, $Date);
                $stmt->execute();
                $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode(array(
                    "products" => $resultados_p
                ));
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}

function get_related_products($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $IdP = isset($_GET['IdP']) ? (int)$_GET['IdP'] : 0;
            $Date = isset($_GET['Date']) ? (int)$_GET['Date'] : date('Ymd');
            $Date = DateTime::createFromFormat('Ymd', $Date);
            $Date = $Date->format('Y-m-d');
                $query = "
                    SELECT * FROM v_related_products_prices_lists
                    WHERE Producto_rp = :idp  AND 
                                Estatus_price_list = 1 AND
                                Estatus_price = 1 AND 
                    :date BETWEEN  FechaHoraInicio AND FechaHoraFin                                      
                ";                            
                $stmt = $db->prepare($query);
                $stmt->bindParam(':idp', $IdP, PDO::PARAM_INT);
                $stmt->bindParam(':date', $Date, PDO::PARAM_STR);
                //$stmt->bindValue(1, $IdCat);
                //$stmt->bindValue(2, $Date);
                $stmt->execute();
                $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode(array(
                    "products" => $resultados_p
                ));
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }   
}

function get_organizatios($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q!=""){
            $query = "
                SELECT Id, Nombre, Direccion FROM organizations
                WHERE Nombre LIKE :q AND Estatus = 'A'
            ";                            
            $stmt = $db->prepare($query);

            // Adiciona os curingas para busca parcial
            $searchTerm = "%" . $Q . "%";
            $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);

            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "items" => $resultados_p
            ));
            }
            else{
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }  
}

function get_customers($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q!=""){
            $query = "
                SELECT Id, CONCAT(Nombres,' ', Apellidos) as Nombre, Direccion FROM customers
                WHERE ( Nombres LIKE :q  OR Apellidos LIKE :q2)  AND Estatus = 'A'
            ";                            
            $stmt = $db->prepare($query);

            // Adiciona os curingas para busca parcial
            $searchTerm = "%" . $Q . "%";
            $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':q2', $searchTerm, PDO::PARAM_STR);

            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "items" => $resultados_p
            ));
            }
            else{
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function get_venues($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $Q = isset($_GET['q']) ? $_GET['q'] : '';
            if ($Q!=""){
            $query = "
                SELECT Id, Nombre, Direccion FROM venues
                WHERE Nombre LIKE :q 
            ";                            
            $stmt = $db->prepare($query);

            // Adiciona os curingas para busca parcial
            $searchTerm = "%" . $Q . "%";
            $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);

            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "items" => $resultados_p
            ));
            }
            else{
                echo json_encode(array(
                    "items" => []
                ));
            }
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function distance_charge($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $ZIP = $id;
            if ($ZIP!=""){
                    //RECUPERAMOS EL COSTO EXTRA POR MILLA
                    $query = "SELECT Rate, Zip,Distance,State,Total,Restriction FROM distance_charges  LIMIT 1";
                    
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    //$costo_extra = $stmt->fetchColumn();
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($data) {
                        // Ahora accedes a cada valor por su nombre
                        $costo_extra =  $data['Rate'];
                        $Zip =  $data['Zip'];
                        $Distance =  $data['Distance'];
                        
                    }           
                    //echo " ** $Distance **";
                    if ($Distance==1){
                    $total_millas = 35; //AQUI VA LA FUNCION DE GOOGLE MAPS PARA SABER LAS MILLAS

                    // 1. Consultamos los rangos ordenados
                        $query = "SELECT MinM, MaxM, ChargeD, ChargeType 
                                FROM distance_charges_distance 
                                ORDER BY MinM ASC";
                    //echo $query;    
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $rangos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $costo_total = 0;
                        $max_milla_cubierta = 0;

                        // 2. Procesamos cada tramo
                        foreach ($rangos as $rango) {
                            $min = (float)$rango['MinM'];
                            $max = (float)$rango['MaxM'];
                            $cargo = (float)$rango['ChargeD'];
                            $tipo = strtoupper($rango['ChargeType']);

                            // Si el viaje no llega ni al inicio de este rango, lo ignoramos
                            if ($total_millas < $min) {
                                continue;
                            }

                            // Determinamos el final del tramo actual
                            $milla_final_en_tramo = min($total_millas, $max);
                            
                            if ($tipo === 'F') {
                                // Cargo FIJO: Se suma el monto completo si el envío toca este rango
                                $costo_total += $cargo;
                            } elseif ($tipo === 'M') {
                                // Cargo POR MILLA: Calculamos cuántas millas del total caen en este rango
                                $millas_a_cobrar = $milla_final_en_tramo - ($min - 1); 
                                $costo_total += ($millas_a_cobrar * $cargo);
                            }

                            // Guardamos hasta dónde llega la cobertura de la tabla
                            $max_milla_cubierta = max($max_milla_cubierta, $max);
                        }

                        // 3. Si hay millas excedentes fuera de la tabla, aplicamos el costo extra
                        if ($total_millas > $max_milla_cubierta) {
                            $millas_excedentes = $total_millas - $max_milla_cubierta;
                            $costo_total += ($millas_excedentes * $costo_extra);
                        }
                        //$costo_total;
                        
                            $respuesta = [
                                "status" => "success",
                                "total_millas" => $total_millas,
                                "costo_total" => round($costo_total, 2),
                            ];

                            echo json_encode(array(
                                "cost" => $respuesta
                            ));                    
                    }
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
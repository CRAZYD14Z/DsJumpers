<?php
ob_start();
session_start(); 
header('Content-Type: application/json');

    include_once 'config/config.php';     
    include_once 'config/database.php';    
    $database = new Database();
    $pdo = $database->getConnection();

    
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }


/* ════════════════════════════════════════════
   HELPERS
════════════════════════════════════════════ */
function resp(bool $ok, $data = null, string $msg = ''): void {
    echo json_encode(['success' => $ok, 'data' => $data, 'message' => $msg]);
    exit;
}

function body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?: [];
}

/* ════════════════════════════════════════════
   ROUTER
════════════════════════════════════════════ */
$method = $_SERVER['REQUEST_METHOD'];
$action = $method === 'GET'
    ? ($_GET['action'] ?? '')
    : (body()['action'] ?? '');

try {
    switch ($action) {

        /* ── Clientes ── */
        case 'search_customers': searchCustomers(); break;
        case 'create_customer':  createCustomer();  break;
        case 'update_customer':  updateCustomer();  break;

        /* ── Direcciones ── */
        case 'get_addresses':    getAddresses();    break;
        case 'create_address':   createAddress();   break;
        case 'update_address':   updateAddress();   break;

        /* ── Productos ── */
        case 'search_products':  searchProducts();  break;
        case 'get_stock':        getStock();        break;

        /* ── Ventas ── */
        case 'create_sale':      createSale();      break;
        case 'update_sale':      updateSale();      break;
        case 'get_sale':         getSale();         break;
        case 'list_sales':       listSales();       break;
        case 'get_country':      listCountry();     break;
        case 'get_states':        listStates();      break;

        default:
            http_response_code(400);
            resp(false, null, 'Acción desconocida: ' . $action);
    }
} catch (PDOException $e) {
    http_response_code(500);
    resp(false, null, 'Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    resp(false, null, $e->getMessage());
}


/* ════════════════════════════════════════════
   CLIENTES
════════════════════════════════════════════ */
function searchCustomers(): void {
    global $pdo;
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    $stmt = $pdo->prepare("
        SELECT id, firstname, lastname, email, phone, state
        FROM sale_customers
        WHERE (firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR phone LIKE ?)
          AND state != 'inactive'
        ORDER BY firstname, lastname
        LIMIT 30
    ");
    $stmt->execute([$q, $q, $q, $q]);
    resp(true, $stmt->fetchAll());
}

function createCustomer(): void {
    global $pdo;
    $b = body();
    $required = ['firstname','lastname','email','phone','state'];
    foreach ($required as $f) {
        if (empty($b[$f])) resp(false, null, "Campo requerido: $f");
    }

    // Check duplicate email
    $check = $pdo->prepare("SELECT id FROM sale_customers WHERE email = ?");
    $check->execute([$b['email']]);
    if ($check->fetch()) resp(false, null, 'Ya existe un cliente con ese email.');

    $pass = !empty($b['password']) ? password_hash($b['password'], PASSWORD_BCRYPT) : password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO sale_customers (firstname, lastname, email, phone, state, password)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$b['firstname'], $b['lastname'], $b['email'], $b['phone'], $b['state'], $pass]);
    $id = $pdo->lastInsertId();

    $row = $pdo->prepare("SELECT id, firstname, lastname, email, phone, state FROM sale_customers WHERE id = ?");
    $row->execute([$id]);
    resp(true, $row->fetch(), 'Cliente creado');
}

function updateCustomer(): void {
    global $pdo;
    $b = body();
    if (empty($b['id'])) resp(false, null, 'id requerido');

    $stmt = $pdo->prepare("
        UPDATE sale_customers
        SET firstname=?, lastname=?, email=?, phone=?, state=?
        WHERE id=?
    ");
    $stmt->execute([$b['firstname'], $b['lastname'], $b['email'], $b['phone'], $b['state'], $b['id']]);
    resp(true, ['id' => $b['id']], 'Cliente actualizado');
}

/* ════════════════════════════════════════════
   DIRECCIONES
════════════════════════════════════════════ */
function getAddresses(): void {
    global $pdo;
    $cid = (int)($_GET['customer_id'] ?? 0);
    if (!$cid) resp(false, null, 'customer_id requerido');
    $stmt = $pdo->prepare("
        SELECT * FROM sale_customer_addresses
        WHERE customer_id = ?
        ORDER BY is_default DESC, alias ASC
    ");
    $stmt->execute([$cid]);
    resp(true, $stmt->fetchAll());
}

function createAddress(): void {
    global $pdo;
    $b = body();
    $required = ['customer_id','alias','state','city','street','colonia','zip'];
    foreach ($required as $f) {
        if (empty($b[$f])) resp(false, null, "Campo requerido: $f");
    }

    $db = $pdo;

    // Si es default, limpiar otros defaults del cliente
    if (!empty($b['is_default'])) {
        $db->prepare("UPDATE sale_customer_addresses SET is_default=0 WHERE customer_id=?")->execute([$b['customer_id']]);
    }

    $stmt = $db->prepare("
        INSERT INTO sale_customer_addresses (customer_id, alias, state, city, street, colonia, zip, reference, is_default, country)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $b['customer_id'], $b['alias'], $b['state'], $b['city'],
        $b['street'], $b['colonia'], $b['zip'],
        $b['references'] ?? null, !empty($b['is_default']) ? 1 : 0, $b['country'],
    ]);
    $id = $db->lastInsertId();

    $row = $db->prepare("SELECT * FROM sale_customer_addresses WHERE id=?");
    $row->execute([$id]);
    resp(true, $row->fetch(), 'Dirección guardada');
}

function updateAddress(): void {
    global $pdo;
    $b = body();
    if (empty($b['id'])) resp(false, null, 'id requerido');

    $db = $pdo;
    if (!empty($b['is_default'])) {
        $db->prepare("UPDATE sale_customer_addresses SET is_default=0 WHERE customer_id=?")->execute([$b['customer_id']]);
    }

    $stmt = $db->prepare("
        UPDATE sale_customer_addresses
        SET alias=?, state=?, city=?, street=?, colonia=?, zip=?, reference=?, is_default=?
        WHERE id=?
    ");
    $stmt->execute([
        $b['alias'], $b['state'], $b['city'], $b['street'],
        $b['colonia'], $b['zip'], $b['references'] ?? null,
        !empty($b['is_default']) ? 1 : 0, $b['id'],
    ]);
    resp(true, ['id' => $b['id']], 'Dirección actualizada');
}

/* ════════════════════════════════════════════
   PRODUCTOS
════════════════════════════════════════════ */
function searchProducts(): void {
    global $pdo;
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    $stmt = $pdo->prepare("
        SELECT Id, Name, Active, OnlyRequest, For_Sale, SalePrice, Discount,
               (SELECT Image FROM products_images WHERE Product = p.Id ORDER BY Orden ASC LIMIT 1) AS image
        FROM products p
        WHERE (Name LIKE ?)
          AND Active = '1'
          AND For_Sale = '1'
        ORDER BY Name
        LIMIT 50
    ");
    $stmt->execute([$q]);
    resp(true, $stmt->fetchAll());
}

function getStock(): void {
    global $pdo;
    $pid = (int)($_GET['product_id'] ?? 0);
    if (!$pid) resp(false, null, 'product_id requerido');

    $stmt = $pdo->prepare("
        SELECT SUM(Quantity_for_sale) AS total, Id_wharehouse, Location
        FROM inventory_stock
        WHERE Id_product = ? AND Active = 1
        GROUP BY Id_product
    ");
    $stmt->execute([$pid]);
    $row = $stmt->fetch();
    resp(true, $row ?: ['total' => 0]);
}

/* ════════════════════════════════════════════
   VENTAS — CREAR
════════════════════════════════════════════ */
function createSale(): void {
    global $pdo;
    $b = body();
    $required = ['customer_id','address_id','total_amount','payer_name','payer_lastname','payer_email','payment_method','items'];
    foreach ($required as $f) {
        if (!isset($b[$f]) || $b[$f] === '') resp(false, null, "Campo requerido: $f");
    }
    if (empty($b['items'])) resp(false, null, 'La venta debe incluir al menos un producto');

    $db = $pdo;
    $db->beginTransaction();
    try {
        // 1. Validar existencias (productos que no sean OnlyRequest)
        foreach ($b['items'] as $item) {
            $prod = $db->prepare("SELECT OnlyRequest FROM products WHERE Id=?");
            $prod->execute([$item['product_id']]);
            $p = $prod->fetch();
            if ($p && $p['OnlyRequest'] != '1') {
                $stock = $db->prepare("SELECT COALESCE(SUM(Quantity_for_sale),0) AS total FROM inventory_stock WHERE Id_product=? AND Active=1");
                $stock->execute([$item['product_id']]);
                $s = $stock->fetch();
                if ($s['total'] < $item['quantity']) {
                    $db->rollBack();
                    resp(false, null, "Sin existencia suficiente para producto ID {$item['product_id']} (disponible: {$s['total']}, solicitado: {$item['quantity']})");
                }
            }
        }

        // 2. Insertar venta
        $stmt = $db->prepare("
            INSERT INTO sales (customer_id, address_id, total_amount, cart_notes, payer_name, payer_lastname,
                               payer_email, payment_method, gateway_token, device_fingerprint, cart_json, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $b['customer_id'],
            $b['address_id'],
            $b['total_amount'],
            $b['cart_notes'] ?? null,
            $b['payer_name'],
            $b['payer_lastname'],
            $b['payer_email'],
            $b['payment_method'],
            $b['gateway_token'] ?? '',
            $b['device_fingerprint'] ?? null,
            $b['cart_json'] ?? null,
            $b['payment_status'] ?? 'pending',
        ]);
        $saleId = $db->lastInsertId();

        // 3. Insertar items
        $itemStmt = $db->prepare("
            INSERT INTO sale_items (sale_id, product_id, product_name_snapshot, price, quantity, is_special_production)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($b['items'] as $item) {
            $itemStmt->execute([
                $saleId,
                $item['product_id'],
                $item['product_name_snapshot'],
                $item['price'],
                $item['quantity'],
                $item['is_special_production'] ?? 0,
            ]);
        }

        $db->commit();
        resp(true, ['sale_id' => $saleId], 'Venta registrada correctamente');

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/* ════════════════════════════════════════════
   VENTAS — ACTUALIZAR
════════════════════════════════════════════ */
function updateSale(): void {
    global $pdo;
    $b = body();
    if (empty($b['sale_id'])) resp(false, null, 'sale_id requerido');

    $db = $pdo;
    $db->beginTransaction();
    try {
        // Update sale header
        $stmt = $db->prepare("
            UPDATE sales
            SET customer_id=?, address_id=?, total_amount=?, cart_notes=?,
                payer_name=?, payer_lastname=?, payer_email=?,
                payment_method=?, gateway_token=?, payment_status=?, cart_json=?
            WHERE id=?
        ");
        $stmt->execute([
            $b['customer_id'],
            $b['address_id'],
            $b['total_amount'],
            $b['cart_notes'] ?? null,
            $b['payer_name'],
            $b['payer_lastname'],
            $b['payer_email'],
            $b['payment_method'],
            $b['gateway_token'] ?? '',
            $b['payment_status'] ?? 'pending',
            $b['cart_json'] ?? null,
            $b['sale_id'],
        ]);

        // Delete old items and re-insert
        $db->prepare("DELETE FROM sale_items WHERE sale_id=?")->execute([$b['sale_id']]);

        if (!empty($b['items'])) {
            $itemStmt = $db->prepare("
                INSERT INTO sale_items (sale_id, product_id, product_name_snapshot, price, quantity, is_special_production)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($b['items'] as $item) {
                $itemStmt->execute([
                    $b['sale_id'],
                    $item['product_id'],
                    $item['product_name_snapshot'],
                    $item['price'],
                    $item['quantity'],
                    $item['is_special_production'] ?? 0,
                ]);
            }
        }

        $db->commit();
        resp(true, ['sale_id' => $b['sale_id']], 'Venta actualizada');

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/* ════════════════════════════════════════════
   VENTAS — OBTENER
════════════════════════════════════════════ */
function getSale(): void {
    global $pdo;
    $id = (int)($_GET['sale_id'] ?? 0);
    if (!$id) resp(false, null, 'sale_id requerido');

    $db = $pdo;

    $saleStmt = $db->prepare("SELECT * FROM sales WHERE id=?");
    $saleStmt->execute([$id]);
    $sale = $saleStmt->fetch();
    if (!$sale) resp(false, null, "Venta #{$id} no encontrada");

    // Customer
    $custStmt = $db->prepare("SELECT id, firstname, lastname, email, phone, state FROM sale_customers WHERE id=?");
    $custStmt->execute([$sale['customer_id']]);
    $sale['customer'] = $custStmt->fetch();

    // Address
    $addrStmt = $db->prepare("SELECT * FROM sale_customer_addresses WHERE id=?");
    $addrStmt->execute([$sale['address_id']]);
    $sale['address'] = $addrStmt->fetch();

    // Items
    $itemStmt = $db->prepare("SELECT * FROM sale_items WHERE sale_id=?");
    $itemStmt->execute([$id]);
    $sale['items'] = $itemStmt->fetchAll();

    resp(true, $sale);
}

/* ════════════════════════════════════════════
   VENTAS — LISTAR
════════════════════════════════════════════ */
function listSales(): void {
    global $pdo;
    $limit  = min((int)($_GET['limit'] ?? 50), 200);
    $offset = (int)($_GET['offset'] ?? 0);
    $status = $_GET['status'] ?? '';
    $q      = '%' . ($_GET['q'] ?? '') . '%';

    $where = '1=1';
    $params = [];

    if ($status) { $where .= ' AND s.payment_status=?'; $params[] = $status; }
    if ($_GET['q'] ?? '') {
        $where .= ' AND (c.firstname LIKE ? OR c.lastname LIKE ? OR c.email LIKE ? OR s.id LIKE ?)';
        $params = array_merge($params, [$q,$q,$q,$q]);
    }

    $stmt = $pdo->prepare("
        SELECT s.id, s.total_amount, s.payment_status, s.payment_method, s.created_at,
               c.firstname, c.lastname, c.email
        FROM sales s
        LEFT JOIN sale_customers c ON c.id = s.customer_id
        WHERE $where
        ORDER BY s.id DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    resp(true, $stmt->fetchAll());
}

function listCountry(): void {
    global $pdo;
    $lng = 'es';
    $stmt = $pdo->prepare("SELECT Codigo, Pais FROM pais WHERE Idioma = ?");
    $stmt->execute([$lng]);
    resp(true, $stmt->fetchAll());
}

function listStates(): void {
    global $pdo;
    $lng = 'es';
    $ctry = $_GET['ctry'];
    $stmt = $pdo->prepare("
        SELECT Id, Estado FROM estados_pais WHERE Idioma = ? AND CodigoPais = ?
    ");
    $stmt->execute([$lng,$ctry]);
    resp(true, $stmt->fetchAll());
}
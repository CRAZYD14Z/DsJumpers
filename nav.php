<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fas fa-rocket me-2"></i>Gaxi Brincolines
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- INICIO -->
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">
                        <i class="fas fa-home"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Home" : "Inicio"; ?>
                    </a>
                </li>

                <!-- VENTAS Y EVENTOS -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar-alt"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Sales" : "Ventas"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="lead.php"><i class="fas fa-plus me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "New Lead" : "Nuevo Evento"; ?></a></li>
                        <li><a class="dropdown-item" href="leads.php"><i class="fas fa-list me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Leads" : "Eventos"; ?></a></li>
                        <li><a class="dropdown-item" href="pending_payments.php"><i class="fas fa-money-bill me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Payments" : "Pagos Pendientes"; ?></a></li>
                        <li><a class="dropdown-item" href="payment_report.php"><i class="fas fa-chart-line me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Reports" : "Reporte de Pagos"; ?></a></li>
                    </ul>
                </li>

                <!-- OPERACIÓN Y LOGÍSTICA -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-truck"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Operation" : "Operación"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="route.php">Armado de rutas</a></li>
                        <li><a class="dropdown-item" href="operation.php">Operación General</a></li>
                        <li><a class="dropdown-item" href="acondicionamiento.php">Acondicionamiento</a></li>
                        <li><a class="dropdown-item" href="monitor.php">Monitor</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=wharehouses">Almacenes</a></li>                                                
                        <li><a class="dropdown-item" href="crud.php?Id=inventory_stock">Inventario / Stock</a></li>
                    </ul>
                </li>

                <!-- CATÁLOGOS / CONFIGURACIÓN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Settings" : "Configuración"; ?>
                    </a>
                    <ul class="dropdown-menu scrollable-menu" style="max-height: 600px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Administración</h6></li>
                        <li><a class="dropdown-item" href="crud.php?Id=account">Cuenta</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=customers">Clientes</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=customer_type">Tipo Cliente</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=organizations">Organizaciones</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=venues">Lugares de eventos</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=referals">Referidos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="crud.php?Id=products">Productos</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=categories">Categorias</a></li>                        
                        <li><a class="dropdown-item" href="crud.php?Id=price_lists">Listas de Precios</a></li>                        
                        <li><a class="dropdown-item" href="crud.php?Id=item_prices">Item Prices</a></li>                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="crud.php?Id=discounts">Descuentos-Cupones-Tarifas</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=distance_charges">Cargos por distancia</a></li>
                        <li><a class="dropdown-item" href="documentcenter.php">Centro de Documentos</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=gifcard">Tarjetas de regalo</a></li>                
                        <li><a class="dropdown-item" href="crud.php?Id=surfaces">Superficies</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Logística</h6></li>
                        <li><a class="dropdown-item" href="crud.php?Id=wharehouses">Almacenes</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=venues">Lugares</a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=distance_charges">Cargos Distancia</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logout-link">
                            <i class="fas fa-sign-out-alt me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Sign Out" : "Salir"; ?>
                        </a></li>
                    </ul>
                </li>
            </ul>

            <!-- BUSCADOR E IDIOMA A LA DERECHA -->
            <div class="d-flex align-items-center">
                <form class="me-3" role="search">
                    <div class="input-group input-group-sm">
                        <input class="form-control" type="search" placeholder="Buscar...">
                        <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>

                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe"></i> <?php echo strtoupper($_SESSION['Idioma'])?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item lang-option" href="#" data-lang="es">Español</a></li>
                        <li><a class="dropdown-item lang-option" href="#" data-lang="en">English</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('logout-link').addEventListener('click', function(e) {
        e.preventDefault(); // Evita que el enlace redirija inmediatamente
        
        // 1. Borramos el token del navegador
        localStorage.removeItem('apiToken');
        
        // 2. Redirigimos al archivo PHP que cierra la sesión en el servidor
        window.location.href = 'logout.php';
    });
</script>  
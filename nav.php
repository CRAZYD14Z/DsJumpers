    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">DsJumpers</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
<div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="home.php"><?php if ($_SESSION['Idioma']== 'en'){echo "Home";}else{echo "Inicio";} ?></a>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-cog"></i>
            </a>
            <ul class="dropdown-menu">
                <?php if ($_SESSION['Idioma']== 'en'){?>
                <li><a class="dropdown-item" href="crud.php?Id=account">Account</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=categories">Categories</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=customers">Customers</a></li>                
                <li><a class="dropdown-item" href="crud.php?Id=customer_type">Customer Type</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=discounts">Discounts - Coupons - Fees</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=distance_charges">Distance Charges</a></li>
                <li><a class="dropdown-item" href="documentcenter.php">Document Center</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=gifcard">Gifcard</a></li>                
                <li><a class="dropdown-item" href="crud.php?Id=item_prices">Item Prices</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=organizations">Organizations</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=price_lists">Price Lists</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=products">Products</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=referals">Referals</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=surfaces">Surfaces</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=venues">Venues</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=wharehouses">Wharehouses</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="lead.php">New Lead</a></li>
                <li><a class="dropdown-item" href="leads.php">Leads</a></li>
                <li><a class="dropdown-item" href="pending_payments.php">Pending Payments</a></li>
                <li><a class="dropdown-item" href="logout.php">Sign Out</a></li>
                <?php } else{?>
                <li><a class="dropdown-item" href="crud.php?Id=account">Cuenta</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=categories">Categorias</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=customers">Clientes</a></li>                
                <li><a class="dropdown-item" href="crud.php?Id=customer_type">Tipo Cliente</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=discounts">Descuentos-Cupones-Tarifas</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=distance_charges">Cargos por distancia</a></li>
                <li><a class="dropdown-item" href="documentcenter.php">Centro de Documentos</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=gifcard">Tarjetas de regalo</a></li>                
                <li><a class="dropdown-item" href="crud.php?Id=item_prices">Item Prices</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=organizations">Organizaciones</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=price_lists">Listas de Precios</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=products">Productos</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=referals">Referidos</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=surfaces">Superficies</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=venues">Lugares de eventos</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=wharehouses">Almacenes</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="lead.php">Nuevo Evento </a></li>
                <li><a class="dropdown-item" href="leads.php">Eventos</a></li>
                <li><a class="dropdown-item" href="pending_payments.php">Pendientes de pago</a></li>
                <li><a class="dropdown-item" href="logout.php">Salir</a></li>                
                <?php }?>
            </ul>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-globe"></i> <span id="current-lang-text"><?php echo $_SESSION['Idioma']?></span>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item lang-option" href="#" data-lang="es">Español</a></li>
                <li><a class="dropdown-item lang-option" href="#" data-lang="en">English</a></li>
            </ul>
        </li>
    </ul>

    <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
</div>
    </div>
    </nav>
    <br>
    <br>
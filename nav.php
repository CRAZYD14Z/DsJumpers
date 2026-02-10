    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">DsJumpers</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="index.php">Home</a>
            </li>

            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-cog"></i>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="crud.php?Id=account">Account</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=categories">Categories</a></li>
                <li><a class="dropdown-item" href="crud.php?Id=customers">Customers</a></li>                
                <li><a class="dropdown-item" href="crud.php?Id=customer_type">Customer Type</a></li>
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
                <li><a class="dropdown-item" href="lead.php">Leads</a></li>
                <li><a class="dropdown-item" href="#">Sign Out</a></li>
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
<?php
function sidebar($paginaActual = '') {
    ?>
<link rel="stylesheet" href="/lab/css/style.css">
<link rel="stylesheet" href="/lab/css/sidebar.css">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">

        <button class="btn btn-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">

            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white" class="bi bi-list"
                viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M2.5 12.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11z" />
            </svg>

            Menu
        </button>

        <div class="offcanvas offcanvas-start bg-dark" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
            aria-labelledby="offcanvasWithBothOptionsLabel">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title text-white bg-dark" id="offcanvasWithBothOptionsLabel">Menu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body">

                <?php if ($_SESSION["rol"] == "admin") { ?>

                <div class="d-flex flex-column p-3 text-white bg-dark" style="height: 90vh; width: 100%;">

                    <ul class="nav nav-pills flex-column mb-auto">

                        <li class="nav-item">
                            <a href="/lab/bienvenida.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Inicio') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 9l9-6 9 6"></path>
                                    <path d="M9 22V12h6v10"></path>
                                    <path d="M3 9v12h18V9"></path>
                                </svg>
                                Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/lab/dashboard/dashboard.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Dashboard') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="me-2">
                                    <rect x="2" y="2" width="6" height="6" rx="1"></rect>
                                    <rect x="10" y="2" width="4" height="9" rx="1"></rect>
                                    <rect x="2" y="10" width="6" height="4" rx="1"></rect>
                                    <rect x="10" y="12" width="4" height="3" rx="1"></rect>
                                </svg>
                                Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/lab/usuarios/administarUsuarios.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Usuarios') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white"
                                    class="bi bi-people-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0-2-5.235A3 3 0 0 0 11 8z" />
                                    <path
                                        d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                                    <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                                </svg>
                                Usuarios
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/lab/pacientes/pacientes.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Pacientes') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                    viewBox="0 0 16 16">
                                    <path d="M3 1h5l2 2h3v12H3z" />
                                </svg>
                                Pacientes
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/lab/estudios/estudios.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Estudios') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="me-2">
                                    <rect x="3" y="2" width="10" height="14" rx="1"></rect>
                                    <line x1="5" y1="6" x2="11" y2="6"></line>
                                    <line x1="5" y1="9" x2="11" y2="9"></line>
                                    <line x1="5" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Estudios
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/lab/citas/citas.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Citas') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                                    <circle cx="12" cy="16" r="3"></circle>
                                    <path d="M12 14v2l1 1"></path>
                                </svg>
                                Citas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/lab/laboratorio/laboratorio.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'laboratorio') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                    class="bi bi-flask" viewBox="0 0 16 16">
                                    <path
                                        d="M6.5 1a.5.5 0 0 1 .5.5V5h2V1.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 .5.5v1.664a2 2 0 0 1-.586 1.414l-2.828 2.828A1 1 0 0 0 7 11v2h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h3v-2a1 1 0 0 0-.707-.293l-2.828-2.828A2 2 0 0 1 4 6.664V5.5a.5.5 0 0 1 .5-.5H5V1.5a.5.5 0 0 1 .5-.5z" />
                                </svg>
                                Laboratorio
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/lab/almacen/almacen.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Almacen') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                    viewBox="0 0 16 16">
                                    <path d="M2 3l6-2 6 2v10l-6 2-6-2V3z" />
                                    <path d="M2 3l6 2 6-2" />
                                </svg>
                                Almacén
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/lab/caja/caja.php"
                                class="nav-link text-white hoverbutton <?= ($paginaActual == 'Caja') ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="me-2">
                                    <path d="M2 4l6-2 6 2v10l-6 2-6-2z"></path>
                                    <path d="M2 4l6 3 6-3"></path>
                                </svg>
                                Caja
                            </a>
                        </li>

                    </ul>

                    <hr>

                    <div class="dropup">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                            id="userMenu" data-bs-toggle="dropdown">

                            <img src="https://github.com/mdo.png" alt="" width="32" height="32"
                                class="rounded-circle me-2">

                            <strong><?php echo $_SESSION['usuario']; ?></strong>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li><a class="dropdown-item" href="#">Ajustes</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/lab/logout.php">Cerrar sesión</a></li>
                        </ul>
                    </div>

                </div>

                <?php } ?>

            </div>
        </div>

        <span class="navbar-brand"><?php echo $paginaActual?></span>

        <div class="dropdown me-4">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userMenu"
                data-bs-toggle="dropdown">

                <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">

                <strong><?php echo $_SESSION['usuario']; ?></strong>
            </a>

            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="/lab/logout.php">Cerrar sesión</a></li>
            </ul>
        </div>

    </div>
</nav>
<?php    }?>
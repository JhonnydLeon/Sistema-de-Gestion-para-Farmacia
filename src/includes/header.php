<?php
if (empty($_SESSION['active'])) {
    header('Location: ../');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Panel de Administración</title>
    <link href="../assets/css/material-dashboard.css" rel="stylesheet" />
    <link href="../assets/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="../assets/js/jquery-ui/jquery-ui.min.css">
    <script src="../assets/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="wrapper ">
        <div class="sidebar" data-color="purple" data-background-color="blue" data-image="../assets/img/sidebar.jpg">
            <div class="logo bg-primary"><a href="./" class="simple-text logo-normal">
                    Sistema de Farmacia
                </a></div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="usuarios.php">
                            <i class="fas fa-user mr-2 fa-2x"></i>
                            <p> Usuarios</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="config.php">
                            <i class="fas fa-cogs mr-2 fa-2x"></i>
                            <p> Configuración</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="tipo.php">
                            <i class=" fas fa-tags mr-2 fa-2x"></i>
                            <p> Categorias</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="presentacion.php">
                            <i class=" fas fa-list mr-2 fa-2x"></i>
                            <p> Presentación</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="laboratorio.php">
                            <i class=" fas fa-hospital mr-2 fa-2x"></i>
                            <p> Laboratorios</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="proveedor.php">
                            <i class=" fas fa-box mr-2 fa-2x"></i>
                            <p> Proveedores</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="productos.php">
                            <i class="fa fa-medkit mr-2 fa-2x"></i>
                            <p> Medicinas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="clientes.php">
                            <i class=" fas fa-users mr-2 fa-2x"></i>
                            <p> Clientes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="ventas.php">
                            <i class="fas fa-cash-register mr-2 fa-2x"></i>
                            <p> Nueva Venta</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="lista_ventas.php">
                            <i class="fas fa-cart-plus mr-2 fa-2x"></i>
                            <p> Historial Ventas</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute fixed-top">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <a class="navbar-brand" href="javascript:;">Sistema de Venta</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="navbar-toggler-icon icon-bar"></span>
                        <span class="navbar-toggler-icon icon-bar"></span>
                        <span class="navbar-toggler-icon icon-bar"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end">

                    <!-- Nuevo: Mostrar nombre de usuario -->
                    <div class="mr-3 d-flex align-items-center">
                        <span class="text-black">
                            <?php 
                            // Mostrar el nombre del usuario si está disponible en la sesión
                            if (isset($_SESSION['nombre'])) {
                                echo $_SESSION['nombre'];
                            } else if (isset($_SESSION['user'])) {
                                echo $_SESSION['user'];
                            } else {
                                echo "Usuario";
                            }
                            ?>
                        </span>
                    </div>

                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="javascript:;" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-user"></i>
                                    <p class="d-lg-none d-md-block">
                                        Cuenta
                                    </p>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#nuevo_pass">Perfil</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="salir.php">Cerrar Sesión</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>


            <script>
            // Función mejorada para enfocar campos
            function focusFirstInput() {
                try {
                    // Lista de IDs prioritarios
                    const priorityIds = [
                        'categoria-nombre', 'presentacion-nombre', 
                        'laboratorio-nombre', 'proveedor-nombre',
                        'producto-codigo', 'cliente-nombre'
                    ];
                    
                    // Buscar campos prioritarios
                    for (const id of priorityIds) {
                        const element = document.getElementById(id);
                        if (element && element.offsetParent !== null) { // Visible
                            element.focus();
                            return;
                        }
                    }
                    
                    // Buscar el primer campo enfocable
                    const focusable = document.querySelector('input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled])');
                    if (focusable && focusable.offsetParent !== null) {
                        focusable.focus();
                    }
                } catch (error) {
                    console.error("Error en focusFirstInput:", error);
                }
            }

            // Enfocar al cargar la página
            document.addEventListener('DOMContentLoaded', function() {
                // Esperar 100ms para asegurar que todo está renderizado
                setTimeout(focusFirstInput, 100);
            });

            // Función para manejar acciones de formulario
            function handleFormAction(action) {
                if (action === 'cancel') {
                    // Lógica de limpieza existente
                    limpiar(); 
                    
                    // Reenfocar después de limpiar
                    setTimeout(focusFirstInput, 50);
                } else if (action === 'submit') {
                    // Reenfocar después de enviar (manejado en el evento submit)
                }
            }
            </script>

            <!-- End Navbar -->
            <div class="content bg">
                <div class="container-fluid">


<?php
session_start();

// Mostrar alertas de eliminación
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
} else {
    $alert = '';
}

include "formatear.php";  // Asegúrate de usar la ruta correcta
include "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "productos";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}
if (!empty($_POST)) {
    $alert = "";
    $id = $_POST['id'];
    $codigo = $_POST['codigo'];
    // APLICAR FORMATEO AL NOMBRE DEL PRODUCTO AQUÍ
    $producto = formatearNombre($_POST['producto']);
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];
    $tipo = $_POST['tipo'];
    $presentacion = $_POST['presentacion'];
    $laboratorio = $_POST['laboratorio'];

    //campo de proveedor
    $proveedor = $_POST['proveedor'];
    $vencimiento = isset($_POST['vencimiento']) ? $_POST['vencimiento'] : '';

    //ANTES la linea 21
    // $vencimiento = '';
    // if (!empty($_POST['accion'])) {
    //     $vencimiento = $_POST['vencimiento'];
    // }
    if (empty($codigo) || empty($producto) || empty($tipo) || empty($presentacion) || empty($laboratorio) || empty($proveedor)  || empty($precio) || $precio <  0 || empty($cantidad) || $cantidad <  0) {
        $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todo los campos son obligatorios
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    } else {
        if (empty($id)) {
            $query = mysqli_query($conexion, "SELECT * FROM producto WHERE codigo = '$codigo'");
            $result = mysqli_fetch_array($query);
            if ($result > 0) {
                $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        El codigo ya existe
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            } else {
                $query_insert = mysqli_query($conexion, "INSERT INTO producto(codigo,descripcion,precio,existencia,id_lab,id_proveedor,id_presentacion,id_tipo, vencimiento) values ('$codigo', '$producto', '$precio', '$cantidad', $laboratorio, $proveedor, $presentacion, $tipo, '$vencimiento')");
                if ($query_insert) {
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Producto registrado
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                } else {
                    $alert = '<div class="alert alert-danger" role="alert">
                    Error al registrar el producto
                  </div>';
                }
            }
        } else {
            $query_update = mysqli_query($conexion, "UPDATE producto SET codigo = '$codigo', descripcion = '$producto', precio= $precio, existencia = $cantidad, id_lab = $laboratorio, id_proveedor = $proveedor, id_presentacion = $presentacion, id_tipo = $tipo, vencimiento = '$vencimiento' WHERE codproducto = $id");


            if ($query_update) {
                $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Producto Modificado
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            } else {
                $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Error al modificar
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            }
        }
    }
}
include_once "includes/header.php";
?>
<div class="card shadow-lg">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        Productos
                    </div>
                    <div class="card-body">
                        <form action="" method="post" autocomplete="off" id="formulario">

                           <!-- cambiar esto para mostrar las alertas del principio del codigo -->
                            <?php 
                            if (isset($alert) && !empty($alert)) {
                                echo $alert;
                            } 
                            ?>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codigo" class=" text-dark font-weight-bold"><i class="fas fa-barcode"></i> Código de Barras</label>
                                        <input type="text" placeholder="Ingrese código de barras" name="codigo" id="codigo" class="form-control">
                                        <input type="hidden" id="id" name="id">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="producto" class=" text-dark font-weight-bold">Producto</label>
                                        <input type="text" placeholder="Ingrese nombre de Medicina" name="producto" id="producto" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="precio" class=" text-dark font-weight-bold">Precio</label>
                                        <input type="text" placeholder="Ingrese precio" class="form-control" name="precio" id="precio">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cantidad" class=" text-dark font-weight-bold">Cantidad</label>
                                        <input type="number" placeholder="Ingrese cantidad" class="form-control" name="cantidad" id="cantidad">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo">Categoría</label>
                                        <select id="tipo" class="form-control" name="tipo" required>
                                            <?php
                                            $query_tipo = mysqli_query($conexion, "SELECT * FROM tipos");
                                            while ($datos = mysqli_fetch_assoc($query_tipo)) { ?>
                                                <option value="<?php echo $datos['id'] ?>"><?php echo $datos['tipo'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="presentacion">Presentación</label>
                                        <select id="presentacion" class="form-control" name="presentacion" required>
                                            <?php
                                            $query_pre = mysqli_query($conexion, "SELECT * FROM presentacion");
                                            while ($datos = mysqli_fetch_assoc($query_pre)) { ?>
                                                <option value="<?php echo $datos['id'] ?>"><?php echo $datos['nombre'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="laboratorio">Laboratorio</label>
                                        <select id="laboratorio" class="form-control" name="laboratorio" required>
                                            <?php
                                            $query_lab = mysqli_query($conexion, "SELECT * FROM laboratorios");
                                            while ($datos = mysqli_fetch_assoc($query_lab)) { ?>
                                                <option value="<?php echo $datos['id'] ?>"><?php echo $datos['laboratorio'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- campo de proveedor -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="proveedor">Proveedor</label>
                                        <select id="proveedor" class="form-control" name="proveedor" required>
                                            <?php
                                            $query_pro = mysqli_query($conexion, "SELECT * FROM proveedor");
                                            while ($datos = mysqli_fetch_assoc($query_pro)) { ?>
                                                <option value="<?php echo $datos['id_proveedor'] ?>"><?php echo $datos['nombre'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <!-- <input id="accion" class="form-check-input" type="checkbox" name="accion" value="si"> -->
                                        <label for="vencimiento">Vencimiento</label>
                                        <input id="vencimiento" class="form-control" type="date" name="vencimiento" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="submit" value="Registrar" class="btn btn-success" id="btnAccion">
                                    <input type="button" value="Cancelar" onclick="handleFormAction('cancel')" class="btn btn-danger" id="btnNuevo">
                                </div>

                                <!-- funcion de tabular -->
                                <script>
                                // Función de limpieza modificada
                                function limpiar() {
                                    document.getElementById("id").value = "";
                                    document.getElementById("nombre").value = "";
                                    document.getElementById("btnAccion").value = "Registrar";
                                    
                                    // Ya no llamamos focusFirstInput aquí, lo maneja handleFormAction
                                }
                                </script>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="tbl">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Medicina</th>
                            <th>Tipo</th>
                            <th>Presentación</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include "../conexion.php";

                        $query = mysqli_query($conexion, "SELECT p.*, t.id, t.tipo, pr.id, pr.nombre FROM producto p INNER JOIN tipos t ON p.id_tipo = t.id INNER JOIN presentacion pr ON p.id_presentacion = pr.id");
                        $result = mysqli_num_rows($query);
                        if ($result > 0) {
                            while ($data = mysqli_fetch_assoc($query)) { ?>
                                <tr>
                                    <td><?php echo $data['codproducto']; ?></td>
                                    <td><?php echo $data['codigo']; ?></td>
                                    <td><?php echo $data['descripcion']; ?></td>
                                    <td><?php echo $data['tipo']; ?></td>
                                    <td><?php echo $data['nombre']; ?></td>
                                    <td><?php echo $data['precio']; ?></td>
                                    <td><?php echo $data['existencia']; ?></td>
                                    <td>
                                        <a href="#" onclick="editarProducto(<?php echo $data['codproducto']; ?>)" class="btn btn-primary"><i class='fas fa-edit'></i></a>

                                        <form action="eliminar_producto.php?id=<?php echo $data['codproducto']; ?>" method="post" class="confirmar d-inline">
                                            <button class="btn btn-danger" type="submit"><i class='fas fa-trash-alt'></i> </button>
                                        </form>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
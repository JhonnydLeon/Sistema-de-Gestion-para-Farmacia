<?php
session_start();
require_once "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "ventas";
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}

// Obtener parámetros de fecha si existen
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir la consulta con filtros de fecha/hora
$sql_ventas = "SELECT v.*, c.idcliente, c.nombre FROM ventas v INNER JOIN cliente c ON v.id_cliente = c.idcliente";

// Agregar condiciones si hay fechas seleccionadas
$where = [];
if (!empty($fecha_inicio)) {
    // Convertir a formato datetime si no tiene hora
    if (strlen($fecha_inicio) == 10) $fecha_inicio .= ' 00:00:00';
    $where[] = "v.fecha >= '$fecha_inicio'";
}
if (!empty($fecha_fin)) {
    // Convertir a formato datetime si no tiene hora
    if (strlen($fecha_fin) == 10) $fecha_fin .= ' 23:59:59';
    $where[] = "v.fecha <= '$fecha_fin'";
}

if (count($where) > 0) {
    $sql_ventas .= " WHERE " . implode(" AND ", $where);
}

$query = mysqli_query($conexion, $sql_ventas);
include_once "includes/header.php";
?>
<div class="card">
    <div class="card-header">
        Historial ventas
    </div>

    <!-- Formulario de filtro por fechas con hora -->
    <div class="card-header bg-light">
        <form method="GET" class="row">
            <div class="col-md-3 mb-2">
                <label>Fecha/Hora Inicio</label>
                <input type="datetime-local" class="form-control" name="fecha_inicio" 
                    value="<?php echo $fecha_inicio; ?>" step="1">
            </div>
            <div class="col-md-3 mb-2">
                <label>Fecha/Hora Fin</label>
                <input type="datetime-local" class="form-control" name="fecha_fin" 
                    value="<?php echo $fecha_fin; ?>" step="1">
            </div>
            <div class="col-md-3 mb-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <?php if (!empty($fecha_inicio) || !empty($fecha_fin)): ?>
                    <a href="lista_ventas.php" class="btn btn-secondary ml-2">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <a href="excel/reporte_ventas_excel.php?fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>" 
        class="btn btn-success">
            <i class="fas fa-file-excel mr-2"></i>Exportar Historial
        </a>
        
        <?php if (!empty($fecha_inicio) || !empty($fecha_fin)): ?>
            <div class="alert alert-info mb-0">
                Mostrando ventas desde <strong><?php echo $fecha_inicio; ?></strong> hasta <strong><?php echo $fecha_fin; ?></strong>
            </div>
        <?php endif; ?>
    </div>

        <div class="table-responsive">
            <table class="table table-light" id="tbl">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Fecha/Hora</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($query)) { 
                        // Formatear fecha para mostrar
                        $fecha_venta = date('d/m/Y H:i:s', strtotime($row['fecha']));
                    ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nombre']; ?></td>
                            <td><?php echo $row['total']; ?></td>
                            <td><?php echo $fecha_venta; ?></td>
                            <td>
                                <a href="pdf/generar.php?cl=<?php echo $row['id_cliente'] ?>&v=<?php echo $row['id'] ?>" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf"></i></a>
                                <a href="excel/generar_excel.php?v=<?php echo $row['id'] ?>" class="btn btn-success"><i class="fas fa-file-excel"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
<?php
session_start();
require("../conexion.php");

$id_user = $_SESSION['idUser'];
$permiso = 'presentacion';
$sql = mysqli_query($conexion, "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'");
$existe = mysqli_fetch_all($sql);
if (empty($existe) && $id_user != 1) {
    header("Location: permisos.php");
    exit;
}

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Verificar si existen productos asociados a esta presentación
    $check_query = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM producto WHERE id_presentacion = $id");
    
    if (!$check_query) {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            Error en la verificación: ' . mysqli_error($conexion) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>';
    } else {
        $result = mysqli_fetch_assoc($check_query);
        
        if ($result['total'] > 0) {
            // Mostrar mensaje específico sobre productos asociados
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> ¡No se puede eliminar la presentación!
                <hr>
                Existen <strong>' . $result['total'] . ' productos</strong> asociados a esta presentación.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
        } else {
            $query_delete = mysqli_query($conexion, "DELETE FROM presentacion WHERE id = $id");
            
            if ($query_delete) {
                $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> Presentación eliminada correctamente.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            } else {
                $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error al eliminar: ' . mysqli_error($conexion) . '
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            }
        }
    }
    
    mysqli_close($conexion);
    header("Location: presentacion.php");
    exit;
}
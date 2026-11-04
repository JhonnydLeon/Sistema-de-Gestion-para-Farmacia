<?php
require_once '../../conexion.php';
require_once 'fpdf/fpdf.php';
require_once 'phpqrcode/qrlib.php';

// Iniciar buffer para evitar errores de salida
ob_start();

$pdf = new FPDF('P', 'mm', array(80, 250)); // Altura aumentada
$pdf->AddPage();
$pdf->SetMargins(5, 0, 0);
$pdf->SetTitle("Ventas");
$pdf->SetFont('Arial', 'B', 12);
$id = $_GET['v'];
$idcliente = $_GET['cl'];

// Obtener datos de configuración
$config = mysqli_query($conexion, "SELECT * FROM configuracion");
$datos = mysqli_fetch_assoc($config);

// Obtener datos del cliente
$clientes = mysqli_query($conexion, "SELECT * FROM cliente WHERE idcliente = $idcliente");
$datosC = mysqli_fetch_assoc($clientes);

// Obtener fecha de venta
$ventaInfo = mysqli_query($conexion, "SELECT fecha FROM ventas WHERE id = $id");
$datosVenta = mysqli_fetch_assoc($ventaInfo);

// Obtener detalles de venta y calcular totales
$ventas = mysqli_query($conexion, "SELECT d.*, p.codproducto, p.descripcion 
                                  FROM detalle_venta d 
                                  INNER JOIN producto p ON d.id_producto = p.codproducto 
                                  WHERE d.id_venta = $id");
$total = 0.00;
$desc = 0.00;

// Almacenar los productos para usarlos en el QR
$productos = [];
while ($row = mysqli_fetch_assoc($ventas)) {
    $sub_total = $row['total'];
    $total += $sub_total;
    $desc += $row['descuento'];
    $productos[] = $row;
}

// Volver al inicio del resultado para procesar los productos
mysqli_data_seek($ventas, 0);

// Generar código QR con detalles de productos
$qrData = "FACTURA N°: $id\n";
$qrData .= "CLIENTE: " . $datosC['nombre'] . "\n";
$qrData .= "FECHA: " . date('d/m/Y H:i', strtotime($datosVenta['fecha'])) . "\n\n";
$qrData .= "DETALLE DE PRODUCTOS:\n";

foreach ($productos as $producto) {
    // Acortar nombres muy largos para mantener el QR legible
    $nombreProducto = $producto['descripcion'];
    if (strlen($nombreProducto) > 25) {
        $nombreProducto = substr($nombreProducto, 0, 22) . '...';
    }
    
    $qrData .= "• " . $nombreProducto . " - ";
    $qrData .= "Cant: " . $producto['cantidad'] . " - ";
    $qrData .= "Precio: " . number_format($producto['precio'], 2) . " Bs. - ";
    $qrData .= "Subtotal: " . number_format($producto['total'], 2) . " Bs.\n";
}

$qrData .= "\nTOTAL: " . number_format($total, 2, '.', ',') . " Bs.";
$qrData .= "\nDESCUENTO: " . number_format($desc, 2, '.', ',') . " Bs.";
$qrData .= "\nTOTAL PAGAR: " . number_format($total - $desc, 2, '.', ',') . " Bs.";

$tempQR = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
QRcode::png($qrData, $tempQR, QR_ECLEVEL_L, 10);

// Cabecera de la factura
$pdf->Cell(65, 5, utf8_decode($datos['nombre']), 0, 1, 'C');
$pdf->image("../../assets/img/logo.png", 60, 15, 15, 15, 'PNG');
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(15, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(15, 5, $datos['telefono'], 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(15, 5, utf8_decode("Dirección: "), 0, 0, 'L');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(15, 5, utf8_decode($datos['direccion']), 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(15, 5, "Correo: ", 0, 0, 'L');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(15, 5, utf8_decode($datos['email']), 0, 1, 'L');
$pdf->Ln();

// Datos del cliente
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(70, 5, "Datos del cliente", 1, 1, 'C', 1);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(30, 5, utf8_decode('Nombre'), 0, 0, 'L');
$pdf->Cell(20, 5, utf8_decode('Teléfono'), 0, 0, 'L');
$pdf->Cell(20, 5, utf8_decode('Dirección'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(30, 5, utf8_decode($datosC['nombre']), 0, 0, 'L');
$pdf->Cell(20, 5, utf8_decode($datosC['telefono']), 0, 0, 'L');
$pdf->Cell(20, 5, utf8_decode($datosC['direccion']), 0, 1, 'L');
$pdf->Ln(3);

// Detalle de productos
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(70, 5, "Detalle de Medicina(s)", 1, 1, 'C', 1);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(30, 5, utf8_decode('Descripción'), 0, 0, 'L');
$pdf->Cell(10, 5, 'Cant.', 0, 0, 'L');
$pdf->Cell(15, 5, 'Precio', 0, 0, 'L');
$pdf->Cell(15, 5, 'Sub Total.', 0, 1, 'L');
$pdf->SetFont('Arial', '', 7);
while ($row = mysqli_fetch_assoc($ventas)) {
    $pdf->Cell(30, 5, $row['descripcion'], 0, 0, 'L');
    $pdf->Cell(10, 5, $row['cantidad'], 0, 0, 'L');
    $pdf->Cell(15, 5, $row['precio'], 0, 0, 'L');
    $pdf->Cell(15, 5, number_format($row['total'], 2, '.', ','), 0, 1, 'L');
}
$pdf->Ln();

// Totales
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 5, 'Descuento Total', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 5, number_format($desc, 2, '.', ','), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 5, 'Total Pagar', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 5, number_format($total, 2, '.', ','), 0, 1, 'R');

// ======== CÓDIGO QR ========
$pdf->Ln(65);
$pdf->SetFont('Arial', 'I', 8, );
$pdf->Cell(70, 5, 'Codigo QR de Factura', 0, 1, 'C');
$pdf->Image($tempQR, 25, $pdf->GetY(), 30, 30);
$pdf->Ln(30); // Espacio después del QR

// ======== TEXTOS LEGALES ========
$pdf->SetY(-39); // Posición desde el final
$pdf->SetFont('Arial', '', 5);

$textos = [
    "ESTA FACTURA CONTRIBUYE",
    "AL DESARROLLO DEL PAIS",
    "EL USO ILICITO DE ESTA",
    "SERA SANCIONADA DE ACUERDO A LA LEY",
];

foreach ($textos as $texto) {
    $pdf->Cell(0, 2, utf8_decode($texto), 0, 1, 'C');
}

// Fechas
date_default_timezone_set('America/La_Paz');
$pdf->SetFont('Arial', 'I', 5);
$pdf->Cell(0, 5, utf8_decode("Fecha de Impresión: " . date('d/m/Y H:i')), 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 5);
$pdf->Cell(0, 5, utf8_decode("Fecha de emisión: " . date('d/m/Y H:i', strtotime($datosVenta['fecha']))), 0, 1, 'C');

// Limpiar y eliminar temporal
if (file_exists($tempQR)) {
    unlink($tempQR);
}

// Enviar PDF
ob_end_clean();
$pdf->Output("ventas.pdf", "I");
exit;
?>
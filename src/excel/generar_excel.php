<?php
require_once "../../conexion.php";
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Obtener datos de configuración de la empresa
$query_config = mysqli_query($conexion, "SELECT * FROM configuracion LIMIT 1");
$config = mysqli_fetch_assoc($query_config);

// Verificar parámetro de venta
if (empty($_GET['v'])) {
    die("Venta no especificada");
}

$id_venta = $_GET['v'];

// Obtener datos de la venta
$query_venta = mysqli_query($conexion, "SELECT v.*, c.nombre AS cliente, c.telefono, c.direccion 
                                        FROM ventas v 
                                        INNER JOIN cliente c ON v.id_cliente = c.idcliente 
                                        WHERE v.id = $id_venta");
$venta = mysqli_fetch_assoc($query_venta);

// Obtener detalles de la venta
$query_detalle = mysqli_query($conexion, "SELECT d.*, p.descripcion 
                                         FROM detalle_venta d 
                                         INNER JOIN producto p ON d.id_producto = p.codproducto 
                                         WHERE d.id_venta = $id_venta");

// Crear un nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Establecer propiedades del documento
$spreadsheet->getProperties()
    ->setCreator($config['nombre'] ?? 'Sistema de Farmacia')
    ->setTitle("Factura {$id_venta}");

// 1. DATOS DE LA EMPRESA (nueva sección)
$sheet->mergeCells('A1:E1');
$sheet->setCellValue('A1', $config['nombre'] ?? 'Farmacia');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$sheet->mergeCells('A2:E2');
$sheet->setCellValue('A2', $config['direccion'] ?? '');
$sheet->getStyle('A2')->getFont()->setSize(10);

$sheet->mergeCells('A3:E3');
$sheet->setCellValue('A3', 'Teléfono: ' . ($config['telefono'] ?? ''));
$sheet->getStyle('A3')->getFont()->setSize(10);

$sheet->mergeCells('A4:E4');
$sheet->setCellValue('A4', 'Email: ' . ($config['email'] ?? ''));
$sheet->getStyle('A4')->getFont()->setSize(10);

// Espacio entre empresa y factura
$sheet->setCellValue('A5', '');

// 2. ENCABEZADO DE LA FACTURA
$sheet->mergeCells('A6:E6');
$sheet->setCellValue('A6', 'FACTURA');
$sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 3. INFORMACIÓN DE LA VENTA
$sheet->setCellValue('A7', 'Factura N°:');
$sheet->setCellValue('B7', $venta['id']);
$sheet->setCellValue('A8', 'Fecha:');
$sheet->setCellValue('B8', $venta['fecha']);
$sheet->setCellValue('A9', 'Cliente:');
$sheet->setCellValue('B9', $venta['cliente']);
$sheet->setCellValue('A10', 'Teléfono:');
// Formatear teléfono como texto para mantener formato
$sheet->setCellValue('B10', $venta['telefono']);
$sheet->getStyle('B10')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
$sheet->getStyle('B10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->setCellValue('A11', 'Dirección:');
$sheet->setCellValue('B11', $venta['direccion']);

// 4. DETALLES DE PRODUCTOS
$sheet->setCellValue('A13', 'Producto');
$sheet->setCellValue('B13', 'Cantidad');
$sheet->setCellValue('C13', 'Precio');
$sheet->setCellValue('D13', 'Descuento');
$sheet->setCellValue('E13', 'Total');

// Llenar los detalles de los productos
$row = 14;
while ($item = mysqli_fetch_assoc($query_detalle)) {
    $sheet->setCellValue('A'.$row, $item['descripcion']);
    $sheet->setCellValue('B'.$row, $item['cantidad']);
    $sheet->setCellValue('C'.$row, $item['precio']);
    $sheet->setCellValue('D'.$row, $item['descuento']);
    $sheet->setCellValue('E'.$row, $item['total']);
    $row++;
}

// 5. TOTAL
$sheet->setCellValue('D'.($row+1), 'TOTAL:');
$sheet->setCellValue('E'.($row+1), $venta['total']);
$sheet->getStyle('D'.($row+1).':E'.($row+1))->getFont()->setBold(true);

// Ajustar el ancho de las columnas
$sheet->getColumnDimension('A')->setWidth(40);  // Producto más ancho
$sheet->getColumnDimension('B')->setAutoSize(true);
$sheet->getColumnDimension('C')->setAutoSize(true);
$sheet->getColumnDimension('D')->setAutoSize(true);
$sheet->getColumnDimension('E')->setAutoSize(true);

// Establecer estilos para los encabezados
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
];
$sheet->getStyle('A13:E13')->applyFromArray($headerStyle);

// Crear el escritor y guardar en formato Excel 2007
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="factura_'.$id_venta.'.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
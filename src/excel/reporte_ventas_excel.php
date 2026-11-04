<?php
require_once "../../conexion.php";
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir la consulta con los mismos filtros que lista_ventas.php
$sql = "SELECT v.*, c.nombre AS cliente 
        FROM ventas v 
        INNER JOIN cliente c ON v.id_cliente = c.idcliente";

// Aplicar filtros de fecha si existen
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
    $sql .= " WHERE " . implode(" AND ", $where);
}

$query = mysqli_query($conexion, $sql);

// Crear un nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Establecer propiedades del documento
$spreadsheet->getProperties()
    ->setCreator("Sistema de Farmacia")
    ->setTitle("Historial de Ventas");

// Encabezados
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Cliente');
$sheet->setCellValue('C1', 'Total');
$sheet->setCellValue('D1', 'Fecha');

// Llenar datos
$row = 2;
while ($venta = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue('A'.$row, $venta['id']);
    $sheet->setCellValue('B'.$row, $venta['cliente']);
    $sheet->setCellValue('C'.$row, $venta['total']);
    $sheet->setCellValue('D'.$row, $venta['fecha']);
    $row++;
}

// Ajustar ancho de columnas
foreach(range('A','D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Estilo para encabezados
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

// Nombre del archivo con rango de fechas si existe
$filename = "historial_ventas";
if (!empty($fecha_inicio) || !empty($fecha_fin)) {
    $inicio = substr($fecha_inicio, 0, 10);
    $fin = substr($fecha_fin, 0, 10);
    $filename .= "_" . str_replace('-', '', $inicio) . "-" . str_replace('-', '', $fin);
}

// Crear el escritor y guardar
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
<?php
// helpers.php
function formatearNombre($texto) {
    // Convertir todo a minúsculas primero
    $texto = mb_strtolower($texto, 'UTF-8');
    
    // Convertir primera letra de cada palabra a mayúscula
    return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
}
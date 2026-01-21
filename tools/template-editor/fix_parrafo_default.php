<?php
$file = 'E:/appVerumax/certificatum/creare.php';
$content = file_get_contents($file);

// Modificar $parrafo_default para incluir marcadores de formato
$search = '$parrafo_default = "El día {$fecha_formateada} se certifica que {$nombre_alumno} con DNI {$dni_formateado} ha completado y aprobado satisfactoriamente el curso {$nombre_curso} con una carga horaria de {$carga_horaria} horas.";';

$replace = '$parrafo_default = "El día {$fecha_formateada} se certifica que **{$nombre_alumno}** con DNI **{$dni_formateado}** ha completado y aprobado satisfactoriamente el curso **{$nombre_curso}** con una carga horaria de **{$carga_horaria}** horas.";';

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "parrafo_default modificado con marcadores de formato\n";
} else {
    echo "No se encontro el patron\n";
}

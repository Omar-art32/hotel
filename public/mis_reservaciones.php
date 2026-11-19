<?php
$rutaBase = '';
require __DIR__ . '/../app/config/config.php';

$usuario = Autorizacion::requerirPermiso('consultar_reservaciones', $rutaBase);

$tituloPagina = 'Mis reservaciones';
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/panel_provisional.php';
require __DIR__ . '/../app/vistas/pie.php';

<?php
$rutaBase = '../';
require __DIR__ . '/../../app/config/config.php';

$usuario = Autorizacion::requerirPermiso('realizar_check_in', $rutaBase);

$tituloPagina = 'Panel de recepción';
require __DIR__ . '/../../app/vistas/encabezado.php';
require __DIR__ . '/../../app/vistas/panel_provisional.php';
require __DIR__ . '/../../app/vistas/pie.php';

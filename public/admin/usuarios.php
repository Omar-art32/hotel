<?php
$rutaBase = '../';
require __DIR__ . '/../../app/config/config.php';

$usuario = Autorizacion::requerirPermiso('gestionar_usuarios', $rutaBase);

$tituloPagina = 'Usuarios';
require __DIR__ . '/../../app/vistas/encabezado.php';
require __DIR__ . '/../../app/vistas/panel_provisional.php';
require __DIR__ . '/../../app/vistas/pie.php';

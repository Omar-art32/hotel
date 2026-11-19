<?php
/** Documento HTML común. Espera $tituloPagina. Las rutas son relativas a public/. */
// shortcut: rutas relativas solo sirven para páginas en la raíz de public/; añadir una ruta base al crear recepcion/ y admin/.
$tituloPagina = $tituloPagina ?? NOMBRE_HOTEL;
?>
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($tituloPagina) ?> | <?= htmlspecialchars(NOMBRE_HOTEL) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..700&family=Hanken+Grotesk:wght@400;500;600&display=swap">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="min-h-dvh bg-ostion font-texto text-tinta antialiased">
  <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-10 focus:rounded-lg focus:bg-tinta focus:px-4 focus:py-2 focus:text-white">Saltar al contenido</a>

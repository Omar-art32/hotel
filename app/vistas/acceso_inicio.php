<?php /* Marco de las pantallas de acceso: panel del hotel a la izquierda y columna del formulario. Cerrar con acceso_fin.php. */ ?>
<div class="min-h-dvh lg:grid lg:grid-cols-[5fr_6fr]">
  <aside class="relative isolate flex flex-col overflow-hidden bg-naranja px-6 pb-10 pt-6 text-tinta sm:px-10 lg:sticky lg:top-0 lg:h-dvh lg:px-16 lg:py-12">
    <div class="absolute inset-0 -z-10 bg-[url(../img/login_portada.jpg)] bg-cover bg-center opacity-15 mix-blend-multiply" aria-hidden="true"></div>
    <svg class="absolute inset-0 -z-10 h-full w-full text-white/40 [&_path]:[vector-effect:non-scaling-stroke]" viewBox="0 0 600 800" preserveAspectRatio="xMaxYMax slice" fill="none" stroke="currentColor" aria-hidden="true">
      <g>
        <path d="M500 800V400A100 100 0 0 1 700 400V800" />
        <path d="M420 800V400A180 180 0 0 1 780 400V800" />
        <path d="M340 800V400A260 260 0 0 1 860 400V800" />
        <path d="M260 800V400A340 340 0 0 1 940 400V800" />
        <path d="M180 800V400A420 420 0 0 1 1020 400V800" />
      </g>
    </svg>

    <div class="flex items-center justify-between gap-4">
      <a href="index.php" class="flex w-fit items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-tinta">
        <svg viewBox="0 0 24 32" class="h-8 w-6" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M1.5 32V12a10.5 10.5 0 0 1 21 0v20" />
          <path d="M6.5 32V12.5a5.5 5.5 0 0 1 11 0V32" />
        </svg>
        <span class="font-titulo text-xl"><?= htmlspecialchars(NOMBRE_HOTEL) ?></span>
      </a>
      <a href="index.php" class="rounded-sm text-sm text-tinta underline-offset-4 hover:text-tinta hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-tinta lg:hidden">Volver al sitio</a>
    </div>

    <div class="mt-10 lg:my-auto">
      <p class="max-w-[12ch] text-balance font-titulo text-4xl leading-[1.05] sm:text-5xl lg:text-6xl xl:text-7xl">Tu estancia empieza aquí</p>
      <p class="mt-5 hidden max-w-[38ch] text-lg text-tinta sm:block lg:mt-8">
        Consulta tus reservaciones, revisa las fechas de tu visita y mantén tus datos al día desde un solo lugar.
      </p>
    </div>

    <p class="hidden text-sm text-tinta lg:block">© <?= date('Y') ?> <?= htmlspecialchars(NOMBRE_HOTEL) ?>. Todos los derechos reservados.</p>
  </aside>

  <div class="flex flex-col px-6 sm:px-10 lg:min-h-dvh lg:px-16">
    <div class="hidden justify-end py-6 lg:flex">
      <a href="index.php" class="enlace text-sm no-underline hover:underline">Volver al sitio</a>
    </div>

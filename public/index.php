<?php
require __DIR__ . '/../app/config/config.php';
Sesion::iniciar(); // La cabecera muestra "Mi cuenta" si hay sesión.

// Contenido de ejemplo. Las habitaciones vendrán de MariaDB; las fotos se colocan en public/assets/img/.
$habitaciones = [
    [
        'nombre' => 'Habitación Patio',
        'descripcion' => 'Ventanas al patio central, piso de barro y una cama king con ropa de algodón.',
        'huespedes' => 2, 'metros' => 26, 'cama' => 'Cama king', 'precio' => 2450,
        'imagen' => 'habitacion_patio.jpg', 'fondo' => 'bg-cal',
    ],
    [
        'nombre' => 'Suite Cantera',
        'descripcion' => 'Muros de cantera original, sala de estar y baño con regadera de lluvia.',
        'huespedes' => 3, 'metros' => 38, 'cama' => 'Cama king y sofá cama', 'precio' => 3600,
        'imagen' => 'suite_cantera.jpg', 'fondo' => 'bg-naranja/25',
    ],
    [
        'nombre' => 'Suite Terraza',
        'descripcion' => 'Terraza privada con vista a las cúpulas del centro y tina independiente.',
        'huespedes' => 4, 'metros' => 52, 'cama' => 'Dos camas queen', 'precio' => 4900,
        'imagen' => 'suite_terraza.jpg', 'fondo' => 'bg-turquesa/25',
    ],
];

$servicios = [
    ['Desayuno de la casa', 'Pan del día, fruta de temporada y platillos queretanos de 7:30 a 11:00.'],
    ['Alberca en la azotea', 'Climatizada todo el año, con camastros y vista a la ciudad.'],
    ['Estacionamiento', 'Cubierto y vigilado, sin costo para huéspedes.'],
    ['Wi-Fi en todo el hotel', 'Conexión de fibra óptica en habitaciones y áreas comunes.'],
    ['Recepción las 24 horas', 'Llegadas tardías, guarda de equipaje y recomendaciones locales.'],
    ['Traslado al aeropuerto', 'Servicio privado con reservación previa.'],
];

$galeria = [
    ['patio_central.jpg', 'Patio central con fuente de cantera', 'col-span-2 row-span-2 bg-turquesa/30'],
    ['alberca.jpg', 'Alberca en la azotea', 'bg-cal'],
    ['desayuno.jpg', 'Desayuno servido en el patio', 'bg-naranja/25'],
    ['pasillo.jpg', 'Pasillo con arcos de piedra', 'bg-turquesa/20'],
    ['terraza.jpg', 'Terraza al atardecer', 'bg-naranja/15'],
];

$opiniones = [
    ['La habitación daba al patio y por las mañanas solo se oía la fuente. Recepción nos ayudó a reservar en restaurantes cercanos.', 'Mariana G.', 'Estancia de 3 noches'],
    ['Llegamos pasada la medianoche y todo estuvo listo. El desayuno vale la pena por sí solo.', 'Jorge R.', 'Viaje de trabajo'],
    ['Caminamos a todo el centro. La suite con terraza es lo mejor para ver el atardecer.', 'Lucía y Andrés', 'Aniversario'],
];

$preguntas = [
    ['¿A qué hora son la entrada y la salida?', 'La entrada es a partir de las 15:00 y la salida antes de las 12:00. Si necesitas otro horario, avísanos y lo revisamos según disponibilidad.'],
    ['¿Necesito una cuenta para reservar?', 'Sí. Con tu cuenta puedes consultar, modificar o cancelar tus reservaciones en cualquier momento.'],
    ['¿Cuál es la política de cancelación?', 'Puedes cancelar sin costo hasta 48 horas antes de tu llegada. Después se cobra la primera noche.'],
    ['¿Aceptan mascotas?', 'Aceptamos mascotas de hasta 10 kg en la Habitación Patio, con aviso previo.'],
    ['¿Tienen estacionamiento?', 'Sí, cubierto y sin costo para huéspedes, sujeto a disponibilidad.'],
];

$hoy = date('Y-m-d');
$tituloPagina = 'Hotel boutique en el centro histórico';
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/cabecera_sitio.php';
?>
<main id="contenido">

  <!-- Portada y búsqueda de disponibilidad -->
  <section class="px-4 pt-4 sm:px-8" aria-labelledby="titulo-portada">
    <div class="relative isolate mx-auto flex min-h-[34rem] max-w-7xl flex-col justify-end overflow-hidden rounded-[2rem] bg-tinta px-6 pb-28 pt-24 text-white sm:px-12 lg:min-h-[40rem]">
      <svg class="absolute inset-0 -z-20 h-full w-full text-turquesa/60 [&_path]:[vector-effect:non-scaling-stroke]" viewBox="0 0 1200 700" preserveAspectRatio="xMaxYMax slice" fill="none" stroke="currentColor" aria-hidden="true">
        <path d="M900 700V380A120 120 0 0 1 1140 380V700" />
        <path d="M820 700V380A200 200 0 0 1 1220 380V700" />
        <path d="M740 700V380A280 280 0 0 1 1300 380V700" />
        <path d="M660 700V380A360 360 0 0 1 1380 380V700" />
      </svg>
      <div class="absolute inset-0 -z-10 bg-[url(../img/portada.jpg)] bg-cover bg-center" aria-hidden="true"></div>
      <div class="absolute inset-0 -z-10 bg-linear-to-t from-tinta/70 via-tinta/10 to-transparent" aria-hidden="true"></div>

      <h1 id="titulo-portada" class="max-w-[16ch] text-balance font-titulo text-5xl leading-[1.05] sm:text-6xl lg:text-7xl">
        Una casa de cantera en el centro de Querétaro
      </h1>
      <p class="mt-6 max-w-[46ch] text-lg text-white/85">
        Dieciocho habitaciones alrededor de un patio del siglo XVIII, a dos calles del jardín principal.
      </p>
    </div>

    <form id="reservar" action="index.php#habitaciones" method="get" novalidate data-validar
          class="relative z-10 mx-auto -mt-16 grid max-w-5xl scroll-mt-24 gap-4 rounded-2xl border border-linea bg-white p-4 shadow-[0_12px_40px_rgb(30_42_40/0.10)] sm:grid-cols-2 sm:p-5 lg:grid-cols-[1fr_1fr_0.8fr_auto] lg:items-end">
      <div>
        <label for="llegada" class="etiqueta-campo">Llegada</label>
        <input id="llegada" name="llegada" type="date" min="<?= $hoy ?>" required class="campo-texto"
               aria-describedby="error-llegada" data-error-vacio="Elige la fecha de llegada.">
        <p id="error-llegada" class="error-campo" hidden></p>
      </div>
      <div>
        <label for="salida" class="etiqueta-campo">Salida</label>
        <input id="salida" name="salida" type="date" min="<?= $hoy ?>" required class="campo-texto"
               aria-describedby="error-salida" data-error-vacio="Elige la fecha de salida."
               data-despues-de="llegada" data-error-orden="La salida debe ser después de la llegada.">
        <p id="error-salida" class="error-campo" hidden></p>
      </div>
      <div>
        <label for="huespedes" class="etiqueta-campo">Huéspedes</label>
        <select id="huespedes" name="huespedes" class="campo-texto">
          <?php for ($numero = 1; $numero <= 4; $numero++): ?>
            <option value="<?= $numero ?>" <?= $numero === 2 ? 'selected' : '' ?>><?= $numero ?> <?= $numero === 1 ? 'huésped' : 'huéspedes' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <button type="submit" class="boton-principal group sm:col-span-2 lg:col-span-1 lg:px-8">
        <span>Ver disponibilidad</span>
      </button>
    </form>
  </section>

  <!-- Habitaciones -->
  <section id="habitaciones" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-24 sm:px-8" aria-labelledby="titulo-habitaciones">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <h2 id="titulo-habitaciones" class="font-titulo text-4xl sm:text-5xl">Habitaciones</h2>
      <p class="max-w-[44ch] text-musgo">Todas incluyen desayuno, aire acondicionado y artículos de baño elaborados en la región.</p>
    </div>

    <ul class="mt-12 grid gap-10 md:grid-cols-3 md:gap-6">
      <?php foreach ($habitaciones as $habitacion): ?>
        <li class="flex flex-col">
          <div class="aspect-[4/3] rounded-2xl bg-cover bg-center <?= htmlspecialchars($habitacion['fondo']) ?>"
               style="background-image: url('assets/img/<?= htmlspecialchars(rawurlencode($habitacion['imagen'])) ?>')" role="img"
               aria-label="Fotografía de <?= htmlspecialchars($habitacion['nombre']) ?>"></div>
          <h3 class="mt-5 font-titulo text-2xl"><?= htmlspecialchars($habitacion['nombre']) ?></h3>
          <p class="mt-2 text-musgo"><?= htmlspecialchars($habitacion['descripcion']) ?></p>
          <ul class="mt-4 flex flex-wrap gap-2 text-sm">
            <li class="rounded-lg bg-turquesa/15 px-3 py-1"><?= (int) $habitacion['huespedes'] ?> huéspedes</li>
            <li class="rounded-lg bg-turquesa/15 px-3 py-1"><?= (int) $habitacion['metros'] ?> m²</li>
            <li class="rounded-lg bg-turquesa/15 px-3 py-1"><?= htmlspecialchars($habitacion['cama']) ?></li>
          </ul>
          <div class="mt-auto flex items-end justify-between gap-4 pt-6">
            <p><span class="text-2xl font-medium">$<?= number_format($habitacion['precio']) ?></span> <span class="text-sm text-musgo">MXN por noche</span></p>
            <a href="login.php" class="enlace text-sm font-medium">Reservar</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <!-- Servicios -->
  <section id="servicios" class="scroll-mt-20 bg-cal" aria-labelledby="titulo-servicios">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 py-24 sm:px-8 lg:grid-cols-[1fr_2fr]">
      <div>
        <h2 id="titulo-servicios" class="font-titulo text-4xl sm:text-5xl">Servicios</h2>
        <p class="mt-4 max-w-[36ch] text-musgo">Lo necesario para una estancia tranquila, sin cargos ocultos.</p>
      </div>
      <ul class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
        <?php foreach ($servicios as [$nombreServicio, $detalleServicio]): ?>
          <li class="border-t border-linea pt-5">
            <h3 class="font-medium"><?= htmlspecialchars($nombreServicio) ?></h3>
            <p class="mt-1 text-musgo"><?= htmlspecialchars($detalleServicio) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <!-- Galería -->
  <section id="galeria" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-24 sm:px-8" aria-labelledby="titulo-galeria">
    <h2 id="titulo-galeria" class="font-titulo text-4xl sm:text-5xl">Galería</h2>
    <ul class="mt-12 grid auto-rows-[10rem] grid-cols-2 gap-3 sm:auto-rows-[14rem] md:grid-cols-4">
      <?php foreach ($galeria as [$imagen, $descripcion, $clases]): ?>
        <li class="rounded-2xl bg-cover bg-center <?= htmlspecialchars($clases) ?>" style="background-image: url('assets/img/<?= htmlspecialchars(rawurlencode($imagen)) ?>')"
            role="img" aria-label="<?= htmlspecialchars($descripcion) ?>"></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <!-- Opiniones (contenido de ejemplo) -->
  <section id="opiniones" class="scroll-mt-20 bg-cal" aria-labelledby="titulo-opiniones">
    <div class="mx-auto max-w-7xl px-4 py-24 sm:px-8">
      <h2 id="titulo-opiniones" class="font-titulo text-4xl sm:text-5xl">Lo que cuentan nuestros huéspedes</h2>
      <ul class="mt-12 grid gap-6 md:grid-cols-3">
        <?php foreach ($opiniones as [$texto, $autor, $contexto]): ?>
          <li>
            <figure class="flex h-full flex-col rounded-2xl bg-white p-7">
              <blockquote class="font-titulo text-xl font-medium leading-snug tracking-tight">“<?= htmlspecialchars($texto) ?>”</blockquote>
              <figcaption class="mt-auto pt-6 text-sm">
                <span class="font-medium"><?= htmlspecialchars($autor) ?></span>
                <span class="block text-musgo"><?= htmlspecialchars($contexto) ?></span>
              </figcaption>
            </figure>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <!-- Preguntas frecuentes -->
  <section id="preguntas" class="mx-auto grid max-w-7xl scroll-mt-20 gap-12 px-4 py-24 sm:px-8 lg:grid-cols-[1fr_2fr]" aria-labelledby="titulo-preguntas">
    <h2 id="titulo-preguntas" class="font-titulo text-4xl sm:text-5xl">Preguntas frecuentes</h2>
    <div class="border-t border-linea">
      <?php foreach ($preguntas as [$pregunta, $respuesta]): ?>
        <details class="group border-b border-linea">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-6 rounded-lg py-5 font-medium focus-visible:outline-2 focus-visible:outline-tinta [&::-webkit-details-marker]:hidden">
            <?= htmlspecialchars($pregunta) ?>
            <svg viewBox="0 0 24 24" class="size-5 shrink-0 text-musgo transition-transform group-open:rotate-45 motion-reduce:transition-none" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
          </summary>
          <p class="max-w-[60ch] pb-6 text-musgo"><?= htmlspecialchars($respuesta) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Contacto -->
  <section id="contacto" class="scroll-mt-20 px-4 pb-24 sm:px-8" aria-labelledby="titulo-contacto">
    <div class="mx-auto grid max-w-7xl gap-10 overflow-hidden rounded-[2rem] bg-naranja px-6 py-14 text-tinta sm:px-12 lg:grid-cols-2">
      <div>
        <h2 id="titulo-contacto" class="font-titulo text-4xl sm:text-5xl">Cómo llegar</h2>
        <p class="mt-4 max-w-[40ch] text-tinta/85">A 10 minutos de la central de autobuses y a 30 del aeropuerto internacional.</p>
        <div class="mt-8 flex flex-wrap gap-3">
          <a href="#reservar" class="inline-flex h-12 items-center rounded-xl bg-tinta px-6 font-medium text-white transition-colors hover:bg-black focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">Ver disponibilidad</a>
          <a href="login.php" class="inline-flex h-12 items-center rounded-xl border border-tinta/40 px-6 font-medium transition-colors hover:bg-tinta/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">Iniciar sesión</a>
        </div>
      </div>
      <dl class="grid gap-6 sm:grid-cols-2">
        <div><dt class="text-sm text-tinta/75">Dirección</dt><dd class="mt-1">Calle de la Cantera 14, Centro, Querétaro, Qro.</dd></div>
        <div><dt class="text-sm text-tinta/75">Teléfono</dt><dd class="mt-1"><a href="tel:+524420000000" class="underline decoration-tinta/40 underline-offset-4 hover:decoration-tinta">442 000 0000</a></dd></div>
        <div><dt class="text-sm text-tinta/75">Correo</dt><dd class="mt-1"><a href="mailto:reservaciones@casacantera.mx" class="underline decoration-tinta/40 underline-offset-4 hover:decoration-tinta">reservaciones@casacantera.mx</a></dd></div>
        <div><dt class="text-sm text-tinta/75">Horarios</dt><dd class="mt-1">Entrada 15:00 · Salida 12:00</dd></div>
      </dl>
    </div>
  </section>
</main>
<?php
require __DIR__ . '/../app/vistas/pie_sitio.php';
require __DIR__ . '/../app/vistas/pie.php';

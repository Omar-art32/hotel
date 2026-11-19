<?php
require __DIR__ . '/../app/config/config.php';
Sesion::iniciar(); // La cabecera muestra "Mi cuenta" si hay sesión.

// Plantilla de ejemplo: los textos entre <mark> son datos que el hotel debe completar.
// Antes de publicar, estos términos debe revisarlos una persona especialista en derecho.
$tituloDocumento = 'Términos y condiciones';
$fechaActualizacion = '19 de noviembre de 2026';
$introduccion = 'Estas condiciones aplican al uso de este sitio, a tu cuenta y a las reservaciones que hagas en ' . htmlspecialchars(NOMBRE_HOTEL) . '. Al crear una cuenta o reservar, aceptas estas condiciones.';

$secciones = [
    [
        'id' => 'cuenta',
        'titulo' => 'Tu cuenta',
        'contenido' => '
            <ul>
              <li>Necesitas ser mayor de 18 años para crear una cuenta y reservar.</li>
              <li>Los datos que registres deben ser verdaderos y estar actualizados.</li>
              <li>Eres responsable de mantener tu contraseña en privado. Si sospechas que alguien más entró a tu cuenta, cámbiala y avísanos.</li>
              <li>Podemos suspender cuentas que se usen para fraudes o reservaciones falsas.</li>
            </ul>',
    ],
    [
        'id' => 'reservaciones',
        'titulo' => 'Reservaciones y tarifas',
        'contenido' => '
            <p>Tu reservación queda confirmada cuando recibes el correo de confirmación con su número de folio.</p>
            <p>Las tarifas se muestran en pesos mexicanos por noche e <mark>[incluyen / no incluyen]</mark> IVA y el impuesto sobre hospedaje. El precio que aparece al confirmar es el que pagarás.</p>
            <p>Formas de pago aceptadas: <mark>[completar]</mark>.</p>',
    ],
    [
        'id' => 'cancelaciones',
        'titulo' => 'Cambios y cancelaciones',
        'contenido' => '
            <ul>
              <li>Puedes cambiar o cancelar sin costo hasta 48 horas antes de la fecha de llegada.</li>
              <li>Si cancelas con menos de 48 horas de anticipación, se cobra la primera noche.</li>
              <li>Si no te presentas el día de llegada y no avisaste, se cobra la primera noche y se libera el resto de la reservación.</li>
            </ul>
            <p>Puedes hacer cambios y cancelaciones desde <a href="mis_reservaciones.php">Mis reservaciones</a>.</p>',
    ],
    [
        'id' => 'estancia',
        'titulo' => 'Entrada, salida y estancia',
        'contenido' => '
            <ul>
              <li>La entrada es a partir de las 15:00 y la salida antes de las 12:00.</li>
              <li>Al llegar, cada huésped adulto debe presentar una identificación oficial vigente.</li>
              <li>Todas las habitaciones son libres de humo.</li>
              <li>Aceptamos mascotas de hasta 10 kg en la Habitación Patio, con aviso previo.</li>
              <li>Por respeto a los demás huéspedes, pedimos guardar silencio entre las 22:00 y las 8:00.</li>
            </ul>',
    ],
    [
        'id' => 'responsabilidad',
        'titulo' => 'Responsabilidad',
        'contenido' => '
            <p>Cada habitación cuenta con caja de seguridad. El hotel no se hace responsable por objetos de valor que no se guarden en ella, salvo en los casos que establezca la ley.</p>
            <p>Los daños causados a la habitación o a las instalaciones durante la estancia se cobrarán al huésped titular de la reservación.</p>',
    ],
    [
        'id' => 'sitio',
        'titulo' => 'Uso de este sitio',
        'contenido' => '
            <p>Los textos, fotografías y logotipos de este sitio pertenecen a <mark>[Razón social]</mark>. No puedes copiarlos ni usarlos con fines comerciales sin autorización.</p>
            <p>Hacemos lo posible por mantener la información del sitio correcta y disponible, pero puede haber interrupciones por mantenimiento.</p>',
    ],
    [
        'id' => 'privacidad',
        'titulo' => 'Privacidad',
        'contenido' => '
            <p>El uso de tus datos personales se rige por nuestro <a href="aviso_privacidad.php">aviso de privacidad</a>.</p>',
    ],
    [
        'id' => 'legislacion',
        'titulo' => 'Cambios y legislación aplicable',
        'contenido' => '
            <p>Podemos actualizar estas condiciones. Las reservaciones ya confirmadas se rigen por las condiciones vigentes al momento de hacerlas.</p>
            <p>Estas condiciones se rigen por las leyes de los Estados Unidos Mexicanos. Como consumidor, conservas los derechos que te otorga la Ley Federal de Protección al Consumidor.</p>
            <p>Para dudas o aclaraciones, escríbenos a <a href="mailto:reservaciones@casacantera.mx">reservaciones@casacantera.mx</a> o llámanos al <a href="tel:+524420000000">442 000 0000</a>.</p>',
    ],
];

$tituloPagina = $tituloDocumento;
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/cabecera_sitio.php';
require __DIR__ . '/../app/vistas/documento_legal.php';
require __DIR__ . '/../app/vistas/pie_sitio.php';
require __DIR__ . '/../app/vistas/pie.php';

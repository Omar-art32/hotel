<?php
require __DIR__ . '/../app/config/config.php';
Sesion::iniciar(); // La cabecera muestra "Mi cuenta" si hay sesión.

// Plantilla de ejemplo: los textos entre <mark> son datos que el hotel debe completar.
// Antes de publicar, este aviso debe revisarlo una persona especialista en protección de datos.
$tituloDocumento = 'Aviso de privacidad';
$fechaActualizacion = '19 de noviembre de 2026';
$introduccion = 'Este aviso explica qué datos personales recabamos cuando usas este sitio o te hospedas con nosotros, para qué los usamos y cómo puedes ejercer tus derechos sobre ellos.';

$secciones = [
    [
        'id' => 'responsable',
        'titulo' => 'Quién es responsable de tus datos',
        'contenido' => '
            <p><mark>[Razón social]</mark>, con nombre comercial ' . htmlspecialchars(NOMBRE_HOTEL) . ' y domicilio en Calle de la Cantera 14, Centro, Querétaro, Qro., es responsable del tratamiento de tus datos personales.</p>
            <p>Para cualquier asunto relacionado con este aviso, escríbenos a <a href="mailto:privacidad@casacantera.mx">privacidad@casacantera.mx</a>.</p>',
    ],
    [
        'id' => 'datos',
        'titulo' => 'Qué datos recabamos',
        'contenido' => '
            <ul>
              <li><strong>Identificación y contacto:</strong> nombre, apellidos, correo electrónico y, si lo proporcionas, teléfono.</li>
              <li><strong>Reservaciones:</strong> fechas de estancia, número de huéspedes, tipo de habitación y solicitudes especiales.</li>
              <li><strong>Registro en el hotel:</strong> los datos de tu identificación oficial que se solicitan al momento de la entrada.</li>
              <li><strong>Facturación:</strong> RFC, razón social y domicilio fiscal, solo si solicitas factura.</li>
              <li><strong>Seguridad de la cuenta:</strong> fecha, hora y dirección IP de los inicios de sesión y de las acciones realizadas en el sistema.</li>
            </ul>
            <p>No solicitamos datos personales sensibles.</p>',
    ],
    [
        'id' => 'finalidades',
        'titulo' => 'Para qué usamos tus datos',
        'contenido' => '
            <p>Usamos tus datos para estas finalidades, necesarias para darte el servicio:</p>
            <ul>
              <li>Crear y administrar tu cuenta.</li>
              <li>Registrar, modificar y cancelar tus reservaciones.</li>
              <li>Realizar tu entrada y salida del hotel.</li>
              <li>Emitir facturas cuando las solicites.</li>
              <li>Proteger tu cuenta y detectar accesos no autorizados.</li>
            </ul>
            <p>Además, si no te opones, los usaremos para enviarte promociones y encuestas de satisfacción. Puedes negarte en cualquier momento escribiendo a <a href="mailto:privacidad@casacantera.mx">privacidad@casacantera.mx</a>; negarte no afecta tus reservaciones.</p>',
    ],
    [
        'id' => 'transferencias',
        'titulo' => 'Con quién compartimos tus datos',
        'contenido' => '
            <p>No vendemos tus datos personales. Solo los compartimos en estos casos:</p>
            <ul>
              <li>Con autoridades competentes, cuando una ley o una orden lo requiera.</li>
              <li>Con proveedores que nos prestan servicios de alojamiento web, envío de correos y procesamiento de pagos, quienes solo pueden usarlos para prestarnos ese servicio.</li>
            </ul>',
    ],
    [
        'id' => 'derechos',
        'titulo' => 'Tus derechos sobre tus datos',
        'contenido' => '
            <p>Puedes acceder a tus datos, rectificarlos si son incorrectos, pedir que los cancelemos u oponerte a su uso (derechos ARCO). También puedes revocar tu consentimiento o limitar el uso de tus datos.</p>
            <p>Para hacerlo, envía un correo a <a href="mailto:privacidad@casacantera.mx">privacidad@casacantera.mx</a> con:</p>
            <ul>
              <li>Tu nombre y un medio para responderte.</li>
              <li>Una copia de tu identificación oficial.</li>
              <li>La descripción clara de lo que solicitas.</li>
            </ul>
            <p>Te responderemos dentro de los plazos que establece la legislación aplicable.</p>',
    ],
    [
        'id' => 'cookies',
        'titulo' => 'Cookies',
        'contenido' => '
            <p>Usamos una cookie de sesión, necesaria para mantenerte conectado mientras navegas en tu cuenta. Se borra al cerrar sesión o al cerrar el navegador.</p>
            <p>No usamos cookies de publicidad ni de seguimiento.</p>',
    ],
    [
        'id' => 'seguridad',
        'titulo' => 'Conservación y seguridad',
        'contenido' => '
            <p>Conservamos tus datos mientras tengas una cuenta activa y durante el tiempo que exijan las obligaciones fiscales y legales. Después los eliminamos de forma segura.</p>
            <p>Protegemos tus datos con medidas como contraseñas cifradas, conexiones seguras y registro de accesos al sistema.</p>',
    ],
    [
        'id' => 'cambios',
        'titulo' => 'Cambios a este aviso',
        'contenido' => '
            <p>Si modificamos este aviso, publicaremos la nueva versión en esta página con su fecha de actualización. Si el cambio afecta las finalidades para las que usamos tus datos, te avisaremos por correo.</p>
            <p>Si consideras que tus derechos no fueron atendidos, puedes acudir a la autoridad competente en materia de protección de datos personales.</p>',
    ],
];

$tituloPagina = $tituloDocumento;
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/cabecera_sitio.php';
require __DIR__ . '/../app/vistas/documento_legal.php';
require __DIR__ . '/../app/vistas/pie_sitio.php';
require __DIR__ . '/../app/vistas/pie.php';

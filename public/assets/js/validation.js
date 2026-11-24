// Validación de formularios con [data-validar] y botones para mostrar u ocultar contraseñas.

function obtenerMensajeError(campo) {
  if (campo.validity.valueMissing) return campo.dataset.errorVacio;
  if (campo.validity.typeMismatch || campo.validity.patternMismatch || campo.validity.tooShort) return campo.dataset.errorFormato;
  // Confirmación: data-igual-a="id" exige el mismo valor que el otro campo.
  const campoOriginal = document.getElementById(campo.dataset.igualA);
  if (campoOriginal && campo.value !== campoOriginal.value) return campo.dataset.errorCoincidencia;
  // Fechas: data-despues-de="id" exige que este valor sea posterior al del otro campo.
  const campoAnterior = document.getElementById(campo.dataset.despuesDe);
  if (campoAnterior && campoAnterior.value && campo.value <= campoAnterior.value) return campo.dataset.errorOrden;
  return '';
}

function mostrarError(campo, mensaje) {
  const elementoError = document.getElementById('error-' + campo.id);
  if (mensaje) campo.setAttribute('aria-invalid', 'true');
  else campo.removeAttribute('aria-invalid');
  if (!elementoError) return;
  elementoError.textContent = mensaje;
  elementoError.hidden = mensaje === '';
}

function validarFormulario(evento) {
  const formulario = evento.currentTarget;
  // Los opcionales con pattern (p. ej. teléfono) solo se validan si tienen contenido.
  const campos = [...formulario.querySelectorAll('input[required], input[pattern]')];
  let primerCampoInvalido = null;

  campos.forEach((campo) => {
    const mensaje = obtenerMensajeError(campo);
    mostrarError(campo, mensaje);
    if (mensaje && !primerCampoInvalido) primerCampoInvalido = campo;
  });

  if (primerCampoInvalido) {
    evento.preventDefault();
    primerCampoInvalido.focus();
    return;
  }

  const boton = formulario.querySelector('[type="submit"]');
  boton.disabled = true;
  boton.setAttribute('aria-busy', 'true');

  // Prototipo: sin backend, se simula la respuesta. Quitar data-prototipo-destino al conectar AuthController.
  const destinoPrototipo = formulario.dataset.prototipoDestino;
  if (destinoPrototipo) {
    evento.preventDefault();
    setTimeout(() => { window.location.href = destinoPrototipo; }, 1200);
  }
}

document.querySelectorAll('form[data-validar]').forEach((formulario) => {
  formulario.addEventListener('submit', validarFormulario);
  // El error desaparece en cuanto la persona corrige el campo.
  formulario.addEventListener('input', (evento) => {
    if (evento.target.hasAttribute('aria-invalid') && obtenerMensajeError(evento.target) === '') {
      mostrarError(evento.target, '');
    }
  });
});

document.querySelectorAll('input[data-solo-digitos]').forEach((campo) => {
  campo.addEventListener('input', () => { campo.value = campo.value.replace(/\D/g, ''); });
});

document.querySelectorAll('[data-alternar-contrasena]').forEach((boton) => {
  const campo = document.getElementById(boton.dataset.alternarContrasena);
  boton.addEventListener('click', () => {
    const estaOculta = campo.type === 'password';
    campo.type = estaOculta ? 'text' : 'password';
    boton.textContent = estaOculta ? 'Ocultar' : 'Mostrar';
    boton.setAttribute('aria-pressed', String(estaOculta));
    campo.focus();
  });
});

// Menú móvil: se cierra al elegir una sección.
document.querySelectorAll('details[data-menu]').forEach((menu) => {
  menu.addEventListener('click', (evento) => {
    if (evento.target.closest('a')) menu.open = false;
  });
});

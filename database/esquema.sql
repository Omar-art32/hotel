-- =====================================================
-- SISTEMA WEB DE RESERVACIONES HOTELERAS
-- BASE DE DATOS: equipo_2
-- MARIADB 10.11 / XAMPP
--
-- Criterios:
-- - Los registros importantes no se borran: se desactivan (esta_activo / esta_activa).
--   Por eso las llaves foráneas usan ON DELETE RESTRICT.
-- - CASCADE solo en tablas que dependen por completo de su dueño
--   (roles_permisos y tokens_usuario). La bitácora usa SET NULL para conservar el historial.
-- - Las validaciones de una sola fila usan CHECK; las que consultan otras tablas usan triggers.
-- =====================================================

CREATE DATABASE IF NOT EXISTS equipo_2
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE equipo_2;

-- =====================================================
-- 1. ROLES
-- =====================================================

CREATE TABLE roles (
    id_rol TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    requiere_2fa BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT uq_roles_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- =====================================================
-- 2. PERMISOS
-- =====================================================

CREATE TABLE permisos (
    id_permiso SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    CONSTRAINT uq_permisos_codigo UNIQUE (codigo)
) ENGINE=InnoDB;

-- =====================================================
-- 3. ROLES_PERMISOS
-- =====================================================

CREATE TABLE roles_permisos (
    id_rol TINYINT UNSIGNED NOT NULL,
    id_permiso SMALLINT UNSIGNED NOT NULL,

    PRIMARY KEY (id_rol, id_permiso),

    FOREIGN KEY (id_rol)
        REFERENCES roles(id_rol)
        ON DELETE CASCADE,

    FOREIGN KEY (id_permiso)
        REFERENCES permisos(id_permiso)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 4. USUARIOS
-- =====================================================

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol TINYINT UNSIGNED NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    correo VARCHAR(254) NOT NULL,
    telefono VARCHAR(20) NULL,
    contrasena_hash VARCHAR(255) NOT NULL,
    -- Secreto TOTP de la aplicación de autenticación. Debe guardarse cifrado por la aplicación.
    secreto_2fa VARCHAR(255) NULL,
    tiene_2fa_activo BOOLEAN NOT NULL DEFAULT FALSE,
    esta_activo BOOLEAN NOT NULL DEFAULT TRUE,
    esta_verificado BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_usuarios_correo UNIQUE (correo),

    FOREIGN KEY (id_rol)
        REFERENCES roles(id_rol)
        ON DELETE RESTRICT,

    INDEX idx_usuarios_rol (id_rol)
) ENGINE=InnoDB;

-- =====================================================
-- 5. TIPOS_HABITACION
-- =====================================================

CREATE TABLE tipos_habitacion (
    id_tipo_habitacion TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL,
    descripcion VARCHAR(255) NULL,
    capacidad TINYINT UNSIGNED NOT NULL,
    precio_noche DECIMAL(10,2) NOT NULL,
    metros_cuadrados SMALLINT UNSIGNED NULL,
    tipo_cama VARCHAR(60) NULL,
    -- Nombre del archivo en public/assets/img/.
    imagen VARCHAR(120) NULL,

    CONSTRAINT uq_tipos_habitacion_nombre UNIQUE (nombre),
    CONSTRAINT ck_tipos_habitacion_capacidad CHECK (capacidad > 0),
    CONSTRAINT ck_tipos_habitacion_precio CHECK (precio_noche > 0),
    CONSTRAINT ck_tipos_habitacion_metros CHECK (metros_cuadrados IS NULL OR metros_cuadrados > 0)
) ENGINE=InnoDB;

-- =====================================================
-- 6. HABITACIONES
-- =====================================================

CREATE TABLE habitaciones (
    id_habitacion SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_tipo_habitacion TINYINT UNSIGNED NOT NULL,
    numero VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255) NULL,

    estado ENUM(
        'disponible',
        'ocupada',
        'limpieza',
        'mantenimiento'
    ) NOT NULL DEFAULT 'disponible',

    esta_activa BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT uq_habitaciones_numero UNIQUE (numero),

    FOREIGN KEY (id_tipo_habitacion)
        REFERENCES tipos_habitacion(id_tipo_habitacion)
        ON DELETE RESTRICT,

    INDEX idx_habitaciones_tipo (id_tipo_habitacion),
    INDEX idx_habitaciones_estado (estado)
) ENGINE=InnoDB;

-- =====================================================
-- 7. RESERVACIONES
-- =====================================================

CREATE TABLE reservaciones (
    id_reservacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_habitacion SMALLINT UNSIGNED NOT NULL,

    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,

    num_personas TINYINT UNSIGNED NOT NULL,
    precio_total DECIMAL(10,2) NOT NULL,

    estado ENUM(
        'confirmada',
        'check_in',
        'finalizada',
        'cancelada',
        'no_show'
    ) NOT NULL DEFAULT 'confirmada',

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT ck_reservaciones_fechas CHECK (fecha_salida > fecha_entrada),
    CONSTRAINT ck_reservaciones_personas CHECK (num_personas > 0),
    CONSTRAINT ck_reservaciones_precio CHECK (precio_total > 0),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE RESTRICT,

    FOREIGN KEY (id_habitacion)
        REFERENCES habitaciones(id_habitacion)
        ON DELETE RESTRICT,

    INDEX idx_reservaciones_habitacion_fechas
        (id_habitacion, fecha_entrada, fecha_salida),

    INDEX idx_reservaciones_usuario (id_usuario),
    INDEX idx_reservaciones_estado (estado)
) ENGINE=InnoDB;

-- =====================================================
-- 8. PAGOS
-- =====================================================

CREATE TABLE pagos (
    id_pago INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_reservacion INT UNSIGNED NOT NULL,
    monto DECIMAL(10,2) NOT NULL,

    metodo_pago ENUM(
        'efectivo',
        'tarjeta',
        'transferencia',
        'otro'
    ) NOT NULL,

    estado ENUM(
        'pendiente',
        'completado',
        'rechazado',
        'reembolsado'
    ) NOT NULL DEFAULT 'pendiente',

    referencia VARCHAR(100) NULL,
    fecha_pago DATETIME NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT ck_pagos_monto CHECK (monto > 0),

    FOREIGN KEY (id_reservacion)
        REFERENCES reservaciones(id_reservacion)
        ON DELETE RESTRICT,

    INDEX idx_pagos_reservacion (id_reservacion),
    INDEX idx_pagos_estado (estado)
) ENGINE=InnoDB;

-- =====================================================
-- 9. SERVICIOS
-- =====================================================

CREATE TABLE servicios (
    id_servicio SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NULL,
    precio DECIMAL(10,2) NOT NULL,
    esta_activo BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT uq_servicios_nombre UNIQUE (nombre),
    CONSTRAINT ck_servicios_precio CHECK (precio >= 0)
) ENGINE=InnoDB;

-- =====================================================
-- 10. RESERVACIONES_SERVICIOS
-- =====================================================

CREATE TABLE reservaciones_servicios (
    id_reservacion_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_reservacion INT UNSIGNED NOT NULL,
    id_servicio SMALLINT UNSIGNED NOT NULL,
    cantidad SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT ck_rs_cantidad CHECK (cantidad > 0),
    CONSTRAINT ck_rs_precio CHECK (precio_unitario >= 0),

    FOREIGN KEY (id_reservacion)
        REFERENCES reservaciones(id_reservacion)
        ON DELETE RESTRICT,

    FOREIGN KEY (id_servicio)
        REFERENCES servicios(id_servicio)
        ON DELETE RESTRICT,

    INDEX idx_rs_reservacion (id_reservacion),
    INDEX idx_rs_servicio (id_servicio)
) ENGINE=InnoDB;

-- =====================================================
-- 11. BITACORA
-- =====================================================

CREATE TABLE bitacora (
    id_bitacora BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NULL,
    evento VARCHAR(50) NOT NULL,
    detalle VARCHAR(255) NULL,
    ip VARCHAR(45) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL,

    INDEX idx_bitacora_fecha (fecha_creacion),
    INDEX idx_bitacora_usuario (id_usuario)
) ENGINE=InnoDB;

-- =====================================================
-- 12. INTENTOS_LOGIN
-- =====================================================

CREATE TABLE intentos_login (
    id_intento_login BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(254) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    fue_exitoso BOOLEAN NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_intentos_correo_fecha
        (correo, fecha_creacion),

    INDEX idx_intentos_ip_fecha
        (ip, fecha_creacion)
) ENGINE=InnoDB;

-- =====================================================
-- 13. TOKENS_USUARIO
-- Recuperación de contraseña, verificación de correo y "mantener la sesión iniciada".
-- Solo se guarda el hash SHA-256 del token, nunca el token.
-- =====================================================

CREATE TABLE tokens_usuario (
    id_token_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,

    tipo ENUM(
        'recuperacion',
        'verificacion_correo',
        'recordar_sesion'
    ) NOT NULL,

    token_hash CHAR(64) NOT NULL,
    fecha_expiracion DATETIME NOT NULL,
    esta_usado BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_tokens_hash UNIQUE (token_hash),

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    INDEX idx_tokens_usuario_tipo (id_usuario, tipo)
) ENGINE=InnoDB;

-- =====================================================
-- 14. REGISTRO DE ROLES
-- =====================================================

INSERT INTO roles (nombre, descripcion, requiere_2fa) VALUES
('invitado', 'Visitante de la pagina web', FALSE),
('cliente', 'Usuario registrado que realiza reservaciones', FALSE),
('recepcionista', 'Atencion en recepcion: entradas, salidas y cobros', TRUE),
('administrador', 'Gestion de las operaciones del hotel', TRUE),
('superadministrador', 'Control total del sistema', TRUE);

-- =====================================================
-- 15. REGISTRO DE PERMISOS
-- =====================================================

INSERT INTO permisos (codigo, descripcion) VALUES
('ver_pagina', 'Consultar pagina publica'),
('consultar_habitaciones', 'Consultar habitaciones'),
('consultar_disponibilidad', 'Consultar disponibilidad'),
('registrarse', 'Crear cuenta de cliente'),
('realizar_reservacion', 'Realizar reservaciones'),
('consultar_reservaciones', 'Consultar reservaciones propias'),
('cancelar_reservacion', 'Cancelar reservaciones propias'),
('gestionar_perfil', 'Modificar perfil personal'),
('realizar_check_in', 'Registrar la entrada de huespedes'),
('realizar_check_out', 'Registrar la salida de huespedes'),
('actualizar_estado_habitacion', 'Cambiar el estado de una habitacion'),
('gestionar_habitaciones', 'Administrar habitaciones'),
('gestionar_tipos_habitacion', 'Administrar tipos de habitaciones'),
('gestionar_reservaciones', 'Administrar reservaciones'),
('gestionar_clientes', 'Administrar clientes'),
('gestionar_pagos', 'Administrar pagos'),
('gestionar_servicios', 'Administrar servicios'),
('consultar_estadisticas', 'Consultar estadisticas'),
('gestionar_usuarios', 'Administrar usuarios'),
('gestionar_roles', 'Administrar roles'),
('gestionar_permisos', 'Administrar permisos'),
('configurar_sistema', 'Configurar el sistema'),
('consultar_bitacora', 'Consultar bitacora'),
('consultar_intentos_login', 'Consultar intentos de acceso');

-- =====================================================
-- 16. PERMISOS POR ROL (se buscan por nombre, no por id)
-- =====================================================

INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
INNER JOIN permisos p ON p.codigo IN (
    'ver_pagina',
    'consultar_habitaciones',
    'consultar_disponibilidad',
    'registrarse'
)
WHERE r.nombre = 'invitado';

INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
INNER JOIN permisos p ON p.codigo IN (
    'ver_pagina',
    'consultar_habitaciones',
    'consultar_disponibilidad',
    'realizar_reservacion',
    'consultar_reservaciones',
    'cancelar_reservacion',
    'gestionar_perfil'
)
WHERE r.nombre = 'cliente';

INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
INNER JOIN permisos p ON p.codigo IN (
    'ver_pagina',
    'consultar_habitaciones',
    'consultar_disponibilidad',
    'gestionar_perfil',
    'realizar_check_in',
    'realizar_check_out',
    'actualizar_estado_habitacion',
    'gestionar_reservaciones',
    'gestionar_pagos'
)
WHERE r.nombre = 'recepcionista';

INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
INNER JOIN permisos p ON p.codigo IN (
    'ver_pagina',
    'consultar_habitaciones',
    'consultar_disponibilidad',
    'gestionar_perfil',
    'realizar_check_in',
    'realizar_check_out',
    'actualizar_estado_habitacion',
    'gestionar_habitaciones',
    'gestionar_tipos_habitacion',
    'gestionar_reservaciones',
    'gestionar_clientes',
    'gestionar_pagos',
    'gestionar_servicios',
    'consultar_estadisticas'
)
WHERE r.nombre = 'administrador';

INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
CROSS JOIN permisos p
WHERE r.nombre = 'superadministrador';

-- =====================================================
-- 17. VALIDACION DE RESERVACIONES
-- Capacidad y habitación activa: al crear, o al cambiar habitación o número de personas.
-- Traslape de fechas: siempre que la reservación quede activa (confirmada o check_in).
-- FOR UPDATE bloquea las reservaciones de esa habitación para que dos reservas
-- simultáneas no pasen la validación al mismo tiempo.
-- =====================================================

DELIMITER //

CREATE PROCEDURE validar_reservacion(
    IN p_id_reservacion INT UNSIGNED,
    IN p_id_habitacion SMALLINT UNSIGNED,
    IN p_fecha_entrada DATE,
    IN p_fecha_salida DATE,
    IN p_num_personas TINYINT UNSIGNED,
    IN p_estado VARCHAR(20),
    IN p_validar_habitacion BOOLEAN
)
BEGIN
    DECLARE v_capacidad TINYINT UNSIGNED DEFAULT NULL;
    DECLARE v_traslapes INT DEFAULT 0;

    IF p_validar_habitacion THEN
        SELECT th.capacidad
        INTO v_capacidad
        FROM habitaciones h
        INNER JOIN tipos_habitacion th
            ON h.id_tipo_habitacion = th.id_tipo_habitacion
        WHERE h.id_habitacion = p_id_habitacion
          AND h.esta_activa = TRUE;

        IF v_capacidad IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Habitacion inexistente o inactiva';
        END IF;

        IF p_num_personas > v_capacidad THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Se supera la capacidad de la habitacion';
        END IF;
    END IF;

    IF p_estado IN ('confirmada', 'check_in') THEN
        SELECT COUNT(*)
        INTO v_traslapes
        FROM reservaciones r
        WHERE r.id_habitacion = p_id_habitacion
          AND r.id_reservacion <> p_id_reservacion
          AND r.estado IN ('confirmada', 'check_in')
          AND r.fecha_entrada < p_fecha_salida
          AND r.fecha_salida > p_fecha_entrada
        FOR UPDATE;

        IF v_traslapes > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La habitacion ya esta reservada en esas fechas';
        END IF;
    END IF;
END//

CREATE TRIGGER validar_reservacion_insert
BEFORE INSERT ON reservaciones
FOR EACH ROW
BEGIN
    CALL validar_reservacion(
        NEW.id_reservacion, NEW.id_habitacion,
        NEW.fecha_entrada, NEW.fecha_salida,
        NEW.num_personas, NEW.estado, TRUE
    );
END//

CREATE TRIGGER validar_reservacion_update
BEFORE UPDATE ON reservaciones
FOR EACH ROW
BEGIN
    CALL validar_reservacion(
        NEW.id_reservacion, NEW.id_habitacion,
        NEW.fecha_entrada, NEW.fecha_salida,
        NEW.num_personas, NEW.estado,
        NEW.id_habitacion <> OLD.id_habitacion OR NEW.num_personas <> OLD.num_personas
    );
END//

DELIMITER ;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================

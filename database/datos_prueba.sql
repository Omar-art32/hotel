-- =====================================================
-- DATOS DE PRUEBA: solo para desarrollo, nunca en producción.
-- Importar después de esquema.sql.
--
-- Contraseña de todos los usuarios: Hotel#2026
-- (.test es un dominio reservado para pruebas: estos correos no existen)
-- =====================================================

USE equipo_2;

SET @hash_prueba = '$2y$12$Kh9HjqKS/vcFbm9wEqYo4OB2sC2088bNUUnLEWgtIU1YwWq83YXYu';

INSERT INTO usuarios (id_rol, nombre, apellidos, correo, telefono, contrasena_hash, esta_activo, esta_verificado)
SELECT r.id_rol, d.nombre, d.apellidos, d.correo, NULL, @hash_prueba, d.esta_activo, d.esta_verificado
FROM (
    SELECT 'superadministrador' AS rol, 'Sofía' AS nombre, 'Ramírez Ortega' AS apellidos, 'superadmin@hotel.test' AS correo, TRUE AS esta_activo, TRUE AS esta_verificado
    UNION ALL SELECT 'administrador', 'Daniel', 'Herrera Luna', 'admin@hotel.test', TRUE, TRUE
    UNION ALL SELECT 'recepcionista', 'Lucía', 'Mendoza Paz', 'recepcion@hotel.test', TRUE, TRUE
    UNION ALL SELECT 'cliente', 'Andrés', 'Torres Vega', 'cliente@hotel.test', TRUE, TRUE
    UNION ALL SELECT 'cliente', 'Marta', 'Gil Robles', 'inactivo@hotel.test', FALSE, TRUE
    UNION ALL SELECT 'cliente', 'Iván', 'Soto Cruz', 'sin.verificar@hotel.test', TRUE, FALSE
) AS d
INNER JOIN roles r ON r.nombre = d.rol;

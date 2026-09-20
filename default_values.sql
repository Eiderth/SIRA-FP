USE db_peñalver;

-- =========================================================================
-- 2. DIRECCIONES DE PRUEBA (Usando Parroquia id = 269: Tocuyito, Carabobo)
-- =========================================================================

INSERT INTO DIRECCION (id, parroquia_id, sector, avenida, calle, manzana, vereda, numero_vivienda, tipo_vivienda, condicion_vivienda, infraestructura_vivienda) VALUES
(1, 269, 'Tocuyito Centro', 'Bolívar', 'Calle 5', 'Manzana C', 'V-2', '12-4', 'Casa', 'Propia', 'Buena'),
(2, 269, 'El Naipe', 'Principal', 'Calle Principal', NULL, NULL, '45', 'Casa', 'Prestada', 'Regular'),
(3, 269, 'Palo Negro', 'Sur', 'Callejon Los Mangos', NULL, NULL, '89', 'Quinta', 'Propia', 'Buena'),
(4, 269, 'La Encrucijada', 'Los Próceres', 'Calle 3', 'Manzana A', 'V-1', '10', 'Casa', 'Alquilada', 'Buena'),
(5, 269, 'Fundalara', 'Norte', 'Calle 8', NULL, NULL, '23-B', 'Edificio', 'Propia', 'Buena'),
(6, 269, 'Urb. Libertador', 'Principal', 'Calle 1', 'Manzana F', NULL, '56', 'Casa', 'Pagandose', 'Buena'),
(7, 269, 'Sector Monumental', 'Bolívar', 'Calle 4', NULL, NULL, '78', 'Casa', 'Propia', 'Deteriorada'),
(8, 269, 'Barrera Sur', 'Carabobo', 'Calle 2', NULL, NULL, '90', 'Rancho', 'Invadida', 'Deteriorada'),
(9, 269, 'Lagunita', 'Principal', 'Calle Central', NULL, NULL, '12', 'Casa', 'Alquilada', 'Regular'),
(10, 269, 'Campo Elías', 'Los Cedros', 'Calle 6', NULL, NULL, '34', 'Quinta', 'Propia', 'Buena'),
(11, 269, 'Hato Royal', 'Principal', 'Calle Principal', NULL, NULL, '55', 'Casa', 'Propia', 'Buena'),
(12, 269, 'La Guasima', 'Sur', 'Calle 9', NULL, NULL, '88', 'Edificio', 'Alquilada', 'Buena'),
(13, 269, 'Sector Centro', 'Bolívar', 'Calle 10', NULL, NULL, '99', 'Casa', 'Propia', 'Buena'),
(14, 269, 'Parque Valencia', 'Este', 'Calle 7', NULL, NULL, '33', 'Apartamento', 'Pagandose', 'Buena'),
(15, 269, 'Sector La Manga', 'Principal', 'Callejon Sucre', NULL, NULL, '14', 'Casa', 'Prestada', 'Regular');

-- =========================================================================
-- 3. DATOS ANTROPOMÉTRICOS
-- =========================================================================

INSERT INTO ANTROPOMETRICO (id, estatura, peso, talla_camisa, talla_pantalon, talla_zapato) VALUES
(1, 1.20, 25.50, '6', '6', 28),
(2, 1.25, 27.00, '8', '8', 29),
(3, 1.15, 22.10, '4', '4', 26),
(4, 1.30, 30.20, '10', '10', 31),
(5, 1.22, 26.00, '8', '8', 30),
(6, 1.28, 29.40, '10', '10', 32),
(7, 1.18, 24.00, '6', '6', 27),
(8, 1.32, 32.50, '12', '12', 33),
(9, 1.21, 25.00, '8', '8', 29),
(10, 1.26, 28.10, '10', '10', 31),
(11, 1.19, 24.50, '6', '6', 28),
(12, 1.35, 34.00, '12', '12', 34),
(13, 1.23, 26.80, '8', '8', 30),
(14, 1.27, 29.00, '10', '10', 32),
(15, 1.17, 23.50, '6', '6', 27);

-- =========================================================================
-- 4. DATOS DE SALUD
-- =========================================================================

INSERT INTO SALUD (id, reacciones_alergicas, cuales_alergias, enfermedades_padecidas, atencion_especial, horario_tratamiento, atendido_por_especialista, nombre_especialista, fecha_inicio_especialista, condicion_atencion) VALUES
(1, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(2, 'Si', 'Polvo', 'Asma leve', 'No', NULL, 'No', NULL, NULL, NULL),
(3, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(4, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(5, 'Si', 'Mariscos', NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(6, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(7, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(8, 'Si', 'Penicilina', NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(9, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(10, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(11, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(12, 'Si', 'Picadura de abeja', NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(13, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(14, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL),
(15, 'No', NULL, NULL, 'No', NULL, 'No', NULL, NULL, NULL);

-- =========================================================================
-- 5. DATOS EXTRACURRICULARES Y CANAIMAS
-- =========================================================================

INSERT INTO EXTRA_CURRICULAR (id, realiza_deportes, cuales_deportes, posee_canaima, fecha_asignacion_canaima, serial_canaima, estado_canaima, falla_canaima, posee_cargador, estado_cargador, falla_cargador) VALUES
(1, 'Si', 'Fútbol', 'Si', '2024-10-10', 'CNM-12345', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(2, 'No', NULL, 'Si', '2024-10-10', 'CNM-12346', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(3, 'Si', 'Béisbol', 'No', NULL, NULL, 'Operativa', NULL, 'No', 'Operativo', NULL),
(4, 'Si', 'Natación', 'Si', '2024-10-10', 'CNM-12347', 'Dañada', 'Pantalla rota', 'No', 'Dañado', 'Cable partido'),
(5, 'No', NULL, 'Si', '2024-10-10', 'CNM-12348', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(6, 'Si', 'Fútbol', 'Si', '2024-10-10', 'CNM-12349', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(7, 'No', NULL, 'No', NULL, NULL, 'Operativa', NULL, 'No', 'Operativo', NULL),
(8, 'Si', 'Baloncesto', 'Si', '2024-10-10', 'CNM-12350', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(9, 'No', NULL, 'Si', '2024-10-10', 'CNM-12351', 'En Reparacion', 'Teclado', 'Si', 'Operativo', NULL),
(10, 'Si', 'Karate', 'Si', '2024-10-10', 'CNM-12352', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(11, 'No', NULL, 'No', NULL, NULL, 'Operativa', NULL, 'No', 'Operativo', NULL),
(12, 'Si', 'Fútbol', 'Si', '2024-10-10', 'CNM-12353', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(13, 'No', NULL, 'Si', '2024-10-10', 'CNM-12354', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(14, 'Si', 'Atletismo', 'Si', '2024-10-10', 'CNM-12355', 'Operativa', NULL, 'Si', 'Operativo', NULL),
(15, 'No', NULL, 'No', NULL, NULL, 'Operativa', NULL, 'No', 'Operativo', NULL);

-- =========================================================================
-- 6. PERSONAS: REPRESENTANTES (IDs 1 al 15) Y DATOS DE REPRESENTANTE
-- =========================================================================

-- Personas Representantes (IDs 1 a 15 en la tabla PERSONA)
INSERT INTO PERSONA (id, nacionalidad, cedula_identidad, nombre_1, nombre_2, apellido_1, apellido_2, fecha_nacimiento, sexo, direccion_id) VALUES
(1, 'V', '15111222', 'Mariana', 'Coromoto', 'Pérez', 'Gómez', '1985-04-12', 'F', 1),
(2, 'V', '16222333', 'Carlos', 'Eduardo', 'Rodríguez', 'Silva', '1982-09-20', 'M', 2),
(3, 'V', '14333444', 'Ana', 'Lucía', 'Martínez', 'López', '1988-01-15', 'F', 3),
(4, 'V', '17444555', 'José', 'Gregorio', 'Hernández', 'Díaz', '1980-07-30', 'M', 4),
(5, 'V', '18555666', 'Carmen', 'Rosa', 'González', 'Padrón', '1990-12-05', 'F', 5),
(6, 'V', '13666777', 'Luis', 'Alberto', 'Ramírez', 'Torres', '1979-03-22', 'M', 6),
(7, 'V', '19777888', 'Yelitza', 'Del Valle', 'Blanco', 'Rojas', '1986-11-18', 'F', 7),
(8, 'V', '12888999', 'Pedro', 'Pablo', 'Sánchez', 'Mendoza', '1975-06-10', 'M', 8),
(9, 'V', '20999000', 'Yenny', 'Carolina', 'Castillo', 'Vargas', '1992-05-25', 'F', 9),
(10, 'V', '15000111', 'Miguel', 'Ángel', 'Flores', 'Castillo', '1983-08-14', 'M', 10),
(11, 'V', '16111223', 'Rosa', 'Elena', 'Suárez', 'Morales', '1987-02-19', 'F', 11),
(12, 'V', '17222334', 'Alejandro', 'Javier', 'Rivas', 'Pérez', '1981-10-01', 'M', 12),
(13, 'V', '18333445', 'Daniela', 'Sofía', 'Navarro', 'Riera', '1991-07-07', 'F', 13),
(14, 'V', '14444556', 'Héctor', 'Manuel', 'Medina', 'Acosta', '1984-04-29', 'M', 14),
(15, 'V', '19555667', 'Maritza', 'Beatriz', 'Guerra', 'Salas', '1989-09-11', 'F', 15);

-- Relación Representante (IDs 1 al 15)
INSERT INTO PERSONA_REPRESENTANTE (id, parentesco, estado_civil, ingreso_mensual, telefono_movil, correo_electronico, profesion, empresa_trabajo, telefono_empresa, direccion_empresa, persona_id, estado) VALUES
(1, 'Madre', 'Casada/o', 350.00, '04121234567', 'mariana@gmail.com', 'Comerciante', 'Independiente', '02418000001', 'Tocuyito', 1, 'Activo'),
(2, 'Padre', 'Casada/o', 400.00, '04142345678', 'carlos@gmail.com', 'Técnico', 'Empresa C.A.', '02418000002', 'Valencia', 2, 'Activo'),
(3, 'Madre', 'Soltera/o', 300.00, '04163456789', 'ana@gmail.com', 'Docente', 'U.E. Nacional', '02418000003', 'Tocuyito', 3, 'Activo'),
(4, 'Padre', 'Concubinato', 450.00, '04244567890', 'jose@gmail.com', 'Obrero', 'Constructora', '02418000004', 'Valencia', 4, 'Activo'),
(5, 'Madre', 'Soltera/o', 280.00, '04125678901', 'carmen@gmail.com', 'Asistente', 'Oficina', '02418000005', 'Tocuyito', 5, 'Activo'),
(6, 'Padre', 'Casada/o', 500.00, '04146789012', 'luis@gmail.com', 'Ingeniero', 'Industria', '02418000006', 'Valencia', 6, 'Activo'),
(7, 'Madre', 'Divorciada/o', 320.00, '04167890123', 'yelitza@gmail.com', 'Enfermera', 'Hospital', '02418000007', 'Tocuyito', 7, 'Activo'),
(8, 'Padre', 'Casada/o', 380.00, '04248901234', 'pedro@gmail.com', 'Comerciante', 'Local Propio', '02418000008', 'Tocuyito', 8, 'Activo'),
(9, 'Madre', 'Soltera/o', 290.00, '04129012345', 'yenny@gmail.com', 'Estilista', 'Salón', '02418000009', 'Valencia', 9, 'Activo'),
(10, 'Padre', 'Concubinato', 420.00, '04140123456', 'miguel@gmail.com', 'Mecánico', 'Taller', '02418000010', 'Tocuyito', 10, 'Activo'),
(11, 'Madre', 'Casada/o', 350.00, '04161234568', 'rosa@gmail.com', 'Contador', 'Firma', '02418000011', 'Valencia', 11, 'Activo'),
(12, 'Padre', 'Casada/o', 460.00, '04242345679', 'alejandro@gmail.com', 'Chofer', 'Línea', '02418000012', 'Tocuyito', 12, 'Activo'),
(13, 'Madre', 'Soltera/o', 310.00, '04123456790', 'daniela@gmail.com', 'Secretaria', 'Empresa', '02418000013', 'Valencia', 13, 'Activo'),
(14, 'Padre', 'Casada/o', 490.00, '04144567891', 'hector@gmail.com', 'Supervisor', 'Galpón', '02418000014', 'Valencia', 14, 'Activo'),
(15, 'Madre', 'Viuda/o', 270.00, '04165678902', 'maritza@gmail.com', 'Comerciante', 'Independiente', '02418000015', 'Tocuyito', 15, 'Activo');

-- =========================================================================
-- 7. PERSONAS: ESTUDIANTES (IDs 16 al 30 en la tabla PERSONA) Y REGISTRO ESTUDIANTE
-- =========================================================================

-- Personas Estudiantes (IDs 16 a 30)
INSERT INTO PERSONA (id, nacionalidad, cedula_identidad, nombre_1, nombre_2, apellido_1, apellido_2, fecha_nacimiento, sexo, direccion_id) VALUES
(16, 'V', '35100101', 'Samuel', 'David', 'Pérez', 'Pérez', '2018-02-10', 'M', 1),
(17, 'V', '35100102', 'Valeria', 'Victoria', 'Rodríguez', 'Silva', '2018-05-14', 'F', 2),
(18, 'V', '35100103', 'Mateo', 'Alejandro', 'Martínez', 'López', '2019-01-20', 'M', 3),
(19, 'V', '35100104', 'Isabella', 'Sofía', 'Hernández', 'Díaz', '2018-08-11', 'F', 4),
(20, 'V', '35100105', 'Lucas', 'Gabriel', 'González', 'Padrón', '2019-03-03', 'M', 5),
(21, 'V', '35100106', 'Emma', 'Lucía', 'Ramírez', 'Torres', '2018-11-22', 'F', 6),
(22, 'V', '35100107', 'Santiago', 'José', 'Blanco', 'Rojas', '2019-07-05', 'M', 7),
(23, 'V', '35100108', 'Victoria', 'Valentina', 'Sánchez', 'Mendoza', '2018-04-19', 'F', 8),
(24, 'V', '35100109', 'Thiago', 'Daniel', 'Castillo', 'Vargas', '2019-09-30', 'M', 9),
(25, 'V', '35100110', 'Camila', 'Andreea', 'Flores', 'Castillo', '2018-12-12', 'F', 10),
(26, 'V', '35100111', 'Liam', 'Emanuel', 'Suárez', 'Morales', '2019-02-28', 'M', 11),
(27, 'V', '35100112', 'Sofía', 'Alejandra', 'Rivas', 'Pérez', '2018-06-17', 'F', 12),
(28, 'V', '35100113', 'Gael', 'Ignacio', 'Navarro', 'Riera', '2019-04-09', 'M', 13),
(29, 'V', '35100114', 'Valeria', 'Cristina', 'Medina', 'Acosta', '2018-10-25', 'F', 14),
(30, 'V', '35100115', 'Damián', 'Esteban', 'Guerra', 'Salas', '2019-05-16', 'M', 15);

-- Tabla PERSONA_ESTUDIANTE (Conectando cada estudiante con su respectivo antropométrico, salud, extra_curricular, representante y país de nacimiento ID 232)
INSERT INTO PERSONA_ESTUDIANTE (id, cedula_escolar, numero_hijo, lateralidad, persona_id, pais_nacimiento_id, parroquia_nacimiento_id, antropometrico_id, salud_id, extra_curricular_id, representante_principal_id, representante_secundario_id, estado) VALUES
(1, '35100101-1', 1, 'Derecha', 16, 232, 269, 1, 1, 1, 1, NULL, 'Activo'),
(2, '35100102-1', 1, 'Derecha', 17, 232, 269, 2, 2, 2, 2, NULL, 'Activo'),
(3, '35100103-1', 2, 'Izquierda', 18, 232, 269, 3, 3, 3, 3, NULL, 'Activo'),
(4, '35100104-1', 1, 'Derecha', 19, 232, 269, 4, 4, 4, 4, NULL, 'Activo'),
(5, '35100105-1', 1, 'Ambidextro', 20, 232, 269, 5, 5, 5, 5, NULL, 'Activo'),
(6, '35100106-1', 2, 'Derecha', 21, 232, 269, 6, 6, 6, 6, NULL, 'Activo'),
(7, '35100107-1', 1, 'Derecha', 22, 232, 269, 7, 7, 7, 7, NULL, 'Activo'),
(8, '35100108-1', 3, 'Izquierda', 23, 232, 269, 8, 8, 8, 8, NULL, 'Activo'),
(9, '35100109-1', 1, 'Derecha', 24, 232, 269, 9, 9, 9, 9, NULL, 'Activo'),
(10, '35100110-1', 1, 'Derecha', 25, 232, 269, 10, 10, 10, 10, NULL, 'Activo'),
(11, '35100111-1', 2, 'Derecha', 26, 232, 269, 11, 11, 11, 11, NULL, 'Activo'),
(12, '35100112-1', 1, 'Ambidextro', 27, 232, 269, 12, 12, 12, 12, NULL, 'Activo'),
(13, '35100113-1', 1, 'Derecha', 28, 232, 269, 13, 13, 13, 13, NULL, 'Activo'),
(14, '35100114-1', 2, 'Derecha', 29, 232, 269, 14, 14, 14, 14, NULL, 'Activo'),
(15, '35100115-1', 1, 'Izquierda', 30, 232, 269, 15, 15, 15, 15, NULL, 'Activo');

-- =========================================================================
-- 8. INSCRIPCIONES (Exactamente 15 registros para probar la paginación)
-- =========================================================================

INSERT INTO INSCRIPCION (id, periodo_academico_id, estudiante_id, grado_seccion_id, tipo_ingreso, colegio_procedencia) VALUES
(1, 1, 1, 1, 'Nuevo Ingreso', 'Preescolar Mi Pequeño Mundo'),
(2, 1, 2, 1, 'Regular', NULL),
(3, 1, 3, 1, 'Nuevo Ingreso', 'Preescolar Los Angelitos'),
(4, 1, 4, 1, 'Regular', NULL),
(5, 1, 5, 2, 'Nuevo Ingreso', 'Centro de Educación Inicial Carabobo'),
(6, 1, 6, 2, 'Repitiente', NULL),
(7, 1, 7, 2, 'Regular', NULL),
(8, 1, 8, 2, 'Nuevo Ingreso', 'Preescolar Semillitas de Fe'),
(9, 1, 9, 3, 'Regular', NULL),
(10, 1, 10, 3, 'Nuevo Ingreso', 'C.E.I. Ana María Campos'),
(11, 1, 11, 3, 'Regular', NULL),
(12, 1, 12, 1, 'Nuevo Ingreso', 'Preescolar Juan Pablo II'),
(13, 1, 13, 2, 'Regular', NULL),
(14, 1, 14, 3, 'Repitiente', NULL),
(15, 1, 15, 1, 'Nuevo Ingreso', 'Preescolar Bambi');
-- Base de datos
CREATE DATABASE sibe;
USE sibe;

-- TABLAS PRINCIPALES
-- 1. Tabla de áreas
CREATE TABLE areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

-- 2. Tabla de semestres
CREATE TABLE semestres (
    id_semestre INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL
);

-- 3. Tabla de turnos
CREATE TABLE turnos (
    id_turno INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL
);

-- 4. Tabla de carreras
CREATE TABLE carrera (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- 5. Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    area_id INT NOT NULL,
    FOREIGN KEY (area_id) REFERENCES areas(id)
);

-- 6. Tabla de tutores (con campo fecha agregado)
CREATE TABLE tutores (
    id_tutor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    dni CHAR(8) NOT NULL UNIQUE,
    correo VARCHAR(100) NOT NULL,
    telefono CHAR(9),
    id_carrera INT,
    id_semestre INT,
    id_turno INT,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activo', 'papelera') DEFAULT 'activo',
    FOREIGN KEY (id_carrera) REFERENCES carrera(id_carrera) ON DELETE SET NULL,
    FOREIGN KEY (id_semestre) REFERENCES semestres(id_semestre) ON DELETE SET NULL,
    FOREIGN KEY (id_turno) REFERENCES turnos(id_turno) ON DELETE SET NULL
);

-- 7. Tabla de registros de tópico
CREATE TABLE registros_topico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    dni VARCHAR(8) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    edad INT NOT NULL,
    id_carrera INT NOT NULL,
    id_semestre INT NOT NULL,
    id_turno INT NOT NULL,
    sintomas TEXT NOT NULL,
    estado ENUM('activo', 'papelera') DEFAULT 'activo',
    ultima_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_carrera) REFERENCES carrera(id_carrera),
    FOREIGN KEY (id_semestre) REFERENCES semestres(id_semestre),
    FOREIGN KEY (id_turno) REFERENCES turnos(id_turno)
);

-- 8. Tabla de inventario de medicamentos
CREATE TABLE inventario_medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 9. Tabla intermedia para asociar registros de tópico con medicamentos
CREATE TABLE registros_medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_registro_topico INT NOT NULL,
    id_medicamento INT NOT NULL,
    cantidad_utilizada INT NOT NULL,
    fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_registro_topico) REFERENCES registros_topico(id) ON DELETE CASCADE,
    FOREIGN KEY (id_medicamento) REFERENCES inventario_medicamentos(id) ON DELETE CASCADE
);

-- 10. Tabla principal de pacientes en psicología
CREATE TABLE registros_psicologia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni CHAR(8) NOT NULL,
    apellidos_nombres VARCHAR(50) NOT NULL,
    id_carrera INT NOT NULL,
    id_turno INT NOT NULL,
    edad INT,
    id_semestre INT NOT NULL,
    direccion VARCHAR(100),
    telefono CHAR(9),
    correo_estudiantil VARCHAR(100) NULL,
    sesiones INT(11) NOT NULL DEFAULT 1,
    tratamiento TEXT NULL,
    foto_evidencia VARCHAR(255) NULL,
    vive_con VARCHAR(100),
    motivo_consulta VARCHAR(100),
    antecedentes TEXT,
    fecha DATETIME NOT NULL, 
    ultima_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'papelera') DEFAULT 'activo',
    FOREIGN KEY (id_carrera) REFERENCES carrera(id_carrera),
    FOREIGN KEY (id_turno) REFERENCES turnos(id_turno),
    FOREIGN KEY (id_semestre) REFERENCES semestres(id_semestre)
);

-- 11. Tabla para gestionar citas psicológicas (con constraint modificada)
CREATE TABLE gestion_citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    paciente_id INT NOT NULL,
    psicologo_id INT NOT NULL,
    estado ENUM('Programada', 'Cancelada', 'Completada') NOT NULL DEFAULT 'Programada',
    FOREIGN KEY (paciente_id) REFERENCES registros_psicologia(id) ON DELETE CASCADE,
    FOREIGN KEY (psicologo_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 12. Tabla para registrar actividades realizadas
CREATE TABLE actividades_realizadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES registros_psicologia(id) ON DELETE CASCADE
);

-- 13. Tabla para registrar actividades realizadas en psicología
CREATE TABLE realizacion_psicologia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    duracion INT,
    cantidad_participantes INT,
    lugar VARCHAR(100),
    responsables VARCHAR(200) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    foto VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 14. Tabla intermedia para relacionar actividades con carreras
CREATE TABLE actividad_carrera (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    id_carrera INT NOT NULL,
    FOREIGN KEY (id_actividad) REFERENCES realizacion_psicologia(id) ON DELETE CASCADE,
    FOREIGN KEY (id_carrera) REFERENCES carrera(id_carrera) ON DELETE CASCADE
);

-- 15. Tabla orientacion_vocacional (con campo fecha agregado)
CREATE TABLE orientacion_vocacional (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apellidos_nombres VARCHAR(50) NOT NULL,
    celular VARCHAR(15),
    id_carrera INT,
    colegio VARCHAR(150),
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activo', 'papelera') DEFAULT 'activo',
    FOREIGN KEY (id_carrera) REFERENCES carrera(id_carrera)
);

-- DATOS INICIALES
INSERT INTO areas (nombre) VALUES
('Tópico'),
('Psicología'),
('Consejería'),
('Jefatura'),
('Administrador');

INSERT INTO carrera (nombre) VALUES
('Diseño y Programación Web'),
('Asistencia Administrativa'),
('Electricidad Industrial'),
('Mecánica de Producción Industrial'),
('Mecatrónica Automotriz'),
('Mantenimiento de Maquinaria Pesada'),
('Metalurgia'),
('Electrónica Industrial'),
('Tecnología de Análisis Químico');

INSERT INTO turnos (nombre) VALUES
('Diurno'),
('Vespertino');

INSERT INTO semestres (nombre) VALUES
('1er Semestre'),
('2do Semestre'),
('3er Semestre'),
('4to Semestre'),
('5to Semestre'),
('6to Semestre');
ALTER TABLE usuarios ADD COLUMN (
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_ingreso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_conexion DATETIME NULL,
    intentos_fallidos INT DEFAULT 0,
    bloqueado ENUM('si', 'no') DEFAULT 'no'
);
ALTER TABLE usuarios ADD COLUMN fecha_bloqueo DATETIME NULL;
-- Tabla para gestionar entregas de tutores
CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tutor INT NOT NULL,
    tema VARCHAR(255) NOT NULL,
    fecha_entrega DATE NOT NULL,
    evidencia VARCHAR(255) NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activo', 'papelera') DEFAULT 'activo',
    FOREIGN KEY (id_tutor) REFERENCES tutores(id_tutor) ON DELETE CASCADE
);
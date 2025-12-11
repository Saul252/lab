/* ==========================================================
   CREAR BASE DE DATOS
   ========================================================== */
CREATE DATABASE IF NOT EXISTS laboratorio;
USE laboratorio;

/* ==========================================================
   TABLA: PACIENTES
   ========================================================== */
CREATE TABLE pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    edad INT,
    sexo ENUM('H','M','Otro'),
    fecha_nacimiento DATE,
    domicilio VARCHAR(255),
    telefono VARCHAR(20),
    email VARCHAR(120),
    medico_solicitante VARCHAR(150),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* ==========================================================
   TABLA: CITAS (Relación con PACIENTES)
   ========================================================== */
CREATE TABLE citas (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    estado ENUM('programada','completada','cancelada') DEFAULT 'programada',
    observaciones TEXT,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente)
);

/* ==========================================================
   TABLA: ORDENES (Relación con PACIENTES y CITAS)
   ========================================================== */
CREATE TABLE ordenes (
    id_orden INT AUTO_INCREMENT PRIMARY KEY,
    folio VARCHAR(50) UNIQUE NOT NULL,
    id_paciente INT NOT NULL,
    id_cita INT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente','pagada','en_proceso','completa') DEFAULT 'pendiente',
    total DECIMAL(10,2) DEFAULT 0,

    /* Acceso temporal del paciente */
    usuario_temporal VARCHAR(50),
    password_temporal VARCHAR(255),
    expira_en DATETIME,

    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_cita) REFERENCES citas(id_cita)
);

/* ==========================================================
   TABLA: PAGOS (Relación con ORDENES)
   ========================================================== */
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    metodo ENUM('efectivo','tarjeta','transferencia') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    referencia VARCHAR(100),
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden)
);

/* ==========================================================
   TABLA: ESTUDIOS (Catálogo de estudios)
   ========================================================== */
CREATE TABLE estudios (
    id_estudio INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    unidad VARCHAR(50),
    tipo_resultado ENUM('numerico','cualitativo','ambos') DEFAULT 'numerico',
    
    /* Rangos por sexo */
    rango_hombre_min DECIMAL(10,2),
    rango_hombre_max DECIMAL(10,2),
    rango_mujer_min DECIMAL(10,2),
    rango_mujer_max DECIMAL(10,2),

    descripcion TEXT
);

/* ==========================================================
   TABLA: ORDEN_ESTUDIOS (Relación N:M entre ORDENES y ESTUDIOS)
   ========================================================== */
CREATE TABLE orden_estudios (
    id_orden_estudio INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    id_estudio INT NOT NULL,
    estado ENUM('pendiente','capturado','validado','aprobado') DEFAULT 'pendiente',
    cancelado TINYINT DEFAULT 0,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden),
    FOREIGN KEY (id_estudio) REFERENCES estudios(id_estudio)
);

/* ==========================================================
   TABLA: RESULTADOS (1 resultado por cada estudio dentro de una orden)
   ========================================================== */
CREATE TABLE resultados (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    id_orden_estudio INT NOT NULL,

    valor_numerico DECIMAL(10,2) NULL,
    valor_cualitativo VARCHAR(100) NULL,
    unidad VARCHAR(50),

    observaciones TEXT,
    interpretacion TEXT,

    capturado_por INT,
    validado_por INT,
    aprobado_por INT,

    fecha_captura TIMESTAMP NULL,
    fecha_validacion TIMESTAMP NULL,
    fecha_aprobacion TIMESTAMP NULL,

    FOREIGN KEY (id_orden_estudio) REFERENCES orden_estudios(id_orden_estudio)
);

/* ==========================================================
   TABLA: USUARIOS
   ========================================================== */
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    usuario VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    rol ENUM('admin','recepcion','laboratorio','valida','entrega'),
    activo TINYINT DEFAULT 1
);

/* ==========================================================
   TABLA: TICKETS (Impresiones y etiquetas)
   ========================================================== */
CREATE TABLE tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    codigo_barras VARCHAR(200),
    tipo ENUM('ticket','etiqueta') NOT NULL,
    fecha_impresion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    impreso_por INT,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden),
    FOREIGN KEY (impreso_por) REFERENCES usuarios(id_usuario)
);

/* ==========================================================
   TABLA: REACTIVOS (Inventario)
   ========================================================== */
CREATE TABLE reactivos (
    id_reactivo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    descripcion TEXT,
    unidad VARCHAR(50),
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 0
);

/* ==========================================================
   TABLA: LOTES_REACTIVOS (Control de lotes)
   ========================================================== */
CREATE TABLE lotes_reactivos (
    id_lote INT AUTO_INCREMENT PRIMARY KEY,
    id_reactivo INT NOT NULL,
    numero_lote VARCHAR(50),
    fecha_caducidad DATE,
    cantidad INT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_reactivo) REFERENCES reactivos(id_reactivo)
);

/* ==========================================================
   TABLA: MOVIMIENTOS_REACTIVOS (Entradas/Salidas)
   ========================================================== */
CREATE TABLE movimientos_reactivos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_lote INT NOT NULL,
    tipo ENUM('entrada','salida') NOT NULL,
    cantidad INT NOT NULL,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT,
    FOREIGN KEY (id_lote) REFERENCES lotes_reactivos(id_lote)
);

/* ==========================================================
   TABLA: AUDITORIA (Registro de acciones)
   ========================================================== */
CREATE TABLE auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    accion VARCHAR(255),
    tabla_afectada VARCHAR(100),
    id_registro_afectado INT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

/* ==========================================================
   TABLA: RESPALDOS (Historial de backups)
   ========================================================== */
CREATE TABLE respaldos (
    id_respaldo INT AUTO_INCREMENT PRIMARY KEY,
    ruta_archivo VARCHAR(255),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('auto','manual')
);

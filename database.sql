-- Estructura de la base de datos
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    correo VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    telefono VARCHAR(20),
    saldo_congelado DECIMAL(10,2) DEFAULT 0.00,
    meta_objetivo DECIMAL(10,2) DEFAULT 5000.00,
    permiso_retiro TINYINT(1) DEFAULT 0,
    codigo_referido VARCHAR(10) UNIQUE,
    referido_por_id INT DEFAULT NULL,
    pin_seguridad VARCHAR(4) DEFAULT '0000',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS log_transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    monto DECIMAL(10,2),
    referencia VARCHAR(100),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS datos_bancarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    banco_nombre VARCHAR(50),
    clabe VARCHAR(18),
    beneficiario VARCHAR(100),
    tipo_cuenta VARCHAR(20) DEFAULT 'Débito',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Añadir código de referido al usuario y quién lo invitó
ALTER TABLE usuarios ADD COLUMN codigo_referido VARCHAR(10) UNIQUE;
ALTER TABLE usuarios ADD COLUMN referido_por_id INT DEFAULT NULL;

-- Tabla para ver el historial de comisiones (opcional pero profesional)
CREATE TABLE IF NOT EXISTS comisiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    referido_id INT,
    monto_comision DECIMAL(10,2),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE usuarios ADD COLUMN pin_seguridad VARCHAR(4) DEFAULT '0000';

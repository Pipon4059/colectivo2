USE elcolectivo;

ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS activo TINYINT NOT NULL DEFAULT 1;
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS tipo ENUM('comprador', 'vendedor', 'ambos') NOT NULL DEFAULT 'ambos';
ALTER TABLE productos ADD COLUMN IF NOT EXISTS estado_conservacion VARCHAR(50) NOT NULL DEFAULT 'Bueno';
ALTER TABLE productos ADD COLUMN IF NOT EXISTS certificado VARCHAR(150) NULL;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS disponible TINYINT NOT NULL DEFAULT 1;

ALTER TABLE ordenes MODIFY estado ENUM('pendiente', 'confirmada', 'cancelada', 'pagado',
    'enviado', 'en_verificacion', 'verificado', 'liberado', 'en_disputa',
    'reembolsado', 'cancelado') NOT NULL DEFAULT 'pagado';
UPDATE ordenes SET estado = 'pagado' WHERE estado IN ('', 'pendiente', 'confirmada');
UPDATE ordenes SET estado = 'cancelado' WHERE estado = 'cancelada';
ALTER TABLE ordenes MODIFY estado ENUM('pagado', 'enviado', 'en_verificacion', 'verificado',
    'liberado', 'en_disputa', 'reembolsado', 'cancelado') NOT NULL DEFAULT 'pagado';
ALTER TABLE ordenes ADD COLUMN IF NOT EXISTS direccion VARCHAR(200) NOT NULL DEFAULT 'Sin direccion';
ALTER TABLE ordenes ADD COLUMN IF NOT EXISTS medio_pago VARCHAR(30) NOT NULL DEFAULT 'simulado';
ALTER TABLE ordenes ADD COLUMN IF NOT EXISTS codigo_seguimiento VARCHAR(100) NULL;

CREATE TABLE IF NOT EXISTS intermediarios (
    id_cop INT UNSIGNED PRIMARY KEY,
    CONSTRAINT fk_intermediario_usuario FOREIGN KEY (id_cop)
        REFERENCES usuarios(id_cop) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS verificaciones (
    id_verificacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_orden INT UNSIGNED NOT NULL,
    id_intermediario INT UNSIGNED NOT NULL,
    resultado ENUM('aprobado', 'rechazado') NOT NULL,
    observacion VARCHAR(500) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_verificacion_orden FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE CASCADE,
    CONSTRAINT fk_verificacion_intermediario FOREIGN KEY (id_intermediario) REFERENCES intermediarios(id_cop)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS disputas (
    id_disputa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_orden INT UNSIGNED NOT NULL,
    motivo VARCHAR(500) NOT NULL,
    estado ENUM('abierta', 'resuelta') NOT NULL DEFAULT 'abierta',
    resolucion VARCHAR(500) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_disputa_orden FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notificaciones (
    id_notificacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cop INT UNSIGNED NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leida TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT fk_notificacion_usuario FOREIGN KEY (id_cop) REFERENCES usuarios(id_cop) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS calificaciones (
    id_calificacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_orden INT UNSIGNED NOT NULL,
    id_autor INT UNSIGNED NOT NULL,
    id_calificado INT UNSIGNED NOT NULL,
    puntaje TINYINT UNSIGNED NOT NULL,
    comentario VARCHAR(300) NULL,
    UNIQUE KEY unica_calificacion (id_orden, id_autor),
    CONSTRAINT fk_calificacion_orden FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE CASCADE,
    CONSTRAINT fk_calificacion_autor FOREIGN KEY (id_autor) REFERENCES usuarios(id_cop),
    CONSTRAINT fk_calificacion_usuario FOREIGN KEY (id_calificado) REFERENCES usuarios(id_cop)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auditoria (
    id_auditoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cop INT UNSIGNED NULL,
    accion VARCHAR(150) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO usuarios (email, nombre, telefono, contrasena)
SELECT 'intermediario@elcolectivo.local', 'Intermediario Demo', '099222333',
       '$2y$10$GKdBwGx.ncdLXNiTKES31uRg8gI9IM6mYXS70lb3Zz/pNLcvhLmmy'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'intermediario@elcolectivo.local');

INSERT IGNORE INTO intermediarios (id_cop)
SELECT id_cop FROM usuarios WHERE email = 'intermediario@elcolectivo.local';

UPDATE productos
JOIN detalle_orden ON productos.id_producto = detalle_orden.id_producto
JOIN ordenes ON detalle_orden.id_orden = ordenes.id_orden
SET productos.disponible = 0
WHERE ordenes.estado NOT IN ('reembolsado', 'cancelado');

CREATE USER IF NOT EXISTS 'colectivo_app'@'127.0.0.1' IDENTIFIED BY 'colectivo123';
GRANT SELECT, INSERT, UPDATE, DELETE ON elcolectivo.* TO 'colectivo_app'@'127.0.0.1';

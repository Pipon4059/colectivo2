CREATE DATABASE IF NOT EXISTS elcolectivo
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE elcolectivo;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS imagenes;
DROP TABLE IF EXISTS detalle_orden;
DROP TABLE IF EXISTS ordenes;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS publicaciones;
DROP TABLE IF EXISTS administradores;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
    id_cop INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    calle VARCHAR(100) NULL,
    depto VARCHAR(50) NULL,
    numero VARCHAR(20) NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NULL,
    contrasena VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE administradores (
    id_cop INT UNSIGNED PRIMARY KEY,
    nivel TINYINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT fk_admin_usuario FOREIGN KEY (id_cop)
        REFERENCES usuarios(id_cop) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE clientes (
    id_cop INT UNSIGNED PRIMARY KEY,
    fecha_ingreso DATE NOT NULL,
    CONSTRAINT fk_cliente_usuario FOREIGN KEY (id_cop)
        REFERENCES usuarios(id_cop) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE publicaciones (
    id_publi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(500) NOT NULL,
    id_cop INT UNSIGNED NOT NULL,
    fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_publicacion_usuario FOREIGN KEY (id_cop)
        REFERENCES usuarios(id_cop) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE productos (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(500) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    precio DECIMAL(10,2) UNSIGNED NOT NULL,
    id_publi INT UNSIGNED NOT NULL,
    id_categoria INT UNSIGNED NOT NULL,
    CONSTRAINT fk_producto_publicacion FOREIGN KEY (id_publi)
        REFERENCES publicaciones(id_publi) ON DELETE CASCADE,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
) ENGINE=InnoDB;

CREATE TABLE imagenes (
    id_producto INT UNSIGNED NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_producto, imagen),
    CONSTRAINT fk_imagen_producto FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ordenes (
    id_orden INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estado ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_cop INT UNSIGNED NOT NULL,
    CONSTRAINT fk_orden_cliente FOREIGN KEY (id_cop)
        REFERENCES clientes(id_cop)
) ENGINE=InnoDB;

CREATE TABLE detalle_orden (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    precio_unitario DECIMAL(10,2) UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    id_orden INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    CONSTRAINT fk_detalle_orden FOREIGN KEY (id_orden)
        REFERENCES ordenes(id_orden) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto)
) ENGINE=InnoDB;

INSERT INTO usuarios (email, nombre, telefono, contrasena)
VALUES ('admin@elcolectivo.local', 'Administrador', '099000000',
        '$2y$10$GKdBwGx.ncdLXNiTKES31uRg8gI9IM6mYXS70lb3Zz/pNLcvhLmmy');
INSERT INTO administradores (id_cop, nivel) VALUES (LAST_INSERT_ID(), 1);

INSERT INTO categorias (nombre) VALUES
('Camisetas'), ('Pelotas'), ('Figuritas'), ('Botines'), ('Otros');

INSERT INTO usuarios (calle, depto, numero, email, nombre, telefono, contrasena)
VALUES ('18 de Julio', 'Montevideo', '1234', 'demo@elcolectivo.local', 'Usuario Demo',
        '098111222', '$2y$10$GKdBwGx.ncdLXNiTKES31uRg8gI9IM6mYXS70lb3Zz/pNLcvhLmmy');
SET @demo_id = LAST_INSERT_ID();
INSERT INTO clientes (id_cop, fecha_ingreso) VALUES (@demo_id, CURDATE());
INSERT INTO publicaciones (descripcion, id_cop)
VALUES ('Camiseta cuidada, ideal para coleccionistas.', @demo_id);
INSERT INTO productos (descripcion, nombre, precio, id_publi, id_categoria)
VALUES ('Camiseta original en excelente estado.', 'Camiseta Uruguay 2010', 5900, LAST_INSERT_ID(), 1);

INSERT INTO publicaciones (descripcion, id_cop)
VALUES ('Pelota oficial para coleccionistas.', @demo_id);
INSERT INTO productos (descripcion, nombre, precio, id_publi, id_categoria)
VALUES ('Pelota del Mundial de Qatar en muy buen estado.', 'Pelota Mundial 2022', 3200, LAST_INSERT_ID(), 2);

INSERT INTO publicaciones (descripcion, id_cop)
VALUES ('Album deportivo completo.', @demo_id);
INSERT INTO productos (descripcion, nombre, precio, id_publi, id_categoria)
VALUES ('Album con todas las figuritas del Mundial 2014.', 'Album Mundial 2014', 1800, LAST_INSERT_ID(), 3);

CREATE DATABASE IF NOT EXISTS FinalWebI;
CREATE USER 'MartinezJuan'@'localhost' IDENTIFIED BY 'FinalWebI';
GRANT ALL PRIVILEGES ON FinalWebI.* TO 'MartinezJuan'@'localhost';

USE finalWebI;

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contacto VARCHAR(100) NOT NULL,
    es_admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS razas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    cantidad_publicaciones INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    id_raza INT NOT NULL,
    lugar VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_raza) REFERENCES razas(id)
);

-- Crear tabla para manejo de JWT
CREATE TABLE IF NOT EXISTS token (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(500) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);


--Volcado de datos
INSERT INTO usuarios (nombre, email, password, contacto) VALUES
('Juan Perez', 'juan.perez@example.com', 'password123', '123456789'),
('Maria Gomez', 'maria.gomez@example.com', 'password123', '987654321'),
('Carlos Martinez', 'carlos.martinez@example.com', 'password123', '456789123');

INSERT INTO razas (nombre, cantidad_publicaciones) VALUES
('Labrador', 0),
('Golden Retriever', 0),
('Bulldog', 0),
('Beagle', 0),
('Poodle', 0);

INSERT INTO publicaciones (usuario_id, id_raza, lugar, foto) VALUES
(1, 1, 'Parque Central', 'labrador1.jpg'),
(2, 2, 'Plaza Mayor', 'golden1.jpg'),
(3, 3, 'Jardin Botanico', 'bulldog1.jpg'),
(1, 4, 'Parque de la Ciudad', 'beagle1.jpg'),
(2, 5, 'Playa Norte', 'poodle1.jpg');

UPDATE razas SET cantidad_publicaciones = (SELECT COUNT(*) FROM publicaciones WHERE id_raza = razas.id) WHERE id = 1;
UPDATE razas SET cantidad_publicaciones = (SELECT COUNT(*) FROM publicaciones WHERE id_raza = razas.id) WHERE id = 2;
UPDATE razas SET cantidad_publicaciones = (SELECT COUNT(*) FROM publicaciones WHERE id_raza = razas.id) WHERE id = 3;
UPDATE razas SET cantidad_publicaciones = (SELECT COUNT(*) FROM publicaciones WHERE id_raza = razas.id) WHERE id = 4;
UPDATE razas SET cantidad_publicaciones = (SELECT COUNT(*) FROM publicaciones WHERE id_raza = razas.id) WHERE id = 5;


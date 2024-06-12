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
    es_admin BOOLEAN NOT NULL
);

-- Crear tabla de publicaciones
CREATE TABLE IF NOT EXISTS publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    raza VARCHAR(100) NOT NULL,
    lugar VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla para manejo de JWT
CREATE TABLE IF NOT EXISTS token (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(500) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

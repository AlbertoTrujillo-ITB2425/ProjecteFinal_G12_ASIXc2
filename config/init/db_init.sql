CREATE DATABASE IF NOT EXISTS cyberaudit;
USE cyberaudit;
-- Tabla base de usuarios para el despliegue inicial
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

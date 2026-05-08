CREATE DATABASE IF NOT EXISTS cyberaudit;
USE cyberaudit;
-- Aquí puedes añadir tus tablas de usuarios, logs de auditoría, etc.
CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50), password VARCHAR(255));

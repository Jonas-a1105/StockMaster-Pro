-- Crear usuario seguro
CREATE USER IF NOT EXISTS 'app_stockmaster'@'localhost' IDENTIFIED BY 'StockMaster_Secure_2025!';

-- Otorgar permisos solo sobre la base de datos de la aplicación
GRANT ALL PRIVILEGES ON inventario_oop.* TO 'app_stockmaster'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- =========================================================================
-- SISTEMA DE INVENTARIO - ESQUEMA COMPLETO V2
-- Fecha de Generación: 2025
-- Descripción: Esquema unificado que incluye todas las tablas, actualizaciones y
-- nuevas características (IVA, Sucursales, Auditoría, API Keys, etc.)
-- =========================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- 1. Tabla: usuarios
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL, -- Agregado en V2
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `plan` enum('free','premium') NOT NULL DEFAULT 'free',
  `tasa_dolar` decimal(10,2) DEFAULT 0.00, -- Agregado en V2
  `trial_ends_at` datetime DEFAULT NULL,
  `empresa_nombre` varchar(255) DEFAULT NULL,
  `empresa_direccion` text DEFAULT NULL,
  `empresa_telefono` varchar(50) DEFAULT NULL,
  `empresa_logo` varchar(255) DEFAULT NULL,
  `rol` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabla: proveedores
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. Tabla: clientes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo_documento` enum('CI','RIF','Pasaporte','Otro') DEFAULT 'CI',
  `numero_documento` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `tipo_cliente` enum('Natural','Juridico') DEFAULT 'Natural',
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabla: productos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `precioCompraUSD` decimal(10,2) NOT NULL,
  `precioVentaUSD` decimal(10,2) NOT NULL,
  `gananciaUnitariaUSD` decimal(10,2) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  
  -- Campos V2 (IVA)
  `precio_base_usd` decimal(10,2) DEFAULT NULL, 
  `tiene_iva` tinyint(1) DEFAULT 0,  
  `iva_porcentaje` decimal(5,2) DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `idx_busqueda_nombre` (`nombre`),
  KEY `idx_tiene_iva` (`tiene_iva`),
  FULLTEXT KEY `idx_fulltext_search` (`nombre`, `categoria`),
  CONSTRAINT `fk_producto_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 5. Tabla: producto_proveedores
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `producto_proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `ultimo_precio_costo` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_prov` (`producto_id`,`proveedor_id`),
  KEY `proveedor_id` (`proveedor_id`),
  CONSTRAINT `producto_proveedores_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_proveedores_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 6. Tabla: ventas
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL, -- V2
  `total_usd` decimal(10,2) NOT NULL,
  `tasa_ves` decimal(10,2) NOT NULL,
  `total_ves` decimal(10,2) NOT NULL,
  `estado_pago` enum('Pagada','Pendiente') DEFAULT 'Pagada', -- V2
  `metodo_pago` enum('Efectivo','Transferencia','Debito','Credito','Mixto') DEFAULT 'Efectivo', -- V2
  `notas` text DEFAULT NULL, -- V2
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Tabla: venta_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venta_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario_usd` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `venta_items_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venta_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Tabla: compras
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `nro_factura` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Pagada') DEFAULT 'Pagada',
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `idx_estado_fecha` (`estado`,`fecha_vencimiento`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 9. Tabla: compra_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `compra_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compra_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_id` (`compra_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `compra_items_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compra_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 10. Tabla: movimientos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `productoNombre` varchar(255) DEFAULT NULL,
  `tipo` enum('Entrada','Salida') NOT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `proveedor` varchar(255) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `nota` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `movimientos_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 11. Tabla: api_keys (NUEVA)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `api_key` VARCHAR(64) NOT NULL UNIQUE,
    `nombre` VARCHAR(100) NOT NULL,
    `permisos` JSON NULL,
    `activo` BOOLEAN DEFAULT TRUE,
    `expira` DATETIME NULL,
    `ultimo_uso` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_api_key` (`api_key`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 12. Tabla: audit_logs (NUEVA)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT NULL,
    `entity_name` VARCHAR(255) NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 13. Tabla: notificaciones (NUEVA)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notificaciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `tipo` ENUM('stock', 'venta', 'factura', 'sistema') NOT NULL,
    `titulo` VARCHAR(100) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `prioridad` ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
    `link` VARCHAR(255) NULL,
    `icono` VARCHAR(50) DEFAULT 'fa-bell',
    `leida` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_leida` (`leida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 14. Tablas: Sucursales (NUEVAS)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sucursales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `codigo` VARCHAR(20) NULL,
    `direccion` TEXT NULL,
    `telefono` VARCHAR(30) NULL,
    `email` VARCHAR(100) NULL,
    `es_principal` BOOLEAN DEFAULT FALSE,
    `activa` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_sucursales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sucursal_id` INT NOT NULL,
    `producto_id` INT NOT NULL,
    `stock` INT DEFAULT 0,
    `stock_minimo` INT DEFAULT 5,
    `ubicacion` VARCHAR(100) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_sucursal_producto` (`sucursal_id`, `producto_id`),
    INDEX `idx_sucursal` (`sucursal_id`),
    INDEX `idx_producto` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transferencias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `sucursal_origen_id` INT NOT NULL,
    `sucursal_destino_id` INT NOT NULL,
    `producto_id` INT NOT NULL,
    `cantidad` INT NOT NULL,
    `estado` ENUM('Pendiente', 'En Tránsito', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
    `nota` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_origen` (`sucursal_origen_id`),
    INDEX `idx_destino` (`sucursal_destino_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 15. Tablas: Support Tickets (Legacy)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('Abierto','En Progreso','Cerrado') NOT NULL DEFAULT 'Abierto',
  `priority` enum('Baja','Media','Alta') NOT NULL DEFAULT 'Media',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ticket_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

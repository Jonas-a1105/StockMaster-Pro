-- SQLite Schema for Desktop App
-- Generated from sistema_inventario_completo.sql
-- Adapted for SQLite syntax (No ENUMs, different AUTOINCREMENT, separate Indices)

PRAGMA foreign_keys = OFF;

-- 1. Tabla: usuarios
CREATE TABLE IF NOT EXISTS "usuarios" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "owner_id" INTEGER DEFAULT NULL,
  "username" TEXT DEFAULT NULL,
  "email" TEXT NOT NULL UNIQUE,
  "password" TEXT NOT NULL,
  "plan" TEXT NOT NULL DEFAULT 'free', -- enum: free, premium
  "tasa_dolar" NUMERIC DEFAULT 0.00,
  "trial_ends_at" DATETIME DEFAULT NULL,
  "empresa_nombre" TEXT DEFAULT NULL,
  "empresa_direccion" TEXT DEFAULT NULL,
  "empresa_telefono" TEXT DEFAULT NULL,
  "empresa_logo" TEXT DEFAULT NULL,
  "rol" TEXT NOT NULL DEFAULT 'usuario', -- enum: usuario, admin
  "remember_token" TEXT DEFAULT NULL,
  "fecha_registro" DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX IF NOT EXISTS "idx_username" ON "usuarios" ("username");

-- 2. Tabla: proveedores
CREATE TABLE IF NOT EXISTS "proveedores" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "nombre" TEXT NOT NULL,
  "contacto" TEXT DEFAULT NULL,
  "telefono" TEXT DEFAULT NULL,
  "email" TEXT DEFAULT NULL
);
CREATE INDEX IF NOT EXISTS "idx_prov_user_id" ON "proveedores" ("user_id");

-- 3. Tabla: clientes
CREATE TABLE IF NOT EXISTS "clientes" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "nombre" TEXT NOT NULL,
  "tipo_documento" TEXT DEFAULT 'CI', -- enum
  "numero_documento" TEXT DEFAULT NULL,
  "telefono" TEXT DEFAULT NULL,
  "email" TEXT DEFAULT NULL,
  "direccion" TEXT DEFAULT NULL,
  "tipo_cliente" TEXT DEFAULT 'Natural', -- enum
  "limite_credito" NUMERIC DEFAULT 0.00,
  "activo" INTEGER DEFAULT 1,
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "usuarios" ("id") ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS "idx_client_user_id" ON "clientes" ("user_id");

-- 4. Tabla: productos
CREATE TABLE IF NOT EXISTS "productos" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "codigo" TEXT DEFAULT NULL,
  "codigo_barras" TEXT DEFAULT NULL,
  "user_id" INTEGER NOT NULL,
  "nombre" TEXT NOT NULL,
  "categoria" TEXT DEFAULT NULL,
  "stock" INTEGER NOT NULL,
  "precioCompraUSD" NUMERIC NOT NULL,
  "precioVentaUSD" NUMERIC NOT NULL,
  "gananciaUnitariaUSD" NUMERIC NOT NULL,
  "proveedor_id" INTEGER DEFAULT NULL,
  "precio_base_usd" NUMERIC DEFAULT NULL, 
  "tiene_iva" INTEGER DEFAULT 0,  
  "iva_porcentaje" NUMERIC DEFAULT NULL,
  FOREIGN KEY ("proveedor_id") REFERENCES "proveedores" ("id") ON DELETE SET NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS "idx_prod_code_user" ON "productos" ("codigo_barras", "user_id");
CREATE INDEX IF NOT EXISTS "idx_prod_user_id" ON "productos" ("user_id");
CREATE INDEX IF NOT EXISTS "idx_prod_prov_id" ON "productos" ("proveedor_id");
CREATE INDEX IF NOT EXISTS "idx_prod_nombre" ON "productos" ("nombre");

-- 5. Tabla: producto_proveedores
CREATE TABLE IF NOT EXISTS "producto_proveedores" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "producto_id" INTEGER NOT NULL,
  "proveedor_id" INTEGER NOT NULL,
  "ultimo_precio_costo" NUMERIC NOT NULL,
  FOREIGN KEY ("producto_id") REFERENCES "productos" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("proveedor_id") REFERENCES "proveedores" ("id") ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS "idx_prod_prov_unique" ON "producto_proveedores" ("producto_id", "proveedor_id");

-- 6. Tabla: ventas
CREATE TABLE IF NOT EXISTS "ventas" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "cliente_id" INTEGER DEFAULT NULL,
  "total_usd" NUMERIC NOT NULL,
  "tasa_ves" NUMERIC NOT NULL,
  "total_ves" NUMERIC NOT NULL,
  "estado_pago" TEXT DEFAULT 'Pagada', -- enum
  "metodo_pago" TEXT DEFAULT 'Efectivo', -- enum
  "notas" TEXT DEFAULT NULL,
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "usuarios" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("cliente_id") REFERENCES "clientes" ("id") ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS "idx_ventas_user_id" ON "ventas" ("user_id");
CREATE INDEX IF NOT EXISTS "idx_ventas_created_at" ON "ventas" ("created_at");

-- 7. Tabla: venta_items
CREATE TABLE IF NOT EXISTS "venta_items" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "venta_id" INTEGER NOT NULL,
  "producto_id" INTEGER DEFAULT NULL,
  "nombre_producto" TEXT NOT NULL,
  "cantidad" INTEGER NOT NULL,
  "precio_unitario_usd" NUMERIC NOT NULL,
  FOREIGN KEY ("venta_id") REFERENCES "ventas" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("producto_id") REFERENCES "productos" ("id") ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS "idx_vitems_venta_id" ON "venta_items" ("venta_id");

-- 8. Tabla: compras
CREATE TABLE IF NOT EXISTS "compras" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "proveedor_id" INTEGER NOT NULL,
  "nro_factura" TEXT DEFAULT NULL,
  "total" NUMERIC NOT NULL,
  "estado" TEXT DEFAULT 'Pagada', -- enum
  "fecha_emision" DATE NOT NULL,
  "fecha_vencimiento" DATE NOT NULL,
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "usuarios" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("proveedor_id") REFERENCES "proveedores" ("id") ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS "idx_compras_user" ON "compras" ("user_id");
CREATE INDEX IF NOT EXISTS "idx_compras_estado_fecha" ON "compras" ("estado", "fecha_vencimiento");

-- 9. Tabla: compra_items
CREATE TABLE IF NOT EXISTS "compra_items" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "compra_id" INTEGER NOT NULL,
  "producto_id" INTEGER NOT NULL,
  "cantidad" INTEGER NOT NULL,
  "precio_unitario" NUMERIC NOT NULL,
  FOREIGN KEY ("compra_id") REFERENCES "compras" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("producto_id") REFERENCES "productos" ("id") ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS "idx_citems_compra" ON "compra_items" ("compra_id");

-- 10. Tabla: movimientos
CREATE TABLE IF NOT EXISTS "movimientos" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "producto_id" INTEGER NOT NULL,
  "productoNombre" TEXT DEFAULT NULL,
  "tipo" TEXT NOT NULL, -- enum entrada/salida
  "motivo" TEXT DEFAULT NULL,
  "proveedor" TEXT DEFAULT NULL,
  "cantidad" INTEGER NOT NULL,
  "nota" TEXT DEFAULT NULL,
  "fecha" DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("producto_id") REFERENCES "productos" ("id") ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS "idx_mov_producto" ON "movimientos" ("producto_id");
CREATE INDEX IF NOT EXISTS "idx_mov_user" ON "movimientos" ("user_id");
CREATE INDEX IF NOT EXISTS "idx_mov_fecha" ON "movimientos" ("fecha");

-- 11. Tabla: api_keys
CREATE TABLE IF NOT EXISTS "api_keys" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "api_key" TEXT NOT NULL UNIQUE,
    "nombre" TEXT NOT NULL,
    "permisos" TEXT NULL, -- JSON stored as TEXT
    "activo" INTEGER DEFAULT 1,
    "expira" DATETIME NULL,
    "ultimo_uso" DATETIME NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS "idx_apikey_user" ON "api_keys" ("user_id");

-- 12. Tabla: audit_logs
CREATE TABLE IF NOT EXISTS "audit_logs" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "action" TEXT NOT NULL,
    "entity_type" TEXT NOT NULL,
    "entity_id" INTEGER NULL,
    "entity_name" TEXT NULL,
    "old_values" TEXT NULL, -- JSON
    "new_values" TEXT NULL, -- JSON
    "ip_address" TEXT NULL,
    "user_agent" TEXT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS "idx_audit_user" ON "audit_logs" ("user_id");

-- 13. Tabla: notificaciones
CREATE TABLE IF NOT EXISTS "notificaciones" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "tipo" TEXT NOT NULL, -- enum
    "titulo" TEXT NOT NULL,
    "mensaje" TEXT NOT NULL,
    "prioridad" TEXT DEFAULT 'media', -- enum
    "link" TEXT NULL,
    "icono" TEXT DEFAULT 'fa-bell',
    "leida" INTEGER DEFAULT 0,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS "idx_notif_user" ON "notificaciones" ("user_id");

-- 14. Tablas: Sucursales
CREATE TABLE IF NOT EXISTS "sucursales" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "nombre" TEXT NOT NULL,
    "codigo" TEXT NULL,
    "direccion" TEXT NULL,
    "telefono" TEXT NULL,
    "email" TEXT NULL,
    "es_principal" INTEGER DEFAULT 0,
    "activa" INTEGER DEFAULT 1,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "stock_sucursales" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "sucursal_id" INTEGER NOT NULL,
    "producto_id" INTEGER NOT NULL,
    "stock" INTEGER DEFAULT 0,
    "stock_minimo" INTEGER DEFAULT 5,
    "ubicacion" TEXT NULL,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE ("sucursal_id", "producto_id")
);

CREATE TABLE IF NOT EXISTS "transferencias" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "sucursal_origen_id" INTEGER NOT NULL,
    "sucursal_destino_id" INTEGER NOT NULL,
    "producto_id" INTEGER NOT NULL,
    "cantidad" INTEGER NOT NULL,
    "estado" TEXT DEFAULT 'Pendiente',
    "nota" TEXT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "completed_at" DATETIME NULL
);

-- 15. Tablas: Support Tickets (Legacy)
CREATE TABLE IF NOT EXISTS "password_resets" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "email" TEXT NOT NULL,
  "token" TEXT NOT NULL,
  "expires_at" DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS "tickets" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "subject" TEXT NOT NULL,
  "status" TEXT NOT NULL DEFAULT 'Abierto',
  "priority" TEXT NOT NULL DEFAULT 'Media',
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
  "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "usuarios" ("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "ticket_replies" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "ticket_id" INTEGER NOT NULL,
  "user_id" INTEGER NOT NULL,
  "message" TEXT NOT NULL,
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("ticket_id") REFERENCES "tickets" ("id") ON DELETE CASCADE
);

PRAGMA foreign_keys = ON;

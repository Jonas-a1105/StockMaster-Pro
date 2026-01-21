# 📦 StockMaster Pro - Gestión de Inventario & POS (SaaS & Desktop)

Un sistema completo de gestión de inventario y Punto de Venta (POS) diseñado con arquitectura **MVC**. Este proyecto es híbrido: funciona tanto como una plataforma **SaaS web** (PHP/MySQL) como una **aplicación de escritorio independiente** (Electron/SQLite).

---

## 🚀 Características Principales

### 💼 Gestión de Negocio (SaaS)
* **Modelo Freemium:** Sistema de planes (Free/Premium) con restricciones automáticas.
* **Periodo de Prueba (Trial):** 30 días de Premium automático para nuevos usuarios.
* **Aislamiento de Datos:** Arquitectura Multi-tenant; cada usuario accede solo a su información.
* **Panel de Administración:** Gestión de usuarios, activación de planes y soporte técnico.

### 📦 Gestión de Inventario
* **CRUD Pro:** Operaciones rápidas mediante Modales AJAX.
* **Cálculos Financieros:** Precios, márgenes y conversión automática a Moneda Local (VES).
* **Control de Stock:** Notificaciones visuales de stock bajo o agotado.
* **Proveedores:** Base de datos vinculada al historial de compras.

### 💰 Punto de Venta (POS)
* **Venta Rápida:** Buscador en tiempo real por nombre o SKU.
* **Facturación:** Generación de recibos imprimibles y descuento automático de stock.

### 📊 Dashboard y Reportes
* **KPIs y Gráficos:** Visualización de valor de inventario y ganancias (Chart.js).
* **Exportación:** Reportes en **PDF** y **Excel/CSV**.

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8.0+ (MVC), Slim/Core propio.
* **Escritorio:** Electron (Proceso principal en `main.js`).
* **Bases de Datos:** MySQL (Web) / SQLite (Escritorio).
* **Frontend:** HTML5, CSS3 (Glassmorphism), JavaScript (Vanilla + AJAX).
* **Librerías principales:** PHPMailer, Chart.js, jsPDF.

---

## ⚙️ Instalación y Ejecución

### 🌐 Modo Web (Servidor)
1. Clona el repositorio: `git clone https://github.com/Jonas-a1105/StockMaster-Pro.git`
2. Instala dependencias: `composer install`
3. Configura el archivo `.env` con tus credenciales de MySQL.

### 💻 Modo Escritorio (Desarrollo)
1. Instala dependencias de Node: `npm install`
2. Ejecuta la app: `npm start`
*En modo desarrollo, la app usará el PHP instalado en el PATH de tu sistema.*

---

## 🏗️ Construcción de la Versión de Escritorio (.exe)

Para generar el instalador independiente:
```bash
npm run dist
```
*Si tienes errores de permisos en PowerShell, usa:* `cmd /c "npm run dist"`

### ⚠️ Modo "Portable Offline" Real
Para que el `.exe` funcione sin PHP instalado en la PC destino:
1. Descarga **PHP for Windows** (VS16 x64 Non-Thread-Safe).
2. Extrae el contenido en `resources/bin/php/`.
3. Asegúrate de que `php.ini` tenga habilitadas: `pdo_sqlite`, `sqlite3`, `mbstring` y `gd`.
4. Ejecuta `npm run dist` nuevamente.

---

## 🛠️ Solución de Problemas Comunes

**1. Error de Scripts en PowerShell:**
Ejecuta `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass` antes de compilar.

**2. Base de Datos no encontrada:**
En la versión de escritorio, la base de datos se migra automáticamente a la carpeta de datos de usuario del sistema local (AppData) para persistencia.

---

Diseñado y Desarrollado con ❤️ por: **Jonas Mendoza** - Técnico en Informática & Desarrollador Full Stack

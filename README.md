
# 📦 Sistema de Gestión de Inventario & POS (SaaS)

Un sistema completo de gestión de inventario y Punto de Venta (POS) basado en la web, diseñado con arquitectura **MVC** y modelo de negocio **SaaS (Software as a Service)**. Incluye gestión de planes (Free/Premium), facturación, reportes financieros y herramientas administrativas.

---

## 🚀 Características Principales

### 💼 Gestión de Negocio (SaaS)
* **Modelo Freemium:** Sistema de planes con restricciones automáticas para usuarios Free.
* **Periodo de Prueba (Trial):** Los nuevos usuarios reciben 30 días de Premium automáticamente.
* **Downgrade Automático:** Al vencer el trial, el sistema limita el acceso a las funciones Free.
* **Panel de Administración:** Gestión de usuarios, activación manual de planes y monitoreo.
* **Aislamiento de Datos:** Arquitectura Multi-tenant donde cada usuario accede únicamente a su propia información.

### 📦 Gestión de Inventario
* **CRUD de Productos:** Creación y edición mediante Modales AJAX (sin recargas).
* **Cálculos Financieros en Vivo:** Cálculo automático de precios de venta, márgenes y conversión a Moneda Local (VES) según tasa del día.
* **Control de Stock:** Alertas automáticas (visuales y notificaciones) para stock bajo y agotado.
* **Gestión de Proveedores:** Base de datos de proveedores vinculada al historial de entradas.

### 💰 Punto de Venta (POS)
* **Interfaz de Venta Rápida:** Buscador en tiempo real por nombre o SKU.
* **Carrito de Compras:** Agrega, edita y elimina ítems antes de procesar.
* **Recibos:** Generación automática de recibos de venta imprimibles.
* **Descuento de Stock:** Sincronización inmediata con el inventario al completar la venta.

### 📊 Dashboard y Reportes
* **KPIs Financieros:** Visualización de Valor de Inventario, Costo Total y Ganancia Potencial.
* **Gráficos Interactivos:** Análisis de valor por categoría y distribución de stock (Chart.js).
* **Exportación:** Generación de reportes detallados en **PDF** y **CSV/Excel**.
* **Tasa de Cambio:** Integración con API para tasa del dólar en tiempo real + opción de tasa manual persistente.

### 🛡️ Seguridad y Soporte
* **Autenticación Robusta:** Login, Registro y Recuperación de Contraseña (vía Email con Token seguro).
* **Gestión de Equipos:** Los dueños de negocio pueden crear cuentas para empleados.
* **Sistema de Tickets:** Módulo de soporte técnico interno para comunicación Usuario-Admin.

---

## 🛠️ Tecnologías Utilizadas

* **Lenguaje:** PHP 8.0+ (Arquitectura MVC Estricta).
* **Base de Datos:** MySQL / MariaDB.
* **Frontend:** HTML5, CSS3 (Diseño Glassmorphism), JavaScript (Vanilla + AJAX).
* **Dependencias (Composer):**
    * `phpmailer/phpmailer`: Envío de correos transaccionales.
    * `stripe/stripe-php`: (Preparado para integración de pagos).
* **Librerías JS:**
    * `Chart.js`: Visualización de datos.
    * `jsPDF` & `AutoTable`: Generación de reportes PDF.

---

## ⚙️ Instalación y Configuración

### 1. Requisitos Previos
* Servidor Web (Apache/Nginx) o XAMPP/Laragon.
* PHP 8.0 o superior.
* Composer instalado.

### 2. Clonar e Instalar Dependencias
```bash
git clone [https://github.com/Jonas_1105/sistema-inventario.git](https://github.com/tu-usuario/sistema-inventario.git)
cd sistema-inventario
composer install

Diseñado y Desarrollado con ❤️ por: [Jonas Mendoza] Técnico en Informática & Desarrollador Full Stack

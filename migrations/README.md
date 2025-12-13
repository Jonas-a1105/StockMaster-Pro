# Migraciones de Base de Datos

Este directorio contiene el esquema completo de la base de datos para el sistema de inventario.

## ⚠️ IMPORTANTE: Hacer Respaldo Antes de Ejecutar

Antes de ejecutar cualquier cambio, haz un respaldo completo de tu base de datos:

```bash
mysqldump -u root sistema_inventario > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Archivo Principal

### `sistema_inventario_completo.sql`
Este es el **esquema unificado y actualizado (V2)** que contiene:
- Todas las tablas base (`usuarios`, `productos`, `ventas`...)
- Módulos Enterprise (`sucursales`, `api_keys`, `auditoria`, `notificaciones`)
- Actualizaciones recientes (`iva`, `username`, `tasa_dolar`)

Usa este archivo para:
1.  **Instalar el sistema desde cero.**
2.  **Restaurar la estructura completa.**

```bash
mysql -u root sistema_inventario < migrations/sistema_inventario_completo.sql
```

---
*Nota: Los archivos de migración antiguos han sido consolidados en este único archivo para facilitar el mantenimiento.*

# StockMaster Pro - Versión de Escritorio

Este proyecto ha sido configurado para funcionar como una aplicación de escritorio independiente (**StockMaster Pro**) usando **Electron** y **SQLite**.

## Requisitos Previos (Para Desarrollo)
- Node.js instalado.
- PHP instalado (o XAMPP) para modo desarrollo.

## Estructura
- `main.js`: Proceso principal de Electron.
- `package.json`: Configuración y scripts.
- `database/database.sqlite`: Base de datos local.
- `resources/bin/`: Carpeta para binarios portables (PHP).

## Cómo Ejecutar (Modo Desarrollo)
```bash
npm start
```
*En modo desarrollo, la app usará el PHP instalado en tu sistema (PATH).*

## Cómo Generar el Instalador (.exe)
```bash
npm run dist
```
El archivo de instalación se generará en la carpeta `dist/`.

## ⚠️ IMPORTANTE: Para Modo "Portable Offline" Real
Para que la aplicación generada (`.exe`) funcione en **cualquier computadora** sin necesidad de tener PHP instalado, debes incluir los binarios de PHP manualmente antes de construir:

1. Descarga **PHP for Windows** (Versión VS16 x64 Non-Thread-Safe recomendada).
2. Extrae el contenido en la carpeta: `resources/bin/php/`.
   - Debería quedar así: `c:\xampp\htdocs\inventario_oop\resources\bin\php\php.exe`
3. Asegúrate de que el `php.ini` en esa carpeta tenga habilitadas las extensiones:
   - `extension=pdo_sqlite`
   - `extension=sqlite3`
   - `extension=mbstring` (recomendado)
   - `extension=gd` (para imágenes)

Una vez colocado el PHP allí, ejecuta `npm run dist` de nuevo. El instalador incluirá PHP dentro del paquete y funcionará en cualquier PC.

## Solución de Problemas

### Error: "La ejecución de scripts está deshabilitada (UnauthorizedAccess)"
Si al ejecutar `npm run dist` recibes un error sobre **Execution Policies** en PowerShell, significa que Windows está bloqueando la ejecución de scripts.

**Solución rápida:**
Usa el Símbolo del Sistema (CMD) o ejecuta el siguiente comando especial que "envuelve" la instrucción para saltarse esa restricción:

```bash
cmd /c "npm run dist"
```

Alternativamente, si tienes permisos de Administrador, puedes permitir scripts temporalmente:
```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
npm run dist
```

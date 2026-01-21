const { app, BrowserWindow, dialog } = require('electron');
const { autoUpdater } = require('electron-updater');
const path = require('path');
const { spawn, execSync } = require('child_process');
const log = require('electron-log');
const fs = require('fs');

// Logging Setup
autoUpdater.logger = log;
autoUpdater.logger.transports.file.level = 'info';

let mainWindow;
let phpServer;
const PHP_PORT = 8000;

// --- AUTO UPDATE LOGIC ---
function setupAutoUpdater() {
    log.info(`App starting... v${app.getVersion()}`);

    // Check for updates immediately
    if (app.isPackaged) {
        autoUpdater.checkForUpdatesAndNotify();
    }

    autoUpdater.on('checking-for-update', () => {
        log.info('Checking for update...');
    });

    autoUpdater.on('update-available', (info) => {
        log.info('Update available.', info);
        if (mainWindow) {
            // Send event to renderer instead of native dialog
            mainWindow.webContents.send('update-available', info);
        }
    });

    autoUpdater.on('update-not-available', (info) => {
        log.info('Update not available.', info);
    });

    autoUpdater.on('error', (err) => {
        log.error('Error in auto-updater. ' + err);
        if (mainWindow) {
            mainWindow.webContents.send('update-error', err.toString());
        }
    });

    autoUpdater.on('download-progress', (progressObj) => {
        let log_message = "Download speed: " + progressObj.bytesPerSecond;
        log_message = log_message + ' - Downloaded ' + progressObj.percent + '%';
        log_message = log_message + ' (' + progressObj.transferred + "/" + progressObj.total + ')';
        log.info(log_message);
        if (mainWindow) {
            mainWindow.setProgressBar(progressObj.percent / 100);
            // Send progress to renderer
            mainWindow.webContents.send('download-progress', progressObj);
        }
    });

    autoUpdater.on('update-downloaded', (info) => {
        log.info('Update downloaded', info);
        if (mainWindow) {
            mainWindow.setProgressBar(-1);
            // Send ready event to renderer
            mainWindow.webContents.send('update-downloaded', info);
        }
    });
}
// Listen for restart command from renderer
const { ipcMain } = require('electron');
ipcMain.on('restart_app', () => {
    app.isQuiting = true;
    autoUpdater.quitAndInstall();
});

// --- PHP SERVER LOGIC ---
function startPhpServer() {
    let phpPath = 'php'; // Default system PHP for dev
    let documentRoot = path.join(__dirname, 'public');
    let dbSourcePath = path.join(__dirname, 'database', 'database.sqlite'); // Fuente original

    if (app.isPackaged) {
        // In production: resources/bin/php/php.exe
        phpPath = path.join(process.resourcesPath, 'bin', 'php', 'php.exe');
        documentRoot = path.join(process.resourcesPath, 'public');
        dbSourcePath = path.join(process.resourcesPath, 'database', 'database.sqlite');
    }

    // --- MIGRACIÓN A CARPETA SEGURA (APPDATA) ---
    // UserData: C:\Users\Usuario\AppData\Roaming\stockmaster-pro-desktop
    const safeUserDataPath = app.getPath('userData');
    const safeDbDir = path.join(safeUserDataPath, 'database');
    const safeDbPath = path.join(safeDbDir, 'database.sqlite');

    log.info(`Checking Database... Safe Path: ${safeDbPath}`);

    // Asegurar que existe la carpeta en AppData
    if (!fs.existsSync(safeDbDir)) {
        fs.mkdirSync(safeDbDir, { recursive: true });
    }

    // Si NO existe la DB en AppData, copiarla desde la instalación
    if (!fs.existsSync(safeDbPath)) {
        log.info('Database not found in AppData. Migrating from resources...');
        try {
            if (fs.existsSync(dbSourcePath)) {
                fs.copyFileSync(dbSourcePath, safeDbPath);
                log.info('Database migrated successfully!');
            } else {
                log.error('Original database not found in resources! Creating empty?');
                // Aquí podrías crear una vacía si fuera necesario
            }
        } catch (err) {
            log.error('Error migrating database: ' + err);
        }
    } else {
        log.info('Database found in AppData. Using existing file.');
    }

    log.info(`Starting PHP Server... Path: ${phpPath}, Root: ${documentRoot}, DB: ${safeDbPath}`);

    // Configuración de Entorno para PHP (SQLite)
    const env = Object.create(process.env);
    env.DB_CONNECTION = 'sqlite';
    env.DB_DATABASE = safeDbPath; // <--- USAR LA RUTA SEGURA
    env.APP_VERSION_ELECTRON = app.getVersion(); // <--- INYECTAR VERSION OFICIAL
    // Forzar modo producción si está empaquetado para evitar errores de visualización de debug
    if (app.isPackaged) {
        env.APP_ENV = 'production';
        env.DISPLAY_ERRORS = '0';
    }

    phpServer = spawn(phpPath, ['-S', `127.0.0.1:${PHP_PORT}`, '-t', documentRoot], {
        cwd: app.isPackaged ? process.resourcesPath : __dirname,
        env: env // Inyectar variables de entorno
    });

    phpServer.stdout.on('data', (data) => {
        log.info(`PHP Out: ${data}`);
    });

    phpServer.stderr.on('data', (data) => {
        log.error(`PHP Err: ${data}`);
    });

    phpServer.on('close', (code) => {
        log.info(`PHP Server exited with code ${code}`);
    });
}

// --- WINDOW CREATION ---
function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1000,
        height: 800,
        minWidth: 1024,
        minHeight: 768,
        title: "StockMaster Pro",
        icon: path.join(__dirname, 'public/img/StockMasterPro.ico'),
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false // Allowing node in renderer for verify features if needed
        }
    });

    // Remove default menu
    mainWindow.setMenuBarVisibility(false);

    // Load PHP Server URL
    mainWindow.loadURL(`http://127.0.0.1:${PHP_PORT}`);

    // setupAutoUpdater(); // Call updater setup

    // --- IPC LISTENERS ---
    const { ipcMain } = require('electron');
    ipcMain.on('confirm-exit', () => {
        app.isQuiting = true;
        app.quit();
    });

    let closeAttempts = 0;
    mainWindow.on('close', (e) => {
        if (app.isQuiting) return;

        // Si el usuario intenta cerrar dos veces, forzar el cierre
        // (Útil si la web ha crasheado y no responde al IPC)
        closeAttempts++;
        if (closeAttempts > 1) {
            app.isQuiting = true;
            app.quit();
            return;
        }

        // Restablecer intentos si no cierra después de 3 segundos
        setTimeout(() => { closeAttempts = 0; }, 3000);

        // Prevenir cierre y mostrar modal HTML
        e.preventDefault();
        mainWindow.webContents.send('show-exit-confirm');
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

// --- APP LIFECYCLE ---
const gotTheLock = app.requestSingleInstanceLock();

if (!gotTheLock) {
    app.quit();
} else {
    app.on('second-instance', (event, commandLine, workingDirectory) => {
        if (mainWindow) {
            if (mainWindow.isMinimized()) mainWindow.restore();
            mainWindow.focus();
        }
    });

    app.whenReady().then(() => {
        startPhpServer();
        // Give PHP a moment to start?
        setTimeout(createWindow, 500);
        setTimeout(setupAutoUpdater, 3000); // Check for updates after launch
    });
}

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('will-quit', () => {
    app.isQuiting = true;
    if (phpServer) {
        if (process.platform === 'win32') {
            try {
                execSync(`taskkill /pid ${phpServer.pid} /T /F`);
            } catch (e) { /* ignore */ }
        } else {
            phpServer.kill();
        }
    }
});

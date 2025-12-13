const { app, BrowserWindow, dialog } = require('electron');
const { autoUpdater } = require('electron-updater');

// ... (rest of imports)

// --- AUTO UPDATE LOGIC ---
function setupAutoUpdater() {
    autoUpdater.checkForUpdatesAndNotify();

    autoUpdater.on('update-available', () => {
        console.log('Update available.');
    });

    autoUpdater.on('update-downloaded', () => {
        dialog.showMessageBox(mainWindow, {
            type: 'info',
            buttons: ['Reiniciar e Instalar', 'Más tarde'],
            title: 'Actualización Disponible',
            message: 'Una nueva versión se ha descargado. ¿Deseas reiniciar ahora para instalarla?'
        }).then(result => {
            if (result.response === 0) {
                autoUpdater.quitAndInstall();
            }
        });
    });
}

// ... (createWindow function)

app.whenReady().then(async () => {
    try {
        await startPHPServer();
        createWindow();
        setupAutoUpdater(); // Initialize Updater
    } catch (e) {
        console.error('Failed to start:', e);
    }
});
const path = require('path');
const { spawn } = require('child_process');
const fs = require('fs');

let mainWindow;
let phpServer;
const PHP_PORT = 8000;
const HOST = '127.0.0.1';

// Determine Environment
const isDev = !app.isPackaged;
const appPath = app.getAppPath(); // In production: .../resources/app.asar

// Logic to find PHP binary
let phpBin;
if (isDev) {
    // Development: Use system PHP (XAMPP or Global)
    phpBin = 'php';
    console.log('Development Mode: Using system PHP');
} else {
    // Production: Use bundled PHP in /bin
    // resources/bin/php/php.exe
    const basePath = path.dirname(appPath); // .../resources
    phpBin = path.join(basePath, 'bin', 'php', 'php.exe');
    console.log('Production Mode: Using bundled PHP at', phpBin);
}

function startPHPServer() {
    return new Promise((resolve, reject) => {
        // Root dir for PHP server
        // In dev: project root
        // In prod: .../resources/ (we unpacked 'public', 'src', etc here via extraResources)
        // OR we map it to just inside app.asar if we included it there?
        // package.json says we copy public/src to extraResources root? 
        // No, usually "to": "bin" means /resources/bin. "to": "public" means /resources/public.

        let docRoot;
        let dbPath;

        if (isDev) {
            // Development
            docRoot = path.join(__dirname, 'public');
            dbPath = path.join(__dirname, 'database', 'database.sqlite');
        } else {
            // Production
            // 1. Files are in resources/app.asar.unpacked (physically present for PHP)
            // __dirname in prod is inside app.asar. We need to get out.
            // process.resourcesPath = .../resources
            docRoot = path.join(process.resourcesPath, 'app.asar.unpacked', 'public');

            // 2. Database Handling (Move to UserData to be writable and hidden)
            const userDataPath = app.getPath('userData'); // C:\Users\User\AppData\Roaming\sistema-inventario
            const targetDbPath = path.join(userDataPath, 'database.sqlite');

            // Check if DB exists in UserData, if not, copy from resources
            if (!fs.existsSync(targetDbPath)) {
                // Template DB in unpacked resources
                const sourceDbPath = path.join(process.resourcesPath, 'app.asar.unpacked', 'database', 'database.sqlite');
                console.log('Deploying Database to:', targetDbPath);
                try {
                    fs.copyFileSync(sourceDbPath, targetDbPath);
                } catch (err) {
                    console.error('Error copying DB:', err);
                }
            }
            dbPath = targetDbPath;
        }

        console.log('Starting PHP Server...');
        // console.log('Binary:', phpBin);
        // console.log('DocRoot:', docRoot);

        const env = Object.create(process.env);
        env.DB_CONNECTION = 'sqlite';
        env.DB_DATABASE = dbPath;

        // console.log('DB Path:', dbPath);

        phpServer = spawn(phpBin, ['-S', `${HOST}:${PHP_PORT}`, '-t', docRoot], {
            env: env,
            cwd: isDev ? __dirname : path.join(process.resourcesPath, 'app.asar.unpacked'),
            windowsHide: true // Hide the PHP console window on Windows
        });

        // Suppress PHP logs in the console (uncomment for debugging)
        // phpServer.stdout.on('data', (data) => console.log(`PHP: ${data}`));
        // phpServer.stderr.on('data', (data) => console.error(`PHP Error: ${data}`));

        phpServer.on('close', (code) => {
            console.log(`PHP Server exited with code ${code}`);
        });

        // Give it a moment to verify it started
        setTimeout(resolve, 1000);
    });
}

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        minWidth: 400,
        minHeight: 600,
        icon: path.join(__dirname, 'public/img/favicon.ico'),
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true
        },
        autoHideMenuBar: true,
        backgroundColor: '#eef2f6', // Match body bg
        show: false // Wait until ready to show to avoid white flash
    });

    // mainWindow.webContents.openDevTools(); // Uncomment for debug

    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
    });

    mainWindow.loadURL(`http://${HOST}:${PHP_PORT}`);

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

app.whenReady().then(async () => {
    try {
        await startPHPServer();
        createWindow();
        setupAutoUpdater(); // Initialize Updater
    } catch (e) {
        console.error('Failed to start:', e);
    }
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('will-quit', () => {
    if (phpServer) {
        phpServer.kill();
    }
});

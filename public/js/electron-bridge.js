/**
 * ELECTRON BRIDGE
 * Maneja la integración entre la web y el proceso principal de Electron.
 * Específicamente: Modal de Salida Personalizado.
 */

if (typeof require !== 'undefined') {
    const { ipcRenderer } = require('electron');

    // Check if overlay already exists to prevent duplicates
    if (!document.getElementById('electron-exit-overlay')) {

        document.addEventListener('DOMContentLoaded', () => {
            // Double check inside DOMContentLoaded just in case
            if (document.getElementById('electron-exit-overlay')) return;

            // 1. Inyectar Estilos y Modal al Body
            const modalHtml = `
            <style>
                #electron-exit-overlay {
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(8px);
                    z-index: 999999; display: flex; align-items: center; justify-content: center;
                    opacity: 0; transition: opacity 0.2s ease-in-out; pointer-events: none;
                    visibility: hidden; /* Ensure it doesn't block clicks when opacity is 0 */
                }
                #electron-exit-overlay.active {
                    opacity: 1; pointer-events: auto; visibility: visible;
                }
                #electron-exit-card {
                    background: white; width: 90%; max-width: 400px; padding: 24px;
                    border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                    text-align: center; transform: scale(0.95); transition: transform 0.2s ease-in-out;
                }
                #electron-exit-overlay.active #electron-exit-card {
                    transform: scale(1);
                }
                /* Dark Mode Support via class on body */
                .dark #electron-exit-card { background: #1e293b; color: white; }
                .dark #electron-exit-card h3 { color: white; }
                .dark #electron-exit-card p { color: #94a3b8; }
            </style>
            <div id="electron-exit-overlay">
                <div id="electron-exit-card">
                    <div style="width: 64px; height: 64px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                        <svg style="width: 32px; height: 32px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px; color: #1e293b;">¿Salir de la Aplicación?</h3>
                    <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 24px;">
                        ¿Estás seguro de que quieres cerrar StockMaster Pro?
                    </p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <button id="btn-cancel-exit" style="padding: 10px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                            Cancelar
                        </button>
                        <button id="btn-confirm-exit" style="padding: 10px; background: #dc2626; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3); transition: background 0.2s;">
                            Salir
                        </button>
                    </div>
                </div>
            </div>
            `;

            const div = document.createElement('div');
            div.innerHTML = modalHtml;
            document.body.appendChild(div);

            // 2. Elementos
            const overlay = document.getElementById('electron-exit-overlay');
            const btnCancel = document.getElementById('btn-cancel-exit');
            const btnConfirm = document.getElementById('btn-confirm-exit');

            function closeModal() {
                overlay.classList.remove('active');
            }

            function openModal() {
                overlay.classList.add('active');
            }

            // 3. Listeners del Renderizado
            btnCancel.addEventListener('click', closeModal);

            // Cerrar si se hace click fuera (en el overlay)
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal();
            });

            btnConfirm.addEventListener('click', () => {
                // Enviar confirmación al Main Process
                ipcRenderer.send('confirm-exit');
            });

            // 4. Listener IPC (Desde Main.js)
            // Remove previous listeners to avoid duplicates if re-run
            ipcRenderer.removeAllListeners('show-exit-confirm');
            ipcRenderer.on('show-exit-confirm', () => {
                openModal();
            });
            // 1B. Inyectar Modal de Actualización (Update Modal)
            const updateModalHtml = `
            <style>
                #electron-update-overlay {
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(8px);
                    z-index: 9999999; display: flex; align-items: center; justify-content: center;
                    opacity: 0; transition: opacity 0.3s ease-in-out; pointer-events: none;
                    visibility: hidden;
                }
                #electron-update-overlay.active {
                    opacity: 1; pointer-events: auto; visibility: visible;
                }
                #electron-update-card {
                    background: white; width: 90%; max-width: 420px; padding: 24px;
                    border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                    text-align: center; transform: scale(0.95); transition: transform 0.2s ease-in-out;
                    position: relative; overflow: hidden;
                }
                #electron-update-overlay.active #electron-update-card { transform: scale(1); }
                
                /* Progress Bar */
                .update-progress-container { width: 100%; height: 8px; background: #e2e8f0; border-radius: 99px; margin: 20px 0 10px 0; overflow: hidden; position: relative; }
                .update-progress-bar { height: 100%; background: #10b981; width: 0%; transition: width 0.2s linear; border-radius: 99px; }
                
                /* Typography */
                .update-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
                .update-msg { font-size: 0.875rem; color: #64748b; margin-bottom: 4px; }
                .update-stats { font-size: 0.75rem; font-weight: 600; color: #475569; display: flex; justify-content: space-between; margin-bottom: 24px; }
                
                /* Button */
                .update-btn {
                    padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 12px;
                    font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
                    transition: all 0.2s; width: 100%; display: none; /* Hidden until ready */
                }
                .update-btn:hover { background: #059669; }
                
                /* Dark Mode */
                .dark #electron-update-card { background: #1e293b; border-color: #334155; }
                .dark .update-title { color: white; }
                .dark .update-msg { color: #94a3b8; }
                .dark .update-progress-container { background: #334155; }
            </style>
            
            <div id="electron-update-overlay">
                <div id="electron-update-card">
                    <!-- Icon -->
                    <div style="width: 56px; height: 56px; background: #d1fae5; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: #10b981;">
                        <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    
                    <h3 class="update-title">Actualización Disponible</h3>
                    <p class="update-msg" id="update-status-text">Descargando nueva versión...</p>
                    
                    <!-- Progress Section -->
                    <div id="update-progress-section">
                        <div class="update-progress-container">
                            <div class="update-progress-bar" id="update-bar"></div>
                        </div>
                        <div class="update-stats">
                            <span id="update-speed">Calculando...</span>
                            <span id="update-percent">0%</span>
                        </div>
                    </div>
                    
                    <button id="btn-restart-update" class="update-btn">
                        Reiniciar e Instalar
                    </button>
                </div>
            </div>
            `;

            // Append Update Modal
            const divUpdate = document.createElement('div');
            divUpdate.innerHTML = updateModalHtml;
            document.body.appendChild(divUpdate);

            // --- MANEJO UI UPDATE ---
            const updateOverlay = document.getElementById('electron-update-overlay');
            const updateBar = document.getElementById('update-bar');
            const updateSpeed = document.getElementById('update-speed');
            const updatePercent = document.getElementById('update-percent');
            const updateStatus = document.getElementById('update-status-text');
            const btnRestart = document.getElementById('btn-restart-update');
            const progressSection = document.getElementById('update-progress-section');

            function showUpdateModal() {
                updateOverlay.classList.add('active');
            }

            // Helpers
            function formatBytes(bytes, decimals = 2) {
                if (!+bytes) return '0 B';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
            }

            // IPC: Update Found
            ipcRenderer.on('update-available', (event, info) => {
                showUpdateModal();
                updateStatus.textContent = `Nueva versión ${info.version} encontrada.`;
            });

            // IPC: Progress
            ipcRenderer.on('download-progress', (event, progressObj) => {
                showUpdateModal(); // Ensure visible
                const percent = Math.round(progressObj.percent);
                updateBar.style.width = `${percent}%`;
                updatePercent.textContent = `${percent}%`;

                // Speed & Size
                const speed = formatBytes(progressObj.bytesPerSecond) + '/s';
                const transferred = formatBytes(progressObj.transferred);
                const total = formatBytes(progressObj.total);

                updateSpeed.textContent = `${speed} (${transferred} de ${total})`;
                updateStatus.textContent = "Descargando actualización...";
            });

            // IPC: Ready
            ipcRenderer.on('update-downloaded', (event, info) => {
                showUpdateModal();
                updateBar.style.width = '100%';
                updatePercent.textContent = '100%';
                updateStatus.textContent = "✅ Descarga completada.";
                updateSpeed.textContent = "Listo para instalar";

                // Hide progress, show button
                setTimeout(() => {
                    progressSection.style.display = 'none';
                    btnRestart.style.display = 'block';
                    // Optional: bounce animation
                    btnRestart.classList.add('animate-bounce');
                }, 500);
            });

            // IPC: Error
            ipcRenderer.on('update-error', (event, err) => {
                console.error("Update Error:", err);
                // Opcional: Mostrar error en el modal o cerrarlo
                // updateStatus.textContent = "Error en la descarga.";
            });

            btnRestart.addEventListener('click', () => {
                ipcRenderer.send('restart_app');
            });
        });
    }
}

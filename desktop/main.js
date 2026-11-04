const { app, BrowserWindow, dialog } = require('electron');
const path = require('path');
const fs = require('fs');
const http = require('http');
const { spawn, execSync } = require('child_process');

// Log para diagnóstico
function getLogFile() {
  try {
    const dir = app.getPath('userData');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    return path.join(dir, 'app.log');
  } catch (e) {
    return path.join(process.cwd(), 'app.log');
  }
}

function logMsg(...args) {
  const line = `[${new Date().toISOString()}] ${args.join(' ')}\n`;
  try {
    fs.appendFileSync(getLogFile(), line);
  } catch (e) {}
  console.log(...args);
}

process.on('uncaughtException', (err) => {
  logMsg(`[UNCAUGHT EXCEPTION]: ${err.stack || err}`);
});

process.on('unhandledRejection', (reason, promise) => {
  logMsg(`[UNHANDLED REJECTION]: ${reason ? (reason.stack || reason) : 'Unknown reason'}`);
});

logMsg('=== AKY Lanches Desktop Iniciado ===');
let splashWindow = null;
let phpProcess = null;

const PORT = 8765;
const APP_URL = `http://127.0.0.1:${PORT}`;

// Evita múltiplas instâncias
const gotTheLock = app.requestSingleInstanceLock();
if (!gotTheLock) {
  app.quit();
} else {
  app.on('second-instance', () => {
    if (mainWindow) {
      if (mainWindow.isMinimized()) mainWindow.restore();
      mainWindow.focus();
    }
  });
}

function resolvePaths() {
  const isPackaged = app.isPackaged;
  let phpPath = '';
  let laravelPath = '';

  if (isPackaged) {
    phpPath = path.join(process.resourcesPath, 'php', 'php.exe');
    laravelPath = path.join(process.resourcesPath, 'laravel');
  } else {
    phpPath = path.join(__dirname, 'resources', 'php', 'php.exe');
    if (!fs.existsSync(phpPath)) {
      phpPath = 'C:\\PHP\\php.exe';
    }
    laravelPath = path.resolve(__dirname, '..', 'laravel');
  }

  return { phpPath, laravelPath };
}

function startPhpServer(phpPath, laravelPath) {
  return new Promise((resolve, reject) => {
    logMsg(`[Electron] Iniciando PHP: ${phpPath}`);
    logMsg(`[Electron] CWD Laravel: ${laravelPath}`);

    if (!fs.existsSync(phpPath)) {
      logMsg(`[ERRO] Executável PHP não encontrado em: ${phpPath}`);
      return reject(new Error(`Executável PHP não encontrado em: ${phpPath}`));
    }
    if (!fs.existsSync(laravelPath)) {
      logMsg(`[ERRO] Diretório do Laravel não encontrado em: ${laravelPath}`);
      return reject(new Error(`Diretório do Laravel não encontrado em: ${laravelPath}`));
    }

    const phpDir = path.dirname(phpPath);
    const envPath = `${phpDir};${process.env.PATH || ''}`;

    const phpArgs = [
      '-d', 'opcache.enable=0',
      '-d', 'opcache.enable_cli=0',
      'artisan', 'serve',
      '--host=127.0.0.1',
      `--port=${PORT}`,
      '--no-reload'
    ];

    phpProcess = spawn(phpPath, phpArgs, {
      cwd: laravelPath,
      windowsHide: true,
      env: {
        ...process.env,
        PATH: envPath,
        PHP_BINARY: phpPath,
        APP_PORT: String(PORT)
      }
    });

    phpProcess.stdout.on('data', (data) => {
      logMsg(`[PHP stdout]: ${data.toString()}`);
    });

    phpProcess.stderr.on('data', (data) => {
      logMsg(`[PHP stderr]: ${data.toString()}`);
    });

    phpProcess.on('error', (err) => {
      logMsg('[PHP erro]:', err);
      reject(err);
    });

    phpProcess.on('exit', (code, signal) => {
      logMsg(`[PHP finalizado] Código: ${code}, Sinal: ${signal}`);
    });

    // Aguarda o servidor responder
    waitForServer(APP_URL, 30000)
      .then(() => {
        // Dispara espelhamento SQLite em background sem travar o app
        try {
          const backupProc = spawn(phpPath, [
            '-d', 'opcache.enable=0',
            '-d', 'opcache.enable_cli=0',
            'artisan', 'backup:sqlite'
          ], {
            cwd: laravelPath,
            windowsHide: true
          });
          backupProc.stdout.on('data', (d) => logMsg(`[SQLite Backup]: ${d.toString().trim()}`));
        } catch (e) {
          logMsg('[SQLite Backup aviso]:', e.message);
        }

        // Sincronizacao bidirecional com a nuvem Hostinger
        function triggerCloudSync() {
          try {
            logMsg('[Cloud Sync] Sincronizando catalogo com Hostinger...');
            const syncProc = spawn(phpPath, [
              '-d', 'opcache.enable=0',
              '-d', 'opcache.enable_cli=0',
              'artisan', 'cloud:sync'
            ], {
              cwd: laravelPath,
              windowsHide: true
            });
            syncProc.stdout.on('data', (d) => logMsg(`[Cloud Sync]: ${d.toString().trim()}`));
            syncProc.stderr.on('data', (d) => logMsg(`[Cloud Sync erro]: ${d.toString().trim()}`));
          } catch (e) {
            logMsg('[Cloud Sync aviso]:', e.message);
          }
        }

        // Executa na subida e a cada 5 minutos
        setTimeout(triggerCloudSync, 2000);
        setInterval(triggerCloudSync, 5 * 60 * 1000);

        resolve(true);
      })
      .catch(reject);
  });
}

function waitForServer(url, timeoutMs) {
  const startTime = Date.now();
  return new Promise((resolve, reject) => {
    const check = () => {
      http.get(url, (res) => {
        if (res.statusCode >= 200 && res.statusCode < 500) {
          resolve(true);
        } else {
          retry();
        }
      }).on('error', () => {
        retry();
      });
    };

    const retry = () => {
      if (Date.now() - startTime > timeoutMs) {
        reject(new Error(`Tempo esgotado aguardando servidor PHP em ${url}`));
      } else {
        setTimeout(check, 400);
      }
    };

    check();
  });
}

function getAppIcon() {
  const iconInAssets = path.join(__dirname, 'assets', 'icon.ico');
  if (fs.existsSync(iconInAssets)) return iconInAssets;
  if (process.resourcesPath) {
    const iconInResources = path.join(process.resourcesPath, 'assets', 'icon.ico');
    if (fs.existsSync(iconInResources)) return iconInResources;
  }
  const rootIcon = path.resolve(__dirname, '..', 'Logo-de-Hambúrguer-AKY-Lanches.ico');
  if (fs.existsSync(rootIcon)) return rootIcon;
  return null;
}

function createSplashWindow() {
  const icon = getAppIcon();
  splashWindow = new BrowserWindow({
    width: 480,
    height: 360,
    frame: false,
    resizable: false,
    center: true,
    alwaysOnTop: true,
    backgroundColor: '#b91c1c',
    icon: icon || undefined,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true
    }
  });

  splashWindow.loadFile(path.join(__dirname, 'splash.html'));
  logMsg('[Electron] Splash window exibida');
}

function createMainWindow() {
  logMsg('[Electron] Criando mainWindow...');
  const icon = getAppIcon();
  mainWindow = new BrowserWindow({
    width: 1366,
    height: 768,
    minWidth: 1024,
    minHeight: 600,
    title: 'AKY Lanches - Atendimento',
    show: false,
    autoHideMenuBar: true,
    icon: icon || undefined,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      nodeIntegration: false,
      contextIsolation: true
    }
  });

  mainWindow.loadURL(APP_URL);

  mainWindow.webContents.on('did-finish-load', () => {
    logMsg('[Electron] MainWindow carregou com sucesso!');
    if (splashWindow && !splashWindow.isDestroyed()) {
      splashWindow.close();
      splashWindow = null;
    }
    mainWindow.maximize();
    mainWindow.show();
  });

  mainWindow.webContents.on('did-fail-load', (e, code, desc) => {
    logMsg(`[Electron] MainWindow falhou ao carregar: ${code} - ${desc}`);
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

function killPhpProcess() {
  if (phpProcess && !phpProcess.killed) {
    try {
      if (process.platform === 'win32') {
        execSync(`taskkill /pid ${phpProcess.pid} /T /F`);
      } else {
        phpProcess.kill('SIGTERM');
      }
    } catch (e) {
      // Ignora se o processo já tiver sido fechado
    }
    phpProcess = null;
  }
}

app.whenReady().then(async () => {
  logMsg('[Electron] app.whenReady disparado');
  createSplashWindow();

  try {
    const { phpPath, laravelPath } = resolvePaths();
    logMsg(`[Electron] Caminhos resolvidos: PHP=${phpPath}, Laravel=${laravelPath}`);
    await startPhpServer(phpPath, laravelPath);
    logMsg('[Electron] Servidor PHP pronto! Criando janela principal...');
    createMainWindow();
  } catch (error) {
    logMsg(`[ERRO FATAL]: ${error.message}`);
    if (splashWindow && !splashWindow.isDestroyed()) {
      splashWindow.close();
    }
    dialog.showErrorBox('Erro ao Iniciar Sistema', `Não foi possível iniciar o servidor local:\n\n${error.message}`);
    killPhpProcess();
    app.quit();
  }

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createMainWindow();
    }
  });
});

app.on('window-all-closed', () => {
  killPhpProcess();
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('before-quit', () => {
  killPhpProcess();
});

app.on('will-quit', () => {
  killPhpProcess();
});


const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const PosDatabase = require('./src/database');
const SyncEngine = require('./src/syncEngine');

let mainWindow = null;
let db = null;
let syncEngine = null;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1366,
    height: 850,
    minWidth: 1024,
    minHeight: 700,
    title: 'AKY Lanches • Terminal de Atendimento & PDV Offline-First',
    icon: path.join(__dirname, '..', 'laravel', 'public', 'images', 'aky_lanches_icon.svg'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      nodeIntegration: false,
      contextIsolation: true,
      sandbox: false
    }
  });

  mainWindow.loadFile(path.join(__dirname, 'public', 'index.html'));

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(() => {
  // Inicializa banco de dados SQLite local no diretório do usuário ou data local
  const userDataPath = app.getPath('userData');
  const dbPath = path.join(userDataPath, 'aky_pos_local.sqlite');
  db = new PosDatabase(dbPath);

  // Inicializa o SyncEngine
  syncEngine = new SyncEngine(db, {
    cloudApiUrl: 'http://127.0.0.1:8000/api',
    pollIntervalMs: 6000,
    onStatusChange: (status) => {
      if (mainWindow && !mainWindow.isDestroyed()) {
        mainWindow.webContents.send('pos:sync-status', status);
      }
    },
    onOrdersChange: () => {
      if (mainWindow && !mainWindow.isDestroyed()) {
        mainWindow.webContents.send('pos:orders-updated');
      }
    },
    onCatalogChange: (catalogData) => {
      if (mainWindow && !mainWindow.isDestroyed()) {
        mainWindow.webContents.send('pos:catalog-updated', catalogData);
      }
    }
  });

  // Registra IPC Handlers
  ipcMain.handle('pos:create-order', async (event, orderData, itemsData) => {
    return db.createOrder(orderData, itemsData);
  });

  ipcMain.handle('pos:get-orders', async (event, limit) => {
    return db.getOrders(limit || 100);
  });

  ipcMain.handle('pos:update-order-status', async (event, uuid, status) => {
    return db.updateOrderStatus(uuid, status);
  });

  ipcMain.handle('pos:get-catalog', async () => {
    const local = db.getLocalCatalog();
    return local ? local.data : null;
  });

  ipcMain.handle('pos:get-sync-status', async () => {
    return syncEngine.getStatus();
  });

  ipcMain.handle('pos:trigger-sync', async () => {
    return syncEngine.triggerNow();
  });

  ipcMain.handle('pos:set-cloud-url', async (event, url) => {
    syncEngine.setCloudUrl(url);
    return syncEngine.getStatus();
  });

  createWindow();
  syncEngine.start();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (syncEngine) {
    syncEngine.stop();
  }
  if (process.platform !== 'darwin') {
    app.quit();
  }
});


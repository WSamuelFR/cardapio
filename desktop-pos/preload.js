const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('akyPos', {
  createOrder: (orderData, itemsData) => ipcRenderer.invoke('pos:create-order', orderData, itemsData),
  getOrders: (limit) => ipcRenderer.invoke('pos:get-orders', limit),
  updateOrderStatus: (uuid, status) => ipcRenderer.invoke('pos:update-order-status', uuid, status),
  getCatalog: () => ipcRenderer.invoke('pos:get-catalog'),
  getSyncStatus: () => ipcRenderer.invoke('pos:get-sync-status'),
  triggerSync: () => ipcRenderer.invoke('pos:trigger-sync'),
  setCloudUrl: (url) => ipcRenderer.invoke('pos:set-cloud-url', url),
  
  onSyncStatus: (callback) => {
    ipcRenderer.on('pos:sync-status', (event, data) => callback(data));
  },
  onOrdersUpdated: (callback) => {
    ipcRenderer.on('pos:orders-updated', () => callback());
  },
  onCatalogUpdated: (callback) => {
    ipcRenderer.on('pos:catalog-updated', (event, data) => callback(data));
  }
});


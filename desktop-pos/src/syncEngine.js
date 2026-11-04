class SyncEngine {
  constructor(db, options = {}) {
    this.db = db;
    this.cloudApiUrl = options.cloudApiUrl || 'http://127.0.0.1:8000/api';
    this.pollIntervalMs = options.pollIntervalMs || 6000;
    this.isOnline = false;
    this.isSyncing = false;
    this.lastSyncTime = null;
    this.timer = null;
    this.onStatusChange = options.onStatusChange || (() => {});
    this.onOrdersChange = options.onOrdersChange || (() => {});
    this.onCatalogChange = options.onCatalogChange || (() => {});
  }

  setCloudUrl(newUrl) {
    if (!newUrl) return;
    let clean = newUrl.trim().replace(/\/+$/, '');
    if (!clean.endsWith('/api')) {
      clean += '/api';
    }
    this.cloudApiUrl = clean;
    this.triggerNow();
  }

  start() {
    this.stop();
    this.tick();
    this.timer = setInterval(() => this.tick(), this.pollIntervalMs);
  }

  stop() {
    if (this.timer) {
      clearInterval(this.timer);
      this.timer = null;
    }
  }

  async triggerNow() {
    return await this.tick();
  }

  getStatus() {
    const pending = this.db.getPendingQueue();
    return {
      isOnline: this.isOnline,
      isSyncing: this.isSyncing,
      pendingCount: pending.length,
      lastSyncTime: this.lastSyncTime,
      cloudApiUrl: this.cloudApiUrl,
      statusLabel: !this.isOnline
        ? `Modo Offline (SQLite Local - ${pending.length} pendentes)`
        : (this.isSyncing
            ? `Sincronizando com a Nuvem (${pending.length} na fila)...`
            : `Online & Sincronizado (${pending.length} pendentes)`)
    };
  }

  async tick() {
    if (this.isSyncing) return;
    this.isSyncing = true;
    this.emitStatus();

    try {
      // 1. Teste de conectividade e espelhamento do catálogo
      const catalogRes = await fetch(`${this.cloudApiUrl}/sync/catalog`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout(4500)
      });

      if (!catalogRes.ok) {
        throw new Error(`HTTP ${catalogRes.status}`);
      }

      const catalogData = await catalogRes.json();
      this.isOnline = true;
      this.lastSyncTime = new Date().toISOString();

      // Espelha catálogo se o hash mudou
      const localCat = this.db.getLocalCatalog();
      if (!localCat || localCat.hash !== catalogData.catalog_hash) {
        this.db.saveLocalCatalog(catalogData.data, catalogData.catalog_hash);
        this.onCatalogChange(catalogData.data);
      }

      // 2. Push da Fila Local para a Nuvem (FIFO)
      await this.pushPendingQueue();

      // 3. Pull Delta da Nuvem para o SQLite Local (novos pedidos da Web)
      await this.pullCloudOrdersDelta();

    } catch (err) {
      this.isOnline = false;
    } finally {
      this.isSyncing = false;
      this.emitStatus();
    }
  }

  async pushPendingQueue() {
    const pendingItems = this.db.getPendingQueue();
    if (!pendingItems || pendingItems.length === 0) return;

    const ordersToSync = [];
    const itemMap = new Map();

    for (const qItem of pendingItems) {
      try {
        const payload = JSON.parse(qItem.payload);
        ordersToSync.push(payload);
        itemMap.set(payload.uuid, qItem.id);
      } catch (e) {
        this.db.markQueueItemFailed(qItem.id, 'JSON de fila corrompido');
      }
    }

    if (ordersToSync.length === 0) return;

    try {
      const res = await fetch(`${this.cloudApiUrl}/sync/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ orders: ordersToSync }),
        signal: AbortSignal.timeout(8000)
      });

      if (!res.ok) {
        throw new Error(`Falha no Push: HTTP ${res.status}`);
      }

      const data = await res.json();
      if (Array.isArray(data.results)) {
        for (const r of data.results) {
          const queueId = itemMap.get(r.uuid);
          if (queueId) {
            if (r.status === 'error') {
              this.db.markQueueItemFailed(queueId, r.message || 'Erro do servidor');
            } else {
              this.db.markQueueItemSynced(queueId, r.uuid, r.server_status);
            }
          }
        }
        this.onOrdersChange();
      }
    } catch (e) {
      for (const qItem of pendingItems) {
        this.db.markQueueItemFailed(qItem.id, e.message);
      }
    }
  }

  async pullCloudOrdersDelta() {
    const lastPulledAt = this.db.getSetting('last_pulled_at', null);
    const url = lastPulledAt
      ? `${this.cloudApiUrl}/sync/orders-delta?since=${encodeURIComponent(lastPulledAt)}`
      : `${this.cloudApiUrl}/sync/orders-delta`;

    try {
      const res = await fetch(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout(6000)
      });

      if (!res.ok) return;

      const data = await res.json();
      if (Array.isArray(data.orders) && data.orders.length > 0) {
        const count = this.db.upsertCloudOrdersDelta(data.orders);
        if (count > 0) {
          this.onOrdersChange();
        }
      }

      if (data.timestamp) {
        this.db.setSetting('last_pulled_at', data.timestamp);
      }
    } catch (e) {
      // Falha silenciosa no pull delta; tenta no próximo tick
    }
  }

  emitStatus() {
    this.onStatusChange(this.getStatus());
  }
}

module.exports = SyncEngine;


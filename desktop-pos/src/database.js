const { DatabaseSync } = require('node:sqlite');
const path = require('path');
const fs = require('fs');
const crypto = require('crypto');

class PosDatabase {
  constructor(customPath = null) {
    const dbDir = customPath ? path.dirname(customPath) : path.join(__dirname, '..', 'data');
    if (!fs.existsSync(dbDir)) {
      fs.mkdirSync(dbDir, { recursive: true });
    }
    const dbFile = customPath || path.join(dbDir, 'aky_pos_local.sqlite');
    this.db = new DatabaseSync(dbFile);
    this.initTables();
  }

  initTables() {
    this.db.exec(`
      PRAGMA journal_mode = WAL;
      PRAGMA foreign_keys = ON;

      CREATE TABLE IF NOT EXISTS local_orders (
        uuid TEXT PRIMARY KEY,
        order_number TEXT UNIQUE NOT NULL,
        origin TEXT DEFAULT 'desktop_pos',
        sync_status TEXT DEFAULT 'pending',
        customer_name TEXT NOT NULL,
        customer_phone TEXT,
        customer_document_encrypted TEXT,
        delivery_cep TEXT,
        delivery_address TEXT,
        delivery_complement TEXT,
        delivery_reference TEXT,
        status TEXT DEFAULT 'recebido',
        total REAL DEFAULT 0.00,
        notes TEXT,
        payment_method TEXT DEFAULT 'dinheiro',
        needs_change INTEGER DEFAULT 0,
        change_for REAL,
        motoboy_name TEXT,
        client_updated_at TEXT,
        created_at TEXT,
        updated_at TEXT
      );

      CREATE TABLE IF NOT EXISTS local_order_items (
        uuid TEXT PRIMARY KEY,
        order_uuid TEXT NOT NULL,
        product_id INTEGER,
        product_name_snapshot TEXT NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        unit_price REAL NOT NULL DEFAULT 0.00,
        subtotal REAL NOT NULL DEFAULT 0.00,
        FOREIGN KEY (order_uuid) REFERENCES local_orders(uuid) ON DELETE CASCADE
      );

      CREATE TABLE IF NOT EXISTS sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL DEFAULT 'order',
        entity_uuid TEXT NOT NULL,
        action TEXT NOT NULL DEFAULT 'upsert',
        payload TEXT NOT NULL,
        attempts INTEGER DEFAULT 0,
        status TEXT DEFAULT 'pending',
        last_error TEXT,
        created_at TEXT,
        updated_at TEXT
      );

      CREATE TABLE IF NOT EXISTS local_catalog (
        key TEXT PRIMARY KEY,
        catalog_hash TEXT,
        data_json TEXT,
        updated_at TEXT
      );

      CREATE TABLE IF NOT EXISTS local_settings (
        key TEXT PRIMARY KEY,
        value_json TEXT,
        updated_at TEXT
      );
    `);
  }

  generateOrderNumber() {
    const d = new Date();
    const dateStr = d.toISOString().slice(0, 10).replace(/-/g, '');
    const rand = crypto.randomBytes(2).toString('hex').toUpperCase();
    return `AKY-${dateStr}-${rand}`;
  }

  createOrder(orderData, itemsData = []) {
    const uuid = orderData.uuid || crypto.randomUUID();
    const orderNumber = orderData.order_number || this.generateOrderNumber();
    const now = new Date().toISOString();

    const insertOrderStmt = this.db.prepare(`
      INSERT INTO local_orders (
        uuid, order_number, origin, sync_status, customer_name, customer_phone,
        customer_document_encrypted, delivery_cep, delivery_address, delivery_complement,
        delivery_reference, status, total, notes, payment_method, needs_change,
        change_for, motoboy_name, client_updated_at, created_at, updated_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const insertItemStmt = this.db.prepare(`
      INSERT INTO local_order_items (
        uuid, order_uuid, product_id, product_name_snapshot, quantity, unit_price, subtotal
      ) VALUES (?, ?, ?, ?, ?, ?, ?)
    `);

    const insertQueueStmt = this.db.prepare(`
      INSERT INTO sync_queue (
        entity_type, entity_uuid, action, payload, status, created_at, updated_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?)
    `);

    // Início da transação local
    insertOrderStmt.run(
      uuid,
      orderNumber,
      orderData.origin || 'desktop_pos',
      'pending',
      orderData.customer_name || 'Balcão / Terminal',
      orderData.customer_phone || '',
      orderData.customer_document_encrypted || null,
      orderData.delivery_cep || null,
      orderData.delivery_address || 'RETIRADA NO BALCÃO',
      orderData.delivery_complement || null,
      orderData.delivery_reference || null,
      orderData.status || 'recebido',
      Number(orderData.total || 0),
      orderData.notes || null,
      orderData.payment_method || 'dinheiro',
      orderData.needs_change ? 1 : 0,
      orderData.change_for ? Number(orderData.change_for) : null,
      orderData.motoboy_name || null,
      now,
      now,
      now
    );

    const createdItems = [];
    for (const item of itemsData) {
      const itemUuid = item.uuid || crypto.randomUUID();
      const qty = Number(item.quantity || 1);
      const price = Number(item.unit_price || 0);
      const subtotal = Number(item.subtotal || (qty * price));

      insertItemStmt.run(
        itemUuid,
        uuid,
        item.product_id ? Number(item.product_id) : null,
        item.product_name_snapshot || item.name || 'Item',
        qty,
        price,
        subtotal
      );

      createdItems.push({
        uuid: itemUuid,
        order_uuid: uuid,
        product_id: item.product_id,
        product_name_snapshot: item.product_name_snapshot || item.name,
        quantity: qty,
        unit_price: price,
        subtotal: subtotal
      });
    }

    // Prepara payload de sincronização na fila
    const fullOrderPayload = {
      uuid: uuid,
      order_number: orderNumber,
      origin: orderData.origin || 'desktop_pos',
      customer_name: orderData.customer_name || 'Balcão / Terminal',
      customer_phone: orderData.customer_phone || '',
      delivery_address: orderData.delivery_address || 'RETIRADA NO BALCÃO',
      delivery_complement: orderData.delivery_complement || null,
      delivery_reference: orderData.delivery_reference || null,
      status: orderData.status || 'recebido',
      total: Number(orderData.total || 0),
      notes: orderData.notes || null,
      payment_method: orderData.payment_method || 'dinheiro',
      needs_change: !!orderData.needs_change,
      change_for: orderData.change_for ? Number(orderData.change_for) : null,
      client_updated_at: now,
      items: createdItems.map(i => ({
        uuid: i.uuid,
        product_id: i.product_id,
        product_name_snapshot: i.product_name_snapshot,
        quantity: i.quantity,
        unit_price: i.unit_price,
        subtotal: i.subtotal
      }))
    };

    insertQueueStmt.run(
      'order',
      uuid,
      'upsert',
      JSON.stringify(fullOrderPayload),
      'pending',
      now,
      now
    );

    return {
      ...fullOrderPayload,
      sync_status: 'pending',
      created_at: now,
      updated_at: now,
      items: createdItems
    };
  }

  updateOrderStatus(orderUuid, newStatus) {
    const now = new Date().toISOString();
    const updateStmt = this.db.prepare(`
      UPDATE local_orders
      SET status = ?, sync_status = 'pending', client_updated_at = ?, updated_at = ?
      WHERE uuid = ?
    `);
    updateStmt.run(newStatus, now, now, orderUuid);

    const insertQueueStmt = this.db.prepare(`
      INSERT INTO sync_queue (
        entity_type, entity_uuid, action, payload, status, created_at, updated_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?)
    `);

    insertQueueStmt.run(
      'order_status',
      orderUuid,
      'status_update',
      JSON.stringify({ uuid: orderUuid, status: newStatus, client_updated_at: now }),
      'pending',
      now,
      now
    );

    return true;
  }

  getOrders(limit = 100) {
    const ordersStmt = this.db.prepare(`
      SELECT * FROM local_orders
      ORDER BY created_at DESC
      LIMIT ?
    `);
    const orders = ordersStmt.all(limit);

    const itemsStmt = this.db.prepare(`
      SELECT * FROM local_order_items
      WHERE order_uuid = ?
    `);

    return orders.map(order => {
      const items = itemsStmt.all(order.uuid);
      return {
        ...order,
        needs_change: !!order.needs_change,
        items: items
      };
    });
  }

  getPendingQueue() {
    const stmt = this.db.prepare(`
      SELECT * FROM sync_queue
      WHERE status = 'pending'
      ORDER BY id ASC
      LIMIT 50
    `);
    return stmt.all();
  }

  markQueueItemSynced(queueId, orderUuid, serverStatus = null) {
    const now = new Date().toISOString();
    this.db.prepare(`
      UPDATE sync_queue
      SET status = 'synced', updated_at = ?
      WHERE id = ?
    `).run(now, queueId);

    if (orderUuid) {
      if (serverStatus) {
        this.db.prepare(`
          UPDATE local_orders
          SET sync_status = 'synced', status = ?, updated_at = ?
          WHERE uuid = ?
        `).run(serverStatus, now, orderUuid);
      } else {
        this.db.prepare(`
          UPDATE local_orders
          SET sync_status = 'synced', updated_at = ?
          WHERE uuid = ?
        `).run(now, orderUuid);
      }
    }
  }

  markQueueItemFailed(queueId, errorMsg) {
    const now = new Date().toISOString();
    this.db.prepare(`
      UPDATE sync_queue
      SET attempts = attempts + 1, last_error = ?, updated_at = ?
      WHERE id = ?
    `).run(String(errorMsg || 'Erro desconhecido'), now, queueId);
  }

  upsertCloudOrdersDelta(cloudOrders = []) {
    if (!Array.isArray(cloudOrders) || cloudOrders.length === 0) return 0;
    let count = 0;

    const findStmt = this.db.prepare('SELECT uuid, sync_status FROM local_orders WHERE uuid = ?');
    const insertStmt = this.db.prepare(`
      INSERT INTO local_orders (
        uuid, order_number, origin, sync_status, customer_name, customer_phone,
        customer_document_encrypted, delivery_cep, delivery_address, delivery_complement,
        delivery_reference, status, total, notes, payment_method, needs_change,
        change_for, motoboy_name, client_updated_at, created_at, updated_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const updateStmt = this.db.prepare(`
      UPDATE local_orders
      SET status = ?, motoboy_name = ?, updated_at = ?
      WHERE uuid = ?
    `);

    const insertItemStmt = this.db.prepare(`
      INSERT OR REPLACE INTO local_order_items (
        uuid, order_uuid, product_id, product_name_snapshot, quantity, unit_price, subtotal
      ) VALUES (?, ?, ?, ?, ?, ?, ?)
    `);

    for (const co of cloudOrders) {
      if (!co.uuid) continue;
      const existing = findStmt.get(co.uuid);
      const motoboyName = co.motoboy ? co.motoboy.name : null;

      if (!existing) {
        insertStmt.run(
          co.uuid,
          co.order_number,
          co.origin || 'web',
          'synced',
          co.customer_name || 'Cliente Web',
          co.customer_phone || '',
          null,
          co.delivery_cep || null,
          co.delivery_address || 'Retirada no Balcão',
          co.delivery_complement || null,
          co.delivery_reference || null,
          co.status || 'recebido',
          Number(co.total || 0),
          co.notes || null,
          co.payment_method || 'dinheiro',
          co.needs_change ? 1 : 0,
          co.change_for ? Number(co.change_for) : null,
          motoboyName,
          co.client_updated_at || co.updated_at || new Date().toISOString(),
          co.created_at || new Date().toISOString(),
          co.updated_at || new Date().toISOString()
        );

        if (Array.isArray(co.items)) {
          for (const it of co.items) {
            insertItemStmt.run(
              it.uuid || crypto.randomUUID(),
              co.uuid,
              it.product_id ? Number(it.product_id) : null,
              it.product_name_snapshot || 'Item',
              Number(it.quantity || 1),
              Number(it.unit_price || 0),
              Number(it.subtotal || 0)
            );
          }
        }
        count++;
      } else if (existing.sync_status === 'synced') {
        // Atualiza status se na nuvem mudou e aqui não tem edição pendente
        updateStmt.run(co.status, motoboyName, co.updated_at || new Date().toISOString(), co.uuid);
        count++;
      }
    }

    return count;
  }

  saveLocalCatalog(catalogData, hash) {
    const now = new Date().toISOString();
    this.db.prepare(`
      INSERT OR REPLACE INTO local_catalog (key, catalog_hash, data_json, updated_at)
      VALUES ('main_catalog', ?, ?, ?)
    `).run(hash || '', JSON.stringify(catalogData || {}), now);
  }

  getLocalCatalog() {
    const row = this.db.prepare("SELECT * FROM local_catalog WHERE key = 'main_catalog'").get();
    if (!row || !row.data_json) return null;
    try {
      return {
        hash: row.catalog_hash,
        data: JSON.parse(row.data_json),
        updated_at: row.updated_at
      };
    } catch (e) {
      return null;
    }
  }

  getSetting(key, defaultValue = null) {
    const row = this.db.prepare('SELECT value_json FROM local_settings WHERE key = ?').get(key);
    if (!row) return defaultValue;
    try {
      return JSON.parse(row.value_json);
    } catch (e) {
      return defaultValue;
    }
  }

  setSetting(key, value) {
    const now = new Date().toISOString();
    this.db.prepare(`
      INSERT OR REPLACE INTO local_settings (key, value_json, updated_at)
      VALUES (?, ?, ?)
    `).run(key, JSON.stringify(value), now);
  }
}

module.exports = PosDatabase;


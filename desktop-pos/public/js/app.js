const { createApp } = Vue;

const app = createApp({
  data() {
    return {
      currentTab: 'pos',
      searchTerm: '',
      orderSearchTerm: '',
      selectedCategory: null,
      catalogCategories: [],
      catalogProducts: [],
      localOrders: [],
      cart: [],
      customerName: '',
      paymentMethod: 'dinheiro',
      changeFor: null,
      savingOrder: false,
      showConfigModal: false,
      cloudUrlInput: 'http://127.0.0.1:8000/api',
      syncStatus: {
        isOnline: false,
        isSyncing: false,
        pendingCount: 0,
        lastSyncTime: null,
        statusLabel: 'Conectando ao SQLite Local...',
        cloudApiUrl: 'http://127.0.0.1:8000/api'
      }
    };
  },
  computed: {
    filteredCatalogProducts() {
      let list = this.catalogProducts;
      if (this.selectedCategory !== null) {
        list = list.filter(p => p.category_id === this.selectedCategory);
      }
      if (this.searchTerm.trim()) {
        const term = this.searchTerm.toLowerCase();
        list = list.filter(p =>
          (p.name && p.name.toLowerCase().includes(term)) ||
          (p.description && p.description.toLowerCase().includes(term))
        );
      }
      return list;
    },
    filteredLocalOrders() {
      if (!this.orderSearchTerm.trim()) {
        return this.localOrders;
      }
      const term = this.orderSearchTerm.toLowerCase();
      return this.localOrders.filter(o =>
        (o.order_number && o.order_number.toLowerCase().includes(term)) ||
        (o.customer_name && o.customer_name.toLowerCase().includes(term))
      );
    },
    cartTotal() {
      return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
    },
    calculatedChange() {
      if (this.paymentMethod !== 'dinheiro' || !this.changeFor) return 0;
      const diff = Number(this.changeFor) - this.cartTotal;
      return diff > 0 ? diff : 0;
    }
  },
  async mounted() {
    this.initElectronListeners();
    await this.loadInitialData();
  },
  methods: {
    getPosBridge() {
      if (window.akyPos) {
        return window.akyPos;
      }
      // Mock Fallback se testado fora do Electron
      return {
        createOrder: async (orderData, itemsData) => {
          console.warn('Fallback mock: createOrder');
          return { ...orderData, uuid: 'mock-' + Date.now(), items: itemsData };
        },
        getOrders: async () => [],
        updateOrderStatus: async () => true,
        getCatalog: async () => null,
        getSyncStatus: async () => ({ isOnline: false, pendingCount: 0, statusLabel: 'Modo Web Mock' }),
        triggerSync: async () => {},
        setCloudUrl: async (url) => ({ isOnline: false, cloudApiUrl: url }),
        onSyncStatus: () => {},
        onOrdersUpdated: () => {},
        onCatalogUpdated: () => {}
      };
    },
    initElectronListeners() {
      const pos = this.getPosBridge();
      pos.onSyncStatus(status => {
        this.syncStatus = status;
        if (status.cloudApiUrl) {
          this.cloudUrlInput = status.cloudApiUrl;
        }
      });
      pos.onOrdersUpdated(() => {
        this.loadOrders();
      });
      pos.onCatalogUpdated(catData => {
        this.applyCatalogData(catData);
      });
    },
    async loadInitialData() {
      const pos = this.getPosBridge();
      try {
        const status = await pos.getSyncStatus();
        if (status) {
          this.syncStatus = status;
          if (status.cloudApiUrl) {
            this.cloudUrlInput = status.cloudApiUrl;
          }
        }
        await this.loadCatalog();
        await this.loadOrders();
      } catch (e) {
        console.error(e);
      }
    },
    async loadCatalog() {
      const pos = this.getPosBridge();
      const catData = await pos.getCatalog();
      if (catData) {
        this.applyCatalogData(catData);
      }
    },
    applyCatalogData(catData) {
      if (!catData) return;
      if (Array.isArray(catData.categories)) {
        this.catalogCategories = catData.categories;
      }
      if (Array.isArray(catData.products)) {
        this.catalogProducts = catData.products;
      }
    },
    async loadOrders() {
      const pos = this.getPosBridge();
      try {
        const orders = await pos.getOrders(100);
        this.localOrders = orders || [];
      } catch (e) {
        console.error(e);
      }
    },
    addToCart(product) {
      const existing = this.cart.find(it => it.product_id === product.id);
      const price = Number(product.is_promotion && product.promotional_price ? product.promotional_price : (product.effective_price || product.price || 0));
      if (existing) {
        existing.quantity += 1;
      } else {
        this.cart.push({
          product_id: product.id,
          name: product.name,
          quantity: 1,
          unit_price: price
        });
      }
    },
    incrementItem(idx) {
      this.cart[idx].quantity += 1;
    },
    decrementItem(idx) {
      if (this.cart[idx].quantity > 1) {
        this.cart[idx].quantity -= 1;
      } else {
        this.cart.splice(idx, 1);
      }
    },
    clearCart() {
      this.cart = [];
      this.customerName = '';
      this.changeFor = null;
    },
    async submitOrderLocal() {
      if (this.cart.length === 0) return;
      this.savingOrder = true;
      const pos = this.getPosBridge();

      const orderPayload = {
        customer_name: this.customerName.trim() || 'Balcão / Caixa',
        customer_phone: '',
        delivery_address: 'RETIRADA NO BALCÃO',
        payment_method: this.paymentMethod,
        needs_change: this.paymentMethod === 'dinheiro' && Boolean(this.changeFor),
        change_for: this.paymentMethod === 'dinheiro' && this.changeFor ? Number(this.changeFor) : null,
        total: this.cartTotal,
        origin: 'desktop_pos'
      };

      const itemsPayload = this.cart.map(it => ({
        product_id: it.product_id,
        product_name_snapshot: it.name,
        quantity: it.quantity,
        unit_price: it.unit_price,
        subtotal: it.quantity * it.unit_price
      }));

      try {
        // Grava instantaneamente no SQLite local (< 5ms)
        const created = await pos.createOrder(orderPayload, itemsPayload);
        this.clearCart();
        await this.loadOrders();
        this.currentTab = 'orders';
      } catch (err) {
        alert('Erro ao gravar pedido localmente: ' + err.message);
      } finally {
        this.savingOrder = false;
      }
    },
    async advanceStatus(uuid, newStatus) {
      const pos = this.getPosBridge();
      try {
        await pos.updateOrderStatus(uuid, newStatus);
        await this.loadOrders();
      } catch (err) {
        alert('Erro ao atualizar status: ' + err.message);
      }
    },
    async triggerManualSync() {
      const pos = this.getPosBridge();
      await pos.triggerSync();
      await this.loadOrders();
    },
    async saveCloudUrl() {
      const pos = this.getPosBridge();
      this.syncStatus = await pos.setCloudUrl(this.cloudUrlInput);
      this.showConfigModal = false;
    },
    isDrink(prod) {
      if (!prod) return false;
      const catSlug = (prod.category && prod.category.slug) ? prod.category.slug.toLowerCase() : '';
      const name = (prod.name || '').toLowerCase();
      const keywords = ['bebida', 'suco', 'refrigerante', 'refrig', 'cerveja', 'agua', 'água', 'coca', 'guaraná', 'guarana'];
      return keywords.some(k => catSlug.includes(k) || name.includes(k));
    },
    getCardBorderClass(prod) {
      if (prod.is_promotion) return 'card-promo-rgb';
      if (this.isDrink(prod)) return 'border-warning';
      if (prod.is_combo) return 'border-success';
      if (prod.name && (prod.name.toLowerCase().includes('monte') || prod.name.toLowerCase().includes('pizza'))) return 'border-primary';
      return 'border-danger';
    },
    getPriceTextClass(prod) {
      if (this.isDrink(prod)) return 'text-warning-emphasis';
      if (prod.is_combo) return 'text-success';
      if (prod.name && (prod.name.toLowerCase().includes('monte') || prod.name.toLowerCase().includes('pizza'))) return 'text-primary';
      return 'text-danger';
    },
    getStatusLabel(status) {
      switch (status) {
        case 'recebido': return 'Recebido';
        case 'em_preparacao': return 'Em Preparação';
        case 'saiu_para_entrega': return 'Pronto / Saiu';
        case 'concluido': return 'Concluído';
        case 'cancelado': return 'Cancelado';
        default: return status;
      }
    },
    getStatusBadgeClass(status) {
      switch (status) {
        case 'recebido': return 'bg-warning text-dark';
        case 'em_preparacao': return 'bg-primary text-white';
        case 'saiu_para_entrega': return 'bg-info text-dark';
        case 'concluido': return 'bg-success text-white';
        case 'cancelado': return 'bg-danger text-white';
        default: return 'bg-secondary text-white';
      }
    },
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },
    async printKitchenTicket(order) {
      try {
        const storeInfo = (this.settings && this.settings.store_info) ? this.settings.store_info : { name: 'AKY LANCHES' };
        const printSettings = (this.settings && this.settings.print_settings) ? this.settings.print_settings : {
          paper_size: '80mm',
          font_size: '13px',
          margin_mm: 2,
          show_prices: false,
          show_customer_phone: true,
          show_delivery_address: true,
          show_cut_line: true,
          footer_text: 'Bom apetite! Preparado com capricho.',
        };

        let width = '72mm';
        if (printSettings.paper_size === '58mm') width = '48mm';
        else if (printSettings.paper_size === 'a4') width = '190mm';

        const lines = [];
        lines.push('================================');
        lines.push(`        ${storeInfo.name || 'AKY LANCHES'}`);
        lines.push('     *** CUPOM DA COZINHA ***');
        lines.push('================================');
        lines.push(`PEDIDO: #${order.order_number || (order.uuid ? order.uuid.substring(0, 8) : '0000')}`);
        lines.push(`DATA/HORA: ${this.formatTime(order.created_at)}`);
        lines.push(`CLIENTE: ${(order.customer_name || 'Balcão').toUpperCase()}`);
        if (order.customer_phone && printSettings.show_customer_phone) {
          lines.push(`FONE: ${order.customer_phone}`);
        }
        if (printSettings.show_delivery_address) {
          lines.push(`LOCAL: ${(order.delivery_address || 'Balcão (Presencial)').toUpperCase()}`);
        }
        lines.push('--------------------------------');
        lines.push('ITENS DA COMANDA:');
        if (order.items && order.items.length) {
          order.items.forEach(it => {
            const price = printSettings.show_prices ? ` [R$ ${this.formatPrice(it.subtotal)}]` : '';
            lines.push(`[${it.quantity}x] ${(it.product_name_snapshot || 'Item').toUpperCase()}${price}`);
            if (it.notes) lines.push(`   >> OBS: ${it.notes}`);
          });
        }
        lines.push('--------------------------------');
        if (order.notes) {
          lines.push(`ATENÇÃO OBS: ${order.notes.toUpperCase()}`);
        }
        lines.push(`PAGAMENTO: ${(order.payment_method || 'A DEFINIR').toUpperCase()}`);
        if (printSettings.show_prices) {
          lines.push(`TOTAL: R$ ${this.formatPrice(order.total)}`);
        }
        lines.push('================================');
        lines.push(`      ${printSettings.footer_text || 'Bom apetite!'}`);
        lines.push('================================');

        const content = lines.join('\n');
        const cut = printSettings.show_cut_line ? '<div style="border-top: 1px dashed #000; margin-top: 12px; padding-top: 6px; text-align: center; font-size: 10px;">✂ - - - - CORTE AQUI - - - - ✂</div>' : '';

        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.top = '-9999px';
        iframe.style.left = '-9999px';
        iframe.style.width = '1px';
        iframe.style.height = '1px';
        document.body.appendChild(iframe);

        const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Cupom Cozinha</title><style>@page{size: ${width} auto; margin: 2mm;} body{font-family: monospace; font-size: ${printSettings.font_size || '13px'}; font-weight: bold; width: ${width}; margin: 0 auto; white-space: pre-wrap;}</style></head><body>${content}${cut}</body></html>`;

        const idoc = iframe.contentWindow || iframe.contentDocument;
        (idoc.document || idoc).open();
        (idoc.document || idoc).write(html);
        (idoc.document || idoc).close();

        setTimeout(() => {
          try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
          } catch(e) {}
          setTimeout(() => { if (iframe.parentNode) iframe.parentNode.removeChild(iframe); }, 1000);
        }, 200);
      } catch (err) {
        console.error('Erro ao imprimir cupom desktop:', err);
      }
    }
  }
});

app.mount('#app');


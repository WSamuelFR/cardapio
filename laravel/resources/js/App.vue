<template>
  <div class="app-wrapper min-vh-100 d-flex flex-column bg-light">
    
    <!-- HEADER SUPERIOR COM FUNDO BRANCO (DESTAQUE MÁXIMO PARA A LOGO OFICIAL) -->
    <header class="bg-white border-bottom shadow-sm sticky-top py-2">
      <div class="container d-flex align-items-center justify-content-between">
        
        <!-- Logo Oficial e Nome da Loja -->
        <div class="d-flex align-items-center cursor-pointer" @click="currentView = 'home'">
          <!-- Imagem da Logo com fundo e tamanho ideais para visibilidade perfeita -->
          <img src="/images/aky_lanches_icon.svg" alt="AKY Lanches Logo" class="me-2" style="height: 52px; width: auto; object-fit: contain;">
          <div>
            <span class="fw-bold fs-5 tracking-tight d-block leading-none text-dark">AKY LANCHES</span>
            <small class="text-aky-primary fw-semibold" style="font-size: 0.75rem;">Cardápio Digital & Pedidos</small>
          </div>
        </div>

        <!-- MENU DE NAVEGAÇÃO DESKTOP COM OS MESMOS ÍCONES DO MODO CELULAR -->
        <nav class="d-none d-md-flex align-items-center gap-1 mx-3">
          <button
            :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'home' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'home'"
          >
            <i class="bi bi-house-door-fill fs-6" :class="currentView === 'home' ? 'text-white' : 'text-danger'"></i>
            <span>Início</span>
          </button>

          <button
            :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'menu' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'menu'"
          >
            <i class="bi bi-book-half fs-6" :class="currentView === 'menu' ? 'text-white' : 'text-warning'"></i>
            <span>Cardápio</span>
          </button>

          <button
            :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'tracker' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'tracker'"
          >
            <i class="bi bi-search fs-6" :class="currentView === 'tracker' ? 'text-white' : 'text-primary'"></i>
            <span>Rastrear</span>
          </button>

          <!-- Links de Gestão se Logado -->
          <template v-if="currentUser">
            <!-- Admin e Atendente: Pedidos -->
            <button
              v-if="currentUser.role === 'admin' || currentUser.role === 'attendant'"
              :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'admin-orders' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-orders'"
            >
              <i class="bi bi-kanban-fill fs-6" :class="currentView === 'admin-orders' ? 'text-white' : 'text-warning'"></i>
              <span>Pedidos</span>
            </button>

            <!-- Motoboy: Minhas Entregas -->
            <button
              v-if="currentUser.role === 'motoboy'"
              :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'motoboy-orders' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'motoboy-orders'"
            >
              <i class="bi bi-bicycle fs-6" :class="currentView === 'motoboy-orders' ? 'text-white' : 'text-warning'"></i>
              <span>Minhas Entregas</span>
            </button>

            <!-- Exclusivo Admin: Produtos, Equipe e Configurações -->
            <button
              v-if="currentUser.role === 'admin'"
              :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'admin-products' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-products'"
            >
              <i class="bi bi-tags-fill fs-6" :class="currentView === 'admin-products' ? 'text-white' : 'text-info'"></i>
              <span>Produtos</span>
            </button>
            <button
              v-if="currentUser.role === 'admin'"
              :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'admin-attendants' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-attendants'"
            >
              <i class="bi bi-people-fill fs-6" :class="currentView === 'admin-attendants' ? 'text-white' : 'text-success'"></i>
              <span>Equipe</span>
            </button>
            <button
              v-if="currentUser.role === 'admin'"
              :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'admin-settings' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-settings'"
            >
              <i class="bi bi-gear-fill fs-6" :class="currentView === 'admin-settings' ? 'text-white' : 'text-primary'"></i>
              <span>Configurações</span>
            </button>
          </template>
        </nav>

        <!-- AÇÕES DO HEADER (CELULAR E PC): Carrinho e Área Restrita / Perfil -->
        <div class="d-flex align-items-center gap-2">
          
          <!-- Botão Carrinho com Badge Dinâmico -->
          <button
            class="btn btn-warning rounded-pill px-3 py-1 py-md-2 fw-bold d-flex align-items-center shadow-sm"
            @click="showCart = true"
          >
            <i class="bi bi-cart3 fs-5 me-1"></i>
            <span class="d-none d-sm-inline me-1">Carrinho</span>
            <span v-if="cartCount > 0" class="badge rounded-pill bg-danger ms-1 px-2 py-1">
              {{ cartCount }}
            </span>
          </button>

          <!-- Botão Área Restrita / Perfil -->
          <div v-if="currentUser" class="dropdown">
            <button class="btn btn-outline-dark rounded-pill px-3 py-1 py-md-2 dropdown-toggle btn-sm fw-semibold" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i>
              <span class="d-none d-sm-inline">{{ currentUser.name.split(' ')[0] }}</span>
              <span class="badge ms-1" :class="currentUser.role === 'admin' ? 'bg-danger' : (currentUser.role === 'motoboy' ? 'bg-warning text-dark' : 'bg-primary')">
                {{ currentUser.role === 'admin' ? 'Admin' : (currentUser.role === 'motoboy' ? 'Motoboy' : 'Atendente') }}
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
              <li><h6 class="dropdown-header small">{{ currentUser.email }}</h6></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger cursor-pointer" @click="handleLogout"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
            </ul>
          </div>
          <button
            v-else
            class="btn btn-outline-secondary rounded-pill btn-sm px-3 py-1 py-md-2 fw-semibold"
            @click="showLogin = true"
            title="Acesso Restrito"
          >
            <i class="bi bi-lock-fill me-1"></i>
            <span class="d-none d-sm-inline">Área Restrita</span>
          </button>

        </div>

      </div>

      <!-- BARRA DE NAVEGAÇÃO EXCLUSIVA PARA CELULAR (ABAIXO DO HEADER) -->
      <div class="d-md-none bg-light border-top mt-1 py-2">
        <div class="container-fluid px-1 d-flex justify-content-between text-center overflow-x-auto text-nowrap no-scrollbar gap-1">
          <button
            :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'home' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'home'"
          >
            <i class="bi bi-house-door-fill d-block fs-6" :class="currentView === 'home' ? 'text-white' : 'text-danger'"></i>
            <span style="font-size: 0.72rem;">Início</span>
          </button>

          <button
            :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'menu' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'menu'"
          >
            <i class="bi bi-book-half d-block fs-6" :class="currentView === 'menu' ? 'text-white' : 'text-warning'"></i>
            <span style="font-size: 0.72rem;">Cardápio</span>
          </button>

          <button
            :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'tracker' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'tracker'"
          >
            <i class="bi bi-search d-block fs-6" :class="currentView === 'tracker' ? 'text-white' : 'text-primary'"></i>
            <span style="font-size: 0.72rem;">Rastrear</span>
          </button>

          <!-- Links rápidos no celular para painel se logado -->
          <template v-if="currentUser">
            <button
              v-if="currentUser.role === 'admin' || currentUser.role === 'attendant'"
              :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'admin-orders' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-orders'"
            >
              <i class="bi bi-kanban-fill d-block fs-6" :class="currentView === 'admin-orders' ? 'text-white' : 'text-warning'"></i>
              <span style="font-size: 0.72rem;">Pedidos</span>
            </button>

            <button
              v-if="currentUser.role === 'motoboy'"
              :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'motoboy-orders' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'motoboy-orders'"
            >
              <i class="bi bi-bicycle d-block fs-6" :class="currentView === 'motoboy-orders' ? 'text-white' : 'text-warning'"></i>
              <span style="font-size: 0.72rem;">Entregas</span>
            </button>

            <button
              v-if="currentUser.role === 'admin'"
              :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'admin-products' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-products'"
            >
              <i class="bi bi-tags-fill d-block fs-6" :class="currentView === 'admin-products' ? 'text-white' : 'text-info'"></i>
              <span style="font-size: 0.72rem;">Produtos</span>
            </button>

            <button
              v-if="currentUser.role === 'admin'"
              :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'admin-attendants' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-attendants'"
            >
              <i class="bi bi-people-fill d-block fs-6" :class="currentView === 'admin-attendants' ? 'text-white' : 'text-success'"></i>
              <span style="font-size: 0.72rem;">Equipe</span>
            </button>

            <button
              v-if="currentUser.role === 'admin'"
              :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'admin-settings' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'admin-settings'"
            >
              <i class="bi bi-gear-fill d-block fs-6" :class="currentView === 'admin-settings' ? 'text-white' : 'text-primary'"></i>
              <span style="font-size: 0.72rem;">Config</span>
            </button>
          </template>
        </div>
      </div>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-grow-1 container py-4">
      
      <!-- TELA HOME: Banner, Busca, Promoções, Combos do Dia e Mais Pedidos -->
      <Home
        v-if="currentView === 'home'"
        :categories="menuData.categories"
        :promotions="menuData.promotions"
        :combos="menuData.combos"
        :popular="menuData.popular"
        @add-to-cart="addToCart"
        @go-to-menu="currentView = 'menu'"
        @open-section="openSection"
        @select-product="openProductDetail"
      />

      <!-- TELA CARDÁPIO: Seções de Categorias com Rolagem Horizontal -->
      <Menu
        v-else-if="currentView === 'menu'"
        :categories="menuData.categories"
        @add-to-cart="addToCart"
        @open-section="openSection"
        @select-product="openProductDetail"
      />

      <!-- TELA DEDICADA DE SEÇÃO / CATEGORIA (Com Barra de Pesquisa no Topo) -->
      <SectionView
        v-else-if="currentView === 'section'"
        :title="sectionData.title"
        :subtitle="sectionData.subtitle"
        :icon="sectionData.icon"
        :theme-color="sectionData.themeColor"
        :badge-text="sectionData.badgeText"
        :items="sectionData.items"
        @back="closeSection"
        @add-to-cart="addToCart"
        @select-product="openProductDetail"
      />

      <!-- TELA DE DETALHES DO PRODUTO (Estilo Marketplace) -->
      <ProductDetailView
        v-else-if="currentView === 'product-detail' && selectedProduct"
        :product="selectedProduct"
        :all-products="allProductsList"
        :menu-settings="menuData.menu_settings || null"
        @back="closeProductDetail"
        @add-to-cart="onDetailAddToCart"
        @select-product="(p) => openProductDetail(p, productBackView)"
      />

      <!-- TELA RASTREAR PEDIDO -->
      <OrderTracker
        v-else-if="currentView === 'tracker'"
        :initial-code="trackerCode"
      />

      <!-- TELA PAINEL DE PEDIDOS (Admin e Atendente) -->
      <AdminOrders
        v-else-if="currentView === 'admin-orders'"
      />

      <!-- TELA PAINEL DO MOTOBOY (Minhas Entregas) -->
      <MotoboyOrders
        v-else-if="currentView === 'motoboy-orders'"
      />

      <!-- TELA GESTÃO DE PRODUTOS E CATEGORIAS (Admin) -->
      <AdminProducts
        v-else-if="currentView === 'admin-products'"
        @menu-updated="loadMenu"
      />

      <!-- TELA GESTÃO DA EQUIPE (Admin) -->
      <AdminAttendants
        v-else-if="currentView === 'admin-attendants'"
      />

      <!-- TELA CONFIGURAÇÕES DO SISTEMA (Exclusivo Admin) -->
      <AdminSettings
        v-else-if="currentView === 'admin-settings' && currentUser && currentUser.role === 'admin'"
        :current-user="currentUser"
        @menu-updated="loadMenu"
      />

    </main>

    <!-- RODAPÉ -->
    <footer class="bg-white border-top py-4 mt-auto">
      <div class="container text-center">
        <img src="/images/aky_lanches_icon.svg" alt="AKY Lanches Logo" class="mb-2" style="height: 48px;">
        <h6 class="fw-bold text-dark mb-1">AKY LANCHES</h6>
        <p class="text-muted small mb-0">Cardápio digital e gerenciamento de pedidos • Todos os direitos reservados</p>
      </div>
    </footer>

    <!-- MODAIS -->
    <CartModal
      :show="showCart"
      :cart="cart"
      @close="showCart = false"
      @update-qty="updateCartQty"
      @remove-item="removeFromCart"
      @order-created="onOrderCreated"
    />

    <OrderSuccessModal
      :order="createdOrder"
      :store-whatsapp="menuData.menu_settings ? menuData.menu_settings.store_whatsapp : ''"
      @close="createdOrder = null"
      @track="trackNewOrder"
    />

    <LoginModal
      :show="showLogin"
      @close="showLogin = false"
      @logged-in="onLoggedIn"
    />

  </div>
</template>

<script>
import axios from 'axios';
import Home from './components/Home.vue';
import Menu from './components/Menu.vue';
import SectionView from './components/SectionView.vue';
import ProductDetailView from './components/ProductDetailView.vue';
import CartModal from './components/CartModal.vue';
import OrderSuccessModal from './components/OrderSuccessModal.vue';
import OrderTracker from './components/OrderTracker.vue';
import LoginModal from './components/LoginModal.vue';
import AdminOrders from './components/AdminOrders.vue';
import AdminProducts from './components/AdminProducts.vue';
import AdminAttendants from './components/AdminAttendants.vue';
import AdminSettings from './components/AdminSettings.vue';
import MotoboyOrders from './components/MotoboyOrders.vue';

export default {
  components: {
    Home,
    Menu,
    SectionView,
    ProductDetailView,
    CartModal,
    OrderSuccessModal,
    OrderTracker,
    LoginModal,
    AdminOrders,
    AdminProducts,
    AdminAttendants,
    AdminSettings,
    MotoboyOrders,
  },
  data() {
    return {
      currentView: 'home',
      currentUser: null,
      showCart: false,
      showLogin: false,
      createdOrder: null,
      trackerCode: '',
      cart: [],
      selectedProduct: null,
      productBackView: 'home',
      sectionData: {
        title: '',
        subtitle: '',
        icon: '',
        themeColor: 'danger',
        badgeText: '',
        items: [],
        backTo: 'home',
      },
      menuData: {
        categories: [],
        promotions: [],
        combos: [],
        popular: [],
      },
      menuPollingInterval: null,
      visibilityHandler: null,
    };
  },
  computed: {
    cartCount() {
      return this.cart.reduce((sum, item) => sum + Number(item.quantity || 1), 0);
    },
    allProductsList() {
      const list = [];
      if (this.menuData.categories) {
        this.menuData.categories.forEach(c => {
          if (c.products) list.push(...c.products);
        });
      }
      if (this.menuData.combos) {
        list.push(...this.menuData.combos);
      }
      return list;
    },
  },
  mounted() {
    this.checkCurrentUser();
    this.loadMenu();
    this.loadCartFromStorage();
    this.startMenuPolling();
    this.checkUrlForTracking();
  },
  beforeUnmount() {
    this.stopMenuPolling();
  },
  methods: {
    startMenuPolling() {
      // Polling autônomo e leve em background a cada 25 segundos
      this.menuPollingInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
          this.loadMenu();
        }
      }, 25000);

      this.visibilityHandler = () => {
        if (document.visibilityState === 'visible') {
          this.loadMenu();
        }
      };
      document.addEventListener('visibilitychange', this.visibilityHandler);
    },
    stopMenuPolling() {
      if (this.menuPollingInterval) {
        clearInterval(this.menuPollingInterval);
        this.menuPollingInterval = null;
      }
      if (this.visibilityHandler) {
        document.removeEventListener('visibilitychange', this.visibilityHandler);
      }
    },
    checkUrlForTracking() {
      try {
        const urlParams = new URLSearchParams(window.location.search);
        let code = urlParams.get('rastreio');
        if (!code && window.location.hash) {
          const match = window.location.hash.match(/rastreio=([a-zA-Z0-9-]+)/);
          if (match) code = match[1];
        }
        if (code) {
          this.trackNewOrder(code.trim());
        }
      } catch (e) {
        console.error(e);
      }
    },
    async checkCurrentUser() {
      try {
        const res = await axios.get('/api/auth/me');
        if (res.data.authenticated) {
          this.currentUser = res.data.user;
          if (this.currentUser.role === 'motoboy') {
            this.currentView = 'motoboy-orders';
          }
        }
      } catch (e) {
        console.error(e);
      }
    },
    async loadMenu() {
      try {
        const res = await axios.get('/api/menu');
        this.menuData = res.data;
      } catch (e) {
        console.error(e);
      }
    },
    loadCartFromStorage() {
      const saved = localStorage.getItem('aky_cart');
      if (saved) {
        try {
          const parsed = JSON.parse(saved);
          if (Array.isArray(parsed)) {
            this.cart = parsed
              .filter(item => item && item.id && !isNaN(Number(item.id)))
              .map(item => ({
                cart_item_id: item.cart_item_id || (Date.now() + '_' + Math.random().toString(36).substring(2, 7)),
                id: Number(item.id),
                name: String(item.name || ''),
                photo_path: item.photo_path || null,
                effective_price: parseFloat(item.effective_price || item.price) || 0,
                is_promotion: Boolean(item.is_promotion),
                quantity: parseInt(item.quantity) > 0 ? parseInt(item.quantity) : 1,
                notes: item.notes ? String(item.notes) : '',
              }));
          } else {
            this.cart = [];
          }
        } catch (e) {
          this.cart = [];
        }
      }
    },
    saveCartToStorage() {
      localStorage.setItem('aky_cart', JSON.stringify(this.cart));
    },
    addToCart(product, quantity = 1, notes = '') {
      if (!product || !product.id) return;

      const productId = Number(product.id);
      const addQty = parseInt(quantity) > 0 ? parseInt(quantity) : 1;
      
      const rawPrice = (product.effective_price !== undefined && product.effective_price !== null)
        ? product.effective_price
        : (product.is_promotion && product.promotional_price ? product.promotional_price : product.price);
      
      const unitPrice = parseFloat(rawPrice) || 0;
      const itemName = product.name ? String(product.name).trim() : '';
      const itemNotes = notes ? String(notes).trim() : (product.notes ? String(product.notes).trim() : '');

      // Localiza item idêntico no carrinho (mesmo ID, mesmo nome personalizado e mesmas notas)
      const existing = this.cart.find(i => Number(i.id) === productId && (i.name || '') === itemName && (i.notes || '') === itemNotes);

      if (existing) {
        existing.quantity = Number(existing.quantity || 1) + addQty;
        existing.effective_price = unitPrice;
      } else {
        this.cart.push({
          cart_item_id: Date.now() + '_' + Math.random().toString(36).substring(2, 7),
          id: productId,
          name: itemName,
          photo_path: product.photo_path || null,
          effective_price: unitPrice,
          is_promotion: Boolean(product.is_promotion),
          quantity: addQty,
          notes: itemNotes,
        });
      }

      this.saveCartToStorage();
      this.showCart = true;
    },
    onDetailAddToCart(detail) {
      this.addToCart(detail, detail.quantity || 1, detail.notes || '');
    },
    openProductDetail(product, backView = 'home') {
      this.selectedProduct = product;
      this.productBackView = backView || (this.currentView !== 'product-detail' ? this.currentView : 'home');
      this.currentView = 'product-detail';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    closeProductDetail() {
      this.currentView = this.productBackView || 'home';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    updateCartQty({ cart_item_id, id, qty }) {
      let item = null;
      if (cart_item_id) {
        item = this.cart.find(i => i.cart_item_id === cart_item_id);
      }
      if (!item && id) {
        const targetId = Number(id);
        item = this.cart.find(i => Number(i.id) === targetId);
      }
      if (item) {
        item.quantity = Number(qty);
        this.saveCartToStorage();
      }
    },
    removeFromCart(target) {
      if (typeof target === 'string' && target.includes('_')) {
        this.cart = this.cart.filter(i => i.cart_item_id !== target);
      } else if (typeof target === 'object' && target && target.cart_item_id) {
        this.cart = this.cart.filter(i => i.cart_item_id !== target.cart_item_id);
      } else {
        const targetId = Number(target && target.id ? target.id : target);
        this.cart = this.cart.filter(i => Number(i.id) !== targetId);
      }
      this.saveCartToStorage();
    },
    onOrderCreated(order) {
      this.showCart = false;
      this.cart = [];
      this.saveCartToStorage();
      this.createdOrder = order;
      this.loadMenu();
    },
    trackNewOrder(code) {
      this.createdOrder = null;
      this.trackerCode = code;
      this.currentView = 'tracker';
    },
    openSection(data) {
      this.sectionData = data;
      this.currentView = 'section';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    closeSection() {
      this.currentView = (this.sectionData && this.sectionData.backTo) ? this.sectionData.backTo : 'home';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    onLoggedIn(user) {
      this.currentUser = user;
      // Limpeza de segurança: remove dados de clientes na sessão do funcionário
      localStorage.removeItem('aky_customer_session');
      localStorage.removeItem('aky_cart');
      this.cart = [];

      if (user.role === 'motoboy') {
        this.currentView = 'motoboy-orders';
      } else {
        this.currentView = 'admin-orders';
      }
    },
    async handleLogout() {
      try {
        await axios.post('/api/auth/logout');
        this.currentUser = null;
        this.currentView = 'home';
        localStorage.removeItem('aky_customer_session');
        localStorage.removeItem('aky_cart');
        this.cart = [];
      } catch (e) {
        console.error(e);
      }
    },
  },
};
</script>

<style>
.cursor-pointer {
  cursor: pointer;
}
.transition {
  transition: all 0.2s ease-in-out;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

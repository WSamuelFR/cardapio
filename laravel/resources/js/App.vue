<template>
  <div class="app-wrapper min-vh-100 d-flex flex-column bg-light">
    
    <!-- HEADER SUPERIOR COM FUNDO BRANCO (DESTAQUE MÁXIMO PARA A LOGO OFICIAL) -->
    <header class="bg-white border-bottom shadow-sm sticky-top py-2">
      <div class="container d-flex align-items-center justify-content-between">
        
        <!-- Logo Oficial e Nome da Loja (Protegido contra quebra de linha no PC e Celular) -->
        <div class="d-flex align-items-center cursor-pointer flex-shrink-0 text-nowrap" @click="currentView = 'menu'">
          <!-- Imagem da Logo com fundo e tamanho ideais para visibilidade perfeita -->
          <img src="/images/aky_lanches_icon.svg" alt="AKY Lanches Logo" class="me-2 flex-shrink-0" style="height: 50px; width: auto; object-fit: contain;">
          <div class="text-nowrap flex-shrink-0">
            <span class="fw-bold fs-5 tracking-tight d-block leading-none text-dark text-nowrap">AKY LANCHES</span>
            <div class="d-flex align-items-center gap-1.5">
              <small class="text-aky-primary fw-semibold text-nowrap d-block" style="font-size: 0.75rem;">Cardápio Digital & Pedidos</small>
              <span v-if="storeStatus && storeStatus.is_open" class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">
                ● Aberto
              </span>
              <span v-else-if="storeStatus" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">
                ● Fechado
              </span>
            </div>
          </div>
        </div>

        <!-- MENU DE NAVEGAÇÃO DESKTOP COM OS MESMOS ÍCONES DO MODO CELULAR -->
        <nav class="d-none d-md-flex align-items-center gap-1 mx-2 flex-wrap justify-content-center">
          <button
            :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'menu' || currentView === 'home' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'menu'"
          >
            <i class="bi bi-book-half fs-6" :class="currentView === 'menu' || currentView === 'home' ? 'text-white' : 'text-danger'"></i>
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

            <!-- Cozinha (KDS / TV) - Visível para Cozinha, Admin e Atendente -->
            <button
              v-if="currentUser.role === 'admin' || currentUser.role === 'attendant' || currentUser.role === 'kitchen'"
              :class="['btn btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 border-0 transition', currentView === 'kitchen-orders' ? 'btn-danger text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'kitchen-orders'"
            >
              <i class="bi bi-fire fs-6" :class="currentView === 'kitchen-orders' ? 'text-white' : 'text-danger'"></i>
              <span>Cozinha / TV</span>
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
              <span class="badge ms-1" :class="currentUser.role === 'admin' ? 'bg-danger' : (currentUser.role === 'motoboy' ? 'bg-warning text-dark' : (currentUser.role === 'kitchen' ? 'bg-danger text-white' : 'bg-primary'))">
                {{ currentUser.role === 'admin' ? 'Admin' : (currentUser.role === 'motoboy' ? 'Motoboy' : (currentUser.role === 'kitchen' ? 'Cozinha' : 'Atendente')) }}
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2" style="min-width: 220px;">
              <li>
                <button
                  type="button"
                  class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2 cursor-pointer"
                  @click="showProfileModal = true"
                >
                  <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                    <i class="bi bi-person-gear fs-5"></i>
                  </div>
                  <div class="min-w-0">
                    <div class="fw-bold text-dark text-truncate" style="max-width: 150px;">{{ currentUser.name }}</div>
                    <div class="text-muted small" style="font-size: 0.75rem;">Gerenciar Meus Dados</div>
                  </div>
                </button>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
              <li>
                <button
                  type="button"
                  class="dropdown-item text-danger rounded-3 py-2 px-3 d-flex align-items-center gap-2 cursor-pointer"
                  @click="handleLogout"
                >
                  <i class="bi bi-box-arrow-right fs-5"></i>
                  <span class="fw-semibold">Sair da Conta</span>
                </button>
              </li>
            </ul>
          </div>
          
          <!-- Botão Moderno de Acesso ao Painel -->
          <button
            v-else
            class="btn btn-dark rounded-pill btn-sm px-3 py-1.5 py-md-2 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 border transition"
            @click="showLogin = true"
            title="Acesso Administrativo"
          >
            <i class="bi bi-shield-lock-fill text-warning"></i>
            <span class="d-none d-sm-inline">Painel Admin</span>
            <span class="d-sm-none">Entrar</span>
          </button>

        </div>

      </div>

      <!-- BARRA DE NAVEGAÇÃO EXCLUSIVA PARA CELULAR (ABAIXO DO HEADER) -->
      <div class="d-md-none bg-light border-top mt-1 py-2">
        <div :class="['container-fluid px-2 d-flex align-items-center text-center overflow-x-auto text-nowrap no-scrollbar gap-2', !currentUser ? 'justify-content-center' : 'justify-content-start']">
          <button
            :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'menu' || currentView === 'home' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentView = 'menu'"
          >
            <i class="bi bi-book-half d-block fs-6" :class="currentView === 'menu' || currentView === 'home' ? 'text-white' : 'text-danger'"></i>
            <span style="font-size: 0.72rem;">Cardápio</span>
          </button>

          <button
            :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'tracker' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
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
              v-if="currentUser.role === 'admin' || currentUser.role === 'attendant' || currentUser.role === 'kitchen'"
              :class="['btn btn-sm rounded-pill px-2 py-1 fw-semibold border-0 transition flex-shrink-0', currentView === 'kitchen-orders' ? 'btn-danger text-white shadow-sm' : 'btn-light text-dark']"
              @click="currentView = 'kitchen-orders'"
            >
              <i class="bi bi-fire d-block fs-6" :class="currentView === 'kitchen-orders' ? 'text-white' : 'text-danger'"></i>
              <span style="font-size: 0.72rem;">Cozinha</span>
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

    <!-- BANNER DE STATUS OPERACIONAL DO ESTABELECIMENTO -->
    <div
      v-if="storeStatus && !storeStatus.is_open"
      class="bg-danger text-white py-2 px-3 shadow-sm border-bottom text-center small fw-semibold"
    >
      <div class="container d-flex align-items-center justify-content-center gap-2 flex-wrap">
        <i class="bi bi-moon-stars-fill"></i>
        <span>Estamos FECHADOS no momento (Horário de Atendimento: {{ storeStatus.open_time }} às {{ storeStatus.close_time }}). Você pode navegar pelo cardápio!</span>
      </div>
    </div>
    <div
      v-else-if="storeStatus && storeStatus.only_pickup_mode"
      class="bg-warning text-dark py-2 px-3 shadow-sm border-bottom text-center small fw-bold"
    >
      <div class="container d-flex align-items-center justify-content-center gap-2 flex-wrap">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-6"></i>
        <span>MODO SÓ RETIRADA ATIVO: Hoje estamos atendendo exclusivamente com retirada no balcão da loja!</span>
      </div>
    </div>
    <div
      v-else-if="storeStatus && !storeStatus.can_delivery"
      class="bg-warning bg-opacity-25 text-dark py-2 px-3 shadow-sm border-bottom text-center small fw-semibold"
    >
      <div class="container d-flex align-items-center justify-content-center gap-2 flex-wrap">
        <i class="bi bi-bicycle text-danger"></i>
        <span>Entregas temporariamente pausadas. Pedidos disponíveis para retirada no balcão!</span>
      </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-grow-1 container py-4">
      
      <!-- TELA CARDÁPIO UNIFICADO (Busca + Filtros por Categoria + Lista em Linhas) -->
      <UnifiedMenu
        v-if="currentView === 'home' || currentView === 'menu'"
        :categories="menuData.categories"
        :promotions="menuData.promotions"
        :combos="menuData.combos"
        :popular="menuData.popular"
        @add-to-cart="addToCart"
        @select-product="openProductDetail"
      />

      <!-- TELA DE DETALHES DO PRODUTO (Estilo Marketplace) -->
      <ProductDetailView
        v-else-if="currentView === 'product-detail' && selectedProduct"
        :product="selectedProduct"
        :all-products="allProductsList"
        :menu-settings="menuData.menu_settings || null"
        :product-reviews="menuData.product_reviews ? menuData.product_reviews[selectedProduct.id] : null"
        @back="closeProductDetail"
        @add-to-cart="onDetailAddToCart"
        @select-product="(p) => openProductDetail(p, productBackView)"
      />

      <!-- TELA RASTREAR PEDIDO -->
      <OrderTracker
        v-else-if="currentView === 'tracker'"
        :initial-code="trackerCode"
        :store-info="menuData.menu_settings ? menuData.menu_settings.store_info : null"
        :store-whatsapp="menuData.menu_settings ? menuData.menu_settings.store_whatsapp : ''"
        :whatsapp-messages="menuData.menu_settings ? menuData.menu_settings.whatsapp_messages : []"
      />

      <!-- TELA PAINEL DE PEDIDOS (Admin e Atendente) -->
      <AdminOrders
        v-else-if="currentView === 'admin-orders'"
      />

      <!-- TELA MONITOR KDS DA COZINHA (TV / Monitor) -->
      <KitchenOrders
        v-else-if="currentView === 'kitchen-orders'"
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

    <!-- RODAPÉ (Oculto no Monitor KDS da Cozinha para Máximo Aproveitamento da TV) -->
    <footer v-if="currentView !== 'kitchen-orders'" class="bg-white border-top py-4 mt-auto">
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
      :store-info="menuData.menu_settings ? menuData.menu_settings.store_info : null"
      :store-status="storeStatus"
      @close="showCart = false"
      @update-qty="updateCartQty"
      @remove-item="removeFromCart"
      @order-created="onOrderCreated"
    />

    <OrderSuccessModal
      :order="createdOrder"
      :store-whatsapp="menuData.menu_settings ? menuData.menu_settings.store_whatsapp : ''"
      :store-info="menuData.menu_settings ? menuData.menu_settings.store_info : null"
      :whatsapp-template="menuData.menu_settings ? menuData.menu_settings.whatsapp_order_template : ''"
      @close="createdOrder = null"
      @track="trackNewOrder"
    />

    <LoginModal
      :show="showLogin"
      @close="showLogin = false"
      @logged-in="onLoggedIn"
    />

    <ProfileModal
      :show="showProfileModal"
      :current-user="currentUser"
      @close="showProfileModal = false"
      @updated="onProfileUpdated"
    />

    <!-- ALERTA SONORO & TOAST FLUTUANTE DE NOVO PEDIDO PARA ADMIN (EM QUALQUER PÁGINA) -->
    <transition name="fade">
      <div
        v-if="newOrderNotification"
        class="position-fixed top-0 end-0 m-3 p-3 bg-dark text-white rounded-4 shadow-lg border border-warning d-flex align-items-center gap-3 cursor-pointer"
        style="z-index: 1090; max-width: 380px;"
        @click="goToOrdersFromNotification"
      >
        <div class="rounded-circle bg-warning text-dark p-2.5 d-flex align-items-center justify-content-center flex-shrink-0">
          <i class="bi bi-bell-fill fs-4 text-dark"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex align-items-center justify-content-between mb-0.5">
            <strong class="text-warning small text-uppercase">🔔 Novo Pedido Recebido!</strong>
            <button type="button" class="btn-close btn-close-white btn-sm" @click.stop="newOrderNotification = null"></button>
          </div>
          <h6 class="fw-bold mb-0 text-truncate text-white">#{{ newOrderNotification.order_number }} • {{ newOrderNotification.customer_name }}</h6>
          <small class="text-white-50" style="font-size: 0.72rem;">Clique aqui para abrir os pedidos</small>
        </div>
      </div>
    </transition>

  </div>
</template>

<script>
import axios from 'axios';
import UnifiedMenu from './components/UnifiedMenu.vue';
import Home from './components/Home.vue';
import Menu from './components/Menu.vue';
import SectionView from './components/SectionView.vue';
import ProductDetailView from './components/ProductDetailView.vue';
import CartModal from './components/CartModal.vue';
import OrderSuccessModal from './components/OrderSuccessModal.vue';
import OrderTracker from './components/OrderTracker.vue';
import LoginModal from './components/LoginModal.vue';
import ProfileModal from './components/ProfileModal.vue';
import AdminOrders from './components/AdminOrders.vue';
import AdminProducts from './components/AdminProducts.vue';
import AdminAttendants from './components/AdminAttendants.vue';
import AdminSettings from './components/AdminSettings.vue';
import MotoboyOrders from './components/MotoboyOrders.vue';
import KitchenOrders from './components/KitchenOrders.vue';

export default {
  components: {
    UnifiedMenu,
    Home,
    Menu,
    SectionView,
    ProductDetailView,
    CartModal,
    OrderSuccessModal,
    OrderTracker,
    LoginModal,
    ProfileModal,
    AdminOrders,
    AdminProducts,
    AdminAttendants,
    AdminSettings,
    MotoboyOrders,
    KitchenOrders,
  },
  data() {
    return {
      currentView: 'menu',
      currentUser: null,
      showCart: false,
      showLogin: false,
      showProfileModal: false,
      createdOrder: null,
      trackerCode: '',
      cart: [],
      selectedProduct: null,
      productBackView: 'menu',
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
      newOrderNotification: null,
      lastKnownOrderId: null,
      adminNotificationInterval: null,
    };
  },
  watch: {
    currentUser(newVal) {
      if (newVal && (newVal.role === 'admin' || newVal.role === 'attendant')) {
        this.startAdminNotificationPolling();
      } else {
        this.stopAdminNotificationPolling();
      }
    },
  },
  computed: {
    storeStatus() {
      return this.menuData && this.menuData.menu_settings ? this.menuData.menu_settings.store_status : null;
    },
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
    this.stopAdminNotificationPolling();
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
          if (this.currentUser.role === 'admin' || this.currentUser.role === 'attendant') {
            this.startAdminNotificationPolling();
          } else if (this.currentUser.role === 'motoboy') {
            this.currentView = 'motoboy-orders';
          } else if (this.currentUser.role === 'kitchen') {
            this.currentView = 'kitchen-orders';
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
      this.currentView = this.productBackView || 'menu';
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

      if (user.role === 'admin' || user.role === 'attendant') {
        this.startAdminNotificationPolling();
        this.currentView = 'admin-orders';
      } else if (user.role === 'motoboy') {
        this.currentView = 'motoboy-orders';
      } else if (user.role === 'kitchen') {
        this.currentView = 'kitchen-orders';
      } else {
        this.currentView = 'admin-orders';
      }
    },
    onProfileUpdated(updatedUser) {
      if (updatedUser) {
        this.currentUser = { ...this.currentUser, ...updatedUser };
      }
    },
    async handleLogout() {
      this.stopAdminNotificationPolling();
      this.newOrderNotification = null;
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
    playNewOrderSound() {
      try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();

        const now = ctx.currentTime;
        // Tom 1 - Agudo inicial
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(587.33, now); // D5
        osc1.frequency.exponentialRampToValueAtTime(880.00, now + 0.15); // A5
        gain1.gain.setValueAtTime(0.3, now);
        gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.25);

        // Tom 2 - Sino harmônico de campainha de pedido
        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'triangle';
        osc2.frequency.setValueAtTime(880.00, now + 0.25); // A5
        osc2.frequency.exponentialRampToValueAtTime(1174.66, now + 0.45); // D6
        gain2.gain.setValueAtTime(0.35, now + 0.25);
        gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.7);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.25);
        osc2.stop(now + 0.7);
      } catch (e) {
        console.warn('Audio notification unavailable:', e);
      }
    },
    startAdminNotificationPolling() {
      this.stopAdminNotificationPolling();
      this.checkNewOrdersForAdmin(true);
      // Checa a cada 12 segundos em background mesmo estando em outras abas
      this.adminNotificationInterval = setInterval(() => {
        this.checkNewOrdersForAdmin(false);
      }, 12000);
    },
    stopAdminNotificationPolling() {
      if (this.adminNotificationInterval) {
        clearInterval(this.adminNotificationInterval);
        this.adminNotificationInterval = null;
      }
    },
    async checkNewOrdersForAdmin(initial = false) {
      if (!this.currentUser || (this.currentUser.role !== 'admin' && this.currentUser.role !== 'attendant')) return;
      try {
        const res = await axios.get('/api/admin/orders');
        const orders = res.data.data || res.data || [];
        if (orders.length > 0) {
          const latest = orders[0];
          if (initial) {
            this.lastKnownOrderId = latest.id;
          } else if (this.lastKnownOrderId !== null && latest.id > this.lastKnownOrderId) {
            this.lastKnownOrderId = latest.id;
            this.playNewOrderSound();
            this.newOrderNotification = {
              order_number: latest.order_number,
              customer_name: latest.customer_name,
            };
            setTimeout(() => {
              this.newOrderNotification = null;
            }, 10000);
          }
        }
      } catch (err) {
        // Polling silencioso
      }
    },
    goToOrdersFromNotification() {
      this.newOrderNotification = null;
      this.currentView = 'admin-orders';
      window.scrollTo({ top: 0, behavior: 'smooth' });
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

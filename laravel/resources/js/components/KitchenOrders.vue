<template>
  <div class="kitchen-orders pb-5">
    
    <!-- CABEÇALHO PADRÃO DO SISTEMA (MODERNO, CLEAN & FLEX) -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center">
          <span class="badge bg-danger text-white me-2 rounded-circle p-2 shadow-sm">👨‍🍳</span> Painel da Cozinha
        </h4>
        <small class="text-muted">Fila de pedidos em preparação com ingredientes e montagem em tempo real</small>
      </div>

      <!-- Ações do Cabeçalho: Relógio, Modo TV e Atualizar -->
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Relógio Digital da Cozinha -->
        <div class="bg-white border rounded-pill px-3 py-1.5 shadow-sm small fw-bold text-dark d-flex align-items-center gap-1.5 font-monospace">
          <i class="bi bi-clock-fill text-warning"></i>
          <span>{{ currentTimeStr }}</span>
        </div>

        <!-- Alternador de Tamanho para TV (Alta Visibilidade à Distância) -->
        <button
          type="button"
          class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold border shadow-sm transition"
          :class="isTvMode ? 'btn-dark text-white' : 'btn-white bg-white text-muted'"
          @click="isTvMode = !isTvMode"
          :title="isTvMode ? 'Voltar para modo padrão' : 'Modo TV: fontes e cards ampliados para visualização à distância'"
        >
          <i class="bi bi-tv me-1" :class="isTvMode ? 'text-warning' : 'text-primary'"></i>
          <span>{{ isTvMode ? 'Modo TV Ativo' : 'Modo TV' }}</span>
        </button>

        <!-- Botão Atualizar Manualmente -->
        <button
          class="btn btn-outline-secondary rounded-pill btn-sm px-3 shadow-sm bg-white"
          @click="fetchOrders(false)"
          :disabled="loading"
        >
          <i class="bi bi-arrow-clockwise me-1" :class="{'spin': loading}"></i> Atualizar
        </button>
      </div>
    </div>

    <!-- SELETOR DE ABAS PRINCIPAL (SEGMENTED CONTROL FLEX) -->
    <div class="bg-white p-1 rounded-4 shadow-sm border mb-3 d-flex gap-1 w-100" style="max-width: 480px;">
      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', activeTab === 'preparing' ? 'btn-danger text-white shadow-sm' : 'btn-light text-dark']"
        @click="activeTab = 'preparing'"
      >
        <i class="bi bi-fire me-1"></i> Em Preparo ({{ preparingOrders.length }})
      </button>

      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', activeTab === 'recent_ready' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark']"
        @click="activeTab = 'recent_ready'"
      >
        <i class="bi bi-check2-circle me-1"></i> Prontos Recentemente ({{ recentReadyOrders.length }})
      </button>
    </div>

    <!-- FILTRO RÁPIDO POR COZINHEIRO RESPONSÁVEL (FLEX PILLS) -->
    <div v-if="activeTab === 'preparing' && kitchenStaffList.length > 0" class="d-flex align-items-center gap-1.5 flex-wrap mb-4">
      <span class="small text-muted fw-bold me-1">
        <i class="bi bi-funnel-fill text-danger me-1"></i>Filtrar Cozinha:
      </span>

      <!-- Todos -->
      <button
        type="button"
        class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border shadow-none transition"
        :class="selectedStaffFilter === 'all' ? 'btn-danger text-white shadow-sm' : 'btn-white bg-white text-muted'"
        style="font-size: 0.78rem;"
        @click="selectedStaffFilter = 'all'"
      >
        Todos ({{ preparingOrders.length }})
      </button>

      <!-- Cozinha Geral / Sem Operador Específico -->
      <button
        type="button"
        class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border shadow-none transition"
        :class="selectedStaffFilter === 'unassigned' ? 'btn-danger text-white shadow-sm' : 'btn-white bg-white text-muted'"
        style="font-size: 0.78rem;"
        @click="selectedStaffFilter = 'unassigned'"
      >
        Geral / Não Atribuídos ({{ countUnassigned }})
      </button>

      <!-- Pílula para cada Operador de Cozinha -->
      <button
        v-for="staff in kitchenStaffList"
        :key="'staff-btn-' + staff.id"
        type="button"
        class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border shadow-none transition"
        :class="selectedStaffFilter === staff.id ? 'btn-danger text-white shadow-sm' : 'btn-white bg-white text-muted'"
        style="font-size: 0.78rem;"
        @click="selectedStaffFilter = staff.id"
      >
        👨‍🍳 {{ staff.name }} ({{ countByStaff(staff.id) }})
      </button>
    </div>

    <!-- ======================================================== -->
    <!-- ABA 1: PEDIDOS EM PREPARAÇÃO (KDS / COZINHA)             -->
    <!-- ======================================================== -->
    <div v-if="activeTab === 'preparing'">
      
      <!-- Loading State -->
      <div v-if="loading && preparingOrders.length === 0" class="text-center py-5">
        <div class="spinner-border text-danger" role="status"></div>
        <p class="text-muted small mt-2">Buscando pedidos da cozinha...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredPreparingOrders.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <div class="mb-3">
          <i class="bi bi-check-circle-fill text-success fs-1"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">Cozinha em Dia! Nenhum Pedido na Fila</h5>
        <p class="text-muted small mb-0" style="max-width: 480px; margin: 0 auto;">
          Assim que o atendente ou administrador passar um pedido para o status <strong>"Em Preparação"</strong>, ele aparecerá aqui com os ingredientes e itens detalhados.
        </p>
      </div>

      <!-- GRADE FLEXÍVEL DE CARDS DE PREPARO (PADRÃO AKY LANCHES) -->
      <div v-else class="row g-3">
        <div
          v-for="order in filteredPreparingOrders"
          :key="'kds-order-' + order.id"
          class="col-12 col-md-6 col-lg-4 d-flex"
        >
          <div
            :class="[
              'card border-0 border-start border-4 shadow-sm rounded-4 bg-white w-100 p-3 d-flex flex-column justify-content-between transition',
              orderBorderClass(order),
              isTvMode ? 'kds-tv-mode' : ''
            ]"
          >
            <!-- TOPO DO CARD: CÓDIGO DO PEDIDO, TEMPO E TIPO -->
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge font-monospace fs-6 px-2.5 py-1 shadow-sm rounded-pill" :class="orderBadgeClass(order)">
                  #{{ order.order_number }}
                </span>

                <div class="d-flex align-items-center gap-1.5">
                  <!-- Cronômetro com tempo decorrido -->
                  <small class="text-muted fw-semibold" :class="getOrderMinutes(order) >= 20 ? 'text-danger fw-bold' : ''">
                    <i class="bi bi-clock me-1"></i>{{ formatElapsedTime(order.created_at) }}
                  </small>
                  
                  <!-- Botão Discreto de Impressão de Cupom para a Cozinha -->
                  <button
                    type="button"
                    class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-none text-muted transition"
                    style="width: 28px; height: 28px;"
                    @click.stop="printTicket(order)"
                    title="Imprimir cupom descritivo para a cozinha"
                  >
                    <i class="bi bi-printer"></i>
                  </button>
                </div>
              </div>

              <!-- NOME DO CLIENTE E TIPO DO PEDIDO (RETIRADA VS ENTREGA) -->
              <div class="d-flex justify-content-between align-items-center gap-2 mb-2 pb-1 border-bottom">
                <h6 class="fw-bold text-dark mb-0 text-truncate" :title="order.customer_name">
                  <i class="bi bi-person text-muted me-1"></i>{{ order.customer_name }}
                </h6>
                <span
                  class="badge rounded-pill px-2.5 py-1 fw-semibold flex-shrink-0"
                  :class="isPickupOrder(order) ? 'bg-dark text-warning' : 'bg-primary text-white'"
                  style="font-size: 0.72rem;"
                >
                  <i :class="isPickupOrder(order) ? 'bi-shop' : 'bi-bicycle'" class="me-1"></i>
                  {{ isPickupOrder(order) ? 'Retirada no Balcão' : 'Entrega' }}
                </span>
              </div>

              <!-- RESPONSÁVEL PELO PREPARO (COZINHEIRO ATRIBUÍDO) -->
              <div class="p-2 rounded-3 bg-light border d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2 min-w-0">
                  <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-1 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <i class="bi bi-fire fs-6"></i>
                  </div>
                  <div class="min-w-0">
                    <small class="text-muted d-block" style="font-size: 0.68rem;">Responsável pelo Preparo:</small>
                    <span class="fw-bold small text-dark text-truncate d-block">
                      {{ order.kitchen_user ? order.kitchen_user.name : 'Cozinha Geral (Não atribuído)' }}
                    </span>
                  </div>
                </div>

                <button
                  type="button"
                  class="btn btn-sm btn-white border rounded-pill px-2.5 py-0.5 text-danger fw-bold shadow-none"
                  style="font-size: 0.72rem;"
                  @click="openReassignModal(order)"
                  title="Alterar responsável da cozinha"
                >
                  {{ order.kitchen_user ? 'Alterar' : 'Atribuir' }}
                </button>
              </div>

              <!-- ITENS DO PEDIDO COM INGREDIENTES E MONTAGEM EM DESTAQUE -->
              <div class="bg-light rounded-3 p-2.5 small mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary-subtle">
                  <span class="fw-bold text-dark" style="font-size: 0.75rem;">
                    <i class="bi bi-card-checklist text-danger me-1"></i> ITENS DO PEDIDO:
                  </span>
                  <span class="badge bg-secondary bg-opacity-25 text-dark rounded-pill" style="font-size: 0.68rem;">
                    {{ order.items ? order.items.length : 0 }} {{ order.items && order.items.length === 1 ? 'item' : 'itens' }}
                  </span>
                </div>

                <div class="d-flex flex-column gap-2">
                  <div
                    v-for="(item, idx) in order.items"
                    :key="'kit-item-' + (item.id || idx)"
                    class="bg-white p-2 rounded-3 border"
                  >
                    <!-- Quantidade e Nome do Produto -->
                    <div class="d-flex align-items-start gap-2">
                      <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 fw-bold flex-shrink-0" style="font-size: 0.78rem;">
                        {{ item.quantity }}x
                      </span>

                      <div class="flex-grow-1 min-w-0">
                        <span class="fw-bold text-dark d-block" style="font-size: 0.88rem;">
                          {{ item.product_name_snapshot }}
                        </span>

                        <!-- DESCRIÇÃO COMPLETA COM INGREDIENTES DO PRODUTO CADASTRADO -->
                        <div v-if="getProductDescription(item)" class="text-muted small my-1 p-1 bg-light rounded fst-italic" style="font-size: 0.74rem; line-height: 1.3;">
                          <strong class="text-dark">Ingredientes:</strong> {{ getProductDescription(item) }}
                        </div>

                        <!-- Sabores, Bordas, Tamanhos e Adicionais Customizados -->
                        <div v-if="hasItemDetails(item)" class="mt-1 pt-1 border-top border-light-subtle">
                          <div
                            v-for="(detail, dIdx) in formatItemDetailsLines(item)"
                            :key="'det-' + dIdx"
                            class="badge bg-light text-primary border rounded-pill px-2 py-0.5 me-1 mb-1"
                            style="font-size: 0.7rem;"
                          >
                            {{ detail }}
                          </div>
                        </div>

                        <!-- Observação Individual do Item (Ex: "Sem cebola", "Sem maionese") -->
                        <div v-if="item.notes" class="mt-1 text-danger fw-bold small" style="font-size: 0.74rem;">
                          <i class="bi bi-exclamation-circle-fill me-1"></i> Obs: {{ item.notes }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- OBSERVAÇÃO GERAL DO PEDIDO (ALERTA EM DESTAQUE) -->
                <div v-if="order.notes && order.notes.trim().length > 0" class="mt-2 pt-2 border-top text-danger">
                  <div class="p-2 rounded-2 bg-warning bg-opacity-25 border border-warning text-dark small">
                    <span class="fw-bold text-danger d-block mb-0.5" style="font-size: 0.72rem;">
                      <i class="bi bi-exclamation-triangle-fill me-1"></i> OBSERVAÇÃO DO PEDIDO:
                    </span>
                    <span class="fw-bold text-dark fst-italic">"{{ order.notes }}"</span>
                  </div>
                </div>
              </div>

            </div>

            <!-- RODAPÉ DO CARD: BOTÃO IMPRIMIR CUPOM E CONCLUIR PREPARO -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
              <!-- Botão Discreto de Impressão do Cupom -->
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-muted fw-semibold d-inline-flex align-items-center gap-1 shadow-none"
                style="font-size: 0.74rem;"
                @click.stop="printTicket(order)"
                title="Imprimir cupom descritivo para a cozinha"
              >
                <i class="bi bi-printer text-dark"></i>
                <span>Cupom</span>
              </button>

              <!-- Botão Concluir Preparo / Pedido Pronto -->
              <button
                type="button"
                class="btn btn-sm btn-success rounded-pill px-3.5 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1"
                :disabled="actionLoadingId === order.id"
                @click="markOrderReady(order)"
              >
                <span v-if="actionLoadingId === order.id" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check2-circle me-1"></i>
                <span>{{ isPickupOrder(order) ? 'Pronto no Balcão' : 'Pronto p/ Entrega' }}</span>
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>

    <!-- ======================================================== -->
    <!-- ABA 2: PRONTOS RECENTEMENTE                              -->
    <!-- ======================================================== -->
    <div v-else-if="activeTab === 'recent_ready'">
      <div v-if="recentReadyOrders.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-clock-history fs-1 text-muted d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhum pedido finalizado nas últimas 2 horas</h6>
      </div>

      <div v-else class="row g-3">
        <div
          v-for="order in recentReadyOrders"
          :key="'ready-' + order.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div class="card border-0 border-start border-3 border-success shadow-sm rounded-4 bg-white p-3 h-100 d-flex flex-column justify-content-between opacity-85">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-dark font-monospace fs-6 px-2.5 py-1">#{{ order.order_number }}</span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2.5 py-1 fw-bold small">
                  <i class="bi bi-check2-all me-1"></i> Pronto
                </span>
              </div>
              <h6 class="fw-bold text-dark text-truncate mb-1">{{ order.customer_name }}</h6>
              <small class="text-muted d-block mb-2">Finalizado às {{ formatTime(order.updated_at) }}</small>

              <div class="bg-light rounded-3 p-2 small mb-2 text-muted">
                <div v-for="it in order.items" :key="it.id">
                  {{ it.quantity }}x {{ it.product_name_snapshot }}
                </div>
              </div>
            </div>

            <div class="text-end pt-2 border-top">
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted small"
                @click="printTicket(order)"
              >
                <i class="bi bi-printer me-1"></i> Reimprimir Cupom
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE ATRIBUIÇÃO DE COZINHA / PREPARO (ESTILO IDÊNTICO AO DE MOTOBOY) -->
    <div v-if="showKitchenModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0 rounded-4 overflow-hidden">
          
          <div class="modal-header bg-danger text-white border-0 py-3">
            <h6 class="modal-title fw-bold d-flex align-items-center">
              <i class="bi bi-fire me-2 fs-5"></i> Selecionar Cozinha / Preparo
            </h6>
            <button type="button" class="btn-close btn-close-white shadow-none" @click="showKitchenModal = false"></button>
          </div>

          <div class="modal-body p-3 bg-light">
            
            <!-- Barra de Busca no Modal -->
            <div class="input-group input-group-sm mb-3 shadow-sm rounded-pill overflow-hidden bg-white border">
              <span class="input-group-text bg-white border-0 ps-3">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-0 shadow-none ps-2"
                placeholder="Buscar por nome do cozinheiro..."
                v-model="kitchenSearchTerm"
              >
            </div>

            <!-- Botão para Deixar Cozinha Geral -->
            <div class="mb-2">
              <button
                class="btn btn-outline-secondary btn-sm w-100 rounded-pill py-2 text-start px-3 d-flex align-items-center justify-content-between"
                @click="assignSelectedKitchen(null)"
              >
                <span><i class="bi bi-fire me-1 text-danger"></i> Cozinha Geral (Sem operador)</span>
                <i class="bi bi-chevron-right small text-muted"></i>
              </button>
            </div>

            <hr class="my-2 opacity-25">

            <!-- Lista de Cozinheiros em Cards Clicáveis -->
            <div class="d-flex flex-column gap-2" style="max-height: 260px; overflow-y: auto;">
              <div v-if="filteredKitchenStaff.length === 0" class="text-center py-4 text-muted small">
                Nenhum operador da cozinha encontrado com "{{ kitchenSearchTerm }}".
              </div>

              <div
                v-for="k in filteredKitchenStaff"
                :key="'sel-kit-kds-' + k.id"
                class="card border-0 shadow-sm rounded-3 p-2 bg-white cursor-pointer transition hover-card"
                :class="selectedOrderForKitchen && selectedOrderForKitchen.kitchen_user_id === k.id ? 'border-danger border-2 bg-danger bg-opacity-10' : ''"
                @click="assignSelectedKitchen(k.id)"
              >
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2 min-w-0">
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                      👨‍🍳
                    </div>
                    <div class="min-w-0">
                      <div class="fw-bold text-dark small text-truncate">{{ k.name }}</div>
                      <small class="text-muted d-block" style="font-size: 0.68rem;">{{ k.email || 'Operador da Cozinha' }}</small>
                    </div>
                  </div>
                  <i class="bi bi-check-circle-fill text-danger fs-5" v-if="selectedOrderForKitchen && selectedOrderForKitchen.kitchen_user_id === k.id"></i>
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer border-0 p-2 bg-white text-center">
            <button class="btn btn-light btn-sm rounded-pill w-100 text-muted" @click="showKitchenModal = false">
              Fechar
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import { defaultPrintSettings, printThermalTicket } from '../utils/printer';
import { showSuccess, showError } from '../utils/swal';

export default {
  name: 'KitchenOrders',
  data() {
    return {
      activeTab: 'preparing',
      preparingOrders: [],
      recentReadyOrders: [],
      kitchenStaffList: [],
      selectedStaffFilter: 'all', // 'all', 'unassigned' ou staff.id
      loading: false,
      actionLoadingId: null,
      pollingInterval: null,
      clockInterval: null,
      currentTimeStr: '',
      nowTimestamp: Date.now(),
      isTvMode: false,
      showKitchenModal: false,
      selectedOrderForKitchen: null,
      kitchenSearchTerm: '',
      printSettings: null,
      storeInfo: null,
      knownOrderIds: new Set(),
    };
  },
  computed: {
    countUnassigned() {
      return this.preparingOrders.filter(o => !o.kitchen_user_id).length;
    },
    filteredPreparingOrders() {
      if (this.selectedStaffFilter === 'all') {
        return this.preparingOrders;
      }
      if (this.selectedStaffFilter === 'unassigned') {
        return this.preparingOrders.filter(o => !o.kitchen_user_id);
      }
      return this.preparingOrders.filter(o => o.kitchen_user_id === this.selectedStaffFilter);
    },
    filteredKitchenStaff() {
      if (!this.kitchenSearchTerm.trim()) return this.kitchenStaffList;
      const term = this.kitchenSearchTerm.toLowerCase();
      return this.kitchenStaffList.filter(k =>
        k.name.toLowerCase().includes(term) ||
        (k.email && k.email.toLowerCase().includes(term))
      );
    },
  },
  mounted() {
    this.updateClock();
    this.clockInterval = setInterval(() => {
      this.updateClock();
      this.nowTimestamp = Date.now();
    }, 1000);

    this.fetchOrders(false);
    this.fetchStoreSettings();
    this.startPolling();
  },
  beforeUnmount() {
    if (this.pollingInterval) clearInterval(this.pollingInterval);
    if (this.clockInterval) clearInterval(this.clockInterval);
  },
  methods: {
    updateClock() {
      const now = new Date();
      this.currentTimeStr = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    },
    countByStaff(staffId) {
      return this.preparingOrders.filter(o => o.kitchen_user_id === staffId).length;
    },
    async fetchStoreSettings() {
      try {
        const res = await axios.get('/api/admin/store-info');
        if (res.data) {
          if (res.data.print_settings) this.printSettings = res.data.print_settings;
          if (res.data.store_info) this.storeInfo = res.data.store_info;
        }
      } catch (err) {
        // silencioso
      }
    },
    async fetchOrders(silent = true) {
      if (!silent) this.loading = true;
      try {
        const res = await axios.get('/api/kitchen/orders');
        if (res.data) {
          this.preparingOrders = res.data.preparing || [];
          this.recentReadyOrders = res.data.recent_ready || [];
          this.kitchenStaffList = res.data.kitchen_staff || [];
        }
      } catch (err) {
        console.error('Erro ao buscar pedidos da cozinha:', err);
      } finally {
        if (!silent) this.loading = false;
      }
    },
    startPolling() {
      this.pollingInterval = setInterval(() => {
        this.fetchOrders(true);
      }, 7000);
    },
    getProductDescription(item) {
      if (item && item.product && item.product.description) {
        return item.product.description;
      }
      return '';
    },
    async markOrderReady(order) {
      this.actionLoadingId = order.id;
      try {
        await axios.patch(`/api/kitchen/orders/${order.id}/ready`);
        showSuccess('Pedido Pronto!', `Pedido #${order.order_number} concluído pela cozinha.`);
        this.fetchOrders(true);
      } catch (err) {
        showError('Erro ao finalizar', err.response?.data?.message || 'Não foi possível atualizar o pedido.');
      } finally {
        this.actionLoadingId = null;
      }
    },
    async printTicket(order) {
      try {
        await printThermalTicket(order, this.printSettings || defaultPrintSettings, this.storeInfo || {});
      } catch (err) {
        console.error('Erro ao imprimir cupom da cozinha:', err);
      }
    },
    openReassignModal(order) {
      this.selectedOrderForKitchen = order;
      this.kitchenSearchTerm = '';
      this.showKitchenModal = true;
    },
    async assignSelectedKitchen(kitchenUserId) {
      if (!this.selectedOrderForKitchen) return;
      const order = this.selectedOrderForKitchen;
      try {
        await axios.patch(`/api/admin/orders/${order.id}/assign-kitchen`, {
          kitchen_user_id: kitchenUserId,
          status: 'em_preparacao',
        });
        this.showKitchenModal = false;
        order.kitchen_user_id = kitchenUserId;
        this.fetchOrders(true);
        showSuccess('Responsável Atualizado', 'Pedido direcionado com sucesso.');
      } catch (err) {
        showError('Erro ao atribuir', err.response?.data?.message || 'Não foi possível atribuir o responsável.');
      }
    },
    isPickupOrder(order) {
      return order.delivery_address && order.delivery_address.toUpperCase().includes('RETIRADA');
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },
    formatElapsedTime(createdStr) {
      if (!createdStr) return '0 min';
      const created = new Date(createdStr).getTime();
      const diffMs = Math.max(0, this.nowTimestamp - created);
      const minutes = Math.floor(diffMs / 60000);
      return `${minutes} min`;
    },
    getOrderMinutes(order) {
      if (!order.created_at) return 0;
      const created = new Date(order.created_at).getTime();
      return Math.floor((this.nowTimestamp - created) / 60000);
    },
    orderBorderClass(order) {
      const mins = this.getOrderMinutes(order);
      if (mins < 15) return 'border-success';
      if (mins < 25) return 'border-warning';
      return 'border-danger';
    },
    orderBadgeClass(order) {
      const mins = this.getOrderMinutes(order);
      if (mins < 15) return 'bg-success text-white';
      if (mins < 25) return 'bg-warning text-dark';
      return 'bg-danger text-white';
    },
    hasItemDetails(item) {
      if (!item.details) return false;
      if (typeof item.details === 'string' && item.details.trim().length > 0) return true;
      if (typeof item.details === 'object') {
        return !!(item.details.size || item.details.border || (item.details.flavors && item.details.flavors.length) || (item.details.extras && item.details.extras.length));
      }
      return false;
    },
    formatItemDetailsLines(item) {
      const lines = [];
      if (!item.details) return lines;
      if (typeof item.details === 'string') {
        lines.push(item.details);
        return lines;
      }
      if (item.details.size) lines.push(`Tamanho: ${item.details.size}`);
      if (item.details.border) lines.push(`Borda: ${item.details.border}`);
      if (item.details.flavors && Array.isArray(item.details.flavors)) {
        lines.push(`Sabores: ${item.details.flavors.join(' / ')}`);
      }
      if (item.details.extras && Array.isArray(item.details.extras)) {
        lines.push(`Adicionais: ${item.details.extras.join(', ')}`);
      }
      return lines;
    },
  },
};
</script>

<style scoped>
.hover-card:hover {
  background-color: #f8f9fa !important;
  transform: translateY(-1px);
}

.kds-tv-mode {
  transform: scale(1.02);
}

.kds-tv-mode h6 {
  font-size: 1.15rem !important;
}

.kds-tv-mode .badge {
  font-size: 0.85rem !important;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}
</style>

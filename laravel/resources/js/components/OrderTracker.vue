<template>
  <div class="card border-0 shadow-sm rounded-4 p-4 my-4 bg-white">
    
    <!-- Cabeçalho -->
    <div class="d-flex align-items-center mb-3">
      <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-2 text-aky-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
        <i class="bi bi-clock-history fs-4"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0 text-dark">Acompanhar Pedido</h5>
        <small class="text-muted">Consulte o status em tempo real</small>
      </div>
    </div>

    <!-- Barra de Busca Compacta (Apenas o Ícone de Lupa no Botão) -->
    <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden border">
      <input
        type="text"
        class="form-control text-uppercase font-monospace border-0 shadow-none ps-3"
        placeholder="Digite o código (ex: AKY-20261029-ABCD)"
        v-model="orderNumber"
        @keyup.enter="searchOrder"
      >
      <button class="btn btn-aky-primary px-3 px-sm-4 border-0 d-flex align-items-center justify-content-center" @click="searchOrder" :disabled="loading" title="Consultar">
        <span v-if="loading" class="spinner-border spinner-border-sm"></span>
        <i v-else class="bi bi-search fs-5"></i>
      </button>
    </div>

    <div v-if="error" class="alert alert-warning py-2 small rounded-3">
      <i class="bi bi-info-circle me-1"></i> {{ error }}
    </div>

    <!-- Resultado do Pedido -->
    <div v-if="order" class="card bg-light border-0 rounded-4 p-3 mt-2 shadow-sm">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
          <span class="text-muted small d-block">Código do Pedido:</span>
          <span class="fw-bold fs-5 font-monospace text-aky-primary">{{ order.order_number }}</span>
        </div>
        <div class="text-end">
          <span class="text-muted small d-block">Horário do Pedido:</span>
          <span class="small text-dark fw-semibold">{{ formatDate(order.created_at) }}</span>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- EXIBIÇÃO EXCLUSIVA DO STATUS ATUAL (APENAS A INFORMAÇÃO ATUAL) -->
      <!-- ======================================================== -->

      <!-- 1. STATUS: RECEBIDO -->
      <div v-if="order.status === 'recebido'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-warning">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-warning bg-opacity-25 text-dark p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-receipt fs-2 text-warning"></i>
          </div>
          <div>
            <span class="badge bg-warning text-dark rounded-pill mb-1 fw-bold">Status Atual: Recebido</span>
            <h6 class="fw-bold text-dark mb-1">Seu pedido foi confirmado!</h6>
            <p class="text-muted small mb-0">Sua comanda já entrou na fila da cozinha e em instantes começará a ser preparada.</p>
          </div>
        </div>
      </div>

      <!-- 2. STATUS: EM PREPARAÇÃO -->
      <div v-else-if="order.status === 'em_preparacao'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-primary">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-primary bg-opacity-25 text-primary p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-fire fs-2 text-primary"></i>
          </div>
          <div>
            <span class="badge bg-primary rounded-pill mb-1 fw-bold">Status Atual: Em Preparo</span>
            <h6 class="fw-bold text-dark mb-1">Preparando seus lanches!</h6>
            <p class="text-muted small mb-0">Nossos chapeiros e cozinheiros estão montando e fritando seus itens na hora com todo o capricho.</p>
          </div>
        </div>
      </div>

      <!-- 3. STATUS: SAIU PARA ENTREGA (COM IDENTIFICAÇÃO DO MOTOBOY E ANIMAÇÃO DA MOTO) -->
      <div v-else-if="order.status === 'saiu_para_entrega'" class="delivery-track-box my-3 p-3 bg-white rounded-4 shadow-sm border border-info border-start border-4 border-info">
        
        <!-- Identificação do Motoboy -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-info bg-opacity-25 p-2 text-dark me-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
              <i class="bi bi-bicycle fs-4 text-info"></i>
            </div>
            <div>
              <span class="badge bg-info text-dark rounded-pill mb-1">Status Atual: A Caminho</span>
              <h6 class="fw-bold text-dark mb-0 fs-6">
                Entregador: {{ order.motoboy ? order.motoboy.name : 'Equipe AKY Lanches' }}
              </h6>
            </div>
          </div>
          <span class="text-muted small">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i> Indo até o seu endereço
          </span>
        </div>

        <!-- CENÁRIO COM A RUA E A MOTO ANIMADA EM MOVIMENTO -->
        <div class="street-container position-relative overflow-hidden rounded-3">
          <div class="asphalt">
            <div class="road-lines"></div>
          </div>

          <div class="moto-vehicle">
            <div class="moto-wrapper">
              <span class="moto-icon">🛵</span>
            </div>
          </div>

          <div class="destination-house">
            <i class="bi bi-house-door-fill text-white fs-5"></i>
          </div>
        </div>

        <div class="text-center mt-2">
          <small class="text-muted fst-italic">
            <i class="bi bi-hourglass-split text-warning me-1"></i> Seu pedido está quentinho na mochila térmica e já está chegando!
          </small>
        </div>
      </div>

      <!-- 4. STATUS: CONCLUÍDO -->
      <div v-else-if="order.status === 'concluido'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-success">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-success bg-opacity-25 text-success p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-check-circle-fill fs-2 text-success"></i>
          </div>
          <div>
            <span class="badge bg-success rounded-pill mb-1 fw-bold">Status Atual: Concluído</span>
            <h6 class="fw-bold text-dark mb-1">Pedido Entregue!</h6>
            <p class="text-muted small mb-0">Bom apetite! Muito obrigado pela preferência e volte sempre ao AKY Lanches.</p>
          </div>
        </div>
      </div>

      <!-- 5. STATUS: CANCELADO -->
      <div v-else-if="order.status === 'cancelado'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-danger">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-danger bg-opacity-25 text-danger p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-x-circle-fill fs-2 text-danger"></i>
          </div>
          <div>
            <span class="badge bg-danger rounded-pill mb-1 fw-bold">Status: Cancelado</span>
            <h6 class="fw-bold text-dark mb-1">Este pedido foi cancelado</h6>
            <p class="text-muted small mb-0">Se precisar de qualquer esclarecimento, fale com nossa equipe de atendimento.</p>
          </div>
        </div>
      </div>

      <!-- Detalhes dos Itens do Pedido -->
      <div class="bg-white p-3 rounded-4 shadow-sm mt-3">
        <h6 class="fw-bold text-dark mb-2">Itens Solicitados</h6>
        <div class="small">
          <div v-for="item in order.items" :key="item.id" class="d-flex justify-content-between py-1 border-bottom">
            <span>{{ item.quantity }}x {{ item.product_name_snapshot }}</span>
            <span class="fw-semibold">R$ {{ Number(item.subtotal).toFixed(2).replace('.', ',') }}</span>
          </div>
          <div class="d-flex justify-content-between pt-2 mt-1 fw-bold fs-6 text-dark">
            <span>Total:</span>
            <span class="text-aky-primary">R$ {{ Number(order.total).toFixed(2).replace('.', ',') }}</span>
          </div>
        </div>

        <div class="mt-3 pt-2 border-top small text-muted">
          <div><i class="bi bi-geo-alt me-1 text-danger"></i> <strong>Entrega:</strong> {{ order.delivery_address }} <span v-if="order.delivery_complement">({{ order.delivery_complement }})</span></div>
          <div v-if="order.delivery_reference" class="mt-1"><i class="bi bi-signpost me-1"></i> <strong>Referência:</strong> {{ order.delivery_reference }}</div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    initialCode: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      orderNumber: this.initialCode,
      order: null,
      loading: false,
      error: null,
    };
  },
  watch: {
    initialCode(val) {
      if (val) {
        this.orderNumber = val;
        this.searchOrder();
      }
    },
  },
  mounted() {
    if (this.orderNumber) {
      this.searchOrder();
    }
  },
  methods: {
    async searchOrder() {
      if (!this.orderNumber.trim()) return;

      this.loading = true;
      this.error = null;
      this.order = null;

      try {
        const res = await axios.get(`/api/orders/${this.orderNumber.trim()}`);
        this.order = res.data.order;
      } catch (err) {
        this.error = 'Pedido não encontrado. Verifique o código e tente novamente.';
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('pt-BR') + ' às ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },
  },
};
</script>

<style scoped>
/* ========================================= */
/* ANIMAÇÃO CSS DA MOTO ANDANDO PELA RUA      */
/* ========================================= */
.street-container {
  height: 80px;
  background: #2b2d42;
  position: relative;
  border: 2px solid #3a0ca3;
}

.asphalt {
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 4px;
  transform: translateY(-50%);
}

.road-lines {
  width: 200%;
  height: 4px;
  background: repeating-linear-gradient(
    90deg,
    #ffffff,
    #ffffff 20px,
    transparent 20px,
    transparent 40px
  );
  animation: moveRoad 1.2s linear infinite;
}

@keyframes moveRoad {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-50%);
  }
}

.moto-vehicle {
  position: absolute;
  top: 50%;
  left: 20%;
  transform: translateY(-55%);
  animation: motoBounce 0.4s ease-in-out infinite alternate, motoDrive 4s ease-in-out infinite alternate;
}

.moto-wrapper {
  position: relative;
  display: inline-block;
}

.moto-icon {
  font-size: 2.2rem;
  display: inline-block;
  transform: scaleX(-1);
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.4));
}

.destination-house {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  background: #e63946;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 10px rgba(230, 57, 70, 0.8);
}

@keyframes motoBounce {
  from {
    transform: translateY(-55%);
  }
  to {
    transform: translateY(-62%);
  }
}

@keyframes motoDrive {
  0% {
    left: 15%;
  }
  50% {
    left: 45%;
  }
  100% {
    left: 65%;
  }
}
</style>

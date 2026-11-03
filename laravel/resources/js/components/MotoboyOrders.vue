<template>
  <div class="motoboy-orders pb-5">
    
    <!-- Cabeçalho do Entregador -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center">
          <span class="badge bg-warning text-dark me-2 rounded-circle p-2">🛵</span> Minhas Entregas
        </h4>
        <small class="text-muted">Consulte seus pedidos, veja o mapa da rota e confirme entregas</small>
      </div>

      <button class="btn btn-outline-secondary rounded-pill btn-sm px-3 shadow-sm bg-white" @click="fetchOrders" :disabled="loading">
        <i class="bi bi-arrow-clockwise me-1" :class="{'spin': loading}"></i> Atualizar
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-aky-primary" role="status"></div>
      <p class="text-muted small mt-2">Buscando suas entregas...</p>
    </div>

    <!-- Lista Vazia -->
    <div v-else-if="orders.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
      <div class="mb-3">
        <i class="bi bi-bicycle fs-1 text-muted opacity-50"></i>
      </div>
      <h6 class="text-dark fw-bold mb-1">Nenhuma entrega atribuída a você no momento</h6>
      <p class="text-muted small mb-0">Quando a cozinha despachar um pedido para você, ele aparecerá aqui!</p>
    </div>

    <!-- Grade de Cards das Entregas do Motoboy -->
    <div v-else class="row g-3">
      <div
        v-for="order in orders"
        :key="'moto-order-' + order.id"
        class="col-12 col-md-6 col-lg-6"
      >
        <div :class="['card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden', order.status === 'concluido' ? 'opacity-75 border-start border-4 border-success' : 'border-start border-4 border-warning']">
          
          <!-- Topo do Card -->
          <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0 d-flex justify-content-between align-items-center">
            <span class="badge font-monospace fs-6 px-2 py-1 shadow-sm" :class="order.status === 'concluido' ? 'bg-success text-white' : 'bg-warning text-dark'">
              {{ order.order_number }}
            </span>
            <span class="badge rounded-pill px-3 py-1" :class="order.status === 'concluido' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-25 text-dark fw-bold'">
              {{ order.status === 'concluido' ? 'Entregue' : 'Em Rota de Entrega' }}
            </span>
          </div>

          <div class="card-body p-3">
            
            <!-- Dados do Cliente -->
            <div class="bg-light rounded-3 p-3 mb-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="fw-bold text-dark mb-0 fs-6">{{ order.customer_name }}</h6>
                  <span class="small text-muted"><i class="bi bi-telephone text-primary me-1"></i> {{ order.customer_phone }}</span>
                </div>
                <!-- Botão WhatsApp / Ligar Direto -->
                <a
                  :href="'https://wa.me/55' + sanitizePhone(order.customer_phone)"
                  target="_blank"
                  class="btn btn-success btn-sm rounded-pill px-3 fw-bold d-flex align-items-center shadow-sm"
                >
                  <i class="bi bi-whatsapp me-1"></i> Chamar
                </a>
              </div>

              <!-- Endereço Completo -->
              <div class="border-top pt-2 mt-2">
                <div class="fw-bold text-dark small">
                  <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ order.delivery_address }}
                </div>
                <div v-if="order.delivery_complement" class="small text-muted mt-1">
                  <strong>Compl:</strong> {{ order.delivery_complement }}
                </div>
                <div v-if="order.delivery_reference" class="small text-muted mt-1">
                  <strong>Ref:</strong> {{ order.delivery_reference }}
                </div>
              </div>
            </div>

            <!-- MINIMAPA MOSTRANDO O ENDEREÇO DA ENTREGA -->
            <div class="minimap-card rounded-3 overflow-hidden border mb-3 position-relative shadow-sm" style="height: 160px; background: #e9ecef;">
              <iframe
                width="100%"
                height="100%"
                frameborder="0"
                scrolling="no"
                marginheight="0"
                marginwidth="0"
                :src="'https://maps.google.com/maps?q=' + encodeURIComponent(order.delivery_address) + '&t=&z=15&ie=UTF8&iwloc=&output=embed'"
                style="border:0;"
                loading="lazy"
              ></iframe>
            </div>

            <!-- Botão de Traçar Rota no GPS (Google Maps / Waze) -->
            <div class="mb-3">
              <a
                :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(order.delivery_address)"
                target="_blank"
                class="btn btn-outline-primary w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center shadow-sm"
              >
                <i class="bi bi-map-fill me-2 fs-5"></i> Abrir Rota no Google Maps / Waze
              </a>
            </div>

            <!-- Resumo Financeiro da Entrega -->
            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3 mb-3">
              <span class="small text-muted">Total a receber do cliente:</span>
              <span class="fw-bold text-aky-primary fs-5">R$ {{ formatPrice(order.total) }}</span>
            </div>

            <!-- Confirmações Antifraude e Avaliação -->
            <div class="mb-3 p-2 rounded-3 bg-light border small">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span><i class="bi bi-bicycle text-info me-1"></i> <strong>Sua Confirmação:</strong></span>
                <span v-if="order.motoboy_confirmed_at" class="badge bg-success">Confirmado ✓</span>
                <span v-else class="badge bg-secondary">Pendente ⏳</span>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-check text-primary me-1"></i> <strong>Cliente Recebeu:</strong></span>
                <span v-if="order.customer_confirmed_at" class="badge bg-success">Confirmado ✓</span>
                <span v-else class="badge bg-warning text-dark">Aguardando... ⏳</span>
              </div>
              
              <!-- Se o cliente já avaliou -->
              <div v-if="order.food_rating" class="mt-2 pt-2 border-top text-center">
                <span class="badge bg-warning text-dark fw-bold mb-1">Avaliação do Cliente:</span>
                <div class="small">
                  <span>🍔 Lanche: <strong>{{ order.food_rating }}/5 ★</strong></span>
                  <span class="ms-2">🛵 Entrega: <strong>{{ order.delivery_rating }}/5 ★</strong></span>
                </div>
                <div v-if="order.review_comment" class="fst-italic text-muted mt-1" style="font-size: 0.78rem;">
                  "{{ order.review_comment }}"
                </div>
              </div>
            </div>

            <!-- Botão de Confirmar Entrega Realizada -->
            <div v-if="order.status !== 'concluido'">
              <button
                class="btn btn-success w-100 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center"
                @click="completeDelivery(order.id)"
                :disabled="order.motoboy_confirmed_at"
              >
                <i class="bi bi-check2-circle fs-5 me-2"></i>
                <span v-if="order.motoboy_confirmed_at">Entrega Confirmada por Você ✓</span>
                <span v-else>Confirmar Entrega Realizada</span>
              </button>

              <!-- Botão para pedir confirmação e avaliação via WhatsApp -->
              <a
                :href="getWhatsAppReviewLink(order)"
                target="_blank"
                class="btn btn-outline-success w-100 py-1.5 mt-2 rounded-pill fw-semibold shadow-sm d-flex align-items-center justify-content-center small"
              >
                <i class="bi bi-whatsapp me-1.5 text-success"></i> Pedir Confirmação / Avaliação no WhatsApp
              </a>
            </div>
            <div v-else class="text-center py-2 text-success fw-bold small">
              <i class="bi bi-check-circle-fill me-1"></i> Pedido concluído com sucesso!
              <a
                :href="getWhatsAppReviewLink(order)"
                target="_blank"
                class="btn btn-sm btn-outline-success rounded-pill px-3 mt-2 d-inline-flex align-items-center"
              >
                <i class="bi bi-whatsapp me-1"></i> Enviar Link de Avaliação ao Cliente
              </a>
            </div>

          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import { showConfirm, showSuccess, showError } from '../utils/swal';

export default {
  data() {
    return {
      orders: [],
      loading: false,
    };
  },
  mounted() {
    this.fetchOrders();
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    sanitizePhone(phone) {
      if (!phone) return '';
      return phone.replace(/\D/g, '');
    },
    async fetchOrders() {
      this.loading = true;
      try {
        const res = await axios.get('/api/motoboy/orders');
        this.orders = res.data;
      } catch (err) {
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    getWhatsAppReviewLink(order) {
      const phone = this.sanitizePhone(order.customer_phone);
      if (!phone) return '#';
      const origin = window.location.origin;
      const trackingUrl = `${origin}/?rastreio=${order.order_number}`;
      const msg = `Olá, *${order.customer_name}*! 👋🍔\n\nSou o entregador da *AKY LANCHES* e seu pedido *#${order.order_number}* foi entregue!\n\n⭐ *Poderia avaliar seu lanche e a entrega em menos de 1 minuto?*\nBasta clicar no link abaixo para confirmar o recebimento e dar suas estrelas:\n👉 ${trackingUrl}\n\nMuito obrigado e bom apetite! ❤️`;
      return `https://wa.me/55${phone}?text=${encodeURIComponent(msg)}`;
    },
    async completeDelivery(orderId) {
      const confirmed = await showConfirm(
        'Confirmar Entrega?',
        'Deseja registrar que a entrega deste pedido foi realizada?',
        'Sim, Confirmar',
        'Cancelar'
      );
      if (!confirmed) return;

      try {
        await axios.patch(`/api/motoboy/orders/${orderId}/complete`);
        await showSuccess('Entrega Registrada!', 'Sua confirmação de entrega foi salva com sucesso.');
        this.fetchOrders();
      } catch (err) {
        showError('Erro ao confirmar entrega', err.response?.data?.message || 'Não foi possível confirmar a entrega.');
      }
    },
  },
};
</script>

<style scoped>
.spin {
  animation: rotation 1s infinite linear;
}
@keyframes rotation {
  from { transform: rotate(0deg); }
  to { transform: rotate(359deg); }
}
</style>


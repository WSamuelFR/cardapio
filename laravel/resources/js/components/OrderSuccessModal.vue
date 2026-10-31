<template>
  <div v-if="order" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow border-0 rounded-4">
        <div class="modal-body text-center p-4">
          <div class="mb-3">
            <span class="badge bg-success rounded-circle p-3 shadow-sm">
              <i class="bi bi-check-lg fs-1 text-white"></i>
            </span>
          </div>

          <h4 class="fw-bold text-dark mb-1">Pedido Confirmado!</h4>
          <p class="text-muted small">Seu pedido foi registrado e nossa cozinha já foi notificada.</p>

          <!-- Bloco Destacado com Código do Pedido e Botão Chamativo de Copiar -->
          <div class="my-3 p-3 bg-light border border-2 border-warning rounded-4 shadow-sm text-center">
            <span class="text-muted small d-block mb-1 fw-bold text-uppercase">Código do seu pedido:</span>
            <div class="fs-3 font-monospace fw-black text-aky-primary mb-3 text-break">
              {{ order.order_number }}
            </div>

            <!-- Botão de Copiar Bem Destacado -->
            <button
              :class="['btn w-100 py-2 fw-bold rounded-pill shadow-sm d-flex align-items-center justify-content-center transition', copied ? 'btn-success text-white' : 'btn-warning text-dark']"
              @click="copyCode"
            >
              <i :class="copied ? 'bi bi-check2-circle fs-5 me-2' : 'bi bi-clipboard-check fs-5 me-2'"></i>
              <span>{{ copied ? 'Código Copiado com Sucesso!' : 'Copiar Código do Pedido' }}</span>
            </button>
          </div>

          <!-- Resumo de Dados -->
          <div class="card bg-white border rounded-3 p-3 my-3 text-start small shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted">Cliente:</span>
              <span class="fw-semibold text-dark">{{ order.customer_name }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted">Endereço:</span>
              <span class="text-end text-dark">{{ order.delivery_address }}</span>
            </div>
            <hr class="my-2 text-muted opacity-25">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold">Total a pagar:</span>
              <span class="fw-bold text-aky-primary fs-5">R$ {{ Number(order.total).toFixed(2).replace('.', ',') }}</span>
            </div>
          </div>

          <!-- Ações -->
          <div class="d-grid gap-2">
            <!-- Botão de Envio para WhatsApp com Link de Rastreio e Dados -->
            <a
              :href="whatsappUrl"
              target="_blank"
              class="btn btn-success py-2.5 fw-bold rounded-pill shadow-sm d-flex align-items-center justify-content-center transition text-white text-decoration-none"
              style="background-color: #25D366; border-color: #25D366;"
            >
              <i class="bi bi-whatsapp fs-5 me-2"></i>
              <span>Receber Dados no WhatsApp</span>
            </a>

            <button class="btn btn-aky-primary py-2 fw-semibold rounded-pill shadow-sm" @click="$emit('track', order.order_number)">
              <i class="bi bi-search me-2"></i> Acompanhar Status do Pedido
            </button>
            <button class="btn btn-outline-secondary py-2 rounded-pill" @click="$emit('close')">
              <i class="bi bi-bag-check me-2"></i> Voltar ao Cardápio
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    order: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      copied: false,
    };
  },
  computed: {
    whatsappUrl() {
      if (!this.order) return '#';
      const rawPhone = this.order.customer_phone || '';
      const digits = rawPhone.replace(/\D/g, '');
      const phone = digits.startsWith('55') ? digits : ('55' + digits);
      const trackingUrl = `${window.location.origin}/?rastreio=${this.order.order_number}`;

      const paymentMap = {
        dinheiro: 'Dinheiro',
        pix: 'PIX',
        cartao_credito: 'Cartão de Crédito',
        cartao_debito: 'Cartão de Débito',
      };
      const paymentName = paymentMap[this.order.payment_method] || this.order.payment_method || 'Não informado';
      let trocoText = '';
      if (this.order.payment_method === 'dinheiro' && this.order.needs_change && this.order.change_for) {
        trocoText = ` (Troco para R$ ${Number(this.order.change_for).toFixed(2).replace('.', ',')})`;
      }

      let itemsList = '';
      if (this.order.items && this.order.items.length) {
        itemsList = '\n*Itens do Pedido:*\n' + this.order.items.map(item => `• ${item.quantity}x ${item.product_name_snapshot || 'Item'}`).join('\n') + '\n';
      }

      const text =
`🍔 *AKY LANCHES - PEDIDO CONFIRMADO!*

Olá, *${this.order.customer_name || 'Cliente'}*! Seu pedido foi registrado com sucesso.

📋 *Código do Pedido:* #${this.order.order_number}
💰 *Total:* R$ ${Number(this.order.total).toFixed(2).replace('.', ',')} (${paymentName}${trocoText})
📍 *Endereço de Entrega:* ${this.order.delivery_address || 'Teixeira - PB'}${this.order.delivery_complement ? ' (' + this.order.delivery_complement + ')' : ''}
${itemsList}
🔍 *Acompanhe seu pedido em tempo real pelo link abaixo:*
${trackingUrl}

Agradecemos pela preferência! 🍟🥤`;

      return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
    },
  },
  methods: {
    copyCode() {
      if (this.order && this.order.order_number) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(this.order.order_number);
        } else {
          // Fallback para navegadores antigos
          const input = document.createElement('input');
          input.value = this.order.order_number;
          document.body.appendChild(input);
          input.select();
          document.execCommand('copy');
          document.body.removeChild(input);
        }
        this.copied = true;
        setTimeout(() => {
          this.copied = false;
        }, 3000);
      }
    },
  },
};
</script>

<style scoped>
.fw-black {
  font-weight: 900;
}
.transition {
  transition: all 0.2s ease-in-out;
}
</style>

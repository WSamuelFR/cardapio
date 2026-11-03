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
              <span class="text-muted">Modalidade:</span>
              <span :class="isPickup ? 'badge bg-dark rounded-pill px-2.5 py-1' : 'badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1'">
                <i :class="isPickup ? 'bi bi-shop me-1' : 'bi bi-bicycle me-1'"></i>
                {{ isPickup ? 'Retirar no Estabelecimento' : 'Entrega no Endereço' }}
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted">{{ isPickup ? 'Local de Retirada:' : 'Endereço:' }}</span>
              <span class="text-end text-dark fw-semibold">{{ order.delivery_address }}</span>
            </div>
            <hr class="my-2 text-muted opacity-25">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold">Total a pagar:</span>
              <span class="fw-bold text-aky-primary fs-5">R$ {{ Number(order.total).toFixed(2).replace('.', ',') }}</span>
            </div>
          </div>

          <!-- Ações -->
          <div class="d-grid gap-2">
            <!-- Botão de Envio para WhatsApp da AKY Lanches com Pedido Formatado -->
            <a
              :href="whatsappUrl"
              target="_blank"
              class="btn btn-success py-2.5 fw-bold rounded-pill shadow-sm d-flex align-items-center justify-content-center transition text-white text-decoration-none"
              style="background-color: #25D366; border-color: #25D366;"
            >
              <i class="bi bi-whatsapp fs-5 me-2"></i>
              <span>Enviar Pedido no WhatsApp da Lanchonete</span>
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
    storeWhatsapp: {
      type: String,
      default: '',
    },
    storeInfo: {
      type: Object,
      default: null,
    },
    whatsappTemplate: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      copied: false,
    };
  },
  computed: {
    isPickup() {
      return this.order && this.order.delivery_address && this.order.delivery_address.includes('RETIRADA');
    },
    whatsappUrl() {
      if (!this.order) return '#';

      // Pega o número comercial da AKY Lanches configurado pelo Admin
      const rawStorePhone = this.storeWhatsapp || '';
      let storeDigits = rawStorePhone.replace(/\D/g, '');
      if (!storeDigits) {
        // Fallback de segurança se ainda não tiver sido configurado
        storeDigits = '5583999999999';
      }
      const storePhone = storeDigits.startsWith('55') ? storeDigits : ('55' + storeDigits);

      const trackingUrl = `${window.location.origin}/?rastreio=${this.order.order_number}`;

      const paymentMap = {
        dinheiro: 'Dinheiro',
        pix: 'PIX (Na Entrega)',
        cartao_credito: 'Cartão de Crédito',
        cartao_debito: 'Cartão de Débito',
      };
      const paymentName = paymentMap[this.order.payment_method] || this.order.payment_method || 'Não informado';
      let trocoText = '';
      if (this.order.payment_method === 'dinheiro' && this.order.needs_change && this.order.change_for) {
        trocoText = `\n💵 *Troco para:* R$ ${Number(this.order.change_for).toFixed(2).replace('.', ',')}`;
      }

      let itemsList = '';
      if (this.order.items && this.order.items.length) {
        itemsList = this.order.items.map((item, idx) => {
          let line = `${idx + 1}. *${item.quantity}x ${item.product_name_snapshot || 'Item'}*`;
          if (item.unit_price) {
            line += ` (R$ ${Number(item.unit_price).toFixed(2).replace('.', ',')} un.)`;
          }
          if (item.notes) {
            line += `\n   ↳ _${item.notes}_`;
          }
          return line;
        }).join('\n\n');
      }

      let addressHeader = '📍 *Endereço de Entrega:*';
      let addressDetails = this.order.delivery_address || 'Teixeira - PB';
      if (this.isPickup) {
        addressHeader = '🏪 *Modalidade:* RETIRADA NO ESTABELECIMENTO (Balcão AKY Lanches)';
        addressDetails = 'Rua Principal, Centro - Teixeira - PB';
      } else {
        if (this.order.delivery_complement) {
          addressDetails += ` - Compl: ${this.order.delivery_complement}`;
        }
        if (this.order.delivery_reference) {
          addressDetails += `\n📍 *Ponto de Ref:* ${this.order.delivery_reference}`;
        }
        if (this.order.delivery_cep) {
          addressDetails += `\n📮 *CEP:* ${this.order.delivery_cep}`;
        }
      }

      let generalNotes = '';
      if (this.order.notes && this.order.notes.trim()) {
        generalNotes = `\n📝 *Observações:* ${this.order.notes.trim()}\n`;
      }

      let text = '';
      if (this.whatsappTemplate && this.whatsappTemplate.trim()) {
        text = this.whatsappTemplate
          .replace(/{codigo}/g, this.order.order_number)
          .replace(/{cliente}/g, this.order.customer_name || 'Não informado')
          .replace(/{telefone}/g, this.order.customer_phone || 'Não informado')
          .replace(/{endereco}/g, `${addressHeader}\n${addressDetails}`)
          .replace(/{itens}/g, (itemsList || 'Nenhum item') + generalNotes)
          .replace(/{pagamento}/g, `${paymentName}${trocoText}`)
          .replace(/{total}/g, Number(this.order.total).toFixed(2).replace('.', ','))
          .replace(/{link_rastreio}/g, trackingUrl);
      } else {
        text =
`👋 Olá, *AKY Lanches*! Acabei de fazer um pedido pelo cardápio digital.

🍔 *NOVO PEDIDO #${this.order.order_number}*
━━━━━━━━━━━━━━━━━━━━━
👤 *Cliente:* ${this.order.customer_name || 'Não informado'}
📱 *Telefone:* ${this.order.customer_phone || 'Não informado'}

${addressHeader}
${addressDetails}

🛒 *Itens do Pedido:*
${itemsList || 'Nenhum item informado'}
${generalNotes}
━━━━━━━━━━━━━━━━━━━━━
💳 *Forma de Pagamento:* ${paymentName}${trocoText}
💰 *TOTAL:* R$ ${Number(this.order.total).toFixed(2).replace('.', ',')}

🔍 *Link de Acompanhamento:*
${trackingUrl}

Por favor, confirmem o recebimento do meu pedido! 🙏🍟🥤`;
      }

      return `https://wa.me/${storePhone}?text=${encodeURIComponent(text)}`;
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

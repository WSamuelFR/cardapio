<template>
  <div class="product-detail-view pb-5">
    
    <!-- Barra Superior de Navegação -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <button
        class="btn btn-white border rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm d-flex align-items-center gap-1 transition"
        @click="$emit('back')"
      >
        <i class="bi bi-arrow-left fs-6"></i>
        <span>Voltar</span>
      </button>

      <div class="d-flex align-items-center gap-2">
        <span v-if="product.is_combo" class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 small fw-semibold">
          <i class="bi bi-gift-fill me-1"></i> Combo Especial
        </span>
        <span v-else-if="product.is_promotion" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 small fw-semibold">
          <i class="bi bi-fire me-1"></i> Oferta Especial
        </span>
        <span v-if="!product.is_available" class="badge bg-secondary rounded-pill px-3 py-2 small">
          Esgotado
        </span>
      </div>
    </div>

    <!-- Container Principal do Produto (Estilo Marketplace) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
      <div class="row g-0">
        
        <!-- Foto em Destaque do Produto -->
        <div class="col-12 col-md-6 bg-light d-flex align-items-center justify-content-center position-relative overflow-hidden" style="min-height: 280px; max-height: 400px;">
          <img
            v-if="product.photo_path"
            :src="product.photo_path"
            :alt="product.name"
            class="w-100 h-100 object-fit-cover"
          >
          <div v-else class="text-center p-5 text-muted">
            <i :class="product.is_combo ? 'bi-gift-fill text-success' : 'bi-image text-danger'" class="bi fs-1 d-block mb-2 opacity-50"></i>
            <span class="small fw-semibold text-secondary">Foto do Produto</span>
          </div>

          <!-- Badge Flutuante de Economia se for promoção ou combo -->
          <div
            v-if="discountAmount > 0"
            class="position-absolute top-0 start-0 m-3 badge bg-danger rounded-pill px-3 py-2 shadow-sm fs-6"
          >
            <i class="bi bi-tag-fill me-1"></i> Economize R$ {{ formatPrice(discountAmount) }}
          </div>
        </div>

        <!-- Informações e Opções do Produto -->
        <div class="col-12 col-md-6 p-4 d-flex flex-column justify-content-between">
          <div>
            <!-- Categoria ou Tag -->
            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
              {{ product.category ? product.category.name : (product.is_combo ? 'Combo do Dia' : 'AKY Lanches') }}
            </div>

            <!-- Título do Produto -->
            <h3 class="fw-bold text-dark mb-2" style="font-size: 1.35rem;">
              {{ product.name }}
            </h3>

            <!-- Bloco de Preços -->
            <div class="d-flex align-items-baseline gap-2 mb-3">
              <span v-if="product.is_promotion && product.promotional_price" class="text-muted text-decoration-line-through fs-6">
                R$ {{ formatPrice(product.price) }}
              </span>
              <span class="fw-bold fs-3 text-aky-primary">
                R$ {{ formatPrice(unitPrice) }}
              </span>
              <span v-if="product.is_combo" class="badge bg-success rounded-pill px-2 py-1 small">
                Preço Promocional
              </span>
            </div>

            <hr class="opacity-25 my-3">

            <!-- Descrição e Ingredientes -->
            <div class="mb-4">
              <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">
                <i class="bi bi-info-circle text-aky-primary me-1"></i> Detalhes & Ingredientes
              </h6>
              <p class="text-muted small mb-0 lh-base">
                {{ product.description || 'Preparo artesanal com os melhores ingredientes, feito na hora para garantir o máximo de sabor e qualidade.' }}
              </p>
            </div>

            <!-- Campo de Observações Personalizadas -->
            <div class="mb-3">
              <label class="form-label fw-bold text-dark mb-1 small d-flex align-items-center justify-content-between">
                <span><i class="bi bi-chat-left-dots text-aky-primary me-1"></i> Alguma observação?</span>
                <span class="text-muted fw-normal" style="font-size: 0.7rem;">Opcional</span>
              </label>
              <textarea
                class="form-control rounded-3 border bg-light small"
                rows="2"
                v-model="itemNotes"
                placeholder="Ex: Sem cebola, caprichar no molho, maionese à parte..."
              ></textarea>
            </div>
          </div>

          <!-- Controle de Quantidade e Total -->
          <div class="pt-3 border-top">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="text-muted small fw-semibold">Quantidade:</span>
              <div class="input-group input-group-sm rounded-pill overflow-hidden border shadow-sm" style="width: 120px;">
                <button
                  class="btn btn-light border-0 fw-bold px-3 text-dark"
                  type="button"
                  @click="decreaseQty"
                  :disabled="quantity <= 1"
                >
                  <i class="bi bi-dash"></i>
                </button>
                <input
                  type="text"
                  class="form-control text-center border-0 bg-white fw-bold shadow-none p-0"
                  :value="quantity"
                  readonly
                >
                <button
                  class="btn btn-light border-0 fw-bold px-3 text-dark"
                  type="button"
                  @click="increaseQty"
                >
                  <i class="bi bi-plus"></i>
                </button>
              </div>
            </div>

            <!-- Botão de Ação: Adicionar ao Carrinho -->
            <button
              class="btn btn-aky-primary w-100 rounded-pill py-3 fw-bold d-flex align-items-center justify-content-between px-4 shadow transition"
              :disabled="!product.is_available"
              @click="handleAddToCart"
            >
              <span class="d-flex align-items-center gap-2">
                <i class="bi bi-cart-plus fs-5"></i>
                <span>Adicionar ao Pedido</span>
              </span>
              <span class="fs-6 fw-bold">
                R$ {{ formatPrice(totalPrice) }}
              </span>
            </button>
          </div>

        </div>

      </div>
    </div>

    <!-- Seção de Sugestões / Você também pode gostar -->
    <div v-if="suggestedItems.length > 0" class="mt-5">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-hand-thumbs-up-fill text-aky-primary fs-5"></i>
          <span>Você também pode gostar</span>
        </h5>
        <small class="text-muted">Aproveite para completar</small>
      </div>

      <div class="d-flex gap-3 overflow-x-auto pb-2 no-scrollbar" style="scroll-snap-type: x mandatory;">
        <div
          v-for="sug in suggestedItems"
          :key="'sug-' + sug.id"
          class="flex-shrink-0 cursor-pointer"
          style="width: 250px; min-width: 250px; scroll-snap-align: start;"
          @click="$emit('select-product', sug)"
        >
          <div class="card aky-card h-100 bg-white shadow-sm border-0 rounded-4 overflow-hidden p-3 d-flex flex-column justify-content-between">
            <div class="d-flex gap-2 align-items-center mb-2">
              <div class="rounded-3 overflow-hidden bg-light border flex-shrink-0" style="width: 60px; height: 60px;">
                <img v-if="sug.photo_path" :src="sug.photo_path" class="w-100 h-100 object-fit-cover" :alt="sug.name">
                <i v-else class="bi bi-tag text-muted fs-4 d-flex align-items-center justify-content-center h-100"></i>
              </div>
              <div class="min-w-0">
                <h6 class="fw-bold text-dark mb-0 small text-truncate" :title="sug.name">{{ sug.name }}</h6>
                <span class="fw-bold text-aky-primary small d-block">R$ {{ formatPrice(sug.effective_price || sug.price) }}</span>
              </div>
            </div>
            <button class="btn btn-outline-danger btn-sm rounded-pill w-100 mt-2 fw-semibold" style="font-size: 0.72rem;">
              Ver Detalhes
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'ProductDetailView',
  props: {
    product: {
      type: Object,
      required: true,
    },
    allProducts: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      quantity: 1,
      itemNotes: '',
    };
  },
  watch: {
    product() {
      this.quantity = 1;
      this.itemNotes = '';
    },
  },
  computed: {
    unitPrice() {
      if (this.product.effective_price !== undefined && this.product.effective_price !== null) {
        return parseFloat(this.product.effective_price);
      }
      if (this.product.is_promotion && this.product.promotional_price) {
        return parseFloat(this.product.promotional_price);
      }
      return parseFloat(this.product.price) || 0;
    },
    discountAmount() {
      if (this.product.is_promotion && this.product.promotional_price) {
        const original = parseFloat(this.product.price) || 0;
        const promo = parseFloat(this.product.promotional_price) || 0;
        return original > promo ? original - promo : 0;
      }
      return 0;
    },
    totalPrice() {
      return this.unitPrice * this.quantity;
    },
    suggestedItems() {
      return (this.allProducts || [])
        .filter(p => p.id !== this.product.id && p.is_available)
        .slice(0, 6);
    },
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    increaseQty() {
      this.quantity += 1;
    },
    decreaseQty() {
      if (this.quantity > 1) {
        this.quantity -= 1;
      }
    },
    handleAddToCart() {
      this.$emit('add-to-cart', {
        ...this.product,
        quantity: this.quantity,
        notes: this.itemNotes.trim(),
      });
    },
  },
};
</script>

<style scoped>
.transition {
  transition: all 0.2s ease-in-out;
}
.cursor-pointer {
  cursor: pointer;
}
</style>


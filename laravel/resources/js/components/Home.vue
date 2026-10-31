<template>
  <div class="home-container pb-5">
    
    <!-- Banner Principal -->
    <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden mb-4" style="background: linear-gradient(135deg, #d62828 0%, #9e1212 100%);">
      <div class="card-body p-4 p-md-5 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div style="max-width: 520px;">
          <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-2 shadow-sm">
            <i class="bi bi-star-fill me-1"></i> Tradição e Sabor
          </span>
          <h1 class="fw-black mb-2 display-6 fw-bold">Bateu aquela fome?</h1>
          <p class="text-white-50 mb-3 fs-6">
            Peça agora seus pastéis sequinhos, salgados crocantes e sucos naturais sem complicação e sem necessidade de cadastro!
          </p>
          <button class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm d-inline-flex align-items-center" @click="$emit('go-to-menu')">
            <i class="bi bi-book-half me-2"></i> Ver Cardápio Completo
          </button>
        </div>
        <div class="d-none d-md-block text-center p-3 bg-white bg-opacity-10 rounded-circle shadow">
          <img src="/images/aky_lanches_icon.svg" alt="AKY Lanches" style="height: 120px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));">
        </div>
      </div>
    </div>

    <!-- Barra de Pesquisa Rápida na Home -->
    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden bg-white mb-4 border">
      <span class="input-group-text bg-white border-0 ps-3">
        <i class="bi bi-search text-muted fs-5"></i>
      </span>
      <input
        type="text"
        class="form-control border-0 shadow-none ps-2"
        placeholder="O que você quer comer hoje? (ex: pastel de queijo, coxinha...)"
        v-model="searchTerm"
      >
      <button v-if="searchTerm" class="btn btn-link text-muted border-0 text-decoration-none" @click="searchTerm = ''">
        <i class="bi bi-x-circle-fill"></i>
      </button>
    </div>

    <!-- Resultados da Busca (se o usuário estiver buscando) -->
    <div v-if="searchTerm.trim()" class="mb-5">
      <h5 class="fw-bold text-dark mb-3">
        <i class="bi bi-search text-aky-primary me-2"></i> Resultados para "{{ searchTerm }}"
      </h5>

      <div v-if="searchResults.length === 0" class="text-center py-4 bg-white rounded-4 shadow-sm p-3">
        <i class="bi bi-emoji-frown fs-2 text-muted d-block mb-2"></i>
        <p class="text-muted mb-0">Nenhum produto encontrado. Tente buscar por outro termo.</p>
      </div>

      <div v-else class="row g-3">
        <div v-for="(product, idx) in searchResults" :key="'search-' + product.id" class="col-12 col-md-6 col-lg-4">
          <div
            :class="['card aky-card h-100 bg-white border-start border-4 shadow-sm rounded-4 cursor-pointer transition', getCardTheme(idx).borderClass]"
            @click="$emit('select-product', product, 'home')"
          >
            <div class="d-flex p-3 gap-3">
              <!-- Elemento de Foto do Produto -->
              <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border" style="width: 85px; height: 85px;">
                <img
                  v-if="product.photo_path"
                  :src="product.photo_path"
                  :alt="product.name"
                  class="w-100 h-100 object-fit-cover"
                >
                <div v-else class="text-center text-muted">
                  <i :class="['bi bi-image fs-3 d-block', getCardTheme(idx).iconClass]"></i>
                  <span style="font-size: 0.65rem;">Sem foto</span>
                </div>
              </div>

              <div class="flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                  <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem; max-width: 175px;">{{ product.name }}</h6>
                  <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 175px;">
                    {{ product.description || 'Delicioso e feito na hora.' }}
                  </p>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <span :class="['fw-bold fs-6', getCardTheme(idx).priceClass]">
                    R$ {{ formatPrice(product.effective_price) }}
                  </span>
                  <button
                    :class="['btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0', getCardTheme(idx).btnClass]"
                    style="width: 36px; height: 36px;"
                    :disabled="!product.is_available"
                    :title="isCustomizable(product) ? 'Personalizar e escolher opções' : 'Adicionar ao pedido'"
                    @click.stop="handleCardAdd(product)"
                  >
                    <i :class="isCustomizable(product) ? 'bi-sliders' : 'bi-plus-lg'" class="fs-6"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 1. SEÇÃO DE PROMOÇÕES IMPERDÍVEIS -->
    <div v-if="!searchTerm.trim() && promotions.length > 0" class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
            <i class="bi bi-fire text-danger me-2 fs-4"></i> Promoções Imperdíveis
          </h5>
          <span class="badge bg-danger rounded-pill px-3 py-1 d-none d-sm-inline">Tempo Limitado</span>
        </div>

        <!-- Botão Ver Tudo das Promoções (Abre tela dedicada) -->
        <button
          class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold shadow-sm transition d-flex align-items-center gap-1 flex-shrink-0"
          @click="$emit('open-section', {
            title: 'Promoções Imperdíveis',
            subtitle: 'Todas as ofertas especiais por tempo limitado',
            icon: 'bi-fire',
            themeColor: 'danger',
            badgeText: 'Tempo Limitado',
            items: promotions,
            backTo: 'home'
          })"
        >
          <span>Ver tudo</span>
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <div class="row g-3">
        <div v-for="(item, idx) in displayedPromotions" :key="'promo-' + item.id" class="col-12 col-md-6 col-lg-4">
          <div
            :class="['card aky-card h-100 bg-white border-start border-4 shadow-sm rounded-4 cursor-pointer transition', getCardTheme(idx).borderClass]"
            @click="$emit('select-product', item, 'home')"
          >
            <div class="d-flex p-3 gap-3">
              <!-- Elemento de Foto do Produto -->
              <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative" style="width: 90px; height: 90px;">
                <img
                  v-if="item.photo_path"
                  :src="item.photo_path"
                  :alt="item.name"
                  class="w-100 h-100 object-fit-cover"
                >
                <div v-else class="text-center text-muted">
                  <i :class="['bi bi-tag-fill fs-2 opacity-75 d-block', getCardTheme(idx).iconClass]"></i>
                  <span style="font-size: 0.65rem;" :class="['fw-semibold', getCardTheme(idx).iconClass]">Promoção</span>
                </div>
              </div>

              <div class="flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-start">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem; max-width: 170px;">{{ item.name }}</h6>
                  </div>
                  <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 170px;">
                    {{ item.description || 'Aproveite nosso preço promocional.' }}
                  </p>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                  <div>
                    <span class="text-muted small text-decoration-line-through d-block" style="font-size: 0.70rem;">
                      R$ {{ formatPrice(item.price) }}
                    </span>
                    <span :class="['fw-bold fs-6', getCardTheme(idx).priceClass]">
                      R$ {{ formatPrice(item.promotional_price) }}
                    </span>
                  </div>

                  <button
                    :class="['btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0', getCardTheme(idx).btnClass]"
                    style="width: 36px; height: 36px;"
                    :disabled="!item.is_available"
                    :title="isCustomizable(item) ? 'Personalizar e escolher opções' : 'Adicionar ao pedido'"
                    @click.stop="handleCardAdd(item)"
                  >
                    <i :class="isCustomizable(item) ? 'bi-sliders' : 'bi-plus-lg'" class="fs-6"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. SEÇÃO DE COMBOS DO DIA -->
    <div v-if="!searchTerm.trim() && combos && combos.length > 0" class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
            <i class="bi bi-gift-fill text-success me-2 fs-4"></i> Combos do Dia
          </h5>
          <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold d-none d-sm-inline">
            <i class="bi bi-tag-fill me-1"></i> Economize Mais
          </span>
        </div>

        <!-- Botão Ver Tudo dos Combos (Abre tela dedicada) -->
        <button
          class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold shadow-sm transition d-flex align-items-center gap-1 flex-shrink-0"
          @click="$emit('open-section', {
            title: 'Combos do Dia',
            subtitle: 'Combos especiais preparados para você economizar',
            icon: 'bi-gift-fill',
            themeColor: 'success',
            badgeText: 'Economize Mais',
            items: combos,
            backTo: 'home'
          })"
        >
          <span>Ver tudo</span>
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <div class="row g-3">
        <div v-for="(item, idx) in displayedCombos" :key="'combo-' + item.id" class="col-12 col-md-6 col-lg-4">
          <div
            :class="['card aky-card h-100 bg-white border-start border-4 shadow-sm rounded-4 cursor-pointer transition', getCardTheme(idx + 1).borderClass]"
            @click="$emit('select-product', item, 'home')"
          >
            <div class="d-flex p-3 gap-3">
              <!-- Elemento de Foto do Combo -->
              <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative" style="width: 90px; height: 90px;">
                <img
                  v-if="item.photo_path"
                  :src="item.photo_path"
                  :alt="item.name"
                  class="w-100 h-100 object-fit-cover"
                >
                <div v-else class="text-center text-muted">
                  <i :class="['bi bi-gift-fill fs-2 opacity-75 d-block', getCardTheme(idx + 1).iconClass]"></i>
                  <span style="font-size: 0.65rem;" :class="['fw-semibold', getCardTheme(idx + 1).iconClass]">Combo</span>
                </div>
              </div>

              <!-- Informações do Combo -->
              <div class="flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-start">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem; max-width: 170px;">{{ item.name }}</h6>
                  </div>
                  <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 170px;" :title="item.description">
                    {{ item.description || 'Itens selecionados com super desconto.' }}
                  </p>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                  <div>
                    <span class="text-muted small text-decoration-line-through d-block" style="font-size: 0.70rem;">
                      R$ {{ formatPrice(item.price) }}
                    </span>
                    <span :class="['fw-bold fs-6', getCardTheme(idx + 1).priceClass]">
                      R$ {{ formatPrice(item.effective_price) }}
                    </span>
                  </div>

                  <button
                    :class="['btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0', getCardTheme(idx + 1).btnClass]"
                    style="width: 36px; height: 36px;"
                    :disabled="!item.is_available"
                    title="Adicionar ao pedido"
                    @click.stop="handleCardAdd(item)"
                  >
                    <i class="bi bi-plus-lg fs-6"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. SEÇÃO DE MAIS PEDIDOS (GLOBALMENTE NO SITE) -->
    <div v-if="!searchTerm.trim() && popular.length > 0" class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
            <i class="bi bi-trophy-fill text-warning me-2 fs-4"></i> Mais Pedidos
          </h5>
          <span class="badge bg-warning bg-opacity-25 text-dark rounded-pill px-3 py-1 fw-semibold d-none d-sm-inline">
            <i class="bi bi-star-fill text-warning me-1"></i> Favoritos do Site
          </span>
        </div>

        <!-- Botão Ver Tudo dos Mais Pedidos (Abre tela dedicada) -->
        <button
          class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-semibold shadow-sm transition d-flex align-items-center gap-1 flex-shrink-0"
          @click="$emit('open-section', {
            title: 'Mais Pedidos',
            subtitle: 'Os lanches favoritos e mais vendidos de todo o site',
            icon: 'bi-trophy-fill',
            themeColor: 'warning',
            badgeText: 'Favoritos do Site',
            items: popular,
            backTo: 'home'
          })"
        >
          <span>Ver tudo</span>
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <div class="row g-3">
        <div v-for="(item, idx) in displayedPopular" :key="'pop-' + item.id" class="col-12 col-md-6 col-lg-4">
          <div
            :class="['card aky-card h-100 bg-white border-start border-4 shadow-sm rounded-4 cursor-pointer transition', getCardTheme(idx + 2).borderClass]"
            @click="$emit('select-product', item, 'home')"
          >
            <div class="d-flex p-3 gap-3">
              <!-- Elemento de Foto do Produto Mais Pedido -->
              <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative" style="width: 90px; height: 90px;">
                <img
                  v-if="item.photo_path"
                  :src="item.photo_path"
                  :alt="item.name"
                  class="w-100 h-100 object-fit-cover"
                >
                <div v-else class="text-center text-muted">
                  <i :class="['bi bi-trophy-fill fs-2 opacity-75 d-block', getCardTheme(idx + 2).iconClass]"></i>
                  <span style="font-size: 0.65rem;" :class="['fw-semibold', getCardTheme(idx + 2).iconClass]">Favorito</span>
                </div>
              </div>

              <!-- Informações e Ação do Produto -->
              <div class="flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-start">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem; max-width: 170px;">{{ item.name }}</h6>
                  </div>
                  <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 170px;">
                    {{ item.description || 'Delicioso e preparado na hora com muito carinho.' }}
                  </p>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                  <div>
                    <div v-if="item.is_promotion">
                      <span class="text-muted small text-decoration-line-through d-block" style="font-size: 0.70rem;">
                        R$ {{ formatPrice(item.price) }}
                      </span>
                      <span :class="['fw-bold fs-6', getCardTheme(idx + 2).priceClass]">
                        R$ {{ formatPrice(item.promotional_price) }}
                      </span>
                    </div>
                    <div v-else>
                      <span :class="['fw-bold fs-6', getCardTheme(idx + 2).priceClass]">
                        R$ {{ formatPrice(item.effective_price) }}
                      </span>
                    </div>
                  </div>

                  <button
                    :class="['btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0', getCardTheme(idx + 2).btnClass]"
                    style="width: 36px; height: 36px;"
                    :disabled="!item.is_available"
                    :title="isCustomizable(item) ? 'Personalizar e escolher opções' : 'Adicionar ao pedido'"
                    @click.stop="handleCardAdd(item)"
                  >
                    <i :class="isCustomizable(item) ? 'bi-sliders' : 'bi-plus-lg'" class="fs-6"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chamada para Acessar o Cardápio Completo -->
    <div class="card border-0 bg-white shadow-sm rounded-4 p-4 text-center my-4">
      <div class="p-2">
        <i class="bi bi-journal-text fs-1 text-aky-primary d-block mb-2"></i>
        <h4 class="fw-bold text-dark">Quer conferir todas as nossas opções?</h4>
        <p class="text-muted small mb-3">Temos pastéis tradicionais e especiais, salgados fritos na hora, sucos naturais e bebidas bem geladas.</p>
        <button class="btn btn-aky-primary rounded-pill px-4 py-2 fw-semibold" @click="$emit('go-to-menu')">
          <i class="bi bi-arrow-right-circle me-2"></i> Ir para o Cardápio por Categorias
        </button>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  props: {
    promotions: {
      type: Array,
      default: () => [],
    },
    combos: {
      type: Array,
      default: () => [],
    },
    popular: {
      type: Array,
      default: () => [],
    },
    categories: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      searchTerm: '',
    };
  },
  computed: {
    displayedPromotions() {
      return (this.promotions || []).slice(0, 3);
    },
    displayedCombos() {
      return (this.combos || []).slice(0, 3);
    },
    displayedPopular() {
      return (this.popular || []).slice(0, 3);
    },
    searchResults() {
      if (!this.searchTerm.trim()) return [];
      const term = this.searchTerm.toLowerCase();
      const results = [];
      this.categories.forEach(cat => {
        if (cat.products) {
          cat.products.forEach(p => {
            if (
              p.name.toLowerCase().includes(term) ||
              (p.description && p.description.toLowerCase().includes(term))
            ) {
              results.push(p);
            }
          });
        }
      });
      return results;
    },
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    getCardTheme(idx) {
      const i = Math.abs(idx) % 3;
      if (i === 0) {
        return {
          borderClass: 'border-danger',
          btnClass: 'btn-danger text-white',
          priceClass: 'text-danger',
          iconClass: 'text-danger',
        };
      } else if (i === 1) {
        return {
          borderClass: 'border-warning',
          btnClass: 'btn-warning text-dark fw-bold',
          priceClass: 'text-warning-emphasis',
          iconClass: 'text-warning',
        };
      } else {
        return {
          borderClass: 'border-success',
          btnClass: 'btn-success text-white',
          priceClass: 'text-success',
          iconClass: 'text-success',
        };
      }
    },
    isCustomizable(product) {
      if (!product) return false;
      const name = (product.name || '').toLowerCase();
      const catSlug = (product.category && product.category.slug) ? product.category.slug.toLowerCase() : '';
      if (name.includes('monte seu pastel')) return true;
      if (catSlug.includes('pizza') || name.startsWith('pizza') || (name.includes('pizza') && !catSlug.includes('pastel'))) return true;
      return false;
    },
    handleCardAdd(product) {
      if (this.isCustomizable(product)) {
        this.$emit('select-product', product, 'home');
      } else {
        this.$emit('add-to-cart', product);
      }
    },
  },
};
</script>

<style scoped>
.text-warning-emphasis {
  color: #b45309 !important;
}
.transition {
  transition: all 0.2s ease-in-out;
}
</style>


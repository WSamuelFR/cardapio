<template>
  <div class="section-view pb-5">
    
    <!-- Cabeçalho com Botão Voltar e Título da Seção -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <button
          class="btn btn-light border rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm d-flex align-items-center gap-1 transition"
          @click="$emit('back')"
        >
          <i class="bi bi-arrow-left fs-6"></i>
          <span>Voltar</span>
        </button>

        <div>
          <h4 class="fw-bold text-dark mb-0 d-flex align-items-center flex-wrap gap-2">
            <i :class="[icon || 'bi-tag-fill', themeColor === 'rgb' ? 'text-rgb' : (themeColor === 'purple' ? 'text-purple' : ('text-' + (themeColor || 'danger'))), 'fs-4']"></i>
            <span>{{ title }}</span>
            <span v-if="badgeText" :class="['badge rounded-pill px-3 py-1 small', themeColor === 'rgb' ? 'badge-rgb text-white' : (themeColor === 'purple' ? 'bg-purple-subtle text-purple' : ('bg-' + (themeColor || 'danger'))), themeColor === 'warning' ? 'text-dark' : 'text-white']">
              {{ badgeText }}
            </span>
          </h4>
          <small class="text-muted" v-if="subtitle">{{ subtitle }}</small>
        </div>
      </div>

      <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
        {{ filteredItems.length }} {{ filteredItems.length === 1 ? 'item' : 'itens' }}
      </span>
    </div>

    <!-- Barra de Pesquisa no Topo da Seção -->
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
      <div class="input-group">
        <span class="input-group-text bg-transparent border-0 text-muted ps-3">
          <i class="bi bi-search fs-5"></i>
        </span>
        <input
          type="text"
          class="form-control border-0 shadow-none ps-2"
          v-model="searchTerm"
          :placeholder="'Pesquisar em ' + title + '...'"
        >
        <button
          v-if="searchTerm"
          class="btn btn-transparent border-0 text-muted pe-3"
          @click="searchTerm = ''"
          title="Limpar busca"
        >
          <i class="bi bi-x-circle-fill"></i>
        </button>
      </div>
    </div>

    <!-- Estado Vazio (Sem resultados) -->
    <div v-if="filteredItems.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
      <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
      <h6 class="text-dark fw-bold mb-1">Nenhum produto encontrado</h6>
      <p class="text-muted small mb-3">Tente buscar por outro termo ou limpe a pesquisa.</p>
      <button v-if="searchTerm" class="btn btn-outline-secondary rounded-pill btn-sm px-3" @click="searchTerm = ''">
        Limpar Pesquisa
      </button>
    </div>

    <!-- Grade Completa de Cards (Combos Verdes, Montadores Azuis, Restante Vermelho) -->
    <div v-else class="row g-3">
      <div
        v-for="(item, idx) in filteredItems"
        :key="'sec-item-' + item.id"
        class="col-12 col-md-6 col-lg-4"
      >
        <div
          :class="['card aky-card h-100 bg-white shadow-sm rounded-4 cursor-pointer transition', getCardTheme(idx, item).borderClass, !item.is_available ? 'opacity-75' : '']"
          @click="$emit('select-product', item, 'section')"
        >
          <div class="d-flex p-3 gap-3">
            
            <!-- Foto do Item -->
            <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative" style="width: 90px; height: 90px;">
              <img
                v-if="item.photo_path"
                :src="item.photo_path"
                :alt="item.name"
                class="w-100 h-100 object-fit-cover"
              >
              <div v-else class="text-center text-muted">
                <i :class="[icon || 'bi-tag-fill', 'fs-2 opacity-75 d-block', getCardTheme(idx, item).iconClass]"></i>
                <span style="font-size: 0.65rem;" :class="['fw-semibold', getCardTheme(idx, item).iconClass]">
                  {{ item.is_combo ? 'Combo' : (item.is_promotion ? 'Promoção' : 'AKY') }}
                </span>
              </div>
            </div>

            <!-- Informações e Botão de Ação -->
            <div class="flex-grow-1 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-start gap-1">
                  <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem; max-width: 170px;">{{ item.name }}</h6>
                  <span v-if="!item.is_available" class="badge bg-secondary small" style="font-size: 0.65rem;">Esgotado</span>
                </div>
                <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 170px;" :title="item.description">
                  {{ item.description || 'Preparo artesanal com os melhores ingredientes.' }}
                </p>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                <div>
                  <div v-if="item.is_promotion && item.promotional_price">
                    <span class="text-muted small text-decoration-line-through d-block" style="font-size: 0.70rem;">
                      R$ {{ formatPrice(item.price) }}
                    </span>
                    <span :class="['fw-bold fs-6', getCardTheme(idx, item).priceClass]">
                      R$ {{ formatPrice(item.promotional_price) }}
                    </span>
                  </div>
                  <div v-else>
                    <span :class="['fw-bold fs-6', getCardTheme(idx, item).priceClass]">
                      R$ {{ formatPrice(item.effective_price || item.price) }}
                    </span>
                  </div>
                </div>

                <button
                  :class="['btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0', getCardTheme(idx, item).btnClass]"
                  style="width: 36px; height: 36px;"
                  :disabled="!item.is_available"
                  :title="isCustomizable(item) ? 'Personalizar opções' : 'Adicionar ao pedido'"
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
</template>

<script>
export default {
  name: 'SectionView',
  props: {
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String,
      default: '',
    },
    icon: {
      type: String,
      default: 'bi-tag-fill',
    },
    themeColor: {
      type: String,
      default: 'danger',
    },
    badgeText: {
      type: String,
      default: '',
    },
    items: {
      type: Array,
      default: () => [],
    },
    backTo: {
      type: String,
      default: 'home',
    },
  },
  data() {
    return {
      searchTerm: '',
    };
  },
  computed: {
    filteredItems() {
      if (!this.searchTerm.trim()) {
        return this.items;
      }
      const term = this.searchTerm.toLowerCase();
      return this.items.filter(item => {
        const nameMatch = item.name && item.name.toLowerCase().includes(term);
        const descMatch = item.description && item.description.toLowerCase().includes(term);
        return nameMatch || descMatch;
      });
    },
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    isDrink(item) {
      if (!item) return false;
      const catSlug = (item.category && item.category.slug) ? item.category.slug.toLowerCase() : '';
      const catName = (item.category && item.category.name) ? item.category.name.toLowerCase() : '';
      const name = (item.name || '').toLowerCase();
      const keywords = ['bebida', 'suco', 'refrigerante', 'refrig', 'cerveja', 'agua', 'água', 'drink', 'coca', 'guaraná', 'guarana', 'fanta', 'sprite'];
      return keywords.some(k => catSlug.includes(k) || catName.includes(k) || name.includes(k));
    },
    getCardTheme(idx, item = null) {
      // 1. Promoção tem prioridade com visual animado RGB
      if ((item && item.is_promotion) || this.themeColor === 'rgb') {
        return {
          borderClass: 'card-promo-rgb',
          btnClass: 'btn-rgb text-white',
          priceClass: 'text-rgb fw-bold',
          iconClass: 'text-rgb',
        };
      }

      // 2. Bebidas e Sucos: Amarelo (warning)
      if (item && this.isDrink(item)) {
        return {
          borderClass: 'border-start border-4 border-warning',
          btnClass: 'btn-warning text-dark fw-bold',
          priceClass: 'text-warning-emphasis fw-bold',
          iconClass: 'text-warning',
        };
      }

      // 3. Combos especiais: Verde (success)
      const isCombo = item && (item.is_combo || (item.category && item.category.slug === 'combos-especiais'));
      if (isCombo) {
        return {
          borderClass: 'border-start border-4 border-success',
          btnClass: 'btn-success text-white',
          priceClass: 'text-success fw-bold',
          iconClass: 'text-success',
        };
      }

      // 4. Montadores / Personalizáveis (Pizza, Pastel, Tapioca, Cuscuz): Azul (primary)
      if (item && this.isCustomizable(item)) {
        return {
          borderClass: 'border-start border-4 border-primary',
          btnClass: 'btn-primary text-white',
          priceClass: 'text-primary fw-bold',
          iconClass: 'text-primary',
        };
      }

      // 5. Cores temáticas da seção se especificada
      const color = this.themeColor || 'danger';
      if (color === 'warning') {
        return {
          borderClass: 'border-start border-4 border-warning',
          btnClass: 'btn-warning text-dark fw-bold',
          priceClass: 'text-warning-emphasis fw-bold',
          iconClass: 'text-warning',
        };
      } else if (color === 'success') {
        return {
          borderClass: 'border-start border-4 border-success',
          btnClass: 'btn-success text-white',
          priceClass: 'text-success fw-bold',
          iconClass: 'text-success',
        };
      } else if (color === 'purple') {
        return {
          borderClass: 'border-start border-4 border-purple',
          btnClass: 'btn-purple text-white',
          priceClass: 'text-purple fw-bold',
          iconClass: 'text-purple',
        };
      } else if (color === 'primary') {
        return {
          borderClass: 'border-start border-4 border-primary',
          btnClass: 'btn-primary text-white',
          priceClass: 'text-primary fw-bold',
          iconClass: 'text-primary',
        };
      }

      // 6. Restante padrão: Vermelho (danger)
      return {
        borderClass: 'border-start border-4 border-danger',
        btnClass: 'btn-danger text-white',
        priceClass: 'text-danger fw-bold',
        iconClass: 'text-danger',
      };
    },
    isCustomizable(item) {
      if (!item) return false;
      const name = (item.name || '').toLowerCase();
      const catSlug = (item.category && item.category.slug) ? item.category.slug.toLowerCase() : '';
      if (name.includes('monte seu pastel') || name.includes('monte seu')) return true;
      if (catSlug.includes('pizza') || name.startsWith('pizza') || (name.includes('pizza') && !catSlug.includes('pastel'))) return true;
      if (catSlug.includes('tapioca') || name.includes('tapioca')) return true;
      if (catSlug.includes('cuscuz') || name.includes('cuscuz')) return true;
      return false;
    },
    handleCardAdd(item) {
      if (this.isCustomizable(item)) {
        this.$emit('select-product', item, 'section');
      } else {
        this.$emit('add-to-cart', item);
      }
    },
  },
};
</script>

<style scoped>
.transition {
  transition: all 0.2s ease-in-out;
}
.text-warning-emphasis {
  color: #b45309 !important;
}
</style>

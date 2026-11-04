<template>
  <div class="menu-view pb-5">
    
    <!-- Cabeçalho Principal do Cardápio -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center">
          <i class="bi bi-book-half text-aky-primary me-2"></i> Cardápio AKY Lanches
        </h3>
        <p class="text-muted small mb-0">Explore nossas categorias e monte seu pedido completo</p>
      </div>

      <!-- Atalho Rápido para Rastrear -->
      <span class="badge bg-danger bg-opacity-10 text-aky-primary rounded-pill px-3 py-2 small fw-semibold">
        <i class="bi bi-stars me-1"></i> Produtos Frescos & Feitos na Hora
      </span>
    </div>

    <!-- SEÇÕES DE CATEGORIAS NO ESTILO FEED COM ROLAGEM HORIZONTAL -->
    <div v-if="categories.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
      <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
      <h6 class="text-muted fw-semibold">Nenhuma categoria cadastrada no momento</h6>
    </div>

    <div v-else>
      <div
        v-for="(cat, catIdx) in categories"
        :key="'menu-cat-' + cat.id"
        class="category-feed-section mb-5"
      >
        <!-- Topo da Categoria: Nome, Ícone, Badge e Botão Ver Tudo -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <div :class="['rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm', getCategoryTheme(cat).headerIcon]" style="width: 40px; height: 40px;">
              <i :class="[getCategoryIcon(cat.slug), 'fs-5']"></i>
            </div>
            <div>
              <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <span>{{ cat.name }}</span>
                <span :class="['badge rounded-pill small fw-normal d-none d-sm-inline', getCategoryTheme(cat).badge]">
                  {{ cat.products ? cat.products.length : 0 }} itens
                </span>
              </h4>
            </div>
          </div>

          <!-- Botão Ver Tudo da Categoria -->
          <button
            :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold shadow-sm transition d-flex align-items-center gap-1', getCategoryTheme(cat).btnOutline]"
            @click="openCategorySection(cat)"
          >
            <span>Ver tudo</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

        <!-- Alerta se não houver itens cadastrados nesta categoria -->
        <div v-if="!cat.products || cat.products.length === 0" class="card border-0 bg-white shadow-sm rounded-4 p-4 text-center">
          <small class="text-muted">Nenhum produto cadastrado nesta categoria ainda.</small>
        </div>

        <!-- TRACK COM ROLAGEM HORIZONTAL SUAVE DOS CARDS DA CATEGORIA (COMBOS VERDES, MONTADORES AZUIS, RESTANTE VERMELHO) -->
        <div
          v-else
          class="d-flex gap-3 overflow-x-auto pb-2 no-scrollbar horizontal-scroll-track"
          style="scroll-snap-type: x mandatory;"
        >
          <div
            v-for="(product, prodIdx) in getCategoryPreviewItems(cat)"
            :key="'cat-item-' + product.id"
            class="flex-shrink-0"
            style="width: 290px; min-width: 290px; scroll-snap-align: start;"
          >
            <div
              :class="['card aky-card h-100 bg-white border-start border-4 shadow-sm rounded-4 overflow-hidden cursor-pointer transition', getItemTheme(product, cat).borderClass, !product.is_available ? 'opacity-75' : '']"
              @click="$emit('select-product', product, 'menu')"
            >
              <div class="d-flex p-3 gap-3">
                
                <!-- Foto do Produto -->
                <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative" style="width: 85px; height: 85px;">
                  <img
                    v-if="product.photo_path"
                    :src="product.photo_path"
                    :alt="product.name"
                    class="w-100 h-100 object-fit-cover"
                  >
                  <div v-else class="text-center text-muted">
                    <i :class="[getCategoryIcon(cat.slug), getItemTheme(product, cat).iconClass, 'fs-2 opacity-75 d-block']"></i>
                    <span style="font-size: 0.65rem;" :class="['fw-semibold', getItemTheme(product, cat).iconClass]">AKY</span>
                  </div>
                </div>

                <!-- Detalhes do Produto -->
                <div class="flex-grow-1 d-flex flex-column justify-content-between">
                  <div>
                    <div class="d-flex justify-content-between align-items-start gap-1">
                      <h6 class="fw-bold text-dark mb-1 text-truncate" :title="product.name" style="font-size: 0.92rem; max-width: 145px;">
                        {{ product.name }}
                      </h6>
                      <span v-if="!product.is_available" class="badge bg-secondary small" style="font-size: 0.65rem;">Esgotado</span>
                    </div>

                    <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 145px;" :title="product.description">
                      {{ product.description || 'Preparo artesanal com muito sabor.' }}
                    </p>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                    <div>
                      <div v-if="product.is_promotion && product.promotional_price">
                        <span class="text-muted small text-decoration-line-through d-block" style="font-size: 0.70rem;">
                          R$ {{ formatPrice(product.price) }}
                        </span>
                        <span :class="['fw-bold fs-6', getItemTheme(product, cat).priceClass]">
                          R$ {{ formatPrice(product.promotional_price) }}
                        </span>
                      </div>
                      <div v-else>
                        <span :class="['fw-bold fs-6', getItemTheme(product, cat).priceClass]">
                          R$ {{ formatPrice(product.effective_price || product.price) }}
                        </span>
                      </div>
                    </div>

                    <button
                      :class="['btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0', getItemTheme(product, cat).btnClass]"
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

          <!-- Card de Atalho para "Ver Todos da Categoria" no Final do Carrossel (Em todas as categorias) -->
          <div
            v-if="cat.products && cat.products.length > 0"
            class="flex-shrink-0 d-flex align-items-center justify-content-center"
            style="width: 170px; min-width: 170px; scroll-snap-align: start;"
          >
            <div
              :class="['card aky-card border-2 border-dashed bg-white p-3 rounded-4 text-center cursor-pointer h-100 w-100 d-flex flex-column align-items-center justify-content-center transition shadow-sm', getCategoryTheme(cat).borderDashed]"
              @click="openCategorySection(cat)"
            >
              <div :class="['rounded-circle p-3 mb-2', getCategoryTheme(cat).headerIcon]">
                <i class="bi bi-arrow-right fs-4"></i>
              </div>
              <span class="fw-bold text-dark small d-block">Ver todos</span>
              <small class="text-muted" style="font-size: 0.72rem;">
                {{ cat.products.length > 3 ? '+' + (cat.products.length - 3) + ' opções' : 'Explorar tudo' }}
              </small>
            </div>
          </div>

        </div>

      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'Menu',
  props: {
    categories: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    isCustomizable(product) {
      if (!product) return false;
      const name = (product.name || '').toLowerCase();
      const catSlug = (product.category && product.category.slug) ? product.category.slug.toLowerCase() : '';
      if (name.includes('monte seu pastel') || name.includes('monte seu')) return true;
      if (catSlug.includes('pizza') || name.startsWith('pizza') || (name.includes('pizza') && !catSlug.includes('pastel'))) return true;
      if (catSlug.includes('tapioca') || name.includes('tapioca')) return true;
      if (catSlug.includes('cuscuz') || name.includes('cuscuz')) return true;
      return false;
    },
    isDrink(product, cat = null) {
      const catSlug = (cat?.slug || product?.category?.slug || '').toLowerCase();
      const catName = (cat?.name || product?.category?.name || '').toLowerCase();
      const prodName = (product?.name || '').toLowerCase();
      const terms = ['bebida', 'suco', 'refrigerante', 'refrig', 'cerveja', 'agua', 'água', 'drink', 'coca', 'guaraná', 'guarana', 'fanta', 'sprite'];
      return terms.some(t => catSlug.includes(t) || catName.includes(t) || prodName.includes(t));
    },
    getItemTheme(product, cat = null) {
      if (product?.is_promotion) {
        return {
          borderClass: 'card-promo-rgb',
          btnClass: 'btn-rgb text-white shadow-sm',
          priceClass: 'text-rgb',
          iconClass: 'text-danger',
        };
      }
      if (this.isDrink(product, cat)) {
        return {
          borderClass: 'border-warning',
          btnClass: 'btn-warning text-dark fw-bold',
          priceClass: 'text-warning-emphasis fw-bold',
          iconClass: 'text-warning',
        };
      }
      const isCombo = product?.is_combo || cat?.slug === 'combos-especiais' || (cat?.name && cat.name.toLowerCase().includes('combo'));
      if (isCombo) {
        return {
          borderClass: 'border-success',
          btnClass: 'btn-success text-white',
          priceClass: 'text-success fw-bold',
          iconClass: 'text-success',
        };
      }
      const isBuilder = this.isCustomizable(product) || (cat && (cat.slug.includes('pizza') || cat.slug.includes('monte') || cat.slug.includes('tapioca') || cat.slug.includes('cuscuz')));
      if (isBuilder) {
        return {
          borderClass: 'border-primary',
          btnClass: 'btn-primary text-white',
          priceClass: 'text-primary fw-bold',
          iconClass: 'text-primary',
        };
      }
      return {
        borderClass: 'border-danger',
        btnClass: 'btn-danger text-white',
        priceClass: 'text-danger fw-bold',
        iconClass: 'text-danger',
      };
    },
    getCategoryTheme(cat) {
      if (this.isDrink(null, cat)) {
        return {
          color: 'warning',
          headerIcon: 'bg-warning text-dark',
          badge: 'bg-warning bg-opacity-25 text-dark border border-warning',
          btnOutline: 'btn-outline-dark',
          borderDashed: 'border-warning',
        };
      }
      const slug = (cat?.slug || '').toLowerCase();
      const name = (cat?.name || '').toLowerCase();
      if (slug.includes('combo') || name.includes('combo')) {
        return {
          color: 'success',
          headerIcon: 'bg-success text-white',
          badge: 'bg-success bg-opacity-10 text-success border border-success',
          btnOutline: 'btn-outline-success',
          borderDashed: 'border-success',
        };
      }
      if (slug.includes('pizza') || slug.includes('monte') || slug.includes('tapioca') || slug.includes('cuscuz')) {
        return {
          color: 'primary',
          headerIcon: 'bg-primary text-white',
          badge: 'bg-primary bg-opacity-10 text-primary border border-primary',
          btnOutline: 'btn-outline-primary',
          borderDashed: 'border-primary',
        };
      }
      return {
        color: 'danger',
        headerIcon: 'bg-danger text-white',
        badge: 'bg-danger bg-opacity-10 text-danger border border-danger',
        btnOutline: 'btn-outline-danger',
        borderDashed: 'border-danger',
      };
    },
    handleCardAdd(product) {
      if (this.isCustomizable(product)) {
        this.$emit('select-product', product, 'menu');
      } else {
        this.$emit('add-to-cart', product);
      }
    },
    getCategoryPreviewItems(cat) {
      if (!cat.products) return [];
      return cat.products.slice(0, 3);
    },
    getCategoryIcon(slug) {
      if (!slug) return 'bi-tag-fill';
      if (slug.includes('pastel') || slug.includes('pasteis')) return 'bi-circle-half';
      if (slug.includes('pizza')) return 'bi-pie-chart-fill';
      if (slug.includes('salgado') || slug.includes('petisco')) return 'bi-egg-fried';
      if (slug.includes('suco') || slug.includes('bebida')) return 'bi-cup-straw';
      if (slug.includes('doce') || slug.includes('sobremesa')) return 'bi-cake2';
      if (slug.includes('sanduiche') || slug.includes('artesanal') || slug.includes('classico')) return 'bi-flower1';
      return 'bi-bag-heart-fill';
    },
    openCategorySection(cat) {
      const theme = this.getCategoryTheme(cat);
      this.$emit('open-section', {
        title: cat.name,
        subtitle: 'Confira todos os lanches e opções disponíveis em ' + cat.name,
        icon: this.getCategoryIcon(cat.slug),
        themeColor: theme.color,
        badgeText: (cat.products ? cat.products.length : 0) + ' opções',
        items: cat.products || [],
        backTo: 'menu',
      });
    },
  },
};
</script>

<style scoped>
.transition {
  transition: all 0.2s ease-in-out;
}
.horizontal-scroll-track {
  -webkit-overflow-scrolling: touch;
}
.cursor-pointer {
  cursor: pointer;
}
.text-warning-emphasis {
  color: #b45309 !important;
}
</style>

<template>
  <div class="unified-menu pb-5">
    
    <!-- 1. BARRA DE BUSCA EM TEMPO REAL (STICKY-COMPATÍVEL & MOBILE-FIRST) -->
    <div class="search-bar-container mb-3">
      <div class="input-group bg-white rounded-pill shadow-sm border p-1 align-items-center">
        <span class="input-group-text bg-transparent border-0 text-aky-primary ps-3 pe-2">
          <i class="bi bi-search fs-5"></i>
        </span>
        <input
          type="text"
          class="form-control border-0 shadow-none bg-transparent py-2 text-dark fw-semibold"
          v-model="searchTerm"
          placeholder="Buscar lanche, pastel, pizza, bebida..."
          autocomplete="off"
        >
        <button
          v-if="searchTerm"
          class="btn btn-link text-muted border-0 text-decoration-none pe-3 py-0"
          type="button"
          @click="searchTerm = ''"
          title="Limpar pesquisa"
        >
          <i class="bi bi-x-circle-fill fs-5"></i>
        </button>
      </div>

      <!-- Indicador de busca ativa -->
      <div v-if="searchTerm.trim()" class="d-flex justify-content-between align-items-center mt-2 px-2 small">
        <span class="text-muted">
          Resultados para <strong class="text-dark">"{{ searchTerm }}"</strong>:
          <span class="badge bg-aky-primary text-white rounded-pill ms-1">{{ totalSearchResults }} itens</span>
        </span>
        <button class="btn btn-link btn-sm text-decoration-none text-aky-primary p-0 fw-semibold" @click="searchTerm = ''">
          Limpar busca
        </button>
      </div>
    </div>

    <!-- 2. CARROSSEL DE BOTÕES / CHIPS DE FILTRO POR CATEGORIA (ROLAGEM HORIZONTAL SUAVE) -->
    <div class="category-chips-wrapper mb-4">
      <div class="d-flex gap-2 overflow-x-auto no-scrollbar py-1 px-1">
        
        <!-- Chip "Todos" -->
        <button
          type="button"
          :class="[
            'btn btn-sm rounded-3 px-3 py-2 fw-bold text-nowrap transition d-inline-flex align-items-center gap-1.5 flex-shrink-0 shadow-2xs',
            activeFilter === 'all'
              ? 'btn-aky-primary text-white shadow-sm'
              : 'btn-white bg-white text-dark border'
          ]"
          @click="selectFilter('all')"
        >
          <i class="bi bi-grid-fill"></i>
          <span>Todos</span>
          <span :class="['badge rounded-2', activeFilter === 'all' ? 'bg-white text-aky-primary' : 'bg-light text-muted']" style="font-size: 0.68rem;">
            {{ totalProductsCount }}
          </span>
        </button>

        <!-- Chip Especial: Promoções (se houver) -->
        <button
          v-if="promotions && promotions.length > 0"
          type="button"
          :class="[
            'btn btn-sm rounded-3 px-3 py-2 fw-bold text-nowrap transition d-inline-flex align-items-center gap-1.5 flex-shrink-0 shadow-2xs',
            activeFilter === 'promotions'
              ? 'btn-danger text-white shadow-sm'
              : 'btn-white bg-white text-danger border border-danger-subtle'
          ]"
          @click="selectFilter('promotions')"
        >
          <i class="bi bi-fire"></i>
          <span>Promoções</span>
          <span :class="['badge rounded-2', activeFilter === 'promotions' ? 'bg-white text-danger' : 'bg-danger bg-opacity-10 text-danger']" style="font-size: 0.68rem;">
            {{ promotions.length }}
          </span>
        </button>

        <!-- Chip Especial: Mais Pedidos (se houver) -->
        <button
          v-if="popular && popular.length > 0"
          type="button"
          :class="[
            'btn btn-sm rounded-3 px-3 py-2 fw-bold text-nowrap transition d-inline-flex align-items-center gap-1.5 flex-shrink-0 shadow-2xs',
            activeFilter === 'popular'
              ? 'btn-purple text-white shadow-sm'
              : 'btn-white bg-white text-dark border'
          ]"
          @click="selectFilter('popular')"
        >
          <i class="bi bi-star-fill text-warning"></i>
          <span>Mais Pedidos</span>
          <span :class="['badge rounded-2', activeFilter === 'popular' ? 'bg-white text-purple' : 'bg-light text-muted']" style="font-size: 0.68rem;">
            {{ popular.length }}
          </span>
        </button>

        <!-- Chip Especial: Combos (se houver) -->
        <button
          v-if="combos && combos.length > 0"
          type="button"
          :class="[
            'btn btn-sm rounded-3 px-3 py-2 fw-bold text-nowrap transition d-inline-flex align-items-center gap-1.5 flex-shrink-0 shadow-2xs',
            activeFilter === 'combos'
              ? 'btn-success text-white shadow-sm'
              : 'btn-white bg-white text-success border border-success-subtle'
          ]"
          @click="selectFilter('combos')"
        >
          <i class="bi bi-gift-fill"></i>
          <span>Combos</span>
          <span :class="['badge rounded-2', activeFilter === 'combos' ? 'bg-white text-success' : 'bg-success bg-opacity-10 text-success']" style="font-size: 0.68rem;">
            {{ combos.length }}
          </span>
        </button>

        <!-- Chips das Categorias Reais -->
        <button
          v-for="cat in availableCategories"
          :key="'chip-cat-' + cat.id"
          type="button"
          :class="[
            'btn btn-sm rounded-3 px-3 py-2 fw-bold text-nowrap transition d-inline-flex align-items-center gap-1.5 flex-shrink-0 shadow-2xs',
            activeFilter === 'cat_' + cat.id
              ? 'btn-dark text-white shadow-sm'
              : 'btn-white bg-white text-dark border'
          ]"
          @click="selectFilter('cat_' + cat.id)"
        >
          <i :class="['bi', getCategoryIcon(cat.slug)]"></i>
          <span>{{ cat.name }}</span>
          <span :class="['badge rounded-2', activeFilter === 'cat_' + cat.id ? 'bg-white text-dark' : 'bg-light text-muted']" style="font-size: 0.68rem;">
            {{ cat.products ? cat.products.length : 0 }}
          </span>
        </button>

      </div>
    </div>

    <!-- 3. FEED UNIFICADO COM ITENS SEPARADOS POR LINHAS E AGRUPADOS POR CATEGORIA -->
    
    <!-- Estado de Busca sem Resultados -->
    <div v-if="filteredSections.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
      <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-3 text-muted" style="width: 60px; height: 60px;">
        <i class="bi bi-search fs-3"></i>
      </div>
      <h6 class="fw-bold text-dark mb-1">Nenhum produto encontrado</h6>
      <p class="text-muted small mb-3">Não encontramos nenhum item correspondente à sua pesquisa ou filtro.</p>
      <button class="btn btn-aky-primary rounded-pill btn-sm px-4 fw-semibold" @click="resetFilters">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Mostrar Todos os Produtos
      </button>
    </div>

    <!-- Lista de Seções -->
    <div v-else class="sections-container d-flex flex-column gap-4">
      
      <section
        v-for="section in filteredSections"
        :key="section.key"
        class="category-section"
      >
        <!-- CABEÇALHO DA CATEGORIA COM LINHA DECORATIVA -->
        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div :class="['rounded-circle p-2 d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0', section.iconBgClass]" style="width: 36px; height: 36px;">
              <i :class="['bi', section.icon, section.iconColorClass, 'fs-5']"></i>
            </div>
            <div>
              <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <span>{{ section.title }}</span>
                <span class="badge bg-light text-muted border rounded-pill fw-normal small" style="font-size: 0.72rem;">
                  {{ section.items.length }} {{ section.items.length === 1 ? 'item' : 'itens' }}
                </span>
              </h5>
              <small v-if="section.subtitle" class="text-muted d-block" style="font-size: 0.75rem;">
                {{ section.subtitle }}
              </small>
            </div>
          </div>
        </div>

        <!-- LISTA DE PRODUTOS SEPARADOS POR LINHAS (ROWS) -->
        <div class="products-list d-flex flex-column gap-2.5">
          
          <div
            v-for="product in section.items"
            :key="'prod-' + product.id"
            class="product-row card border-0 shadow-sm rounded-4 p-3 bg-white transition cursor-pointer border-start border-4"
            :class="[
              !product.is_available ? 'opacity-75 bg-light' : 'hover-lift',
              getItemTheme(product, section).borderClass
            ]"
            @click="onRowClick(product)"
          >
            <div class="d-flex align-items-stretch gap-3">
              
              <!-- Miniatura da Foto com fallback moderno e dimensões ampliadas -->
              <div
                class="product-thumb rounded-4 overflow-hidden bg-light d-flex align-items-center justify-content-center flex-shrink-0 border position-relative"
              >
                <img
                  v-if="product.photo_path"
                  :src="product.photo_path"
                  :alt="product.name"
                  class="w-100 h-100 object-fit-cover"
                  loading="lazy"
                >
                <div v-else class="text-center text-muted">
                  <i :class="['bi', getCategoryIcon(product.category?.slug), getItemTheme(product, section).iconClass, 'fs-1 opacity-75']"></i>
                </div>

                <!-- Badge Temática Flutuante sobre a Foto -->
                <span
                  v-if="getItemTheme(product, section).badgeText"
                  :class="['badge position-absolute top-0 start-0 m-1.5 px-2 py-0.5 rounded-pill shadow-sm fw-bold', getItemTheme(product, section).badgeClass]"
                  style="font-size: 0.62rem;"
                >
                  {{ getItemTheme(product, section).badgeText }}
                </span>
              </div>

              <!-- Informações Principais (Nome, Descrição e Linha Inferior com Preço e Botão) -->
              <div class="flex-grow-1 min-w-0 d-flex flex-column justify-content-between py-0.5">
                <div>
                  <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                    <h6 class="fw-bold text-dark mb-0 line-clamp-1" :title="product.name" style="font-size: 1.02rem; letter-spacing: -0.2px;">
                      {{ product.name }}
                    </h6>
                    <span v-if="!product.is_available" class="badge bg-secondary rounded-pill flex-shrink-0" style="font-size: 0.65rem;">
                      Esgotado
                    </span>
                  </div>

                  <!-- Descrição em 2 linhas com ellipsis -->
                  <p class="text-secondary small mb-2 line-clamp-2" style="font-size: 0.82rem; line-height: 1.35;" :title="product.description">
                    {{ product.description || 'Preparo artesanal com os melhores ingredientes da casa.' }}
                  </p>
                </div>

                <!-- Linha Inferior: Preço à Esquerda e Botão de Ação à Direita (Sem Quebras!) -->
                <div class="d-flex align-items-center justify-content-between pt-1 gap-2 border-top border-light-subtle flex-wrap flex-sm-nowrap">
                  <!-- Área de Preço -->
                  <div class="d-flex align-items-baseline gap-1.5">
                    <div v-if="product.is_promotion && product.promotional_price" class="d-flex align-items-baseline gap-1.5">
                      <span class="text-muted text-decoration-line-through small" style="font-size: 0.75rem;">
                        R$ {{ formatPrice(product.price) }}
                      </span>
                      <span :class="['fw-bold fs-5', getItemTheme(product, section).priceClass]">
                        R$ {{ formatPrice(product.promotional_price) }}
                      </span>
                    </div>
                    <div v-else>
                      <span :class="['fw-bold fs-5', getItemTheme(product, section).priceClass]">
                        R$ {{ formatPrice(product.effective_price || product.price) }}
                      </span>
                    </div>

                    <!-- Tag discreta se for montador -->
                    <span v-if="isCustomizable(product)" class="badge bg-primary bg-opacity-10 text-primary rounded-pill small fw-semibold ms-1" style="font-size: 0.68rem;">
                      <i class="bi bi-sliders me-0.5"></i> Opções
                    </span>
                  </div>

                  <!-- Botão Ação [+] Ergonômico e Temático -->
                  <div class="flex-shrink-0" @click.stop>
                    <button
                      type="button"
                      :class="[
                        'btn rounded-pill d-inline-flex align-items-center justify-content-center shadow-sm transition fw-bold px-3 py-1.5 btn-sm',
                        getItemTheme(product, section).btnClass
                      ]"
                      :disabled="!product.is_available"
                      :title="isCustomizable(product) ? 'Personalizar ingredientes e opções' : 'Adicionar ao carrinho'"
                      @click="handleQuickAdd(product)"
                    >
                      <i :class="isCustomizable(product) ? 'bi-sliders me-1' : 'bi-plus-lg me-1'"></i>
                      <span>{{ isCustomizable(product) ? 'Montar' : 'Adicionar' }}</span>
                    </button>
                  </div>
                </div>

              </div>

            </div>
          </div>

        </div>

      </section>

    </div>

  </div>
</template>

<script>
export default {
  name: 'UnifiedMenu',
  props: {
    categories: {
      type: Array,
      default: () => [],
    },
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
  },
  data() {
    return {
      searchTerm: '',
      activeFilter: 'all', // 'all', 'promotions', 'popular', 'combos', ou 'cat_<id>'
    };
  },
  computed: {
    availableCategories() {
      return (this.categories || []).filter(c => c.is_active !== false && c.products && c.products.length > 0);
    },
    totalProductsCount() {
      let count = 0;
      this.availableCategories.forEach(c => {
        count += (c.products || []).length;
      });
      return count;
    },
    totalSearchResults() {
      return this.filteredSections.reduce((sum, sec) => sum + sec.items.length, 0);
    },
    // Monta todas as seções base disponíveis
    allSections() {
      const sections = [];

      // 1. Seção Especial de Promoções
      if (this.promotions && this.promotions.length > 0) {
        sections.push({
          key: 'promotions',
          filterKey: 'promotions',
          title: '🔥 Promoções Especiais',
          subtitle: 'Aproveite descontos exclusivos por tempo limitado',
          icon: 'bi-fire',
          iconColorClass: 'text-danger',
          iconBgClass: 'bg-danger bg-opacity-10',
          items: this.promotions,
        });
      }

      // 2. Seção Especial de Mais Pedidos
      if (this.popular && this.popular.length > 0) {
        sections.push({
          key: 'popular',
          filterKey: 'popular',
          title: '⭐ Mais Pedidos',
          subtitle: 'Os favoritos e mais vendidos da AKY Lanches',
          icon: 'bi-star-fill',
          iconColorClass: 'text-warning',
          iconBgClass: 'bg-warning bg-opacity-10',
          items: this.popular,
        });
      }

      // 3. Seção Especial de Combos
      if (this.combos && this.combos.length > 0) {
        sections.push({
          key: 'combos',
          filterKey: 'combos',
          title: '🎁 Combos do Dia',
          subtitle: 'Combinações completas com lanche, batata e bebida',
          icon: 'bi-gift-fill',
          iconColorClass: 'text-success',
          iconBgClass: 'bg-success bg-opacity-10',
          items: this.combos,
        });
      }

      // 4. Categorias Reais do Cardápio
      this.availableCategories.forEach(cat => {
        const theme = this.getCategoryThemeClasses(cat.slug);
        sections.push({
          key: 'cat_' + cat.id,
          filterKey: 'cat_' + cat.id,
          title: cat.name,
          subtitle: '',
          icon: this.getCategoryIcon(cat.slug),
          iconColorClass: theme.iconColorClass,
          iconBgClass: theme.iconBgClass,
          items: cat.products || [],
        });
      });

      return sections;
    },
    // Filtra as seções aplicando o chip selecionado e o termo de pesquisa
    filteredSections() {
      const term = this.searchTerm.trim().toLowerCase();
      const normalize = str => (str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
      const normTerm = normalize(term);

      // Determina quais seções considerar de acordo com o filtro ativo
      let targetSections = [];
      if (this.activeFilter === 'all') {
        // No modo "Todos", remove seções especiais duplicadas se estiverem apenas espelhando produtos de categorias,
        // mas mantém as categorias reais para uma lista limpa e contínua.
        // Se houver promoções, combos ou populares, podemos exibi-los no topo se não houver busca.
        if (normTerm) {
          // Na busca, busca em todas as categorias reais para evitar itens duplicados
          targetSections = this.allSections.filter(s => s.key.startsWith('cat_'));
        } else {
          targetSections = this.allSections;
        }
      } else {
        targetSections = this.allSections.filter(s => s.filterKey === this.activeFilter);
      }

      // Aplica o filtro de busca por produto se houver termo digitado
      const result = [];
      targetSections.forEach(section => {
        let matchingItems = section.items;
        if (normTerm) {
          matchingItems = section.items.filter(item => {
            const name = normalize(item.name);
            const desc = normalize(item.description);
            return name.includes(normTerm) || desc.includes(normTerm);
          });
        }

        if (matchingItems.length > 0) {
          result.push({
            ...section,
            items: matchingItems,
          });
        }
      });

      return result;
    },
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    selectFilter(key) {
      this.activeFilter = key;
    },
    resetFilters() {
      this.searchTerm = '';
      this.activeFilter = 'all';
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
    isDrink(product, section = null) {
      const catSlug = (section?.key || product?.category?.slug || '').toLowerCase();
      const catName = (section?.title || product?.category?.name || '').toLowerCase();
      const prodName = (product?.name || '').toLowerCase();
      const terms = ['bebida', 'suco', 'refrigerante', 'refrig', 'cerveja', 'agua', 'água', 'drink', 'coca', 'guaraná', 'guarana', 'fanta', 'sprite'];
      return terms.some(t => catSlug.includes(t) || catName.includes(t) || prodName.includes(t));
    },
    getItemTheme(product, section = null) {
      if (product?.is_promotion) {
        return {
          borderClass: 'card-promo-rgb',
          badgeClass: 'badge-rgb',
          badgeText: '🔥 PROMO',
          priceClass: 'text-rgb',
          btnClass: 'btn-rgb',
          iconClass: 'text-danger',
        };
      }
      const isCombo = product?.is_combo || (section && section.key === 'combos') || (product?.category?.slug === 'combos-especiais') || ((product?.category?.name || '').toLowerCase().includes('combo'));
      if (isCombo) {
        return {
          borderClass: 'border-success',
          badgeClass: 'bg-success text-white',
          badgeText: '🎁 COMBO',
          priceClass: 'text-success',
          btnClass: 'btn-success text-white',
          iconClass: 'text-success',
        };
      }
      const isPopular = (section && section.key === 'popular');
      if (isPopular) {
        return {
          borderClass: 'border-purple',
          badgeClass: 'bg-purple text-white',
          badgeText: '⭐ MAIS PEDIDO',
          priceClass: 'text-purple',
          btnClass: 'btn-purple text-white',
          iconClass: 'text-purple',
        };
      }
      if (this.isDrink(product, section)) {
        return {
          borderClass: 'border-warning',
          badgeClass: 'bg-warning text-dark',
          badgeText: '',
          priceClass: 'text-warning-emphasis',
          btnClass: 'btn-warning text-dark fw-bold',
          iconClass: 'text-warning',
        };
      }
      if (this.isCustomizable(product)) {
        return {
          borderClass: 'border-primary',
          badgeClass: 'bg-primary text-white',
          badgeText: '🍕 MONTE',
          priceClass: 'text-primary',
          btnClass: 'btn-primary text-white',
          iconClass: 'text-primary',
        };
      }
      return {
        borderClass: 'border-danger',
        badgeClass: '',
        badgeText: '',
        priceClass: 'text-danger',
        btnClass: 'btn-aky-primary text-white',
        iconClass: 'text-danger',
      };
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
    getCategoryThemeClasses(slug) {
      const s = (slug || '').toLowerCase();
      if (s.includes('bebida') || s.includes('suco') || s.includes('refrig')) {
        return { iconColorClass: 'text-warning', iconBgClass: 'bg-warning bg-opacity-15' };
      }
      if (s.includes('combo')) {
        return { iconColorClass: 'text-success', iconBgClass: 'bg-success bg-opacity-15' };
      }
      if (s.includes('pizza') || s.includes('monte') || s.includes('tapioca') || s.includes('cuscuz')) {
        return { iconColorClass: 'text-primary', iconBgClass: 'bg-primary bg-opacity-15' };
      }
      return { iconColorClass: 'text-danger', iconBgClass: 'bg-danger bg-opacity-10' };
    },
    onRowClick(product) {
      this.$emit('select-product', product, 'menu');
    },
    handleQuickAdd(product) {
      if (this.isCustomizable(product)) {
        this.$emit('select-product', product, 'menu');
      } else {
        this.$emit('add-to-cart', product);
      }
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
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.35rem 0.95rem rgba(0, 0, 0, 0.08) !important;
}
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Dimensões ampliadas da miniatura do produto */
.product-thumb {
  width: 88px;
  height: 88px;
  min-width: 88px;
}
@media (min-width: 576px) {
  .product-thumb {
    width: 96px;
    height: 96px;
    min-width: 96px;
  }
}

/* Cores e temas utilitários */
.bg-purple {
  background-color: #7c3aed !important;
}
.text-purple {
  color: #7c3aed !important;
}
.border-purple {
  border-left-color: #7c3aed !important;
}
.btn-purple {
  background-color: #7c3aed !important;
  border-color: #7c3aed !important;
  color: #ffffff !important;
}
.btn-purple:hover, .btn-purple:focus {
  background-color: #6d28d9 !important;
  border-color: #6d28d9 !important;
  color: #ffffff !important;
}

.text-warning-emphasis {
  color: #b45309 !important;
}

.product-row {
  border: 1px solid rgba(0,0,0,0.08) !important;
}
</style>


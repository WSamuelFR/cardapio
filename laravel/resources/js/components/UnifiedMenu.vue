<template>
  <div class="unified-menu pb-5">
    
    <!-- 1. BARRA DE BUSCA EM TEMPO REAL E ATALHOS RÁPIDOS DE 1 TOQUE -->
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

      <!-- Sugestões Rápidas de 1 Toque (Para Facilitar Quem Tem Dificuldade de Digitar) -->
      <div class="quick-tags d-flex align-items-center gap-1.5 overflow-x-auto no-scrollbar pt-2 px-1">
        <span class="text-muted small fw-semibold flex-shrink-0 d-none d-sm-inline" style="font-size: 0.75rem;">
          <i class="bi bi-lightning-charge-fill text-warning me-0.5"></i> Sugestões:
        </span>
        <button
          v-for="tag in quickSearchTags"
          :key="tag.label"
          type="button"
          :class="[
            'btn btn-sm rounded-pill py-1 px-2.5 text-nowrap fw-semibold d-inline-flex align-items-center gap-1 shadow-2xs transition flex-shrink-0',
            searchTerm.toLowerCase() === tag.query.toLowerCase()
              ? 'btn-dark text-white'
              : 'btn-white bg-white text-secondary border'
          ]"
          style="font-size: 0.75rem;"
          @click="applyQuickSearch(tag.query)"
        >
          <span>{{ tag.icon }}</span>
          <span>{{ tag.label }}</span>
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

    <!-- MENSAGEM AMIGÁVEL DE ORIENTAÇÃO PARA LEIGOS -->
    <div class="d-none d-sm-flex align-items-center justify-content-between bg-white rounded-4 p-2.5 px-3 border shadow-2xs mb-3 text-muted small">
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-1 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
          <i class="bi bi-hand-index-thumb-fill"></i>
        </div>
        <span>Toque em qualquer item para ver ingredientes e opções, ou no botão redondo <strong>(+)</strong> para pedir direto.</span>
      </div>
      <span class="badge bg-light text-dark border rounded-pill fw-semibold">{{ totalProductsCount }} opções no cardápio</span>
    </div>

    <!-- 2. CARROSSEL DE BOTÕES / CHIPS DE FILTRO POR CATEGORIA (STICKY & ROLAGEM HORIZONTAL SUAVE) -->
    <div class="category-chips-wrapper mb-3">
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

      <!-- Barra de Filtro Ativo com Botão Fácil de "Ver Todos" -->
      <div v-if="activeFilter !== 'all'" class="d-flex align-items-center justify-content-between bg-white rounded-3 px-3 py-2 mt-2 border shadow-2xs">
        <div class="small text-dark d-flex align-items-center gap-2">
          <span class="badge bg-aky-primary text-white rounded-pill px-2 py-1">Filtro</span>
          <span>Exibindo: <strong>{{ activeFilterName }}</strong></span>
        </div>
        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold text-nowrap" @click="selectFilter('all')">
          <i class="bi bi-arrow-counterclockwise me-1"></i> Ver Todos
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
        :id="'sec-' + section.key"
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

        <!-- LISTA DE PRODUTOS: 1 COLUNA NO CELULAR, 2 COLUNAS NO PC -->
        <div class="row row-cols-1 row-cols-lg-2 g-3 products-grid">
          
          <div
            v-for="product in section.items"
            :key="'prod-' + product.id"
            class="col"
          >
            <div
              :class="[
                'card aky-card h-100 bg-white shadow-sm rounded-4 overflow-hidden cursor-pointer transition',
                getItemTheme(product, section).borderClass,
                !product.is_available ? 'opacity-75 bg-light' : 'hover-lift'
              ]"
              @click="onRowClick(product)"
            >
              <div class="d-flex p-3 gap-3 h-100">
                
                <!-- Miniatura da Foto no estilo da área de sugestões (85x85px) -->
                <div
                  class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative"
                  style="width: 85px; height: 85px;"
                >
                  <img
                    v-if="product.photo_path"
                    :src="product.photo_path"
                    :alt="product.name"
                    class="w-100 h-100 object-fit-cover"
                    loading="lazy"
                  >
                  <div v-else class="text-center text-muted">
                    <i :class="['bi', getCategoryIcon(product.category?.slug), getItemTheme(product, section).iconClass, 'fs-2 opacity-75 d-block']"></i>
                    <span style="font-size: 0.65rem;" :class="['fw-semibold', getItemTheme(product, section).iconClass]">
                      {{ product.is_combo ? 'Combo' : (product.is_promotion ? 'Promo' : 'AKY') }}
                    </span>
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

                <!-- Detalhes do Produto (Nome, Descrição e Linha Inferior com Preço e Botão) -->
                <div class="flex-grow-1 min-w-0 d-flex flex-column justify-content-between">
                  <div>
                    <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                      <h6 class="fw-bold text-dark mb-0 line-clamp-1" :title="product.name" style="font-size: 0.96rem; letter-spacing: -0.2px;">
                        {{ product.name }}
                      </h6>
                      <span v-if="!product.is_available" class="badge bg-secondary rounded-pill flex-shrink-0" style="font-size: 0.65rem;">
                        Esgotado
                      </span>
                    </div>

                    <!-- Descrição em 2 linhas com ellipsis -->
                    <p class="text-muted small mb-1 line-clamp-2" style="font-size: 0.78rem; line-height: 1.3;" :title="product.description">
                      {{ product.description || 'Preparo artesanal com os melhores ingredientes da casa.' }}
                    </p>
                  </div>

                  <!-- Linha Inferior: Preço à Esquerda e Botão de Ação Redondo à Direita -->
                  <div class="d-flex align-items-center justify-content-between pt-1.5 mt-2 border-top border-light-subtle flex-wrap flex-sm-nowrap">
                    <!-- Área de Preço -->
                    <div class="d-flex align-items-baseline gap-1.5">
                      <div v-if="product.is_promotion && product.promotional_price">
                        <span class="text-muted text-decoration-line-through d-block" style="font-size: 0.70rem;">
                          R$ {{ formatPrice(product.price) }}
                        </span>
                        <span :class="['fw-bold fs-6', getItemTheme(product, section).priceClass]">
                          R$ {{ formatPrice(product.promotional_price) }}
                        </span>
                      </div>
                      <div v-else>
                        <span :class="['fw-bold fs-6', getItemTheme(product, section).priceClass]">
                          R$ {{ formatPrice(product.effective_price || product.price) }}
                        </span>
                      </div>

                      <!-- Tag discreta se for montador / opções -->
                      <span v-if="isCustomizable(product)" class="badge bg-primary bg-opacity-10 text-primary rounded-pill small fw-semibold ms-1" style="font-size: 0.68rem;">
                        <i class="bi bi-sliders me-0.5"></i> Opções
                      </span>
                    </div>

                    <!-- Botão de Ação Redondo Estilo Sugestões (36x36px) -->
                    <div class="flex-shrink-0" @click.stop>
                      <button
                        type="button"
                        :class="[
                          'btn rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm transition p-0 flex-shrink-0',
                          getItemTheme(product, section).btnClass
                        ]"
                        style="width: 36px; height: 36px;"
                        :disabled="!product.is_available"
                        :title="!product.is_available ? 'Produto esgotado' : (isCustomizable(product) ? 'Personalizar ingredientes e opções' : 'Adicionar ao carrinho')"
                        :aria-label="isCustomizable(product) ? 'Montar item' : 'Adicionar ao carrinho'"
                        @click="handleQuickAdd(product)"
                      >
                        <i v-if="!product.is_available" class="bi bi-slash-circle fs-6"></i>
                        <i v-else-if="isCustomizable(product)" class="bi bi-sliders fs-6"></i>
                        <i v-else class="bi bi-plus-lg fs-6"></i>
                      </button>
                    </div>
                  </div>

                </div>

              </div>
            </div>
          </div>

        </div>

      </section>

    </div>

    <!-- Botão Flutuante Voltar ao Topo (⬆) -->
    <transition name="fade">
      <button
        v-if="showBackToTop"
        type="button"
        class="btn btn-dark rounded-circle shadow-lg position-fixed d-flex align-items-center justify-content-center back-to-top-btn"
        style="bottom: 78px; right: 18px; width: 46px; height: 46px; z-index: 1040;"
        title="Voltar ao início do cardápio"
        aria-label="Voltar ao topo"
        @click="scrollToTop"
      >
        <i class="bi bi-arrow-up fs-5"></i>
      </button>
    </transition>

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
      showBackToTop: false,
      quickSearchTags: [
        { label: 'Pastéis', icon: '🥟', query: 'pastel' },
        { label: 'Pizzas', icon: '🍕', query: 'pizza' },
        { label: 'Sanduíches', icon: '🍔', query: 'sanduiche' },
        { label: 'Bebidas', icon: '🥤', query: 'bebida' },
        { label: 'Promoções', icon: '🔥', query: 'promo' },
        { label: 'Combos', icon: '🎁', query: 'combo' },
      ],
    };
  },
  computed: {
    activeFilterName() {
      if (this.activeFilter === 'all') return 'Todos';
      if (this.activeFilter === 'promotions') return 'Promoções Especiais';
      if (this.activeFilter === 'popular') return 'Mais Pedidos';
      if (this.activeFilter === 'combos') return 'Combos do Dia';
      if (this.activeFilter && this.activeFilter.startsWith('cat_')) {
        const id = this.activeFilter.replace('cat_', '');
        const cat = this.availableCategories.find(c => String(c.id) === String(id));
        return cat ? cat.name : 'Categoria';
      }
      return '';
    },
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
  mounted() {
    window.addEventListener('scroll', this.handleScroll, { passive: true });
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    selectFilter(key) {
      this.activeFilter = key;
      if (key === 'all') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        this.$nextTick(() => {
          const el = document.getElementById('sec-' + key);
          if (el) {
            const yOffset = -115;
            const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
          }
        });
      }
    },
    applyQuickSearch(query) {
      if (this.searchTerm.toLowerCase() === query.toLowerCase()) {
        this.searchTerm = '';
      } else {
        this.searchTerm = query;
        this.activeFilter = 'all';
      }
    },
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    handleScroll() {
      this.showBackToTop = window.scrollY > 350;
    },
    resetFilters() {
      this.searchTerm = '';
      this.activeFilter = 'all';
      this.scrollToTop();
    },
    isCustomizable(product) {
      if (!product) return false;
      const name = (product.name || '').toLowerCase();
      const catSlug = (product.category && product.category.slug) ? product.category.slug.toLowerCase() : '';
      if (name.includes('monte seu pastel') || name.includes('monte seu')) return true;
      if (catSlug.includes('pizza') || name.startsWith('pizza') || (name.includes('pizza') && !catSlug.includes('pastel'))) return true;
      if (catSlug.includes('tapioca') || name.includes('tapioca')) return true;
      if (catSlug.includes('cuscuz') || name.includes('cuscuz')) return true;
      if (catSlug.includes('sanduiche') || catSlug.includes('hamburguer') || catSlug.includes('artesanal') || catSlug.includes('lanche') || name.includes('sanduiche') || name.includes('burger') || name.includes('hambúrguer')) return true;
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
          borderClass: 'card-theme-promo',
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
          borderClass: 'card-theme-combo',
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
          borderClass: 'card-theme-popular',
          badgeClass: 'bg-purple text-white',
          badgeText: '⭐ MAIS PEDIDO',
          priceClass: 'text-purple',
          btnClass: 'btn-purple text-white',
          iconClass: 'text-purple',
        };
      }
      if (this.isDrink(product, section)) {
        return {
          borderClass: 'card-theme-drink',
          badgeClass: 'bg-warning text-dark',
          badgeText: '',
          priceClass: 'text-warning-emphasis',
          btnClass: 'btn-warning text-dark fw-bold',
          iconClass: 'text-warning',
        };
      }
      if (this.isCustomizable(product)) {
        return {
          borderClass: 'card-theme-custom',
          badgeClass: 'bg-primary text-white',
          badgeText: '🍕 MONTE',
          priceClass: 'text-primary',
          btnClass: 'btn-primary text-white',
          iconClass: 'text-primary',
        };
      }
      return {
        borderClass: 'card-theme-default',
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

/* Barra Fixa de Categorias (Sticky) */
.category-chips-wrapper {
  position: sticky;
  top: 58px;
  z-index: 1015;
  background: rgba(248, 249, 250, 0.96);
  backdrop-filter: blur(8px);
  padding-top: 6px;
  padding-bottom: 6px;
  margin-left: -0.75rem;
  margin-right: -0.75rem;
  padding-left: 0.75rem;
  padding-right: 0.75rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

/* Botão Voltar ao Topo */
.back-to-top-btn {
  transition: all 0.25s ease-in-out;
}
.back-to-top-btn:hover {
  transform: translateY(-3px) scale(1.05);
  background-color: #000000 !important;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Espaçamento elegante entre os cards de produto */
.products-list {
  row-gap: 0.85rem !important;
}

/* Bordas coloridas completas dos cards conforme a regra do sistema */
.card-theme-promo {
  border: 1.5px solid #ff007f !important;
  border-left: 5px solid #ff007f !important;
  animation: rgbBorderFull 4s linear infinite !important;
}
@keyframes rgbBorderFull {
  0% { border-color: #ff0055; border-left-color: #ff0055; box-shadow: 0 4px 14px rgba(255, 0, 85, 0.15); }
  33% { border-color: #ffaa00; border-left-color: #ffaa00; box-shadow: 0 4px 14px rgba(255, 170, 0, 0.15); }
  66% { border-color: #00e5ff; border-left-color: #00e5ff; box-shadow: 0 4px 14px rgba(0, 229, 255, 0.15); }
  100% { border-color: #ff0055; border-left-color: #ff0055; box-shadow: 0 4px 14px rgba(255, 0, 85, 0.15); }
}

.card-theme-combo {
  border: 1.5px solid #198754 !important;
  border-left: 5px solid #198754 !important;
}

.card-theme-popular {
  border: 1.5px solid #7c3aed !important;
  border-left: 5px solid #7c3aed !important;
}

.card-theme-drink {
  border: 1.5px solid #f59e0b !important;
  border-left: 5px solid #f59e0b !important;
}

.card-theme-custom {
  border: 1.5px solid #0d6efd !important;
  border-left: 5px solid #0d6efd !important;
}

.card-theme-default {
  border: 1.5px solid #dc3545 !important;
  border-left: 5px solid #dc3545 !important;
}
</style>


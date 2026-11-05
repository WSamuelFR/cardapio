<template>
  <div class="unified-menu pb-5">
    
    <!-- 1. BARRA DE BUSCA EM TEMPO REAL -->
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

      <!-- Barra de Todas as Categorias no Estilo Pílula (Substitui as sugestões rápidas) -->
      <div class="category-pills-bar d-flex align-items-center gap-2 overflow-x-auto no-scrollbar pt-2 px-1 pb-1">
        <!-- Pílula Todos -->
        <button
          type="button"
          :class="[
            'btn btn-sm rounded-pill py-1.5 py-sm-2 px-3 text-nowrap fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs transition flex-shrink-0',
            activeFilter === 'all'
              ? 'btn-dark text-white'
              : 'btn-white bg-white text-secondary border'
          ]"
          style="font-size: 0.85rem;"
          @click="selectCategoryPill('all')"
        >
          <span style="font-size: 1.05rem;">📋</span>
          <span>Todos</span>
          <span
            class="badge rounded-pill ms-0.5 px-2 py-0.5"
            :class="activeFilter === 'all' ? 'bg-secondary text-white' : 'bg-light text-muted border'"
            style="font-size: 0.72rem;"
          >
            {{ totalProductsCount }}
          </span>
        </button>

        <!-- Pílulas de Cada Categoria / Seção -->
        <button
          v-for="sec in allSections"
          :key="'pill-' + sec.filterKey"
          type="button"
          :class="[
            'btn btn-sm rounded-pill py-1.5 py-sm-2 px-3 text-nowrap fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs transition flex-shrink-0',
            activeFilter === sec.filterKey
              ? 'btn-dark text-white'
              : 'btn-white bg-white text-secondary border'
          ]"
          style="font-size: 0.85rem;"
          @click="selectCategoryPill(sec.filterKey)"
        >
          <span style="font-size: 1.05rem;">{{ getCategoryEmoji(sec.key || sec.slug) }}</span>
          <span>{{ getSectionPillTitle(sec) }}</span>
          <span
            v-if="sec.items && sec.items.length"
            class="badge rounded-pill ms-0.5 px-2 py-0.5"
            :class="activeFilter === sec.filterKey ? 'bg-secondary text-white' : 'bg-light text-muted border'"
            style="font-size: 0.72rem;"
          >
            {{ sec.items.length }}
          </span>
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

    <!-- 2. MENU DE NAVEGAÇÃO DE CATEGORIAS TIPO LIVRO (STICKY) -->
    <div class="category-book-nav-wrapper mb-3">
      <div class="bg-white rounded-4 shadow-sm border p-2.5 px-3 d-flex align-items-center justify-content-between gap-2">
        
        <!-- Botão Anterior (<) - Apenas Ícone -->
        <button
          type="button"
          class="btn btn-light rounded-circle border shadow-2xs d-flex align-items-center justify-content-center p-0 flex-shrink-0 nav-book-btn"
          @click="goToPrevCategory"
          title="Categoria Anterior"
          aria-label="Categoria Anterior"
        >
          <i class="bi bi-chevron-left fs-5 text-dark fw-bold"></i>
        </button>

        <!-- Botão Abrir Modal de Categorias (📑) - Apenas Ícone -->
        <button
          type="button"
          class="btn btn-dark rounded-circle border-0 shadow-2xs d-flex align-items-center justify-content-center p-0 flex-shrink-0 nav-book-btn"
          @click="showCategoriesModal = true"
          title="Ver todas as categorias"
          aria-label="Ver todas as categorias"
        >
          <i class="bi bi-grid-3x3-gap-fill text-warning fs-5"></i>
        </button>

        <!-- Nome da Categoria Atual no Meio -->
        <div class="flex-grow-1 text-center min-w-0 px-2 cursor-pointer" @click="showCategoriesModal = true" title="Toque para ver todas as categorias">
          <div class="fw-bold text-dark text-truncate d-flex align-items-center justify-content-center gap-2" style="font-size: 1.05rem;">
            <span class="fs-4">{{ currentCategoryEmoji }}</span>
            <span class="text-truncate">{{ currentCategoryTitle }}</span>
            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
              {{ currentCategoryItemCount }}
            </span>
          </div>
          <small class="text-muted d-block mt-0.5" style="font-size: 0.70rem; line-height: 1;">
            Toque para escolher ou arraste ⟷
          </small>
        </div>

        <!-- Botão Próximo (>) - Apenas Ícone -->
        <button
          type="button"
          class="btn btn-light rounded-circle border shadow-2xs d-flex align-items-center justify-content-center p-0 flex-shrink-0 nav-book-btn"
          @click="goToNextCategory"
          title="Próxima Categoria"
          aria-label="Próxima Categoria"
        >
          <i class="bi bi-chevron-right fs-5 text-dark fw-bold"></i>
        </button>

      </div>

      <!-- Barra de Filtro Ativo com Botão Rápido de "Ver Todos" (se não estiver em 'all') -->
      <div v-if="activeFilter !== 'all'" class="d-flex align-items-center justify-content-between bg-white rounded-3 px-3 py-1.5 mt-2 border shadow-2xs">
        <div class="small text-dark d-flex align-items-center gap-2">
          <span class="badge bg-aky-primary text-white rounded-pill px-2 py-0.5">Filtro</span>
          <span class="text-truncate" style="font-size: 0.82rem;">Exibindo: <strong>{{ activeFilterName }}</strong></span>
        </div>
        <button class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-bold text-nowrap" style="font-size: 0.75rem;" @click="selectFilter('all', false)">
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

    <!-- Lista de Seções com Suporte a Swipe Lateral (Tipo Livro) e Transição Fluida -->
    <transition name="cat-switch" mode="out-in">
      <div
        v-if="filteredSections.length > 0"
        :key="activeFilter"
        class="sections-container d-flex flex-column gap-4"
        @touchstart.passive="handleTouchStart"
        @touchend="handleTouchEnd"
      >
        
        <section
          v-for="section in filteredSections"
          :key="section.key"
          :id="'sec-' + section.key"
          class="category-section"
        >
        <!-- CABEÇALHO DA CATEGORIA COM ÍCONE E EMOJI CORRIGIDO -->
        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0 bg-light border" style="width: 40px; height: 40px; font-size: 1.25rem;">
              <span>{{ getCategoryEmoji(section.key || section.slug) }}</span>
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

        <!-- LISTA DE PRODUTOS: 1 COLUNA NO CELULAR, 2 COLUNAS NO PC (CONFORME DESENHO DO USUÁRIO) -->
        <div class="row row-cols-1 row-cols-lg-2 g-3 products-grid">
          
          <div
            v-for="product in section.items"
            :key="'prod-' + product.id"
            class="col"
          >
            <div
              :class="[
                'card aky-card h-100 bg-white border shadow-2xs rounded-4 overflow-hidden cursor-pointer transition',
                !product.is_available ? 'opacity-75 bg-light' : 'hover-lift'
              ]"
              @click="onRowClick(product)"
            >
              <div class="d-flex p-3 p-sm-3.5 gap-3 h-100 justify-content-between align-items-stretch">
                
                <!-- 1. DETALHES À ESQUERDA (NOME, PREÇO E BOTÃO DO CARRINHO - SEM DESCRIÇÃO PARA POUPAR ESPAÇO) -->
                <div class="flex-grow-1 min-w-0 d-flex flex-column justify-content-between pe-1">
                  <div>
                    <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                      <!-- Nome do Produto -->
                      <h6 class="fw-bold text-dark mb-0 line-clamp-2" :title="product.name" style="font-size: 1.02rem; letter-spacing: -0.2px; line-height: 1.25;">
                        {{ product.name }}
                      </h6>
                      <span v-if="!product.is_available" class="badge bg-secondary rounded-pill flex-shrink-0 d-inline-block d-sm-none" style="font-size: 0.65rem;">
                        Esgotado
                      </span>
                    </div>
                  </div>

                  <!-- Linha Inferior com Preço e Botão do Carrinho no Canto Inferior Esquerdo -->
                  <div class="pt-2 mt-auto">
                    <!-- Preço & Tags -->
                    <div class="d-flex align-items-baseline gap-1.5 mb-2">
                      <div v-if="product.is_promotion && product.promotional_price" class="d-flex align-items-baseline gap-1">
                        <span class="text-muted text-decoration-line-through" style="font-size: 0.72rem;">
                          R$ {{ formatPrice(product.price) }}
                        </span>
                        <span class="fw-bold fs-5 text-danger">
                          R$ {{ formatPrice(product.promotional_price) }}
                        </span>
                      </div>
                      <div v-else>
                        <span class="fw-bold fs-5 text-dark">
                          R$ {{ formatPrice(product.effective_price || product.price) }}
                        </span>
                      </div>

                      <!-- Tag Esgotado se indisponível -->
                      <span v-if="!product.is_available" class="badge bg-secondary rounded-pill d-none d-sm-inline-block" style="font-size: 0.65rem;">
                        Esgotado
                      </span>
                    </div>

                    <!-- Botão do Carrinho (No Canto Inferior Esquerdo Conforme o Desenho) -->
                    <div @click.stop>
                      <button
                        type="button"
                        :class="[
                          'btn rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm transition p-0',
                          !product.is_available
                            ? 'btn-secondary text-white'
                            : (isCustomizable(product) ? 'btn-primary text-white' : 'btn-aky-primary text-white')
                        ]"
                        style="width: 38px; height: 38px;"
                        :disabled="!product.is_available"
                        :title="!product.is_available ? 'Produto esgotado' : (isCustomizable(product) ? 'Personalizar opções' : 'Adicionar ao carrinho')"
                        :aria-label="isCustomizable(product) ? 'Montar item' : 'Adicionar ao carrinho'"
                        @click="handleQuickAdd(product)"
                      >
                        <i v-if="!product.is_available" class="bi bi-slash-circle fs-6"></i>
                        <i v-else-if="isCustomizable(product)" class="bi bi-sliders fs-6"></i>
                        <i v-else class="bi bi-cart-plus-fill fs-5"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- 2. MINIATURA DA FOTO À DIREITA (AMPLIADA E 100% LIMPA SEM BOTÃO) -->
                <div class="flex-shrink-0 d-flex flex-column align-items-end justify-content-center">
                  <div
                    class="product-thumb rounded-3 overflow-hidden bg-light d-flex align-items-center justify-content-center border position-relative"
                  >
                    <img
                      v-if="product.photo_path"
                      :src="product.photo_path"
                      :alt="product.name"
                      class="w-100 h-100 object-fit-cover"
                      loading="lazy"
                    >
                    <div v-else class="text-center text-muted p-2">
                      <i :class="['bi', getCategoryIcon(product.category?.slug), 'text-danger fs-1 opacity-75 d-block']"></i>
                      <span style="font-size: 0.70rem;" class="fw-semibold text-danger">
                        {{ product.is_combo ? 'Combo' : (product.is_promotion ? 'Promo' : 'AKY') }}
                      </span>
                    </div>

                    <!-- Badge Promocional / Combo Discreta no Topo da Foto se houver -->
                    <span
                      v-if="product.is_promotion"
                      class="badge bg-danger text-white position-absolute top-0 start-0 m-1.5 px-2 py-0.5 rounded-pill shadow-sm fw-bold"
                      style="font-size: 0.62rem;"
                    >
                      PROMO
                    </span>
                    <span
                      v-else-if="product.is_combo"
                      class="badge bg-success text-white position-absolute top-0 start-0 m-1.5 px-2 py-0.5 rounded-pill shadow-sm fw-bold"
                      style="font-size: 0.62rem;"
                    >
                      COMBO
                    </span>
                  </div>
                </div>

              </div>
            </div>
          </div>

        </div>

      </section>

      </div>
    </transition>

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

    <!-- 4. MODAL DE CATEGORIAS CENTRALIZADO (PADRÃO LIVRO) -->
    <div
      v-if="showCategoriesModal"
      class="modal-categories-backdrop"
      @click="showCategoriesModal = false"
    ></div>

    <div
      v-if="showCategoriesModal"
      class="modal-categories-wrapper"
      role="dialog"
      aria-modal="true"
      @click.self="showCategoriesModal = false"
    >
      <div class="modal-dialog modal-dialog-centered modal-categories-dialog">
        <div class="modal-content border border-light-subtle shadow-lg rounded-4 overflow-hidden bg-white">
          
          <!-- Header do Modal -->
          <div class="modal-header border-bottom bg-white px-3 py-2.5 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-grid-3x3-gap-fill fs-6"></i>
              </div>
              <div>
                <h6 class="modal-title fw-bold text-dark mb-0">Categorias do Cardápio</h6>
                <small class="text-muted" style="font-size: 0.72rem;">Toque para ir direto aos itens</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close shadow-none"
              aria-label="Fechar"
              @click="showCategoriesModal = false"
            ></button>
          </div>

          <!-- Corpo do Modal com a Lista de Categorias -->
          <div class="modal-body p-2 px-3 bg-white" style="max-height: 70vh; overflow-y: auto;">
            <div class="list-group list-group-flush gap-1">
              
              <!-- Item: Ver Todos os Produtos -->
              <button
                type="button"
                :class="[
                  'list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2.5 rounded-3 border-0 transition hover-bg-gray',
                  activeFilter === 'all' ? 'bg-danger bg-opacity-10 text-danger fw-bold' : 'text-dark'
                ]"
                @click="selectCategoryFromModal('all')"
              >
                <div class="d-flex align-items-center gap-2.5">
                  <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; font-size: 1.25rem;">
                    <span>📋</span>
                  </div>
                  <div>
                    <div class="fw-bold" style="font-size: 0.92rem;">Todos os Produtos</div>
                    <small class="text-muted" style="font-size: 0.70rem;">Cardápio completo</small>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                    {{ totalProductsCount }} itens
                  </span>
                  <i class="bi bi-chevron-right text-muted small"></i>
                </div>
              </button>

              <hr class="my-1 border-light-subtle">

              <!-- Linhas de cada Categoria -->
              <button
                v-for="section in allSections"
                :key="'modal-sec-' + section.key"
                type="button"
                :class="[
                  'list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2.5 rounded-3 border-0 transition hover-bg-gray',
                  activeFilter === section.filterKey ? 'bg-danger bg-opacity-10 text-danger fw-bold' : 'text-dark'
                ]"
                @click="selectCategoryFromModal(section.filterKey)"
              >
                <div class="d-flex align-items-center gap-2.5">
                  <div class="rounded-circle d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0 bg-light border" style="width: 38px; height: 38px; font-size: 1.25rem;">
                    <span>{{ getCategoryEmoji(section.key || section.slug) }}</span>
                  </div>
                  <div>
                    <div class="fw-bold" style="font-size: 0.92rem;">{{ section.title }}</div>
                    <small v-if="section.subtitle" class="text-muted d-block line-clamp-1" style="font-size: 0.70rem;">
                      {{ section.subtitle }}
                    </small>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                    {{ section.items ? section.items.length : 0 }} itens
                  </span>
                  <i class="bi bi-chevron-right text-muted small"></i>
                </div>
              </button>

            </div>
          </div>

          <!-- Footer simples para fechar -->
          <div class="modal-footer border-top bg-white py-2 px-3">
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary rounded-pill px-4 fw-semibold w-100"
              @click="showCategoriesModal = false"
            >
              Fechar
            </button>
          </div>

        </div>
      </div>
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
      showCategoriesModal: false,
      showBackToTop: false,
      touchStartX: 0,
      touchStartY: 0,
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
    navigableSections() {
      return this.allSections || [];
    },
    currentCategoryIndex() {
      if (this.activeFilter === 'all') return -1;
      return this.navigableSections.findIndex(s => s.filterKey === this.activeFilter);
    },
    currentCategoryTitle() {
      if (this.activeFilter === 'all') return 'Todos os Produtos';
      const sec = this.navigableSections.find(s => s.filterKey === this.activeFilter);
      return sec ? sec.title : 'Cardápio';
    },
    currentCategoryEmoji() {
      if (this.activeFilter === 'all') return '📋';
      const sec = this.navigableSections.find(s => s.filterKey === this.activeFilter);
      return sec ? this.getCategoryEmoji(sec.key || sec.slug) : '🍽️';
    },
    currentCategoryItemCount() {
      if (this.activeFilter === 'all') return this.totalProductsCount + ' itens';
      const sec = this.navigableSections.find(s => s.filterKey === this.activeFilter);
      return sec && sec.items ? sec.items.length + ' itens' : '';
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
          title: 'Promoções Especiais',
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
          title: 'Mais Pedidos',
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
          title: 'Combos do Dia',
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
    selectFilter(key, shouldScroll = false) {
      this.activeFilter = key;
      if (shouldScroll) {
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
      }
    },
    selectCategoryFromModal(key) {
      this.showCategoriesModal = false;
      this.selectFilter(key, false);
    },
    selectCategoryPill(key) {
      this.searchTerm = '';
      this.selectFilter(key, false);
    },
    getSectionPillTitle(sec) {
      if (!sec) return '';
      if (sec.filterKey === 'promotions') return 'Promoções';
      if (sec.filterKey === 'popular') return 'Mais Pedidos';
      if (sec.filterKey === 'combos') return 'Combos';
      return (sec.title || '').replace(/^[\p{Emoji}\p{Extended_Pictographic}\s]+/u, '').trim();
    },
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    handleScroll() {
      this.showBackToTop = window.scrollY > 350;
    },
    resetFilters() {
      this.searchTerm = '';
      this.selectFilter('all', false);
      this.scrollToTop();
    },
    isCustomizable(product) {
      if (!product) return false;
      const name = (product.name || '').toLowerCase();
      const catSlug = (product.category && product.category.slug) ? product.category.slug.toLowerCase() : '';

      // Pastelão NÃO é pizza, mesmo que o sabor seja pizza!
      if (catSlug.includes('pastel') || name.includes('pastel') || name.includes('pastelao') || name.includes('pastelão')) {
        return name.includes('monte seu');
      }

      if (catSlug.includes('pizza') || name.startsWith('pizza') || name.includes('pizza')) return true;
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
    goToPrevCategory() {
      if (this.navigableSections.length === 0) return;
      const currentIdx = this.navigableSections.findIndex(s => s.filterKey === this.activeFilter);
      if (currentIdx > 0) {
        this.selectFilter(this.navigableSections[currentIdx - 1].filterKey, false);
      } else if (currentIdx === 0) {
        this.selectFilter('all', false);
      } else {
        this.selectFilter(this.navigableSections[this.navigableSections.length - 1].filterKey, false);
      }
    },
    goToNextCategory() {
      if (this.navigableSections.length === 0) return;
      const currentIdx = this.navigableSections.findIndex(s => s.filterKey === this.activeFilter);
      if (currentIdx === -1) {
        this.selectFilter(this.navigableSections[0].filterKey, false);
      } else if (currentIdx < this.navigableSections.length - 1) {
        this.selectFilter(this.navigableSections[currentIdx + 1].filterKey, false);
      } else {
        this.selectFilter('all', false);
      }
    },
    handleTouchStart(e) {
      if (e.changedTouches && e.changedTouches.length > 0) {
        this.touchStartX = e.changedTouches[0].screenX;
        this.touchStartY = e.changedTouches[0].screenY;
      }
    },
    handleTouchEnd(e) {
      if (!e.changedTouches || e.changedTouches.length === 0) return;
      const deltaX = e.changedTouches[0].screenX - this.touchStartX;
      const deltaY = e.changedTouches[0].screenY - this.touchStartY;
      if (Math.abs(deltaX) > 50 && Math.abs(deltaX) > Math.abs(deltaY) * 1.3) {
        if (deltaX > 0) {
          this.goToPrevCategory();
        } else {
          this.goToNextCategory();
        }
      }
    },
    getCategoryEmoji(slugOrKey) {
      const s = (slugOrKey || '').toLowerCase();
      if (s.includes('sanduiche') || s.includes('hamburguer') || s.includes('artesanal') || s.includes('classico') || s.includes('lanche') || s.includes('burger')) return '🍔';
      if (s.includes('pastel') || s.includes('pasteis')) return '🥟';
      if (s.includes('pizza') && s.includes('doce')) return '🍫';
      if (s.includes('pizza')) return '🍕';
      if (s.includes('tapioca')) return '🫓';
      if (s.includes('cuscuz')) return '🍲';
      if (s.includes('petisco') || s.includes('salgado') || s.includes('batata') || s.includes('porcao') || s.includes('porção')) return '🍟';
      if (s.includes('bebida') || s.includes('refrigerante') || s.includes('refrig') || s.includes('coca')) return '🥤';
      if (s.includes('suco')) return '🍹';
      if (s.includes('a-la-carte') || s.includes('prato') || s.includes('alacarte')) return '🍽️';
      if (s.includes('doce') || s.includes('sobremesa')) return '🍰';
      if (s.includes('combo')) return '🎁';
      if (s.includes('promo')) return '🔥';
      if (s.includes('popular') || s.includes('mais-pedido')) return '⭐';
      if (s.includes('adicional') || s.includes('adicionais')) return '➕';
      return '🍽️';
    },
    getCategoryIcon(slug) {
      const s = (slug || '').toLowerCase();
      if (s.includes('sanduiche') || s.includes('hamburguer') || s.includes('artesanal') || s.includes('classico') || s.includes('lanche') || s.includes('burger')) return 'bi-fork-knife';
      if (s.includes('pastel') || s.includes('pasteis')) return 'bi-pie-chart-fill';
      if (s.includes('pizza')) return 'bi-pie-chart-fill';
      if (s.includes('tapioca') || s.includes('cuscuz')) return 'bi-circle-half';
      if (s.includes('petisco') || s.includes('salgado') || s.includes('batata')) return 'bi-egg-fried';
      if (s.includes('bebida') || s.includes('refrigerante') || s.includes('suco')) return 'bi-cup-straw';
      if (s.includes('a-la-carte') || s.includes('prato')) return 'bi-fork-knife';
      if (s.includes('doce') || s.includes('sobremesa')) return 'bi-cake2';
      if (s.includes('combo')) return 'bi-gift-fill';
      if (s.includes('promo')) return 'bi-fire';
      if (s.includes('popular') || s.includes('mais-pedido')) return 'bi-star-fill';
      if (s.includes('adicional')) return 'bi-plus-circle-fill';
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

/* Dimensões ampliadas da miniatura do produto (Cards Maiores conforme Desenho) */
.product-thumb {
  width: 115px;
  height: 115px;
  min-width: 115px;
}
@media (min-width: 576px) {
  .product-thumb {
    width: 135px;
    height: 135px;
    min-width: 135px;
  }
}

/* Cartões de Produto com Resposta Imediata no 1º Toque Mobile */
.aky-card {
  border: 1px solid #e5e7eb !important;
  background-color: #ffffff;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
  user-select: none;
}
@media (hover: hover) and (pointer: fine) {
  .hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.35rem 0.95rem rgba(0, 0, 0, 0.08) !important;
  }
  .aky-card:hover {
    border-color: #cbd5e1 !important;
  }
}
.aky-card:active {
  transform: scale(0.985);
}

/* Animação Suave e Fluida na Troca de Categoria */
.cat-switch-enter-active {
  transition: opacity 0.22s cubic-bezier(0.25, 1, 0.5, 1), transform 0.22s cubic-bezier(0.25, 1, 0.5, 1);
}
.cat-switch-leave-active {
  transition: opacity 0.12s cubic-bezier(0.5, 0, 0.75, 0), transform 0.12s cubic-bezier(0.5, 0, 0.75, 0);
}
.cat-switch-enter-from {
  opacity: 0;
  transform: translateY(12px) scale(0.995);
}
.cat-switch-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.995);
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

/* Barra Fixa de Categorias Tipo Livro (Sticky) */
.category-book-nav-wrapper {
  position: sticky;
  top: 58px;
  z-index: 1015;
  background: #ffffff;
  padding-top: 6px;
  padding-bottom: 6px;
  margin-left: -0.5rem;
  margin-right: -0.5rem;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}
.nav-book-btn {
  width: 44px;
  height: 44px;
  min-width: 44px;
  transition: all 0.2s ease;
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}
.nav-book-btn:hover {
  transform: scale(1.05);
}
.nav-book-btn:active {
  transform: scale(0.95);
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

/* Modal de Categorias 100% Sólido e Centralizado no Celular e PC */
.modal-categories-backdrop {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.72) !important;
  z-index: 1055;
}
.modal-categories-wrapper {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  z-index: 1060;
  padding: 14px;
  pointer-events: none;
}
.modal-categories-dialog {
  width: 100%;
  max-width: 440px;
  margin: 0 auto !important;
  pointer-events: auto;
}
.modal-categories-dialog .modal-content {
  background-color: #ffffff !important;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.35) !important;
  opacity: 1 !important;
}
.hover-bg-gray:hover {
  background-color: #f8fafc !important;
}

/* Espaçamento elegante entre os cards de produto */
.products-list {
  row-gap: 0.85rem !important;
}

</style>


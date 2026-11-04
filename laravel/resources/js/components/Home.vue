<template>
  <div class="home-container pb-5">
    
    <!-- Banner Principal Interativo: Carrossel Informativo do Cliente -->
    <div
      class="card border-0 shadow-sm rounded-4 text-white overflow-hidden mb-4 position-relative client-carousel-card"
      style="background: linear-gradient(135deg, #d62828 0%, #8f0d0d 100%); min-height: 250px;"
      @mouseenter="stopCarousel"
      @mouseleave="startCarousel"
      @touchstart="stopCarousel"
      @touchend="startCarousel"
    >
      <div class="card-body p-4 p-md-5 d-flex align-items-center justify-content-between flex-wrap gap-3 position-relative">
        
        <!-- Conteúdo do Slide Ativo -->
        <div style="max-width: 560px;" class="carousel-slide-content">
          <span :class="['badge fw-bold px-3 py-1 rounded-pill mb-2 shadow-sm d-inline-flex align-items-center gap-1', currentSlide.badgeClass]">
            <i :class="currentSlide.badgeIcon"></i>
            <span>{{ currentSlide.badgeText }}</span>
          </span>

          <h2 class="display-6 fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">
            {{ currentSlide.title }}
          </h2>

          <p class="text-white-50 mb-3 fs-6 lh-base">
            {{ currentSlide.description }}
          </p>

          <div class="d-flex flex-wrap gap-2 align-items-center">
            <button
              class="btn rounded-pill px-4 py-2 fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-2 transition"
              :class="currentSlide.btnClass || 'btn-warning'"
              @click="handleSlideAction(currentSlide.action)"
            >
              <i :class="currentSlide.btnIcon"></i>
              <span>{{ currentSlide.btnText }}</span>
            </button>

            <!-- Contador de Dicas do Sistema -->
            <span class="small text-white-50 px-2 fw-semibold">
              Dica {{ currentSlideIndex + 1 }} de {{ slides.length }}
            </span>
          </div>
        </div>

        <!-- Logo / Emblema da AKY Lanches com Animação -->
        <div class="d-none d-md-block text-center p-3 bg-white bg-opacity-10 rounded-circle shadow-lg flex-shrink-0">
          <img src="/images/aky_lanches_icon.svg" alt="AKY Lanches" style="height: 125px; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.35));">
        </div>

      </div>

      <!-- Setas Laterais de Navegação Discretas -->
      <button
        type="button"
        class="carousel-nav-btn prev-btn"
        @click="prevSlide"
        title="Dica anterior"
      >
        <i class="bi bi-chevron-left"></i>
      </button>

      <button
        type="button"
        class="carousel-nav-btn next-btn"
        @click="nextSlide"
        title="Próxima dica"
      >
        <i class="bi bi-chevron-right"></i>
      </button>

      <!-- Indicadores / Dots Clicáveis no Rodapé do Banner -->
      <div class="carousel-dots-container">
        <button
          v-for="(s, sIdx) in slides"
          :key="'dot-' + sIdx"
          type="button"
          :class="['carousel-dot-btn', currentSlideIndex === sIdx ? 'active' : '']"
          @click="goToSlide(sIdx)"
          :title="s.title"
        ></button>
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
            class="card aky-card h-100 bg-white border-start border-4 border-dark shadow-sm rounded-4 cursor-pointer transition"
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
                  <i class="bi bi-image fs-3 d-block text-dark"></i>
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
                  <span class="fw-bold fs-6 text-dark">
                    R$ {{ formatPrice(product.effective_price) }}
                  </span>
                  <button
                    class="btn btn-sm btn-dark text-white rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
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

    <!-- 1. SEÇÃO DE PROMOÇÕES IMPERDÍVEIS (VERMELHO) -->
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
            class="card aky-card h-100 bg-white border-start border-4 border-danger shadow-sm rounded-4 cursor-pointer transition"
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
                <div v-else class="text-center text-danger">
                  <i class="bi bi-tag-fill fs-2 opacity-75 d-block text-danger"></i>
                  <span style="font-size: 0.65rem;" class="fw-semibold text-danger">Promoção</span>
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
                    <span class="fw-bold fs-6 text-danger">
                      R$ {{ formatPrice(item.promotional_price) }}
                    </span>
                  </div>

                  <button
                    class="btn btn-sm btn-danger text-white rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
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

    <!-- 2. SEÇÃO DE COMBOS DO DIA (VERDE) -->
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
            class="card aky-card h-100 bg-white border-start border-4 border-success shadow-sm rounded-4 cursor-pointer transition"
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
                <div v-else class="text-center text-success">
                  <i class="bi bi-gift-fill fs-2 opacity-75 d-block text-success"></i>
                  <span style="font-size: 0.65rem;" class="fw-semibold text-success">Combo</span>
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
                    <span class="fw-bold fs-6 text-success">
                      R$ {{ formatPrice(item.effective_price) }}
                    </span>
                  </div>

                  <button
                    class="btn btn-sm btn-success text-white rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
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

    <!-- 3. SEÇÃO DE MAIS PEDIDOS (AMARELO) -->
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
          class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1 fw-semibold shadow-sm transition d-flex align-items-center gap-1 flex-shrink-0"
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
            class="card aky-card h-100 bg-white border-start border-4 border-warning shadow-sm rounded-4 cursor-pointer transition"
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
                <div v-else class="text-center text-warning">
                  <i class="bi bi-trophy-fill fs-2 opacity-75 d-block text-warning"></i>
                  <span style="font-size: 0.65rem;" class="fw-semibold text-warning">Favorito</span>
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
                      <span class="fw-bold fs-6 text-warning-emphasis">
                        R$ {{ formatPrice(item.promotional_price) }}
                      </span>
                    </div>
                    <div v-else>
                      <span class="fw-bold fs-6 text-warning-emphasis">
                        R$ {{ formatPrice(item.effective_price) }}
                      </span>
                    </div>
                  </div>

                  <button
                    class="btn btn-sm btn-warning text-dark fw-bold rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
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

    <!-- 4. SEÇÃO DE ITENS DE MONTAR - "MONTE DO SEU JEITO" (AZUL) -->
    <div v-if="!searchTerm.trim() && builderItems.length > 0" class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
            <i class="bi bi-sliders text-primary me-2 fs-4"></i> Monte do Seu Jeito
          </h5>
          <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-1 fw-semibold d-none d-sm-inline">
            <i class="bi bi-stars me-1"></i> 100% Personalizado
          </span>
        </div>

        <!-- Botão Ver Tudo dos Itens de Montar (Abre tela dedicada) -->
        <button
          class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold shadow-sm transition d-flex align-items-center gap-1 flex-shrink-0"
          @click="$emit('open-section', {
            title: 'Monte do Seu Jeito',
            subtitle: 'Personalize seus pastéis, pizzas e lanches com seus ingredientes favoritos',
            icon: 'bi-sliders',
            themeColor: 'primary',
            badgeText: '100% Personalizado',
            items: builderItems,
            backTo: 'home'
          })"
        >
          <span>Ver tudo</span>
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>

      <div class="row g-3">
        <div v-for="(item, idx) in displayedBuilderItems" :key="'builder-' + item.id" class="col-12 col-md-6 col-lg-4">
          <div
            class="card aky-card h-100 bg-white border-start border-4 border-primary shadow-sm rounded-4 cursor-pointer transition"
            @click="$emit('select-product', item, 'home')"
          >
            <div class="d-flex p-3 gap-3">
              <!-- Elemento de Foto do Item de Montar -->
              <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative" style="width: 90px; height: 90px;">
                <img
                  v-if="item.photo_path"
                  :src="item.photo_path"
                  :alt="item.name"
                  class="w-100 h-100 object-fit-cover"
                >
                <div v-else class="text-center text-primary">
                  <i class="bi bi-sliders fs-2 opacity-75 d-block text-primary"></i>
                  <span style="font-size: 0.65rem;" class="fw-semibold text-primary">Montar</span>
                </div>
              </div>

              <!-- Informações e Ação do Item de Montar -->
              <div class="flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-start">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem; max-width: 170px;">{{ item.name }}</h6>
                  </div>
                  <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 170px;">
                    {{ item.description || 'Escolha seus ingredientes favoritos e monte do seu jeito.' }}
                  </p>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                  <div>
                    <span class="text-muted small d-block" style="font-size: 0.70rem;">A partir de</span>
                    <span class="fw-bold fs-6 text-primary">
                      R$ {{ formatPrice(item.effective_price || item.price) }}
                    </span>
                  </div>

                  <button
                    class="btn btn-sm btn-primary text-white rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                    style="width: 36px; height: 36px;"
                    :disabled="!item.is_available"
                    title="Personalizar do seu jeito"
                    @click.stop="handleCardAdd(item)"
                  >
                    <i class="bi bi-sliders fs-6"></i>
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
      currentSlideIndex: 0,
      carouselTimer: null,
      slides: [
        {
          badgeText: 'Tradição e Sabor',
          badgeIcon: 'bi bi-star-fill text-dark',
          badgeClass: 'bg-warning text-dark',
          title: 'Bateu aquela fome? Peça sem Cadastro!',
          description: 'Seja muito bem-vindo à AKY Lanches! Escolha seus pastéis sequinhos, salgados, pizzas e sucos naturais favoritos e envie seu pedido rapidinho, sem complicações.',
          btnText: 'Ver Cardápio Completo',
          btnIcon: 'bi bi-book-half',
          btnClass: 'btn-warning text-dark',
          action: 'menu',
        },
        {
          badgeText: 'Personalização Total',
          badgeIcon: 'bi bi-layers-fill text-dark',
          badgeClass: 'bg-warning text-dark',
          title: 'Monte seu Pastel ou Pizza do Seu Jeito!',
          description: 'Aqui você tem liberdade para escolher! No "Monte Seu Pastel" você define os recheios salgados e doces, e nas Pizzas pode dividir meio a meio e adicionar bordas recheadas.',
          btnText: 'Explorar Opções',
          btnIcon: 'bi bi-sliders',
          btnClass: 'btn-light text-dark',
          action: 'menu',
        },
        {
          badgeText: 'Praticidade & Comodidade',
          badgeIcon: 'bi bi-geo-alt-fill text-white',
          badgeClass: 'bg-info bg-opacity-75 text-white',
          title: 'Entrega em Casa ou Retirada no Balcão!',
          description: 'Prefere receber quentinho em seu endereço em Teixeira - PB ou buscar diretamente no nosso balcão? No carrinho você escolhe Entrega ou Retirada no Local, com mapa e rota GPS.',
          btnText: 'Conhecer o Cardápio',
          btnIcon: 'bi bi-shop',
          btnClass: 'btn-warning text-dark',
          action: 'menu',
        },
        {
          badgeText: 'Finalização Direta',
          badgeIcon: 'bi bi-whatsapp text-white',
          badgeClass: 'bg-success text-white',
          title: 'Pedido Fácil e Pagamento Flexível!',
          description: 'Ao montar seu pedido no carrinho, escolha sua forma preferida de pagamento (PIX, Cartão ou Dinheiro com cálculo de troco) e envie a comanda pronta no WhatsApp com 1 toque.',
          btnText: 'Fazer Meu Pedido',
          btnIcon: 'bi bi-cart-check-fill',
          btnClass: 'btn-light text-dark',
          action: 'menu',
        },
        {
          badgeText: 'Transparência ao Vivo',
          badgeIcon: 'bi bi-clock-history text-white',
          badgeClass: 'bg-primary text-white',
          title: 'Acompanhe seu Pedido em Tempo Real!',
          description: 'Ao concluir sua compra, você recebe um código exclusivo de acompanhamento. Use a aba "Rastrear" para saber exatamente quando seu lanche entrou na chapa e quando saiu para entrega!',
          btnText: 'Rastrear um Pedido',
          btnIcon: 'bi bi-search',
          btnClass: 'btn-warning text-dark',
          action: 'tracker',
        },
        {
          badgeText: 'Sua Opinião Importa',
          badgeIcon: 'bi bi-chat-heart-fill text-white',
          badgeClass: 'bg-danger text-white border border-light',
          title: 'Confirme a Entrega e Avalie com Estrelas!',
          description: 'Recebeu seu pedido? Na página de rastreio você confirma o recebimento e pode avaliar o lanche e o atendimento com estrelas e comentário, com opção de assinar com seu nome ou como anônimo.',
          btnText: 'Ir para o Rastreio',
          btnIcon: 'bi bi-star-fill text-warning',
          btnClass: 'btn-light text-dark',
          action: 'tracker',
        },
      ],
    };
  },
  mounted() {
    this.startCarousel();
  },
  beforeUnmount() {
    this.stopCarousel();
  },
  computed: {
    currentSlide() {
      return this.slides[this.currentSlideIndex] || this.slides[0];
    },
    displayedPromotions() {
      return (this.promotions || []).slice(0, 3);
    },
    displayedCombos() {
      return (this.combos || []).slice(0, 3);
    },
    displayedPopular() {
      return (this.popular || []).slice(0, 3);
    },
    builderItems() {
      const list = [];
      const seen = new Set();
      (this.categories || []).forEach(cat => {
        (cat.products || []).forEach(p => {
          if (this.isCustomizable(p) || (p.name && (p.name.toLowerCase().includes('monte') || p.name.toLowerCase().includes('pizza')))) {
            if (!seen.has(p.id)) {
              seen.add(p.id);
              list.push(p);
            }
          }
        });
      });
      return list;
    },
    displayedBuilderItems() {
      return (this.builderItems || []).slice(0, 3);
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
    startCarousel() {
      this.stopCarousel();
      this.carouselTimer = setInterval(() => {
        this.nextSlide();
      }, 6500);
    },
    stopCarousel() {
      if (this.carouselTimer) {
        clearInterval(this.carouselTimer);
        this.carouselTimer = null;
      }
    },
    nextSlide() {
      this.currentSlideIndex = (this.currentSlideIndex + 1) % this.slides.length;
    },
    prevSlide() {
      this.currentSlideIndex = (this.currentSlideIndex - 1 + this.slides.length) % this.slides.length;
    },
    goToSlide(index) {
      this.currentSlideIndex = index;
      this.startCarousel();
    },
    handleSlideAction(action) {
      if (action === 'tracker') {
        this.$emit('go-to-tracker');
      } else {
        this.$emit('go-to-menu');
      }
    },
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
      if (name.includes('monte seu pastel') || name.includes('monte seu')) return true;
      if (catSlug.includes('pizza') || name.startsWith('pizza') || (name.includes('pizza') && !catSlug.includes('pastel'))) return true;
      if (catSlug.includes('tapioca') || name.includes('tapioca')) return true;
      if (catSlug.includes('cuscuz') || name.includes('cuscuz')) return true;
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
.client-carousel-card {
  transition: transform 0.25s ease;
}

.carousel-slide-content {
  animation: fadeInSlide 0.4s ease-in-out;
}

@keyframes fadeInSlide {
  from {
    opacity: 0.2;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.carousel-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 5;
}

.carousel-nav-btn:hover {
  background: rgba(0, 0, 0, 0.5);
  transform: translateY(-50%) scale(1.1);
  color: #ffd166;
}

.prev-btn {
  left: 10px;
}

.next-btn {
  right: 10px;
}

.carousel-dots-container {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
  z-index: 5;
}

.carousel-dot-btn {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.35);
  cursor: pointer;
  transition: all 0.25s ease;
  padding: 0;
}

.carousel-dot-btn.active {
  background: #ffc107;
  width: 26px;
  border-radius: 10px;
}

.text-warning-emphasis {
  color: #b45309 !important;
}
.transition {
  transition: all 0.2s ease-in-out;
}
</style>


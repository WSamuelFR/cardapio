<template>
  <div class="admin-products pb-5">
    
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <div>
        <h4 class="fw-bold mb-1 text-dark">
          <i class="bi bi-box-seam me-2 text-aky-primary"></i> Gestão do Cardápio
        </h4>
        <small class="text-muted">Gerencie produtos, categorias, preços e disponibilidades</small>
      </div>

      <!-- Botões de Ação Dinâmicos Conforme a Aba Ativa -->
      <div class="d-flex gap-2">
        <button
          v-if="currentTab === 'products'"
          class="btn btn-aky-primary rounded-pill btn-sm px-3 fw-semibold shadow-sm"
          @click="openProductModal()"
        >
          <i class="bi bi-plus-lg me-1"></i> Novo Produto
        </button>

        <button
          v-if="currentTab === 'categories'"
          class="btn btn-warning text-dark rounded-pill btn-sm px-3 fw-bold shadow-sm"
          @click="openCategoryModal()"
        >
          <i class="bi bi-folder-plus me-1"></i> Nova Categoria
        </button>

        <button
          v-if="currentTab === 'combos'"
          class="btn btn-success rounded-pill btn-sm px-3 fw-semibold shadow-sm"
          @click="openComboModal()"
        >
          <i class="bi bi-gift-fill me-1"></i> Novo Combo
        </button>

        <button
          v-if="currentTab === 'settings'"
          class="btn btn-dark rounded-pill btn-sm px-3 fw-bold shadow-sm"
          @click="saveSettings"
          :disabled="savingSettings"
        >
          <span v-if="savingSettings" class="spinner-border spinner-border-sm me-1"></span>
          <i v-else class="bi bi-floppy-fill me-1"></i> Salvar Valores
        </button>
      </div>
    </div>

    <!-- BARRA DE PESQUISA RÁPIDA NA GESTÃO -->
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-3 bg-white">
      <div class="input-group">
        <span class="input-group-text bg-transparent border-0 text-muted ps-3">
          <i class="bi bi-search"></i>
        </span>
        <input
          type="text"
          class="form-control border-0 shadow-none ps-2"
          v-model="adminSearchTerm"
          placeholder="Pesquisar por nome de produto, combo ou categoria..."
        >
        <button
          v-if="adminSearchTerm"
          class="btn btn-transparent border-0 text-muted pe-3"
          @click="adminSearchTerm = ''"
          title="Limpar busca"
        >
          <i class="bi bi-x-circle-fill"></i>
        </button>
      </div>
    </div>

    <!-- SELETOR DE ABAS MODERNO & RESPONSIVO (2x2 no Celular, 4 Lado a Lado no PC) -->
    <div class="bg-white p-2 rounded-4 shadow-sm border mb-4" style="max-width: 760px;">
      <div class="row g-2">
        <div class="col-6 col-md-3">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'products' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'products'"
          >
            <i class="bi bi-tag-fill me-1"></i> Produtos ({{ normalProducts.length }})
          </button>
        </div>
        <div class="col-6 col-md-3">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'categories' ? 'btn-warning text-dark shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'categories'"
          >
            <i class="bi bi-folder-fill me-1"></i> Categorias ({{ categories.length }})
          </button>
        </div>
        <div class="col-6 col-md-3">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'combos' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'combos'"
          >
            <i class="bi bi-gift-fill me-1"></i> Combos ({{ comboProducts.length }})
          </button>
        </div>
        <div class="col-6 col-md-3">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'settings' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'settings'"
          >
            <i class="bi bi-sliders me-1"></i> Montadores & Preços
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- ABA 1: PRODUTOS EM CARDS COMPACTOS -->
    <!-- ========================================== -->
    <div v-if="currentTab === 'products'">
      <div id="products-top-anchor"></div>
      
      <!-- Filtro Rápido de Categoria nos Produtos -->
      <div class="d-flex gap-2 overflow-auto pb-2 mb-3 no-scrollbar">
        <button
          :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold flex-shrink-0', filterCategory === null ? 'btn-dark' : 'btn-outline-secondary']"
          @click="filterCategory = null"
        >
          Todos ({{ normalProducts.length }})
        </button>
        <button
          v-for="cat in categories"
          :key="'filter-' + cat.id"
          :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold flex-shrink-0', filterCategory === cat.id ? 'btn-dark' : 'btn-outline-secondary']"
          @click="filterCategory = cat.id"
        >
          {{ cat.name }}
        </button>
      </div>

      <div v-if="filteredProducts.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhum produto encontrado</h6>
      </div>

      <!-- Grade de Cards Compactos de Produtos com Paginação -->
      <div v-else>
        <div class="row g-3">
          <div
            v-for="(product, idx) in paginatedProducts"
            :key="'admin-prod-' + product.id"
            class="col-12 col-md-6 col-lg-4"
          >
            <div :class="['card border-0 border-start border-4 shadow-sm rounded-4 h-100 bg-white overflow-hidden', idx % 3 === 0 ? 'border-danger' : (idx % 3 === 1 ? 'border-warning' : 'border-success'), !product.is_available ? 'opacity-75' : '']">
              <div class="card-body p-2 p-sm-3 d-flex flex-column justify-content-between">
                
                <!-- Topo: Foto + Detalhes Compactos -->
                <div class="d-flex gap-2 mb-2">
                  <!-- Foto Compacta -->
                  <div class="rounded-3 overflow-hidden bg-light border flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <img
                      v-if="product.photo_path"
                      :src="product.photo_path"
                      :alt="product.name"
                      class="w-100 h-100 object-fit-cover"
                    >
                    <i v-else class="bi bi-image text-muted fs-4"></i>
                  </div>

                  <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-start gap-1">
                      <h6 class="fw-bold text-dark mb-0 text-truncate" :title="product.name" style="font-size: 0.88rem; max-width: 155px;">
                        {{ product.name }}
                      </h6>
                      <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        <button class="btn btn-sm btn-light rounded-circle shadow-none p-1 text-muted" title="Editar" @click="openProductModal(product)">
                          <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-light text-danger rounded-circle shadow-none p-1" title="Apagar Produto" @click="requestDeleteProduct(product)">
                          <i class="bi bi-trash3-fill" style="font-size: 0.75rem;"></i>
                        </button>
                      </div>
                    </div>

                    <div class="d-flex align-items-center gap-1 my-1">
                      <span class="badge bg-light text-dark border small" style="font-size: 0.65rem;">
                        {{ product.category ? product.category.name : 'Geral' }}
                      </span>
                      <span v-if="product.is_promotion" class="badge bg-danger text-white rounded-pill" style="font-size: 0.65rem;">
                        Promo
                      </span>
                    </div>

                    <p class="text-muted small mb-0 text-truncate" style="font-size: 0.72rem; max-width: 200px;">
                      {{ product.description || 'Sem descrição cadastrada' }}
                    </p>
                  </div>
                </div>

                <!-- Preços Compactos com Edição Rápida ("1 Toque") -->
                <div class="bg-light rounded-3 p-2 d-flex justify-content-between align-items-center mb-2">
                  <div v-if="editingPriceProductId === product.id" class="d-flex align-items-center gap-1 w-100" @click.stop>
                    <span class="small fw-bold text-muted">R$</span>
                    <input
                      type="number"
                      step="0.01"
                      class="form-control form-control-sm py-0 px-1 fw-bold text-dark border-primary"
                      style="width: 82px;"
                      v-model="quickPriceValue"
                      @keyup.enter="saveQuickPrice(product)"
                      @keyup.esc="cancelQuickPrice"
                      ref="quickPriceInput"
                    >
                    <button
                      class="btn btn-sm btn-success p-1 rounded-circle d-flex align-items-center justify-content-center"
                      style="width: 26px; height: 26px;"
                      @click="saveQuickPrice(product)"
                      :disabled="savingQuickPrice"
                      title="Salvar preço"
                    >
                      <i class="bi bi-check-lg"></i>
                    </button>
                    <button
                      class="btn btn-sm btn-light p-1 rounded-circle d-flex align-items-center justify-content-center border"
                      style="width: 26px; height: 26px;"
                      @click="cancelQuickPrice"
                      title="Cancelar"
                    >
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                  <div v-else class="d-flex justify-content-between align-items-center w-100 cursor-pointer" @click="startQuickPrice(product)" title="Toque para editar o preço rapidamente">
                    <div>
                      <small class="text-muted d-flex align-items-center gap-1" style="font-size: 0.68rem;">
                        <span>Preço</span>
                        <i class="bi bi-pencil-fill text-primary" style="font-size: 0.62rem;"></i>
                      </small>
                      <span :class="['fw-bold', product.is_promotion ? 'text-decoration-line-through text-muted small' : 'text-dark fs-6']">
                        R$ {{ formatPrice(product.price) }}
                      </span>
                    </div>

                    <div v-if="product.is_promotion" class="text-end">
                      <small class="text-danger d-block fw-semibold" style="font-size: 0.68rem;">Promoção Ativa</small>
                      <div class="fw-bold text-danger fs-6">R$ {{ formatPrice(product.promotional_price) }}</div>
                    </div>
                  </div>
                </div>

                <!-- Switches Compactos em Linha -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                  <div class="form-check form-switch mb-0">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :id="'avail-' + product.id"
                      :checked="product.is_available"
                      @change="toggleAvailability(product)"
                    >
                    <label class="form-check-label small fw-semibold text-dark" style="font-size: 0.75rem;" :for="'avail-' + product.id">
                      {{ product.is_available ? 'Disponível' : 'Esgotado' }}
                    </label>
                  </div>

                  <div class="form-check form-switch mb-0">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :id="'promo-' + product.id"
                      :checked="product.is_promotion"
                      @change="togglePromotion(product)"
                    >
                    <label class="form-check-label small fw-semibold text-dark" style="font-size: 0.75rem;" :for="'promo-' + product.id">
                      Promoção
                    </label>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- BARRA DE PAGINAÇÃO MOBILE-FIRST OTIMIZADA -->
        <div v-if="filteredProducts.length > itemsPerPage" class="card border-0 shadow-sm rounded-4 p-3 bg-white mt-4">
          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            
            <!-- Informações de Contagem -->
            <div class="text-muted small fw-semibold text-center text-sm-start">
              Mostrando <span class="text-dark fw-bold">{{ paginationStart }}</span> a <span class="text-dark fw-bold">{{ paginationEnd }}</span> de <span class="text-dark fw-bold">{{ filteredProducts.length }}</span> produtos
            </div>

            <!-- Controles de Navegação (Confortáveis para toque no celular) -->
            <div class="d-flex align-items-center gap-2">
              <button
                class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1 shadow-none"
                :disabled="currentPage <= 1"
                @click="goToPage(currentPage - 1)"
              >
                <i class="bi bi-chevron-left"></i>
                <span>Anterior</span>
              </button>

              <div class="d-flex align-items-center gap-1 px-1">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold" style="font-size: 0.82rem;">
                  {{ currentPage }} de {{ totalPages }}
                </span>
              </div>

              <button
                class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1 shadow-none"
                :disabled="currentPage >= totalPages"
                @click="goToPage(currentPage + 1)"
              >
                <span>Próxima</span>
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>

          </div>
        </div>

      </div>

    </div>

    <!-- ========================================== -->
    <!-- ABA 2: CATEGORIAS EM CARDS COMPACTOS -->
    <!-- ========================================== -->
    <div v-else-if="currentTab === 'categories'">
      
      <div v-if="filteredCategories.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-folder-x fs-1 text-muted d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhuma categoria encontrada</h6>
      </div>

      <!-- Grade de Cards de Categorias -->
      <div v-else class="row g-3">
        <div
          v-for="cat in filteredCategories"
          :key="'admin-cat-' + cat.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div class="card border-0 border-start border-3 border-warning shadow-sm rounded-4 h-100 bg-white p-2 p-sm-3 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="rounded-circle bg-warning bg-opacity-25 p-2 text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                  <i class="bi bi-folder2-open fs-5"></i>
                </div>
                <div class="min-w-0">
                  <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.88rem;">{{ cat.name }}</h6>
                  <small class="text-muted font-monospace" style="font-size: 0.68rem;">slug: {{ cat.slug }}</small>
                </div>
              </div>

              <div class="d-flex align-items-center gap-1 flex-shrink-0">
                <button class="btn btn-sm btn-light rounded-circle shadow-none p-1 text-muted" title="Editar" @click="openCategoryModal(cat)">
                  <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                </button>
                <button class="btn btn-sm btn-light text-danger rounded-circle shadow-none p-1" title="Apagar Categoria" @click="requestDeleteCategory(cat)">
                  <i class="bi bi-trash3-fill" style="font-size: 0.75rem;"></i>
                </button>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 p-2 my-2">
              <span class="small text-dark fw-semibold" style="font-size: 0.75rem;">Produtos Vinculados:</span>
              <span class="badge bg-warning text-dark border border-warning rounded-pill px-2" style="font-size: 0.72rem;">{{ cat.products_count || (cat.products ? cat.products.length : 0) }}</span>
            </div>

            <!-- Switch de Ativação da Categoria -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <span class="small fw-semibold text-muted" style="font-size: 0.75rem;">Exibir no Cardápio:</span>
              <div class="form-check form-switch mb-0">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="'cat-act-' + cat.id"
                  :checked="cat.is_active"
                  @change="toggleCategoryActive(cat)"
                >
                <label class="form-check-label small fw-semibold" style="font-size: 0.75rem;" :class="cat.is_active ? 'text-success' : 'text-danger'" :for="'cat-act-' + cat.id">
                  {{ cat.is_active ? 'Ativa' : 'Oculta' }}
                </label>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>

    <!-- ========================================== -->
    <!-- ABA 3: COMBOS EM CARDS COMPACTOS -->
    <!-- ========================================== -->
    <div v-else-if="currentTab === 'combos'">
      <div v-if="filteredCombos.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-gift fs-1 text-muted d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhum combo encontrado</h6>
      </div>

      <div v-else class="row g-3">
        <div
          v-for="combo in filteredCombos"
          :key="'admin-combo-' + combo.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div :class="['card border-0 border-start border-3 border-success shadow-sm rounded-4 h-100 bg-white overflow-hidden', !combo.is_available ? 'opacity-75' : '']">
            <div class="card-body p-2 p-sm-3 d-flex flex-column justify-content-between">
              
              <!-- Topo do Card: Foto + Detalhes Compactos -->
              <div class="d-flex gap-2 mb-2">
                <!-- Foto do Combo -->
                <div class="rounded-3 overflow-hidden bg-light border flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                  <img
                    v-if="combo.photo_path"
                    :src="combo.photo_path"
                    :alt="combo.name"
                    class="w-100 h-100 object-fit-cover"
                  >
                  <i v-else class="bi bi-gift-fill text-success fs-4"></i>
                </div>

                <div class="flex-grow-1 min-w-0">
                  <div class="d-flex justify-content-between align-items-start gap-1">
                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.88rem; max-width: 155px;" :title="combo.name">{{ combo.name }}</h6>
                    <div class="d-flex gap-1 flex-shrink-0">
                      <button class="btn btn-sm btn-light rounded-circle shadow-none p-1 text-muted" title="Editar" @click="openComboModal(combo)">
                        <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                      </button>
                      <button class="btn btn-sm btn-light rounded-circle shadow-none p-1 text-danger" title="Apagar Combo" @click="requestDeleteProduct(combo)">
                        <i class="bi bi-trash3-fill" style="font-size: 0.75rem;"></i>
                      </button>
                    </div>
                  </div>

                  <span class="badge bg-success bg-opacity-10 text-success rounded-pill my-1" style="font-size: 0.65rem;">
                    <i class="bi bi-gift-fill me-1"></i> Combo Especial
                  </span>

                  <p class="text-muted small mb-0 text-truncate" style="font-size: 0.72rem; max-width: 200px;" :title="combo.description">
                    {{ combo.description || 'Itens selecionados com desconto especial.' }}
                  </p>
                </div>
              </div>

              <!-- Preços do Combo Compactos -->
              <div class="bg-light rounded-3 p-2 mb-2 d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-muted d-block" style="font-size: 0.68rem;">Original</small>
                  <span class="small text-muted text-decoration-line-through">R$ {{ formatPrice(combo.price) }}</span>
                </div>
                <div class="text-end">
                  <small class="text-success d-block fw-semibold" style="font-size: 0.68rem;">Preço Combo</small>
                  <span class="fw-bold text-success fs-6">R$ {{ formatPrice(combo.effective_price) }}</span>
                </div>
              </div>

              <!-- Switch de Disponibilidade -->
              <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <span class="small fw-semibold text-muted" style="font-size: 0.75rem;">Disponível para Pedidos:</span>
                <div class="form-check form-switch mb-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    :id="'combo-avail-' + combo.id"
                    :checked="combo.is_available"
                    @change="toggleAvailability(combo)"
                  >
                  <label class="form-check-label small fw-semibold" style="font-size: 0.75rem;" :class="combo.is_available ? 'text-success' : 'text-danger'" :for="'combo-avail-' + combo.id">
                    {{ combo.is_available ? 'Ativo' : 'Pausado' }}
                  </label>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- ABA 4: MONTADORES & PREÇOS DO SISTEMA (DINÂMICOS MYSQL)  -->
    <!-- ======================================================== -->
    <div v-else-if="currentTab === 'settings'" class="settings-tab">
      
      <!-- Barra Superior de Salvar Rápido (Responsiva) -->
      <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 bg-white mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
          <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-sliders text-primary"></i>
              <span>Configurações dos Montadores & Preços Globais</span>
            </h5>
          </div>

          <button
            class="btn btn-dark rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 flex-shrink-0"
            @click="saveSettings"
            :disabled="savingSettings"
          >
            <span v-if="savingSettings" class="spinner-border spinner-border-sm"></span>
            <i v-else class="bi bi-floppy-fill"></i>
            <span>Salvar Todas as Alterações</span>
          </button>
        </div>
      </div>

      <!-- 1. BLOCO DE CONFIGURAÇÃO DAS PIZZAS -->
      <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
          <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-pie-chart-fill text-danger fs-4"></i>
              <span>1. Tamanhos e Preços das Pizzas</span>
            </h5>
            <small class="text-muted">Defina o nome dos tamanhos, quantidade de fatias e preço oficial de cada um</small>
          </div>

          <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto">
            <button
              class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-sm flex-fill flex-sm-grow-0"
              @click="openSyncPizzaModal"
              :disabled="syncingPizzas"
              title="Ajusta o preço base de todas as pizzas cadastradas para o valor da Pequena"
            >
              <span v-if="syncingPizzas" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-arrow-repeat me-1"></i>
              Sincronizar Preço Base
            </button>
            <button
              class="btn btn-sm btn-danger rounded-pill px-3 py-1.5 fw-bold shadow-sm flex-fill flex-sm-grow-0"
              @click="addPizzaSize"
            >
              <i class="bi bi-plus-lg me-1"></i> Novo Tamanho
            </button>
          </div>
        </div>

        <!-- Grade de Tamanhos Editáveis -->
        <div class="row g-3 mb-4">
          <div
            v-for="(sz, idx) in menuSettings.pizza_sizes"
            :key="'size-edit-' + idx"
            class="col-12 col-md-4"
          >
            <div class="card p-3 rounded-4 border bg-light h-100 shadow-sm">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-danger rounded-pill px-2.5 py-1 small fw-semibold">Tamanho #{{ idx + 1 }}</span>
                <button
                  v-if="menuSettings.pizza_sizes && menuSettings.pizza_sizes.length > 1"
                  class="btn btn-link text-danger p-0"
                  @click="removePizzaSize(idx)"
                  title="Remover tamanho"
                >
                  <i class="bi bi-trash3-fill"></i>
                </button>
              </div>

              <div class="mb-2">
                <label class="form-label small fw-bold text-dark mb-1">Nome do Tamanho</label>
                <input type="text" class="form-control form-control-sm bg-white" v-model="sz.name" placeholder="Ex: Média (Mais Pedida)">
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label small fw-bold text-dark mb-1">Fatias</label>
                  <input type="text" class="form-control form-control-sm bg-white" v-model="sz.slices" placeholder="Ex: 8 Fatias">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold text-dark mb-1">Preço (R$)</label>
                  <input type="number" step="0.01" class="form-control form-control-sm bg-white fw-bold text-danger" v-model="sz.price" placeholder="0,00">
                </div>
              </div>

              <div class="form-check form-switch pt-1 border-top mt-auto">
                <input class="form-check-input" type="checkbox" :id="'sz-avail-' + idx" v-model="sz.is_available">
                <label class="form-check-label small fw-semibold" :for="'sz-avail-' + idx">
                  {{ sz.is_available ? 'Disponível no Cardápio' : 'Pausado Temporariamente' }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- BORDAS RECHEADAS -->
        <div class="pt-3 border-top">
          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <div>
              <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                <i class="bi bi-record-circle text-warning fs-5"></i>
                <span>Bordas Recheadas (Opcional)</span>
              </h6>
              <small class="text-muted">Gerencie as opções de borda e valor adicional cobrado</small>
            </div>
            <button class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold w-100 w-sm-auto" @click="addPizzaBorder">
              <i class="bi bi-plus-lg me-1"></i> Nova Opção de Borda
            </button>
          </div>

          <div class="row g-2">
            <div
              v-for="(borda, bIdx) in menuSettings.pizza_borders"
              :key="'borda-edit-' + bIdx"
              class="col-12 col-sm-6 col-md-3"
            >
              <div class="card p-2.5 rounded-3 border bg-light h-100">
                <div class="d-flex justify-content-between align-items-start mb-1">
                  <span class="small fw-bold text-dark">Opção #{{ bIdx + 1 }}</span>
                  <button
                    v-if="borda.id !== 'nenhuma'"
                    class="btn btn-link text-danger p-0"
                    @click="removePizzaBorder(bIdx)"
                    title="Remover borda"
                  >
                    <i class="bi bi-trash3 fs-6"></i>
                  </button>
                </div>
                <div class="mb-1">
                  <input type="text" class="form-control form-control-sm bg-white mb-1" v-model="borda.name" placeholder="Nome da borda">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted fw-bold">R$</span>
                    <input type="number" step="0.01" class="form-control bg-white text-success fw-bold" v-model="borda.price" placeholder="0,00">
                  </div>
                </div>
                <div class="form-check form-switch mt-1">
                  <input class="form-check-input" type="checkbox" :id="'borda-avail-' + bIdx" v-model="borda.is_available">
                  <label class="form-check-label small" style="font-size: 0.72rem;" :for="'borda-avail-' + bIdx">
                    {{ borda.is_available ? 'Ativa' : 'Pausada' }}
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- 2. BLOCO DE CONFIGURAÇÃO DO MONTE SEU PASTEL -->
      <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
        <div class="mb-3">
          <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-layers-fill text-warning fs-4"></i>
            <span>2. Regras e Ingredientes do "Monte Seu Pastel"</span>
          </h5>
          <small class="text-muted">Ajuste o valor base, cobrança por ingrediente extra e ative ou pause opções</small>
        </div>

        <!-- Parâmetros Gerais e Regra de Cobrança do Pastel -->
        <div class="row g-3 p-3 bg-light rounded-4 mb-4 border">
          <!-- 1. Preço Base -->
          <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label small fw-bold text-dark mb-1">Preço Base do Pastel</label>
            <div class="input-group">
              <span class="input-group-text bg-white fw-bold text-warning">R$</span>
              <input type="number" step="0.01" class="form-control bg-white fw-bold text-dark" v-model="menuSettings.monte_pastel.base_price" placeholder="15,00">
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Valor inicial do pastel no cardápio</small>
          </div>

          <!-- 2. A partir de qual ingrediente cobrar -->
          <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label small fw-bold text-dark mb-1">Cobrar adicional a partir de qual?</label>
            <div class="input-group">
              <span class="input-group-text bg-white text-muted small fw-semibold">A partir do</span>
              <input
                type="number"
                class="form-control bg-white fw-bold text-primary text-center"
                v-model.number="menuSettings.monte_pastel.charge_from_ingredient"
                @input="syncPastelFreeIngredients"
                min="2"
                max="15"
                placeholder="5"
              >
              <span class="input-group-text bg-white text-muted small fw-semibold">º ingrediente</span>
            </div>
            <small class="text-primary fw-semibold d-block mt-1" style="font-size: 0.72rem;">
              <i class="bi bi-check2-circle me-1"></i> Até {{ Math.max(1, (menuSettings.monte_pastel.charge_from_ingredient || 5) - 1) }} ingredientes são grátis/inclusos
            </small>
          </div>

          <!-- 3. Valor por Ingrediente Adicional -->
          <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label small fw-bold text-dark mb-1">Valor por Ingrediente Extra</label>
            <div class="input-group">
              <span class="input-group-text bg-white fw-bold text-success">R$</span>
              <input type="number" step="0.01" class="form-control bg-white fw-bold text-success" v-model="menuSettings.monte_pastel.extra_ingredient_price" placeholder="3,00">
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Cobrado a cada ingrediente além do limite</small>
          </div>

          <!-- 4. Limite Máximo de Ingredientes -->
          <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label small fw-bold text-dark mb-1">Limite Máximo de Ingredientes</label>
            <div class="input-group">
              <input type="number" class="form-control bg-white fw-bold text-dark text-center" v-model.number="menuSettings.monte_pastel.max_total_ingredients" min="4" max="25" placeholder="10">
              <span class="input-group-text bg-white text-muted small">ingredientes</span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Máximo total por pastel (inclusos + extras)</small>
          </div>
        </div>

        <!-- GESTÃO DOS INGREDIENTES SALGADOS (RESPONSIVO E SEM SOBREPOSIÇÃO) -->
        <div class="mb-4">
          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
              <span>🧀 Ingredientes Salgados ({{ menuSettings.monte_pastel.ingredients_salgados ? menuSettings.monte_pastel.ingredients_salgados.length : 0 }})</span>
            </h6>

            <!-- Adicionar Novo Salgado -->
            <div class="input-group input-group-sm w-100 w-sm-auto" style="max-width: 360px;">
              <input
                type="text"
                class="form-control"
                placeholder="Novo salgado..."
                v-model="newSalgadoName"
                @keyup.enter="addSalgadoIngredient"
              >
              <button class="btn btn-warning text-dark fw-bold px-3 text-nowrap" @click="addSalgadoIngredient">
                <i class="bi bi-plus-lg"></i> Adicionar
              </button>
            </div>
          </div>

          <!-- Grade Responsiva de Salgados (1 Coluna no celular para nunca bater botões no texto) -->
          <div class="row g-2">
            <div
              v-for="(ing, idx) in menuSettings.monte_pastel.ingredients_salgados"
              :key="'salgado-' + idx"
              class="col-12 col-sm-6 col-md-4 col-lg-3"
            >
              <div :class="['card p-2.5 rounded-3 border transition d-flex flex-row justify-content-between align-items-center shadow-none', ing.is_available ? 'bg-white' : 'bg-light opacity-50']">
                <div class="min-w-0 me-2">
                  <span class="small fw-semibold text-truncate d-block text-dark" style="font-size: 0.85rem;" :title="ing.name || ing">{{ ing.name || ing }}</span>
                  <small :class="ing.is_available ? 'text-success fw-bold' : 'text-danger fw-bold'" style="font-size: 0.7rem;">
                    {{ ing.is_available ? '● Disponível' : '○ Pausado' }}
                  </small>
                </div>

                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                  <!-- Toggle Disponível -->
                  <button
                    type="button"
                    class="btn btn-sm p-1 rounded-circle border-0"
                    :class="ing.is_available ? 'text-success' : 'text-muted'"
                    @click="ing.is_available = !ing.is_available"
                    :title="ing.is_available ? 'Pausar ingrediente' : 'Ativar ingrediente'"
                  >
                    <i :class="ing.is_available ? 'bi-toggle-on fs-4' : 'bi-toggle-off fs-4'"></i>
                  </button>
                  <!-- Excluir -->
                  <button
                    type="button"
                    class="btn btn-sm p-1 text-danger border-0"
                    @click="removeSalgadoIngredient(idx)"
                    title="Remover ingrediente"
                  >
                    <i class="bi bi-trash3 fs-6"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- GESTÃO DOS INGREDIENTES DOCES (RESPONSIVO E SEM SOBREPOSIÇÃO) -->
        <div>
          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
              <span>🍫 Ingredientes Doces ({{ menuSettings.monte_pastel.ingredients_doces ? menuSettings.monte_pastel.ingredients_doces.length : 0 }})</span>
            </h6>

            <!-- Adicionar Novo Doce -->
            <div class="input-group input-group-sm w-100 w-sm-auto" style="max-width: 360px;">
              <input
                type="text"
                class="form-control"
                placeholder="Novo doce..."
                v-model="newDoceName"
                @keyup.enter="addDoceIngredient"
              >
              <button class="btn btn-warning text-dark fw-bold px-3 text-nowrap" @click="addDoceIngredient">
                <i class="bi bi-plus-lg"></i> Adicionar
              </button>
            </div>
          </div>

          <!-- Grade Responsiva de Doces (1 Coluna no celular para nunca bater botões no texto) -->
          <div class="row g-2">
            <div
              v-for="(ing, idx) in menuSettings.monte_pastel.ingredients_doces"
              :key="'doce-' + idx"
              class="col-12 col-sm-6 col-md-4 col-lg-3"
            >
              <div :class="['card p-2.5 rounded-3 border transition d-flex flex-row justify-content-between align-items-center shadow-none', ing.is_available ? 'bg-white' : 'bg-light opacity-50']">
                <div class="min-w-0 me-2">
                  <span class="small fw-semibold text-truncate d-block text-dark" style="font-size: 0.85rem;" :title="ing.name || ing">{{ ing.name || ing }}</span>
                  <small :class="ing.is_available ? 'text-success fw-bold' : 'text-danger fw-bold'" style="font-size: 0.7rem;">
                    {{ ing.is_available ? '● Disponível' : '○ Pausado' }}
                  </small>
                </div>

                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                  <button
                    type="button"
                    class="btn btn-sm p-1 rounded-circle border-0"
                    :class="ing.is_available ? 'text-success' : 'text-muted'"
                    @click="ing.is_available = !ing.is_available"
                    :title="ing.is_available ? 'Pausar ingrediente' : 'Ativar ingrediente'"
                  >
                    <i :class="ing.is_available ? 'bi-toggle-on fs-4' : 'bi-toggle-off fs-4'"></i>
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm p-1 text-danger border-0"
                    @click="removeDoceIngredient(idx)"
                    title="Remover ingrediente"
                  >
                    <i class="bi bi-trash3 fs-6"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- BOTÃO INFERIOR DE SALVAR (RESPONSIVO) -->
      <div class="d-flex justify-content-center justify-content-md-end mb-5">
        <button
          class="btn btn-dark rounded-pill px-5 py-3 fw-bold shadow d-inline-flex align-items-center justify-content-center gap-2 w-100 w-md-auto"
          @click="saveSettings"
          :disabled="savingSettings"
        >
          <span v-if="savingSettings" class="spinner-border spinner-border-sm"></span>
          <i v-else class="bi bi-floppy-fill fs-5"></i>
          <span>Salvar Todas as Configurações</span>
        </button>
      </div>

    </div>

    <!-- MODAL DE CRIAR / EDITAR PRODUTO (MODERNO & MOBILE-FIRST) -->
    <div v-if="showProductModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <!-- Cabeçalho Moderno com Gradiente e Logo AKY Lanches -->
          <div class="modal-header text-white border-0 py-3 px-3 px-sm-4" style="background: linear-gradient(135deg, #e50914 0%, #b20710 100%);">
            <div class="d-flex align-items-center gap-3">
              <img src="/images/aky_lanches_icon.svg" alt="Aky Lanches" class="rounded-circle bg-white p-1 shadow-sm flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
              <div>
                <h5 class="modal-title fw-bold text-white mb-0">
                  {{ editingProduct.id ? 'Editar Produto' : 'Cadastrar Novo Produto' }}
                </h5>
                <small class="text-white-50" style="font-size: 0.78rem;">Configure as informações do cardápio</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showProductModal = false"></button>
          </div>

          <form @submit.prevent="saveProduct" class="d-flex flex-column overflow-hidden">
            <div class="modal-body p-3 p-sm-4" style="max-height: 72vh; overflow-y: auto;">
              
              <!-- Categoria -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Categoria do Produto *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-grid-fill"></i>
                  </span>
                  <select class="form-select border-start-0 ps-1" v-model="editingProduct.category_id" required>
                    <option value="" disabled>Selecione uma categoria...</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
              </div>

              <!-- Nome do Produto -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Nome do Produto *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-tag-fill"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-1" v-model="editingProduct.name" placeholder="Ex: Pastel Especial de Carne com Queijo" required>
                </div>
              </div>

              <!-- Descrição -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Descrição / Ingredientes</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted align-items-start pt-2">
                    <i class="bi bi-card-text"></i>
                  </span>
                  <textarea class="form-control border-start-0 ps-1" rows="2" v-model="editingProduct.description" placeholder="Ex: Carne moída temperada, queijo derretido, azeitonas e orégano..."></textarea>
                </div>
              </div>

              <!-- Preços em Duas Colunas Responsivas -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-bold text-dark mb-1">Preço Normal *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted fw-bold">R$</span>
                    <input type="number" step="0.01" class="form-control border-start-0 ps-1" v-model="editingProduct.price" placeholder="0,00" required>
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold text-dark mb-1">Preço Promoção</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 fw-bold" :class="editingProduct.is_promotion ? 'text-danger' : 'text-muted'">R$</span>
                    <input type="number" step="0.01" class="form-control border-start-0 ps-1" v-model="editingProduct.promotional_price" placeholder="0,00" :disabled="!editingProduct.is_promotion">
                  </div>
                </div>
              </div>

              <!-- Card Switch: Produto Promocional -->
              <div class="card border rounded-3 p-2 mb-2 transition" :class="editingProduct.is_promotion ? 'border-danger bg-danger bg-opacity-10' : 'bg-light border-light-subtle'">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-fire fs-5" :class="editingProduct.is_promotion ? 'text-danger' : 'text-muted'"></i>
                    <div>
                      <div class="fw-bold small" :class="editingProduct.is_promotion ? 'text-danger' : 'text-dark'">Ativar como Promoção</div>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">Aparece em destaque na seção de ofertas da Home</small>
                    </div>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input fs-5" type="checkbox" id="promoCheck" v-model="editingProduct.is_promotion">
                  </div>
                </div>
              </div>

              <!-- Card Switch: Disponibilidade -->
              <div class="card border rounded-3 p-2 mb-3 transition" :class="editingProduct.is_available ? 'border-success bg-success bg-opacity-10' : 'bg-light border-light-subtle'">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5" :class="editingProduct.is_available ? 'text-success' : 'text-muted'"></i>
                    <div>
                      <div class="fw-bold small" :class="editingProduct.is_available ? 'text-success' : 'text-dark'">Disponível para Pedidos</div>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">Permite que os clientes peçam este produto online</small>
                    </div>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input fs-5" type="checkbox" id="availCheck" v-model="editingProduct.is_available">
                  </div>
                </div>
              </div>

              <!-- Foto do Produto com Preview Instantâneo -->
              <div class="mb-2">
                <label class="form-label small fw-bold text-dark mb-1">Foto do Produto (JPG ou PNG)</label>
                <div class="d-flex align-items-center gap-3 p-2 border rounded-3 bg-light">
                  <div class="rounded-3 border overflow-hidden bg-white flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 72px; height: 72px;">
                    <img v-if="filePreview" :src="filePreview" class="w-100 h-100 object-fit-cover" alt="Preview da Foto">
                    <div v-else class="text-center text-muted">
                      <i class="bi bi-camera fs-3 d-block text-secondary"></i>
                      <span style="font-size: 0.6rem;">Sem foto</span>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <input type="file" class="form-control form-control-sm" accept="image/*" @change="handleFileChange">
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                      {{ filePreview ? 'Foto carregada! Clique acima para alterar.' : 'Escolha uma foto quadrada para melhor visualização.' }}
                    </small>
                  </div>
                </div>
              </div>

            </div>

            <!-- Rodapé Flexível para Celular e PC -->
            <div class="modal-footer border-top bg-light p-3 d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
              <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-muted fw-semibold w-100 w-sm-auto" @click="showProductModal = false">
                Cancelar
              </button>
              <button type="submit" class="btn btn-aky-primary rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check-lg me-1"></i>
                Salvar Produto
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL DE CRIAR / EDITAR CATEGORIA (MODERNO & MOBILE-FIRST) -->
    <div v-if="showCategoryModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <!-- Cabeçalho Moderno Amarelo com Logo AKY Lanches -->
          <div class="modal-header border-0 py-3 px-3 px-sm-4" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529;">
            <div class="d-flex align-items-center gap-3">
              <img src="/images/aky_lanches_icon.svg" alt="Aky Lanches" class="rounded-circle bg-white p-1 shadow-sm flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
              <div>
                <h6 class="modal-title fw-bold text-dark mb-0">
                  {{ editingCategory.id ? 'Editar Categoria' : 'Nova Categoria' }}
                </h6>
                <small class="text-dark text-opacity-75" style="font-size: 0.75rem;">Organize a divisão do cardápio</small>
              </div>
            </div>
            <button type="button" class="btn-close shadow-none" @click="showCategoryModal = false"></button>
          </div>

          <form @submit.prevent="saveCategory">
            <div class="modal-body p-3 p-sm-4">
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Nome da Categoria *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-tag-fill"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-1" v-model="editingCategory.name" placeholder="Ex: Sobremesas, Bebidas Geladas..." required>
                </div>
                <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                  <i class="bi bi-info-circle me-1"></i> As categorias agrupam os produtos para facilitar a escolha dos clientes.
                </small>
              </div>
            </div>

            <!-- Rodapé Responsivo Amarelo -->
            <div class="modal-footer border-top bg-light p-3 d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
              <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-muted fw-semibold w-100 w-sm-auto" @click="showCategoryModal = false">
                Cancelar
              </button>
              <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto">
                <i class="bi bi-check-lg me-1"></i> Salvar Categoria
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL DE CRIAR / EDITAR COMBO (MODERNO & MOBILE-FIRST) -->
    <div v-if="showComboModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <!-- Cabeçalho Moderno Verde com Gradiente e Logo AKY Lanches -->
          <div class="modal-header text-white border-0 py-3 px-3 px-sm-4" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
            <div class="d-flex align-items-center gap-3">
              <img src="/images/aky_lanches_icon.svg" alt="Aky Lanches" class="rounded-circle bg-white p-1 shadow-sm flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
              <div>
                <h5 class="modal-title fw-bold text-white mb-0">
                  {{ editingCombo.id ? 'Editar Combo' : 'Novo Combo do Dia' }}
                </h5>
                <small class="text-white-50" style="font-size: 0.78rem;">Pesquise produtos e monte o combo com foto e preço especial</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showComboModal = false"></button>
          </div>

          <form @submit.prevent="saveCombo" class="d-flex flex-column overflow-hidden">
            <div class="modal-body p-3 p-sm-4" style="max-height: 72vh; overflow-y: auto;">
              
              <!-- Nome do Combo -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Nome do Combo *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-gift-fill"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-1" v-model="editingCombo.name" placeholder="Ex: Combo Casal: 2 Pastéis + 2 Sucos" required>
                </div>
              </div>

              <!-- BUSCAR PRODUTOS DO CARDÁPIO PARA O COMBO -->
              <div class="card border rounded-4 p-3 mb-3 bg-light bg-opacity-50">
                <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                  <span><i class="bi bi-search me-1 text-success"></i> Pesquisar Produtos do Cardápio</span>
                  <small class="text-muted fw-normal">Clique para incluir no combo</small>
                </label>
                
                <div class="input-group input-group-sm mb-2 shadow-sm rounded-pill overflow-hidden bg-white border">
                  <span class="input-group-text bg-white border-0 ps-3 text-muted">
                    <i class="bi bi-search"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none ps-1"
                    v-model="comboProductSearchTerm"
                    placeholder="Digite o nome do pastel, bebida ou lanche..."
                  >
                  <button
                    v-if="comboProductSearchTerm"
                    class="btn btn-white border-0 text-muted pe-3"
                    @click="comboProductSearchTerm = ''"
                    type="button"
                  >
                    <i class="bi bi-x-circle-fill"></i>
                  </button>
                </div>

                <!-- Lista de Produtos Encontrados (Resultados com foto e preço) -->
                <div class="d-flex flex-column gap-1 overflow-auto" style="max-height: 170px;">
                  <div
                    v-for="prod in searchedComboProducts"
                    :key="'search-prod-' + prod.id"
                    class="p-2 rounded-3 bg-white border d-flex align-items-center justify-content-between hover-card cursor-pointer shadow-none"
                    @click="addComboItem(prod)"
                  >
                    <div class="d-flex align-items-center gap-2 min-w-0">
                      <div class="rounded-2 overflow-hidden bg-light border flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <img v-if="prod.photo_path" :src="prod.photo_path" class="w-100 h-100 object-fit-cover">
                        <i v-else class="bi bi-box-seam text-muted"></i>
                      </div>
                      <div class="min-w-0">
                        <div class="fw-bold small text-dark text-truncate">{{ prod.name }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">R$ {{ formatPrice(prod.price) }}</small>
                      </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                      <i class="bi bi-plus-lg me-1"></i> Incluir
                    </button>
                  </div>
                </div>
              </div>

              <!-- LISTA DE PRODUTOS SELECIONADOS NO COMBO -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label small fw-bold text-dark mb-0">
                    <i class="bi bi-check2-circle text-success me-1"></i> Itens Incluídos no Combo
                    <span class="badge bg-success rounded-pill ms-1">{{ comboSelectedItems.length }}</span>
                  </label>
                  <small class="text-muted" style="font-size: 0.72rem;">Ajuste quantidades e fotos</small>
                </div>

                <div v-if="comboSelectedItems.length === 0" class="text-center p-3 border rounded-3 bg-light text-muted small">
                  <i class="bi bi-inbox d-block fs-4 text-muted mb-1"></i>
                  Nenhum produto incluído ainda. Pesquise e adicione produtos acima.
                </div>

                <div v-else class="d-flex flex-column gap-2">
                  <div
                    v-for="(item, idx) in comboSelectedItems"
                    :key="'combo-item-' + item.id"
                    class="card border rounded-3 p-2 bg-white"
                  >
                    <div class="d-flex align-items-center justify-content-between gap-2">
                      <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="rounded-2 overflow-hidden bg-light border flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                          <img v-if="item.photo_path" :src="item.photo_path" class="w-100 h-100 object-fit-cover">
                          <i v-else class="bi bi-box-seam text-muted"></i>
                        </div>
                        <div class="min-w-0">
                          <div class="fw-bold small text-dark text-truncate">{{ item.name }}</div>
                          <small class="text-muted" style="font-size: 0.72rem;">R$ {{ formatPrice(item.price) }} un.</small>
                        </div>
                      </div>

                      <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <!-- Botão Usar Foto no Combo -->
                        <button
                          v-if="item.photo_path"
                          type="button"
                          class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-muted"
                          style="font-size: 0.68rem;"
                          @click="useProductPhotoForCombo(item.photo_path)"
                          title="Definir foto deste produto como a foto do combo"
                        >
                          <i class="bi bi-camera me-1 text-primary"></i> Usar Foto
                        </button>

                        <!-- Controles de Quantidade -->
                        <div class="btn-group btn-group-sm border rounded-pill overflow-hidden">
                          <button type="button" class="btn btn-light px-2 py-0" @click="changeComboItemQuantity(idx, -1)">-</button>
                          <span class="px-2 py-0 small fw-bold bg-white user-select-none">{{ item.quantity }}</span>
                          <button type="button" class="btn btn-light px-2 py-0" @click="changeComboItemQuantity(idx, 1)">+</button>
                        </div>

                        <!-- Remover -->
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeComboItem(idx)" title="Remover do combo">
                          <i class="bi bi-x-circle fs-5"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Itens Inclusos / Descrição -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Descrição Final dos Itens *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted align-items-start pt-2">
                    <i class="bi bi-card-checklist"></i>
                  </span>
                  <textarea class="form-control border-start-0 ps-1" rows="2" v-model="editingCombo.description" placeholder="Ex: 2 Pastéis à sua escolha + 2 Sucos Naturais 500ml gelados..." required></textarea>
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Atualizada automaticamente ao selecionar produtos, ou edite livremente.</small>
              </div>

              <!-- Preços do Combo -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-bold text-dark mb-1">Preço Original (R$) *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted fw-bold">R$</span>
                    <input type="number" step="0.01" class="form-control border-start-0 ps-1" v-model="editingCombo.price" placeholder="0,00" required>
                  </div>
                  <small class="text-muted d-block" style="font-size: 0.68rem;">Soma dos itens selecionados</small>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold text-dark mb-1">Preço do Combo (R$) *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 fw-bold text-success">R$</span>
                    <input type="number" step="0.01" class="form-control border-start-0 ps-1" v-model="editingCombo.promotional_price" placeholder="0,00" required>
                  </div>
                  <small class="text-success d-block" style="font-size: 0.68rem;">Preço especial com desconto</small>
                </div>
              </div>

              <!-- Card Switch: Disponibilidade -->
              <div class="card border rounded-3 p-2 mb-3 transition" :class="editingCombo.is_available ? 'border-success bg-success bg-opacity-10' : 'bg-light border-light-subtle'">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5" :class="editingCombo.is_available ? 'text-success' : 'text-muted'"></i>
                    <div>
                      <div class="fw-bold small" :class="editingCombo.is_available ? 'text-success' : 'text-dark'">Disponível para Pedidos</div>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">Permite que clientes vejam e peçam este combo no feed</small>
                    </div>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input fs-5" type="checkbox" id="comboAvailCheck" v-model="editingCombo.is_available">
                  </div>
                </div>
              </div>

              <!-- Foto do Combo com Preview Instantâneo -->
              <div class="mb-2">
                <label class="form-label small fw-bold text-dark mb-1">Foto do Combo (JPG ou PNG)</label>
                <div class="d-flex align-items-center gap-3 p-2 border rounded-3 bg-light">
                  <div class="rounded-3 border overflow-hidden bg-white flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 72px; height: 72px;">
                    <img v-if="comboFilePreview" :src="comboFilePreview" class="w-100 h-100 object-fit-cover" alt="Preview da Foto">
                    <div v-else class="text-center text-muted">
                      <i class="bi bi-gift fs-3 d-block text-success"></i>
                      <span style="font-size: 0.6rem;">Sem foto</span>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <input type="file" class="form-control form-control-sm" accept="image/*" @change="handleComboFileChange">
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                      {{ comboFilePreview ? 'Foto carregada! Clique acima para trocar ou use a foto de um produto.' : 'Envie uma foto ou use a foto de um produto selecionado.' }}
                    </small>
                  </div>
                </div>
              </div>

            </div>

            <!-- Rodapé Flexível para Celular e PC -->
            <div class="modal-footer border-top bg-light p-3 d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
              <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-muted fw-semibold w-100 w-sm-auto" @click="showComboModal = false">
                Cancelar
              </button>
              <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check-lg me-1"></i>
                Salvar Combo
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO PARA APAGAR CATEGORIA -->
    <div v-if="showDeleteCategoryModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
              <i class="bi bi-trash3-fill fs-2 text-danger"></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">Apagar Categoria</h5>
          </div>

          <div class="modal-body px-4 py-2 text-center">
            <p class="text-muted small mb-3">
              Tem certeza que deseja apagar a categoria <strong class="text-dark">{{ categoryToDelete?.name }}</strong>?
            </p>

            <!-- AVISO ESPECÍFICO SE CATEGORIA POSSUI PRODUTOS VINCULADOS -->
            <div v-if="categoryToDelete && (categoryToDelete.products_count > 0 || (categoryToDelete.products && categoryToDelete.products.length > 0))" class="alert alert-warning border-0 rounded-3 p-3 text-start small mb-0 d-flex gap-2 align-items-start">
              <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0 mt-0.5"></i>
              <div>
                <strong>Atenção:</strong> essa categoria esta sendo usada desvicule para poder apagar, deseja mesmo apagar?
              </div>
            </div>
          </div>

          <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="showDeleteCategoryModal = false" :disabled="isDeletingCategory">
              Cancelar
            </button>
            <button type="button" class="btn btn-danger rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="confirmDeleteCategory" :disabled="isDeletingCategory">
              <span v-if="isDeletingCategory" class="spinner-border spinner-border-sm me-1"></span>
              Sim, Apagar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO PARA APAGAR PRODUTO OU COMBO -->
    <div v-if="showDeleteProductModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
              <i class="bi bi-trash3-fill fs-2 text-danger"></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">Apagar Item</h5>
          </div>

          <div class="modal-body px-4 py-2 text-center">
            <p class="text-muted small mb-3">
              Tem certeza que deseja apagar o item <strong class="text-dark">{{ productToDelete?.name }}</strong>?
            </p>

            <!-- Loading da checagem de pedidos -->
            <div v-if="checkingPendingOrders" class="py-2 text-muted small">
              <span class="spinner-border spinner-border-sm me-1 text-primary"></span> Verificando pedidos vinculados...
            </div>

            <!-- AVISO ESPECÍFICO SE PRODUTO TEM PEDIDOS AGUARDANDO CONFIRMAÇÃO -->
            <div v-else-if="productHasPendingOrders" class="alert alert-danger border-0 rounded-3 p-3 text-start small mb-0 d-flex gap-2 align-items-start">
              <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0 mt-0.5"></i>
              <div>
                <strong>Atenção:</strong> esse produto tem pedidos aguardando confirmação deseja mesmo apagar?
              </div>
            </div>
          </div>

          <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="showDeleteProductModal = false" :disabled="isDeletingProduct">
              Cancelar
            </button>
            <button type="button" class="btn btn-danger rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="confirmDeleteProduct" :disabled="isDeletingProduct || checkingPendingOrders">
              <span v-if="isDeletingProduct" class="spinner-border spinner-border-sm me-1"></span>
              Sim, Apagar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO PARA SINCRONIZAR PREÇO DAS PIZZAS -->
    <div v-if="showSyncPizzaModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
              <i class="bi bi-arrow-repeat fs-1 text-danger"></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">Sincronizar Pizzas</h5>
          </div>

          <div class="modal-body px-4 py-2 text-center">
            <p class="text-muted small mb-2">
              Deseja atualizar o preço base de <strong>todas as pizzas tradicionais e doces</strong> para o valor do tamanho <strong>Pequena (R$ {{ getSmallPizzaPrice() }})</strong>?
            </p>
            <div class="alert alert-info border-0 rounded-3 p-2 small mb-0 text-start" style="font-size: 0.75rem;">
              <i class="bi bi-info-circle-fill me-1"></i> Isso ajustará o preço inicial de exibição das pizzas no cardápio de uma só vez.
            </div>
          </div>

          <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="showSyncPizzaModal = false" :disabled="syncingPizzas">
              Cancelar
            </button>
            <button type="button" class="btn btn-danger rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="confirmSyncAllPizzaPrices" :disabled="syncingPizzas">
              <span v-if="syncingPizzas" class="spinner-border spinner-border-sm me-1"></span>
              Sim, Sincronizar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL MODERNO DE FEEDBACK / AVISO (Substitui alert() nativo) -->
    <div v-if="showFeedbackModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1080;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div
              :class="[
                'rounded-circle p-3 mb-2 d-flex align-items-center justify-content-center',
                feedbackModalData.type === 'success' ? 'bg-success bg-opacity-10 text-success' : (feedbackModalData.type === 'warning' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-danger bg-opacity-10 text-danger')
              ]"
              style="width: 58px; height: 58px;"
            >
              <i
                :class="[
                  'fs-1',
                  feedbackModalData.type === 'success' ? 'bi bi-check-circle-fill text-success' : (feedbackModalData.type === 'warning' ? 'bi bi-exclamation-triangle-fill text-warning' : 'bi bi-x-circle-fill text-danger')
                ]"
              ></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">{{ feedbackModalData.title }}</h5>
          </div>

          <div class="modal-body px-4 py-2 text-center">
            <p class="text-muted small mb-0 lh-base">
              {{ feedbackModalData.message }}
            </p>
          </div>

          <div class="modal-footer border-0 px-4 pb-4 pt-3">
            <button
              type="button"
              :class="[
                'btn rounded-pill w-100 fw-bold btn-sm py-2 shadow-sm',
                feedbackModalData.type === 'success' ? 'btn-success text-white' : (feedbackModalData.type === 'warning' ? 'btn-warning text-dark' : 'btn-danger text-white')
              ]"
              @click="showFeedbackModal = false"
            >
              OK, Entendido
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      currentTab: 'products', // 'products', 'categories', 'combos' ou 'settings'
      filterCategory: null,
      adminSearchTerm: '',
      itemsPerPage: 12,
      currentPage: 1,
      editingPriceProductId: null,
      quickPriceValue: '',
      savingQuickPrice: false,

      // Configurações Dinâmicas dos Montadores
      menuSettings: {
        pizza_sizes: [],
        pizza_borders: [],
        monte_pastel: {
          base_price: 15.00,
          charge_from_ingredient: 5,
          max_free_ingredients: 4,
          extra_ingredient_price: 3.00,
          max_total_ingredients: 10,
          allow_extra_ingredients: true,
          ingredients_salgados: [],
          ingredients_doces: [],
        },
      },
      savingSettings: false,
      syncingPizzas: false,
      newSalgadoName: '',
      newDoceName: '',

      // Modais Modernos
      showSyncPizzaModal: false,
      showFeedbackModal: false,
      feedbackModalData: {
        title: '',
        message: '',
        type: 'success',
      },

      products: [],
      categories: [],
      showProductModal: false,
      showCategoryModal: false,
      showComboModal: false,
      editingProduct: {},
      editingCategory: { name: '' },
      editingCombo: {},
      selectedFile: null,
      filePreview: null,
      selectedComboFile: null,
      comboFilePreview: null,
      saving: false,
      comboProductSearchTerm: '',
      comboSelectedItems: [],
      showDeleteCategoryModal: false,
      categoryToDelete: null,
      isDeletingCategory: false,
      showDeleteProductModal: false,
      productToDelete: null,
      productHasPendingOrders: false,
      checkingPendingOrders: false,
      isDeletingProduct: false,
    };
  },
  computed: {
    normalProducts() {
      return this.products.filter(p => !p.is_combo);
    },
    comboProducts() {
      return this.products.filter(p => p.is_combo);
    },
    searchedComboProducts() {
      if (!this.comboProductSearchTerm.trim()) {
        return this.normalProducts.slice(0, 8);
      }
      const term = this.comboProductSearchTerm.toLowerCase();
      return this.normalProducts.filter(p => p.name.toLowerCase().includes(term));
    },
    filteredProducts() {
      let list = this.normalProducts;
      if (this.filterCategory !== null) {
        list = list.filter(p => p.category_id === this.filterCategory);
      }
      if (this.adminSearchTerm.trim()) {
        const term = this.adminSearchTerm.toLowerCase();
        list = list.filter(p => (p.name && p.name.toLowerCase().includes(term)) || (p.description && p.description.toLowerCase().includes(term)));
      }
      return list;
    },
    filteredCategories() {
      if (!this.adminSearchTerm.trim()) return this.categories;
      const term = this.adminSearchTerm.toLowerCase();
      return this.categories.filter(c => c.name && c.name.toLowerCase().includes(term));
    },
    filteredCombos() {
      let list = this.comboProducts;
      if (this.adminSearchTerm.trim()) {
        const term = this.adminSearchTerm.toLowerCase();
        list = list.filter(c => (c.name && c.name.toLowerCase().includes(term)) || (c.description && c.description.toLowerCase().includes(term)));
      }
      return list;
    },
    totalPages() {
      return Math.ceil(this.filteredProducts.length / this.itemsPerPage) || 1;
    },
    paginatedProducts() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredProducts.slice(start, start + this.itemsPerPage);
    },
    paginationStart() {
      if (this.filteredProducts.length === 0) return 0;
      return (this.currentPage - 1) * this.itemsPerPage + 1;
    },
    paginationEnd() {
      const end = this.currentPage * this.itemsPerPage;
      return end > this.filteredProducts.length ? this.filteredProducts.length : end;
    },
  },
  watch: {
    filterCategory() {
      this.currentPage = 1;
    },
    adminSearchTerm() {
      this.currentPage = 1;
    },
    currentTab() {
      this.currentPage = 1;
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.$nextTick(() => {
          const el = document.getElementById('products-top-anchor');
          if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
          }
        });
      }
    },
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    async fetchData() {
      try {
        const [prodRes, catRes, settingsRes] = await Promise.all([
          axios.get('/api/admin/products'),
          axios.get('/api/admin/categories'),
          axios.get('/api/admin/menu-settings'),
        ]);
        this.products = prodRes.data;
        this.categories = catRes.data;
        if (settingsRes && settingsRes.data) {
          const mp = settingsRes.data.monte_pastel || {};
          const chargeFrom = mp.charge_from_ingredient || ((Number(mp.max_free_ingredients) || 4) + 1);
          const maxFree = mp.max_free_ingredients !== undefined ? mp.max_free_ingredients : (chargeFrom - 1);

          this.menuSettings = {
            pizza_sizes: settingsRes.data.pizza_sizes || [],
            pizza_borders: settingsRes.data.pizza_borders || [],
            monte_pastel: {
              base_price: mp.base_price !== undefined ? mp.base_price : 15.00,
              charge_from_ingredient: chargeFrom,
              max_free_ingredients: maxFree,
              extra_ingredient_price: mp.extra_ingredient_price !== undefined ? mp.extra_ingredient_price : 3.00,
              max_total_ingredients: mp.max_total_ingredients || 10,
              allow_extra_ingredients: mp.allow_extra_ingredients !== false,
              ingredients_salgados: mp.ingredients_salgados || [],
              ingredients_doces: mp.ingredients_doces || [],
            },
          };
        }
      } catch (err) {
        console.error(err);
      }
    },
    openFeedback(title, message, type = 'success') {
      this.feedbackModalData = { title, message, type };
      this.showFeedbackModal = true;
    },
    syncPastelFreeIngredients() {
      const chargeFrom = Number(this.menuSettings.monte_pastel.charge_from_ingredient) || 5;
      this.menuSettings.monte_pastel.max_free_ingredients = Math.max(1, chargeFrom - 1);
    },
    getSmallPizzaPrice() {
      const small = (this.menuSettings.pizza_sizes || []).find(s => s.id === 'pequena');
      return small ? this.formatPrice(small.price) : '36,00';
    },
    openSyncPizzaModal() {
      this.showSyncPizzaModal = true;
    },
    async confirmSyncAllPizzaPrices() {
      this.syncingPizzas = true;
      try {
        const res = await axios.post('/api/admin/sync-pizza-prices');
        await this.fetchData();
        this.$emit('menu-updated');
        this.showSyncPizzaModal = false;
        this.openFeedback('Preços Sincronizados!', res.data.message || 'Preço das pizzas atualizado com sucesso.', 'success');
      } catch (err) {
        this.showSyncPizzaModal = false;
        this.openFeedback('Erro ao Sincronizar', err.response?.data?.message || 'Erro ao sincronizar preços.', 'danger');
      } finally {
        this.syncingPizzas = false;
      }
    },
    startQuickPrice(product) {
      this.editingPriceProductId = product.id;
      this.quickPriceValue = product.price;
      this.$nextTick(() => {
        if (this.$refs.quickPriceInput && this.$refs.quickPriceInput[0]) {
          this.$refs.quickPriceInput[0].focus();
          this.$refs.quickPriceInput[0].select();
        }
      });
    },
    cancelQuickPrice() {
      this.editingPriceProductId = null;
      this.quickPriceValue = '';
    },
    async saveQuickPrice(product) {
      const newPrice = parseFloat(this.quickPriceValue);
      if (isNaN(newPrice) || newPrice <= 0) {
        this.openFeedback('Valor Inválido', 'Informe um valor válido maior que zero.', 'warning');
        return;
      }
      this.savingQuickPrice = true;
      try {
        await axios.patch(`/api/admin/products/${product.id}/quick-price`, { price: newPrice });
        product.price = newPrice;
        this.editingPriceProductId = null;
        this.$emit('menu-updated');
        this.openFeedback('Preço Atualizado!', `O preço de "${product.name}" agora é R$ ${this.formatPrice(newPrice)}.`, 'success');
      } catch (err) {
        this.openFeedback('Erro ao Atualizar', err.response?.data?.message || 'Erro ao atualizar preço.', 'danger');
      } finally {
        this.savingQuickPrice = false;
      }
    },
    async saveSettings() {
      this.savingSettings = true;
      try {
        this.syncPastelFreeIngredients();
        const payload = { ...this.menuSettings };
        delete payload.store_whatsapp;
        const res = await axios.post('/api/admin/menu-settings', payload);
        if (res.data && res.data.settings) {
          const { store_whatsapp, ...otherSettings } = res.data.settings;
          this.menuSettings = otherSettings;
        }
        await this.fetchData();
        this.$emit('menu-updated');
        this.openFeedback('Tudo Pronto!', 'Configurações e valores salvos com sucesso no sistema!', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Salvar', err.response?.data?.message || 'Erro ao salvar configurações.', 'danger');
      } finally {
        this.savingSettings = false;
      }
    },
    addPizzaSize() {
      const newId = 'tam_' + Date.now();
      if (!this.menuSettings.pizza_sizes) this.menuSettings.pizza_sizes = [];
      this.menuSettings.pizza_sizes.push({
        id: newId,
        name: 'Novo Tamanho',
        slices: '8 Fatias',
        price: 40.00,
        is_available: true,
      });
    },
    removePizzaSize(idx) {
      this.menuSettings.pizza_sizes.splice(idx, 1);
    },
    addPizzaBorder() {
      const newId = 'borda_' + Date.now();
      if (!this.menuSettings.pizza_borders) this.menuSettings.pizza_borders = [];
      this.menuSettings.pizza_borders.push({
        id: newId,
        name: 'Nova Borda',
        price: 8.00,
        is_available: true,
      });
    },
    removePizzaBorder(idx) {
      this.menuSettings.pizza_borders.splice(idx, 1);
    },
    addSalgadoIngredient() {
      if (!this.newSalgadoName.trim()) return;
      if (!this.menuSettings.monte_pastel.ingredients_salgados) {
        this.menuSettings.monte_pastel.ingredients_salgados = [];
      }
      this.menuSettings.monte_pastel.ingredients_salgados.push({
        name: this.newSalgadoName.trim(),
        is_available: true,
      });
      this.newSalgadoName = '';
    },
    removeSalgadoIngredient(idx) {
      this.menuSettings.monte_pastel.ingredients_salgados.splice(idx, 1);
    },
    addDoceIngredient() {
      if (!this.newDoceName.trim()) return;
      if (!this.menuSettings.monte_pastel.ingredients_doces) {
        this.menuSettings.monte_pastel.ingredients_doces = [];
      }
      this.menuSettings.monte_pastel.ingredients_doces.push({
        name: this.newDoceName.trim(),
        is_available: true,
      });
      this.newDoceName = '';
    },
    removeDoceIngredient(idx) {
      this.menuSettings.monte_pastel.ingredients_doces.splice(idx, 1);
    },
    openProductModal(product = null) {
      this.selectedFile = null;
      if (product) {
        this.editingProduct = { ...product };
        this.filePreview = product.photo_path || null;
      } else {
        this.editingProduct = {
          category_id: this.categories[0] ? this.categories[0].id : '',
          name: '',
          description: '',
          price: '',
          is_promotion: false,
          promotional_price: '',
          is_available: true,
        };
        this.filePreview = null;
      }
      this.showProductModal = true;
    },
    handleFileChange(e) {
      const file = e.target.files[0] || null;
      this.selectedFile = file;
      if (file) {
        this.filePreview = URL.createObjectURL(file);
      } else {
        this.filePreview = this.editingProduct.photo_path || null;
      }
    },
    async saveProduct() {
      this.saving = true;
      try {
        const formData = new FormData();
        formData.append('category_id', this.editingProduct.category_id);
        formData.append('name', this.editingProduct.name);
        formData.append('description', this.editingProduct.description || '');
        formData.append('price', this.editingProduct.price);
        formData.append('is_promotion', this.editingProduct.is_promotion ? 1 : 0);
        if (this.editingProduct.is_promotion && this.editingProduct.promotional_price) {
          formData.append('promotional_price', this.editingProduct.promotional_price);
        }
        formData.append('is_available', this.editingProduct.is_available ? 1 : 0);

        if (this.selectedFile) {
          formData.append('photo', this.selectedFile);
        }

        const url = this.editingProduct.id
          ? `/api/admin/products/${this.editingProduct.id}`
          : '/api/admin/products';

        await axios.post(url, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        this.showProductModal = false;
        this.fetchData();
        this.openFeedback('Produto Salvo!', 'O produto foi salvo com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Salvar', 'Erro ao salvar produto. Verifique os dados preenchidos.', 'danger');
      } finally {
        this.saving = false;
      }
    },
    async toggleAvailability(product) {
      try {
        await axios.patch(`/api/admin/products/${product.id}/toggle-availability`);
        product.is_available = !product.is_available;
      } catch (err) {
        this.openFeedback('Erro', 'Não foi possível alterar a disponibilidade.', 'danger');
      }
    },
    async togglePromotion(product) {
      try {
        await axios.patch(`/api/admin/products/${product.id}/toggle-promotion`);
        product.is_promotion = !product.is_promotion;
      } catch (err) {
        this.openFeedback('Erro', 'Não foi possível alterar a promoção.', 'danger');
      }
    },
    openCategoryModal(cat = null) {
      if (cat) {
        this.editingCategory = { ...cat };
      } else {
        this.editingCategory = { name: '' };
      }
      this.showCategoryModal = true;
    },
    async saveCategory() {
      try {
        if (this.editingCategory.id) {
          await axios.put(`/api/admin/categories/${this.editingCategory.id}`, { name: this.editingCategory.name });
        } else {
          await axios.post('/api/admin/categories', { name: this.editingCategory.name });
        }
        this.showCategoryModal = false;
        this.fetchData();
        this.openFeedback('Categoria Salva!', 'A categoria foi salva com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Salvar', 'Não foi possível salvar a categoria.', 'danger');
      }
    },
    async toggleCategoryActive(cat) {
      try {
        await axios.patch(`/api/admin/categories/${cat.id}/toggle`);
        cat.is_active = !cat.is_active;
      } catch (err) {
        this.openFeedback('Erro', 'Não foi possível alterar o status da categoria.', 'danger');
      }
    },
    openComboModal(combo = null) {
      this.selectedComboFile = null;
      this.comboProductSearchTerm = '';
      this.comboSelectedItems = [];
      if (combo) {
        this.editingCombo = { ...combo };
        this.comboFilePreview = combo.photo_path || null;
      } else {
        this.editingCombo = {
          name: '',
          description: '',
          price: '',
          promotional_price: '',
          is_promotion: true,
          is_combo: true,
          is_available: true,
          photo_path: null,
        };
        this.comboFilePreview = null;
      }
      this.showComboModal = true;
    },
    addComboItem(product) {
      const existing = this.comboSelectedItems.find(item => item.id === product.id);
      if (existing) {
        existing.quantity++;
      } else {
        this.comboSelectedItems.push({
          id: product.id,
          name: product.name,
          price: Number(product.price || 0),
          photo_path: product.photo_path || null,
          quantity: 1,
        });
      }
      if (!this.comboFilePreview && !this.selectedComboFile && product.photo_path) {
        this.comboFilePreview = product.photo_path;
        this.editingCombo.photo_path = product.photo_path;
      }
      this.recalculateComboFromItems();
    },
    changeComboItemQuantity(index, delta) {
      const item = this.comboSelectedItems[index];
      if (!item) return;
      item.quantity += delta;
      if (item.quantity <= 0) {
        this.comboSelectedItems.splice(index, 1);
      }
      this.recalculateComboFromItems();
    },
    removeComboItem(index) {
      this.comboSelectedItems.splice(index, 1);
      this.recalculateComboFromItems();
    },
    useProductPhotoForCombo(photoPath) {
      if (!photoPath) return;
      this.selectedComboFile = null;
      this.comboFilePreview = photoPath;
      this.editingCombo.photo_path = photoPath;
    },
    recalculateComboFromItems() {
      if (this.comboSelectedItems.length === 0) return;
      const sum = this.comboSelectedItems.reduce((acc, item) => acc + (item.price * item.quantity), 0);
      this.editingCombo.price = Number(sum.toFixed(2));
      const desc = this.comboSelectedItems
        .map(item => `${item.quantity}x ${item.name}`)
        .join(' + ');
      this.editingCombo.description = desc;
    },
    handleComboFileChange(e) {
      const file = e.target.files[0] || null;
      this.selectedComboFile = file;
      if (file) {
        this.comboFilePreview = URL.createObjectURL(file);
      } else {
        this.comboFilePreview = this.editingCombo.photo_path || null;
      }
    },
    async saveCombo() {
      this.saving = true;
      try {
        const formData = new FormData();
        formData.append('name', this.editingCombo.name);
        formData.append('description', this.editingCombo.description || '');
        formData.append('price', this.editingCombo.price);
        formData.append('promotional_price', this.editingCombo.promotional_price || this.editingCombo.price);
        formData.append('is_promotion', 1);
        formData.append('is_combo', 1);
        formData.append('is_available', this.editingCombo.is_available ? 1 : 0);

        if (this.selectedComboFile) {
          formData.append('photo', this.selectedComboFile);
        } else if (this.editingCombo.photo_path) {
          formData.append('photo_path', this.editingCombo.photo_path);
        }

        const url = this.editingCombo.id
          ? `/api/admin/products/${this.editingCombo.id}`
          : '/api/admin/products';

        await axios.post(url, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        this.showComboModal = false;
        this.fetchData();
        this.openFeedback('Combo Salvo!', 'O combo foi salvo com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Salvar', 'Erro ao salvar combo. Verifique os dados preenchidos.', 'danger');
      } finally {
        this.saving = false;
      }
    },
    requestDeleteCategory(cat) {
      this.categoryToDelete = cat;
      this.showDeleteCategoryModal = true;
    },
    async confirmDeleteCategory() {
      if (!this.categoryToDelete) return;
      this.isDeletingCategory = true;
      try {
        await axios.delete(`/api/admin/categories/${this.categoryToDelete.id}`);
        this.showDeleteCategoryModal = false;
        this.categoryToDelete = null;
        this.fetchData();
        this.openFeedback('Categoria Excluída', 'A categoria foi removida com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Excluir', err.response?.data?.message || 'Erro ao excluir categoria.', 'danger');
      } finally {
        this.isDeletingCategory = false;
      }
    },
    async requestDeleteProduct(product) {
      this.productToDelete = product;
      this.productHasPendingOrders = false;
      this.checkingPendingOrders = true;
      this.showDeleteProductModal = true;
      try {
        const res = await axios.get(`/api/admin/products/${product.id}/pending-orders-check`);
        this.productHasPendingOrders = res.data.has_pending_orders;
      } catch (err) {
        console.error(err);
      } finally {
        this.checkingPendingOrders = false;
      }
    },
    async confirmDeleteProduct() {
      if (!this.productToDelete) return;
      this.isDeletingProduct = true;
      try {
        await axios.delete(`/api/admin/products/${this.productToDelete.id}`);
        this.showDeleteProductModal = false;
        this.productToDelete = null;
        this.fetchData();
        this.openFeedback('Item Excluído', 'O item foi removido com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Excluir', err.response?.data?.message || 'Erro ao excluir item.', 'danger');
      } finally {
        this.isDeletingProduct = false;
      }
    },
  },
};
</script>

<style scoped>
@media (min-width: 768px) {
  .w-md-auto {
    width: auto !important;
  }
}
@media (min-width: 576px) {
  .w-sm-auto {
    width: auto !important;
  }
}
</style>

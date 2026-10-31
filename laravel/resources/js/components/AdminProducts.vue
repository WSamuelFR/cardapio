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

    <!-- SELETOR DE ABAS MODERNO & ALINHADO (Segmented Control) -->
    <div class="bg-white p-1 rounded-4 shadow-sm border mb-4 d-flex gap-1 w-100" style="max-width: 560px;">
      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'products' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
        @click="currentTab = 'products'"
      >
        <i class="bi bi-tag-fill me-1"></i> Produtos ({{ normalProducts.length }})
      </button>

      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'categories' ? 'btn-warning text-dark shadow-sm' : 'btn-light text-dark']"
        @click="currentTab = 'categories'"
      >
        <i class="bi bi-folder-fill me-1"></i> Categorias ({{ categories.length }})
      </button>

      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'combos' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark']"
        @click="currentTab = 'combos'"
      >
        <i class="bi bi-gift-fill me-1"></i> Combos ({{ comboProducts.length }})
      </button>
    </div>

    <!-- ========================================== -->
    <!-- ABA 1: PRODUTOS EM CARDS COMPACTOS -->
    <!-- ========================================== -->
    <div v-if="currentTab === 'products'">
      
      <!-- Filtro Rápido de Categoria nos Produtos -->
      <div class="d-flex gap-2 overflow-auto pb-2 mb-3 no-scrollbar">
        <button
          :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold flex-shrink-0', filterCategory === null ? 'btn-dark' : 'btn-outline-secondary']"
          @click="filterCategory = null"
        >
          Todos
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

      <!-- Grade de Cards Compactos de Produtos -->
      <div v-else class="row g-3">
        <div
          v-for="product in filteredProducts"
          :key="'admin-prod-' + product.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div :class="['card border-0 border-start border-3 border-danger shadow-sm rounded-4 h-100 bg-white overflow-hidden', !product.is_available ? 'opacity-75' : '']">
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

              <!-- Preços Compactos -->
              <div class="bg-light rounded-3 p-2 d-flex justify-content-between align-items-center mb-2">
                <div>
                  <small class="text-muted d-block" style="font-size: 0.68rem;">Preço</small>
                  <span :class="['fw-bold', product.is_promotion ? 'text-decoration-line-through text-muted small' : 'text-dark fs-6']">
                    R$ {{ formatPrice(product.price) }}
                  </span>
                </div>

                <div v-if="product.is_promotion" class="text-end">
                  <small class="text-danger d-block fw-semibold" style="font-size: 0.68rem;">Promoção Ativa</small>
                  <div class="fw-bold text-danger fs-6">R$ {{ formatPrice(product.promotional_price) }}</div>
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

  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      currentTab: 'products', // 'products', 'categories' ou 'combos'
      filterCategory: null,
      adminSearchTerm: '',
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
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    async fetchData() {
      try {
        const [prodRes, catRes] = await Promise.all([
          axios.get('/api/admin/products'),
          axios.get('/api/admin/categories'),
        ]);
        this.products = prodRes.data;
        this.categories = catRes.data;
      } catch (err) {
        console.error(err);
      }
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
      } catch (err) {
        alert('Erro ao salvar produto. Verifique os dados.');
      } finally {
        this.saving = false;
      }
    },
    async toggleAvailability(product) {
      try {
        await axios.patch(`/api/admin/products/${product.id}/toggle-availability`);
        product.is_available = !product.is_available;
      } catch (err) {
        alert('Erro ao alterar disponibilidade.');
      }
    },
    async togglePromotion(product) {
      try {
        await axios.patch(`/api/admin/products/${product.id}/toggle-promotion`);
        product.is_promotion = !product.is_promotion;
      } catch (err) {
        alert('Erro ao alterar promoção.');
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
      } catch (err) {
        alert('Erro ao salvar categoria.');
      }
    },
    async toggleCategoryActive(cat) {
      try {
        await axios.patch(`/api/admin/categories/${cat.id}/toggle`);
        cat.is_active = !cat.is_active;
      } catch (err) {
        alert('Erro ao alterar status da categoria.');
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
      } catch (err) {
        alert('Erro ao salvar combo. Verifique os dados.');
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
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao excluir categoria.');
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
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao excluir item.');
      } finally {
        this.isDeletingProduct = false;
      }
    },
  },
};
</script>

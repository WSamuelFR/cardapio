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

    <!-- Container Principal do Produto (Estilo Marketplace & iFood) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
      <div class="row g-0">
        
        <!-- Foto em Destaque do Produto -->
        <div class="col-12 col-md-5 bg-light d-flex align-items-center justify-content-center position-relative overflow-hidden" style="min-height: 260px; max-height: 380px;">
          <img
            v-if="product.photo_path"
            :src="product.photo_path"
            :alt="product.name"
            class="w-100 h-100 object-fit-cover"
          >
          <div v-else class="text-center p-5 text-muted">
            <i :class="isPizza ? 'bi-pie-chart text-warning' : (isMonteSeuPastel ? 'bi-layers text-warning' : (product.is_combo ? 'bi-gift-fill text-success' : 'bi-image text-danger'))" class="bi fs-1 d-block mb-2 opacity-50"></i>
            <span class="small fw-semibold text-secondary">
              {{ isPizza ? 'Pizza AKY' : (isMonteSeuPastel ? 'Monte Seu Pastel' : 'Foto do Produto') }}
            </span>
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
        <div class="col-12 col-md-7 p-3 p-md-4 d-flex flex-column justify-content-between">
          <div>
            <!-- Categoria ou Tag -->
            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
              {{ product.category ? product.category.name : (product.is_combo ? 'Combo do Dia' : 'AKY Lanches') }}
            </div>

            <!-- Título do Produto -->
            <h3 class="fw-bold text-dark mb-2" style="font-size: 1.35rem;">
              {{ product.name }}
            </h3>

            <!-- Bloco de Preços Dinâmicos -->
            <div class="d-flex align-items-baseline gap-2 mb-3">
              <span v-if="product.is_promotion && product.promotional_price && !isPizza" class="text-muted text-decoration-line-through fs-6">
                R$ {{ formatPrice(product.price) }}
              </span>
              <span class="fw-bold fs-3 text-aky-primary">
                R$ {{ formatPrice(calculatedUnitPrice) }}
              </span>
              <span v-if="isPizza" class="badge bg-warning bg-opacity-25 text-dark rounded-pill px-2.5 py-1 small fw-semibold">
                Tamanho: {{ pizzaSizeLabel }}
              </span>
              <span v-else-if="product.is_combo" class="badge bg-success rounded-pill px-2 py-1 small">
                Preço Promocional
              </span>
            </div>

            <!-- Descrição e Ingredientes Originais -->
            <div class="mb-3">
              <p class="text-muted small mb-0 lh-base">
                {{ product.description || 'Preparo artesanal com os melhores ingredientes, feito na hora para garantir o máximo de sabor e qualidade.' }}
              </p>
            </div>

            <hr class="opacity-25 my-3">

            <!-- ======================================================== -->
            <!-- FERRAMENTA ESTILO IFOOD 1: MONTE SEU PASTEL (ATÉ 4 INGR) -->
            <!-- ======================================================== -->
            <div v-if="isMonteSeuPastel" class="card border-0 bg-light rounded-4 p-3 mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                    <i class="bi bi-layers-fill text-warning fs-5"></i>
                    <span>Monte Seu Pastel</span>
                  </h6>
                  <small class="text-muted" style="font-size: 0.75rem;">
                    Até {{ pastelMaxFree }} ingredientes inclusos sem custo extra
                  </small>
                </div>
                <div>
                  <span
                    :class="['badge rounded-pill px-3 py-1.5 fw-bold small transition', selectedPastelIngredients.length === 0 ? 'bg-danger bg-opacity-15 text-danger border border-danger' : 'bg-success text-white shadow-sm']"
                  >
                    {{ selectedPastelIngredients.length === 0 ? `Obrigatório (0/${pastelMaxFree})` : (selectedPastelIngredients.length <= pastelMaxFree ? `${selectedPastelIngredients.length}/${pastelMaxFree} Inclusos ✓` : `${selectedPastelIngredients.length} Selecionados (${selectedPastelIngredients.length - pastelMaxFree} extras)`) }}
                  </span>
                </div>
              </div>

              <!-- Regra Clara de Cobrança -->
              <div class="alert alert-light border rounded-3 py-2 px-2.5 mb-2 small d-flex flex-wrap align-items-center justify-content-between text-muted" style="font-size: 0.75rem;">
                <div class="d-flex align-items-center gap-1.5">
                  <i class="bi bi-info-circle-fill text-warning fs-6"></i>
                  <span>
                    <strong>Regra:</strong> 1º ao {{ pastelMaxFree }}º incluso. A partir do <strong>{{ pastelChargeFrom }}º ingrediente</strong>: + R$ {{ formatPrice(pastelExtraPrice) }} cada.
                  </span>
                </div>
                <span class="badge bg-white text-secondary border mt-1 mt-sm-0">Máx. {{ pastelMaxTotal }}</span>
              </div>

              <!-- Barra de Progresso Visual dos Ingredientes -->
              <div class="progress mb-2 bg-white border" style="height: 6px;">
                <div
                  class="progress-bar transition"
                  :class="selectedPastelIngredients.length > 0 ? (selectedPastelIngredients.length > pastelMaxFree ? 'bg-warning' : 'bg-success') : 'bg-danger'"
                  role="progressbar"
                  :style="{ width: Math.min(100, (selectedPastelIngredients.length / pastelMaxFree * 100)) + '%' }"
                ></div>
              </div>

              <!-- Aviso de ingrediente extra se houver -->
              <div v-if="selectedPastelIngredients.length > pastelMaxFree" class="text-success small fw-semibold mb-2 d-flex align-items-center gap-1">
                <i class="bi bi-plus-circle-fill"></i>
                <span>+{{ selectedPastelIngredients.length - pastelMaxFree }} ingrediente(s) extra(s) (+ R$ {{ formatPrice((selectedPastelIngredients.length - pastelMaxFree) * pastelExtraPrice) }})</span>
              </div>

              <!-- SELETOR DE ABAS SALGADOS / DOCES -->
              <div class="d-flex gap-2 mb-3">
                <button
                  type="button"
                  :class="['btn btn-sm rounded-pill px-3 fw-bold flex-fill transition border-0', pastelTab === 'salgados' ? 'btn-warning text-dark shadow-sm' : 'btn-white text-muted border']"
                  @click="pastelTab = 'salgados'"
                >
                  🧀 Salgados ({{ pastelIngredientsSalgados.length }})
                </button>
                <button
                  type="button"
                  :class="['btn btn-sm rounded-pill px-3 fw-bold flex-fill transition border-0', pastelTab === 'doces' ? 'btn-warning text-dark shadow-sm' : 'btn-white text-muted border']"
                  @click="pastelTab = 'doces'"
                >
                  🍫 Doces ({{ pastelIngredientsDoces.length }})
                </button>
              </div>

              <!-- Grade de Ingredientes Interativos com Badge de Preço Extra -->
              <div class="row g-2">
                <div
                  v-for="ing in currentPastelIngredientsList"
                  :key="'ing-' + ing"
                  class="col-6 col-sm-4"
                >
                  <div
                    :class="[
                      'card p-2 rounded-3 text-center cursor-pointer transition user-select-none h-100 d-flex flex-column justify-content-center align-items-center border position-relative',
                      isPastelIngredientSelected(ing) ? 'border-success bg-white shadow-sm ring-success' : 'bg-white border-light text-dark',
                      isPastelMaxReached && !isPastelIngredientSelected(ing) ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    @click="togglePastelIngredient(ing)"
                  >
                    <div class="d-flex align-items-center justify-content-between w-100 px-1 gap-1">
                      <div class="min-w-0 text-start">
                        <span class="small fw-semibold text-truncate d-block text-dark" style="font-size: 0.78rem;">
                          {{ ing }}
                        </span>
                        <span v-if="!isPastelIngredientSelected(ing) && selectedPastelIngredients.length >= pastelMaxFree" class="badge bg-success bg-opacity-10 text-success p-0 font-monospace fw-bold" style="font-size: 0.68rem;">
                          + R$ {{ formatPrice(pastelExtraPrice) }}
                        </span>
                      </div>
                      <i
                        v-if="isPastelIngredientSelected(ing)"
                        class="bi bi-check-circle-fill text-success fs-6 flex-shrink-0"
                      ></i>
                      <i
                        v-else
                        class="bi bi-plus-circle text-muted fs-6 flex-shrink-0"
                      ></i>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Resumo dos Ingredientes Selecionados -->
              <div v-if="selectedPastelIngredients.length > 0" class="mt-3 p-2 bg-white rounded-3 border small">
                <span class="text-muted fw-bold d-block mb-1" style="font-size: 0.72rem;">Seu pastel terá:</span>
                <div class="d-flex flex-wrap gap-1">
                  <span
                    v-for="ing in selectedPastelIngredients"
                    :key="'badge-' + ing"
                    class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2.5 py-1"
                  >
                    {{ ing }}
                    <i class="bi bi-x ms-1 cursor-pointer" @click.stop="togglePastelIngredient(ing)"></i>
                  </span>
                </div>
              </div>
            </div>

            <!-- ======================================================== -->
            <!-- FERRAMENTA ESTILO IFOOD 2: PIZZAS (TAMANHOS E SABORES) -->
            <!-- ======================================================== -->
            <div v-if="isPizza" class="d-flex flex-column gap-3 mb-3">
              
              <!-- ETAPA 1: ESCOLHA DO TAMANHO (OBRIGATÓRIO) -->
              <div class="card border-0 bg-light rounded-4 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                      <i class="bi bi-pie-chart-fill text-danger fs-5"></i>
                      <span>1. Escolha o Tamanho</span>
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Selecione 1 opção • Preço ajustado na hora</small>
                  </div>
                  <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold small shadow-sm">
                    Obrigatório ✓
                  </span>
                </div>

                <div class="row g-2">
                  <div
                    v-for="sz in pizzaSizes"
                    :key="'sz-' + sz.id"
                    class="col-12 col-sm-4"
                  >
                    <div
                      :class="['card p-2.5 rounded-3 cursor-pointer transition border h-100', selectedPizzaSize === sz.id ? 'border-danger bg-white shadow-sm ring-danger' : 'bg-white border-light']"
                      @click="selectedPizzaSize = sz.id"
                    >
                      <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold small text-dark">{{ sz.name }}</span>
                        <i :class="selectedPizzaSize === sz.id ? 'bi-record-circle-fill text-danger' : 'bi-circle text-muted'" class="bi fs-5"></i>
                      </div>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">{{ sz.slices }}</small>
                      <span class="fw-bold text-danger d-block mt-1 small">R$ {{ formatPrice(sz.price) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ETAPA 2: DIVISÃO DE SABORES (MEIO A MEIO - OPCIONAL) -->
              <div class="card border-0 bg-light rounded-4 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                      <i class="bi bi-intersect text-warning fs-5"></i>
                      <span>2. Quantidade de Sabores</span>
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Deseja dividir a pizza meio a meio?</small>
                  </div>
                </div>

                <!-- Seletor de Modo: Inteira ou Meio a Meio -->
                <div class="d-flex gap-2 mb-3">
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill px-3 fw-bold flex-fill transition border-0', pizzaFlavorsMode === 'single' ? 'btn-dark text-white shadow-sm' : 'btn-white text-muted border']"
                    @click="pizzaFlavorsMode = 'single'"
                  >
                    <i class="bi bi-circle-fill text-warning me-1"></i> Inteira (1 Sabor)
                  </button>
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill px-3 fw-bold flex-fill transition border-0', pizzaFlavorsMode === 'half' ? 'btn-danger text-white shadow-sm' : 'btn-white text-muted border']"
                    @click="pizzaFlavorsMode = 'half'"
                  >
                    <i class="bi bi-pie-chart text-warning me-1"></i> Meio a Meio (2 Sabores)
                  </button>
                </div>

                <!-- Painel Meio a Meio Ativo -->
                <div v-if="pizzaFlavorsMode === 'half'" class="bg-white p-3 rounded-3 border">
                  
                  <!-- 1ª Metade Fixa -->
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-danger rounded-pill px-2 py-1 small">1ª Metade (50%)</span>
                      <strong class="small text-dark">{{ product.name }}</strong>
                    </div>
                    <i class="bi bi-lock-fill text-muted small" title="Sabor principal"></i>
                  </div>

                  <!-- 2ª Metade Selecionável -->
                  <div class="mb-2">
                    <label class="small fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                      <span>Escolha a 2ª Metade (50%):</span>
                      <span v-if="secondPizzaFlavor" class="badge bg-success rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Escolhido ✓</span>
                    </label>

                    <!-- Campo de Busca de Sabores -->
                    <div class="input-group input-group-sm mb-2">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                      </span>
                      <input
                        type="text"
                        class="form-control border-start-0"
                        v-model="secondFlavorSearch"
                        placeholder="Pesquisar pizza para a 2ª metade..."
                      >
                    </div>

                    <!-- Lista com Rolagem Suave de Sabores Disponíveis -->
                    <div class="overflow-y-auto border rounded-3 p-1 bg-light" style="max-height: 150px;">
                      <div
                        v-for="piz in filteredSecondPizzaFlavors"
                        :key="'piz2-' + piz.id"
                        :class="['d-flex align-items-center justify-content-between p-2 rounded-2 cursor-pointer transition mb-1', secondPizzaFlavor && secondPizzaFlavor.id === piz.id ? 'bg-success text-white shadow-sm' : 'bg-white hover-bg-light text-dark']"
                        @click="secondPizzaFlavor = piz"
                      >
                        <span class="small fw-semibold text-truncate me-2">{{ piz.name }}</span>
                        <i :class="secondPizzaFlavor && secondPizzaFlavor.id === piz.id ? 'bi-check-circle-fill text-white' : 'bi-plus-circle text-muted'" class="bi fs-6 flex-shrink-0"></i>
                      </div>
                    </div>
                  </div>

                  <!-- Resumo da Pizza Meio a Meio -->
                  <div v-if="secondPizzaFlavor" class="alert alert-success border-0 rounded-3 p-2 small mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-check2-circle fs-5 text-success"></i>
                    <div>
                      <strong>Combinação Meio a Meio:</strong> 1/2 {{ product.name }} + 1/2 {{ secondPizzaFlavor.name }}
                    </div>
                  </div>
                </div>

              </div>

              <!-- ETAPA 3: BORDA RECHEADA (OPCIONAL) -->
              <div class="card border-0 bg-light rounded-4 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                      <i class="bi bi-record-circle text-danger fs-5"></i>
                      <span>3. Deseja Borda Recheada?</span>
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Opcional • Deixe sua pizza ainda mais gostosa</small>
                  </div>
                  <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small">Opcional</span>
                </div>

                <div class="row g-2">
                  <div
                    v-for="border in pizzaBorderOptions"
                    :key="'border-' + border.id"
                    class="col-6 col-sm-3"
                  >
                    <div
                      :class="['card p-2 rounded-3 cursor-pointer transition border text-center h-100', selectedPizzaBorder === border.id ? 'border-success bg-white shadow-sm ring-success' : 'bg-white border-light']"
                      @click="selectedPizzaBorder = border.id"
                    >
                      <span class="small fw-bold text-dark d-block mb-1" style="font-size: 0.76rem;">{{ border.name }}</span>
                      <span :class="['small fw-bold', border.price > 0 ? 'text-success' : 'text-muted']" style="font-size: 0.72rem;">
                        {{ border.price > 0 ? '+ R$ ' + formatPrice(border.price) : 'Sem Custo' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- ======================================================== -->
            <!-- FERRAMENTA ESTILO IFOOD 3: ADICIONAIS OPCIONAIS (LANCHES) -->
            <!-- ======================================================== -->
            <div v-if="hasExtrasOption" class="card border-0 bg-light rounded-4 p-3 mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                    <i class="bi bi-plus-square-fill text-success fs-5"></i>
                    <span>Turbine seu Pedido (Adicionais)</span>
                  </h6>
                  <small class="text-muted" style="font-size: 0.75rem;">Escolha adicionais para enriquecer seu lanche</small>
                </div>
                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small">Opcional</span>
              </div>

              <div class="row g-2">
                <div
                  v-for="extra in availableExtrasList"
                  :key="'extra-' + extra.name"
                  class="col-12 col-sm-6"
                >
                  <div
                    :class="['card p-2.5 rounded-3 cursor-pointer transition border bg-white d-flex flex-row align-items-center justify-content-between', isExtraSelected(extra) ? 'border-success shadow-sm ring-success' : 'border-light']"
                    @click="toggleExtra(extra)"
                  >
                    <div>
                      <span class="small fw-semibold text-dark d-block" style="font-size: 0.8rem;">{{ extra.name }}</span>
                      <span class="small fw-bold text-success" style="font-size: 0.75rem;">+ R$ {{ formatPrice(extra.price) }}</span>
                    </div>
                    <i :class="isExtraSelected(extra) ? 'bi-check-circle-fill text-success fs-5' : 'bi-plus-circle text-muted fs-5'"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Campo de Observações Personalizadas -->
            <div class="mb-3">
              <label class="form-label fw-bold text-dark mb-1 small d-flex align-items-center justify-content-between">
                <span><i class="bi bi-chat-left-dots text-aky-primary me-1"></i> Alguma observação para a cozinha?</span>
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

            <!-- Alerta de Validação se houver etapa obrigatória pendente -->
            <div v-if="!isValidToAddToCart" class="alert alert-warning border-0 rounded-3 p-2 text-center small mb-2 fw-semibold">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              <span v-if="isMonteSeuPastel">Escolha pelo menos 1 ingrediente para seu pastel!</span>
              <span v-else-if="isPizza && pizzaFlavorsMode === 'half' && !secondPizzaFlavor">Escolha a 2ª metade da sua pizza!</span>
            </div>

            <!-- Botão de Ação: Adicionar ao Carrinho -->
            <button
              class="btn btn-aky-primary w-100 rounded-pill py-3 fw-bold d-flex align-items-center justify-content-between px-4 shadow transition"
              :disabled="!product.is_available || !isValidToAddToCart"
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
    menuSettings: {
      type: Object,
      default: () => null,
    },
  },
  data() {
    return {
      quantity: 1,
      itemNotes: '',
      
      // Estado do Montador "Monte Seu Pastel"
      selectedPastelIngredients: [],
      pastelTab: 'salgados',

      // Estado do Montador de Pizzas
      selectedPizzaSize: 'pequena', // 'pequena', 'media', 'grande'
      pizzaFlavorsMode: 'single', // 'single' ou 'half'
      secondPizzaFlavor: null,
      secondFlavorSearch: '',
      selectedPizzaBorder: 'nenhuma',

      // Estado dos Adicionais Opcionais
      selectedExtras: [],
    };
  },
  watch: {
    product: {
      immediate: true,
      handler() {
        this.resetOptions();
      },
    },
    pizzaSizes: {
      immediate: true,
      handler(sizes) {
        if (sizes && sizes.length > 0 && !sizes.some(s => s.id === this.selectedPizzaSize)) {
          this.selectedPizzaSize = sizes[0].id;
        }
      },
    },
  },
  computed: {
    isMonteSeuPastel() {
      const name = (this.product.name || '').toLowerCase();
      return name.includes('monte seu pastel');
    },
    isPizza() {
      const name = (this.product.name || '').toLowerCase();
      const catSlug = (this.product.category && this.product.category.slug) ? this.product.category.slug.toLowerCase() : '';
      if (catSlug.includes('pastel') && !name.includes('pizza')) return false;
      return catSlug.includes('pizza') || name.startsWith('pizza');
    },
    hasExtrasOption() {
      if (this.isMonteSeuPastel || this.isPizza) return false;
      const catSlug = (this.product.category && this.product.category.slug) ? this.product.category.slug.toLowerCase() : '';
      return catSlug.includes('sanduiche') || catSlug.includes('tapioca') || catSlug.includes('cuscuz') || catSlug.includes('artesanal');
    },

    // Configurações Dinâmicas do Banco de Dados para Monte Seu Pastel
    montePastelSettings() {
      const defaults = {
        base_price: 15.00,
        charge_from_ingredient: 5,
        max_free_ingredients: 4,
        extra_ingredient_price: 3.00,
        max_total_ingredients: 10,
        allow_extra_ingredients: true,
        ingredients_salgados: [],
        ingredients_doces: [],
      };
      return (this.menuSettings && this.menuSettings.monte_pastel) ? { ...defaults, ...this.menuSettings.monte_pastel } : defaults;
    },
    pastelChargeFrom() {
      if (this.montePastelSettings.charge_from_ingredient) {
        return Number(this.montePastelSettings.charge_from_ingredient);
      }
      return (Number(this.montePastelSettings.max_free_ingredients) || 4) + 1;
    },
    pastelMaxFree() {
      if (this.montePastelSettings.charge_from_ingredient) {
        return Math.max(1, Number(this.montePastelSettings.charge_from_ingredient) - 1);
      }
      return Number(this.montePastelSettings.max_free_ingredients) || 4;
    },
    pastelMaxTotal() {
      return Number(this.montePastelSettings.max_total_ingredients) || 10;
    },
    pastelExtraPrice() {
      return parseFloat(this.montePastelSettings.extra_ingredient_price) || 3.00;
    },
    pastelIngredientsSalgados() {
      if (this.montePastelSettings.ingredients_salgados && this.montePastelSettings.ingredients_salgados.length > 0) {
        return this.montePastelSettings.ingredients_salgados
          .filter(i => (typeof i === 'string') || (i.is_available !== false))
          .map(i => typeof i === 'string' ? i : i.name);
      }
      return [
        'Bacon', 'Calabresa', 'Carne Moída', 'Carne Seca', 'Frango',
        'Catupiry', 'Cheddar', 'Queijo Coalho', 'Queijo de Manteiga',
        'Muçarela', 'Parmesão', 'Presunto', 'Azeitona', 'Dueto',
        'Milho', 'Ovo', 'Tomate', 'Cebola'
      ];
    },
    pastelIngredientsDoces() {
      if (this.montePastelSettings.ingredients_doces && this.montePastelSettings.ingredients_doces.length > 0) {
        return this.montePastelSettings.ingredients_doces
          .filter(i => (typeof i === 'string') || (i.is_available !== false))
          .map(i => typeof i === 'string' ? i : i.name);
      }
      return [
        'Banana', 'Canela', 'Chocolate ao Leite', 'Chocolate Branco',
        'Doce de Leite', 'Goiabada', 'Leite Condensado'
      ];
    },
    currentPastelIngredientsList() {
      return this.pastelTab === 'salgados' ? this.pastelIngredientsSalgados : this.pastelIngredientsDoces;
    },
    isPastelMaxReached() {
      if (!this.montePastelSettings.allow_extra_ingredients) {
        return this.selectedPastelIngredients.length >= this.pastelMaxFree;
      }
      return this.selectedPastelIngredients.length >= this.pastelMaxTotal;
    },

    // Configurações Dinâmicas do Banco de Dados para Pizzas
    pizzaSizes() {
      if (this.menuSettings && this.menuSettings.pizza_sizes && this.menuSettings.pizza_sizes.length > 0) {
        return this.menuSettings.pizza_sizes.filter(s => s.is_available !== false);
      }
      return [
        { id: 'pequena', name: 'Pequena', slices: '6 Fatias', price: 36.00 },
        { id: 'media', name: 'Média (Mais Pedida)', slices: '8 Fatias', price: 43.00 },
        { id: 'grande', name: 'Grande (Família)', slices: '10 Fatias', price: 52.00 },
      ];
    },
    pizzaBorderOptions() {
      if (this.menuSettings && this.menuSettings.pizza_borders && this.menuSettings.pizza_borders.length > 0) {
        return this.menuSettings.pizza_borders.filter(b => b.is_available !== false);
      }
      return [
        { id: 'nenhuma', name: 'Sem Borda Recheada', price: 0.00 },
        { id: 'catupiry', name: 'Borda Catupiry', price: 8.00 },
        { id: 'cheddar', name: 'Borda Cheddar', price: 8.00 },
        { id: 'chocolate', name: 'Borda Chocolate', price: 10.00 },
      ];
    },
    pizzaSizeLabel() {
      const match = this.pizzaSizes.find(s => s.id === this.selectedPizzaSize) || this.pizzaSizes[0];
      return match ? `${match.name} (${match.slices})` : 'Pequena';
    },
    filteredSecondPizzaFlavors() {
      const term = this.secondFlavorSearch.toLowerCase().trim();
      return (this.allProducts || [])
        .filter(p => {
          if (p.id === this.product.id) return false;
          const pName = (p.name || '').toLowerCase();
          const pSlug = (p.category && p.category.slug) ? p.category.slug.toLowerCase() : '';
          const isPiz = pSlug.includes('pizza') || pName.startsWith('pizza');
          if (!isPiz) return false;
          if (term) return pName.includes(term);
          return true;
        });
    },

    // Adicionais Dinâmicos: Puxa diretamente dos produtos da categoria "Adicionais" cadastrados no banco
    availableExtrasList() {
      const adicionaisProducts = (this.allProducts || []).filter(p => {
        const isAvailable = p.is_available !== false;
        const catSlug = (p.category && p.category.slug) ? p.category.slug.toLowerCase() : '';
        const name = (p.name || '').toLowerCase();
        return isAvailable && (catSlug === 'adicionais' || name.startsWith('adicional'));
      });

      if (adicionaisProducts.length > 0) {
        return adicionaisProducts.map(p => ({
          name: p.name.replace(/^Adicional de\s+/i, '').replace(/^Adicional\s+/i, ''),
          full_name: p.name,
          price: parseFloat(p.effective_price || p.price) || 0,
        }));
      }

      return [
        { name: 'Bacon Crocante', price: 5.00 },
        { name: 'Queijo Coalho Assado', price: 5.00 },
        { name: 'Queijo Muçarela Extra', price: 5.00 },
        { name: 'Cheddar Cremoso', price: 4.00 },
        { name: 'Catupiry Original', price: 4.00 },
        { name: 'Ovo Frito na Manteiga', price: 3.00 },
        { name: 'Carne Tradicional Extra', price: 5.00 },
        { name: 'Carne Artesanal 100g', price: 8.00 },
        { name: 'Salada Extra (Alface e Tomate)', price: 3.00 },
      ];
    },

    calculatedUnitPrice() {
      // 1. Regra para Monte Seu Pastel Dinâmico
      if (this.isMonteSeuPastel) {
        let base = parseFloat(this.montePastelSettings.base_price || this.product.price) || 15.00;
        const extraCount = Math.max(0, this.selectedPastelIngredients.length - this.pastelMaxFree);
        if (extraCount > 0) {
          base += extraCount * this.pastelExtraPrice;
        }
        return base;
      }

      // 2. Regra para Pizzas Dinâmico (Tamanhos e Bordas do Banco)
      if (this.isPizza) {
        const sizeObj = this.pizzaSizes.find(s => s.id === this.selectedPizzaSize) || this.pizzaSizes[0];
        let base = sizeObj ? parseFloat(sizeObj.price) : 36.00;
        const borderObj = this.pizzaBorderOptions.find(b => b.id === this.selectedPizzaBorder);
        if (borderObj && borderObj.price > 0) {
          base += parseFloat(borderObj.price);
        }
        return base;
      }

      // 3. Regra Padrão com Adicionais Dinâmicos
      let unit = 0;
      if (this.product.effective_price !== undefined && this.product.effective_price !== null) {
        unit = parseFloat(this.product.effective_price);
      } else if (this.product.is_promotion && this.product.promotional_price) {
        unit = parseFloat(this.product.promotional_price);
      } else {
        unit = parseFloat(this.product.price) || 0;
      }

      if (this.selectedExtras && this.selectedExtras.length > 0) {
        this.selectedExtras.forEach(e => {
          unit += parseFloat(e.price || 0);
        });
      }

      return unit;
    },
    discountAmount() {
      if (this.product.is_promotion && this.product.promotional_price && !this.isPizza) {
        const original = parseFloat(this.product.price) || 0;
        const promo = parseFloat(this.product.promotional_price) || 0;
        return original > promo ? original - promo : 0;
      }
      return 0;
    },
    totalPrice() {
      return this.calculatedUnitPrice * this.quantity;
    },
    isValidToAddToCart() {
      if (this.isMonteSeuPastel) {
        return this.selectedPastelIngredients.length >= 1;
      }
      if (this.isPizza && this.pizzaFlavorsMode === 'half') {
        return this.secondPizzaFlavor !== null;
      }
      return true;
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
    resetOptions() {
      this.quantity = 1;
      this.itemNotes = '';
      this.selectedPastelIngredients = [];
      this.pastelTab = 'salgados';
      this.selectedPizzaSize = (this.pizzaSizes && this.pizzaSizes[0]) ? this.pizzaSizes[0].id : 'pequena';
      this.pizzaFlavorsMode = 'single';
      this.secondPizzaFlavor = null;
      this.secondFlavorSearch = '';
      this.selectedPizzaBorder = 'nenhuma';
      this.selectedExtras = [];
    },
    increaseQty() {
      this.quantity += 1;
    },
    decreaseQty() {
      if (this.quantity > 1) {
        this.quantity -= 1;
      }
    },
    isPastelIngredientSelected(ing) {
      return this.selectedPastelIngredients.includes(ing);
    },
    togglePastelIngredient(ing) {
      const idx = this.selectedPastelIngredients.indexOf(ing);
      if (idx > -1) {
        this.selectedPastelIngredients.splice(idx, 1);
      } else {
        if (!this.isPastelMaxReached) {
          this.selectedPastelIngredients.push(ing);
        }
      }
    },
    isExtraSelected(extra) {
      return this.selectedExtras.some(e => e.name === extra.name);
    },
    toggleExtra(extra) {
      const idx = this.selectedExtras.findIndex(e => e.name === extra.name);
      if (idx > -1) {
        this.selectedExtras.splice(idx, 1);
      } else {
        this.selectedExtras.push(extra);
      }
    },
    handleAddToCart() {
      if (!this.isValidToAddToCart) return;

      let finalName = this.product.name;
      let notesParts = [];

      // Monta detalhes específicos conforme o produto
      if (this.isMonteSeuPastel) {
        finalName = `Monte Seu Pastel (${this.selectedPastelIngredients.length} Ingredientes)`;
        notesParts.push(`Recheio: ${this.selectedPastelIngredients.join(', ')}`);
      } else if (this.isPizza) {
        finalName = `${this.product.name} [${this.pizzaSizeLabel}]`;
        if (this.pizzaFlavorsMode === 'half' && this.secondPizzaFlavor) {
          notesParts.push(`Meio a Meio: 1/2 ${this.product.name} + 1/2 ${this.secondPizzaFlavor.name}`);
        } else {
          notesParts.push(`Sabor: Inteira ${this.product.name}`);
        }
        const borderObj = this.pizzaBorderOptions.find(b => b.id === this.selectedPizzaBorder);
        if (borderObj && borderObj.id !== 'nenhuma') {
          notesParts.push(`${borderObj.name} (+R$ ${this.formatPrice(borderObj.price)})`);
        }
      }

      if (this.selectedExtras.length > 0) {
        notesParts.push(`Adicionais: ${this.selectedExtras.map(e => e.name).join(', ')}`);
      }

      if (this.itemNotes.trim()) {
        notesParts.push(`Obs: ${this.itemNotes.trim()}`);
      }

      const fullNotes = notesParts.join(' | ');

      this.$emit('add-to-cart', {
        ...this.product,
        name: finalName,
        effective_price: this.calculatedUnitPrice,
        quantity: this.quantity,
        notes: fullNotes,
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
.cursor-not-allowed {
  cursor: not-allowed;
}
.ring-success {
  border-color: #198754 !important;
  background-color: #f6fcf8 !important;
}
.ring-danger {
  border-color: #dc3545 !important;
  background-color: #fff8f8 !important;
}
.hover-bg-light:hover {
  background-color: #e9ecef !important;
}
</style>

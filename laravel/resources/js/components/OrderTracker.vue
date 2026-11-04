<template>
  <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 my-4 bg-white">
    
    <!-- Cabeçalho -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
      <div class="d-flex align-items-center">
        <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-2 text-aky-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
          <i class="bi bi-clock-history fs-4"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0 text-dark">Acompanhar Pedido</h5>
          <small class="text-muted">Consulte o status do seu pedido em tempo real</small>
        </div>
      </div>

      <button
        v-if="order"
        type="button"
        class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-muted d-inline-flex align-items-center gap-1.5 shadow-none"
        @click="resetSearch"
      >
        <i class="bi bi-search"></i>
        <span>Consultar outro</span>
      </button>
    </div>

    <!-- Barra de Busca -->
    <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden border">
      <input
        type="text"
        class="form-control text-uppercase font-monospace border-0 shadow-none ps-3"
        placeholder="Digite o código (ex: AKY-20261029-ABCD)"
        v-model="orderNumber"
        @keyup.enter="searchOrder(true)"
      >
      <button
        class="btn btn-aky-primary px-3 px-sm-4 border-0 d-flex align-items-center justify-content-center"
        @click="searchOrder(true)"
        :disabled="loading"
        title="Consultar"
      >
        <span v-if="loading" class="spinner-border spinner-border-sm"></span>
        <i v-else class="bi bi-search fs-5"></i>
      </button>
    </div>

    <!-- Pedidos Recentes Consultados Neste Aparelho -->
    <div v-if="recentOrders.length > 0 && !order" class="mb-3 p-2.5 rounded-4 bg-light border">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <small class="text-muted fw-bold d-flex align-items-center gap-1" style="font-size: 0.75rem;">
          <i class="bi bi-clock-history text-danger"></i> Pedidos Recentes neste Aparelho:
        </small>
        <button
          type="button"
          class="btn btn-link p-0 text-muted small text-decoration-none"
          style="font-size: 0.72rem;"
          @click="clearRecentOrders"
        >
          Limpar histórico
        </button>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <button
          v-for="rec in recentOrders"
          :key="rec.code"
          type="button"
          class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-1 text-dark d-flex align-items-center gap-2 shadow-sm transition hover-pill"
          @click="selectRecentOrder(rec.code)"
        >
          <span class="font-monospace fw-bold text-aky-primary" style="font-size: 0.78rem;">{{ rec.code }}</span>
          <span v-if="rec.date" class="text-muted small d-none d-sm-inline" style="font-size: 0.7rem;">• {{ rec.date }}</span>
          <span class="badge rounded-pill" :class="getStatusBadgeClass(rec.status)" style="font-size: 0.65rem;">
            {{ getStatusLabel(rec.status) }}
          </span>
        </button>
      </div>
    </div>

    <!-- Alerta de Erro -->
    <div v-if="error" class="alert alert-warning py-2 small rounded-3 d-flex align-items-center gap-2">
      <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
      <span>{{ error }}</span>
    </div>

    <!-- ======================================================== -->
    <!-- RESULTADO DO PEDIDO                                       -->
    <!-- ======================================================== -->
    <div v-if="order" class="card bg-light border-0 rounded-4 p-3 mt-2 shadow-sm">
      
      <!-- Cabeçalho do Pedido com Código, Cópia e Auto-refresh -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-3 border-bottom">
        <div>
          <span class="text-muted small d-block">Código do Pedido:</span>
          <div class="d-flex align-items-center gap-2 mt-0.5">
            <span class="fw-bold fs-5 font-monospace text-aky-primary">{{ order.order_number }}</span>
            <button
              type="button"
              class="btn btn-sm rounded-pill px-2.5 py-0.5 border d-inline-flex align-items-center gap-1 transition"
              :class="copiedCode ? 'btn-success text-white' : 'btn-white bg-white text-dark shadow-sm'"
              style="font-size: 0.72rem;"
              @click="copyOrderCode"
              :title="copiedCode ? 'Código copiado!' : 'Copiar código'"
            >
              <i :class="copiedCode ? 'bi bi-check2' : 'bi bi-copy'"></i>
              <span>{{ copiedCode ? 'Copiado!' : 'Copiar' }}</span>
            </button>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2 text-end">
          <div v-if="isActiveOrder" class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-success bg-opacity-10 text-success fw-semibold" style="font-size: 0.72rem;">
            <span class="live-dot"></span>
            <span>Ao Vivo (15s)</span>
          </div>

          <button
            type="button"
            class="btn btn-sm btn-white bg-white border rounded-pill px-2.5 py-1 text-dark d-inline-flex align-items-center gap-1 shadow-sm transition"
            style="font-size: 0.75rem;"
            @click="searchOrder(false)"
            :disabled="loading"
            title="Atualizar dados agora"
          >
            <i class="bi bi-arrow-repeat" :class="loading ? 'spin-icon' : ''"></i>
            <span class="d-none d-sm-inline">Atualizar</span>
          </button>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- LINHA DO TEMPO VISUAL (STEPPER DE 4 ETAPAS)              -->
      <!-- ======================================================== -->
      <div v-if="order.status !== 'cancelado'" class="card bg-white border-0 rounded-4 p-3 mb-3 shadow-sm">
        <div class="stepper-container">
          <!-- Linha de fundo -->
          <div class="stepper-progress-track">
            <div
              class="stepper-progress-fill"
              :style="{ width: stepperProgressPercent + '%' }"
            ></div>
          </div>

          <!-- Os 4 Passos -->
          <div class="stepper-steps">
            <!-- Passo 1: Recebido -->
            <div class="step-item" :class="getStepClass(1)">
              <div class="step-circle shadow-sm">
                <i v-if="currentStepIndex > 1" class="bi bi-check-lg"></i>
                <i v-else class="bi bi-receipt"></i>
              </div>
              <div class="step-label">Recebido</div>
            </div>

            <!-- Passo 2: Em Preparo -->
            <div class="step-item" :class="getStepClass(2)">
              <div class="step-circle shadow-sm">
                <i v-if="currentStepIndex > 2" class="bi bi-check-lg"></i>
                <i v-else class="bi bi-fire"></i>
              </div>
              <div class="step-label">Em Preparo</div>
            </div>

            <!-- Passo 3: A Caminho / Pronto -->
            <div class="step-item" :class="getStepClass(3)">
              <div class="step-circle shadow-sm">
                <i v-if="currentStepIndex > 3" class="bi bi-check-lg"></i>
                <i v-else :class="isPickupOrder ? 'bi bi-shop' : 'bi bi-bicycle'"></i>
              </div>
              <div class="step-label">{{ isPickupOrder ? 'Pronto' : 'A Caminho' }}</div>
            </div>

            <!-- Passo 4: Concluído -->
            <div class="step-item" :class="getStepClass(4)">
              <div class="step-circle shadow-sm">
                <i class="bi bi-check2-circle"></i>
              </div>
              <div class="step-label">Entregue</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- CARD DE STATUS ATUAL DETALHADO                            -->
      <!-- ======================================================== -->

      <!-- 1. STATUS: RECEBIDO -->
      <div v-if="order.status === 'recebido'" class="card border-0 shadow-sm rounded-4 p-3 my-2 bg-white border-start border-4 border-warning">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-warning bg-opacity-25 text-dark p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-receipt fs-2 text-warning"></i>
          </div>
          <div>
            <span class="badge bg-warning text-dark rounded-pill mb-1 fw-bold">Status Atual: Recebido</span>
            <h6 class="fw-bold text-dark mb-1">Seu pedido foi confirmado!</h6>
            <p class="text-muted small mb-0">Sua comanda já entrou na fila da cozinha e em instantes começará a ser preparada.</p>
          </div>
        </div>
      </div>

      <!-- 2. STATUS: EM PREPARAÇÃO -->
      <div v-else-if="order.status === 'em_preparacao'" class="card border-0 shadow-sm rounded-4 p-3 my-2 bg-white border-start border-4 border-primary">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-primary bg-opacity-25 text-primary p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-fire fs-2 text-primary"></i>
          </div>
          <div>
            <span class="badge bg-primary rounded-pill mb-1 fw-bold">Status Atual: Em Preparo</span>
            <h6 class="fw-bold text-dark mb-1">Preparando seus lanches!</h6>
            <p class="text-muted small mb-0">Nossos chapeiros e cozinheiros estão montando e fritando seus itens na hora com todo o capricho.</p>
          </div>
        </div>
      </div>

      <!-- 3. STATUS: SAIU PARA ENTREGA (COM MOTOBOY E ANIMAÇÃO OU BALCÃO DE RETIRADA) -->
      <div v-else-if="order.status === 'saiu_para_entrega'" class="delivery-track-box my-2 p-3 bg-white rounded-4 shadow-sm border border-info border-start border-4 border-info">
        
        <!-- Caso A: Retirada no Balcão -->
        <template v-if="isPickupOrder">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-info bg-opacity-25 text-dark p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="bi bi-shop fs-2 text-info"></i>
              </div>
              <div>
                <span class="badge bg-info text-dark rounded-pill mb-1 fw-bold">Status Atual: Pronto para Retirada!</span>
                <h6 class="fw-bold text-dark mb-1">Seu pedido já está pronto no balcão!</h6>
                <p class="text-muted small mb-0">Você já pode comparecer ao balcão da AKY Lanches para retirar seu lanche quentinho.</p>
              </div>
            </div>

            <button
              type="button"
              class="btn btn-sm btn-dark rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5"
              @click="showMapModal = true"
            >
              <i class="bi bi-geo-alt-fill text-danger"></i>
              <span>Ver no Mapa</span>
            </button>
          </div>

          <!-- MINI MAPA DA LOCALIZAÇÃO DA AKY LANCHES NO RASTREIO -->
          <div class="rounded-3 overflow-hidden border shadow-sm mb-3 position-relative" style="height: 180px; background: #e9ecef;">
            <iframe
              width="100%"
              height="100%"
              frameborder="0"
              scrolling="no"
              marginheight="0"
              marginwidth="0"
              :src="'https://maps.google.com/maps?q=' + encodeURIComponent(currentStoreAddress) + '&t=&z=16&ie=UTF8&iwloc=&output=embed'"
              style="border:0;"
              loading="lazy"
            ></iframe>
          </div>

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
            <small class="text-muted">
              <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ currentStoreAddress }}
            </small>
            <a
              :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(currentStoreAddress)"
              target="_blank"
              class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1"
            >
              <i class="bi bi-compass-fill"></i>
              <span>Como Chegar (GPS / Waze)</span>
            </a>
          </div>
        </template>

        <!-- Caso B: Entrega em Domicílio com Motoboy -->
        <template v-else>
          <!-- Identificação do Motoboy -->
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center">
              <div class="rounded-circle bg-info bg-opacity-25 p-2 text-dark me-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-bicycle fs-4 text-info"></i>
              </div>
              <div>
                <span class="badge bg-info text-dark rounded-pill mb-1">Status Atual: A Caminho</span>
                <h6 class="fw-bold text-dark mb-0 fs-6">
                  Entregador: {{ order.motoboy ? order.motoboy.name : 'Equipe AKY Lanches' }}
                </h6>
              </div>
            </div>
            <span class="text-muted small">
              <i class="bi bi-geo-alt-fill text-danger me-1"></i> Indo até o seu endereço
            </span>
          </div>

          <!-- CENÁRIO COM A RUA E A MOTO ANIMADA EM MOVIMENTO -->
          <div class="street-container position-relative overflow-hidden rounded-3">
            <div class="asphalt">
              <div class="road-lines"></div>
            </div>

            <div class="moto-vehicle">
              <div class="moto-wrapper">
                <span class="moto-icon">🛵</span>
              </div>
            </div>

            <div class="destination-house">
              <i class="bi bi-house-door-fill text-white fs-5"></i>
            </div>
          </div>

          <div class="text-center mt-2">
            <small class="text-muted fst-italic">
              <i class="bi bi-hourglass-split text-warning me-1"></i> Seu pedido está quentinho na mochila térmica e já está chegando!
            </small>
          </div>
        </template>
      </div>

      <!-- 4. STATUS: CONCLUÍDO -->
      <div v-else-if="order.status === 'concluido'" class="card border-0 shadow-sm rounded-4 p-3 my-2 bg-white border-start border-4 border-success">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-success bg-opacity-25 text-success p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-check-circle-fill fs-2 text-success"></i>
          </div>
          <div>
            <span class="badge bg-success rounded-pill mb-1 fw-bold">Status Atual: Concluído</span>
            <h6 class="fw-bold text-dark mb-1">Pedido Entregue!</h6>
            <p class="text-muted small mb-0">Bom apetite! Muito obrigado pela preferência e volte sempre ao AKY Lanches.</p>
          </div>
        </div>
      </div>

      <!-- 5. STATUS: CANCELADO -->
      <div v-else-if="order.status === 'cancelado'" class="card border-0 shadow-sm rounded-4 p-3 my-2 bg-white border-start border-4 border-danger">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-danger bg-opacity-25 text-danger p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-x-circle-fill fs-2 text-danger"></i>
          </div>
          <div>
            <span class="badge bg-danger rounded-pill mb-1 fw-bold">Status: Cancelado</span>
            <h6 class="fw-bold text-dark mb-1">Este pedido foi cancelado</h6>
            <p class="text-muted small mb-0">Se precisar de qualquer esclarecimento, fale diretamente com nossa equipe de atendimento.</p>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- ÁREA DE CONFIRMAÇÃO DE RECEBIMENTO & AVALIAÇÃO DO CLIENTE -->
      <!-- ======================================================== -->
      <div v-if="order.status === 'saiu_para_entrega' || order.status === 'concluido'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-warning">
        
        <!-- Caso 1: Ainda NÃO confirmou nem avaliou -->
        <div v-if="!order.customer_confirmed_at && !order.reviewed_at">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
              <span class="fs-4">🍔⭐</span>
              <div>
                <h6 class="fw-bold text-dark mb-0">
                  {{ isPickupOrder ? 'Já pegou seu pedido no balcão?' : 'Seu pedido acabou de chegar?' }}
                </h6>
                <small class="text-muted">Confirme o recebimento e avalie seu lanche em segundos!</small>
              </div>
            </div>
          </div>

          <div class="p-3 bg-light rounded-4 border">
            <!-- Avaliação do Lanche -->
            <div class="mb-3">
              <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-1">
                <span><i class="bi bi-star-fill text-warning me-1"></i> 1. Como estava o lanche?</span>
                <span class="badge bg-warning text-dark fw-bold">{{ getRatingLabel(ratingForm.food_rating) }}</span>
              </label>
              <div class="d-flex gap-2">
                <button
                  v-for="star in 5"
                  :key="'food-star-' + star"
                  type="button"
                  class="btn p-1 border-0 bg-transparent fs-3 transition-star"
                  :class="star <= ratingForm.food_rating ? 'text-warning' : 'text-secondary opacity-25'"
                  @click="ratingForm.food_rating = star"
                  :title="star + ' estrelas'"
                >
                  ★
                </button>
              </div>
            </div>

            <!-- Avaliação da Entrega / Atendimento na Retirada -->
            <div class="mb-3 border-top pt-2">
              <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-1">
                <span>
                  <i :class="isPickupOrder ? 'bi bi-shop text-primary' : 'bi bi-bicycle text-info'" class="me-1"></i>
                  {{ isPickupOrder ? '2. Como foi o atendimento no balcão?' : '2. Como foi a rapidez e o entregador?' }}
                </span>
                <span class="badge bg-info text-dark fw-bold">{{ getRatingLabel(ratingForm.delivery_rating) }}</span>
              </label>
              <div class="d-flex gap-2">
                <button
                  v-for="star in 5"
                  :key="'deliv-star-' + star"
                  type="button"
                  class="btn p-1 border-0 bg-transparent fs-3 transition-star"
                  :class="star <= ratingForm.delivery_rating ? 'text-warning' : 'text-secondary opacity-25'"
                  @click="ratingForm.delivery_rating = star"
                  :title="star + ' estrelas'"
                >
                  ★
                </button>
              </div>
            </div>

            <!-- Comentário / Descrição -->
            <div class="mb-3 border-top pt-2">
              <label class="form-label fw-bold small text-dark mb-1">
                <i class="bi bi-chat-heart text-danger me-1"></i> 3. Deixe seu comentário ou sugestão (opcional):
              </label>
              <textarea
                class="form-control rounded-3"
                rows="2"
                placeholder="Ex: O pastel estava bem recheado e quentinho! A entrega foi super rápida."
                v-model="ratingForm.review_comment"
                maxlength="500"
              ></textarea>
            </div>

            <!-- OPÇÕES DE PRIVACIDADE E NOVO DESIGN COMPACTO DOS BOTÕES DE ASSINATURA -->
            <div class="p-2.5 rounded-3 bg-white border mb-3">
              <div class="form-check form-switch mb-2">
                <input
                  class="form-check-input cursor-pointer"
                  type="checkbox"
                  id="allowPublicReviewSwitch"
                  v-model="ratingForm.allow_public_review"
                >
                <label class="form-check-label small fw-bold text-dark cursor-pointer" for="allowPublicReviewSwitch">
                  <i class="bi bi-eye-fill text-primary me-1"></i> Permitir exibir minha avaliação no cardápio
                </label>
                <small class="text-muted d-block" style="font-size: 0.72rem;">
                  Ajuda outros clientes a escolherem os melhores lanches da casa.
                </small>
              </div>

              <!-- NOVO CONTROLE COMPACTO EM FORMATO DE PÍLULA SEGMENTADA -->
              <div v-if="ratingForm.allow_public_review" class="pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <span class="small fw-bold text-dark d-block" style="font-size: 0.78rem;">Assinar avaliação como:</span>
                  <small class="text-muted" style="font-size: 0.7rem;">Visível no cardápio junto ao seu elogio</small>
                </div>

                <div class="bg-light p-1 rounded-pill border d-inline-flex gap-1 align-items-center shadow-sm">
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill px-2.5 py-1 fw-semibold transition border-0', ratingForm.review_show_name ? 'bg-primary text-white shadow-sm' : 'text-dark bg-transparent']"
                    style="font-size: 0.78rem;"
                    @click="ratingForm.review_show_name = true"
                  >
                    <i class="bi bi-person-fill me-1"></i> {{ order.customer_name ? order.customer_name.split(' ')[0] : 'Meu Nome' }}
                  </button>
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill px-2.5 py-1 fw-semibold transition border-0', !ratingForm.review_show_name ? 'bg-dark text-white shadow-sm' : 'text-dark bg-transparent']"
                    style="font-size: 0.78rem;"
                    @click="ratingForm.review_show_name = false"
                  >
                    <i class="bi bi-incognito me-1"></i> Anônimo
                  </button>
                </div>
              </div>
            </div>

            <button
              class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 transition"
              @click="submitCustomerConfirmationAndReview"
              :disabled="submittingReview"
            >
              <span v-if="submittingReview" class="spinner-border spinner-border-sm"></span>
              <template v-else>
                <i class="bi bi-check2-circle fs-5"></i>
                <span>Confirmar Recebimento & Enviar Avaliação</span>
              </template>
            </button>
          </div>
        </div>

        <!-- Caso 2: Já avaliou -->
        <div v-else class="text-center py-2">
          <div class="badge bg-success bg-opacity-10 text-success p-2 rounded-pill px-3 fw-bold mb-2">
            <i class="bi bi-patch-check-fill me-1"></i> Recebimento Confirmado & Avaliado com Sucesso!
          </div>
          <div class="d-flex justify-content-center gap-4 my-2">
            <div>
              <small class="text-muted d-block">Lanche:</small>
              <div class="text-warning fs-5">
                <span v-for="i in (order.food_rating || 5)" :key="'rf-' + i">★</span>
              </div>
            </div>
            <div class="vr"></div>
            <div>
              <small class="text-muted d-block">{{ isPickupOrder ? 'Atendimento:' : 'Entrega:' }}</small>
              <div class="text-warning fs-5">
                <span v-for="i in (order.delivery_rating || 5)" :key="'rd-' + i">★</span>
              </div>
            </div>
          </div>
          <p v-if="order.review_comment" class="fst-italic text-dark small bg-light p-2 rounded-3 mb-2 border">
            "{{ order.review_comment }}"
          </p>
          <small class="text-muted">A equipe AKY Lanches agradece imensamente a sua preferência e avaliação!</small>
        </div>

      </div>

      <!-- ======================================================== -->
      <!-- RESUMO COMPLETO DOS ITENS, PAGAMENTO E SUPORTE           -->
      <!-- ======================================================== -->
      <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm mt-2">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
          <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
            <i class="bi bi-bag-check-fill text-danger"></i>
            <span>Itens Solicitados</span>
          </h6>
          <span class="badge bg-light text-dark border rounded-pill">
            {{ order.items ? order.items.length : 0 }} {{ (order.items && order.items.length === 1) ? 'item' : 'itens' }}
          </span>
        </div>

        <div class="small">
          <div v-for="item in order.items" :key="item.id" class="d-flex justify-content-between py-1.5 border-bottom">
            <span><strong>{{ item.quantity }}x</strong> {{ item.product_name_snapshot }}</span>
            <span class="fw-semibold text-dark">R$ {{ Number(item.subtotal).toFixed(2).replace('.', ',') }}</span>
          </div>

          <div class="d-flex justify-content-between pt-2.5 mt-1 fw-bold fs-6 text-dark">
            <span>Total Geral:</span>
            <span class="text-aky-primary fs-5">R$ {{ Number(order.total).toFixed(2).replace('.', ',') }}</span>
          </div>
        </div>

        <!-- Forma de Pagamento e Endereço -->
        <div class="row g-2 mt-2 pt-2 border-top small text-muted">
          <div class="col-12 col-sm-6">
            <div class="p-2 bg-light rounded-3">
              <span class="d-block fw-bold text-dark mb-0.5">
                <i class="bi bi-wallet2 text-primary me-1"></i> Forma de Pagamento:
              </span>
              <span class="text-dark">{{ paymentMethodDescription }}</span>
            </div>
          </div>

          <div class="col-12 col-sm-6">
            <div class="p-2 bg-light rounded-3">
              <span class="d-block fw-bold text-dark mb-0.5">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Destino:
              </span>
              <span class="text-dark">{{ order.delivery_address }}</span>
              <span v-if="order.delivery_complement" class="text-muted d-block">Compl: {{ order.delivery_complement }}</span>
            </div>
          </div>
        </div>

        <!-- Botão WhatsApp de Dúvidas sobre o Pedido -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-2 border-top">
          <small class="text-muted">
            <i class="bi bi-clock me-1"></i> Feito em: {{ formatDate(order.created_at) }}
          </small>

          <button
            type="button"
            class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm transition"
            @click="handleWhatsAppSupport"
          >
            <i class="bi bi-whatsapp fs-6"></i>
            <span>Tirar dúvidas sobre este pedido</span>
          </button>
        </div>

      </div>

    </div>

    <!-- MODAL DE MAPA AMPLIADO DA AKY LANCHES NO RASTREIO -->
    <div v-if="showMapModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7); z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-dark text-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-shop fs-5"></i>
              </div>
              <div>
                <h6 class="modal-title fw-bold mb-0 text-white">Local de Retirada • AKY Lanches</h6>
                <small class="text-white-50" style="font-size: 0.72rem;">{{ currentStoreAddress }}</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showMapModal = false"></button>
          </div>

          <div class="modal-body p-0" style="height: 380px; background: #e9ecef;">
            <iframe
              width="100%"
              height="100%"
              frameborder="0"
              scrolling="no"
              marginheight="0"
              marginwidth="0"
              :src="'https://maps.google.com/maps?q=' + encodeURIComponent(currentStoreAddress) + '&t=&z=17&ie=UTF8&iwloc=&output=embed'"
              style="border:0;"
              loading="lazy"
            ></iframe>
          </div>

          <div class="modal-footer border-0 bg-light p-3 d-flex flex-column flex-sm-row justify-content-between gap-2">
            <span class="small text-muted d-flex align-items-center gap-1">
              <i class="bi bi-geo-alt-fill text-danger"></i> {{ currentStoreAddress }}
            </span>
            <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end">
              <a
                :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(currentStoreAddress)"
                target="_blank"
                class="btn btn-primary rounded-pill btn-sm px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm"
              >
                <i class="bi bi-compass-fill"></i>
                <span>Abrir no Google Maps / GPS</span>
              </a>
              <button type="button" class="btn btn-secondary rounded-pill btn-sm px-3" @click="showMapModal = false">
                Fechar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { showSuccess, showError } from '../utils/swal';

export default {
  name: 'OrderTracker',
  props: {
    initialCode: {
      type: String,
      default: '',
    },
    storeInfo: {
      type: Object,
      default: null,
    },
    storeWhatsapp: {
      type: String,
      default: '',
    },
    whatsappMessages: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      orderNumber: this.initialCode,
      order: null,
      loading: false,
      error: null,
      showMapModal: false,
      submittingReview: false,
      copiedCode: false,
      recentOrders: [],
      pollingTimer: null,
      visibilityHandler: null,
      loadedStoreWhatsapp: '',
      doubtTemplate: null,
      ratingForm: {
        food_rating: 5,
        delivery_rating: 5,
        review_comment: '',
        allow_public_review: true,
        review_show_name: true,
      },
    };
  },
  computed: {
    isPickupOrder() {
      return this.order && this.order.delivery_address && this.order.delivery_address.includes('RETIRADA');
    },
    isActiveOrder() {
      return this.order && this.order.status !== 'concluido' && this.order.status !== 'cancelado';
    },
    currentStoreAddress() {
      if (this.storeInfo && this.storeInfo.address && this.storeInfo.address.trim()) {
        return this.storeInfo.address.trim();
      }
      return 'Rua Principal, Centro - Teixeira - PB';
    },
    currentStepIndex() {
      if (!this.order) return 1;
      switch (this.order.status) {
        case 'recebido': return 1;
        case 'em_preparacao': return 2;
        case 'saiu_para_entrega': return 3;
        case 'concluido': return 4;
        default: return 1;
      }
    },
    stepperProgressPercent() {
      switch (this.currentStepIndex) {
        case 1: return 0;
        case 2: return 33;
        case 3: return 66;
        case 4: return 100;
        default: return 0;
      }
    },
    paymentMethodDescription() {
      if (!this.order) return '';
      const method = this.order.payment_method;
      if (method === 'pix') return 'PIX (Chave da Lanchonete)';
      if (method === 'cartao') return 'Cartão de Débito / Crédito na Entrega';
      if (method === 'dinheiro') {
        if (this.order.needs_change && this.order.change_for) {
          return `Dinheiro (Troco para R$ ${Number(this.order.change_for).toFixed(2).replace('.', ',')})`;
        }
        return 'Dinheiro (Sem troco)';
      }
      return method || 'Não especificado';
    },
    effectiveStoreWhatsapp() {
      if (this.loadedStoreWhatsapp && this.loadedStoreWhatsapp.trim()) {
        return this.loadedStoreWhatsapp.trim();
      }
      if (this.storeWhatsapp && this.storeWhatsapp.trim()) {
        return this.storeWhatsapp.trim();
      }
      if (this.storeInfo && this.storeInfo.whatsapp && this.storeInfo.whatsapp.trim()) {
        return this.storeInfo.whatsapp.trim();
      }
      return '';
    },
  },
  watch: {
    initialCode(val) {
      if (val) {
        this.orderNumber = val;
        this.searchOrder(true);
      }
    },
  },
  mounted() {
    this.loadRecentOrders();
    if (this.orderNumber) {
      this.searchOrder(true);
    }
    this.startAutoPolling();
  },
  beforeUnmount() {
    this.stopAutoPolling();
  },
  methods: {
    isPhoneSuspect(phone) {
      if (!phone) return true;
      const clean = phone.replace(/\D/g, '');
      if (clean.length < 10) return true;
      if (clean.includes('99999999') || clean.includes('00000000') || phone.includes('(99)')) return true;
      return false;
    },
    handleWhatsAppSupport(e) {
      if (e) e.preventDefault();
      const rawPhone = this.effectiveStoreWhatsapp;
      if (this.isPhoneSuspect(rawPhone)) {
        showError(
          'Atendimento WhatsApp Indisponível',
          'O número de WhatsApp da lanchonete ainda não foi configurado ou é inválido. Por favor, compareça ao balcão ou consulte nosso endereço.'
        );
        return;
      }
      const digits = rawPhone.replace(/\D/g, '');
      const phone = digits.startsWith('55') ? digits : ('55' + digits);
      const order = this.order;
      const orderCode = order ? order.order_number : '';

      // Modelo de mensagem editável de dúvidas (definido em AdminSettings / API)
      let template = this.doubtTemplate;
      if (!template && Array.isArray(this.whatsappMessages)) {
        const found = this.whatsappMessages.find(m => m.trigger === 'duvida_pedido' && m.is_active);
        if (found) {
          template = found.text;
        }
      }

      let text = '';
      if (template && order) {
        const trackingUrl = `${window.location.origin}/?rastreio=${order.order_number}`;
        const totalFormatted = Number(order.total || 0).toFixed(2).replace('.', ',');
        const statusText = this.getStatusLabel(order.status);
        const addressText = order.delivery_address || 'Retirada no Balcão';
        const paymentText = this.paymentMethodDescription || (order.payment_method || 'A combinar');

        text = template
          .replace(/\{codigo\}/g, order.order_number || '')
          .replace(/\{cliente\}/g, order.customer_name || 'Cliente')
          .replace(/\{status\}/g, statusText)
          .replace(/\{total\}/g, totalFormatted)
          .replace(/\{pagamento\}/g, paymentText)
          .replace(/\{endereco\}/g, addressText)
          .replace(/\{link_rastreio\}/g, trackingUrl);
      } else {
        text = `Olá, AKY Lanches! 👋🍔\n\nGostaria de tirar uma dúvida sobre o meu pedido *#${orderCode}*. Poderiam me ajudar por favor?`;
      }

      const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
      window.open(url, '_blank');
    },
    resetSearch() {
      this.order = null;
      this.orderNumber = '';
      this.error = null;
    },
    async searchOrder(manual = true) {
      if (!this.orderNumber || !this.orderNumber.trim()) return;

      if (manual) {
        this.loading = true;
      }
      this.error = null;

      try {
        const res = await axios.get(`/api/orders/${this.orderNumber.trim()}`);
        this.order = res.data.order;
        if (res.data.store_whatsapp) {
          this.loadedStoreWhatsapp = res.data.store_whatsapp;
        }
        if (res.data.doubt_whatsapp_template) {
          this.doubtTemplate = res.data.doubt_whatsapp_template;
        }
        this.saveToRecentOrders(this.order);
      } catch (err) {
        if (manual) {
          this.error = 'Pedido não encontrado. Verifique o código e tente novamente.';
          this.order = null;
        }
      } finally {
        if (manual) {
          this.loading = false;
        }
      }
    },
    selectRecentOrder(code) {
      this.orderNumber = code;
      this.searchOrder(true);
    },
    loadRecentOrders() {
      try {
        const saved = localStorage.getItem('aky_recent_orders');
        if (saved) {
          this.recentOrders = JSON.parse(saved);
        }
      } catch (e) {
        this.recentOrders = [];
      }
    },
    saveToRecentOrders(order) {
      if (!order || !order.order_number) return;
      try {
        let list = this.recentOrders || [];
        list = list.filter(item => item.code !== order.order_number);
        list.unshift({
          code: order.order_number,
          status: order.status,
          date: this.formatShortDate(order.created_at),
          total: order.total,
        });
        list = list.slice(0, 4); // mantém até 4
        this.recentOrders = list;
        localStorage.setItem('aky_recent_orders', JSON.stringify(list));
      } catch (e) {
        console.error(e);
      }
    },
    clearRecentOrders() {
      this.recentOrders = [];
      localStorage.removeItem('aky_recent_orders');
    },
    startAutoPolling() {
      this.pollingTimer = setInterval(() => {
        if (document.visibilityState === 'visible' && this.isActiveOrder && this.orderNumber) {
          this.searchOrder(false);
        }
      }, 15000);

      this.visibilityHandler = () => {
        if (document.visibilityState === 'visible' && this.isActiveOrder && this.orderNumber) {
          this.searchOrder(false);
        }
      };
      document.addEventListener('visibilitychange', this.visibilityHandler);
    },
    stopAutoPolling() {
      if (this.pollingTimer) {
        clearInterval(this.pollingTimer);
        this.pollingTimer = null;
      }
      if (this.visibilityHandler) {
        document.removeEventListener('visibilitychange', this.visibilityHandler);
        this.visibilityHandler = null;
      }
    },
    async copyOrderCode() {
      if (!this.order) return;
      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(this.order.order_number);
        } else {
          const el = document.createElement('textarea');
          el.value = this.order.order_number;
          document.body.appendChild(el);
          el.select();
          document.execCommand('copy');
          document.body.removeChild(el);
        }
        this.copiedCode = true;
        setTimeout(() => {
          this.copiedCode = false;
        }, 2000);
      } catch (e) {
        console.error(e);
      }
    },
    getStepClass(stepIndex) {
      if (this.currentStepIndex > stepIndex) return 'step-completed';
      if (this.currentStepIndex === stepIndex) return 'step-active';
      return 'step-pending';
    },
    getStatusLabel(status) {
      switch (status) {
        case 'recebido': return 'Recebido';
        case 'em_preparacao': return 'Em Preparo';
        case 'saiu_para_entrega': return 'A Caminho';
        case 'concluido': return 'Concluído';
        case 'cancelado': return 'Cancelado';
        default: return status;
      }
    },
    getStatusBadgeClass(status) {
      switch (status) {
        case 'recebido': return 'bg-warning text-dark';
        case 'em_preparacao': return 'bg-primary text-white';
        case 'saiu_para_entrega': return 'bg-info text-dark';
        case 'concluido': return 'bg-success text-white';
        case 'cancelado': return 'bg-danger text-white';
        default: return 'bg-secondary text-white';
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('pt-BR') + ' às ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },
    formatShortDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },
    getRatingLabel(stars) {
      switch (stars) {
        case 5: return 'Excelente! ⭐⭐⭐⭐⭐';
        case 4: return 'Muito Bom! ⭐⭐⭐⭐';
        case 3: return 'Bom ⭐⭐⭐';
        case 2: return 'Regular ⭐⭐';
        case 1: return 'Ruim ⭐';
        default: return '';
      }
    },
    async submitCustomerConfirmationAndReview() {
      if (!this.order) return;
      this.submittingReview = true;
      try {
        const res = await axios.post(`/api/orders/${this.order.order_number}/confirm-and-rate`, {
          confirm_delivery: true,
          food_rating: this.ratingForm.food_rating,
          delivery_rating: this.ratingForm.delivery_rating,
          review_comment: this.ratingForm.review_comment,
          allow_public_review: this.ratingForm.allow_public_review,
          review_show_name: this.ratingForm.review_show_name,
        });
        this.order = res.data.order;
        this.saveToRecentOrders(this.order);
        await showSuccess('Avaliação Registrada!', res.data.message || 'Recebimento e avaliação registrados com sucesso. Muito obrigado!');
      } catch (err) {
        showError('Aviso', err.response?.data?.message || 'Erro ao registrar avaliação.');
      } finally {
        this.submittingReview = false;
      }
    },
  },
};
</script>

<style scoped>
.transition-star {
  transition: transform 0.15s ease, color 0.15s ease;
  line-height: 1;
}
.transition-star:hover {
  transform: scale(1.25);
}

.hover-pill:hover {
  background-color: #f8f9fa !important;
  border-color: #dee2e6 !important;
}

/* Indicador pulsante Ao Vivo */
.live-dot {
  width: 8px;
  height: 8px;
  background-color: #198754;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 0 0 rgba(25, 135, 84, 0.4);
  animation: pulseLive 1.8s infinite;
}

@keyframes pulseLive {
  0% {
    box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(25, 135, 84, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
  }
}

.spin-icon {
  animation: spinAnim 0.8s linear infinite;
}

@keyframes spinAnim {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ========================================= */
/* STEPPER / TIMELINE VISUAL DE 4 ETAPAS     */
/* ========================================= */
.stepper-container {
  position: relative;
  padding: 10px 4px 6px;
}

.stepper-progress-track {
  position: absolute;
  top: 26px;
  left: 12%;
  right: 12%;
  height: 4px;
  background-color: #e9ecef;
  z-index: 1;
}

.stepper-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #e50914, #ff8c00, #198754);
  transition: width 0.4s ease;
}

.stepper-steps {
  position: relative;
  display: flex;
  justify-content: space-between;
  z-index: 2;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 25%;
  text-align: center;
}

.step-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  font-weight: bold;
  background-color: #f8f9fa;
  color: #adb5bd;
  border: 2px solid #dee2e6;
  transition: all 0.3s ease;
}

.step-label {
  margin-top: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6c757d;
  transition: color 0.3s ease;
}

/* Estado Completo */
.step-item.step-completed .step-circle {
  background-color: #198754;
  color: #ffffff;
  border-color: #198754;
}

.step-item.step-completed .step-label {
  color: #198754;
}

/* Estado Ativo (Fase Atual) */
.step-item.step-active .step-circle {
  background-color: #e50914;
  color: #ffffff;
  border-color: #e50914;
  box-shadow: 0 0 0 4px rgba(229, 9, 20, 0.2) !important;
  transform: scale(1.1);
}

.step-item.step-active .step-label {
  color: #e50914;
  font-weight: 700;
}

/* ========================================= */
/* ANIMAÇÃO CSS DA MOTO ANDANDO PELA RUA      */
/* ========================================= */
.street-container {
  height: 80px;
  background: #2b2d42;
  position: relative;
  border: 2px solid #3a0ca3;
}

.asphalt {
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 4px;
  transform: translateY(-50%);
}

.road-lines {
  width: 200%;
  height: 4px;
  background: repeating-linear-gradient(
    90deg,
    #ffffff,
    #ffffff 20px,
    transparent 20px,
    transparent 40px
  );
  animation: moveRoad 1.2s linear infinite;
}

@keyframes moveRoad {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-50%);
  }
}

.moto-vehicle {
  position: absolute;
  top: 50%;
  left: 20%;
  transform: translateY(-55%);
  animation: motoBounce 0.4s ease-in-out infinite alternate, motoDrive 4s ease-in-out infinite alternate;
}

.moto-wrapper {
  position: relative;
  display: inline-block;
}

.moto-icon {
  font-size: 2.2rem;
  display: inline-block;
  transform: scaleX(-1);
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.4));
}

.destination-house {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  background: #e63946;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 10px rgba(230, 57, 70, 0.8);
}

@keyframes motoBounce {
  from {
    transform: translateY(-55%);
  }
  to {
    transform: translateY(-62%);
  }
}

@keyframes motoDrive {
  0% {
    left: 15%;
  }
  50% {
    left: 45%;
  }
  100% {
    left: 65%;
  }
}
</style>

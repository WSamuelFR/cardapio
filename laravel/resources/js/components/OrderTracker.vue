<template>
  <div class="card border-0 shadow-sm rounded-4 p-4 my-4 bg-white">
    
    <!-- Cabeçalho -->
    <div class="d-flex align-items-center mb-3">
      <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-2 text-aky-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
        <i class="bi bi-clock-history fs-4"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0 text-dark">Acompanhar Pedido</h5>
        <small class="text-muted">Consulte o status em tempo real</small>
      </div>
    </div>

    <!-- Barra de Busca Compacta (Apenas o Ícone de Lupa no Botão) -->
    <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden border">
      <input
        type="text"
        class="form-control text-uppercase font-monospace border-0 shadow-none ps-3"
        placeholder="Digite o código (ex: AKY-20261029-ABCD)"
        v-model="orderNumber"
        @keyup.enter="searchOrder"
      >
      <button class="btn btn-aky-primary px-3 px-sm-4 border-0 d-flex align-items-center justify-content-center" @click="searchOrder" :disabled="loading" title="Consultar">
        <span v-if="loading" class="spinner-border spinner-border-sm"></span>
        <i v-else class="bi bi-search fs-5"></i>
      </button>
    </div>

    <div v-if="error" class="alert alert-warning py-2 small rounded-3">
      <i class="bi bi-info-circle me-1"></i> {{ error }}
    </div>

    <!-- Resultado do Pedido -->
    <div v-if="order" class="card bg-light border-0 rounded-4 p-3 mt-2 shadow-sm">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
          <span class="text-muted small d-block">Código do Pedido:</span>
          <span class="fw-bold fs-5 font-monospace text-aky-primary">{{ order.order_number }}</span>
        </div>
        <div class="text-end">
          <span class="text-muted small d-block">Horário do Pedido:</span>
          <span class="small text-dark fw-semibold">{{ formatDate(order.created_at) }}</span>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- EXIBIÇÃO EXCLUSIVA DO STATUS ATUAL (APENAS A INFORMAÇÃO ATUAL) -->
      <!-- ======================================================== -->

      <!-- 1. STATUS: RECEBIDO -->
      <div v-if="order.status === 'recebido'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-warning">
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
      <div v-else-if="order.status === 'em_preparacao'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-primary">
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

      <!-- 3. STATUS: SAIU PARA ENTREGA (COM IDENTIFICAÇÃO DO MOTOBOY E ANIMAÇÃO DA MOTO OU AVISO DE RETIRADA) -->
      <div v-else-if="order.status === 'saiu_para_entrega'" class="delivery-track-box my-3 p-3 bg-white rounded-4 shadow-sm border border-info border-start border-4 border-info">
        
        <template v-if="order.delivery_address && order.delivery_address.includes('RETIRADA')">
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
      <div v-else-if="order.status === 'concluido'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-success">
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

            <!-- OPÇÕES DE PRIVACIDADE E EXIBIÇÃO DA AVALIAÇÃO -->
            <div class="p-2.5 rounded-3 bg-white border mb-3">
              <div class="form-check form-switch mb-2">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="allowPublicReviewSwitch"
                  v-model="ratingForm.allow_public_review"
                >
                <label class="form-check-label small fw-bold text-dark" for="allowPublicReviewSwitch">
                  <i class="bi bi-eye-fill text-primary me-1"></i> Permitir exibir minha avaliação no cardápio
                </label>
                <small class="text-muted d-block" style="font-size: 0.72rem;">
                  Ajuda outros clientes a escolherem os melhores lanches da casa.
                </small>
              </div>

              <!-- Se permitir exibição, escolhe se quer mostrar nome ou anônimo -->
              <div v-if="ratingForm.allow_public_review" class="pt-2 border-top">
                <label class="form-label small fw-bold text-dark mb-1">Como você deseja assinar sua avaliação?</label>
                <div class="d-flex gap-2">
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold flex-fill border transition', ratingForm.review_show_name ? 'btn-primary text-white shadow-sm' : 'btn-light text-dark']"
                    @click="ratingForm.review_show_name = true"
                  >
                    <i class="bi bi-person-fill me-1"></i> Com meu nome ({{ order.customer_name ? order.customer_name.split(' ')[0] : 'Meu Nome' }})
                  </button>
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill px-3 py-1 fw-semibold flex-fill border transition', !ratingForm.review_show_name ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
                    @click="ratingForm.review_show_name = false"
                  >
                    <i class="bi bi-incognito me-1"></i> Avaliar como Anônimo
                  </button>
                </div>
              </div>
            </div>

            <button
              class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
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

      <!-- 5. STATUS: CANCELADO -->
      <div v-else-if="order.status === 'cancelado'" class="card border-0 shadow-sm rounded-4 p-3 my-3 bg-white border-start border-4 border-danger">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-danger bg-opacity-25 text-danger p-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bi bi-x-circle-fill fs-2 text-danger"></i>
          </div>
          <div>
            <span class="badge bg-danger rounded-pill mb-1 fw-bold">Status: Cancelado</span>
            <h6 class="fw-bold text-dark mb-1">Este pedido foi cancelado</h6>
            <p class="text-muted small mb-0">Se precisar de qualquer esclarecimento, fale com nossa equipe de atendimento.</p>
          </div>
        </div>
      </div>

      <!-- Detalhes dos Itens do Pedido -->
      <div class="bg-white p-3 rounded-4 shadow-sm mt-3">
        <h6 class="fw-bold text-dark mb-2">Itens Solicitados</h6>
        <div class="small">
          <div v-for="item in order.items" :key="item.id" class="d-flex justify-content-between py-1 border-bottom">
            <span>{{ item.quantity }}x {{ item.product_name_snapshot }}</span>
            <span class="fw-semibold">R$ {{ Number(item.subtotal).toFixed(2).replace('.', ',') }}</span>
          </div>
          <div class="d-flex justify-content-between pt-2 mt-1 fw-bold fs-6 text-dark">
            <span>Total:</span>
            <span class="text-aky-primary">R$ {{ Number(order.total).toFixed(2).replace('.', ',') }}</span>
          </div>
        </div>

        <div class="mt-3 pt-2 border-top small text-muted">
          <div><i class="bi bi-geo-alt me-1 text-danger"></i> <strong>Entrega:</strong> {{ order.delivery_address }} <span v-if="order.delivery_complement">({{ order.delivery_complement }})</span></div>
          <div v-if="order.delivery_reference" class="mt-1"><i class="bi bi-signpost me-1"></i> <strong>Referência:</strong> {{ order.delivery_reference }}</div>
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
                <h6 class="modal-title fw-bold mb-0">Local de Retirada • AKY Lanches</h6>
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
  props: {
    initialCode: {
      type: String,
      default: '',
    },
    storeInfo: {
      type: Object,
      default: null,
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
    currentStoreAddress() {
      if (this.storeInfo && this.storeInfo.address && this.storeInfo.address.trim()) {
        return this.storeInfo.address.trim();
      }
      return 'Rua Principal, Centro - Teixeira - PB';
    },
  },
  watch: {
    initialCode(val) {
      if (val) {
        this.orderNumber = val;
        this.searchOrder();
      }
    },
  },
  mounted() {
    if (this.orderNumber) {
      this.searchOrder();
    }
  },
  methods: {
    async searchOrder() {
      if (!this.orderNumber.trim()) return;

      this.loading = true;
      this.error = null;
      this.order = null;

      try {
        const res = await axios.get(`/api/orders/${this.orderNumber.trim()}`);
        this.order = res.data.order;
      } catch (err) {
        this.error = 'Pedido não encontrado. Verifique o código e tente novamente.';
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('pt-BR') + ' às ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
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

<template>
  <div v-if="show" class="modal fade show d-block cart-modal-backdrop" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1050;">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg cart-modal-dialog">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        
        <!-- Header Estilizado -->
        <div class="modal-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-warning bg-opacity-25 p-2 me-2 text-dark d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
              <i class="bi bi-cart3 fs-5 text-dark"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark mb-0 fs-6 fs-md-5">Seu Carrinho</h5>
              <small class="text-muted" style="font-size: 0.75rem;">{{ cart.length }} {{ cart.length === 1 ? 'item adicionado' : 'itens adicionados' }}</small>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" @click="$emit('close')"></button>
        </div>

        <!-- Body com Scroll Fluido -->
        <div class="modal-body p-3 p-md-4 bg-light">
          
          <!-- Estado de Carrinho Vazio -->
          <div v-if="cart.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
            <div class="mb-3">
              <i class="bi bi-cart-x fs-1 text-muted opacity-50"></i>
            </div>
            <h6 class="text-dark fw-bold mb-1">Seu carrinho está vazio</h6>
            <p class="text-secondary small mb-4">Que tal escolher pastéis quentinhos ou sucos gelados?</p>
            <button class="btn btn-aky-primary rounded-pill px-4 fw-semibold" @click="$emit('close')">
              <i class="bi bi-book me-1"></i> Explorar Cardápio
            </button>
          </div>

          <!-- Conteúdo Mobile-First -->
          <div v-else>
            
            <!-- LISTA DE ITENS EM FORMATO DE CARDS MOBILE-FRIENDLY (SEM TABELA) -->
            <div class="d-flex flex-column gap-2 mb-3">
              <div
                v-for="item in cart"
                :key="'cart-item-' + item.id"
                class="card border-0 shadow-sm rounded-3 p-3 bg-white"
              >
                <div class="d-flex align-items-center gap-3">
                  
                  <!-- Foto do Produto -->
                  <div class="rounded-3 overflow-hidden bg-light border flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <img v-if="item.photo_path" :src="item.photo_path" :alt="item.name" class="w-100 h-100 object-fit-cover">
                    <i v-else class="bi bi-bag-heart text-muted fs-4"></i>
                  </div>

                  <!-- Detalhes do Produto -->
                  <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-start">
                      <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 180px;">{{ item.name }}</h6>
                      <button class="btn btn-link text-danger p-0 ms-2" title="Remover" @click="removeItem(item.id)">
                        <i class="bi bi-trash fs-5"></i>
                      </button>
                    </div>

                    <div class="d-flex align-items-center gap-2 my-1">
                      <span class="text-muted small">R$ {{ formatPrice(itemPrice(item)) }} cada</span>
                      <span v-if="item.is_promotion" class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">Promoção</span>
                    </div>

                    <div v-if="item.notes" class="small text-danger fst-italic mb-1" style="font-size: 0.72rem;">
                      <i class="bi bi-chat-left-dots me-1"></i> Obs: {{ item.notes }}
                    </div>

                    <!-- Controles de Quantidade e Subtotal Móvel -->
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                      <!-- Seletor de Quantidade Ergonômico -->
                      <div class="d-flex align-items-center bg-light rounded-pill p-1 border">
                        <button class="btn btn-sm btn-white rounded-circle shadow-none p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" @click="decreaseQty(item)" :disabled="loading">
                          <i class="bi bi-dash fs-6"></i>
                        </button>
                        <span class="px-3 fw-bold text-dark small">{{ item.quantity }}</span>
                        <button class="btn btn-sm btn-white rounded-circle shadow-none p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" @click="increaseQty(item)" :disabled="loading">
                          <i class="bi bi-plus fs-6"></i>
                        </button>
                      </div>

                      <!-- Subtotal do Item -->
                      <div class="text-end">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Subtotal</small>
                        <span class="fw-bold text-dark fs-6">R$ {{ formatPrice(itemSubtotal(item)) }}</span>
                      </div>
                    </div>

                  </div>

                </div>
              </div>
            </div>

            <!-- Card de Resumo de Valores -->
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small">Subtotal dos itens</span>
                <span class="fw-semibold text-dark">R$ {{ formatPrice(cartTotal) }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Taxa de entrega</span>
                <span class="text-success small fw-semibold">A combinar / Na entrega</span>
              </div>
              <hr class="my-2 opacity-25">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-6">Total do Pedido:</span>
                <span class="fw-bold text-aky-primary fs-4">R$ {{ formatPrice(cartTotal) }}</span>
              </div>
            </div>

            <!-- Formulário de Entrega Otimizado para Teclado de Celular -->
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-2">
              <div class="d-flex align-items-center mb-3">
                <i class="bi bi-geo-alt-fill text-aky-primary fs-5 me-2"></i>
                <h6 class="fw-bold text-dark mb-0">Endereço de Entrega</h6>
              </div>

              <div class="row g-2">
                <!-- Nome com máscara em caixa alta sem símbolos -->
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold text-muted mb-1">Seu Nome Completo *</label>
                  <input
                    ref="nameInput"
                    type="text"
                    class="form-control rounded-3"
                    :value="customer.name"
                    @input="handleNameInput"
                    @change="handleNameInput"
                    @blur="handleNameInput"
                    placeholder="DIGITE SEU NOME"
                    autocomplete="off"
                    required
                  >
                  <small class="text-muted" style="font-size: 0.68rem;">Apenas letras, convertido para maiúsculas</small>
                </div>

                <!-- Telefone com máscara -->
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold text-muted mb-1">Telefone (WhatsApp) *</label>
                  <input
                    ref="phoneInput"
                    type="tel"
                    class="form-control rounded-3"
                    :value="customer.phone"
                    @input="handlePhoneInput"
                    @change="handlePhoneInput"
                    @blur="handlePhoneInput"
                    placeholder="(99) 99999-9999"
                    autocomplete="off"
                    required
                  >
                </div>

                <!-- CEP com máscara e verificação em Teixeira - PB -->
                <div class="col-12 col-md-6">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-muted mb-0">CEP de Entrega *</label>
                    <span v-if="loadingCep" class="text-primary small" style="font-size: 0.7rem;">
                      <span class="spinner-border spinner-border-sm me-1"></span> Verificando...
                    </span>
                  </div>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-pin-map"></i>
                    </span>
                    <input
                      ref="cepInput"
                      type="text"
                      class="form-control border-start-0 ps-1 rounded-end-3"
                      :value="customer.cep"
                      @input="handleCepInput"
                      @change="handleCepInput"
                      @blur="handleCepInput"
                      placeholder="58735-000"
                      maxlength="9"
                      autocomplete="off"
                      required
                    >
                  </div>

                  <!-- Mensagem de Região Válida ou Inválida -->
                  <div v-if="cepValid === true" class="p-2 rounded-2 bg-success bg-opacity-10 text-success small mt-1.5 d-flex align-items-center gap-1.5">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <span>Entrega disponível em <strong>{{ cepCityInfo }}</strong>!</span>
                  </div>

                  <div v-else-if="cepErrorMessage" class="alert alert-danger border-0 rounded-3 p-2 small mt-1.5 mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0"></i>
                    <div><strong>{{ cepErrorMessage }}</strong></div>
                  </div>
                </div>

                <!-- Endereço Completo -->
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold text-muted mb-1">Rua, Número e Bairro *</label>
                  <input
                    type="text"
                    class="form-control rounded-3"
                    v-model="customer.address"
                    placeholder="Ex: Rua São Sebastião, 120, Centro"
                    autocomplete="off"
                    required
                  >
                </div>

                <div class="col-6">
                  <label class="form-label small fw-semibold text-muted mb-1">Complemento</label>
                  <input
                    type="text"
                    class="form-control rounded-3"
                    v-model="customer.complement"
                    placeholder="Apto, Casa, Bloco"
                    autocomplete="off"
                  >
                </div>

                <div class="col-6">
                  <label class="form-label small fw-semibold text-muted mb-1">Ponto de Referência</label>
                  <input
                    type="text"
                    class="form-control rounded-3"
                    v-model="customer.reference"
                    placeholder="Ex: Próximo à praça"
                    autocomplete="off"
                  >
                </div>

                <div class="col-12 mt-2">
                  <label class="form-label small fw-semibold text-muted mb-1">Observações do Pedido</label>
                  <input
                    type="text"
                    class="form-control rounded-3"
                    v-model="customer.notes"
                    placeholder="Ex: Sem cebola, suco sem açúcar..."
                    autocomplete="off"
                  >
                </div>
              </div>
            </div>

            <!-- Seção de Forma de Pagamento e Troco -->
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-2">
              <div class="d-flex align-items-center mb-3">
                <i class="bi bi-wallet2 text-aky-primary fs-5 me-2"></i>
                <h6 class="fw-bold text-dark mb-0">Forma de Pagamento</h6>
              </div>

              <!-- Opções de Pagamento em Botões/Cards Selecionáveis -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <div
                    :class="['p-2.5 rounded-3 border text-center cursor-pointer transition h-100 d-flex flex-column align-items-center justify-content-center', paymentMethod === 'dinheiro' ? 'border-success bg-success bg-opacity-10 text-success fw-bold shadow-sm' : 'bg-light text-dark']"
                    @click="paymentMethod = 'dinheiro'"
                  >
                    <i class="bi bi-cash-stack fs-4 mb-1"></i>
                    <span class="small">Dinheiro</span>
                  </div>
                </div>

                <div class="col-6">
                  <div
                    :class="['p-2.5 rounded-3 border text-center cursor-pointer transition h-100 d-flex flex-column align-items-center justify-content-center', paymentMethod === 'pix' ? 'border-primary bg-primary bg-opacity-10 text-primary fw-bold shadow-sm' : 'bg-light text-dark']"
                    @click="paymentMethod = 'pix'"
                  >
                    <i class="bi bi-qr-code fs-4 mb-1"></i>
                    <span class="small">Pix (Na Entrega)</span>
                  </div>
                </div>

                <div class="col-6">
                  <div
                    :class="['p-2.5 rounded-3 border text-center cursor-pointer transition h-100 d-flex flex-column align-items-center justify-content-center', paymentMethod === 'cartao_credito' ? 'border-danger bg-danger bg-opacity-10 text-danger fw-bold shadow-sm' : 'bg-light text-dark']"
                    @click="paymentMethod = 'cartao_credito'"
                  >
                    <i class="bi bi-credit-card-2-front fs-4 mb-1"></i>
                    <span class="small">Cartão Crédito</span>
                  </div>
                </div>

                <div class="col-6">
                  <div
                    :class="['p-2.5 rounded-3 border text-center cursor-pointer transition h-100 d-flex flex-column align-items-center justify-content-center', paymentMethod === 'cartao_debito' ? 'border-warning bg-warning bg-opacity-10 text-dark fw-bold shadow-sm' : 'bg-light text-dark']"
                    @click="paymentMethod = 'cartao_debito'"
                  >
                    <i class="bi bi-credit-card fs-4 mb-1"></i>
                    <span class="small">Cartão Débito</span>
                  </div>
                </div>
              </div>

              <!-- Opção de Troco Exclusiva para Pagamento em Dinheiro -->
              <div v-if="paymentMethod === 'dinheiro'" class="p-3 bg-light rounded-3 border transition">
                <label class="form-label small fw-bold text-dark mb-2 d-flex align-items-center">
                  <i class="bi bi-coin text-warning me-1"></i> Vai precisar de troco?
                </label>
                
                <div class="d-flex gap-2 mb-2">
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill flex-fill fw-semibold py-1.5 transition', !needsChange ? 'btn-dark text-white' : 'btn-white border text-dark']"
                    @click="needsChange = false; changeFor = ''"
                  >
                    Não preciso
                  </button>
                  <button
                    type="button"
                    :class="['btn btn-sm rounded-pill flex-fill fw-semibold py-1.5 transition', needsChange ? 'btn-success text-white' : 'btn-white border text-dark']"
                    @click="needsChange = true; if (!changeFor) changeFor = suggestedChange"
                  >
                    Sim, preciso de troco
                  </button>
                </div>

                <!-- Campo de Valor do Troco -->
                <div v-if="needsChange" class="mt-3 pt-2 border-top">
                  <label class="form-label small fw-semibold text-muted mb-1">Troco para quanto?</label>
                  <div class="input-group mb-2">
                    <span class="input-group-text bg-white border-end-0 fw-bold text-muted">R$</span>
                    <input
                      type="number"
                      step="1"
                      class="form-control border-start-0 ps-1"
                      v-model="changeFor"
                      :placeholder="'Ex: ' + suggestedChange"
                      autocomplete="off"
                    >
                  </div>

                  <!-- Sugestões Rápidas de Troco -->
                  <div class="d-flex gap-1.5 overflow-auto pb-1 mb-2">
                    <button
                      v-for="sug in changeSuggestions"
                      :key="'sug-' + sug"
                      type="button"
                      class="btn btn-sm btn-outline-secondary rounded-pill py-0.5 px-2.5 small flex-shrink-0"
                      style="font-size: 0.72rem;"
                      @click="changeFor = sug"
                    >
                      R$ {{ sug }},00
                    </button>
                  </div>

                  <!-- Cálculo e Alerta de Troco em Tempo Real -->
                  <div v-if="calculatedChange > 0" class="p-2 rounded-2 bg-success bg-opacity-10 text-success small fw-semibold d-flex justify-content-between align-items-center">
                    <span>Troco a devolver:</span>
                    <span class="fs-6 fw-bold">R$ {{ formatPrice(calculatedChange) }}</span>
                  </div>
                  <div v-else-if="changeFor && calculatedChange < 0" class="p-2 rounded-2 bg-danger bg-opacity-10 text-danger small">
                    <i class="bi bi-exclamation-triangle me-1"></i> O valor informado deve ser maior que o total do pedido (R$ {{ formatPrice(cartTotal) }}).
                  </div>
                </div>
              </div>

              <!-- Mensagem Informativa para Cartão / Pix -->
              <div v-else-if="paymentMethod === 'cartao_credito' || paymentMethod === 'cartao_debito'" class="p-2 rounded-3 bg-light text-muted small d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-primary fs-5"></i>
                <span style="font-size: 0.74rem;">O entregador levará a máquina de cartão até o seu endereço.</span>
              </div>
              <div v-else-if="paymentMethod === 'pix'" class="p-2 rounded-3 bg-light text-muted small d-flex align-items-center gap-2">
                <i class="bi bi-qr-code-scan text-primary fs-5"></i>
                <span style="font-size: 0.74rem;">O entregador apresentará a chave Pix ou QR Code na entrega.</span>
              </div>

            </div>

            <!-- Mensagem de Erro de Validação -->
            <div v-if="errorMessage" class="alert alert-danger py-2 mt-3 small rounded-3">
              <i class="bi bi-exclamation-circle-fill me-1"></i> {{ errorMessage }}
            </div>

          </div>

        </div>

        <!-- Footer com Botão de Confirmação em Destaque Mobile-First -->
        <div v-if="cart.length > 0" class="modal-footer border-top bg-white p-3 d-flex flex-column flex-sm-row justify-content-between gap-2">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 order-2 order-sm-1" @click="$emit('close')">
            Continuar Comprando
          </button>
          
          <button
            type="button"
            class="btn btn-aky-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center justify-content-center shadow-sm order-1 order-sm-2 flex-grow-1 flex-sm-grow-0"
            :disabled="loading"
            @click="submitOrder"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="bi bi-check2-circle fs-5 me-2"></i>
            <span>Confirmar Pedido (R$ {{ formatPrice(cartTotal) }})</span>
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { maskName, maskPhone, maskCep, onlyDigits, applyMask } from '../utils/masks';

export default {
  props: {
    show: Boolean,
    cart: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      loading: false,
      errorMessage: null,
      paymentMethod: 'dinheiro',
      needsChange: false,
      changeFor: '',
      loadingCep: false,
      cepValid: null,
      cepCityInfo: '',
      cepErrorMessage: null,
      customer: {
        name: '',
        phone: '',
        cep: '',
        address: '',
        complement: '',
        reference: '',
        notes: '',
      },
    };
  },
  computed: {
    cartTotal() {
      return this.cart.reduce((sum, item) => sum + this.itemSubtotal(item), 0);
    },
    suggestedChange() {
      const total = this.cartTotal;
      if (total <= 50) return 50;
      if (total <= 100) return 100;
      return Math.ceil((total + 10) / 50) * 50;
    },
    changeSuggestions() {
      const total = this.cartTotal;
      const list = [];
      [50, 100, 150, 200].forEach(val => {
        if (val > total) list.push(val);
      });
      if (list.length === 0) list.push(Math.ceil((total + 20) / 50) * 50);
      return list;
    },
    calculatedChange() {
      if (!this.needsChange || !this.changeFor) return 0;
      return Number(this.changeFor) - this.cartTotal;
    },
  },
  watch: {
    show(val) {
      if (val) {
        this.loadSavedCustomer();
      }
    },
  },
  mounted() {
    this.loadSavedCustomer();
  },
  methods: {
    handleNameInput(e) {
      applyMask(e, maskName, val => { this.customer.name = val; });
    },
    handlePhoneInput(e) {
      applyMask(e, maskPhone, val => { this.customer.phone = val; });
    },
    handleCepInput(e) {
      applyMask(e, maskCep, val => {
        this.customer.cep = val;
        const digits = onlyDigits(val);
        if (digits.length === 8) {
          this.verifyCep(digits);
        } else {
          this.cepValid = null;
          this.cepErrorMessage = null;
          this.cepCityInfo = '';
        }
      });
    },
    async verifyCep(digits) {
      this.cepErrorMessage = null;

      // Regra prioritária para Teixeira - PB (CEP 58735-000 e faixa 58735-xxx)
      if (digits.startsWith('58735')) {
        this.cepValid = true;
        this.cepCityInfo = 'Teixeira - PB';
        this.loadingCep = false;

        // Tenta buscar o logradouro se houver, mas NUNCA invalida o CEP de Teixeira
        try {
          const res = await axios.get(`https://viacep.com.br/ws/${digits}/json/`);
          if (res.data && !res.data.erro && res.data.logradouro && !this.customer.address) {
            const bairro = res.data.bairro ? `, ${res.data.bairro}` : '';
            this.customer.address = `${res.data.logradouro}${bairro}`;
          }
        } catch (e) {
          // Ignora falhas de rede já que 58735 é Teixeira
        }
        return;
      }

      this.loadingCep = true;
      this.cepValid = null;
      this.cepCityInfo = '';

      try {
        const res = await axios.get(`https://viacep.com.br/ws/${digits}/json/`);
        const data = res.data;
        if (data && !data.erro) {
          const loc = (data.localidade || '').trim().toLowerCase();
          const uf = (data.uf || '').trim().toUpperCase();

          if (loc === 'teixeira' && uf === 'PB') {
            this.cepValid = true;
            this.cepCityInfo = `${data.localidade} - ${data.uf}`;
            if (data.logradouro && !this.customer.address) {
              const bairro = data.bairro ? `, ${data.bairro}` : '';
              this.customer.address = `${data.logradouro}${bairro}`;
            }
          } else {
            this.cepValid = false;
            this.cepErrorMessage = 'desculpe, não entregamos na sua região!';
          }
        } else {
          this.cepValid = false;
          this.cepErrorMessage = 'desculpe, não entregamos na sua região!';
        }
      } catch (err) {
        this.cepValid = false;
        this.cepErrorMessage = 'desculpe, não entregamos na sua região!';
      } finally {
        this.loadingCep = false;
      }
    },
    itemPrice(item) {
      if (!item) return 0;
      const val = (item.effective_price !== undefined && item.effective_price !== null)
        ? item.effective_price
        : item.price;
      return parseFloat(val) || 0;
    },
    itemSubtotal(item) {
      return this.itemPrice(item) * (parseInt(item.quantity) || 1);
    },
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    loadSavedCustomer() {
      const saved = localStorage.getItem('aky_customer_session');
      if (saved) {
        try {
          const data = JSON.parse(saved);
          this.customer.name = maskName(data.name || '');
          this.customer.phone = maskPhone(data.phone || '');
          this.customer.cep = maskCep(data.cep || '');
          this.customer.address = data.address || '';
          this.customer.complement = data.complement || '';
          this.customer.reference = data.reference || '';

          if (this.customer.cep) {
            const digits = onlyDigits(this.customer.cep);
            if (digits.length === 8) {
              this.verifyCep(digits);
            }
          }
        } catch (e) {
          console.error(e);
        }
      }
    },
    saveCustomerToSession() {
      localStorage.setItem('aky_customer_session', JSON.stringify({
        name: this.customer.name,
        phone: this.customer.phone,
        cep: this.customer.cep,
        address: this.customer.address,
        complement: this.customer.complement,
        reference: this.customer.reference,
      }));
    },
    increaseQty(item) {
      this.$emit('update-qty', { id: Number(item.id), qty: Number(item.quantity || 1) + 1 });
    },
    decreaseQty(item) {
      const currentQty = Number(item.quantity || 1);
      if (currentQty > 1) {
        this.$emit('update-qty', { id: Number(item.id), qty: currentQty - 1 });
      } else {
        this.removeItem(Number(item.id));
      }
    },
    removeItem(id) {
      this.$emit('remove-item', Number(id));
    },
    async submitOrder() {
      this.errorMessage = null;

      // Sincroniza diretamente com os elementos do DOM caso tenha havido descompasso
      const nameEl = this.$refs.nameInput || (this.$el ? this.$el.querySelector('input[placeholder="DIGITE SEU NOME"]') : null);
      if (nameEl && nameEl.value) {
        this.customer.name = maskName(nameEl.value);
      }
      const phoneEl = this.$refs.phoneInput || (this.$el ? this.$el.querySelector('input[placeholder="(99) 99999-9999"]') : null);
      if (phoneEl && phoneEl.value) {
        this.customer.phone = maskPhone(phoneEl.value);
      }
      const cepEl = this.$refs.cepInput || (this.$el ? this.$el.querySelector('input[placeholder="58735-000"]') : null);
      if (cepEl && cepEl.value) {
        this.customer.cep = maskCep(cepEl.value);
      }

      this.customer.name = maskName(this.customer.name);
      this.customer.phone = maskPhone(this.customer.phone);
      this.customer.cep = maskCep(this.customer.cep);

      if (!this.customer.name.trim() || !this.customer.phone.trim() || !this.customer.cep.trim() || !this.customer.address.trim()) {
        this.errorMessage = 'Por favor, preencha seu nome, telefone, CEP e endereço de entrega.';
        return;
      }

      const digits = onlyDigits(this.customer.cep);
      if (digits.length !== 8) {
        this.errorMessage = 'Por favor, informe um CEP válido com 8 dígitos.';
        return;
      }

      // Validação da Regra de Negócio de Teixeira - PB
      if (digits.startsWith('58735')) {
        this.cepValid = true;
        this.cepCityInfo = 'Teixeira - PB';
      } else if (this.cepValid === false) {
        this.errorMessage = this.cepErrorMessage || 'desculpe, não entregamos na sua região!';
        return;
      } else if (this.cepValid === null) {
        await this.verifyCep(digits);
        if (this.cepValid === false) {
          this.errorMessage = this.cepErrorMessage || 'desculpe, não entregamos na sua região!';
          return;
        }
      }

      if (this.paymentMethod === 'dinheiro' && this.needsChange) {
        if (!this.changeFor || Number(this.changeFor) <= this.cartTotal) {
          this.errorMessage = `Para troco, informe um valor maior que o total do pedido (R$ ${this.formatPrice(this.cartTotal)}).`;
          return;
        }
      }

      this.loading = true;

      try {
        const payload = {
          customer_name: this.customer.name,
          customer_phone: this.customer.phone,
          delivery_cep: this.customer.cep,
          delivery_address: this.customer.address,
          delivery_complement: this.customer.complement,
          delivery_reference: this.customer.reference,
          notes: this.customer.notes,
          payment_method: this.paymentMethod,
          needs_change: this.needsChange,
          change_for: (this.paymentMethod === 'dinheiro' && this.needsChange) ? Number(this.changeFor) : null,
          items: this.cart.map(i => ({
            product_id: Number(i.id),
            quantity: Number(i.quantity || 1),
          })),
        };

        const res = await axios.post('/api/orders', payload);
        this.saveCustomerToSession();
        this.$emit('order-created', res.data.order);
      } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
          this.errorMessage = err.response.data.message;
        } else {
          this.errorMessage = 'Ocorreu um erro ao processar o seu pedido. Tente novamente.';
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
@media (max-width: 576px) {
  .cart-modal-dialog {
    margin: 0;
    max-width: 100%;
    min-height: 100%;
  }
  .cart-modal-dialog .modal-content {
    min-height: 100vh;
    border-radius: 0 !important;
  }
}
</style>

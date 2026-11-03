<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
        
        <!-- Cabeçalho Estilizado com Logo AKY Lanches integrada -->
        <div class="modal-header bg-dark text-white border-0 py-3 px-3 px-sm-4 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white p-1 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
              <img src="/images/aky_lanches_icon.svg" alt="AKY Lanches" style="width: 34px; height: 34px; object-fit: contain;">
            </div>
            <div>
              <h6 class="modal-title fw-bold mb-0">Área Administrativa</h6>
              <small class="text-white-50" style="font-size: 0.72rem;">AKY Lanches • Acesso Restrito</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <div class="modal-body p-4 bg-white">
          <div class="text-center mb-3">
            <h5 class="fw-bold text-dark mb-1">Identificação da Equipe</h5>
            <p class="text-muted small mb-0">Digite suas credenciais cadastradas para gerenciar pedidos, produtos e entregas</p>
          </div>

          <form @submit.prevent="handleLogin">
            <div class="mb-3 text-start">
              <label class="form-label small fw-bold text-dark mb-1">E-mail de Acesso</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                  <i class="bi bi-envelope-fill"></i>
                </span>
                <input
                  type="email"
                  class="form-control border-start-0 ps-2"
                  v-model="email"
                  placeholder="admin@akylanches.com"
                  autocomplete="username"
                  required
                >
              </div>
            </div>

            <div class="mb-3 text-start">
              <label class="form-label small fw-bold text-dark mb-1">Senha de Segurança</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                  <i class="bi bi-key-fill"></i>
                </span>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  class="form-control border-start-0 border-end-0 ps-2"
                  v-model="password"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  required
                >
                <button
                  type="button"
                  class="input-group-text bg-light border-start-0 text-muted pe-3 cursor-pointer"
                  @click="showPassword = !showPassword"
                  title="Mostrar/Ocultar senha"
                >
                  <i :class="showPassword ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                </button>
              </div>
            </div>

            <div v-if="errorMessage" class="alert alert-danger py-2 px-3 small text-start border-0 rounded-3 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
              <span>{{ errorMessage }}</span>
            </div>

            <button
              type="submit"
              class="btn btn-dark w-100 py-2.5 rounded-pill fw-bold shadow d-flex align-items-center justify-content-center gap-2 mt-2 transition"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm"></span>
              <template v-else>
                <i class="bi bi-box-arrow-in-right fs-5 text-warning"></i>
                <span>Entrar no Painel</span>
              </template>
            </button>
          </form>

          <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-center gap-1.5 text-muted" style="font-size: 0.72rem;">
            <i class="bi bi-shield-check text-success"></i>
            <span>Conexão criptografada de alta segurança</span>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    show: Boolean,
  },
  data() {
    return {
      email: '',
      password: '',
      showPassword: false,
      loading: false,
      errorMessage: null,
    };
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      this.errorMessage = null;

      try {
        const res = await axios.post('/api/auth/login', {
          email: this.email,
          password: this.password,
        });

        // Limpeza de segurança: destrói dados de clientes que possam estar na máquina
        localStorage.removeItem('aky_customer_session');
        localStorage.removeItem('aky_cart');

        this.$emit('logged-in', res.data.user);
        this.$emit('close');
      } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
          this.errorMessage = err.response.data.message;
        } else if (err.response && err.response.data && err.response.data.errors) {
          const errors = err.response.data.errors;
          this.errorMessage = Object.values(errors).flat()[0];
        } else {
          this.errorMessage = 'Credenciais inválidas. Tente novamente.';
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>


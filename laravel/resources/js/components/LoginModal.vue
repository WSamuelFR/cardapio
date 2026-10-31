<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content shadow border-0 rounded-4">
        
        <div class="modal-header border-0 pb-0">
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>

        <div class="modal-body p-4 text-center">
          <!-- Logo Oficial no Login -->
          <img src="/images/aky_lanches_icon.svg" alt="AKY Lanches" class="mb-3" style="height: 70px;">
          <h5 class="fw-bold text-dark mb-1">Acesso Restrito</h5>
          <p class="text-muted small mb-4">Painel Administrativo & Atendimento</p>

          <form @submit.prevent="handleLogin">
            <div class="mb-3 text-start">
              <label class="form-label small fw-semibold text-muted">E-mail</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input
                  type="email"
                  class="form-control border-start-0 ps-0"
                  v-model="email"
                  placeholder="seuemail@exemplo.com"
                  required
                >
              </div>
            </div>

            <div class="mb-3 text-start">
              <label class="form-label small fw-semibold text-muted">Senha</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input
                  type="password"
                  class="form-control border-start-0 ps-0"
                  v-model="password"
                  placeholder="••••••••"
                  required
                >
              </div>
            </div>

            <div v-if="errorMessage" class="alert alert-danger py-2 small text-start">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ errorMessage }}
            </div>

            <button type="submit" class="btn btn-aky-primary w-100 py-2 rounded-pill fw-semibold mt-2" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              <span v-else>Entrar no Painel</span>
            </button>
          </form>
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


<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
        
        <!-- Cabeçalho Estilizado com Identidade AKY Lanches -->
        <div class="modal-header bg-dark text-white border-0 py-3 px-3 px-sm-4 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2 min-w-0">
            <div class="rounded-circle bg-danger bg-opacity-25 text-white p-2 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
              <i class="bi bi-person-gear fs-4 text-warning"></i>
            </div>
            <div class="min-w-0">
              <div class="d-flex align-items-center gap-2">
                <h6 class="modal-title fw-bold mb-0 text-truncate text-white">Meus Dados Cadastrados</h6>
                <span
                  class="badge rounded-pill fw-bold"
                  :class="roleBadgeClass"
                  style="font-size: 0.68rem;"
                >
                  {{ roleLabel }}
                </span>
              </div>
              <small class="text-white-50" style="font-size: 0.72rem;">AKY Lanches • Gestão do Perfil</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <!-- Corpo do Modal -->
        <div class="modal-body p-3 p-sm-4 bg-white">
          <form @submit.prevent="handleSubmit">
            
            <!-- Nome Completo -->
            <div class="mb-3 text-start">
              <label class="form-label small fw-bold text-dark mb-1">
                <i class="bi bi-person-fill text-danger me-1"></i> Nome Completo <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control rounded-3 text-uppercase"
                v-model="form.name"
                @input="handleNameInput"
                placeholder="SEU NOME COMPLETO"
                required
                maxlength="150"
              >
              <small class="text-muted" style="font-size: 0.72rem;">Apenas letras e espaços.</small>
            </div>

            <!-- E-mail de Acesso -->
            <div class="mb-3 text-start">
              <label class="form-label small fw-bold text-dark mb-1">
                <i class="bi bi-envelope-fill text-danger me-1"></i> E-mail de Acesso <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                  <i class="bi bi-envelope"></i>
                </span>
                <input
                  type="email"
                  class="form-control border-start-0"
                  v-model="form.email"
                  placeholder="seuemail@exemplo.com"
                  required
                >
              </div>
            </div>

            <!-- Telefone / WhatsApp e CPF em duas colunas -->
            <div class="row g-2 mb-3 text-start">
              <div class="col-12 col-sm-6">
                <label class="form-label small fw-bold text-dark mb-1">
                  <i class="bi bi-whatsapp text-success me-1"></i> Telefone / WhatsApp
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-telephone"></i>
                  </span>
                  <input
                    type="tel"
                    class="form-control border-start-0"
                    v-model="form.phone"
                    @input="handlePhoneInput"
                    placeholder="(83) 99999-9999"
                    maxlength="15"
                  >
                </div>
              </div>

              <div class="col-12 col-sm-6">
                <label class="form-label small fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-person-vcard text-primary me-1"></i> CPF</span>
                  <span v-if="cpfStatus" :class="cpfStatus.class" style="font-size: 0.7rem;">
                    {{ cpfStatus.text }}
                  </span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-card-text"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control border-start-0 font-monospace"
                    v-model="form.document"
                    @input="handleDocumentInput"
                    placeholder="000.000.000-00"
                    maxlength="14"
                  >
                </div>
              </div>
            </div>

            <!-- Alteração Opcional de Senha -->
            <div class="card bg-light border-0 rounded-4 p-3 mb-3 text-start">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="form-label small fw-bold text-dark mb-0">
                  <i class="bi bi-shield-lock-fill text-warning me-1"></i> Alterar Senha (Opcional)
                </label>
                <button
                  type="button"
                  class="btn btn-sm btn-link text-decoration-none text-muted p-0 small"
                  @click="showPasswordFields = !showPasswordFields"
                >
                  <span v-if="!showPasswordFields"><i class="bi bi-pencil-square me-1"></i> Trocar senha</span>
                  <span v-else><i class="bi bi-x-circle me-1"></i> Cancelar troca</span>
                </button>
              </div>

              <div v-if="showPasswordFields" class="mt-2">
                <small class="text-muted d-block mb-2" style="font-size: 0.72rem;">
                  Preencha apenas se deseja alterar sua senha atual. Mínimo de 6 caracteres.
                </small>

                <div class="mb-2">
                  <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0">
                      <i class="bi bi-key"></i>
                    </span>
                    <input
                      :type="showPasswordText ? 'text' : 'password'"
                      class="form-control border-start-0 border-end-0"
                      v-model="form.password"
                      placeholder="Nova senha (mínimo 6 dígitos)"
                      autocomplete="new-password"
                    >
                    <button
                      type="button"
                      class="input-group-text bg-white text-muted border-start-0 cursor-pointer"
                      @click="showPasswordText = !showPasswordText"
                    >
                      <i :class="showPasswordText ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                  </div>
                </div>

                <div>
                  <input
                    :type="showPasswordText ? 'text' : 'password'"
                    class="form-control"
                    v-model="form.password_confirmation"
                    placeholder="Confirme a nova senha"
                    autocomplete="new-password"
                  >
                  <small
                    v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation"
                    class="text-danger d-block mt-1"
                    style="font-size: 0.72rem;"
                  >
                    <i class="bi bi-exclamation-circle me-1"></i> As senhas digitadas não coincidem.
                  </small>
                </div>
              </div>
              <div v-else class="text-muted small" style="font-size: 0.75rem;">
                <i class="bi bi-check2-circle text-success me-1"></i> Sua senha atual permanecerá inalterada.
              </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
              <button
                type="button"
                class="btn btn-light rounded-pill px-3 fw-semibold text-muted"
                @click="$emit('close')"
                :disabled="loading"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 transition"
                :disabled="loading || isSubmitDisabled"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <template v-else>
                  <i class="bi bi-check2-circle text-success fs-5"></i>
                  <span>Salvar Alterações</span>
                </template>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { maskName, maskPhone, maskDocument, isValidCpf } from '../utils/masks';
import { showSuccess, showError } from '../utils/swal';

export default {
  name: 'ProfileModal',
  props: {
    show: {
      type: Boolean,
      default: false,
    },
    currentUser: {
      type: Object,
      default: null,
    },
  },
  emits: ['close', 'updated'],
  data() {
    return {
      form: {
        name: '',
        email: '',
        phone: '',
        document: '',
        password: '',
        password_confirmation: '',
      },
      showPasswordFields: false,
      showPasswordText: false,
      loading: false,
    };
  },
  computed: {
    roleLabel() {
      if (!this.currentUser) return '';
      switch (this.currentUser.role) {
        case 'admin': return 'Administrador';
        case 'motoboy': return 'Entregador / Motoboy';
        default: return 'Atendente';
      }
    },
    roleBadgeClass() {
      if (!this.currentUser) return 'bg-secondary';
      switch (this.currentUser.role) {
        case 'admin': return 'bg-danger text-white';
        case 'motoboy': return 'bg-warning text-dark';
        default: return 'bg-primary text-white';
      }
    },
    cpfStatus() {
      if (!this.form.document) return null;
      const digits = this.form.document.replace(/\D/g, '');
      if (digits.length === 0) return null;
      if (digits.length < 11) {
        return { text: 'Incompleto', class: 'text-muted' };
      }
      if (digits.length === 11) {
        return isValidCpf(this.form.document)
          ? { text: '✓ CPF Válido', class: 'text-success fw-bold' }
          : { text: '✗ CPF Inválido', class: 'text-danger fw-bold' };
      }
      return null;
    },
    isSubmitDisabled() {
      if (!this.form.name.trim() || !this.form.email.trim()) return true;
      if (this.form.document) {
        const digits = this.form.document.replace(/\D/g, '');
        if (digits.length > 0 && (digits.length !== 11 || !isValidCpf(this.form.document))) {
          return true;
        }
      }
      if (this.showPasswordFields && this.form.password) {
        if (this.form.password.length < 6) return true;
        if (this.form.password !== this.form.password_confirmation) return true;
      }
      return false;
    },
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.initForm();
      }
    },
    currentUser: {
      deep: true,
      handler() {
        if (this.show) {
          this.initForm();
        }
      },
    },
  },
  methods: {
    initForm() {
      if (this.currentUser) {
        this.form.name = this.currentUser.name || '';
        this.form.email = this.currentUser.email || '';
        this.form.phone = this.currentUser.phone ? maskPhone(this.currentUser.phone) : '';
        this.form.document = this.currentUser.document ? maskDocument(this.currentUser.document) : '';
      } else {
        this.form.name = '';
        this.form.email = '';
        this.form.phone = '';
        this.form.document = '';
      }
      this.form.password = '';
      this.form.password_confirmation = '';
      this.showPasswordFields = false;
      this.showPasswordText = false;
    },
    handleNameInput(e) {
      this.form.name = maskName(e.target.value);
    },
    handlePhoneInput(e) {
      this.form.phone = maskPhone(e.target.value);
    },
    handleDocumentInput(e) {
      this.form.document = maskDocument(e.target.value);
    },
    async handleSubmit() {
      if (this.isSubmitDisabled) return;

      this.loading = true;
      try {
        const payload = {
          name: this.form.name.trim(),
          email: this.form.email.trim(),
          phone: this.form.phone.trim() || null,
          document: this.form.document.trim() || null,
        };

        if (this.showPasswordFields && this.form.password.trim()) {
          payload.password = this.form.password;
        }

        const res = await axios.put('/api/auth/profile', payload);
        const updatedUser = res.data.user;

        this.$emit('updated', updatedUser);
        this.$emit('close');

        await showSuccess('Perfil Atualizado!', res.data.message || 'Seus dados foram atualizados com sucesso.');
      } catch (err) {
        const msg = err.response?.data?.message || 'Ocorreu um erro ao atualizar seus dados. Tente novamente.';
        showError('Atenção', msg);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>


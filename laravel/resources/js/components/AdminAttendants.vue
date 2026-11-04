<template>
  <div class="admin-attendants pb-5">
    
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <div>
        <h4 class="fw-bold mb-1 text-dark">
          <i class="bi bi-people-fill me-2 text-aky-primary"></i> Gestão da Equipe
        </h4>
        <small class="text-muted">Cadastre e gerencie o acesso de atendentes, entregadores (motoboys) e equipe da cozinha (TV)</small>
      </div>

      <button class="btn btn-aky-primary rounded-pill btn-sm px-3 fw-semibold shadow-sm" @click="openCreateModal">
        <i :class="currentTab === 'attendants' ? 'bi-person-plus' : (currentTab === 'motoboys' ? 'bi-bicycle' : 'bi-fire')" class="bi me-1"></i>
        <span>{{ currentTab === 'attendants' ? 'Novo Atendente' : (currentTab === 'motoboys' ? 'Novo Motoboy' : 'Novo Usuário Cozinha') }}</span>
      </button>
    </div>

    <!-- ABAS DA EQUIPE (ATENDENTES / MOTOBOYS / COZINHA - SEGMENTED CONTROL) -->
    <div class="bg-white p-1 rounded-4 shadow-sm border mb-4 d-flex gap-1 w-100" style="max-width: 640px;">
      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'attendants' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
        @click="currentTab = 'attendants'"
      >
        <i class="bi bi-headset me-1"></i> Atendentes ({{ attendants.length }})
      </button>

      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'motoboys' ? 'btn-primary text-white shadow-sm' : 'btn-light text-dark']"
        @click="currentTab = 'motoboys'"
      >
        <i class="bi bi-bicycle me-1"></i> Motoboys ({{ motoboys.length }})
      </button>

      <button
        :class="['btn flex-fill rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'kitchen' ? 'btn-danger text-white shadow-sm' : 'btn-light text-dark']"
        @click="currentTab = 'kitchen'"
      >
        <i class="bi bi-fire me-1"></i> Cozinha / TV ({{ kitchenUsers.length }})
      </button>
    </div>

    <!-- ABA 1: ATENDENTES -->
    <div v-if="currentTab === 'attendants'">
      <div v-if="attendants.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhum atendente cadastrado no momento</h6>
        <button class="btn btn-aky-primary rounded-pill btn-sm mt-3 px-3" @click="openCreateModal">
          Cadastrar Atendente
        </button>
      </div>

      <div v-else class="row g-3">
        <div
          v-for="user in attendants"
          :key="'attendant-' + user.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div :class="['card border-0 border-start border-3 border-danger shadow-sm rounded-4 h-100 bg-white p-3 d-flex flex-column justify-content-between', !user.is_active ? 'opacity-75' : '']">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle bg-light text-aky-primary p-3 flex-shrink-0 d-flex align-items-center justify-content-center border" style="width: 52px; height: 52px;">
                <i class="bi bi-headset fs-4"></i>
              </div>

              <div class="flex-grow-1 min-w-0">
                <h6 class="fw-bold text-dark mb-0 text-truncate">{{ user.name }}</h6>
                <small class="text-muted text-truncate d-block">{{ user.email }}</small>
                <span class="badge bg-secondary rounded-pill mt-1" style="font-size: 0.68rem;">Atendente</span>
              </div>

              <!-- Botão Editar -->
              <button
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted shadow-sm flex-shrink-0"
                @click="openEditModal(user)"
                title="Editar dados do atendente"
              >
                <i class="bi bi-pencil-square text-dark"></i>
              </button>
            </div>

            <!-- Dados extras: Telefone e Documento -->
            <div class="bg-light rounded-3 p-2 small mb-3 d-flex flex-column gap-1">
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-telephone text-aky-primary me-1"></i> Telefone:</span>
                <span class="fw-semibold text-dark">{{ user.phone || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-person-vcard text-aky-primary me-1"></i> Documento:</span>
                <span class="fw-semibold text-dark">{{ user.document || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted border-top pt-1 mt-1">
                <span>Cadastrado em:</span>
                <span class="fw-semibold text-dark">{{ formatDate(user.created_at) }}</span>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <span class="small fw-semibold text-muted">Acesso ao Painel:</span>
              <div class="form-check form-switch mb-0">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="'user-act-' + user.id"
                  :checked="user.is_active"
                  @change="toggleActive(user)"
                >
                <label class="form-check-label small fw-semibold" :class="user.is_active ? 'text-success' : 'text-danger'" :for="'user-act-' + user.id">
                  {{ user.is_active ? 'Liberado' : 'Bloqueado' }}
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ABA 2: MOTOBOYS -->
    <div v-else-if="currentTab === 'motoboys'">
      <div v-if="motoboys.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-bicycle fs-1 text-muted d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhum motoboy cadastrado no momento</h6>
        <button class="btn btn-aky-primary rounded-pill btn-sm mt-3 px-3" @click="openCreateModal">
          Cadastrar Primeiro Motoboy
        </button>
      </div>

      <div v-else class="row g-3">
        <div
          v-for="user in motoboys"
          :key="'motoboy-' + user.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div :class="['card border-0 border-start border-3 border-primary shadow-sm rounded-4 h-100 bg-white p-3 d-flex flex-column justify-content-between', !user.is_active ? 'opacity-75' : '']">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle bg-warning bg-opacity-25 text-dark p-3 flex-shrink-0 d-flex align-items-center justify-content-center border" style="width: 52px; height: 52px;">
                <i class="bi bi-bicycle fs-4 text-dark"></i>
              </div>

              <div class="flex-grow-1 min-w-0">
                <h6 class="fw-bold text-dark mb-0 text-truncate">{{ user.name }}</h6>
                <small class="text-muted text-truncate d-block">{{ user.email }}</small>
                <span class="badge bg-warning text-dark rounded-pill mt-1" style="font-size: 0.68rem;">Entregador / Motoboy</span>
              </div>

              <!-- Botão Editar -->
              <button
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted shadow-sm flex-shrink-0"
                @click="openEditModal(user)"
                title="Editar dados do entregador"
              >
                <i class="bi bi-pencil-square text-dark"></i>
              </button>
            </div>

            <!-- Dados extras: Telefone e Documento -->
            <div class="bg-light rounded-3 p-2 small mb-3 d-flex flex-column gap-1">
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-telephone text-primary me-1"></i> Telefone:</span>
                <span class="fw-semibold text-dark">{{ user.phone || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-person-vcard text-primary me-1"></i> Documento:</span>
                <span class="fw-semibold text-dark">{{ user.document || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted border-top pt-1 mt-1">
                <span>Cadastrado em:</span>
                <span class="fw-semibold text-dark">{{ formatDate(user.created_at) }}</span>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <span class="small fw-semibold text-muted">Acesso a Entregas:</span>
              <div class="form-check form-switch mb-0">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="'moto-act-' + user.id"
                  :checked="user.is_active"
                  @change="toggleActive(user)"
                >
                <label class="form-check-label small fw-semibold" :class="user.is_active ? 'text-success' : 'text-danger'" :for="'moto-act-' + user.id">
                  {{ user.is_active ? 'Ativo' : 'Inativo' }}
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ABA 3: COZINHA (MONITOR TV / KDS) -->
    <div v-else-if="currentTab === 'kitchen'">
      <div v-if="kitchenUsers.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-fire fs-1 text-danger d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhum operador da cozinha cadastrado no momento</h6>
        <p class="text-muted small mb-3">Cadastre um usuário para login na TV ou terminal da cozinha.</p>
        <button class="btn btn-danger rounded-pill btn-sm px-3 shadow-sm" @click="openCreateModal">
          <i class="bi bi-fire me-1"></i> Cadastrar Operador da Cozinha
        </button>
      </div>

      <div v-else class="row g-3">
        <div
          v-for="user in kitchenUsers"
          :key="'kitchen-' + user.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div :class="['card border-0 border-start border-3 border-danger shadow-sm rounded-4 h-100 bg-white p-3 d-flex flex-column justify-content-between', !user.is_active ? 'opacity-75' : '']">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 flex-shrink-0 d-flex align-items-center justify-content-center border" style="width: 52px; height: 52px;">
                <i class="bi bi-fire fs-4"></i>
              </div>

              <div class="flex-grow-1 min-w-0">
                <h6 class="fw-bold text-dark mb-0 text-truncate">{{ user.name }}</h6>
                <small class="text-muted text-truncate d-block">{{ user.email }}</small>
                <span class="badge bg-danger rounded-pill mt-1" style="font-size: 0.68rem;">Equipe da Cozinha (TV)</span>
              </div>

              <!-- Botão Editar -->
              <button
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted shadow-sm flex-shrink-0"
                @click="openEditModal(user)"
                title="Editar dados do operador"
              >
                <i class="bi bi-pencil-square text-dark"></i>
              </button>
            </div>

            <!-- Dados extras: Telefone e Documento -->
            <div class="bg-light rounded-3 p-2 small mb-3 d-flex flex-column gap-1">
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-telephone text-aky-primary me-1"></i> Telefone:</span>
                <span class="fw-semibold text-dark">{{ user.phone || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-person-vcard text-aky-primary me-1"></i> Documento:</span>
                <span class="fw-semibold text-dark">{{ user.document || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted border-top pt-1 mt-1">
                <span>Cadastrado em:</span>
                <span class="fw-semibold text-dark">{{ formatDate(user.created_at) }}</span>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <span class="small fw-semibold text-muted">Acesso à Cozinha / TV:</span>
              <div class="form-check form-switch mb-0">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="'kitchen-act-' + user.id"
                  :checked="user.is_active"
                  @change="toggleActive(user)"
                >
                <label class="form-check-label small fw-semibold" :class="user.is_active ? 'text-success' : 'text-danger'" :for="'kitchen-act-' + user.id">
                  {{ user.is_active ? 'Liberado' : 'Bloqueado' }}
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE CRIAR USUÁRIO (ATENDENTE OU MOTOBOY - MODERNO & MOBILE-FIRST) -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <!-- Cabeçalho Dinâmico com Gradiente Limpo e Logo AKY Lanches -->
          <div
            class="modal-header text-white border-0 py-3 px-3 px-sm-4"
            :style="currentTab === 'attendants'
              ? 'background: linear-gradient(135deg, #e50914 0%, #b20710 100%);'
              : (currentTab === 'motoboys' ? 'background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);' : 'background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);')"
          >
            <div class="d-flex align-items-center gap-3">
              <img src="/images/aky_lanches_icon.svg" alt="Aky Lanches" class="rounded-circle bg-white p-1 shadow-sm flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
              <div>
                <h5 class="modal-title fw-bold text-white mb-0">
                  {{ currentTab === 'attendants' ? 'Novo Atendente' : (currentTab === 'motoboys' ? 'Novo Motoboy' : 'Novo Operador da Cozinha') }}
                </h5>
                <small class="text-white-50" style="font-size: 0.78rem;">
                  {{ currentTab === 'attendants' ? 'Acesso ao gerenciamento de pedidos e atendimento' : (currentTab === 'motoboys' ? 'Acesso à lista de entregas e mapas de rota' : 'Acesso ao monitor TV e fila de produção KDS') }}
                </small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showModal = false"></button>
          </div>

          <form @submit.prevent="createMember">
            <div class="modal-body p-3 p-sm-4">
              
              <!-- Nome Completo -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Nome Completo *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-person-fill"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control border-start-0 ps-1"
                    :value="form.name"
                    @input="onFormNameInput"
                    @change="onFormNameInput"
                    @blur="onFormNameInput"
                    :placeholder="currentTab === 'attendants' ? 'Ex: Marina Souza' : 'Ex: Carlos Oliveira'"
                    autocomplete="off"
                    required
                  >
                </div>
              </div>

              <!-- E-mail de Acesso -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">E-mail de Acesso *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-envelope-fill"></i>
                  </span>
                  <input
                    type="email"
                    class="form-control border-start-0 ps-1"
                    v-model="form.email"
                    placeholder="colaborador@aky.com"
                    autocomplete="off"
                    required
                  >
                </div>
              </div>

              <!-- Telefone e Documento -->
              <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6">
                  <label class="form-label small fw-bold text-dark mb-1">Telefone / WhatsApp</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-telephone-fill"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control border-start-0 ps-1"
                      :value="form.phone"
                      @input="onFormPhoneInput"
                      @change="onFormPhoneInput"
                      @blur="onFormPhoneInput"
                      placeholder="(99) 99999-9999"
                      autocomplete="off"
                    >
                  </div>
                </div>

                <div class="col-12 col-sm-6">
                  <label class="form-label small fw-bold text-dark mb-1">Documento (CPF / RG)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-person-vcard-fill"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control border-start-0 ps-1"
                      :value="form.document"
                      @input="onFormDocumentInput"
                      @change="onFormDocumentInput"
                      @blur="onFormDocumentInput"
                      placeholder="000.000.000-00"
                      autocomplete="off"
                    >
                  </div>
                  <div v-if="isFormCpfValid === true" class="text-success small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> CPF matematicamente válido
                  </div>
                  <div v-else-if="isFormCpfValid === false" class="text-danger small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> CPF inválido. Verifique os números digitados.
                  </div>
                </div>
              </div>

              <!-- Senha Inicial -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Senha de Acesso *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-key-fill"></i>
                  </span>
                  <input
                    type="password"
                    class="form-control border-start-0 ps-1"
                    v-model="form.password"
                    placeholder="Mínimo 6 caracteres"
                    minlength="6"
                    autocomplete="new-password"
                    required
                  >
                </div>
              </div>

              <!-- Card Informativo de Acesso -->
              <div class="alert alert-light border small rounded-3 p-2 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-5"></i>
                <span class="text-muted" style="font-size: 0.74rem;">
                  O colaborador usará este e-mail e senha para entrar no sistema pelo botão <strong>Área Restrita</strong>.
                </span>
              </div>

              <!-- Mensagem de Erro -->
              <div v-if="error" class="alert alert-danger py-2 small rounded-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ error }}
              </div>
            </div>

            <!-- Rodapé Flexível para Mobile e Desktop -->
            <div class="modal-footer border-top bg-light p-3 d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
              <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-muted fw-semibold w-100 w-sm-auto" @click="showModal = false">
                Cancelar
              </button>
              <button
                type="submit"
                class="btn rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto"
                :class="currentTab === 'attendants' ? 'btn-aky-primary' : 'btn-primary'"
                :disabled="saving"
              >
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-person-plus-fill me-1"></i>
                {{ currentTab === 'attendants' ? 'Cadastrar Atendente' : 'Cadastrar Motoboy' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL DE EDIÇÃO DE USUÁRIO COM NAVEGAÇÃO POR SETAS -->
    <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <!-- Cabeçalho Dinâmico com Setas de Navegação e Logo AKY Lanches -->
          <div
            class="modal-header text-white border-0 py-3 px-3 px-sm-4 d-flex align-items-center justify-content-between"
            :style="currentTab === 'attendants'
              ? 'background: linear-gradient(135deg, #e50914 0%, #b20710 100%);'
              : (currentTab === 'motoboys' ? 'background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);' : 'background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);')"
          >
            <!-- Título com Logo e Subtítulo -->
            <div class="d-flex align-items-center gap-2 min-w-0 me-2">
              <img src="/images/aky_lanches_icon.svg" alt="Aky Lanches" class="rounded-circle bg-white p-1 shadow-sm flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
              <div class="min-w-0">
                <h5 class="modal-title fw-bold text-white mb-0 text-truncate">
                  {{ currentTab === 'attendants' ? 'Editar Atendente' : (currentTab === 'motoboys' ? 'Editar Motoboy' : 'Editar Operador da Cozinha') }}
                </h5>
                <small class="text-white-50 d-block text-truncate" style="font-size: 0.78rem;">
                  Atualize dados cadastrais e credenciais
                </small>
              </div>
            </div>

            <!-- Controles de Navegação por Setas e Fechar -->
            <div class="d-flex align-items-center gap-1 flex-shrink-0">
              <div class="bg-black bg-opacity-25 rounded-pill p-1 d-flex align-items-center gap-1 shadow-sm">
                <!-- Seta Anterior -->
                <button
                  type="button"
                  class="btn btn-sm btn-link text-white p-0 d-flex align-items-center justify-content-center nav-arrow-btn"
                  :disabled="editIndex <= 0"
                  @click="prevUser"
                  title="Usuário anterior"
                  style="width: 28px; height: 28px; text-decoration: none;"
                >
                  <i class="bi bi-chevron-left fw-bold"></i>
                </button>

                <!-- Contador de Usuários -->
                <span class="text-white fw-bold px-1.5 small user-select-none" style="font-size: 0.75rem;">
                  {{ editIndex + 1 }} / {{ currentTeamList.length }}
                </span>

                <!-- Seta Próximo -->
                <button
                  type="button"
                  class="btn btn-sm btn-link text-white p-0 d-flex align-items-center justify-content-center nav-arrow-btn"
                  :disabled="editIndex >= currentTeamList.length - 1"
                  @click="nextUser"
                  title="Próximo usuário"
                  style="width: 28px; height: 28px; text-decoration: none;"
                >
                  <i class="bi bi-chevron-right fw-bold"></i>
                </button>
              </div>

              <!-- Botão Fechar -->
              <button type="button" class="btn-close btn-close-white ms-1" @click="showEditModal = false"></button>
            </div>
          </div>

          <form @submit.prevent="saveEditMember">
            <div class="modal-body p-3 p-sm-4">

              <!-- Alerta de Sucesso -->
              <div v-if="editSuccess" class="alert alert-success py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>Dados atualizados com sucesso!</div>
              </div>

              <!-- Alerta de Erro -->
              <div v-if="editError" class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ editError }}</div>
              </div>

              <!-- Nome Completo -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Nome Completo *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-person-fill"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control border-start-0 ps-1"
                    :value="editForm.name"
                    @input="onEditFormNameInput"
                    @change="onEditFormNameInput"
                    @blur="onEditFormNameInput"
                    autocomplete="off"
                    required
                  >
                </div>
              </div>

              <!-- E-mail de Acesso -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">E-mail de Acesso *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-envelope-fill"></i>
                  </span>
                  <input
                    type="email"
                    class="form-control border-start-0 ps-1"
                    v-model="editForm.email"
                    autocomplete="off"
                    required
                  >
                </div>
              </div>

              <!-- Telefone e Documento -->
              <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6">
                  <label class="form-label small fw-bold text-dark mb-1">Telefone / WhatsApp</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-telephone-fill"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control border-start-0 ps-1"
                      :value="editForm.phone"
                      @input="onEditFormPhoneInput"
                      @change="onEditFormPhoneInput"
                      @blur="onEditFormPhoneInput"
                      placeholder="(99) 99999-9999"
                      autocomplete="off"
                    >
                  </div>
                </div>

                <div class="col-12 col-sm-6">
                  <label class="form-label small fw-bold text-dark mb-1">Documento (CPF / RG)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-person-vcard-fill"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control border-start-0 ps-1"
                      :value="editForm.document"
                      @input="onEditFormDocumentInput"
                      @change="onEditFormDocumentInput"
                      @blur="onEditFormDocumentInput"
                      placeholder="000.000.000-00"
                      autocomplete="off"
                    >
                  </div>
                  <div v-if="isEditFormCpfValid === true" class="text-success small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> CPF matematicamente válido
                  </div>
                  <div v-else-if="isEditFormCpfValid === false" class="text-danger small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> CPF inválido. Verifique os números digitados.
                  </div>
                </div>
              </div>

              <!-- Nova Senha (Opcional) -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label small fw-bold text-dark mb-0">Nova Senha</label>
                  <small class="text-muted" style="font-size: 0.72rem;">Deixe vazio para manter</small>
                </div>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-key-fill"></i>
                  </span>
                  <input
                    type="password"
                    class="form-control border-start-0 ps-1"
                    v-model="editForm.password"
                    placeholder="Mínimo 6 caracteres (opcional)"
                    minlength="6"
                    autocomplete="new-password"
                  >
                </div>
              </div>

              <!-- Status Ativo / Inativo Switch -->
              <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                <div>
                  <div class="fw-bold text-dark small">Status do Acesso</div>
                  <small class="text-muted" style="font-size: 0.74rem;">Permite ou bloqueia a entrada do colaborador</small>
                </div>
                <div class="form-check form-switch mb-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="edit-form-status"
                    v-model="editForm.is_active"
                  >
                  <label class="form-check-label small fw-bold ms-1" :class="editForm.is_active ? 'text-success' : 'text-danger'" for="edit-form-status">
                    {{ editForm.is_active ? 'Liberado' : 'Bloqueado' }}
                  </label>
                </div>
              </div>

            </div>

            <!-- Rodapé do Modal -->
            <div class="modal-footer border-top bg-light p-3 d-flex flex-column-reverse flex-sm-row justify-content-between gap-2">
              <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-muted fw-semibold w-100 w-sm-auto" @click="showEditModal = false">
                Fechar
              </button>
              <button
                type="submit"
                class="btn rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto"
                :class="currentTab === 'attendants' ? 'btn-aky-primary' : (currentTab === 'motoboys' ? 'btn-primary' : 'btn-danger')"
                :disabled="savingEdit"
              >
                <span v-if="savingEdit" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check2-circle me-1"></i>
                Salvar Alterações
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
import { maskName, maskPhone, maskDocument, applyMask, onlyDigits, isValidCpf } from '../utils/masks';
import { showError } from '../utils/swal';

export default {
  data() {
    return {
      currentTab: 'attendants', // 'attendants', 'motoboys' ou 'kitchen'
      attendants: [],
      motoboys: [],
      kitchenUsers: [],
      showModal: false,
      saving: false,
      error: null,
      form: {
        name: '',
        email: '',
        phone: '',
        document: '',
        password: '',
      },
      showEditModal: false,
      editIndex: 0,
      editForm: {
        id: null,
        name: '',
        email: '',
        phone: '',
        document: '',
        password: '',
        is_active: true,
      },
      savingEdit: false,
      editError: null,
      editSuccess: false,
    };
  },
  computed: {
    currentTeamList() {
      if (this.currentTab === 'attendants') return this.attendants;
      if (this.currentTab === 'motoboys') return this.motoboys;
      return this.kitchenUsers;
    },
    isFormCpfValid() {
      if (!this.form.document) return null;
      const digits = onlyDigits(this.form.document);
      if (digits.length === 11) {
        return isValidCpf(digits);
      }
      return null;
    },
    isEditFormCpfValid() {
      if (!this.editForm.document) return null;
      const digits = onlyDigits(this.editForm.document);
      if (digits.length === 11) {
        return isValidCpf(digits);
      }
      return null;
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    maskName,
    maskPhone,
    maskDocument,
    onFormNameInput(e) {
      applyMask(e, maskName, val => { this.form.name = val; });
    },
    onFormPhoneInput(e) {
      applyMask(e, maskPhone, val => { this.form.phone = val; });
    },
    onFormDocumentInput(e) {
      applyMask(e, maskDocument, val => { this.form.document = val; });
    },
    onEditFormNameInput(e) {
      applyMask(e, maskName, val => { this.editForm.name = val; });
    },
    onEditFormPhoneInput(e) {
      applyMask(e, maskPhone, val => { this.editForm.phone = val; });
    },
    onEditFormDocumentInput(e) {
      applyMask(e, maskDocument, val => { this.editForm.document = val; });
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString('pt-BR');
    },
    async fetchData() {
      try {
        const [attRes, motoRes, kitRes] = await Promise.all([
          axios.get('/api/admin/attendants'),
          axios.get('/api/admin/all-motoboys'),
          axios.get('/api/admin/kitchen-users'),
        ]);
        this.attendants = attRes.data;
        this.motoboys = motoRes.data;
        this.kitchenUsers = kitRes.data;
      } catch (err) {
        console.error(err);
      }
    },
    openCreateModal() {
      this.form = { name: '', email: '', phone: '', document: '', password: '' };
      this.error = null;
      this.showModal = true;
    },
    openEditModal(user) {
      const idx = this.currentTeamList.findIndex(u => u.id === user.id);
      this.editIndex = idx >= 0 ? idx : 0;
      this.loadUserIntoEditForm(this.currentTeamList[this.editIndex]);
      this.showEditModal = true;
    },
    loadUserIntoEditForm(user) {
      if (!user) return;
      this.editForm = {
        id: user.id,
        name: user.name,
        email: user.email,
        phone: user.phone || '',
        document: user.document || '',
        password: '',
        is_active: !!user.is_active,
      };
      this.editError = null;
      this.editSuccess = false;
    },
    prevUser() {
      if (this.editIndex > 0) {
        this.editIndex--;
        this.loadUserIntoEditForm(this.currentTeamList[this.editIndex]);
      }
    },
    nextUser() {
      if (this.editIndex < this.currentTeamList.length - 1) {
        this.editIndex++;
        this.loadUserIntoEditForm(this.currentTeamList[this.editIndex]);
      }
    },
    async saveEditMember() {
      this.savingEdit = true;
      this.editError = null;
      this.editSuccess = false;
      try {
        this.editForm.name = maskName(this.editForm.name);
        this.editForm.phone = maskPhone(this.editForm.phone);
        this.editForm.document = maskDocument(this.editForm.document);

        if (!this.editForm.name.trim()) {
          this.editError = 'Nome completo inválido. Digite apenas letras.';
          this.savingEdit = false;
          return;
        }

        if (this.isEditFormCpfValid === false) {
          this.editError = 'O CPF informado é inválido. Verifique os números digitados.';
          this.savingEdit = false;
          return;
        }

        const payload = {
          name: this.editForm.name,
          email: this.editForm.email,
          phone: this.editForm.phone,
          document: this.editForm.document,
          is_active: this.editForm.is_active,
        };
        if (this.editForm.password && this.editForm.password.trim().length >= 6) {
          payload.password = this.editForm.password;
        }

        const res = await axios.put(`/api/admin/users/${this.editForm.id}`, payload);
        this.editSuccess = true;
        this.editForm.password = '';

        // Atualiza o item na lista local
        const updated = res.data.user;
        const targetList = this.currentTeamList;
        if (targetList[this.editIndex]) {
          Object.assign(targetList[this.editIndex], updated);
        }

        setTimeout(() => {
          this.editSuccess = false;
        }, 2500);
      } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
          this.editError = err.response.data.message;
        } else {
          this.editError = 'Erro ao atualizar dados do usuário.';
        }
      } finally {
        this.savingEdit = false;
      }
    },
    async createMember() {
      this.saving = true;
      this.error = null;
      try {
        this.form.name = maskName(this.form.name);
        this.form.phone = maskPhone(this.form.phone);
        this.form.document = maskDocument(this.form.document);

        if (!this.form.name.trim()) {
          this.error = 'Nome completo inválido. Digite apenas letras.';
          this.saving = false;
          return;
        }

        if (this.isFormCpfValid === false) {
          this.error = 'O CPF informado é inválido. Verifique os números digitados.';
          this.saving = false;
          return;
        }

        let endpoint = '/api/admin/attendants';
        if (this.currentTab === 'motoboys') endpoint = '/api/admin/motoboys';
        else if (this.currentTab === 'kitchen') endpoint = '/api/admin/kitchen-users';

        await axios.post(endpoint, this.form);
        this.showModal = false;
        this.fetchData();
      } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
          this.error = err.response.data.message;
        } else {
          this.error = 'Erro ao cadastrar. Verifique os dados.';
        }
      } finally {
        this.saving = false;
      }
    },
    async toggleActive(user) {
      try {
        await axios.patch(`/api/admin/attendants/${user.id}/toggle`);
        user.is_active = !user.is_active;
      } catch (err) {
        showError('Erro ao alterar status', 'Não foi possível atualizar o status do atendente.');
      }
    },
  },
};
</script>

<style scoped>
.nav-arrow-btn {
  opacity: 0.85;
  transition: all 0.2s ease;
}
.nav-arrow-btn:hover:not(:disabled) {
  opacity: 1;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
}
.nav-arrow-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}
</style>

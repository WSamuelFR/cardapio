<template>
  <div class="admin-settings pb-5">
    
    <!-- Cabeçalho Principal -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
          <i class="bi bi-gear-fill text-aky-primary"></i>
          <span>Configurações do Sistema</span>
        </h4>
        <small class="text-muted">Gerencie os dados da AKY Lanches, WhatsApp comercial e a equipe de administradores</small>
      </div>

      <button
        v-if="currentTab === 'admins'"
        class="btn btn-aky-primary rounded-pill btn-sm px-3 fw-semibold shadow-sm d-flex align-items-center gap-1.5"
        @click="openCreateAdminModal"
      >
        <i class="bi bi-person-plus-fill"></i>
        <span>Novo Administrador</span>
      </button>
    </div>

    <!-- SELETOR DE ABAS MODERNO & RESPONSIVO (Segmented Control) -->
    <div class="bg-white p-2 rounded-4 shadow-sm border mb-4" style="max-width: 580px;">
      <div class="row g-2">
        <div class="col-6">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'store' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'store'"
          >
            <i class="bi bi-shop me-1"></i> Dados da Loja & WhatsApp
          </button>
        </div>
        <div class="col-6">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'admins' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'admins'"
          >
            <i class="bi bi-shield-lock-fill me-1"></i> Administradores ({{ admins.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- ABA 1: DADOS DA AKY LANCHES & WHATSAPP COMERCIAL         -->
    <!-- ======================================================== -->
    <div v-if="currentTab === 'store'">
      
      <!-- Card WhatsApp Comercial (Destino de Pedidos) -->
      <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-whatsapp text-success fs-4"></i>
              <span>WhatsApp Comercial (Recebimento de Pedidos)</span>
            </h5>
            <small class="text-muted">Número oficial da AKY Lanches para onde os pedidos dos clientes serão encaminhados</small>
          </div>
          <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 fw-semibold small flex-shrink-0">
            <i class="bi bi-check2-circle me-1"></i> Destino dos Pedidos
          </span>
        </div>

        <div class="row g-3 align-items-center">
          <div class="col-12 col-md-6 col-lg-5">
            <label class="form-label small fw-bold text-dark mb-1">Número do WhatsApp Comercial *</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-success fw-bold border-end-0">
                <i class="bi bi-whatsapp"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-1 fw-bold text-dark"
                :value="storeSettings.store_whatsapp"
                @input="onStorePhoneInput"
                placeholder="(83) 99999-9999"
                maxlength="20"
                required
              >
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
              Ao confirmar o pedido no carrinho, o cliente clica no botão e o app abre automaticamente enviando o pedido para este número.
            </small>
          </div>

          <div class="col-12 col-md-6 col-lg-7">
            <div class="alert alert-light border rounded-3 p-3 mb-0 small text-muted">
              <div class="d-flex gap-2">
                <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0 mt-0.5"></i>
                <div>
                  <strong>Importante:</strong> Certifique-se de incluir o DDD (ex: <strong>83</strong>). Não é necessário colocar o código do país (+55), pois o sistema formata automaticamente.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Dados Gerais da Lanchonete -->
      <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
        <div class="mb-3">
          <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-building text-warning fs-4"></i>
            <span>Informações da Empresa & Atendimento</span>
          </h5>
          <small class="text-muted">Dados exibidos no cabeçalho, rodapé e comprovantes de pedidos</small>
        </div>

        <div class="row g-3">
          <!-- Nome Fantasia -->
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-dark mb-1">Nome da Lanchonete / Marca *</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bi bi-shop"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-1 fw-semibold text-dark"
                v-model="storeSettings.store_info.name"
                placeholder="Ex: AKY LANCHES"
                required
              >
            </div>
          </div>

          <!-- Slogan / Subtítulo -->
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-dark mb-1">Slogan / Subtítulo</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bi bi-tag-fill"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-1"
                v-model="storeSettings.store_info.slogan"
                placeholder="Ex: Cardápio Digital & Pedidos"
              >
            </div>
          </div>

          <!-- Endereço / Localização -->
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-dark mb-1">Endereço Físico / Cidade</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bi bi-geo-alt-fill"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-1"
                v-model="storeSettings.store_info.address"
                placeholder="Ex: Rua Principal, Centro - Teixeira - PB"
              >
            </div>
          </div>

          <!-- Horário de Atendimento -->
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-dark mb-1">Horário de Funcionamento</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bi bi-clock-fill"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-1"
                v-model="storeSettings.store_info.hours"
                placeholder="Ex: Todos os dias, das 18h00 às 23h30"
              >
            </div>
          </div>

          <!-- CNPJ / Inscrição (Opcional) -->
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-dark mb-1">CNPJ / Documento (Opcional)</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bi bi-file-text-fill"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-1"
                v-model="storeSettings.store_info.document"
                placeholder="Ex: 00.000.000/0001-00"
              >
            </div>
          </div>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
          <button
            class="btn btn-dark rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
            @click="saveStoreSettings"
            :disabled="savingStore"
          >
            <span v-if="savingStore" class="spinner-border spinner-border-sm"></span>
            <i v-else class="bi bi-floppy-fill"></i>
            <span>Salvar Informações da Loja</span>
          </button>
        </div>
      </div>

    </div>

    <!-- ======================================================== -->
    <!-- ABA 2: GESTÃO DE ADMINISTRADORES DO SISTEMA             -->
    <!-- ======================================================== -->
    <div v-else-if="currentTab === 'admins'">
      
      <div v-if="loadingAdmins" class="text-center py-5">
        <span class="spinner-border spinner-border text-danger"></span>
        <div class="text-muted small mt-2">Carregando administradores...</div>
      </div>

      <div v-else-if="admins.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
        <i class="bi bi-shield-x fs-1 text-muted d-block mb-2"></i>
        <h6 class="text-muted fw-semibold">Nenhum administrador encontrado</h6>
        <button class="btn btn-aky-primary rounded-pill btn-sm mt-3 px-3" @click="openCreateAdminModal">
          Cadastrar Administrador
        </button>
      </div>

      <div v-else class="row g-3">
        <div
          v-for="admin in admins"
          :key="'admin-' + admin.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <div :class="['card border-0 border-start border-3 border-danger shadow-sm rounded-4 h-100 bg-white p-3 d-flex flex-column justify-content-between', !admin.is_active ? 'opacity-75' : '']">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 flex-shrink-0 d-flex align-items-center justify-content-center border" style="width: 52px; height: 52px;">
                <i class="bi bi-shield-shaded fs-4"></i>
              </div>

              <div class="flex-grow-1 min-w-0">
                <h6 class="fw-bold text-dark mb-0 text-truncate" :title="admin.name">{{ admin.name }}</h6>
                <small class="text-muted text-truncate d-block" :title="admin.email">{{ admin.email }}</small>
                <div class="d-flex align-items-center gap-1 mt-1">
                  <span class="badge bg-danger rounded-pill" style="font-size: 0.68rem;">Administrador</span>
                  <span v-if="currentUser && currentUser.id === admin.id" class="badge bg-light text-dark border rounded-pill" style="font-size: 0.65rem;">
                    Você
                  </span>
                </div>
              </div>

              <!-- Botão Editar -->
              <button
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted shadow-sm flex-shrink-0"
                @click="openEditAdminModal(admin)"
                title="Editar dados do administrador"
              >
                <i class="bi bi-pencil-square text-dark"></i>
              </button>
            </div>

            <!-- Dados extras: Telefone e Documento -->
            <div class="bg-light rounded-3 p-2 small mb-3 d-flex flex-column gap-1">
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-telephone text-aky-primary me-1"></i> Telefone:</span>
                <span class="fw-semibold text-dark">{{ admin.phone || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted">
                <span><i class="bi bi-person-vcard text-aky-primary me-1"></i> Documento:</span>
                <span class="fw-semibold text-dark">{{ admin.document || 'Não informado' }}</span>
              </div>
              <div class="d-flex justify-content-between text-muted border-top pt-1 mt-1">
                <span>Cadastrado em:</span>
                <span class="fw-semibold text-dark">{{ formatDate(admin.created_at) }}</span>
              </div>
            </div>

            <!-- Toggle de Acesso -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <span class="small fw-semibold text-muted">Acesso ao Painel:</span>
              <div class="form-check form-switch mb-0">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="'admin-act-' + admin.id"
                  :checked="admin.is_active"
                  @change="toggleAdminActive(admin)"
                  :disabled="currentUser && currentUser.id === admin.id && activeAdminsCount <= 1"
                >
                <label class="form-check-label small fw-semibold" :class="admin.is_active ? 'text-success' : 'text-danger'" :for="'admin-act-' + admin.id">
                  {{ admin.is_active ? 'Liberado' : 'Bloqueado' }}
                </label>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL: CADASTRAR NOVO ADMINISTRADOR                      -->
    <!-- ======================================================== -->
    <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <div class="modal-header text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #111 0%, #333 100%);">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-danger text-white p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-shield-lock-fill fs-5"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold text-white mb-0">Novo Administrador</h5>
                <small class="text-white-50" style="font-size: 0.75rem;">Acesso total às configurações do sistema</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showCreateModal = false"></button>
          </div>

          <form @submit.prevent="saveNewAdmin">
            <div class="modal-body p-4">
              
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
                    :value="createForm.name"
                    @input="onCreateNameInput"
                    placeholder="DIGITE O NOME COMPLETO"
                    autocomplete="off"
                    required
                  >
                </div>
              </div>

              <!-- E-mail de Acesso -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">E-mail de Login *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-envelope-fill"></i>
                  </span>
                  <input
                    type="email"
                    class="form-control border-start-0 ps-1"
                    v-model="createForm.email"
                    placeholder="admin@akylanches.com"
                    autocomplete="off"
                    required
                  >
                </div>
              </div>

              <!-- Telefone e CPF -->
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
                      :value="createForm.phone"
                      @input="onCreatePhoneInput"
                      placeholder="(83) 99999-9999"
                      autocomplete="off"
                    >
                  </div>
                </div>

                <div class="col-12 col-sm-6">
                  <label class="form-label small fw-bold text-dark mb-1">CPF (Opcional)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-person-vcard-fill"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control border-start-0 ps-1"
                      :value="createForm.document"
                      @input="onCreateDocumentInput"
                      placeholder="000.000.000-00"
                      autocomplete="off"
                    >
                  </div>
                  <div v-if="isCreateCpfValid === true" class="text-success small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> CPF válido
                  </div>
                  <div v-else-if="isCreateCpfValid === false" class="text-danger small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> CPF inválido
                  </div>
                </div>
              </div>

              <!-- Senha -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Senha de Acesso *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-key-fill"></i>
                  </span>
                  <input
                    type="password"
                    class="form-control border-start-0 ps-1"
                    v-model="createForm.password"
                    placeholder="Mínimo de 6 dígitos"
                    required
                    minlength="6"
                  >
                </div>
              </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" @click="showCreateModal = false">
                Cancelar
              </button>
              <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm" :disabled="savingAdmin">
                <span v-if="savingAdmin" class="spinner-border spinner-border-sm me-1"></span>
                Cadastrar Administrador
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: EDITAR ADMINISTRADOR                              -->
    <!-- ======================================================== -->
    <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <div class="modal-header text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #111 0%, #333 100%);">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-danger text-white p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-pencil-square fs-5"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold text-white mb-0">Editar Administrador</h5>
                <small class="text-white-50" style="font-size: 0.75rem;">Atualize os dados de acesso</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showEditModal = false"></button>
          </div>

          <form @submit.prevent="updateAdmin">
            <div class="modal-body p-4">
              
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
                    @input="onEditNameInput"
                    placeholder="NOME COMPLETO"
                    required
                  >
                </div>
              </div>

              <!-- E-mail de Login -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">E-mail de Login *</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-envelope-fill"></i>
                  </span>
                  <input
                    type="email"
                    class="form-control border-start-0 ps-1"
                    v-model="editForm.email"
                    required
                  >
                </div>
              </div>

              <!-- Telefone e CPF -->
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
                      @input="onEditPhoneInput"
                      placeholder="(83) 99999-9999"
                    >
                  </div>
                </div>

                <div class="col-12 col-sm-6">
                  <label class="form-label small fw-bold text-dark mb-1">CPF (Opcional)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-person-vcard-fill"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control border-start-0 ps-1"
                      :value="editForm.document"
                      @input="onEditDocumentInput"
                      placeholder="000.000.000-00"
                    >
                  </div>
                </div>
              </div>

              <!-- Nova Senha Opcional -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">
                  Nova Senha <span class="text-muted fw-normal">(deixe em branco se não quiser alterar)</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-key-fill"></i>
                  </span>
                  <input
                    type="password"
                    class="form-control border-start-0 ps-1"
                    v-model="editForm.password"
                    placeholder="Preencha apenas se for redefinir"
                    minlength="6"
                  >
                </div>
              </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0">
              <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" @click="showEditModal = false">
                Cancelar
              </button>
              <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm" :disabled="savingAdmin">
                <span v-if="savingAdmin" class="spinner-border spinner-border-sm me-1"></span>
                Salvar Alterações
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL MODERNO DE FEEDBACK (Substitui alert() nativo)     -->
    <!-- ======================================================== -->
    <div v-if="showFeedbackModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1080;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div
              :class="[
                'rounded-circle p-3 mb-2 d-flex align-items-center justify-content-center',
                feedbackData.type === 'success' ? 'bg-success bg-opacity-10 text-success' : (feedbackData.type === 'warning' ? 'bg-warning bg-opacity-15 text-warning' : 'bg-danger bg-opacity-10 text-danger')
              ]"
              style="width: 58px; height: 58px;"
            >
              <i
                :class="[
                  'fs-1',
                  feedbackData.type === 'success' ? 'bi bi-check-circle-fill text-success' : (feedbackData.type === 'warning' ? 'bi bi-exclamation-triangle-fill text-warning' : 'bi bi-x-circle-fill text-danger')
                ]"
              ></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">{{ feedbackData.title }}</h5>
          </div>

          <div class="modal-body px-4 py-2 text-center">
            <p class="text-muted small mb-0 lh-base">
              {{ feedbackData.message }}
            </p>
          </div>

          <div class="modal-footer border-0 px-4 pb-4 pt-3">
            <button
              type="button"
              :class="[
                'btn rounded-pill w-100 fw-bold btn-sm py-2 shadow-sm',
                feedbackData.type === 'success' ? 'btn-success text-white' : (feedbackData.type === 'warning' ? 'btn-warning text-dark' : 'btn-danger text-white')
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
import { maskName, maskPhone, maskDocument, onlyDigits, isValidCpf, applyMask } from '../utils/masks';

export default {
  name: 'AdminSettings',
  props: {
    currentUser: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      currentTab: 'store', // 'store' ou 'admins'
      savingStore: false,
      loadingAdmins: false,
      savingAdmin: false,

      // Dados da Loja & WhatsApp
      storeSettings: {
        store_whatsapp: '(83) 99999-9999',
        store_info: {
          name: 'AKY LANCHES',
          slogan: 'Cardápio Digital & Pedidos',
          address: 'Rua Principal, Centro - Teixeira - PB',
          hours: 'Todos os dias, das 18h00 às 23h30',
          document: '',
        },
      },

      // Administradores
      admins: [],

      // Modal de Criação de Admin
      showCreateModal: false,
      createForm: {
        name: '',
        email: '',
        phone: '',
        document: '',
        password: '',
      },

      // Modal de Edição de Admin
      showEditModal: false,
      editForm: {
        id: null,
        name: '',
        email: '',
        phone: '',
        document: '',
        password: '',
      },

      // Modal Moderno de Notificação
      showFeedbackModal: false,
      feedbackData: {
        title: '',
        message: '',
        type: 'success',
      },
    };
  },
  computed: {
    activeAdminsCount() {
      return this.admins.filter(a => a.is_active).length;
    },
    isCreateCpfValid() {
      const digits = onlyDigits(this.createForm.document);
      if (digits.length === 0) return null;
      if (digits.length === 11) return isValidCpf(digits);
      return false;
    },
  },
  mounted() {
    this.fetchStoreInfo();
    this.fetchAdmins();
  },
  methods: {
    openFeedback(title, message, type = 'success') {
      this.feedbackData = { title, message, type };
      this.showFeedbackModal = true;
    },
    formatDate(dateStr) {
      if (!dateStr) return 'Recente';
      const d = new Date(dateStr);
      return d.toLocaleDateString('pt-BR');
    },

    // Inputs com Máscaras
    onStorePhoneInput(e) {
      applyMask(e, maskPhone, val => {
        this.storeSettings.store_whatsapp = val;
      });
    },
    onCreateNameInput(e) {
      applyMask(e, maskName, val => {
        this.createForm.name = val;
      });
    },
    onCreatePhoneInput(e) {
      applyMask(e, maskPhone, val => {
        this.createForm.phone = val;
      });
    },
    onCreateDocumentInput(e) {
      applyMask(e, maskDocument, val => {
        this.createForm.document = val;
      });
    },
    onEditNameInput(e) {
      applyMask(e, maskName, val => {
        this.editForm.name = val;
      });
    },
    onEditPhoneInput(e) {
      applyMask(e, maskPhone, val => {
        this.editForm.phone = val;
      });
    },
    onEditDocumentInput(e) {
      applyMask(e, maskDocument, val => {
        this.editForm.document = val;
      });
    },

    // Buscar Informações da Loja
    async fetchStoreInfo() {
      try {
        const res = await axios.get('/api/admin/store-info');
        if (res.data) {
          if (res.data.store_whatsapp) {
            this.storeSettings.store_whatsapp = res.data.store_whatsapp;
          }
          if (res.data.store_info) {
            this.storeSettings.store_info = {
              ...this.storeSettings.store_info,
              ...res.data.store_info,
            };
          }
        }
      } catch (err) {
        console.error(err);
      }
    },

    // Salvar Informações da Loja
    async saveStoreSettings() {
      this.savingStore = true;
      try {
        const res = await axios.post('/api/admin/store-info', this.storeSettings);
        if (res.data && res.data.store_info) {
          this.storeSettings.store_info = {
            ...this.storeSettings.store_info,
            ...res.data.store_info,
          };
        }
        this.$emit('menu-updated');
        this.openFeedback('Dados Salvos!', 'As informações da lanchonete e o WhatsApp foram salvos com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Salvar', err.response?.data?.message || 'Não foi possível salvar os dados da lanchonete.', 'danger');
      } finally {
        this.savingStore = false;
      }
    },

    // Buscar Administradores
    async fetchAdmins() {
      this.loadingAdmins = true;
      try {
        const res = await axios.get('/api/admin/admins');
        this.admins = res.data || [];
      } catch (err) {
        console.error(err);
      } finally {
        this.loadingAdmins = false;
      }
    },

    // Modal de Criação de Admin
    openCreateAdminModal() {
      this.createForm = {
        name: '',
        email: '',
        phone: '',
        document: '',
        password: '',
      };
      this.showCreateModal = true;
    },

    // Salvar Novo Admin
    async saveNewAdmin() {
      if (this.isCreateCpfValid === false) {
        this.openFeedback('CPF Inválido', 'O CPF informado contém erro nos dígitos verificadores. Verifique os números digitados.', 'warning');
        return;
      }

      this.savingAdmin = true;
      try {
        const res = await axios.post('/api/admin/admins', this.createForm);
        this.showCreateModal = false;
        await this.fetchAdmins();
        this.openFeedback('Admin Cadastrado!', res.data.message || 'Administrador cadastrado com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Cadastrar', err.response?.data?.message || 'Verifique os dados preenchidos e tente novamente.', 'danger');
      } finally {
        this.savingAdmin = false;
      }
    },

    // Modal de Edição de Admin
    openEditAdminModal(admin) {
      this.editForm = {
        id: admin.id,
        name: admin.name,
        email: admin.email,
        phone: admin.phone || '',
        document: admin.document || '',
        password: '',
      };
      this.showEditModal = true;
    },

    // Atualizar Admin
    async updateAdmin() {
      const docDigits = onlyDigits(this.editForm.document);
      if (docDigits.length > 0 && docDigits.length === 11 && !isValidCpf(docDigits)) {
        this.openFeedback('CPF Inválido', 'O CPF informado contém erro nos dígitos verificadores.', 'warning');
        return;
      }

      this.savingAdmin = true;
      try {
        const payload = {
          name: this.editForm.name,
          email: this.editForm.email,
          phone: this.editForm.phone,
          document: this.editForm.document,
        };
        if (this.editForm.password && this.editForm.password.trim().length >= 6) {
          payload.password = this.editForm.password.trim();
        }

        const res = await axios.put(`/api/admin/users/${this.editForm.id}`, payload);
        this.showEditModal = false;
        await this.fetchAdmins();
        this.openFeedback('Dados Atualizados!', res.data.message || 'Dados do administrador atualizados com sucesso.', 'success');
      } catch (err) {
        this.openFeedback('Erro ao Atualizar', err.response?.data?.message || 'Não foi possível atualizar o administrador.', 'danger');
      } finally {
        this.savingAdmin = false;
      }
    },

    // Alternar Ativo/Bloqueado
    async toggleAdminActive(admin) {
      if (admin.is_active && this.activeAdminsCount <= 1) {
        this.openFeedback('Ação Bloqueada', 'Não é possível desativar o único administrador ativo do sistema.', 'warning');
        return;
      }

      try {
        await axios.patch(`/api/admin/attendants/${admin.id}/toggle`);
        admin.is_active = !admin.is_active;
        this.openFeedback('Status Atualizado', `Acesso de ${admin.name} foi ${admin.is_active ? 'liberado' : 'bloqueado'}.`, 'success');
      } catch (err) {
        this.openFeedback('Erro', err.response?.data?.message || 'Não foi possível alterar o status do administrador.', 'danger');
      }
    },
  },
};
</script>

<style scoped>
.transition {
  transition: all 0.2s ease-in-out;
}
</style>


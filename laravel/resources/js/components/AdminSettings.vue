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

      <button
        v-else-if="currentTab === 'whatsapp'"
        class="btn btn-success rounded-pill btn-sm px-3 fw-semibold shadow-sm d-flex align-items-center gap-1.5"
        @click="openCreateMessageModal"
      >
        <i class="bi bi-plus-circle-fill"></i>
        <span>Nova Mensagem WhatsApp</span>
      </button>
    </div>

    <!-- SELETOR DE ABAS MODERNO & RESPONSIVO (Segmented Control) -->
    <div class="bg-white p-2 rounded-4 shadow-sm border mb-4" style="max-width: 680px;">
      <div class="row g-2">
        <div class="col-12 col-sm-4">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'store' ? 'btn-aky-primary text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'store'"
          >
            <i class="bi bi-shop me-1"></i> Dados da Loja
          </button>
        </div>
        <div class="col-12 col-sm-4">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'whatsapp' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'whatsapp'"
          >
            <i class="bi bi-whatsapp me-1"></i> Mensagens WhatsApp ({{ whatsappMessages.length }})
          </button>
        </div>
        <div class="col-12 col-sm-4">
          <button
            :class="['btn w-100 rounded-3 py-2 btn-sm fw-bold border-0 transition text-truncate', currentTab === 'admins' ? 'btn-dark text-white shadow-sm' : 'btn-light text-dark']"
            @click="currentTab = 'admins'"
          >
            <i class="bi bi-shield-lock-fill me-1"></i> Admins ({{ admins.length }})
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

      <!-- Card Regras Operacionais de Entrega & Conclusão -->
      <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-bicycle text-primary fs-4"></i>
              <span>Regras de Entrega e Conclusão de Pedidos</span>
            </h5>
            <small class="text-muted">Defina permissões operacionais para a equipe e entregadores</small>
          </div>
          <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1.5 fw-semibold small flex-shrink-0">
            <i class="bi bi-sliders me-1"></i> Controle de Fluxo
          </span>
        </div>

        <div class="p-3 bg-light rounded-4 border">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div style="max-width: 80%;">
              <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                <i class="bi bi-shield-check text-success"></i>
                <span>Exigir confirmação do entregador para concluir pedidos de entrega</span>
              </h6>
              <small class="text-muted d-block" style="font-size: 0.75rem;">
                Quando <strong>ativada</strong>, um pedido de entrega só poderá ser marcado como "Concluído" pelo próprio motoboy no painel dele. Quando <strong>desativada</strong>, administradores e atendentes podem concluir qualquer pedido diretamente pelo painel. (Pedidos para retirar no balcão nunca são bloqueados por esta regra).
              </small>
            </div>

            <div class="form-check form-switch mb-0 fs-5">
              <input
                class="form-check-input"
                type="checkbox"
                id="req-moto-confirm"
                v-model="storeSettings.require_motoboy_confirmation"
              >
              <label class="form-check-label fs-6 fw-bold ms-1" :class="storeSettings.require_motoboy_confirmation ? 'text-success' : 'text-secondary'" for="req-moto-confirm">
                {{ storeSettings.require_motoboy_confirmation ? 'Ativada' : 'Desativada' }}
              </label>
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
    <!-- ABA 2: GESTÃO COMPLETA DE MENSAGENS DO WHATSAPP DE PEDIDOS -->
    <!-- ======================================================== -->
    <div v-else-if="currentTab === 'whatsapp'">
      <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-sm-center flex-wrap gap-2 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-whatsapp text-success fs-4"></i>
              <span>Gestão Completa de Mensagens de Pedido (WhatsApp)</span>
            </h5>
            <small class="text-muted">Crie e edite as mensagens que o sistema envia ao cliente por etapa de pedido sem mexer em código</small>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold shadow-sm"
              @click="restoreDefaultWhatsAppTemplates"
            >
              <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar Padrões
            </button>
            <button
              type="button"
              class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm d-flex align-items-center gap-1.5"
              @click="openCreateMessageModal"
            >
              <i class="bi bi-plus-circle-fill"></i>
              <span>Nova Mensagem</span>
            </button>
          </div>
        </div>

        <!-- Grade de Cards com as Mensagens do Sistema Cadastradas -->
        <div class="row g-3">
          <div
            v-for="(msg, idx) in whatsappMessages"
            :key="'wmsg-' + msg.id"
            class="col-12 col-md-6 col-lg-4"
          >
            <div :class="['card border-0 border-start border-3 shadow-sm rounded-4 h-100 bg-light p-3 d-flex flex-column justify-content-between', getTriggerBorderClass(msg.trigger), !msg.is_active ? 'opacity-75' : '']">
              <div>
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                  <div>
                    <span :class="['badge rounded-pill px-2.5 py-1 mb-1 fw-bold', getTriggerBadgeClass(msg.trigger)]">
                      {{ getTriggerLabel(msg.trigger) }}
                    </span>
                    <h6 class="fw-bold text-dark mb-0 text-truncate" :title="msg.title">{{ msg.title }}</h6>
                  </div>
                  
                  <div class="d-flex align-items-center gap-1">
                    <button
                      class="btn btn-sm btn-white border rounded-pill px-2 py-1 text-primary shadow-sm"
                      @click="openEditMessageModal(idx)"
                      title="Editar esta mensagem"
                    >
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button
                      v-if="!msg.is_system"
                      class="btn btn-sm btn-white border rounded-pill px-2 py-1 text-danger shadow-sm"
                      @click="deleteCustomMessage(idx)"
                      title="Excluir mensagem personalizada"
                    >
                      <i class="bi bi-trash3-fill"></i>
                    </button>
                  </div>
                </div>

                <small class="text-muted d-block mb-2" style="font-size: 0.73rem;">{{ msg.description || 'Mensagem configurada para notificações de pedidos.' }}</small>

                <!-- Pré-visualização do texto -->
                <div class="bg-white rounded-3 p-2.5 small font-monospace text-dark border mb-3 overflow-hidden" style="max-height: 120px; font-size: 0.72rem; white-space: pre-line;">
                  {{ msg.text }}
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <span class="small fw-semibold text-muted">Status do Envio:</span>
                <div class="form-check form-switch mb-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    :id="'msg-act-' + msg.id"
                    v-model="msg.is_active"
                    @change="saveWhatsAppMessagesFast"
                  >
                  <label class="form-check-label small fw-semibold" :class="msg.is_active ? 'text-success' : 'text-danger'" :for="'msg-act-' + msg.id">
                    {{ msg.is_active ? 'Ativa' : 'Desativada' }}
                  </label>
                </div>
              </div>

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
            <span>Salvar Todas as Mensagens</span>
          </button>
        </div>
      </div>
    </div>
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
    <!-- MODAL: EDITAR ADMINISTRADOR (COM NAVEGAÇÃO POR SETAS)    -->
    <!-- ======================================================== -->
    <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <!-- Cabeçalho Dinâmico com Setas de Navegação e Logo AKY Lanches -->
          <div
            class="modal-header text-white border-0 py-3 px-3 px-sm-4 d-flex align-items-center justify-content-between"
            style="background: linear-gradient(135deg, #111 0%, #333 100%);"
          >
            <!-- Título com Logo e Subtítulo -->
            <div class="d-flex align-items-center gap-2 min-w-0 me-2">
              <img src="/images/aky_lanches_icon.svg" alt="Aky Lanches" class="rounded-circle bg-white p-1 shadow-sm flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
              <div class="min-w-0">
                <h5 class="modal-title fw-bold text-white mb-0 text-truncate">
                  Editar Administrador
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
                  class="btn btn-sm btn-link text-white p-0 d-flex align-items-center justify-content-center"
                  :disabled="editIndex <= 0"
                  @click="prevAdmin"
                  title="Administrador anterior"
                  style="width: 28px; height: 28px; text-decoration: none;"
                >
                  <i class="bi bi-chevron-left fw-bold"></i>
                </button>

                <!-- Contador de Admins -->
                <span class="text-white fw-bold px-1.5 small user-select-none" style="font-size: 0.75rem;">
                  {{ editIndex + 1 }} / {{ admins.length }}
                </span>

                <!-- Seta Próximo -->
                <button
                  type="button"
                  class="btn btn-sm btn-link text-white p-0 d-flex align-items-center justify-content-center"
                  :disabled="editIndex >= admins.length - 1"
                  @click="nextAdmin"
                  title="Próximo administrador"
                  style="width: 28px; height: 28px; text-decoration: none;"
                >
                  <i class="bi bi-chevron-right fw-bold"></i>
                </button>
              </div>

              <!-- Botão Fechar -->
              <button type="button" class="btn-close btn-close-white ms-1" @click="showEditModal = false"></button>
            </div>
          </div>

          <form @submit.prevent="updateAdmin">
            <div class="modal-body p-3 p-sm-4">
              
              <!-- Alerta de Sucesso -->
              <div v-if="editSuccess" class="alert alert-success py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>Dados do administrador atualizados com sucesso!</div>
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
                  <div v-if="isEditCpfValid === true" class="text-success small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> CPF matematicamente válido
                  </div>
                  <div v-else-if="isEditCpfValid === false" class="text-danger small mt-1 d-flex align-items-center" style="font-size: 0.72rem;">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> CPF inválido. Verifique os números digitados.
                  </div>
                </div>
              </div>

              <!-- Nova Senha Opcional -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label small fw-bold text-dark mb-0">Nova Senha</label>
                  <small class="text-muted" style="font-size: 0.72rem;">Deixe vazio para manter a atual</small>
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
                  >
                </div>
              </div>

              <!-- Status Ativo / Inativo Switch -->
              <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                <div>
                  <div class="fw-bold text-dark small">Status do Acesso</div>
                  <small class="text-muted" style="font-size: 0.74rem;">Permite ou bloqueia a entrada do administrador</small>
                </div>
                <div class="form-check form-switch mb-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="edit-admin-status"
                    v-model="editForm.is_active"
                    :disabled="currentUser && currentUser.id === editForm.id && activeAdminsCount <= 1"
                  >
                  <label class="form-check-label small fw-bold ms-1" :class="editForm.is_active ? 'text-success' : 'text-danger'" for="edit-admin-status">
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
              <button type="submit" class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto" :disabled="savingAdmin">
                <span v-if="savingAdmin" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check2-circle me-1"></i>
                Salvar Alterações
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: CRIAR OU EDITAR MENSAGEM DO WHATSAPP DE PEDIDO   -->
    <!-- ======================================================== -->
    <div v-if="showMsgModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          
          <div
            class="modal-header text-white border-0 py-3 px-3 px-sm-4 d-flex align-items-center justify-content-between"
            style="background: linear-gradient(135deg, #198754 0%, #157347 100%);"
          >
            <div class="d-flex align-items-center gap-2 min-w-0 me-2">
              <img src="/images/aky_lanches_icon.svg" alt="Aky Lanches" class="rounded-circle bg-white p-1 shadow-sm flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
              <div class="min-w-0">
                <h5 class="modal-title fw-bold text-white mb-0 text-truncate">
                  {{ isEditingMsg ? 'Editar Mensagem WhatsApp' : 'Nova Mensagem WhatsApp' }}
                </h5>
                <small class="text-white-50 d-block text-truncate" style="font-size: 0.78rem;">
                  Mensagens automáticas de atendimento por WhatsApp
                </small>
              </div>
            </div>

            <!-- Navegação por setas se estiver editando -->
            <div v-if="isEditingMsg" class="d-flex align-items-center gap-1 flex-shrink-0">
              <div class="bg-black bg-opacity-25 rounded-pill p-1 d-flex align-items-center gap-1 shadow-sm">
                <button
                  type="button"
                  class="btn btn-sm btn-link text-white p-0 d-flex align-items-center justify-content-center"
                  :disabled="editMsgIndex <= 0"
                  @click="prevMsg"
                  style="width: 28px; height: 28px; text-decoration: none;"
                >
                  <i class="bi bi-chevron-left fw-bold"></i>
                </button>
                <span class="text-white fw-bold px-1.5 small user-select-none" style="font-size: 0.75rem;">
                  {{ editMsgIndex + 1 }} / {{ whatsappMessages.length }}
                </span>
                <button
                  type="button"
                  class="btn btn-sm btn-link text-white p-0 d-flex align-items-center justify-content-center"
                  :disabled="editMsgIndex >= whatsappMessages.length - 1"
                  @click="nextMsg"
                  style="width: 28px; height: 28px; text-decoration: none;"
                >
                  <i class="bi bi-chevron-right fw-bold"></i>
                </button>
              </div>
              <button type="button" class="btn-close btn-close-white ms-1" @click="showMsgModal = false"></button>
            </div>
            <button v-else type="button" class="btn-close btn-close-white" @click="showMsgModal = false"></button>
          </div>

          <form @submit.prevent="saveMessageFromModal">
            <div class="modal-body p-3 p-sm-4 bg-light">
              <div class="row g-3">
                
                <!-- Título da Mensagem -->
                <div class="col-12 col-sm-7">
                  <label class="form-label small fw-bold text-dark mb-1">Título da Mensagem *</label>
                  <input
                    type="text"
                    class="form-control rounded-3"
                    v-model="msgForm.title"
                    placeholder="Ex: Pedido a Caminho"
                    required
                  >
                </div>

                <!-- Gatilho / Etapa do Pedido -->
                <div class="col-12 col-sm-5">
                  <label class="form-label small fw-bold text-dark mb-1">Etapa / Gatilho *</label>
                  <select class="form-select rounded-3" v-model="msgForm.trigger" required>
                    <option value="recebido">Status: Recebido</option>
                    <option value="em_preparacao">Status: Em Preparação</option>
                    <option value="saiu_para_entrega">Status: Saiu para Entrega</option>
                    <option value="pronto_retirada">Retirada: Pronto no Balcão</option>
                    <option value="concluido">Status: Concluído</option>
                    <option value="cancelado">Status: Cancelado</option>
                    <option value="avulsa">Mensagem Rápida / Opcional</option>
                  </select>
                </div>

                <!-- Descrição Curta -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-dark mb-1">Descrição / Finalidade</label>
                  <input
                    type="text"
                    class="form-control rounded-3"
                    v-model="msgForm.description"
                    placeholder="Ex: Mensagem enviada avisando que o motoboy saiu com o troco"
                  >
                </div>

                <!-- Texto da Mensagem -->
                <div class="col-12">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">Texto da Mensagem no WhatsApp *</label>
                    <small class="text-muted" style="font-size: 0.72rem;">Use asteriscos para *negrito* e underline para _itálico_</small>
                  </div>
                  <textarea
                    class="form-control font-monospace rounded-3 bg-white"
                    rows="8"
                    v-model="msgForm.text"
                    placeholder="Escreva a mensagem aqui..."
                    required
                  ></textarea>

                  <!-- Tags dinâmicas clicáveis para inserir no texto -->
                  <div class="mt-2">
                    <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Inserir tags inteligentes no texto:</small>
                    <div class="d-flex flex-wrap gap-1">
                      <button
                        v-for="tag in ['{cliente}', '{codigo}', '{status}', '{endereco}', '{total}', '{pagamento}', '{troco}', '{entregador}', '{link_rastreio}']"
                        :key="tag"
                        type="button"
                        class="btn btn-sm btn-white border rounded-pill py-0.5 px-2 text-dark font-monospace shadow-sm"
                        style="font-size: 0.7rem;"
                        @click="insertTagInModal(tag)"
                      >
                        + {{ tag }}
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Status Ativa/Desativada -->
                <div class="col-12">
                  <div class="p-2.5 bg-white rounded-3 d-flex justify-content-between align-items-center border">
                    <div>
                      <span class="small fw-bold text-dark">Mensagem Ativa no Sistema</span>
                      <small class="text-muted d-block" style="font-size: 0.7rem;">Quando desativada, não aparecerá como sugestão de envio</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" id="modal-msg-act" v-model="msgForm.is_active">
                      <label class="form-check-label small fw-bold ms-1" :class="msgForm.is_active ? 'text-success' : 'text-danger'" for="modal-msg-act">
                        {{ msgForm.is_active ? 'Ativa' : 'Desativada' }}
                      </label>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <div class="modal-footer border-top bg-white p-3 d-flex flex-column-reverse flex-sm-row justify-content-between gap-2">
              <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-muted fw-semibold w-100 w-sm-auto" @click="showMsgModal = false">
                Cancelar
              </button>
              <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto">
                <i class="bi bi-check2-circle me-1"></i>
                Salvar Mensagem
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
    <div v-if="showFeedbackModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1080;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div
              :class="[
                'rounded-circle p-3 mb-2 d-flex align-items-center justify-content-center',
                feedbackData.type === 'success' ? 'bg-success bg-opacity-10 text-success' : (feedbackData.type === 'warning' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-danger bg-opacity-10 text-danger')
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
import { showConfirm, showSuccess } from '../utils/swal';

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
        require_motoboy_confirmation: true,
        whatsapp_order_template: '',
        whatsapp_status_template: '',
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

      // Gestão de Mensagens WhatsApp
      whatsappMessages: [],
      showMsgModal: false,
      isEditingMsg: false,
      editMsgIndex: 0,
      msgForm: {
        id: '',
        title: '',
        trigger: 'recebido',
        description: '',
        text: '',
        is_active: true,
        is_system: false,
      },

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
      editIndex: 0,
      editSuccess: false,
      editError: null,
      editForm: {
        id: null,
        name: '',
        email: '',
        phone: '',
        document: '',
        password: '',
        is_active: true,
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
    isEditCpfValid() {
      if (!this.editForm.document) return null;
      const digits = onlyDigits(this.editForm.document);
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
    // MÉTODOS DE GESTÃO COMPLETA DE MENSAGENS DO WHATSAPP
    getTriggerLabel(trigger) {
      const map = {
        recebido: 'Status: Recebido',
        em_preparacao: 'Status: Em Preparação',
        saiu_para_entrega: 'Status: A Caminho',
        pronto_retirada: 'Retirada: Pronto no Balcão',
        concluido: 'Status: Concluído',
        cancelado: 'Status: Cancelado',
        avulsa: 'Mensagem Opcional / Rápida',
      };
      return map[trigger] || trigger;
    },
    getTriggerBadgeClass(trigger) {
      const map = {
        recebido: 'bg-warning text-dark',
        em_preparacao: 'bg-primary text-white',
        saiu_para_entrega: 'bg-info text-dark',
        pronto_retirada: 'bg-dark text-warning',
        concluido: 'bg-success text-white',
        cancelado: 'bg-danger text-white',
        avulsa: 'bg-secondary text-white',
      };
      return map[trigger] || 'bg-light text-dark';
    },
    getTriggerBorderClass(trigger) {
      const map = {
        recebido: 'border-warning',
        em_preparacao: 'border-primary',
        saiu_para_entrega: 'border-info',
        pronto_retirada: 'border-dark',
        concluido: 'border-success',
        cancelado: 'border-danger',
        avulsa: 'border-secondary',
      };
      return map[trigger] || 'border-secondary';
    },
    openCreateMessageModal() {
      this.isEditingMsg = false;
      this.msgForm = {
        id: 'msg_custom_' + Date.now(),
        title: '',
        trigger: 'recebido',
        description: '',
        text: '',
        is_active: true,
        is_system: false,
      };
      this.showMsgModal = true;
    },
    openEditMessageModal(index) {
      this.isEditingMsg = true;
      this.editMsgIndex = index;
      const target = this.whatsappMessages[index];
      if (target) {
        this.msgForm = JSON.parse(JSON.stringify(target));
      }
      this.showMsgModal = true;
    },
    prevMsg() {
      if (this.editMsgIndex > 0) {
        this.editMsgIndex--;
        const target = this.whatsappMessages[this.editMsgIndex];
        if (target) this.msgForm = JSON.parse(JSON.stringify(target));
      }
    },
    nextMsg() {
      if (this.editMsgIndex < this.whatsappMessages.length - 1) {
        this.editMsgIndex++;
        const target = this.whatsappMessages[this.editMsgIndex];
        if (target) this.msgForm = JSON.parse(JSON.stringify(target));
      }
    },
    insertTagInModal(tag) {
      this.msgForm.text = (this.msgForm.text || '') + ' ' + tag;
    },
    saveMessageFromModal() {
      if (!this.msgForm.title.trim() || !this.msgForm.text.trim()) {
        this.openFeedback('Campos Obrigatórios', 'Por favor, informe o título e o texto da mensagem.', 'warning');
        return;
      }
      if (this.isEditingMsg) {
        if (this.whatsappMessages[this.editMsgIndex]) {
          this.whatsappMessages[this.editMsgIndex] = JSON.parse(JSON.stringify(this.msgForm));
        }
      } else {
        this.whatsappMessages.push(JSON.parse(JSON.stringify(this.msgForm)));
      }
      this.showMsgModal = false;
      this.saveWhatsAppMessagesFast();
      this.openFeedback('Mensagem Salva!', 'Modelo de mensagem salvo com sucesso.', 'success');
    },
    async deleteCustomMessage(index) {
      const msg = this.whatsappMessages[index];
      if (!msg) return;
      const confirmed = await showConfirm(
        'Excluir Mensagem?',
        `Tem certeza que deseja excluir o modelo "${msg.title}"?`,
        'Sim, Excluir',
        'Cancelar'
      );
      if (!confirmed) return;
      this.whatsappMessages.splice(index, 1);
      this.saveWhatsAppMessagesFast();
      showSuccess('Mensagem Excluída', 'O modelo de mensagem foi removido com sucesso.');
    },
    async saveWhatsAppMessagesFast() {
      try {
        await axios.post('/api/admin/store-info', {
          ...this.storeSettings,
          whatsapp_messages: this.whatsappMessages,
        });
      } catch (e) {
        console.error(e);
      }
    },
    getDefaultMessages() {
      return [
        {
          id: 'msg_recebido',
          title: 'Pedido Recebido',
          trigger: 'recebido',
          description: 'Enviada quando o pedido acaba de ser confirmado no sistema.',
          text: "Olá, *{cliente}*! 👋\n\nRecebemos o seu pedido na *AKY LANCHES*!\n\n📋 *Pedido:* #{codigo}\n📍 *Status atual:* *Recebido e confirmado!*\n💰 *Total:* R$ {total}\n{pagamento}\n\n🔍 *Acompanhe em tempo real pelo link:*\n{link_rastreio}\n\nJá estamos organizando sua comanda. Em instantes começa a preparação! 🍔🍟",
          is_system: true,
          is_active: true,
        },
        {
          id: 'msg_em_preparacao',
          title: 'Pedido em Preparação',
          trigger: 'em_preparacao',
          description: 'Enviada quando a cozinha inicia o preparo dos itens.',
          text: "Olá, *{cliente}*! 👨‍🍳\n\nSeu pedido *#{codigo}* acabou de entrar na chapa e na cozinha da *AKY LANCHES*!\n\n🔥 Estamos montando e fritando tudo com o maior capricho.\n💰 *Total:* R$ {total}\n{pagamento}\n\n🔍 *Link de acompanhamento em tempo real:*\n{link_rastreio}\n\nLogo logo estará prontinho! 🥤🍔",
          is_system: true,
          is_active: true,
        },
        {
          id: 'msg_saiu_para_entrega',
          title: 'Saiu para Entrega (Com Motoboy)',
          trigger: 'saiu_para_entrega',
          description: 'Enviada quando o pedido sai para entrega com o motoboy.',
          text: "Partiu! 🛵 Olá, *{cliente}*!\n\nSeu pedido *#{codigo}* acabou de sair quentinho para entrega!\n\n{entregador}\n📍 *Destino:* {endereco}\n{pagamento}{troco}\n💰 *Total a pagar:* R$ {total}\n\n🔍 *Acompanhe a rota em tempo real:*\n{link_rastreio}\n\nFique atento à campainha ou interfone. Já já chega aí! 🍔🍟",
          is_system: true,
          is_active: true,
        },
        {
          id: 'msg_pronto_retirada',
          title: 'Pronto para Retirada no Balcão',
          trigger: 'pronto_retirada',
          description: 'Enviada para pedidos marcados para Retirar no Local quando estiverem prontos.',
          text: "Seu pedido está pronto! 🛍️ Olá, *{cliente}*!\n\nO pedido *#{codigo}* já está embalado e te esperando no balcão da *AKY LANCHES*!\n\n📍 *Local de Retirada:* {endereco}\n💰 *Total:* R$ {total}\n{pagamento}\n\nVenha buscar enquanto está quentinho e crocante! Te esperamos! 😊🍔",
          is_system: true,
          is_active: true,
        },
        {
          id: 'msg_concluido',
          title: 'Pedido Concluído / Agradecimento',
          trigger: 'concluido',
          description: 'Enviada após a entrega ou retirada confirmada.',
          text: "Pedido entregue com sucesso! 🎉\n\nOlá, *{cliente}*! Muito obrigado por escolher a *AKY LANCHES*!\n\nEsperamos que seu lanche esteja delicioso! Bom apetite! 😋🍔🥤\n\nQualquer dúvida ou feedback estamos sempre à disposição por aqui. Até a próxima!",
          is_system: true,
          is_active: true,
        },
        {
          id: 'msg_cancelado',
          title: 'Pedido Cancelado',
          trigger: 'cancelado',
          description: 'Enviada caso o pedido precise ser cancelado.',
          text: "Aviso sobre seu pedido: ⚠️\n\nOlá, *{cliente}*. Informamos que o pedido *#{codigo}* precisou ser cancelado.\n\nSe tiver qualquer dúvida ou se precisar refazer o pedido com alguma alteração, é só responder esta mensagem que nossa equipe te atende na hora! 🙏",
          is_system: true,
          is_active: true,
        },
      ];
    },
    async restoreDefaultWhatsAppTemplates() {
      const confirmed = await showConfirm(
        'Restaurar Modelos?',
        'Deseja restaurar todas as mensagens de pedido do WhatsApp para os modelos padrão do sistema?',
        'Sim, Restaurar',
        'Cancelar'
      );
      if (!confirmed) return;
      this.whatsappMessages = this.getDefaultMessages();
      this.saveWhatsAppMessagesFast();
      showSuccess('Padrões Restaurados', 'Todas as mensagens originais de pedidos foram restauradas.');
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
          if (res.data.require_motoboy_confirmation !== undefined) {
            this.storeSettings.require_motoboy_confirmation = !!res.data.require_motoboy_confirmation;
          }
          if (res.data.whatsapp_messages && Array.isArray(res.data.whatsapp_messages)) {
            this.whatsappMessages = res.data.whatsapp_messages;
          } else {
            this.whatsappMessages = this.getDefaultMessages();
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
        const payload = {
          ...this.storeSettings,
          whatsapp_messages: this.whatsappMessages,
        };
        const res = await axios.post('/api/admin/store-info', payload);
        if (res.data) {
          if (res.data.store_info) {
            this.storeSettings.store_info = {
              ...this.storeSettings.store_info,
              ...res.data.store_info,
            };
          }
          if (res.data.store_whatsapp) {
            this.storeSettings.store_whatsapp = res.data.store_whatsapp;
          }
          if (res.data.require_motoboy_confirmation !== undefined) {
            this.storeSettings.require_motoboy_confirmation = !!res.data.require_motoboy_confirmation;
          }
          if (res.data.whatsapp_messages && Array.isArray(res.data.whatsapp_messages)) {
            this.whatsappMessages = res.data.whatsapp_messages;
          }
        }
        this.$emit('menu-updated');
        this.openFeedback('Dados Salvos!', 'As informações da lanchonete e as mensagens do WhatsApp foram salvas com sucesso.', 'success');
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
      const idx = this.admins.findIndex(a => a.id === admin.id);
      this.editIndex = idx >= 0 ? idx : 0;
      this.loadAdminIntoEditForm(this.admins[this.editIndex]);
      this.showEditModal = true;
    },

    loadAdminIntoEditForm(admin) {
      if (!admin) return;
      this.editForm = {
        id: admin.id,
        name: admin.name,
        email: admin.email,
        phone: admin.phone || '',
        document: admin.document || '',
        password: '',
        is_active: !!admin.is_active,
      };
      this.editError = null;
      this.editSuccess = false;
    },

    prevAdmin() {
      if (this.editIndex > 0) {
        this.editIndex--;
        this.loadAdminIntoEditForm(this.admins[this.editIndex]);
      }
    },

    nextAdmin() {
      if (this.editIndex < this.admins.length - 1) {
        this.editIndex++;
        this.loadAdminIntoEditForm(this.admins[this.editIndex]);
      }
    },

    // Atualizar Admin
    async updateAdmin() {
      this.editError = null;
      this.editSuccess = false;

      this.editForm.name = maskName(this.editForm.name);
      this.editForm.phone = maskPhone(this.editForm.phone);
      this.editForm.document = maskDocument(this.editForm.document);

      if (!this.editForm.name.trim()) {
        this.editError = 'Nome completo inválido. Digite apenas letras.';
        return;
      }

      if (this.isEditCpfValid === false) {
        this.editError = 'O CPF informado contém erro nos dígitos verificadores.';
        return;
      }

      this.savingAdmin = true;
      try {
        const payload = {
          name: this.editForm.name,
          email: this.editForm.email,
          phone: this.editForm.phone,
          document: this.editForm.document,
          is_active: this.editForm.is_active,
        };
        if (this.editForm.password && this.editForm.password.trim().length >= 6) {
          payload.password = this.editForm.password.trim();
        }

        const res = await axios.put(`/api/admin/users/${this.editForm.id}`, payload);
        this.editSuccess = true;
        this.editForm.password = '';

        const updated = res.data.user;
        if (this.admins[this.editIndex]) {
          Object.assign(this.admins[this.editIndex], updated);
        }

        setTimeout(() => {
          this.editSuccess = false;
        }, 2500);
      } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
          this.editError = err.response.data.message;
        } else {
          this.editError = 'Não foi possível atualizar o administrador.';
        }
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


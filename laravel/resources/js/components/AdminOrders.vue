<template>
  <div class="admin-orders pb-5">
    
    <!-- Cabeçalho do Painel de Pedidos -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center">
          <i class="bi bi-kanban-fill me-2 text-aky-primary"></i> Painel de Pedidos
        </h4>
        <small class="text-muted">Gerencie a esteira de atendimento da cozinha e entrega</small>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button
          v-if="paginatedOrders.length > 0"
          type="button"
          class="btn btn-outline-dark rounded-pill btn-sm px-3 shadow-sm bg-white d-flex align-items-center gap-1.5 transition"
          @click="toggleAllOrdersExpand"
          :title="areAllVisibleExpanded ? 'Recolher todos os cards visíveis' : 'Expandir todos os cards visíveis'"
        >
          <i :class="areAllVisibleExpanded ? 'bi bi-arrows-collapse' : 'bi bi-arrows-expand'"></i>
          <span class="d-none d-sm-inline">{{ areAllVisibleExpanded ? 'Recolher Todos' : 'Expandir Todos' }}</span>
        </button>

        <button class="btn btn-outline-secondary rounded-pill btn-sm px-3 shadow-sm bg-white" @click="fetchOrders" :disabled="loading">
          <i class="bi bi-arrow-clockwise me-1" :class="{'spin': loading}"></i> Atualizar
        </button>
      </div>
    </div>

    <!-- BOTÕES DE FILTRO COM ESTILO MODERNO (CHIPS COM ÍCONES E CONTADORES) -->
    <div class="d-flex gap-2 overflow-auto pb-3 mb-4 filter-chips">
      
      <!-- Todos -->
      <button
        :class="['btn rounded-pill px-3 py-2 btn-sm fw-bold d-flex align-items-center gap-2 border shadow-sm transition', selectedStatus === '' ? 'btn-dark text-white' : 'btn-white text-dark']"
        @click="selectedStatus = ''"
      >
        <i class="bi bi-collection-fill"></i>
        <span>Todos</span>
        <span class="badge rounded-pill bg-light text-dark border">{{ statusCounts.all }}</span>
      </button>

      <!-- Recebidos -->
      <button
        :class="['btn rounded-pill px-3 py-2 btn-sm fw-bold d-flex align-items-center gap-2 border shadow-sm transition', selectedStatus === 'recebido' ? 'btn-warning text-dark' : 'btn-white text-dark']"
        @click="selectedStatus = 'recebido'"
      >
        <i class="bi bi-receipt text-warning"></i>
        <span>Recebidos</span>
        <span class="badge rounded-pill" :class="selectedStatus === 'recebido' ? 'bg-dark text-white' : 'bg-warning bg-opacity-25 text-dark'">
          {{ statusCounts.recebido }}
        </span>
      </button>

      <!-- Em Preparo -->
      <button
        :class="['btn rounded-pill px-3 py-2 btn-sm fw-bold d-flex align-items-center gap-2 border shadow-sm transition', selectedStatus === 'em_preparacao' ? 'btn-primary text-white' : 'btn-white text-dark']"
        @click="selectedStatus = 'em_preparacao'"
      >
        <i class="bi bi-fire text-primary"></i>
        <span>Em Preparo</span>
        <span class="badge rounded-pill" :class="selectedStatus === 'em_preparacao' ? 'bg-white text-primary' : 'bg-primary bg-opacity-25 text-primary'">
          {{ statusCounts.em_preparacao }}
        </span>
      </button>

      <!-- Saiu para Entrega -->
      <button
        :class="['btn rounded-pill px-3 py-2 btn-sm fw-bold d-flex align-items-center gap-2 border shadow-sm transition', selectedStatus === 'saiu_para_entrega' ? 'btn-info text-dark' : 'btn-white text-dark']"
        @click="selectedStatus = 'saiu_para_entrega'"
      >
        <i class="bi bi-bicycle text-info"></i>
        <span>A Caminho</span>
        <span class="badge rounded-pill" :class="selectedStatus === 'saiu_para_entrega' ? 'bg-dark text-white' : 'bg-info bg-opacity-25 text-dark'">
          {{ statusCounts.saiu_para_entrega }}
        </span>
      </button>

      <!-- Concluídos -->
      <button
        :class="['btn rounded-pill px-3 py-2 btn-sm fw-bold d-flex align-items-center gap-2 border shadow-sm transition', selectedStatus === 'concluido' ? 'btn-success text-white' : 'btn-white text-dark']"
        @click="selectedStatus = 'concluido'"
      >
        <i class="bi bi-check-circle-fill text-success"></i>
        <span>Concluídos</span>
        <span class="badge rounded-pill" :class="selectedStatus === 'concluido' ? 'bg-white text-success' : 'bg-success bg-opacity-25 text-success'">
          {{ statusCounts.concluido }}
        </span>
      </button>

      <!-- Cancelados -->
      <button
        :class="['btn rounded-pill px-3 py-2 btn-sm fw-bold d-flex align-items-center gap-2 border shadow-sm transition', selectedStatus === 'cancelado' ? 'btn-danger text-white' : 'btn-white text-dark']"
        @click="selectedStatus = 'cancelado'"
      >
        <i class="bi bi-x-circle-fill text-danger"></i>
        <span>Cancelados</span>
        <span class="badge rounded-pill" :class="selectedStatus === 'cancelado' ? 'bg-white text-danger' : 'bg-danger bg-opacity-25 text-danger'">
          {{ statusCounts.cancelado }}
        </span>
      </button>

    </div>

    <!-- Estado de Carregamento -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-aky-primary" role="status"></div>
      <p class="text-muted small mt-2">Atualizando pedidos...</p>
    </div>

    <!-- Lista Vazia -->
    <div v-else-if="filteredOrders.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
      <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
      <h6 class="text-muted fw-semibold">Nenhum pedido encontrado nesta visualização</h6>
    </div>

    <!-- Grade de Cards de Pedidos Operacionais (Expansíveis) -->
    <div v-else class="row g-3">
      <div v-for="order in paginatedOrders" :key="order.id" class="col-12 col-md-6 col-lg-4">
        <div :class="['card border-0 border-start border-3 shadow-sm rounded-4 bg-white overflow-hidden transition', statusBorderClass(order.status), order.status === 'cancelado' ? 'opacity-75' : '']">
          
          <!-- Cabeçalho Limpo e Direto: Apenas Código, Horário e Botão Expandir -->
          <div class="card-header bg-transparent border-0 pt-3 px-3 pb-1 d-flex justify-content-between align-items-center">
            <span
              class="badge font-monospace fs-6 px-2.5 py-1 shadow-sm rounded-pill cursor-pointer"
              :class="statusBadgeClass(order.status)"
              @click="toggleOrderExpand(order.id)"
              :title="isOrderExpanded(order.id) ? 'Recolher detalhes' : 'Expandir detalhes'"
            >
              {{ order.order_number }}
            </span>

            <div class="d-flex align-items-center gap-1.5">
              <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ formatTime(order.created_at) }}</small>
              
              <!-- Botão Discreto de Impressão de Cupom para a Cozinha -->
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-none text-muted transition"
                style="width: 28px; height: 28px;"
                @click.stop="printKitchenTicket(order)"
                title="Imprimir cupom descritivo para a cozinha"
              >
                <i class="bi bi-printer"></i>
              </button>

              <button
                type="button"
                class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-none expand-toggle-btn transition"
                style="width: 28px; height: 28px;"
                @click="toggleOrderExpand(order.id)"
                :title="isOrderExpanded(order.id) ? 'Recolher detalhes do pedido' : 'Expandir detalhes do pedido'"
              >
                <i class="bi" :class="isOrderExpanded(order.id) ? 'bi-chevron-up text-primary fw-bold' : 'bi-chevron-down text-muted'"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-3 pt-1">
            <!-- Linha Principal Super Limpa: Nome do Cliente e Total -->
            <div class="d-flex justify-content-between align-items-center gap-2 mb-1 cursor-pointer" @click="toggleOrderExpand(order.id)">
              <h6 class="fw-bold text-dark mb-0 text-truncate" :title="order.customer_name">
                {{ order.customer_name }}
              </h6>
              <span class="fw-bold text-aky-primary fs-6 flex-shrink-0">
                R$ {{ formatPrice(order.total) }}
              </span>
            </div>

            <!-- Botão Minimalista para Expandir / Recolher Detalhes -->
            <div class="d-flex align-items-center justify-content-between pb-1">
              <button
                type="button"
                class="btn btn-sm btn-link p-0 text-decoration-none small text-muted d-inline-flex align-items-center gap-1 transition"
                style="font-size: 0.74rem;"
                @click="toggleOrderExpand(order.id)"
              >
                <span>{{ isOrderExpanded(order.id) ? 'Recolher detalhes' : 'Mais detalhes' }}</span>
                <i class="bi" :class="isOrderExpanded(order.id) ? 'bi-chevron-up text-primary' : 'bi-chevron-down text-primary'"></i>
              </button>

              <span v-if="!isOrderExpanded(order.id)" class="text-muted" style="font-size: 0.72rem;">
                {{ getOrderItemsCount(order) }} {{ getOrderItemsCount(order) === 1 ? 'item' : 'itens' }}
              </span>
            </div>

            <!-- ======================================================== -->
            <!-- CONTEÚDO DETALHADO DO PEDIDO (EXPANSÍVEL)                -->
            <!-- ======================================================== -->
            <div v-if="isOrderExpanded(order.id)" class="pt-2 border-top mt-1 animate-expand">
              
              <!-- Badges de Identificação Operacional (Exibidas Apenas Quando Expandido) -->
              <div class="d-flex align-items-center gap-1 flex-wrap mb-2.5" style="font-size: 0.72rem;">
                <span v-if="order.delivery_address && order.delivery_address.includes('RETIRADA')" class="badge bg-dark rounded-pill px-2 py-1">
                  <i class="bi bi-shop text-warning me-1"></i>Balcão (Retirada)
                </span>
                <span v-else class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1">
                  <i class="bi bi-bicycle me-1"></i>Entrega em Domicílio
                </span>

                <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                  🛒 {{ getOrderItemsCount(order) }} {{ getOrderItemsCount(order) === 1 ? 'item' : 'itens' }}
                </span>

                <span :class="paymentBadgeClass(order.payment_method)" class="badge rounded-pill px-2 py-1">
                  <i :class="paymentIconClass(order.payment_method)" class="me-1"></i>
                  {{ paymentLabel(order.payment_method) }}
                </span>

                <span v-if="order.motoboy" class="badge bg-light text-primary border rounded-pill px-2 py-1 text-truncate" style="max-width: 140px;">
                  <i class="bi bi-bicycle me-1"></i>{{ order.motoboy.name.split(' ')[0] }}
                </span>

                <span v-if="order.food_rating" class="badge bg-warning bg-opacity-25 text-dark rounded-pill px-2 py-1">
                  ⭐ {{ order.food_rating }}★
                </span>
              </div>
              
              <!-- Telefone e Notificação WhatsApp -->
              <div class="small text-muted mb-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                <span class="d-inline-flex align-items-center gap-1">
                  <i class="bi bi-telephone text-aky-primary"></i>
                  <span :class="isPhoneSuspect(order.customer_phone) ? 'text-danger fw-bold' : ''">{{ order.customer_phone }}</span>
                  <button
                    type="button"
                    class="btn btn-sm btn-link p-0 text-muted"
                    title="Editar número de telefone do pedido"
                    @click="openEditPhoneModal(order)"
                  >
                    <i class="bi bi-pencil-square text-primary"></i>
                  </button>
                </span>

                <button
                  v-if="isPhoneSuspect(order.customer_phone)"
                  type="button"
                  class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-bold d-inline-flex align-items-center shadow-sm"
                  style="font-size: 0.73rem;"
                  title="Número de teste ou formato inválido. Clique para corrigir antes de enviar para o WhatsApp."
                  @click="openEditPhoneModal(order)"
                >
                  <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> Corrigir WhatsApp
                </button>

                <a
                  v-else-if="order.customer_phone"
                  :href="getCustomerWhatsAppUrl(order)"
                  target="_blank"
                  class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5 fw-bold d-inline-flex align-items-center text-decoration-none shadow-sm"
                  style="font-size: 0.73rem;"
                  title="Notificar cliente no WhatsApp"
                >
                  <i class="bi bi-whatsapp me-1 text-success"></i> WhatsApp
                </a>
              </div>

              <!-- Endereço -->
              <div class="small text-secondary mb-3">
                <span v-if="order.delivery_address && order.delivery_address.includes('RETIRADA')" class="badge bg-dark rounded-pill px-2.5 py-1 mb-1 d-inline-flex align-items-center">
                  <i class="bi bi-shop me-1 text-warning"></i> Retirada no Estabelecimento
                </span>
                <div v-else>
                  <i class="bi bi-geo-alt me-1 text-aky-primary"></i> {{ order.delivery_address }}
                  <span v-if="order.delivery_complement"> ({{ order.delivery_complement }})</span>
                </div>
              </div>

              <!-- Itens do Pedido -->
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-bold text-dark">Itens do Pedido ({{ getOrderItemsCount(order) }}):</span>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 fw-semibold d-inline-flex align-items-center gap-1 shadow-none"
                  style="font-size: 0.72rem;"
                  @click.stop="printKitchenTicket(order)"
                  title="Reimprimir cupom descritivo para a cozinha"
                >
                  <i class="bi bi-printer text-dark"></i> Imprimir Cozinha
                </button>
              </div>
              <div class="bg-light rounded-3 p-2 small mb-3">
                <div v-for="item in order.items" :key="item.id" class="d-flex justify-content-between text-dark py-1">
                  <span>{{ item.quantity }}x {{ item.product_name_snapshot }}</span>
                  <span class="text-muted">R$ {{ formatPrice(item.subtotal) }}</span>
                </div>
                <div v-if="order.notes" class="mt-2 pt-2 border-top text-danger fst-italic">
                  <i class="bi bi-chat-left-dots me-1"></i> Obs: {{ order.notes }}
                </div>
              </div>

              <!-- Forma de Pagamento e Detalhes de Troco -->
              <div class="p-2 rounded-3 bg-light border small mb-3">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted" style="font-size: 0.72rem;">Pagamento:</span>
                  <span :class="paymentBadgeClass(order.payment_method)" class="badge rounded-pill fw-semibold">
                    <i :class="paymentIconClass(order.payment_method)" class="me-1"></i>
                    {{ paymentLabel(order.payment_method) }}
                  </span>
                </div>

                <!-- Detalhes de Troco para Dinheiro -->
                <div v-if="order.payment_method === 'dinheiro'" class="mt-1 pt-1 border-top" style="font-size: 0.72rem;">
                  <div v-if="order.needs_change && order.change_for" class="d-flex justify-content-between align-items-center text-dark">
                    <span>Troco p/ R$ {{ formatPrice(order.change_for) }}:</span>
                    <span class="fw-bold text-danger">Devolver R$ {{ formatPrice(order.change_for - order.total) }}</span>
                  </div>
                  <div v-else class="text-muted">
                    <i class="bi bi-check2 text-success me-1"></i> Não precisa de troco
                  </div>
                </div>
              </div>

              <!-- Atribuição de Motoboy -->
              <div class="mb-3">
                <!-- Se for pedido de retirada no estabelecimento -->
                <div v-if="order.delivery_address && order.delivery_address.includes('RETIRADA')" class="p-2.5 rounded-pill bg-light border text-center d-flex align-items-center justify-content-center gap-2">
                  <i class="bi bi-shop text-dark fs-5"></i>
                  <span class="small fw-bold text-dark">Retirada no Balcão (Sem Entregador)</span>
                </div>

                <!-- Se for pedido de entrega tradicional -->
                <button
                  v-else
                  class="btn btn-sm w-100 rounded-pill py-2 d-flex align-items-center justify-content-between px-3 border shadow-sm transition"
                  :class="order.motoboy ? 'btn-white text-dark border-primary' : 'btn-light text-muted'"
                  :disabled="order.status === 'concluido'"
                  @click="openMotoboyModal(order)"
                >
                  <div class="d-flex align-items-center text-truncate">
                    <i class="bi bi-bicycle fs-5 me-2" :class="order.motoboy ? 'text-primary' : 'text-muted'"></i>
                    <div class="text-start text-truncate">
                      <small class="text-muted d-block" style="font-size: 0.68rem;">Entregador:</small>
                      <span class="fw-bold small">{{ order.motoboy ? order.motoboy.name : 'Nenhum entregador atribuído' }}</span>
                    </div>
                  </div>
                  <span v-if="order.status !== 'concluido'" class="badge rounded-pill bg-primary bg-opacity-10 text-primary ms-2">
                    {{ order.motoboy ? 'Alterar' : 'Escolher' }}
                  </span>
                  <span v-else class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary ms-2" title="Bloqueado para alterações">
                    <i class="bi bi-lock-fill"></i>
                  </span>
                </button>
              </div>

              <!-- Confirmação Antifraude e Avaliação -->
              <div class="p-2 rounded-3 bg-light border small mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span style="font-size: 0.72rem;">
                    <i class="bi bi-bicycle text-info me-1"></i> <strong>Motoboy:</strong>
                  </span>
                  <span v-if="order.delivery_address && order.delivery_address.includes('RETIRADA')" class="badge bg-secondary" style="font-size: 0.68rem;">Disp. (Retirada)</span>
                  <span v-else-if="order.motoboy_confirmed_at" class="badge bg-success" style="font-size: 0.68rem;">Confirmou ✓</span>
                  <span v-else class="badge bg-secondary" style="font-size: 0.68rem;">Pendente ⏳</span>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <span style="font-size: 0.72rem;">
                    <i class="bi bi-person-check text-primary me-1"></i> <strong>Cliente:</strong>
                  </span>
                  <span v-if="order.customer_confirmed_at" class="badge bg-success" style="font-size: 0.68rem;">Confirmou ✓</span>
                  <span v-else class="badge bg-warning text-dark" style="font-size: 0.68rem;">Pendente ⏳</span>
                </div>

                <!-- Se houver avaliação de lanche / entrega -->
                <div v-if="order.food_rating" class="mt-2 pt-2 border-top">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark" style="font-size: 0.72rem;">⭐ Avaliação:</span>
                    <div class="text-warning small" style="font-size: 0.75rem;">
                      <span>🍔 {{ order.food_rating }}★</span>
                      <span class="ms-1.5">🛵 {{ order.delivery_rating }}★</span>
                    </div>
                  </div>
                  <div v-if="order.review_comment" class="text-muted fst-italic p-1.5 bg-white rounded border" style="font-size: 0.7rem; line-height: 1.2;">
                    "{{ order.review_comment }}"
                  </div>
                </div>
              </div>

            </div>

            <!-- Rodapé do Card: Status Atual e Botão de Ação (Sempre Visível) -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1">
              <div class="d-flex align-items-center gap-1">
                <small class="text-muted" style="font-size: 0.7rem;">Status:</small>
                <span class="fw-semibold text-dark small" style="font-size: 0.74rem;">
                  {{ statusLabel(order.status, order.delivery_address && order.delivery_address.includes('RETIRADA')) }}
                </span>
              </div>

              <div class="d-flex align-items-center gap-2">
                <!-- Se pedido Cancelado, botão de apagar -->
                <button
                  v-if="order.status === 'cancelado'"
                  class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold shadow-sm d-flex align-items-center gap-1"
                  @click="openDeleteModal(order)"
                  title="Excluir pedido cancelado do sistema"
                >
                  <i class="bi bi-trash3-fill"></i> Apagar
                </button>

                <!-- Se pedido Concluído, mostra selo bloqueado -->
                <span
                  v-if="order.status === 'concluido'"
                  class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center"
                >
                  <i class="bi bi-lock-fill me-1"></i> Concluído
                </span>

                <!-- Menu Suspenso de Mudança de Status (com modal de confirmação) -->
                <div v-else class="dropdown">
                  <button
                    class="btn btn-sm rounded-pill dropdown-toggle px-3 py-1 fw-bold shadow-sm"
                    :class="statusButtonClass(order.status)"
                    type="button"
                    data-bs-toggle="dropdown"
                  >
                    Mudar Status
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2">
                    <li><h6 class="dropdown-header small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Mudar Status:</h6></li>
                    <li>
                      <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" @click.prevent="requestStatusChange(order, 'recebido')">
                        <span class="p-1 rounded-circle bg-warning d-inline-block"></span> <span>Recebido</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" @click.prevent="requestStatusChange(order, 'em_preparacao')">
                        <span class="p-1 rounded-circle bg-primary d-inline-block"></span> <span>Em Preparação</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" @click.prevent="requestStatusChange(order, 'saiu_para_entrega')">
                        <span class="p-1 rounded-circle bg-info d-inline-block"></span>
                        <span>{{ order.delivery_address && order.delivery_address.includes('RETIRADA') ? 'Pronto p/ Retirada' : 'Saiu para Entrega' }}</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" @click.prevent="requestStatusChange(order, 'concluido')">
                        <span class="p-1 rounded-circle bg-success d-inline-block"></span> <span>Concluído</span>
                      </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                      <a class="dropdown-item rounded-3 py-2 text-danger d-flex align-items-center gap-2" href="#" @click.prevent="requestStatusChange(order, 'cancelado')">
                        <i class="bi bi-x-circle-fill"></i> <span>Cancelar Pedido</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- BARRA DE PAGINAÇÃO MOBILE-FIRST OTIMIZADA (Padrão AdminProducts) -->
    <div v-if="filteredOrders.length > itemsPerPage" class="card border-0 shadow-sm rounded-4 p-3 bg-white mt-4">
      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
        
        <!-- Informações de Contagem -->
        <div class="text-muted small fw-semibold text-center text-sm-start">
          Mostrando <span class="text-dark fw-bold">{{ paginationStart }}</span> a <span class="text-dark fw-bold">{{ paginationEnd }}</span> de <span class="text-dark fw-bold">{{ filteredOrders.length }}</span> pedidos
        </div>

        <!-- Controles de Navegação Confortáveis -->
        <div class="d-flex align-items-center gap-2">
          <button
            class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1 shadow-none"
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
          >
            <i class="bi bi-chevron-left"></i>
            <span>Anterior</span>
          </button>

          <div class="d-flex align-items-center gap-1 px-1">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold" style="font-size: 0.82rem;">
              {{ currentPage }} de {{ totalPages }}
            </span>
          </div>

          <button
            class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1 shadow-none"
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
          >
            <span>Próxima</span>
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>

      </div>
    </div>

    <!-- MODAL DE SELEÇÃO DE MOTOBOY COM BARRA DE BUSCA EM TEMPO REAL -->
    <div v-if="showMotoboyModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0 rounded-4 overflow-hidden">
          
          <div class="modal-header bg-primary text-white border-0 py-3">
            <h6 class="modal-title fw-bold d-flex align-items-center">
              <i class="bi bi-bicycle me-2 fs-5"></i> Selecionar Entregador
            </h6>
            <button type="button" class="btn-close btn-close-white shadow-none" @click="showMotoboyModal = false"></button>
          </div>

          <div class="modal-body p-3 bg-light">
            
            <!-- Barra de Busca no Modal -->
            <div class="input-group input-group-sm mb-3 shadow-sm rounded-pill overflow-hidden bg-white border">
              <span class="input-group-text bg-white border-0 ps-3">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-0 shadow-none ps-2"
                placeholder="Buscar por nome do motoboy..."
                v-model="motoboySearchTerm"
              >
            </div>

            <!-- Botão para Desvincular / Sem Motoboy -->
            <div class="mb-2">
              <button
                class="btn btn-outline-secondary btn-sm w-100 rounded-pill py-2 text-start px-3 d-flex align-items-center justify-content-between"
                @click="assignSelectedMotoboy(null)"
                :disabled="selectedOrderForMotoboy && selectedOrderForMotoboy.status === 'saiu_para_entrega'"
              >
                <span><i class="bi bi-x-circle me-1 text-danger"></i> Deixar sem entregador</span>
                <i class="bi bi-chevron-right small text-muted"></i>
              </button>
              <small v-if="selectedOrderForMotoboy && selectedOrderForMotoboy.status === 'saiu_para_entrega'" class="text-danger d-block mt-1 ps-2" style="font-size: 0.7rem;">
                * Pedidos a caminho da entrega devem ter um motoboy atribuído.
              </small>
            </div>

            <hr class="my-2 opacity-25">

            <!-- Lista de Motoboys em Cards Clicáveis -->
            <div class="d-flex flex-column gap-2" style="max-height: 260px; overflow-y: auto;">
              <div v-if="filteredMotoboys.length === 0" class="text-center py-4 text-muted small">
                Nenhum entregador encontrado com "{{ motoboySearchTerm }}".
              </div>

              <div
                v-for="m in filteredMotoboys"
                :key="'sel-moto-' + m.id"
                class="card border-0 shadow-sm rounded-3 p-2 bg-white cursor-pointer transition hover-card"
                :class="selectedOrderForMotoboy && selectedOrderForMotoboy.motoboy_id === m.id ? 'border-primary border-2 bg-primary bg-opacity-10' : ''"
                @click="assignSelectedMotoboy(m.id)"
              >
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2 min-w-0">
                    <div class="rounded-circle bg-warning bg-opacity-25 text-dark p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                      🛵
                    </div>
                    <div class="min-w-0">
                      <div class="fw-bold text-dark small text-truncate">{{ m.name }}</div>
                      <small class="text-muted d-block" style="font-size: 0.68rem;">{{ m.email }}</small>
                    </div>
                  </div>
                  <i class="bi bi-check-circle-fill text-primary fs-5" v-if="selectedOrderForMotoboy && selectedOrderForMotoboy.motoboy_id === m.id"></i>
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer border-0 p-2 bg-white text-center">
            <button class="btn btn-light btn-sm rounded-pill w-100 text-muted" @click="showMotoboyModal = false">
              Fechar
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO DE ALTERAÇÃO DE STATUS -->
    <div v-if="showConfirmStatusModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
              <i class="bi bi-question-circle-fill fs-2 text-warning"></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">Alterar Status</h5>
          </div>

          <div class="modal-body px-4 py-2 text-center">
            <p class="text-muted small mb-3">
              Deseja alterar o status do pedido <strong class="text-dark">{{ pendingStatusChange.order?.order_number }}</strong> de <strong class="text-dark">{{ pendingStatusChange.order?.customer_name }}</strong>?
            </p>

            <div class="p-3 bg-light rounded-4 mb-3 d-flex align-items-center justify-content-center gap-2">
              <span class="badge rounded-pill px-3 py-2" :class="statusBadgeClass(pendingStatusChange.order?.status)">
                {{ statusLabel(pendingStatusChange.order?.status) }}
              </span>
              <i class="bi bi-arrow-right text-muted fs-6"></i>
              <span class="badge rounded-pill px-3 py-2" :class="statusBadgeClass(pendingStatusChange.newStatus)">
                {{ pendingStatusChange.newStatusLabel }}
              </span>
            </div>

            <div v-if="pendingStatusChange.newStatus === 'concluido'" class="alert alert-warning border-0 rounded-3 py-2 px-3 small text-start d-flex flex-column gap-1 mb-0">
              <div class="d-flex gap-2 align-items-start">
                <i class="bi bi-shield-lock-fill text-warning flex-shrink-0 mt-1"></i>
                <div>
                  <strong>Atenção:</strong> Ao concluir o pedido, ele será bloqueado definitivamente.
                </div>
              </div>
              <div v-if="pendingStatusChange.order && pendingStatusChange.order.delivery_address && !pendingStatusChange.order.delivery_address.includes('RETIRADA')" class="mt-1 pt-1 border-top border-warning border-opacity-25" style="font-size: 0.72rem;">
                <span>🛵 Motoboy: <strong>{{ pendingStatusChange.order.motoboy_confirmed_at ? 'Confirmou ✓' : 'Pendente ⏳' }}</strong></span> | 
                <span>👤 Cliente: <strong>{{ pendingStatusChange.order.customer_confirmed_at ? 'Confirmou ✓' : 'Pendente ⏳' }}</strong></span>
                <div class="text-dark fw-bold mt-1">
                  ⚖️ Como Atendente/Admin, sua confirmação atua como desempate e validação final da entrega!
                </div>
              </div>
            </div>

            <div v-else-if="pendingStatusChange.newStatus === 'cancelado'" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start d-flex gap-2 align-items-start mb-0">
              <i class="bi bi-exclamation-triangle-fill text-danger flex-shrink-0 mt-1"></i>
              <div><strong>Aviso:</strong> O pedido será cancelado. Poderá ser excluído definitivamente depois.</div>
            </div>
          </div>

          <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="showConfirmStatusModal = false" :disabled="isSubmitting">
              Cancelar
            </button>
            <button type="button" class="btn btn-primary rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="confirmStatusChange" :disabled="isSubmitting">
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO DE PEDIDO CANCELADO -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-white border-0 pt-4 px-4 pb-2 text-center d-flex flex-column align-items-center">
            <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
              <i class="bi bi-trash3-fill fs-2 text-danger"></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">Apagar Pedido</h5>
          </div>

          <div class="modal-body px-4 py-2 text-center">
            <p class="text-muted small mb-2">
              Tem certeza que deseja apagar o pedido cancelado <strong class="text-dark">{{ orderToDelete?.order_number }}</strong> de <strong class="text-dark">{{ orderToDelete?.customer_name }}</strong>?
            </p>
            <div class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-0">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              Esta ação é permanente e removerá este pedido e seus itens do banco de dados.
            </div>
          </div>

          <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="showDeleteModal = false" :disabled="isSubmitting">
              Voltar
            </button>
            <button type="button" class="btn btn-danger rounded-pill flex-grow-1 fw-bold btn-sm py-2" @click="confirmDeleteOrder" :disabled="isSubmitting">
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
              Sim, Apagar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE EDITAR TELEFONE DO PEDIDO -->
    <div v-if="showPhoneModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-dark text-white p-3">
            <h6 class="modal-title fw-bold mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-telephone-fill text-warning"></i>
              <span>Editar Telefone do Pedido #{{ orderToEditPhone?.order_number }}</span>
            </h6>
            <button type="button" class="btn-close btn-close-white" @click="showPhoneModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <p class="small text-muted mb-3">
              Atualize o WhatsApp de <strong>{{ orderToEditPhone?.customer_name }}</strong> para enviar notificações e abrir conversa diretamente no aplicativo:
            </p>
            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Número de Telefone / WhatsApp</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-whatsapp text-success"></i>
                </span>
                <input
                  type="tel"
                  class="form-control border-start-0 ps-1"
                  v-model="newPhoneValue"
                  @input="handleEditPhoneInput"
                  placeholder="(83) 99999-9999"
                  required
                >
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light p-3 border-0 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" @click="showPhoneModal = false" :disabled="savingPhone">
              Cancelar
            </button>
            <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold" @click="saveOrderPhone" :disabled="savingPhone || !newPhoneValue">
              <span v-if="savingPhone" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-check2-circle me-1"></i> Salvar Telefone
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE NOTIFICAR CLIENTE NO WHATSAPP APÓS ALTERAÇÃO DE STATUS -->
    <div v-if="showNotifyWhatsAppModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.65); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
          <div class="modal-header bg-dark text-white p-3">
            <h6 class="modal-title fw-bold mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-whatsapp text-success fs-5"></i>
              <span>Notificar Cliente no WhatsApp?</span>
            </h6>
            <button type="button" class="btn-close btn-close-white" @click="showNotifyWhatsAppModal = false"></button>
          </div>
          <div class="modal-body p-4 text-center">
            <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
              <i class="bi bi-send-check-fill fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Status Atualizado com Sucesso!</h5>
            <p class="text-muted small mb-3">
              O pedido <strong>#{{ orderToNotifyWhatsApp?.order_number }}</strong> de <strong>{{ orderToNotifyWhatsApp?.customer_name }}</strong> agora está:
              <br>
              <span class="badge rounded-pill px-3 py-1 mt-1 fs-6" :class="statusButtonClass(orderToNotifyWhatsApp?.status)">
                {{ statusLabel(orderToNotifyWhatsApp?.status) }}
              </span>
            </p>
            <div v-if="orderToNotifyWhatsApp?.needs_change && orderToNotifyWhatsApp?.change_for" class="alert alert-warning border-0 rounded-3 py-2 px-3 small text-start mb-3">
              <i class="bi bi-cash-coin me-1"></i>
              <strong>Atenção ao Troco:</strong> O cliente pediu troco para R$ {{ formatPrice(orderToNotifyWhatsApp?.change_for) }} (Troco: R$ {{ formatPrice(Number(orderToNotifyWhatsApp?.change_for) - Number(orderToNotifyWhatsApp?.total)) }}). Essa informação já foi incluída automaticamente na mensagem do WhatsApp!
            </div>
            <!-- Seletor de Modelo de Mensagem -->
            <div class="mb-3 text-start bg-light p-2.5 rounded-3 border">
              <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-chat-quote-fill text-success me-1"></i> Modelo de Mensagem:</span>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.68rem;">Personalizado</span>
              </label>
              <select class="form-select form-select-sm rounded-3 fw-semibold" v-model="selectedMsgTemplateId">
                <option :value="null">Modelo Padrão do Sistema</option>
                <option v-for="tpl in customWhatsAppList" :key="'opt-' + tpl.id" :value="tpl.id">
                  {{ tpl.title }} ({{ tpl.trigger }})
                </option>
              </select>
            </div>

            <p class="small text-secondary mb-0">
              Deseja abrir o aplicativo do WhatsApp agora para notificar o cliente?
            </p>
          </div>
          <div class="modal-footer bg-light p-3 border-0 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" @click="showNotifyWhatsAppModal = false">
              Agora Não
            </button>
            <a
              :href="getCustomerWhatsAppUrl(orderToNotifyWhatsApp)"
              target="_blank"
              class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5"
              @click="showNotifyWhatsAppModal = false"
            >
              <i class="bi bi-whatsapp"></i>
              <span>Sim, Enviar no WhatsApp</span>
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import { maskPhone } from '../utils/masks';
import { showError, showSuccess } from '../utils/swal';
import { printThermalTicket } from '../utils/printer';

export default {
  data() {
    return {
      allOrders: [],
      motoboys: [],
      selectedStatus: '',
      loading: false,
      showMotoboyModal: false,
      selectedOrderForMotoboy: null,
      motoboySearchTerm: '',
      showConfirmStatusModal: false,
      pendingStatusChange: { order: null, newStatus: '', newStatusLabel: '' },
      showDeleteModal: false,
      orderToDelete: null,
      isSubmitting: false,
      pollingInterval: null,
      showPhoneModal: false,
      orderToEditPhone: null,
      newPhoneValue: '',
      savingPhone: false,
      showNotifyWhatsAppModal: false,
      orderToNotifyWhatsApp: null,
      customWhatsAppList: [],
      selectedMsgTemplateId: null,
      expandedOrderIds: [],
      printSettings: null,
      storeInfo: null,
      selectedMsgTemplateId: null,
      expandedOrderIds: [],

      // Paginação
      currentPage: 1,
      itemsPerPage: 9,
    };
  },
  watch: {
    selectedStatus() {
      this.currentPage = 1;
    },
  },
  computed: {
    areAllVisibleExpanded() {
      if (!this.paginatedOrders || this.paginatedOrders.length === 0) return false;
      return this.paginatedOrders.every(o => this.expandedOrderIds.includes(o.id));
    },
    statusCounts() {
      const counts = {
        all: this.allOrders.length,
        recebido: 0,
        em_preparacao: 0,
        saiu_para_entrega: 0,
        concluido: 0,
        cancelado: 0,
      };
      this.allOrders.forEach(o => {
        if (counts[o.status] !== undefined) {
          counts[o.status]++;
        }
      });
      return counts;
    },
    filteredOrders() {
      if (!this.selectedStatus) return this.allOrders;
      return this.allOrders.filter(o => o.status === this.selectedStatus);
    },
    totalPages() {
      return Math.ceil(this.filteredOrders.length / this.itemsPerPage) || 1;
    },
    paginatedOrders() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredOrders.slice(start, start + this.itemsPerPage);
    },
    paginationStart() {
      if (this.filteredOrders.length === 0) return 0;
      return (this.currentPage - 1) * this.itemsPerPage + 1;
    },
    paginationEnd() {
      return Math.min(this.currentPage * this.itemsPerPage, this.filteredOrders.length);
    },
    filteredMotoboys() {
      if (!this.motoboySearchTerm.trim()) return this.motoboys;
      const term = this.motoboySearchTerm.toLowerCase();
      return this.motoboys.filter(m =>
        m.name.toLowerCase().includes(term) ||
        (m.email && m.email.toLowerCase().includes(term))
      );
    },
  },
  mounted() {
    this.fetchOrders();
    this.fetchMotoboys();
    this.fetchWhatsAppTemplate();
    this.startPolling();
  },
  beforeUnmount() {
    this.stopPolling();
  },
  methods: {
    isOrderExpanded(orderId) {
      return this.expandedOrderIds.includes(orderId);
    },
    toggleOrderExpand(orderId) {
      const idx = this.expandedOrderIds.indexOf(orderId);
      if (idx > -1) {
        this.expandedOrderIds.splice(idx, 1);
      } else {
        this.expandedOrderIds.push(orderId);
      }
    },
    toggleAllOrdersExpand() {
      if (this.areAllVisibleExpanded) {
        const visibleIds = this.paginatedOrders.map(o => o.id);
        this.expandedOrderIds = this.expandedOrderIds.filter(id => !visibleIds.includes(id));
      } else {
        const visibleIds = this.paginatedOrders.map(o => o.id);
        const newSet = new Set([...this.expandedOrderIds, ...visibleIds]);
        this.expandedOrderIds = Array.from(newSet);
      }
    },
    getOrderItemsCount(order) {
      if (!order || !order.items) return 0;
      return order.items.reduce((sum, item) => sum + (Number(item.quantity) || 1), 0);
    },
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },
    startPolling() {
      // Polling autônomo a cada 15 segundos para atualizar pedidos sem travar a interface
      this.pollingInterval = setInterval(() => {
        this.fetchOrders(true);
      }, 15000);
    },
    stopPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
        this.pollingInterval = null;
      }
    },
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },
    paymentLabel(method) {
      const map = {
        dinheiro: 'Dinheiro',
        pix: 'Pix',
        cartao_credito: 'Cartão de Crédito',
        cartao_debito: 'Cartão de Débito',
      };
      return map[method] || 'Não informado';
    },
    paymentBadgeClass(method) {
      const map = {
        dinheiro: 'bg-success bg-opacity-10 text-success border border-success',
        pix: 'bg-primary bg-opacity-10 text-primary border border-primary',
        cartao_credito: 'bg-danger bg-opacity-10 text-danger border border-danger',
        cartao_debito: 'bg-warning bg-opacity-10 text-dark border border-warning',
      };
      return map[method] || 'bg-secondary bg-opacity-10 text-secondary border';
    },
    paymentIconClass(method) {
      const map = {
        dinheiro: 'bi bi-cash-stack',
        pix: 'bi bi-qr-code',
        cartao_credito: 'bi bi-credit-card-2-front',
        cartao_debito: 'bi bi-credit-card',
      };
      return map[method] || 'bi bi-wallet2';
    },
    async fetchMotoboys() {
      try {
        const res = await axios.get('/api/admin/motoboys');
        this.motoboys = res.data;
      } catch (err) {
        console.error(err);
      }
    },
    async fetchWhatsAppTemplate() {
      try {
        const res = await axios.get('/api/admin/store-info');
        if (res.data) {
          if (res.data.whatsapp_messages) {
            this.customWhatsAppList = res.data.whatsapp_messages;
          }
          if (res.data.print_settings) {
            this.printSettings = res.data.print_settings;
          }
          if (res.data.store_info) {
            this.storeInfo = res.data.store_info;
          }
        }
      } catch (err) {
        // silencioso
      }
    },
    async printKitchenTicket(order) {
      try {
        await printThermalTicket(order, this.printSettings || {}, this.storeInfo || {});
      } catch (err) {
        console.error('Falha ao acionar impressão de comanda de cozinha:', err);
      }
    },
    async fetchOrders(silent = false) {
      if (!silent) this.loading = true;
      try {
        const res = await axios.get('/api/admin/orders');
        this.allOrders = res.data.data || res.data;
      } catch (err) {
        console.error(err);
      } finally {
        if (!silent) this.loading = false;
      }
    },
    openMotoboyModal(order) {
      this.selectedOrderForMotoboy = order;
      this.motoboySearchTerm = '';
      this.showMotoboyModal = true;
    },
    async assignSelectedMotoboy(motoboyId) {
      if (!this.selectedOrderForMotoboy) return;
      const order = this.selectedOrderForMotoboy;

      try {
        const payload = { motoboy_id: motoboyId };
        let statusChanged = false;
        if (motoboyId && (order.status === 'recebido' || order.status === 'em_preparacao')) {
          payload.status = 'saiu_para_entrega';
          statusChanged = true;
        }

        await axios.patch(`/api/admin/orders/${order.id}/assign-motoboy`, payload);
        this.showMotoboyModal = false;
        if (statusChanged) {
          order.status = 'saiu_para_entrega';
        }
        await this.fetchOrders();
        this.openWhatsAppPromptModal(order);
      } catch (err) {
        showError('Erro ao atribuir motoboy', err.response?.data?.message || 'Não foi possível atribuir o motoboy.');
      }
    },
    requestStatusChange(order, newStatus) {
      if (order.status === 'concluido') return;

      const isPickup = order.delivery_address && order.delivery_address.includes('RETIRADA');

      // Regra Obrigatória: Pedidos de ENTREGA precisam de entregador para sair para entrega.
      // Pedidos de RETIRADA no balcão NÃO exigem motoboy e passam direto!
      if (newStatus === 'saiu_para_entrega' && !isPickup && !order.motoboy_id) {
        this.openMotoboyModal(order);
        return;
      }

      this.pendingStatusChange = {
        order,
        newStatus,
        newStatusLabel: this.statusLabel(newStatus, isPickup),
      };
      this.showConfirmStatusModal = true;
    },
    async confirmStatusChange() {
      if (!this.pendingStatusChange.order || !this.pendingStatusChange.newStatus) return;
      const order = this.pendingStatusChange.order;
      const newStatus = this.pendingStatusChange.newStatus;
      this.isSubmitting = true;
      try {
        await axios.patch(`/api/admin/orders/${order.id}/status`, {
          status: newStatus
        });
        order.status = newStatus;
        this.showConfirmStatusModal = false;
        this.pendingStatusChange = { order: null, newStatus: '', newStatusLabel: '' };
        await this.fetchOrders();
        this.openWhatsAppPromptModal(order);
      } catch (err) {
        showError('Erro ao atualizar status', err.response?.data?.message || 'Não foi possível atualizar o status do pedido.');
      } finally {
        this.isSubmitting = false;
      }
    },
    openDeleteModal(order) {
      this.orderToDelete = order;
      this.showDeleteModal = true;
    },
    async confirmDeleteOrder() {
      if (!this.orderToDelete) return;
      this.isSubmitting = true;
      try {
        await axios.delete(`/api/admin/orders/${this.orderToDelete.id}`);
        this.showDeleteModal = false;
        this.orderToDelete = null;
        this.fetchOrders();
        showSuccess('Pedido Excluído', 'O pedido cancelado foi excluído com sucesso.');
      } catch (err) {
        showError('Erro ao apagar pedido', err.response?.data?.message || 'Não foi possível apagar o pedido.');
      } finally {
        this.isSubmitting = false;
      }
    },
    statusBorderClass(status) {
      const map = {
        recebido: 'border-warning',
        em_preparacao: 'border-primary',
        saiu_para_entrega: 'border-info',
        concluido: 'border-success',
        cancelado: 'border-danger',
      };
      return map[status] || 'border-secondary';
    },
    statusLabel(status, isPickup = false) {
      if (status === 'saiu_para_entrega') {
        return isPickup ? 'Pronto p/ Retirada' : 'A Caminho';
      }
      const labels = {
        recebido: 'Recebido',
        em_preparacao: 'Em Preparo',
        saiu_para_entrega: isPickup ? 'Pronto p/ Retirada' : 'A Caminho',
        concluido: 'Concluído',
        cancelado: 'Cancelado',
      };
      return labels[status] || status;
    },
    statusBadgeClass(status) {
      const classes = {
        recebido: 'bg-warning text-dark',
        em_preparacao: 'bg-primary text-white',
        saiu_para_entrega: 'bg-info text-dark',
        concluido: 'bg-success text-white',
        cancelado: 'bg-danger text-white',
      };
      return classes[status] || 'bg-secondary text-white';
    },
    statusButtonClass(status) {
      const classes = {
        recebido: 'btn-warning text-dark',
        em_preparacao: 'btn-primary',
        saiu_para_entrega: 'btn-info text-dark',
        concluido: 'btn-success',
        cancelado: 'btn-danger',
      };
      return classes[status] || 'btn-secondary';
    },
    getCustomerWhatsAppUrl(order) {
      if (!order || !order.customer_phone) return '#';
      const digits = (order.customer_phone || '').replace(/\D/g, '');
      const phone = digits.startsWith('55') ? digits : ('55' + digits);
      const trackingUrl = `${window.location.origin}/?rastreio=${order.order_number}`;

      const isPickup = order.delivery_address && order.delivery_address.includes('RETIRADA');
      const statusLabels = {
        recebido: 'Recebido e aguardando confirmação',
        em_preparacao: 'Em preparação na cozinha 👨‍🍳',
        saiu_para_entrega: isPickup ? 'Pronto para retirada no balcão 🛍️' : 'Saiu para entrega com o entregador 🛵',
        concluido: 'Concluído e entregue com sucesso! 🎉',
        cancelado: 'Cancelado',
      };
      const statusText = statusLabels[order.status] || order.status;

      let paymentInfo = `💳 *Forma de Pagamento:* ${this.paymentLabel(order.payment_method)}`;
      let trocoInfo = '';
      if (order.payment_method === 'dinheiro') {
        if (order.needs_change && order.change_for) {
          const changeVal = Math.max(0, Number(order.change_for) - Number(order.total || 0));
          paymentInfo = `💵 *Pagamento:* Dinheiro (Troco para R$ ${this.formatPrice(order.change_for)})\n💰 *Valor do Troco:* R$ ${this.formatPrice(changeVal)}`;
          trocoInfo = `\n💰 *Valor do Troco:* R$ ${this.formatPrice(changeVal)}`;
          if (order.status === 'saiu_para_entrega' && !isPickup) {
            paymentInfo += `\n🛵 *Atenção:* O entregador já está levando seu troco de R$ ${this.formatPrice(changeVal)} em mãos!`;
          }
        } else {
          paymentInfo = `💵 *Pagamento:* Dinheiro (Não precisa de troco)`;
        }
      }

      const motoboyInfo = (order.status === 'saiu_para_entrega' && order.motoboy && !isPickup)
        ? `\n🏍️ *Entregador:* ${order.motoboy.name}`
        : '';

      // Busca o modelo customizado selecionado ou o compatível com a etapa do pedido
      let template = null;
      if (this.selectedMsgTemplateId && this.customWhatsAppList.length) {
        template = this.customWhatsAppList.find(m => m.id === this.selectedMsgTemplateId && m.is_active);
      }
      if (!template && this.customWhatsAppList.length) {
        if (isPickup && order.status === 'saiu_para_entrega') {
          template = this.customWhatsAppList.find(m => m.trigger === 'pronto_retirada' && m.is_active);
        }
        if (!template) {
          template = this.customWhatsAppList.find(m => m.trigger === order.status && m.is_active);
        }
      }

      let text = '';
      if (template && template.text && template.text.trim()) {
        text = template.text
          .replace(/{codigo}/g, order.order_number)
          .replace(/{cliente}/g, order.customer_name || 'Cliente')
          .replace(/{status}/g, statusText)
          .replace(/{endereco}/g, order.delivery_address || 'Teixeira - PB')
          .replace(/{total}/g, this.formatPrice(order.total))
          .replace(/{pagamento}/g, paymentInfo)
          .replace(/{troco}/g, trocoInfo)
          .replace(/{entregador}/g, motoboyInfo)
          .replace(/{link_rastreio}/g, trackingUrl);
      } else {
        text =
`Olá, *${order.customer_name}*! Atualização do seu pedido na *AKY LANCHES*:

📋 *Pedido:* #${order.order_number}
📍 *Status atual:* *${statusText}*
💰 *Total:* R$ ${this.formatPrice(order.total)}
${paymentInfo}${motoboyInfo}

🔍 *Link de acompanhamento em tempo real:*
${trackingUrl}

Qualquer dúvida estamos à disposição! 🍔🥤`;
      }

      return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
    },
    openWhatsAppPromptModal(order) {
      if (!order) return;
      this.orderToNotifyWhatsApp = order;

      // Pré-seleciona automaticamente a mensagem correspondente ao status
      const isPickup = order.delivery_address && order.delivery_address.includes('RETIRADA');
      if (isPickup && order.status === 'saiu_para_entrega') {
        const found = this.customWhatsAppList.find(m => m.trigger === 'pronto_retirada' && m.is_active);
        this.selectedMsgTemplateId = found ? found.id : null;
      } else {
        const found = this.customWhatsAppList.find(m => m.trigger === order.status && m.is_active);
        this.selectedMsgTemplateId = found ? found.id : null;
      }

      this.showNotifyWhatsAppModal = true;
    },
    isPhoneSuspect(phone) {
      if (!phone) return true;
      const clean = phone.replace(/\D/g, '');
      if (clean.length < 10) return true;
      if (clean.includes('99999999') || phone.includes('(99)')) return true;
      return false;
    },
    openEditPhoneModal(order) {
      this.orderToEditPhone = order;
      this.newPhoneValue = order.customer_phone || '';
      this.showPhoneModal = true;
    },
    handleEditPhoneInput(e) {
      this.newPhoneValue = maskPhone(e.target.value);
      e.target.value = this.newPhoneValue;
    },
    async saveOrderPhone() {
      if (!this.orderToEditPhone || !this.newPhoneValue) return;
      this.savingPhone = true;
      try {
        const res = await axios.patch(`/api/admin/orders/${this.orderToEditPhone.id}/phone`, {
          customer_phone: this.newPhoneValue,
        });
        this.orderToEditPhone.customer_phone = this.newPhoneValue;
        this.showPhoneModal = false;
        this.orderToEditPhone = null;
        this.newPhoneValue = '';
        showSuccess('Telefone Atualizado', 'O número de WhatsApp do cliente foi atualizado com sucesso.');
      } catch (err) {
        showError('Erro ao atualizar telefone', err.response?.data?.message || 'Não foi possível atualizar o telefone.');
      } finally {
        this.savingPhone = false;
      }
    },
  },
};
</script>

<style scoped>
.filter-chips::-webkit-scrollbar {
  height: 4px;
}
.filter-chips::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.15);
  border-radius: 4px;
}
.btn-white {
  background-color: #ffffff;
}
.btn-white:hover {
  background-color: #f8f9fa;
}
.hover-card:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
}
.spin {
  animation: rotation 1s infinite linear;
}
@keyframes rotation {
  from { transform: rotate(0deg); }
  to { transform: rotate(359deg); }
}

.expand-toggle-btn:hover {
  background-color: #e9ecef !important;
  transform: scale(1.08);
}

.animate-expand {
  animation: fadeInDown 0.25s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

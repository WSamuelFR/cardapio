<template>
  <div class="section-view pb-5">
    
    <!-- Cabeçalho com Botão Voltar e Título da Seção -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <button
          class="btn btn-light border rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm d-flex align-items-center gap-1 transition"
          @click="$emit('back')"
        >
          <i class="bi bi-arrow-left fs-6"></i>
          <span>Voltar</span>
        </button>

        <div>
          <h4 class="fw-bold text-dark mb-0 d-flex align-items-center flex-wrap gap-2">
            <i :class="[icon || 'bi-tag-fill', 'text-' + (themeColor || 'danger'), 'fs-4']"></i>
            <span>{{ title }}</span>
            <span v-if="badgeText" :class="['badge rounded-pill px-3 py-1 small', 'bg-' + (themeColor || 'danger'), themeColor === 'warning' ? 'text-dark' : 'text-white']">
              {{ badgeText }}
            </span>
          </h4>
          <small class="text-muted" v-if="subtitle">{{ subtitle }}</small>
        </div>
      </div>

      <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
        {{ filteredItems.length }} {{ filteredItems.length === 1 ? 'item' : 'itens' }}
      </span>
    </div>

    <!-- Barra de Pesquisa no Topo da Seção -->
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
      <div class="input-group">
        <span class="input-group-text bg-transparent border-0 text-muted ps-3">
          <i class="bi bi-search fs-5"></i>
        </span>
        <input
          type="text"
          class="form-control border-0 shadow-none ps-2"
          v-model="searchTerm"
          :placeholder="'Pesquisar em ' + title + '...'"
        >
        <button
          v-if="searchTerm"
          class="btn btn-transparent border-0 text-muted pe-3"
          @click="searchTerm = ''"
          title="Limpar busca"
        >
          <i class="bi bi-x-circle-fill"></i>
        </button>
      </div>
    </div>

    <!-- Estado Vazio (Sem resultados) -->
    <div v-if="filteredItems.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
      <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
      <h6 class="text-dark fw-bold mb-1">Nenhum produto encontrado</h6>
      <p class="text-muted small mb-3">Tente buscar por outro termo ou limpe a pesquisa.</p>
      <button v-if="searchTerm" class="btn btn-outline-secondary rounded-pill btn-sm px-3" @click="searchTerm = ''">
        Limpar Pesquisa
      </button>
    </div>

    <!-- Grade Completa de Cards -->
    <div v-else class="row g-3">
      <div
        v-for="item in filteredItems"
        :key="'sec-item-' + item.id"
        class="col-12 col-md-6 col-lg-4"
      >
        <div
          :class="['card aky-card h-100 bg-white shadow-sm rounded-4 cursor-pointer transition', !item.is_available ? 'opacity-75' : '', item.is_combo ? 'border-start border-3 border-success' : 'border-start border-3 border-danger']"
          @click="$emit('select-product', item, 'section')"
        >
          <div class="d-flex p-3 gap-3">
            
            <!-- Foto do Item -->
            <div class="product-thumb rounded-3 overflow-hidden flex-shrink-0 bg-light d-flex align-items-center justify-content-center border position-relative" style="width: 90px; height: 90px;">
              <img
                v-if="item.photo_path"
                :src="item.photo_path"
                :alt="item.name"
                class="w-100 h-100 object-fit-cover"
              >
              <div v-else class="text-center text-muted">
                <i :class="[icon || 'bi-tag-fill', 'fs-2 text-' + (themeColor || 'danger'), 'opacity-75 d-block']"></i>
                <span style="font-size: 0.65rem;" :class="['fw-semibold text-' + (themeColor || 'danger')]">
                  {{ item.is_combo ? 'Combo' : (item.is_promotion ? 'Promoção' : 'AKY') }}
                </span>
              </div>
            </div>

            <!-- Informações e Botão de Ação -->
            <div class="flex-grow-1 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-start gap-1">
                  <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem; max-width: 170px;">{{ item.name }}</h6>
                  <span v-if="!item.is_available" class="badge bg-secondary small" style="font-size: 0.65rem;">Esgotado</span>
                </div>
                <p class="text-muted small mb-1 text-truncate" style="font-size: 0.74rem; max-width: 170px;" :title="item.description">
                  {{ item.description || 'Preparo artesanal com os melhores ingredientes.' }}
                </p>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                <div>
                  <div v-if="item.is_promotion && item.promotional_price">
                    <span class="text-muted small text-decoration-line-through d-block" style="font-size: 0.70rem;">
                      R$ {{ formatPrice(item.price) }}
                    </span>
                    <span :class="['fw-bold fs-6', item.is_combo ? 'text-success' : 'text-danger']">
                      R$ {{ formatPrice(item.promotional_price) }}
                    </span>
                  </div>
                  <div v-else>
                    <span :class="['fw-bold fs-6', 'text-' + (themeColor || 'danger')]">
                      R$ {{ formatPrice(item.effective_price || item.price) }}
                    </span>
                  </div>
                </div>

                <button
                  :class="['btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0', item.is_combo ? 'btn-success' : 'btn-aky-primary']"
                  style="width: 34px; height: 34px;"
                  :disabled="!item.is_available"
                  title="Adicionar ao pedido"
                  @click.stop="$emit('add-to-cart', item)"
                >
                  <i class="bi bi-plus-lg fs-6"></i>
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'SectionView',
  props: {
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String,
      default: '',
    },
    icon: {
      type: String,
      default: 'bi-tag-fill',
    },
    themeColor: {
      type: String,
      default: 'danger',
    },
    badgeText: {
      type: String,
      default: '',
    },
    items: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      searchTerm: '',
    };
  },
  computed: {
    filteredItems() {
      if (!this.searchTerm.trim()) {
        return this.items;
      }
      const term = this.searchTerm.toLowerCase();
      return this.items.filter(item => {
        const nameMatch = item.name && item.name.toLowerCase().includes(term);
        const descMatch = item.description && item.description.toLowerCase().includes(term);
        return nameMatch || descMatch;
      });
    },
  },
  methods: {
    formatPrice(val) {
      return Number(val || 0).toFixed(2).replace('.', ',');
    },
  },
};
</script>

<style scoped>
.transition {
  transition: all 0.2s ease-in-out;
}
</style>


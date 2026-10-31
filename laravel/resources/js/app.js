import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import axios from 'axios';

// Configurações padrão do Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

const app = createApp(App);
app.config.globalProperties.$axios = axios;
app.mount('#app');

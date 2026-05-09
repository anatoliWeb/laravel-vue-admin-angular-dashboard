import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { initializeApplication } from './app/index';
import router from './router';
import { i18n } from './shared/i18n';
import '../scss/app.scss';

initializeApplication();

createApp(App)
  .use(createPinia())
  .use(i18n)
  .use(router)
  .mount('#app');

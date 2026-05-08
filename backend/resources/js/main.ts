import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { initializeApplication } from './app/index';
import router from './router';

initializeApplication();

createApp(App)
  .use(createPinia())
  .use(router)
  .mount('#app');

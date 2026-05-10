import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { initializeApplication } from './app/index';
import router from './router';
import { commandPaletteStore } from './shared/command-palette';
import { i18n } from './shared/i18n';
import '../scss/app.scss';

initializeApplication();
commandPaletteStore.init(router);
commandPaletteStore.registerNavigation({
  id: 'nav-dashboard',
  title: 'Dashboard',
  subtitle: 'Open operational overview',
  icon: '◉',
  keywords: ['home', 'overview', 'stats'],
  group: 'Navigation',
  to: '/dashboard',
});
commandPaletteStore.registerNavigation({
  id: 'nav-users',
  title: 'Users',
  subtitle: 'Open users management',
  icon: '◍',
  keywords: ['accounts', 'members'],
  group: 'Navigation',
  to: '/users',
});
commandPaletteStore.registerNavigation({
  id: 'nav-roles',
  title: 'Roles',
  subtitle: 'Open roles management',
  icon: '◌',
  keywords: ['rbac', 'access'],
  group: 'Navigation',
  to: '/roles',
});
commandPaletteStore.registerNavigation({
  id: 'nav-permissions',
  title: 'Permissions',
  subtitle: 'Open permissions management',
  icon: '◎',
  keywords: ['rbac', 'policy'],
  group: 'Navigation',
  to: '/permissions',
});
commandPaletteStore.registerNavigation({
  id: 'nav-tokens',
  title: 'Tokens',
  subtitle: 'Open API token management',
  icon: '◐',
  keywords: ['api', 'keys', 'scopes'],
  group: 'Navigation',
  to: '/tokens',
});
commandPaletteStore.registerNavigation({
  id: 'nav-activity',
  title: 'Activity',
  subtitle: 'Open audit activity stream',
  icon: '◑',
  keywords: ['logs', 'audit'],
  group: 'Navigation',
  to: '/activity',
});
commandPaletteStore.registerNavigation({
  id: 'nav-settings',
  title: 'Settings',
  subtitle: 'Open platform settings',
  icon: '◒',
  keywords: ['config', 'system'],
  group: 'Navigation',
  to: '/settings',
});
commandPaletteStore.registerNavigation({
  id: 'nav-billing',
  title: 'Billing',
  subtitle: 'Open subscription settings',
  icon: '◓',
  keywords: ['plan', 'invoice'],
  group: 'Navigation',
  to: '/billing',
});
commandPaletteStore.registerNavigation({
  id: 'nav-profile',
  title: 'Profile',
  subtitle: 'Open account profile',
  icon: '◔',
  keywords: ['account', 'me'],
  group: 'Navigation',
  to: '/profile',
});

createApp(App)
  .use(createPinia())
  .use(i18n)
  .use(router)
  .mount('#app');

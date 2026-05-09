import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

import AdminLayout from '../layouts/AdminLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';
import LoginView from '../modules/auth/views/LoginView.vue';
import DashboardView from '../modules/dashboard/views/DashboardView.vue';
import VueDemoPage from '../modules/dashboard/pages/VueDemoPage.vue';
import DemoUI from '../modules/demo/views/DemoUI.vue';
import NotFoundView from '../shared/components/NotFoundView.vue';

/**
 * Router architecture notes:
 * - Layout routes provide stable UI shells for route groups.
 * - Feature views live under `modules/*` to keep domain boundaries explicit.
 * - Guards and permission checks will be introduced later without changing
 *   route ownership structure.
 */
const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: AdminLayout,
    children: [
      {
        path: '',
        name: 'dashboard',
        component: DashboardView,
      },
      {
        path: 'demo-ui',
        name: 'demo-ui',
        component: DemoUI,
      },
      {
        path: 'vue-demo',
        name: 'vue-demo',
        component: VueDemoPage,
      },
    ],
  },
  {
    path: '/login',
    component: AuthLayout,
    children: [
      {
        path: '',
        name: 'login',
        component: LoginView,
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
  },
];

const router = createRouter({
  // Vue admin is mounted from Laravel /admin routes during migration.
  // Using /admin base keeps direct URLs like /admin/vue-demo resolvable by SPA router.
  history: createWebHistory('/admin'),
  routes,
});

export default router;

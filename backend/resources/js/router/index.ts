import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

import AdminLayout from '../layouts/AdminLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';
import LoginView from '../modules/auth/views/LoginView.vue';
import DashboardPage from '../modules/dashboard/pages/DashboardPage.vue';
import VueDemoPage from '../modules/dashboard/pages/VueDemoPage.vue';
import DemoUI from '../modules/demo/views/DemoUI.vue';
import ModulePlaceholderPage from '../modules/shared/pages/ModulePlaceholderPage.vue';
import UsersPage from '../modules/users/pages/UsersPage.vue';
import RolesPage from '../modules/roles/pages/RolesPage.vue';
import PermissionsPage from '../modules/permissions/pages/PermissionsPage.vue';
import TokensPage from '../modules/tokens/pages/TokensPage.vue';
import ActivityPage from '../modules/activity/pages/ActivityPage.vue';
import SettingsPage from '../modules/settings/pages/SettingsPage.vue';
import ProfilePage from '../modules/profile/pages/ProfilePage.vue';
import BillingPage from '../modules/billing/pages/BillingPage.vue';
import TranslationsPage from '../modules/translations/pages/TranslationsPage.vue';
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
        component: DashboardPage,
        meta: {
          title: 'Dashboard',
          subtitle: 'Operational overview',
        },
      },
      {
        path: 'dashboard',
        name: 'dashboard-page',
        component: DashboardPage,
        meta: {
          title: 'Dashboard',
          subtitle: 'Operational overview',
        },
      },
      {
        path: 'users',
        name: 'users',
        component: UsersPage,
        meta: {
          title: 'Users',
          subtitle: 'User management module',
        },
      },
      {
        path: 'roles',
        name: 'roles',
        component: RolesPage,
        meta: {
          title: 'Roles',
          subtitle: 'Role management module',
        },
      },
      {
        path: 'permissions',
        name: 'permissions',
        component: PermissionsPage,
        meta: {
          title: 'Permissions',
          subtitle: 'Permissions module',
        },
      },
      {
        path: 'tokens',
        name: 'tokens',
        component: TokensPage,
        meta: {
          title: 'Tokens',
          subtitle: 'API token management module',
        },
      },
      {
        path: 'activity',
        name: 'activity',
        component: ActivityPage,
        meta: {
          title: 'Activity',
          subtitle: 'Audit log and monitoring module',
        },
      },
      {
        path: 'settings',
        name: 'settings',
        component: SettingsPage,
        meta: {
          title: 'Settings',
          subtitle: 'Platform configuration module',
        },
      },
      {
        path: 'profile',
        name: 'profile',
        component: ProfilePage,
        meta: {
          title: 'Profile',
          subtitle: 'Account center',
        },
      },
      {
        path: 'billing',
        name: 'billing',
        component: BillingPage,
        meta: {
          title: 'Billing',
          subtitle: 'Subscription and usage',
        },
      },
      {
        path: 'translations',
        name: 'translations',
        component: TranslationsPage,
        meta: {
          title: 'Translations',
          subtitle: 'Runtime localization management',
        },
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
  // Vue admin is mounted under /admin/app/* to coexist with legacy /admin Blade pages.
  // This keeps migration route-by-route and avoids collisions with old server-rendered routes.
  history: createWebHistory('/admin/app'),
  routes,
});

export default router;

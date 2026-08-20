import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';
import LoginPage from '../admin/pages/LoginPage.vue';
import { useAuth } from '../admin/composables/useAuth';

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomePage,
  },
  {
    path: '/scholar/:id',
    name: 'scholar',
    component: () => import('../pages/ScholarPage.vue'),
  },
  {
    path: '/login',
    name: 'admin.login',
    component: LoginPage,
    meta: { guest: true, adminShell: true },
  },
  {
    path: '/admin/login',
    redirect: '/login',
  },
  {
    path: '/admin',
    component: () => import('../admin/layouts/AdminLayout.vue'),
    meta: { auth: true, adminShell: true },
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: () => import('../admin/pages/DashboardPage.vue'),
      },
      {
        path: 'madhahib',
        name: 'admin.madhahib',
        component: () => import('../admin/pages/MadhahibPage.vue'),
      },
      {
        path: 'languages',
        name: 'admin.languages',
        component: () => import('../admin/pages/LanguagesPage.vue'),
      },
      {
        path: 'scholars',
        name: 'admin.scholars',
        component: () => import('../admin/pages/ScholarsPage.vue'),
      },
      {
        path: 'bookings',
        name: 'admin.bookings',
        component: () => import('../admin/pages/BookingsPage.vue'),
      },
      {
        path: 'settings',
        name: 'admin.settings',
        component: () => import('../admin/pages/SettingsPage.vue'),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to) {
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' };
    }
    return { top: 0 };
  },
});

router.beforeEach(async (to) => {
  const needsAuthCheck = to.matched.some((record) => record.meta.auth || record.meta.guest);

  if (!needsAuthCheck) {
    return true;
  }

  const auth = useAuth();
  await auth.ensure();

  if (to.matched.some((record) => record.meta.auth) && !auth.user.value) {
    return { name: 'admin.login', query: { redirect: to.fullPath } };
  }

  if (to.matched.some((record) => record.meta.guest) && auth.user.value) {
    return { name: 'admin.dashboard' };
  }

  return true;
});

export default router;

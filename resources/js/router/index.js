import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';

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

export default router;

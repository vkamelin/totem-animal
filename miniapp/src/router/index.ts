import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'welcome',
    component: () => import('@/screens/WelcomeScreen.vue'),
  },
  {
    path: '/intro',
    name: 'intro',
    component: () => import('@/screens/TestIntroScreen.vue'),
  },
  {
    path: '/test',
    name: 'test',
    component: () => import('@/screens/TestScreen.vue'),
  },
  {
    path: '/result',
    name: 'result',
    component: () => import('@/screens/ResultScreen.vue'),
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/screens/ProfileScreen.vue'),
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'error',
    component: () => import('@/screens/ErrorScreen.vue'),
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

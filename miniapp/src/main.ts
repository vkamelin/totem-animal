import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from '@/App.vue';
import { router } from '@/router';
import { useAppStore } from '@/stores/appStore';
import { useUserStore } from '@/stores/userStore';
import { getVkLaunchParams } from '@/services/vkBridge';
import '@/style.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

const appStore = useAppStore(pinia);
const userStore = useUserStore(pinia);

userStore.hydrateFromStorage();
appStore.setLaunchParams(getVkLaunchParams());

app.mount('#app');

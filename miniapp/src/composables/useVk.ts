import { computed } from 'vue';

import { useAppStore } from '@/stores/appStore';
import { getVkLaunchParams, initVkBridge, setViewSettings } from '@/services/vkBridge';
import type { VkViewSettings } from '@/types/vk';

export function useVk() {
  const appStore = useAppStore();

  async function initializeVk() {
    const initialized = await initVkBridge();
    appStore.setVkInitialized(initialized);
    appStore.setLaunchParams(getVkLaunchParams());
    if (initialized) {
      await setViewSettings({
        status_bar_style: 'dark',
        action_bar_color: '#020617',
        navigation_bar_color: '#020617',
      });
    }
    return initialized;
  }

  function updateViewSettings(settings: VkViewSettings) {
    return setViewSettings(settings);
  }

  return {
    isVkInitialized: computed(() => appStore.isVkInitialized),
    launchParams: computed(() => appStore.launchParams),
    initializeVk,
    updateViewSettings,
  };
}

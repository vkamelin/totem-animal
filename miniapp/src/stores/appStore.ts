import { defineStore } from 'pinia';

import type { ApiClientError } from '@/types/api';
import type { VkLaunchParams } from '@/types/vk';

interface AppState {
  isLoading: boolean;
  globalError: ApiClientError | null;
  isVkInitialized: boolean;
  launchParams: VkLaunchParams | null;
}

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    isLoading: false,
    globalError: null,
    isVkInitialized: false,
    launchParams: null,
  }),
  actions: {
    setLoading(isLoading: boolean) {
      this.isLoading = isLoading;
    },
    setError(error: ApiClientError | null) {
      this.globalError = error;
    },
    setVkInitialized(isVkInitialized: boolean) {
      this.isVkInitialized = isVkInitialized;
    },
    setLaunchParams(launchParams: VkLaunchParams | null) {
      this.launchParams = launchParams;
    },
  },
});

import { computed } from 'vue';

import { useAppStore } from '@/stores/appStore';
import type { ApiClientError } from '@/types/api';

export function useApi() {
  const appStore = useAppStore();

  async function request<T>(runner: () => Promise<T>): Promise<T> {
    appStore.setLoading(true);
    appStore.setError(null);

    try {
      return await runner();
    } catch (error) {
      const normalized = normalizeError(error);
      appStore.setError(normalized);
      throw normalized;
    } finally {
      appStore.setLoading(false);
    }
  }

  return {
    isLoading: computed(() => appStore.isLoading),
    globalError: computed(() => appStore.globalError),
    request,
  };
}

function normalizeError(error: unknown): ApiClientError {
  if (isApiClientError(error)) {
    return error;
  }

  const fallback = new Error('Something went wrong.') as ApiClientError;
  fallback.name = 'ApiClientError';
  fallback.code = 'UNKNOWN_ERROR';
  fallback.status = 0;
  fallback.isApiError = true;
  fallback.details = error;
  return fallback;
}

function isApiClientError(error: unknown): error is ApiClientError {
  return (
    typeof error === 'object' &&
    error !== null &&
    'isApiError' in error &&
    (error as ApiClientError).isApiError === true
  );
}

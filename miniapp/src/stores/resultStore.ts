import { computed, ref } from 'vue';
import { defineStore } from 'pinia';

import type { ApiClientError } from '@/types/api';
import type { TotemResult } from '@/types/totem';

export const useResultStore = defineStore('result', () => {
  const result = ref<TotemResult | null>(null);
  const isEmptyResult = ref(false);
  const isLoading = ref(false);
  const error = ref<ApiClientError | null>(null);

  const hasResult = computed(() => result.value !== null);

  function setLoading(nextValue: boolean) {
    isLoading.value = nextValue;
  }

  function setError(nextValue: ApiClientError | null) {
    error.value = nextValue;
  }

  function setResult(nextResult: TotemResult | null) {
    result.value = nextResult;
    isEmptyResult.value = nextResult === null;
  }

  function setEmptyResult() {
    result.value = null;
    isEmptyResult.value = true;
  }

  function clear() {
    result.value = null;
    isEmptyResult.value = false;
    isLoading.value = false;
    error.value = null;
  }

  return {
    result,
    isEmptyResult,
    isLoading,
    error,
    hasResult,
    setLoading,
    setError,
    setResult,
    setEmptyResult,
    clear,
  };
});

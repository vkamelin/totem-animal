<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import BaseCard from '@/components/BaseCard.vue';
import ErrorState from '@/components/ErrorState.vue';
import { useAppStore } from '@/stores/appStore';

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();

const title = computed(() => {
  if (appStore.globalError) {
    return 'The app could not complete the request';
  }

  return 'Page not found';
});

const description = computed(() => {
  if (appStore.globalError) {
    return appStore.globalError.message;
  }

  return `The route "${route.fullPath}" does not exist in this mini app.`;
});
</script>

<template>
  <div class="space-y-4 pb-6">
    <BaseCard>
      <div class="space-y-2">
        <p class="text-sm font-medium text-red-300">Error</p>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-100">
          Something stopped the flow
        </h1>
        <p class="text-base leading-7 text-slate-400">
          This generic screen is used for route errors and any hard API failures that need a full-screen recovery point.
        </p>
      </div>
    </BaseCard>

    <ErrorState
      :title="title"
      :description="description"
      retry-label="Go home"
      home-label="Open intro"
      @retry="router.push('/')"
      @home="router.push('/intro')"
    />
  </div>
</template>

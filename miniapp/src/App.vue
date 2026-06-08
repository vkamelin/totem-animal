<script setup lang="ts">
import { onMounted } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { useVk } from '@/composables/useVk';

const { initializeVk } = useVk();

onMounted(() => {
  void initializeVk();
});
</script>

<template>
  <AppLayout>
    <RouterView />
  </AppLayout>
</template>

<script setup lang="ts">
import BaseButton from '@/components/BaseButton.vue';

withDefaults(
  defineProps<{
    title?: string;
    description?: string;
    retryLabel?: string;
    homeLabel?: string;
    showHomeAction?: boolean;
  }>(),
  {
    title: 'Something went wrong',
    description: 'Please try again.',
    retryLabel: 'Try again',
    homeLabel: 'Go home',
    showHomeAction: true,
  },
);

defineEmits<{
  retry: [];
  home: [];
}>();
</script>

<template>
  <div class="rounded-3xl border border-red-900/40 bg-red-950/30 p-5">
    <div class="space-y-2">
      <h2 class="text-lg font-semibold text-red-200">
        {{ title }}
      </h2>
      <p class="text-sm leading-6 text-red-200/80">
        {{ description }}
      </p>
    </div>
    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
      <BaseButton fullWidth variant="danger" @click="$emit('retry')">
        {{ retryLabel }}
      </BaseButton>
      <BaseButton v-if="showHomeAction" fullWidth variant="secondary" @click="$emit('home')">
        {{ homeLabel }}
      </BaseButton>
    </div>
  </div>
</template>

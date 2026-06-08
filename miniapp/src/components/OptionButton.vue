<script setup lang="ts">
import BaseButton from '@/components/BaseButton.vue';

withDefaults(
  defineProps<{
    selected?: boolean;
    disabled?: boolean;
    loading?: boolean;
  }>(),
  {
    selected: false,
    disabled: false,
    loading: false,
  },
);

defineEmits<{
  click: [event: MouseEvent];
}>();
</script>

<template>
  <BaseButton
    variant="secondary"
    fullWidth
    :disabled="disabled"
    :loading="loading"
    class="min-h-16 items-start justify-start rounded-3xl border px-5 py-4 text-left text-base font-medium leading-6"
    :class="
      selected
        ? 'border-violet-500 bg-violet-500/15 text-violet-100 ring-1 ring-inset ring-violet-500/40'
        : 'border-slate-700 bg-slate-800/90 text-slate-100 hover:border-slate-600 hover:bg-slate-700/90'
    "
    @click="$emit('click', $event)"
  >
    <span class="block w-full whitespace-normal"><slot /></span>
  </BaseButton>
</template>

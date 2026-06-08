<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    current: number;
    total: number;
  }>(),
  {
    current: 0,
    total: 0,
  },
);

const percentage = computed(() => {
  if (props.total <= 0) {
    return 0;
  }

  return Math.min(100, Math.max(0, (props.current / props.total) * 100));
});
</script>

<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between text-xs text-slate-400">
      <span>Progress</span>
      <span>{{ Math.min(current, total) }} / {{ total }}</span>
    </div>
    <div
      class="h-2 overflow-hidden rounded-full bg-slate-800"
      role="progressbar"
      :aria-valuenow="current"
      :aria-valuemin="0"
      :aria-valuemax="total"
    >
      <div
        class="h-full rounded-full bg-gradient-to-r from-violet-500 to-amber-400 transition-all duration-300"
        :style="{ width: `${percentage}%` }"
      />
    </div>
  </div>
</template>

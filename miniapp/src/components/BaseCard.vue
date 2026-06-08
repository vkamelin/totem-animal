<script setup lang="ts">
withDefaults(
  defineProps<{
    compact?: boolean;
  }>(),
  {
    compact: false,
  },
);
</script>

<template>
  <section
    class="rounded-3xl border border-slate-800 bg-slate-900/90 shadow-[0_1px_0_rgba(255,255,255,0.03)_inset]"
    :class="compact ? 'p-4' : 'p-5'"
  >
    <slot />
  </section>
</template>

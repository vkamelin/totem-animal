<script setup lang="ts">
import { computed, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    imageUrl?: string | null;
    animalName?: string | null;
    size?: number;
  }>(),
  {
    imageUrl: null,
    animalName: null,
    size: 144,
  },
);

const hasImageError = ref(false);
const shouldShowImage = computed(() => Boolean(props.imageUrl) && !hasImageError.value);
const initials = computed(() => (props.animalName?.trim()?.[0] ?? '✦').toUpperCase());
</script>

<template>
  <div
    class="relative overflow-hidden rounded-full border border-slate-700 bg-gradient-to-br from-violet-500/25 via-slate-900 to-amber-400/20 shadow-[0_0_0_1px_rgba(255,255,255,0.02)_inset]"
    :style="{ width: `${size}px`, height: `${size}px` }"
  >
    <img
      v-if="shouldShowImage"
      :src="imageUrl ?? undefined"
      :alt="animalName ? `${animalName} totem animal` : 'Totem animal preview'"
      class="h-full w-full object-cover"
      @error="hasImageError = true"
    />
    <div
      v-else
      class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(168,85,247,0.35),_transparent_65%)] text-4xl font-semibold text-amber-200"
      aria-hidden="true"
    >
      {{ initials }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

import { useAppStore } from '@/stores/appStore';
import { useUserStore } from '@/stores/userStore';

const route = useRoute();
const appStore = useAppStore();
const userStore = useUserStore();

const outerStyle = computed(() => ({
  paddingTop: 'max(1rem, env(safe-area-inset-top))',
  paddingBottom: 'max(1.25rem, env(safe-area-inset-bottom))',
}));

const currentLabel = computed(() => {
  const currentRoute = route.name;

  if (currentRoute === 'profile') {
    return 'Profile';
  }

  if (currentRoute === 'result') {
    return 'Result';
  }

  if (currentRoute === 'test') {
    return 'Test';
  }

  if (currentRoute === 'intro') {
    return 'Intro';
  }

  return 'Welcome';
});
</script>

<template>
  <div class="relative min-h-dvh overflow-hidden bg-slate-950 text-slate-100">
    <div
      class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(109,40,217,0.18),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.08),_transparent_28%)]"
      aria-hidden="true"
    />
    <div
      class="relative mx-auto flex min-h-dvh w-full max-w-[430px] flex-col px-4"
      :style="outerStyle"
    >
      <header class="mb-5 flex items-start justify-between gap-3">
        <div class="space-y-1">
          <p class="text-[0.7rem] uppercase tracking-[0.3em] text-amber-300/80">
            Totem Animal
          </p>
          <p class="text-xs text-slate-400">
            {{ currentLabel }}
          </p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-400">
          <span
            class="rounded-full border border-slate-800 bg-slate-900/80 px-3 py-1"
            :title="userStore.publicId ?? 'No public_id yet'"
          >
            {{ appStore.isVkInitialized ? 'VK ready' : 'VK...' }}
          </span>
        </div>
      </header>

      <main class="flex-1">
        <slot />
      </main>

      <footer class="mt-6 grid grid-cols-2 gap-3">
        <RouterLink
          to="/profile"
          class="rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-3 text-center text-sm font-medium text-slate-100 transition-colors hover:bg-slate-800"
        >
          Profile
        </RouterLink>
        <RouterLink
          to="/result"
          class="rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-3 text-center text-sm font-medium text-slate-100 transition-colors hover:bg-slate-800"
        >
          Result
        </RouterLink>
      </footer>
    </div>
  </div>
</template>

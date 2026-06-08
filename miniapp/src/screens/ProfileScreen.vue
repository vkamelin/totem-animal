<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import AnimalAvatar from '@/components/AnimalAvatar.vue';
import BaseButton from '@/components/BaseButton.vue';
import BaseCard from '@/components/BaseCard.vue';
import ErrorState from '@/components/ErrorState.vue';
import LoadingState from '@/components/LoadingState.vue';
import { useApi } from '@/composables/useApi';
import { getResult } from '@/services/apiClient';
import { useAppStore } from '@/stores/appStore';
import { useResultStore } from '@/stores/resultStore';
import { useUserStore } from '@/stores/userStore';
import type { ApiClientError } from '@/types/api';
import type { TotemResult } from '@/types/totem';

const router = useRouter();
const userStore = useUserStore();
const resultStore = useResultStore();
const appStore = useAppStore();
const { request, isLoading } = useApi();

const localError = ref<string | null>(null);

async function loadProfileResult() {
  if (!userStore.publicId) {
    return;
  }

  if (resultStore.hasResult) {
    return;
  }

  localError.value = null;

  await request(async () => {
    const response = await getResult(userStore.publicId as string);
    resultStore.setResult(response.data.result);
  }).catch((error: unknown) => {
    const apiError = error as ApiClientError;
    if (apiError?.code === 'RESULT_NOT_FOUND' || apiError?.code === 'CLIENT_NOT_FOUND') {
      appStore.setError(null);
      resultStore.setEmptyResult();
      return;
    }

    localError.value = error instanceof Error ? error.message : 'We could not load the profile.';
  });
}

function traitsToList(result: TotemResult) {
  return [
    ['Extraversion', result.user_traits.extraversion],
    ['Openness', result.user_traits.openness],
    ['Self-control', result.user_traits.self_control],
    ['Agreeableness', result.user_traits.agreeableness],
    ['Emotional stability', result.user_traits.emotional_stability],
    ['Dominance', result.user_traits.dominance],
    ['Adaptability', result.user_traits.adaptability],
  ] as const;
}

onMounted(() => {
  if (!userStore.hasHydrated) {
    userStore.hydrateFromStorage();
  }

  void loadProfileResult();
});
</script>

<template>
  <div class="space-y-4 pb-6">
    <div class="space-y-2">
      <p class="text-sm font-medium text-amber-300">Profile</p>
      <h1 class="text-3xl font-semibold tracking-tight text-slate-100">
        Your stored session
      </h1>
      <p class="text-base leading-7 text-slate-400">
        This screen keeps the locally stored public ID visible and shows a collected animal summary when the backend already has one.
      </p>
    </div>

    <BaseCard>
      <div class="space-y-3">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">public_id</p>
        <p class="break-all rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 font-mono text-sm text-slate-100">
          {{ userStore.publicId ?? 'No public_id saved yet' }}
        </p>
      </div>
    </BaseCard>

    <LoadingState v-if="isLoading && !resultStore.hasResult">
      <template #title>Loading profile summary</template>
      <template #description>We are checking whether this public ID already has a saved result.</template>
    </LoadingState>

    <ErrorState
      v-else-if="localError"
      title="We could not load the profile"
      :description="localError"
      retry-label="Try again"
      home-label="Back to intro"
      @retry="loadProfileResult"
      @home="router.push('/intro')"
    />

    <BaseCard v-else-if="resultStore.result">
      <div class="space-y-5">
        <div class="flex items-center gap-4">
          <AnimalAvatar
            :image-url="resultStore.result.result_image_path"
            :animal-name="resultStore.result.animal_name"
            :size="88"
          />
          <div class="space-y-1">
            <p class="text-sm text-emerald-300">Collected animal</p>
            <h2 class="text-2xl font-semibold text-slate-100">
              {{ resultStore.result.animal_name }}
            </h2>
          </div>
        </div>

        <p class="text-sm leading-6 text-slate-300">
          {{ resultStore.result.result_title }}
        </p>

        <div class="grid gap-3 sm:grid-cols-2">
          <div
            v-for="[label, value] in traitsToList(resultStore.result)"
            :key="label"
            class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4"
          >
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">
              {{ label }}
            </p>
            <p class="mt-2 text-lg font-semibold text-slate-100">
              {{ value ?? '—' }}
            </p>
          </div>
        </div>

        <BaseButton fullWidth variant="secondary" @click="router.push('/result')">
          Open result
        </BaseButton>
      </div>
    </BaseCard>

    <BaseCard v-else>
      <div class="space-y-3 text-center">
        <p class="text-sm font-medium text-amber-300">No collected animal yet</p>
        <p class="text-sm leading-6 text-slate-400">
          The backend has not stored a result snapshot for this public ID yet.
        </p>
        <BaseButton fullWidth @click="router.push('/intro')">
          Start test
        </BaseButton>
      </div>
    </BaseCard>
  </div>
</template>

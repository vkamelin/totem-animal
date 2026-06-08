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
import { useTestStore } from '@/stores/testStore';
import { useUserStore } from '@/stores/userStore';
import type { ApiClientError } from '@/types/api';
import type { TotemResult } from '@/types/totem';

const router = useRouter();
const userStore = useUserStore();
const resultStore = useResultStore();
const testStore = useTestStore();
const appStore = useAppStore();
const { request, isLoading } = useApi();

const localError = ref<string | null>(null);

async function loadResult() {
  if (!userStore.publicId) {
    appStore.setError(null);
    resultStore.setEmptyResult();
    return;
  }

  localError.value = null;
  resultStore.setLoading(true);
  resultStore.setError(null);

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

    resultStore.setError(apiError);
    localError.value = error instanceof Error ? error.message : 'We could not load your result.';
  }).finally(() => {
    resultStore.setLoading(false);
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

function restartTest() {
  testStore.resetTest();
  resultStore.clear();
  void router.push('/intro');
}

onMounted(() => {
  void loadResult();
});
</script>

<template>
  <div class="space-y-4 pb-6">
    <LoadingState v-if="isLoading || resultStore.isLoading">
      <template #title>Loading result</template>
      <template #description>We are checking whether your totem animal has already been collected.</template>
    </LoadingState>

    <ErrorState
      v-else-if="localError"
      title="We could not load your result"
      :description="localError"
      retry-label="Try again"
      home-label="Back to intro"
      @retry="loadResult"
      @home="router.push('/intro')"
    />

    <BaseCard v-else-if="resultStore.result">
      <div class="space-y-5">
        <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
          <AnimalAvatar
            :image-url="resultStore.result.result_image_path"
            :animal-name="resultStore.result.animal_name"
            :size="120"
          />
          <div class="space-y-2">
            <p class="text-sm font-medium text-emerald-300">Collected result</p>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-100">
              {{ resultStore.result.result_title }}
            </h1>
            <p class="text-base leading-7 text-slate-300">
              {{ resultStore.result.result_description }}
            </p>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
          <p class="text-sm text-slate-400">Animal name</p>
          <p class="mt-1 text-xl font-semibold text-amber-300">{{ resultStore.result.animal_name }}</p>
        </div>

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

        <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4 text-sm text-slate-400">
          <p>Result snapshot saved at {{ resultStore.result.created_at }}</p>
          <p class="mt-1">Distance score: {{ resultStore.result.score_distance }}</p>
        </div>

        <BaseButton fullWidth @click="restartTest">
          Restart test
        </BaseButton>
      </div>
    </BaseCard>

    <BaseCard v-else>
      <div class="space-y-4 text-center">
        <p class="text-sm font-medium text-emerald-300">No result yet</p>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-100">
          Your totem animal has not been collected yet.
        </h1>
        <p class="text-sm leading-6 text-slate-400">
          Start the test to generate and save a result snapshot for your current public ID.
        </p>
        <BaseButton fullWidth @click="router.push('/intro')">
          Start test
        </BaseButton>
      </div>
    </BaseCard>
  </div>
</template>
